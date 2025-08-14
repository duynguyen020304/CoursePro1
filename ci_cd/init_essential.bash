#!/usr/bin/env bash

set -euo pipefail

SECONDS=0

#Updating module

detect_distro() {
  if [[ -r /etc/os-release ]]; then
    . /etc/os-release
    DISTRO_ID="${ID:-}"
    DISTRO_ID_LIKE="${ID_LIKE:-}"
    DISTRO_PRETTY="${PRETTY_NAME:-${NAME:-}}"
    return 0
  fi

  if command -v lsb_release >/dev/null 2>&1; then
    DISTRO_PRETTY="$(lsb_release -ds 2>/dev/null || true)"
    DISTRO_ID="$(lsb_release -is 2>/dev/null || true)"
    DISTRO_ID_LIKE=""
    return 0
  fi

  for f in /etc/lsb-release /etc/*-release /etc/*_version; do
    [[ -r $f ]] || continue
    DISTRO_PRETTY="$(head -n1 "$f")"
    DISTRO_ID=""
    DISTRO_ID_LIKE=""
    return 0
  done

  if [[ -r /etc/issue ]]; then
    DISTRO_PRETTY="$(head -n1 /etc/issue)"
    DISTRO_ID=""
    DISTRO_ID_LIKE=""
    return 0
  fi

  DISTRO_PRETTY="$(uname -sr)"
  DISTRO_ID=""
  DISTRO_ID_LIKE=""
  return 0
}

detect_distro

id_lc="$(printf '%s' "${DISTRO_ID:-}" | tr '[:upper:]' '[:lower:]')"
id_like_lc="$(printf '%s' "${DISTRO_ID_LIKE:-}" | tr '[:upper:]' '[:lower:]')"

if ! printf '%s\n' "$id_lc" "$id_like_lc" | grep -Eiq '(^debian$|^ubuntu$|debian)'; then
  echo "Error: only Debian-based distros supported. Detected: ${DISTRO_PRETTY:-$id_lc}" >&2
  exit 1
fi

echo "Detected debian-based linux distro: ${DISTRO_PRETTY:-$id_lc}"
echo "Starting get root access"

if [[ "${EUID:-}" -ne 0 ]]; then
  if ! sudo -v; then
    echo "Không thể xác thực sudo. Thoát." >&2
    exit 1
  fi

  ( while true; do
      sudo -v
      sleep 60
    done ) &
  SUDO_KEEPALIVE_PID=$!

  trap 'kill "$SUDO_KEEPALIVE_PID" 2>/dev/null || true' EXIT
fi

echo "Updating system"

sudo apt update -y && sudo apt upgrade -y

echo "Install essential packages for deploying apache"

sudo apt install -y \
  apache2 mariadb-server php php-cli \
  libapache2-mod-php php-mysql php-xml php-curl php-gd \
  php-mbstring php-zip php-intl php-bcmath php-opcache \
  php-fpm openssl curl wget unzip git composer imagemagick \
  php-imagick proftpd-basic php-pear php-dev \
  build-essential autoconf pkg-config make \
  libssl-dev zlib1g-dev libbz2-dev libreadline-dev \
  libsqlite3-dev libffi-dev liblzma-dev tk-dev \
  ca-certificates xz-utils \
  libncursesw5-dev libgdbm-dev libnss3-dev libncurses5-dev

##Python module
echo "installing python"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" >/dev/null 2>&1 && pwd)"
PYENV_SCRIPT="$SCRIPT_DIR/pyenv_installer.bash"

if [[ -f "$PYENV_SCRIPT" ]]; then
  if [[ "${EUID:-}" -eq 0 ]]; then
    if [ -n "${SUDO_USER:-}" ]; then
      echo "Running pyenv installer as user: $SUDO_USER"
      sudo -H -u "$SUDO_USER" env SKIP_SHELL_EXEC=1 bash "$PYENV_SCRIPT"
    else
      echo "No SUDO_USER; running pyenv installer as root (not recommended)"
      bash "$PYENV_SCRIPT"
    fi
  else
    bash "$PYENV_SCRIPT"
  fi
else
  echo "pyenv installer not found at $PYENV_SCRIPT"
fi

# Cloning module
echo "Cloning the project from git repo"
REPO_URL="https://github.com/thavananh/CoursePro1.git"
PROJECT_NAME=${REPO_URL##*/}
PROJECT_NAME="${PROJECT_NAME%.git}" 

