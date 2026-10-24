#!/usr/bin/env bash
# ==============================================================================
# Digitsflow PHP Installer
# Downloads and installs the hardened Digitsflow PHP release binary into /opt/digitsflow/php
# Supports Linux x86_64 (amd64) and aarch64 (arm64)
# ==============================================================================
set -euo pipefail

# Available default release versions
LATEST_86="v8.6.0-df.2"
LATEST_85="v8.5.12-df.2"
LATEST_84="v8.4.28-df.1"
LATEST_83="v8.3.37-df.1"
LATEST_82="v8.2.36-df.1"
LATEST_81="v8.1.34-df.17"
LATEST_80="v8.0.30-df.1"
LATEST_74="v7.4.33-df.1"
LATEST_73="v7.3.33-df.1"
LATEST_72="v7.2.34-df.1"
LATEST_71="v7.1.33-df.1"
LATEST_70="v7.0.33-df.1"
LATEST_56="v5.6.40-df.1"
LATEST_55="v5.5.38-df.1"
LATEST_54="v5.4.45-df.1"
LATEST_53="v5.3.29-df.1"
LATEST_52="v5.2.17-df.1"
LATEST_51="v5.1.6-df.1"
LATEST_50="v5.0.5-df.1"
LATEST_44="v4.4.9-df.1"
LATEST_43="v4.3.11-df.1"
LATEST_42="v4.2.3-df.1"
LATEST_41="v4.1.2-df.1"
LATEST_40="v4.0.6-df.1"
LATEST_30="v3.0.18-df.1"
LATEST_20="v2.0.1-df.1"
LATEST_10="v1.0.0-df.1"

PHP_VER="${PHP_VER:-8.4}"
for arg in "$@"; do
  case "$arg" in
    --version=*) PHP_VER="${arg#*=}" ;;
  esac
done

case "${PHP_VER}" in
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

echo "--> Downloading ${TARBALL_NAME}..."
if command -v curl >/dev/null 2>&1; then
  curl -fsSL "${DOWNLOAD_URL}" -o "${TMP_DIR}/${TARBALL_NAME}"
elif command -v wget >/dev/null 2>&1; then
  wget -q "${DOWNLOAD_URL}" -O "${TMP_DIR}/${TARBALL_NAME}"
else
  echo "Error: Neither curl nor wget found. Please install curl or wget."
  exit 1
fi

echo "--> Extracting files to /..."
tar -xzf "${TMP_DIR}/${TARBALL_NAME}" -C /

# Ensure execution permissions on installed binaries
chmod +x /opt/digitsflow/php/bin/* /opt/digitsflow/php/sbin/* 2>/dev/null || true

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

echo "=========================================================="
echo " Digitsflow PHP ${VERSION} (${ARCH}) installed successfully!"
echo "=========================================================="
/opt/digitsflow/php/bin/php-digitsflow -v
