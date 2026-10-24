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

# 2. Configure build with full isolation flags
echo "[2/4] Running configure..."
./configure \
  --prefix="${PREFIX}" \
  --program-suffix="-digitsflow" \
  --with-config-file-path="${SYSCONFDIR}" \
  --with-config-file-scan-dir="${SYSCONFDIR}/conf.d" \
  PHP_EXTRA_VERSION="${EXTRA_VERSION}" \
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
