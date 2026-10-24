#!/usr/bin/env bash
# ==============================================================================
# Digitsflow PHP Installer
# Downloads and installs the hardened Digitsflow PHP release binary into /opt/digitsflow/php
# ==============================================================================
set -euo pipefail

# Available default release versions
LATEST_84="v8.4.5-df.15"
LATEST_83="v8.3.36-df.12"
LATEST_82="v8.2.35-df.12"
LATEST_81="v8.1.34-df.12"

PHP_VER="${PHP_VER:-8.4}"
case "${PHP_VER}" in
  8.4) DEFAULT_VERSION="${LATEST_84}" ;;
  8.3) DEFAULT_VERSION="${LATEST_83}" ;;
  8.2) DEFAULT_VERSION="${LATEST_82}" ;;
  8.1) DEFAULT_VERSION="${LATEST_81}" ;;
  *)   DEFAULT_VERSION="${LATEST_84}" ;;
esac

VERSION="${VERSION:-${DEFAULT_VERSION}}"
GITHUB_REPO="digitsflow/php-src"

# Architecture verification
ARCH="$(uname -m)"
if [ "${ARCH}" != "x86_64" ]; then
  echo "Error: Prebuilt Digitsflow PHP binaries are currently built for x86_64 (detected: ${ARCH})."
  echo "Please compile from source using ./packaging/scripts/build-digitsflow.sh"
  exit 1
fi

# Ensure running with sufficient privileges
if [ "$(id -u)" -ne 0 ]; then
  echo "Error: This installer requires root privileges. Please re-run with sudo."
  exit 1
fi

echo "=========================================================="
echo " Digitsflow PHP Installer"
echo " Target Version: ${VERSION}"
echo " Install Prefix: /opt/digitsflow/php"
echo " Config Dir:     /etc/digitsflow/php"
echo "=========================================================="

TARBALL_NAME="php-digitsflow-${VERSION}-linux-x86_64.tar.gz"
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
echo " Digitsflow PHP ${VERSION} installed successfully!"
echo "=========================================================="
/opt/digitsflow/php/bin/php-digitsflow -v