sudo rm -rf "$PROJECT_NAME"
BRANCH="feature/mysql"
git clone -b "$BRANCH" "$REPO_URL"

APACHE_PROJECT_DESTINATION="/var/www"

echo "Moving project to appropriate destination $APACHE_PROJECT_DESTINATION"

sudo rm -rf "$APACHE_PROJECT_DESTINATION/$PROJECT_NAME"
sudo mv "$PROJECT_NAME" "$APACHE_PROJECT_DESTINATION"
sudo chown -R $USER:$USER "$APACHE_PROJECT_DESTINATION/$PROJECT_NAME"
sudo chmod -R 755 "$APACHE_PROJECT_DESTINATION/$PROJECT_NAME"

echo "Creating self singed ssl"
sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout /etc/ssl/private/apache-selfsigned.key -out /etc/ssl/certs/apache-selfsigned.crt -subj "/C=VN/ST=HoChiMinh/L=District1/O=ExampleCompany/OU=IT/CN=nguyenvominhduy.vn"

OUTPUT_FILE="/etc/apache2/sites-available/$PROJECT_NAME.conf"

echo "Creating config file"
sudo tee "$OUTPUT_FILE" > /dev/null <<EOF
<VirtualHost *:80>
    Alias /$PROJECT_NAME/api/ "/var/www/$PROJECT_NAME/api/"
    <Directory "/var/www/$PROJECT_NAME/api/">
        Require all granted
    </Directory>
    Alias /$PROJECT_NAME "/var/www/$PROJECT_NAME"
    <Directory "/var/www/$PROJECT_NAME">
        DirectoryIndex home.php
        AllowOverride All
        Options FollowSymLinks
        Require all granted
    </Directory>
    ServerAdmin ubuntu@coursepro1
    ServerName ubuntu
    # Redirect / https://nguyenvominhduy.vn/
    ServerAlias www.nguyenvominhduy.vn
    DocumentRoot /var/www/$PROJECT_NAME
    ErrorLog /var/log/apache2/error.log
    CustomLog /var/log/apache2/access.log combined
</VirtualHost>

<VirtualHost *:443>
    Alias /$PROJECT_NAME/api/ "/var/www/$PROJECT_NAME/api/"
    <Directory "/var/www/$PROJECT_NAME/api/">
        Require all granted
    </Directory>
    Alias /$PROJECT_NAME "/var/www/$PROJECT_NAME"
    <Directory "/var/www/$PROJECT_NAME">
        DirectoryIndex home.php
        AllowOverride All
        Options FollowSymLinks
        Require all granted
    </Directory>
    ServerAdmin ubuntu@coursepro1
    ServerName ubuntu
    ServerAlias www.nguyenvominhduy.vn
    DocumentRoot /var/www/$PROJECT_NAME

    ErrorLog /var/log/apache2/error.log
    CustomLog /var/log/apache2/access.log combined

    SSLEngine on
    SSLCertificateFile    /etc/ssl/certs/apache-selfsigned.crt
    SSLCertificateKeyFile /etc/ssl/private/apache-selfsigned.key
</VirtualHost>
EOF

echo "Enabling module ssl and changing config"
sudo a2enmod ssl
sudo systemctl restart apache2
sudo a2ensite $PROJECT_NAME.conf
sudo a2dissite 000-default.conf
sudo apache2ctl configtest
sudo systemctl restart apache2

echo "Configure mysql database"
sudo mysql << 'EOF'
ALTER USER 'root'@'localhost'
  IDENTIFIED VIA mysql_native_password
  USING PASSWORD('30112004');
FLUSH PRIVILEGES;
EOF
export DB_HOST=127.0.0.1
export DB_USER=root
export DB_PASS='30112004'
export DB_NAME=ecourse
export DB_PORT=3306
export DB_CHARSET=utf8mb4

