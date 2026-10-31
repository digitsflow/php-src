#!/usr/bin/env bash
# ==============================================================================
# Digitsflow PHP Installer
# Downloads and installs the hardened Digitsflow PHP release binary into /opt/digitsflow/php
# Supports Linux x86_64 (amd64) and aarch64 (arm64)
# ==============================================================================
set -euo pipefail

# Available default release versions (1 non-overlapping release per PHP series)
LATEST_87="v8.7.0-dev-df.4"
LATEST_86="v8.6.0-df.4"
LATEST_85="v8.5.13-df.2"
LATEST_84="v8.4.28-df.3"
LATEST_83="v8.3.37-df.3"
LATEST_82="v8.2.36-df.3"
LATEST_81="v8.1.34-df.19"
LATEST_80="v8.0.30-df.3"
LATEST_74="v7.4.33-df.3"
LATEST_73="v7.3.33-df.3"
LATEST_72="v7.2.34-df.3"
LATEST_71="v7.1.33-df.3"
LATEST_70="v7.0.33-df.3"
LATEST_56="v5.6.40-df.3"
LATEST_55="v5.5.38-df.3"
LATEST_54="v5.4.45-df.3"
LATEST_53="v5.3.29-df.3"
LATEST_52="v5.2.17-df.3"
LATEST_51="v5.1.6-df.3"
LATEST_50="v5.0.5-df.3"
LATEST_44="v4.4.9-df.3"
LATEST_43="v4.3.11-df.3"
LATEST_42="v4.2.3-df.3"
LATEST_41="v4.1.2-df.3"
LATEST_40="v4.0.6-df.3"
LATEST_30="v3.0.18-df.3"
LATEST_20="v2.0.1-df.3"
LATEST_10="v1.0.0-df.3"

PHP_VER="${PHP_VER:-8.4}"
for arg in "$@"; do
  case "$arg" in
    --version=*) PHP_VER="${arg#*=}" ;;
  esac
done

case "${PHP_VER}" in
  8.7|master) DEFAULT_VERSION="${LATEST_87}" ;;
  8.6) DEFAULT_VERSION="${LATEST_86}" ;;
  8.5) DEFAULT_VERSION="${LATEST_85}" ;;
  8.4) DEFAULT_VERSION="${LATEST_84}" ;;
  8.3) DEFAULT_VERSION="${LATEST_83}" ;;
  8.2) DEFAULT_VERSION="${LATEST_82}" ;;
  8.1) DEFAULT_VERSION="${LATEST_81}" ;;
  8.0) DEFAULT_VERSION="${LATEST_80}" ;;
  7.4) DEFAULT_VERSION="${LATEST_74}" ;;
  7.3) DEFAULT_VERSION="${LATEST_73}" ;;
  7.2) DEFAULT_VERSION="${LATEST_72}" ;;
  7.1) DEFAULT_VERSION="${LATEST_71}" ;;
  7.0) DEFAULT_VERSION="${LATEST_70}" ;;
  5.6) DEFAULT_VERSION="${LATEST_56}" ;;
  5.5) DEFAULT_VERSION="${LATEST_55}" ;;
  5.4) DEFAULT_VERSION="${LATEST_54}" ;;
  5.3) DEFAULT_VERSION="${LATEST_53}" ;;
  5.2) DEFAULT_VERSION="${LATEST_52}" ;;
  5.1) DEFAULT_VERSION="${LATEST_51}" ;;
  5.0) DEFAULT_VERSION="${LATEST_50}" ;;
  4.4) DEFAULT_VERSION="${LATEST_44}" ;;
  4.3) DEFAULT_VERSION="${LATEST_43}" ;;
  4.2) DEFAULT_VERSION="${LATEST_42}" ;;
  4.1) DEFAULT_VERSION="${LATEST_41}" ;;
  4.0) DEFAULT_VERSION="${LATEST_40}" ;;
  3.0) DEFAULT_VERSION="${LATEST_30}" ;;
  2.0) DEFAULT_VERSION="${LATEST_20}" ;;
  1.0) DEFAULT_VERSION="${LATEST_10}" ;;
  *)   DEFAULT_VERSION="${LATEST_84}" ;;
esac

