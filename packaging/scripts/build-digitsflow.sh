#!/usr/bin/env bash
set -euo pipefail

# ==============================================================================
# Digitsflow PHP Build & Packaging Script
# ==============================================================================
# Target install prefix: /opt/digitsflow/php
# Custom binary suffix:  -digitsflow (e.g. php-digitsflow, php-fpm-digitsflow)
# Custom config paths:   /etc/digitsflow/php and /etc/digitsflow/php/conf.d
# Custom runtime path:   /run/digitsflow
# ==============================================================================

PREFIX="${PREFIX:-/opt/digitsflow/php}"
SYSCONFDIR="${SYSCONFDIR:-/etc/digitsflow/php}"
RUNTIMEDIR="${RUNTIMEDIR:-/run/digitsflow}"
INSTALL_ROOT="${INSTALL_ROOT:-}"
EXTRA_VERSION="${EXTRA_VERSION:--digitsflow}"

# Concurrency
NPROC=$(nproc 2>/dev/null || sysctl -n hw.ncpu 2>/dev/null || echo 4)

echo "=========================================================="
echo " Building Digitsflow PHP"
echo " Prefix:           ${PREFIX}"
echo " Config Dir:       ${SYSCONFDIR}"
echo " Extra Version:    ${EXTRA_VERSION}"
echo " Parallel Jobs:    ${NPROC}"
if [ -n "${INSTALL_ROOT}" ]; then
  echo " Staging Root:     ${INSTALL_ROOT}"
fi
echo "=========================================================="

# 1. Regenerate build configuration files
if [ ! -f ./configure ]; then
  echo "[1/4] Running buildconf..."
  ./buildconf --force
fi

# Detect optional library support via pkg-config
EXTRA_CONFIG_FLAGS=()

if pkg-config --exists libsystemd; then
  echo "Found libsystemd: Enabling systemd integration in PHP-FPM"
  EXTRA_CONFIG_FLAGS+=( "--with-fpm-systemd" )
else
  echo "libsystemd not found: Building PHP-FPM without systemd notify"
fi

if pkg-config --exists icu-uc; then
  echo "Found libicu: Enabling intl extension"
  EXTRA_CONFIG_FLAGS+=( "--enable-intl" )
fi

# 2. Configure build with full isolation flags and self-contained RPATH
echo "[2/4] Running configure..."
export LDFLAGS="${LDFLAGS:-} -Wl,-rpath,${PREFIX}/lib -Wl,--disable-new-dtags -Wl,-z,lazy"
./configure \
  --prefix="${PREFIX}" \
  --program-suffix="-digitsflow" \
  --with-config-file-path="${SYSCONFDIR}" \
  --with-config-file-scan-dir="${SYSCONFDIR}/conf.d" \
  PHP_EXTRA_VERSION="${EXTRA_VERSION}" \
  ac_cv_lib_pq_PQclosePrepared=no \
  ac_cv_lib_pq_PQclosePortal=no \
  ac_cv_lib_pq_PQchangePassword=no \
  ac_cv_lib_pq_PQsocketPoll=no \
  ac_cv_lib_pq_PQsetChunkedRowsMode=no \
  --enable-fpm \
  --with-fpm-user=www-data \
  --with-fpm-group=www-data \
  --disable-short-tags \
  --enable-cli \
  --enable-mbstring \
  --enable-session \
  --enable-pdo \
  --with-pdo-mysql=mysqlnd \
  --with-pdo-pgsql \
  --with-openssl \
  --with-zlib \
  --with-zip \
  --with-curl \
  --with-libxml \
  --enable-dom \
  --enable-xml \
  --enable-bcmath \
  --enable-pcntl \
  --enable-sockets \
  --with-pear=no \
  "${EXTRA_CONFIG_FLAGS[@]}"

# 3. Compile
echo "[3/4] Compiling PHP with ${NPROC} workers..."
make -j"${NPROC}"

