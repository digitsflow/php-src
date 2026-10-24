<div align="center">
    <a href="https://github.com/digitsflow/php-src">
        <img
            alt="PHP"
            src="https://www.php.net/images/logos/new-php-logo.svg"
            width="160">
    </a>
    <h1>Digitsflow PHP</h1>
    <p><strong>Security-Hardened, Memory-Safe, Production-Ready PHP Distribution</strong></p>
</div>

[![Security & Sanitizer Quality Gate (ASAN / UBSAN)](https://github.com/digitsflow/php-src/actions/workflows/security-quality-gate.yml/badge.svg)](https://github.com/digitsflow/php-src/actions/workflows/security-quality-gate.yml)
[![Build & Release Digitsflow PHP](https://github.com/digitsflow/php-src/actions/workflows/build-digitsflow.yml/badge.svg)](https://github.com/digitsflow/php-src/actions/workflows/build-digitsflow.yml)
[![License: PHP 3.01](https://img.shields.io/badge/License-PHP%203.01-blue.svg)](LICENSE)

**Digitsflow PHP** is an enterprise-grade distribution of the PHP interpreter maintained by Digitsflow. It delivers proactive security patches, use-after-free (UAF) mitigations, integer overflow protections, and memory leak fixes across active and LTS PHP branches.

Every release is continuously audited and gated against **AddressSanitizer (ASAN)** and **UndefinedBehaviorSanitizer (UBSan)** to guarantee zero memory corruption, zero undefined behaviors, and regression-free stability.

Digitsflow PHP is engineered for **safe side-by-side coexistence**: it installs cleanly into isolated paths (`/opt/digitsflow/php` and `/etc/digitsflow/php`), allowing you to deploy hardened PHP without interfering with OS package managers or default system PHP binaries.

---

## 📋 Release Matrix

| PHP Version | Release Tag | Docker Image (`ghcr.io`) | Architecture |
| :--- | :--- | :--- | :--- |
| **PHP 8.4** *(Default)* | [`v8.4.5-df.15`](https://github.com/digitsflow/php-src/releases/tag/v8.4.5-df.15) | `ghcr.io/digitsflow/php-src:8.4`<br>`ghcr.io/digitsflow/php-src:v8.4.5-df.15` | `x86_64` |
| **PHP 8.3** | [`v8.3.36-df.12`](https://github.com/digitsflow/php-src/releases/tag/v8.3.36-df.12) | `ghcr.io/digitsflow/php-src:8.3`<br>`ghcr.io/digitsflow/php-src:v8.3.36-df.12` | `x86_64` |
| **PHP 8.2** | [`v8.2.35-df.12`](https://github.com/digitsflow/php-src/releases/tag/v8.2.35-df.12) | `ghcr.io/digitsflow/php-src:8.2`<br>`ghcr.io/digitsflow/php-src:v8.2.35-df.12` | `x86_64` |
| **PHP 8.1** | [`v8.1.34-df.12`](https://github.com/digitsflow/php-src/releases/tag/v8.1.34-df.12) | `ghcr.io/digitsflow/php-src:8.1`<br>`ghcr.io/digitsflow/php-src:v8.1.34-df.12` | `x86_64` |
| **PHP 8.7-dev** *(Master)* | [`v8.7.0-dev-df.1`](https://github.com/digitsflow/php-src/releases/tag/v8.7.0-dev-df.1) | `ghcr.io/digitsflow/php-src:latest`<br>`ghcr.io/digitsflow/php-src:master` | `x86_64` |

---

## 🚀 Installation Guide

### Method 1: Automated One-Line Install (Recommended for Linux)

Use the automated installer to download and unpack the precompiled Digitsflow release directly into `/opt/digitsflow/php`:

```bash
# Install latest PHP 8.4 (Default)
curl -fsSL https://raw.githubusercontent.com/digitsflow/php-src/master/packaging/scripts/install.sh | sudo bash
```

#### Installing a Specific Version
You can pass `PHP_VER` (8.4, 8.3, 8.2, 8.1) or an exact `VERSION` tag:

```bash
# Install PHP 8.3
curl -fsSL https://raw.githubusercontent.com/digitsflow/php-src/master/packaging/scripts/install.sh | sudo PHP_VER=8.3 bash

# Install PHP 8.2 with default /usr/local/bin/php symlink replacement
curl -fsSL https://raw.githubusercontent.com/digitsflow/php-src/master/packaging/scripts/install.sh | sudo PHP_VER=8.2 SYMLINK_PHP=1 bash

# Install an exact release tag
curl -fsSL https://raw.githubusercontent.com/digitsflow/php-src/master/packaging/scripts/install.sh | sudo VERSION=v8.4.5-df.15 bash
```

---

### Method 2: Manual Download from GitHub Releases

Self-contained distribution archives are published with every tag on the [Releases](https://github.com/digitsflow/php-src/releases) page.

```bash
# 1. Set desired release version
VERSION="v8.4.5-df.15"

# 2. Download prebuilt tarball
curl -LO "https://github.com/digitsflow/php-src/releases/download/${VERSION}/php-digitsflow-${VERSION}-linux-x86_64.tar.gz"

# 3. Extract to root filesystem (installs to /opt/digitsflow/php and /etc/digitsflow/php)
sudo tar -xzf "php-digitsflow-${VERSION}-linux-x86_64.tar.gz" -C /

# 4. Create symlinks in PATH
sudo ln -sf /opt/digitsflow/php/bin/php-digitsflow /usr/local/bin/php-digitsflow
sudo ln -sf /opt/digitsflow/php/sbin/php-fpm-digitsflow /usr/local/bin/php-fpm-digitsflow

# 5. Verify installation
php-digitsflow -v
```

---

### Method 3: Docker & Container Deployment

Hardened container images are published to the **GitHub Container Registry** (`ghcr.io`).

#### Pull and Run via CLI

```bash
# Run PHP CLI
docker run --rm ghcr.io/digitsflow/php-src:8.4 php -v

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

You can build Digitsflow PHP directly from source code on Ubuntu, Debian, Fedora, or RHEL:

```bash
# 1. Clone the desired branch (digitsflow-8.4, digitsflow-8.3, digitsflow-8.2, digitsflow-8.1, master)
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

Digitsflow maintains proactive backports and fixes for critical vulnerabilities across all active PHP branches. All patches are verified with dedicated `.phpt` test suites under AddressSanitizer and UndefinedBehaviorSanitizer:

| Reference | Subsystem | Vulnerability Type / Impact | Fixed Releases |
| :--- | :--- | :--- | :--- |
| **GH-23814** | `ext/curl` | Re-entrant callback modification Use-After-Free | 8.4, 8.7-dev |
| **GH-23818** | `ext/xml` | Re-entrant SAX handler modification Use-After-Free | 8.4, 8.7-dev |
| **GH-23516** | `Zend` | `find_frameless_function_offset` out-of-bounds over-read | 8.4, 8.7-dev |
| **GH-21882** | `ext/gettext` | Null pointer dereference crash in `textdomain()` / `bindtextdomain()` | 8.1, 8.2, 8.3, 8.4, 8.7-dev |
| **GH-21776** | `Zend` | Lazy object heap Use-After-Free in `zend_std_read_property` via `__isset` | 8.4, 8.7-dev |
| **GH-23807** | `ext/exif` | TIFF IFD signed integer overflow and out-of-bounds heap read | 8.1, 8.2, 8.3, 8.4, 8.7-dev |
| **GH-23771** | `ext/soap` | HexBin integer overflow & heap memory leak | 8.1, 8.2, 8.3, 8.4, 8.7-dev |
| **GH-23454** | `Zend` | Optimizer `zend_analyze_calls()` call stack buffer overflow | 8.1, 8.2, 8.3, 8.4, 8.7-dev |
| **GH-24498** | `ext/mbstring` | Multipart parser heap out-of-bounds over-read | 8.1, 8.2, 8.3, 8.4, 8.7-dev |
| **GH-24488** | `ext/simplexml`| Garbage collector double-free on SimpleXMLElement properties table | 8.1, 8.2, 8.3, 8.4, 8.7-dev |
| **GH-22554** | `ext/dom` | Custom XPath callback foreign document node Heap-Use-After-Free | 8.1, 8.2, 8.3, 8.4, 8.7-dev |
| **GH-24373** | `ext/standard` | Serialization destructor teardown Heap-Use-After-Free & corruption | 8.1, 8.2, 8.3, 8.4, 8.7-dev |
| **GH-24511** | `ext/opcache` | JIT `ASSIGN_DIM_OP` with `CONCAT` Heap-Use-After-Free | 8.4, 8.7-dev |
| **GH-24540** | `main/SAPI` | Infinite loop in `sapi_header_op()` for response headers $\ge$ 4 GiB | 8.1, 8.2, 8.3, 8.4, 8.7-dev |
| **GH-24339** | `ext/ftp` | Remote double-free in `data_accept()` on accept failure | 8.1, 8.2, 8.3, 8.4, 8.7-dev |
| **GH-24508/9**| `ext/standard`| HTTP stream wrapper memory leaks on empty Location / chunk error | 8.1, 8.2, 8.3, 8.4, 8.7-dev |
| **GH-17921** | `ext/sockets` | Signed integer overflow on buffer size in `socket_read`/`socket_recv` | 8.1, 8.2, 8.3, 8.4, 8.7-dev |
| **GH-23334** | `ext/dom` | `php_dom_free_templated_content()` Heap-Use-After-Free | 8.4, 8.7-dev |
| **GH-24176** | `ext/zlib` | Use-After-Free when error handler removes failing `zlib.inflate` filter | 8.1, 8.2, 8.3, 8.4, 8.7-dev |

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