VERSION="${VERSION:-${DEFAULT_VERSION}}"
GITHUB_REPO="digitsflow/php-src"

# Architecture verification (supports x86_64/amd64 and aarch64/arm64)
RAW_ARCH="$(uname -m)"
case "${RAW_ARCH}" in
  x86_64|amd64)
    ARCH="x86_64"
    ;;
  aarch64|arm64)
    ARCH="aarch64"
    ;;
  *)
    echo "Error: Prebuilt Digitsflow PHP binaries are built for x86_64 and aarch64 (detected: ${RAW_ARCH})."
    echo "Please compile from source using ./packaging/scripts/build-digitsflow.sh"
    exit 1
    ;;
esac

# Ensure running with sufficient privileges
if [ "$(id -u)" -ne 0 ]; then
  echo "Error: This installer requires root privileges. Please re-run with sudo."
  exit 1
fi

echo "=========================================================="
echo " Digitsflow PHP Installer"
echo " Target Version: ${VERSION}"
echo " Architecture:   ${ARCH}"
echo " Install Prefix: /opt/digitsflow/php"
echo " Config Dir:     /etc/digitsflow/php"
echo "=========================================================="

TARBALL_NAME="php-digitsflow-${VERSION}-linux-${ARCH}.tar.gz"
DOWNLOAD_URL="https://github.com/${GITHUB_REPO}/releases/download/${VERSION}/${TARBALL_NAME}"

TMP_DIR="$(mktemp -d /tmp/digitsflow-install-XXXXXX)"
cleanup() {
  rm -rf "${TMP_DIR}"
}
trap cleanup EXIT

if [ -n "${LOCAL_TARBALL:-}" ]; then
  if [ ! -f "${LOCAL_TARBALL}" ]; then
    echo "Error: LOCAL_TARBALL not found at ${LOCAL_TARBALL}"
    exit 1
  fi
  echo "--> Using local tarball ${LOCAL_TARBALL}..."
  cp "${LOCAL_TARBALL}" "${TMP_DIR}/${TARBALL_NAME}"
else
  echo "--> Downloading ${TARBALL_NAME}..."
  download_pkg() {
    local url="$1"
    if command -v curl >/dev/null 2>&1; then
      curl -fsSL "${url}" -o "${TMP_DIR}/${TARBALL_NAME}"
    elif command -v wget >/dev/null 2>&1; then
      wget -q "${url}" -O "${TMP_DIR}/${TARBALL_NAME}"
    else
      echo "Error: Neither curl nor wget found. Please install curl or wget."
      exit 1
    fi
  }
  if ! download_pkg "${DOWNLOAD_URL}"; then
    SERIES_MATCH="${PHP_VER}"
    [ "${SERIES_MATCH}" = "master" ] && SERIES_MATCH="8.7"
    API_URL="https://api.github.com/repos/${GITHUB_REPO}/releases?per_page=100"
    FALLBACK_TAG=""
    if command -v curl >/dev/null 2>&1; then
      FALLBACK_TAG="$(curl -fsSL "${API_URL}" 2>/dev/null | grep -oE "\"tag_name\":[[:space:]]*\"v${SERIES_MATCH//./\\.}\\.[^\"]+\"" | head -n 1 | sed -E 's/.*"([^"]+)"/\1/' || true)"
    elif command -v wget >/dev/null 2>&1; then
      FALLBACK_TAG="$(wget -qO- "${API_URL}" 2>/dev/null | grep -oE "\"tag_name\":[[:space:]]*\"v${SERIES_MATCH//./\\.}\\.[^\"]+\"" | head -n 1 | sed -E 's/.*"([^"]+)"/\1/' || true)"
    fi
    if [ -n "${FALLBACK_TAG}" ] && [ "${FALLBACK_TAG}" != "${VERSION}" ]; then
      VERSION="${FALLBACK_TAG}"
      TARBALL_NAME="php-digitsflow-${VERSION}-linux-${ARCH}.tar.gz"
      DOWNLOAD_URL="https://github.com/${GITHUB_REPO}/releases/download/${VERSION}/${TARBALL_NAME}"
      echo "--> Resolved active series release ${VERSION}; downloading ${TARBALL_NAME}..."
      download_pkg "${DOWNLOAD_URL}"
    else
      echo "Error: Failed to download ${DOWNLOAD_URL}"
      exit 1
    fi
  fi