if ! command -v pyenv >/dev/null 2>&1; then
    echo "pyenv is not in PATH. Attempting to reload..."
    export PYENV_ROOT="$HOME/.pyenv"
    export PATH="$PYENV_ROOT/bin:$PATH"
    if command -v pyenv >/dev/null 2>&1; then
      eval "$(pyenv init - bash)"
    else
      echo "pyenv could not be found. Please reopen the terminal or re-run the script." >&2
      exit 1
    fi
fi

# --- Begin: create temporary systemd service for image_crawling_api and start it now ---
# Service will be stopped & unit removed on script exit.

RUN_USER="${SUDO_USER:-$USER}"
# determine home directory of RUN_USER
HOME_DIR="$(getent passwd "$RUN_USER" | cut -d: -f6 2>/dev/null || echo "/home/$RUN_USER")"
PY_VERSION="3.11.13"   # dùng trong PATH; shims được dùng cho ExecStart
SERVICE_NAME="image_crawling_api.service"
SERVICE_PATH="/etc/systemd/system/$SERVICE_NAME"
APP_API_DIR="$APACHE_PROJECT_DESTINATION/$PROJECT_NAME/api"

echo "Creating temporary systemd unit $SERVICE_NAME to run image_crawling_api as $RUN_USER"

sudo tee "$SERVICE_PATH" > /dev/null <<EOF
[Unit]
Description=Image Crawling API (gunicorn)
After=network.target

[Service]
User=$RUN_USER
Group=$RUN_USER
WorkingDirectory=$APP_API_DIR
Environment=PYENV_ROOT=$HOME_DIR/.pyenv
Environment=PATH=$HOME_DIR/.pyenv/shims:$HOME_DIR/.pyenv/versions/$PY_VERSION/bin:/usr/local/bin:/usr/bin
ExecStart=$HOME_DIR/.pyenv/shims/gunicorn -w 2 -k gthread --threads 8 -b 0.0.0.0:5000 image_crawling_api:app
Restart=always

[Install]
WantedBy=multi-user.target
EOF

echo "Prepare python/pip and ensure gunicorn exists in user's pyenv environment (if pyenv present)"
# run under RUN_USER
sudo -H -u "$RUN_USER" bash -lc "set -e
export PYENV_ROOT=\"\$HOME/.pyenv\"
export PATH=\"\$PYENV_ROOT/bin:\$PYENV_ROOT/shims:\$PATH\"
if command -v pyenv >/dev/null 2>&1; then
  eval \"\$(pyenv init -)\"
  # try to ensure requested patch-version exists (skip if already)
  pyenv versions --bare | grep -Fxq \"$PY_VERSION\" || pyenv install --skip-existing \"$PY_VERSION\" || true
  pyenv global \"$PY_VERSION\" || true
fi
cd \"$APP_API_DIR\" || exit 1
python -m pip install --upgrade pip setuptools wheel || true
if [ -f requirements.txt ]; then python -m pip install -r requirements.txt || true; fi
python -m pip install gunicorn || true
"

# reload and start the temporary service (do not enable)
sudo systemctl daemon-reload
sudo systemctl start "$SERVICE_NAME"

if sudo systemctl is-active --quiet "$SERVICE_NAME"; then
  echo "Service $SERVICE_NAME started."
else
  echo "Service $SERVICE_NAME failed to start. Showing last journal lines:" >&2
  sudo journalctl -u "$SERVICE_NAME" --no-pager -n 200 >&2 || true
fi

# cleanup function (stop + remove unit)
cleanup_gunicorn_service() {
  echo "Stopping and removing temporary systemd service $SERVICE_NAME"
  sudo systemctl stop "$SERVICE_NAME" 2>/dev/null || true
  sudo rm -f "$SERVICE_PATH" 2>/dev/null || true
  sudo systemctl daemon-reload 2>/dev/null || true
}

# Extend EXIT trap: preserve sudo keepalive kill and cleanup service
trap 'kill "${SUDO_KEEPALIVE_PID:-}" 2>/dev/null || true; cleanup_gunicorn_service' EXIT
# --- End: systemd service section ---

echo "Creating database, tables, trigger"
php "$APACHE_PROJECT_DESTINATION/$PROJECT_NAME/model/init.php"

echo "Installing all package dependcy for project"
composer install --working-dir="$APACHE_PROJECT_DESTINATION/$PROJECT_NAME"

elapsed=$SECONDS
echo "Elapsed: ${elapsed}s"