# 4. Install
echo "[4/4] Installing..."
if [ -n "${INSTALL_ROOT}" ]; then
  make install INSTALL_ROOT="${INSTALL_ROOT}"

  # Bundle non-glibc runtime shared libraries into ${INSTALL_ROOT}${PREFIX}/lib
  # so the prebuilt tarball is self-contained across Ubuntu/Debian hosts.
  mkdir -p "${INSTALL_ROOT}${PREFIX}/lib"
  if command -v ldd >/dev/null 2>&1; then
    echo "Bundling runtime shared libraries into ${PREFIX}/lib..."
    for bin_target in "${INSTALL_ROOT}${PREFIX}/bin/php-digitsflow" "${INSTALL_ROOT}${PREFIX}/sbin/php-fpm-digitsflow"; do
      if [ -f "${bin_target}" ]; then
        while read -r soname lib_path; do
          [ -z "${soname}" ] || [ -z "${lib_path}" ] && continue
          case "${soname}" in
            linux-vdso*|ld-linux*|libc.so*|libm.so*|libdl.so*|librt.so*|libpthread.so*|libresolv.so*|libutil.so*|libnsl.so*|libanl.so*|libBrokenLocale.so*)
              continue
              ;;
          esac
          if [ -f "${lib_path}" ] && [ ! -f "${INSTALL_ROOT}${PREFIX}/lib/${soname}" ]; then
            cp -L "${lib_path}" "${INSTALL_ROOT}${PREFIX}/lib/${soname}"
            chmod 755 "${INSTALL_ROOT}${PREFIX}/lib/${soname}"
          fi
        done < <(ldd "${bin_target}" | awk '/=> \// { print $1, $3 }')
      fi
    done
    if command -v patchelf >/dev/null 2>&1; then
      for elf_target in "${INSTALL_ROOT}${PREFIX}/bin/php-digitsflow" "${INSTALL_ROOT}${PREFIX}/sbin/php-fpm-digitsflow" "${INSTALL_ROOT}${PREFIX}/lib/"*.so*; do
        if [ -f "${elf_target}" ]; then
          patchelf --force-rpath --set-rpath "${PREFIX}/lib" "${elf_target}" 2>/dev/null || true
        fi
      done
    fi
    echo "Verifying staged ELF symbol relocations and eager binding (LD_BIND_NOW=1)..."
    for bin_target in "${INSTALL_ROOT}${PREFIX}/bin/php-digitsflow" "${INSTALL_ROOT}${PREFIX}/sbin/php-fpm-digitsflow"; do
      if [ -x "${bin_target}" ]; then
        if LD_LIBRARY_PATH="${INSTALL_ROOT}${PREFIX}/lib" ldd -r "${bin_target}" 2>&1 | grep -E 'not found|undefined symbol'; then
          echo "ERROR: Unresolved shared library or symbol relocation detected in ${bin_target}!"
          exit 1
        fi
        LD_BIND_NOW=1 LD_LIBRARY_PATH="${INSTALL_ROOT}${PREFIX}/lib" "${bin_target}" -v >/dev/null
      fi
    done
  fi
  
  # Ensure isolated configuration and runtime directories exist in the package
  mkdir -p "${INSTALL_ROOT}${SYSCONFDIR}/conf.d"
  mkdir -p "${INSTALL_ROOT}${RUNTIMEDIR}"
  
  # Copy production INI template if not already present
  if [ ! -f "${INSTALL_ROOT}${SYSCONFDIR}/php.ini" ]; then
    cp php.ini-production "${INSTALL_ROOT}${SYSCONFDIR}/php.ini"
  fi
  
  # Copy sample FPM config if available
  if [ -f packaging/fpm/php-fpm.conf ]; then
    cp packaging/fpm/php-fpm.conf "${INSTALL_ROOT}${SYSCONFDIR}/php-fpm.conf"
    mkdir -p "${INSTALL_ROOT}${SYSCONFDIR}/php-fpm.d"
    cp packaging/fpm/digitsflow-pool.conf "${INSTALL_ROOT}${SYSCONFDIR}/php-fpm.d/www.conf"
  fi
  
  # Copy systemd service file into package
  if [ -f packaging/systemd/digitsflow-php-fpm.service ]; then
    mkdir -p "${INSTALL_ROOT}/etc/systemd/system"
    cp packaging/systemd/digitsflow-php-fpm.service "${INSTALL_ROOT}/etc/systemd/system/digitsflow-php-fpm.service"
  fi
else
  sudo make install
  sudo mkdir -p "${SYSCONFDIR}/conf.d" "${RUNTIMEDIR}"
  if [ ! -f "${SYSCONFDIR}/php.ini" ]; then
    sudo cp php.ini-production "${SYSCONFDIR}/php.ini"
  fi
fi

echo "=========================================================="
echo " Build & Installation complete!"
echo " Binaries located in: ${PREFIX}/bin and ${PREFIX}/sbin"
echo "=========================================================="