fi

echo "--> Verifying package integrity & symbol relocations before installation..."
STAGE_DIR="${TMP_DIR}/stage"
mkdir -p "${STAGE_DIR}"
tar -xzf "${TMP_DIR}/${TARBALL_NAME}" -C "${STAGE_DIR}"
chmod +x "${STAGE_DIR}/opt/digitsflow/php/bin/"* "${STAGE_DIR}/opt/digitsflow/php/sbin/"* 2>/dev/null || true
chmod 755 "${STAGE_DIR}/opt/digitsflow/php/lib/"*.so* 2>/dev/null || true

if [ ! -x "${STAGE_DIR}/opt/digitsflow/php/bin/php-digitsflow" ]; then
  echo "Error: Package is missing /opt/digitsflow/php/bin/php-digitsflow."
  exit 1
fi
if command -v ldd >/dev/null 2>&1; then
  if LD_LIBRARY_PATH="${STAGE_DIR}/opt/digitsflow/php/lib" ldd -r "${STAGE_DIR}/opt/digitsflow/php/bin/php-digitsflow" 2>&1 | grep -E 'not found|undefined symbol'; then
    echo "Error: Pre-install symbol or library verification failed for php-digitsflow."
    exit 1
  fi
fi
LD_BIND_NOW=1 LD_LIBRARY_PATH="${STAGE_DIR}/opt/digitsflow/php/lib" "${STAGE_DIR}/opt/digitsflow/php/bin/php-digitsflow" -v >/dev/null

echo "--> Extracting files to /..."
rm -rf /opt/digitsflow/php/bin /opt/digitsflow/php/sbin /opt/digitsflow/php/lib 2>/dev/null || true
tar -xzf "${TMP_DIR}/${TARBALL_NAME}" -C /

# Ensure execution permissions on installed binaries and bundled libraries
chmod +x /opt/digitsflow/php/bin/* /opt/digitsflow/php/sbin/* 2>/dev/null || true
chmod 755 /opt/digitsflow/php/lib/*.so* 2>/dev/null || true

# Symlink binaries into /usr/local/bin
echo "--> Creating symlinks in /usr/local/bin..."
mkdir -p /usr/local/bin
ln -sf /opt/digitsflow/php/bin/php-digitsflow /usr/local/bin/php-digitsflow
ln -sf /opt/digitsflow/php/sbin/php-fpm-digitsflow /usr/local/bin/php-fpm-digitsflow

# Optionally symlink default php if requested or not present
if [ "${SYMLINK_PHP:-0}" = "1" ] || [ ! -e /usr/local/bin/php ]; then
  ln -sf /opt/digitsflow/php/bin/php-digitsflow /usr/local/bin/php
  echo "    Symlinked /usr/local/bin/php -> /opt/digitsflow/php/bin/php-digitsflow"
fi

if [ "${SYMLINK_PHP:-0}" = "1" ] || [ ! -e /usr/local/bin/php-fpm ]; then
  ln -sf /opt/digitsflow/php/sbin/php-fpm-digitsflow /usr/local/bin/php-fpm
  echo "    Symlinked /usr/local/bin/php-fpm -> /opt/digitsflow/php/sbin/php-fpm-digitsflow"
fi

# Reload systemd if available
if command -v systemctl >/dev/null 2>&1 && [ -d /run/systemd/system ]; then
  echo "--> Registering systemd service..."
  systemctl daemon-reload
  echo "    To enable and start PHP-FPM now, run:"
  echo "    sudo systemctl enable --now digitsflow-php-fpm"
fi

# Final post-install eager symbol binding verification before declaring success
LD_BIND_NOW=1 /opt/digitsflow/php/bin/php-digitsflow -v >/dev/null
if [ -x /opt/digitsflow/php/sbin/php-fpm-digitsflow ]; then
  LD_BIND_NOW=1 /opt/digitsflow/php/sbin/php-fpm-digitsflow -v >/dev/null
fi

echo "=========================================================="
echo " Digitsflow PHP ${VERSION} (${ARCH}) installed successfully!"
echo "=========================================================="
/opt/digitsflow/php/bin/php-digitsflow -v
