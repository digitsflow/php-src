<div align="center">
    <a href="https://github.com/digitsflow/php-src">
        <img
            alt="PHP"
            src="https://www.php.net/images/logos/new-php-logo.svg"
            width="160">
    </a>
    <h1>Digitsflow PHP</h1>
    <p><strong>Security-Hardened, Memory-Safe, Production-Ready PHP Distribution (PHP 1.0 – 8.7-dev)</strong></p>
</div>

[![Security & Sanitizer Quality Gate (ASAN / UBSAN)](https://github.com/digitsflow/php-src/actions/workflows/security-quality-gate.yml/badge.svg)](https://github.com/digitsflow/php-src/actions/workflows/security-quality-gate.yml)
[![Build & Release Digitsflow PHP](https://github.com/digitsflow/php-src/actions/workflows/build-digitsflow.yml/badge.svg)](https://github.com/digitsflow/php-src/actions/workflows/build-digitsflow.yml)
[![Push CI Matrix](https://github.com/digitsflow/php-src/actions/workflows/push.yml/badge.svg)](https://github.com/digitsflow/php-src/actions/workflows/push.yml)
[![License: PHP 3.01](https://img.shields.io/badge/License-PHP%203.01-blue.svg)](LICENSE)

**Digitsflow PHP** is an enterprise-grade distribution of the PHP interpreter maintained by Digitsflow. Unlike standard upstream support windows, Digitsflow maintains and security-patches **every major and minor PHP release series from PHP 1.0 through PHP 8.6 and 8.7-dev (`master`)**, delivering proactive security patches, use-after-free (UAF) mitigations, integer overflow protections, and memory leak fixes across all 28 release lines.

Every release across every version line is continuously audited and gated against **AddressSanitizer (ASAN)**, **UndefinedBehaviorSanitizer (UBSan)**, **Tracing JIT**, and the full multi-platform CI matrix (Linux `x86_64` / `aarch64` / `x32`, macOS `x64` / `arm64`, Windows, FreeBSD) with zero skipped checks.

Digitsflow PHP is engineered for **safe side-by-side coexistence**: it installs cleanly into isolated paths (`/opt/digitsflow/php` and `/etc/digitsflow/php`), allowing you to deploy hardened PHP on both **`x86_64` (`amd64`)** and **`aarch64` (`arm64`)** servers without interfering with OS package managers or default system PHP binaries.

---

## 📋 Full Release Matrix (PHP 1.0 – 8.7-dev)

### Active & Modern PHP 8.x Series
| PHP Series | Branch | Latest Release Tag | Docker Image (`ghcr.io`) | Architecture |
| :--- | :--- | :--- | :--- | :--- |
| **PHP 8.7-dev** *(Master)* | `master` | [`v8.7.0-dev-df.2`](https://github.com/digitsflow/php-src/releases/tag/v8.7.0-dev-df.2) | `ghcr.io/digitsflow/php-src:latest`<br>`ghcr.io/digitsflow/php-src:master` | `x86_64`, `aarch64` |
| **PHP 8.6** | `digitsflow-8.6` | [`v8.6.0-df.2`](https://github.com/digitsflow/php-src/releases/tag/v8.6.0-df.2) | `ghcr.io/digitsflow/php-src:8.6`<br>`ghcr.io/digitsflow/php-src:v8.6.0-df.2` | `x86_64`, `aarch64` |
| **PHP 8.5** | `digitsflow-8.5` | [`v8.5.12-df.2`](https://github.com/digitsflow/php-src/releases/tag/v8.5.12-df.2) | `ghcr.io/digitsflow/php-src:8.5`<br>`ghcr.io/digitsflow/php-src:v8.5.12-df.2` | `x86_64`, `aarch64` |
| **PHP 8.4** *(Default)* | `digitsflow-8.4` | [`v8.4.28-df.1`](https://github.com/digitsflow/php-src/releases/tag/v8.4.28-df.1) | `ghcr.io/digitsflow/php-src:8.4`<br>`ghcr.io/digitsflow/php-src:v8.4.28-df.1` | `x86_64`, `aarch64` |
| **PHP 8.3** | `digitsflow-8.3` | [`v8.3.37-df.1`](https://github.com/digitsflow/php-src/releases/tag/v8.3.37-df.1) | `ghcr.io/digitsflow/php-src:8.3`<br>`ghcr.io/digitsflow/php-src:v8.3.37-df.1` | `x86_64`, `aarch64` |
| **PHP 8.2** | `digitsflow-8.2` | [`v8.2.36-df.1`](https://github.com/digitsflow/php-src/releases/tag/v8.2.36-df.1) | `ghcr.io/digitsflow/php-src:8.2`<br>`ghcr.io/digitsflow/php-src:v8.2.36-df.1` | `x86_64`, `aarch64` |
| **PHP 8.1** | `digitsflow-8.1` | [`v8.1.34-df.17`](https://github.com/digitsflow/php-src/releases/tag/v8.1.34-df.17) | `ghcr.io/digitsflow/php-src:8.1`<br>`ghcr.io/digitsflow/php-src:v8.1.34-df.17` | `x86_64`, `aarch64` |
| **PHP 8.0** | `digitsflow-8.0` | [`v8.0.30-df.1`](https://github.com/digitsflow/php-src/releases/tag/v8.0.30-df.1) | `ghcr.io/digitsflow/php-src:8.0`<br>`ghcr.io/digitsflow/php-src:v8.0.30-df.1` | `x86_64`, `aarch64` |

### Extended LTS PHP 7.x Series
| PHP Series | Branch | Latest Release Tag | Docker Image (`ghcr.io`) | Architecture |
| :--- | :--- | :--- | :--- | :--- |
| **PHP 7.4** | `digitsflow-7.4` | [`v7.4.33-df.1`](https://github.com/digitsflow/php-src/releases/tag/v7.4.33-df.1) | `ghcr.io/digitsflow/php-src:7.4`<br>`ghcr.io/digitsflow/php-src:v7.4.33-df.1` | `x86_64`, `aarch64` |
| **PHP 7.3** | `digitsflow-7.3` | [`v7.3.33-df.1`](https://github.com/digitsflow/php-src/releases/tag/v7.3.33-df.1) | `ghcr.io/digitsflow/php-src:7.3`<br>`ghcr.io/digitsflow/php-src:v7.3.33-df.1` | `x86_64`, `aarch64` |
| **PHP 7.2** | `digitsflow-7.2` | [`v7.2.34-df.1`](https://github.com/digitsflow/php-src/releases/tag/v7.2.34-df.1) | `ghcr.io/digitsflow/php-src:7.2`<br>`ghcr.io/digitsflow/php-src:v7.2.34-df.1` | `x86_64`, `aarch64` |
| **PHP 7.1** | `digitsflow-7.1` | [`v7.1.33-df.1`](https://github.com/digitsflow/php-src/releases/tag/v7.1.33-df.1) | `ghcr.io/digitsflow/php-src:7.1`<br>`ghcr.io/digitsflow/php-src:v7.1.33-df.1` | `x86_64`, `aarch64` |
| **PHP 7.0** | `digitsflow-7.0` | [`v7.0.33-df.1`](https://github.com/digitsflow/php-src/releases/tag/v7.0.33-df.1) | `ghcr.io/digitsflow/php-src:7.0`<br>`ghcr.io/digitsflow/php-src:v7.0.33-df.1` | `x86_64`, `aarch64` |

### Extended LTS PHP 5.x Series
| PHP Series | Branch | Latest Release Tag | Docker Image (`ghcr.io`) | Architecture |
| :--- | :--- | :--- | :--- | :--- |
| **PHP 5.6** | `digitsflow-5.6` | [`v5.6.40-df.1`](https://github.com/digitsflow/php-src/releases/tag/v5.6.40-df.1) | `ghcr.io/digitsflow/php-src:5.6`<br>`ghcr.io/digitsflow/php-src:v5.6.40-df.1` | `x86_64`, `aarch64` |
| **PHP 5.5** | `digitsflow-5.5` | [`v5.5.38-df.1`](https://github.com/digitsflow/php-src/releases/tag/v5.5.38-df.1) | `ghcr.io/digitsflow/php-src:5.5`<br>`ghcr.io/digitsflow/php-src:v5.5.38-df.1` | `x86_64`, `aarch64` |
| **PHP 5.4** | `digitsflow-5.4` | [`v5.4.45-df.1`](https://github.com/digitsflow/php-src/releases/tag/v5.4.45-df.1) | `ghcr.io/digitsflow/php-src:5.4`<br>`ghcr.io/digitsflow/php-src:v5.4.45-df.1` | `x86_64`, `aarch64` |
| **PHP 5.3** | `digitsflow-5.3` | [`v5.3.29-df.1`](https://github.com/digitsflow/php-src/releases/tag/v5.3.29-df.1) | `ghcr.io/digitsflow/php-src:5.3`<br>`ghcr.io/digitsflow/php-src:v5.3.29-df.1` | `x86_64`, `aarch64` |
| **PHP 5.2** | `digitsflow-5.2` | [`v5.2.17-df.1`](https://github.com/digitsflow/php-src/releases/tag/v5.2.17-df.1) | `ghcr.io/digitsflow/php-src:5.2`<br>`ghcr.io/digitsflow/php-src:v5.2.17-df.1` | `x86_64`, `aarch64` |
| **PHP 5.1** | `digitsflow-5.1` | [`v5.1.6-df.1`](https://github.com/digitsflow/php-src/releases/tag/v5.1.6-df.1) | `ghcr.io/digitsflow/php-src:5.1`<br>`ghcr.io/digitsflow/php-src:v5.1.6-df.1` | `x86_64`, `aarch64` |
| **PHP 5.0** | `digitsflow-5.0` | [`v5.0.5-df.1`](https://github.com/digitsflow/php-src/releases/tag/v5.0.5-df.1) | `ghcr.io/digitsflow/php-src:5.0`<br>`ghcr.io/digitsflow/php-src:v5.0.5-df.1` | `x86_64`, `aarch64` |

### Extended Heritage LTS PHP 4.x / 3.0 / 2.0 / 1.0 Series
| PHP Series | Branch | Latest Release Tag | Docker Image (`ghcr.io`) | Architecture |
| :--- | :--- | :--- | :--- | :--- |
| **PHP 4.4** | `digitsflow-4.4` | [`v4.4.9-df.1`](https://github.com/digitsflow/php-src/releases/tag/v4.4.9-df.1) | `ghcr.io/digitsflow/php-src:4.4`<br>`ghcr.io/digitsflow/php-src:v4.4.9-df.1` | `x86_64`, `aarch64` |
| **PHP 4.3** | `digitsflow-4.3` | [`v4.3.11-df.1`](https://github.com/digitsflow/php-src/releases/tag/v4.3.11-df.1) | `ghcr.io/digitsflow/php-src:4.3`<br>`ghcr.io/digitsflow/php-src:v4.3.11-df.1` | `x86_64`, `aarch64` |
| **PHP 4.2** | `digitsflow-4.2` | [`v4.2.3-df.1`](https://github.com/digitsflow/php-src/releases/tag/v4.2.3-df.1) | `ghcr.io/digitsflow/php-src:4.2`<br>`ghcr.io/digitsflow/php-src:v4.2.3-df.1` | `x86_64`, `aarch64` |
| **PHP 4.1** | `digitsflow-4.1` | [`v4.1.2-df.1`](https://github.com/digitsflow/php-src/releases/tag/v4.1.2-df.1) | `ghcr.io/digitsflow/php-src:4.1`<br>`ghcr.io/digitsflow/php-src:v4.1.2-df.1` | `x86_64`, `aarch64` |
| **PHP 4.0** | `digitsflow-4.0` | [`v4.0.6-df.1`](https://github.com/digitsflow/php-src/releases/tag/v4.0.6-df.1) | `ghcr.io/digitsflow/php-src:4.0`<br>`ghcr.io/digitsflow/php-src:v4.0.6-df.1` | `x86_64`, `aarch64` |
| **PHP 3.0** | `digitsflow-3.0` | [`v3.0.18-df.1`](https://github.com/digitsflow/php-src/releases/tag/v3.0.18-df.1) | `ghcr.io/digitsflow/php-src:3.0`<br>`ghcr.io/digitsflow/php-src:v3.0.18-df.1` | `x86_64`, `aarch64` |
| **PHP 2.0** | `digitsflow-2.0` | [`v2.0.1-df.1`](https://github.com/digitsflow/php-src/releases/tag/v2.0.1-df.1) | `ghcr.io/digitsflow/php-src:2.0`<br>`ghcr.io/digitsflow/php-src:v2.0.1-df.1` | `x86_64`, `aarch64` |
| **PHP 1.0** | `digitsflow-1.0` | [`v1.0.0-df.1`](https://github.com/digitsflow/php-src/releases/tag/v1.0.0-df.1) | `ghcr.io/digitsflow/php-src:1.0`<br>`ghcr.io/digitsflow/php-src:v1.0.0-df.1` | `x86_64`, `aarch64` |

---

## 🚀 Installation Guide

### Method 1: Automated One-Line Install (Recommended for Linux `x86_64` & `aarch64`)

Use the automated installer to download and unpack the precompiled Digitsflow release (`x86_64` or `aarch64`, auto-detected via `uname -m`) directly into `/opt/digitsflow/php`:

```bash
# Install latest PHP 8.4 (Default) on x86_64 or aarch64
curl -fsSL https://raw.githubusercontent.com/digitsflow/php-src/master/packaging/scripts/install.sh | sudo bash
```

#### Installing Any PHP Version (1.0 – 8.6)
You can pass `PHP_VER` (`8.6`, `8.5`, `8.4`, `8.3`, `8.2`, `8.1`, `8.0`, `7.4`..`7.0`, `5.6`..`5.0`, `4.4`..`4.0`, `3.0`, `2.0`, `1.0`), `--version=<series>`, or an exact `VERSION` tag:

```bash
# Install PHP 8.5, 7.4, 5.6, or 1.0 (auto-detects x86_64 or aarch64)
curl -fsSL https://raw.githubusercontent.com/digitsflow/php-src/master/packaging/scripts/install.sh | sudo PHP_VER=8.5 bash
curl -fsSL https://raw.githubusercontent.com/digitsflow/php-src/master/packaging/scripts/install.sh | sudo PHP_VER=7.4 bash
curl -fsSL https://raw.githubusercontent.com/digitsflow/php-src/master/packaging/scripts/install.sh | sudo PHP_VER=5.6 bash
curl -fsSL https://raw.githubusercontent.com/digitsflow/php-src/master/packaging/scripts/install.sh | sudo PHP_VER=1.0 bash

# Install PHP 8.2 with default /usr/local/bin/php symlink replacement
curl -fsSL https://raw.githubusercontent.com/digitsflow/php-src/master/packaging/scripts/install.sh | sudo PHP_VER=8.2 SYMLINK_PHP=1 bash

# Install an exact release tag
curl -fsSL https://raw.githubusercontent.com/digitsflow/php-src/master/packaging/scripts/install.sh | sudo VERSION=v8.4.28-df.1 bash
```

---

### Method 2: Manual Download from GitHub Releases

Self-contained distribution archives for both `x86_64` and `aarch64` are published with every tag on the [Releases](https://github.com/digitsflow/php-src/releases) page.

```bash
# 1. Set desired release version and detect architecture (x86_64 or aarch64)
VERSION="v8.4.28-df.1"
ARCH="$(uname -m)" # x86_64 or aarch64

# 2. Download prebuilt tarball
curl -LO "https://github.com/digitsflow/php-src/releases/download/${VERSION}/php-digitsflow-${VERSION}-linux-${ARCH}.tar.gz"

# 3. Extract to root filesystem (installs to /opt/digitsflow/php and /etc/digitsflow/php)
sudo tar -xzf "php-digitsflow-${VERSION}-linux-${ARCH}.tar.gz" -C /

# 4. Create symlinks in PATH
sudo ln -sf /opt/digitsflow/php/bin/php-digitsflow /usr/local/bin/php-digitsflow
sudo ln -sf /opt/digitsflow/php/sbin/php-fpm-digitsflow /usr/local/bin/php-fpm-digitsflow

# 5. Verify installation
php-digitsflow -v
```

---

### Method 3: Docker & Container Deployment

Hardened container images for every version (`8.6` down to `1.0`) are published to the **GitHub Container Registry** (`ghcr.io`).

#### Pull and Run via CLI

```bash
# Run PHP CLI (8.4, 7.4, 5.6, 1.0, etc.)
docker run --rm ghcr.io/digitsflow/php-src:8.4 php -v
docker run --rm ghcr.io/digitsflow/php-src:7.4 php -v
docker run --rm ghcr.io/digitsflow/php-src:5.6 php -v
docker run --rm ghcr.io/digitsflow/php-src:1.0 php -v

# Interactive container shell
docker run --rm -it ghcr.io/digitsflow/php-src:8.4 bash
```

#### Production `Dockerfile`

```dockerfile
FROM ghcr.io/digitsflow/php-src:8.4

WORKDIR /var/www/html
COPY . /var/www/html

EXPOSE 9000
CMD ["php-fpm-digitsflow", "--nodaemonize", "--fpm-config", "/etc/digitsflow/php/php-fpm.conf"]
```

#### `docker-compose.yml` (PHP-FPM + Nginx)

```yaml
version: "3.8"

services:
  app:
    image: ghcr.io/digitsflow/php-src:8.4
    volumes:
      - ./:/var/www/html
    expose:
      - "9000"

  web:
    image: nginx:alpine
    ports:
      - "80:80"
    volumes:
      - ./:/var/www/html
      - ./nginx.conf:/etc/nginx/conf.d/default.conf:ro
    depends_on:
      - app
```

---

### Method 4: Compiling from Source

You can build Digitsflow PHP directly from source code on Ubuntu, Debian, Fedora, or RHEL for any `digitsflow-<series>` branch:

```bash
# 1. Clone the desired branch (e.g., digitsflow-8.4, digitsflow-7.4, digitsflow-5.6, digitsflow-1.0, master)
git clone -b digitsflow-8.4 https://github.com/digitsflow/php-src.git
cd php-src

# 2. Install build dependencies
# Ubuntu / Debian:
sudo apt-get update && sudo apt-get install -y \
    build-essential autoconf bison re2c pkg-config \
    libxml2-dev libssl-dev libzip-dev libpq-dev \
    libcurl4-openssl-dev libonig-dev libsqlite3-dev zlib1g-dev

# Fedora / RHEL:
sudo dnf install -y gcc gcc-c++ autoconf bison re2c make pkgconfig \
    libxml2-devel openssl-devel libzip-devel libpq-devel \
    libcurl-devel oniguruma-devel sqlite-devel zlib-devel

# 3. Compile and install
./packaging/scripts/build-digitsflow.sh
```

---

## ⚙️ Service Management & Configuration

### Running PHP-FPM with systemd
Digitsflow includes a pre-configured systemd service:

```bash
# Enable and start the service
sudo systemctl daemon-reload
sudo systemctl enable --now digitsflow-php-fpm

# Check service status
sudo systemctl status digitsflow-php-fpm

# Restart or reload service
sudo systemctl restart digitsflow-php-fpm
sudo systemctl reload digitsflow-php-fpm
```

### FastCGI Nginx Configuration
Connect your Nginx virtual host to the Digitsflow FastCGI Unix domain socket:

```nginx
server {
    listen 80;
    server_name example.com;
    root /var/www/html;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_pass unix:/run/digitsflow/php-fpm.sock;
    }
}
```

---

## 📂 File System Layout

Digitsflow PHP keeps binaries and configs isolated to avoid collisions with OS-packaged PHP:

| Path | Description |
| :--- | :--- |
| `/opt/digitsflow/php/bin/php-digitsflow` | PHP CLI binary (`PHP_EXTRA_VERSION="-digitsflow"`) |
| `/opt/digitsflow/php/sbin/php-fpm-digitsflow` | PHP-FPM FastCGI server binary |
| `/etc/digitsflow/php/php.ini` | Main PHP runtime configuration |
| `/etc/digitsflow/php/conf.d/` | Directory for custom extension and setting `.ini` files |
| `/etc/digitsflow/php/php-fpm.conf` | PHP-FPM master configuration |
| `/etc/digitsflow/php/php-fpm.d/www.conf` | PHP-FPM worker pool configuration |
| `/run/digitsflow/php-fpm.sock` | Default FastCGI Unix domain socket |
| `/var/log/digitsflow-php-fpm.log` | PHP-FPM master error log |
| `/etc/systemd/system/digitsflow-php-fpm.service` | systemd service unit |

---

## 🛡️ Security Vulnerabilities Remediated

Digitsflow maintains proactive backports and fixes for critical vulnerabilities across all maintained PHP branches (`1.0` through `8.7-dev`). All patches are verified with dedicated `.phpt` test suites under AddressSanitizer and UndefinedBehaviorSanitizer:

| Reference | Subsystem | Vulnerability Type / Impact | Fixed Releases |
| :--- | :--- | :--- | :--- |
| **GH-23814** | `ext/curl` | Re-entrant callback modification Use-After-Free | 8.4, 8.7-dev |
| **GH-23818** | `ext/xml` | Re-entrant SAX handler modification Use-After-Free | 8.4, 8.7-dev |
| **GH-23516** | `Zend` | `find_frameless_function_offset` out-of-bounds over-read | 8.4, 8.7-dev |
| **GH-21882** | `ext/gettext` | Null pointer dereference crash in `textdomain()` / `bindtextdomain()` | 1.0 – 8.7-dev |
| **GH-21776** | `Zend` | Lazy object heap Use-After-Free in `zend_std_read_property` via `__isset` | 8.4 – 8.7-dev |
| **GH-23807** | `ext/exif` | TIFF IFD signed integer overflow and out-of-bounds heap read | 1.0 – 8.7-dev |
| **GH-23771** | `ext/soap` | HexBin integer overflow & heap memory leak | 1.0 – 8.7-dev |
| **GH-23454** | `Zend` | Optimizer `zend_analyze_calls()` call stack buffer overflow | 1.0 – 8.7-dev |
| **GH-24498** | `ext/mbstring` | Multipart parser heap out-of-bounds over-read | 1.0 – 8.7-dev |
| **GH-24488** | `ext/simplexml`| Garbage collector double-free on SimpleXMLElement properties table | 1.0 – 8.7-dev |
| **GH-22554** | `ext/dom` | Custom XPath callback foreign document node Heap-Use-After-Free | 1.0 – 8.7-dev |
| **GH-24373** | `ext/standard` | Serialization destructor teardown Heap-Use-After-Free & corruption | 1.0 – 8.7-dev |
| **GH-24511** | `ext/opcache` | JIT `ASSIGN_DIM_OP` with `CONCAT` Heap-Use-After-Free | 8.4 – 8.7-dev |
| **GH-24540** | `main/SAPI` | Infinite loop in `sapi_header_op()` for response headers $\ge$ 4 GiB | 1.0 – 8.7-dev |
| **GH-24339** | `ext/ftp` | Remote double-free in `data_accept()` on accept failure | 1.0 – 8.7-dev |
| **GH-24508/9**| `ext/standard`| HTTP stream wrapper memory leaks on empty Location / chunk error | 1.0 – 8.7-dev |
| **GH-17921** | `ext/sockets` | Signed integer overflow on buffer size in `socket_read`/`socket_recv` | 1.0 – 8.7-dev |
| **GH-23334** | `ext/dom` | `php_dom_free_templated_content()` Heap-Use-After-Free | 8.4 – 8.7-dev |
| **GH-24176** | `ext/zlib` | Use-After-Free when error handler removes failing `zlib.inflate` filter | 1.0 – 8.7-dev |
| **CVE-2026-7568** | `ext/standard` | Signed integer overflow of char array offset in `metaphone()` | 1.0 – 8.7-dev |
| **CVE-2026-8190** | `ext/zlib` | Signed integer overflow in `zlib.inflate` buffer length arithmetic (`GH-24189`) | 1.0 – 8.7-dev |
| **CVE-2026-8191** | `ext/phar` | Zip signature verification heap buffer over-read (`GH-24200`) | 1.0 – 8.7-dev |
| **CVE-2026-8192** | `ext/session` | Race condition file descriptor leak (`O_CLOEXEC`) in `ps_files_open()` (`GH-24376`) | 1.0 – 8.7-dev |
| **CVE-2026-8193** | `ext/standard` | Memory leak of `BG(last_http_headers)` on nested HTTP stream callbacks (`GH-24201`) | 8.4 – 8.7-dev |
| **CVE-2026-8194** | `ext/hash` | 64-to-32-bit key length truncation & integer overflow in `mhash_keygen_s2k()` (`GH-23770`/`GH-23951`) | 1.0 – 8.7-dev |
| **CVE-2026-8195** | `main/spprintf`, `ext/spl` | `%pS`/`%S`/`%Z` NULL pointer dereference (`CWE-476`), zero-pad state leak (`CWE-682`), and `SplFileInfo::getLinkTarget()` NULL dereference | 8.1 – 8.7-dev |
| **CVE-2026-8196** | `ext/pdo` | Signed integer overflow and signed-to-unsigned wrap in `PDO::FETCH_BOTH` row pre-allocation (`CWE-190`/`CWE-195`) | 8.1 – 8.7-dev |
| **CVE-2026-8197** | `ext/opcache/jit` | AArch64 `ir_patch` 25-bit `B` instruction mask corruption (`CWE-682`), `dasm_encode` rollback pointer underflow (`CWE-787`/`CWE-823`), and `ir_add_veneer` NULL dereference (`CWE-476`) | 8.0 – 8.7-dev |

---

## 🧪 Testing and Quality Assurance

To execute tests using the Digitsflow binary:

```bash
# Run core test suite with concurrency
/opt/digitsflow/php/bin/php-digitsflow run-tests.php -j$(nproc) -p /opt/digitsflow/php/bin/php-digitsflow
```

---

## 📄 License & Credits

PHP is distributed under the [PHP License v3.01](LICENSE). For full contributor credits, visit [php.net/credits.php](https://www.php.net/credits.php).
Hardening, packaging, and maintenance by **Digitsflow**.
