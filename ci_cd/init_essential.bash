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

sudo apt install -y apache2 mariadb-server php php-cli \
  libapache2-mod-php php-mysql php-xml php-curl php-gd \
  php-mbstring php-zip php-intl php-bcmath php-opcache \
  php-fpm openssl curl wget unzip git composer imagemagick \
  php-imagick proftpd-basic php-pear php-dev build-essential autoconf pkg-config


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
sudo chown -R $USER:$USER "$APACHE_PROJECT_DESTINATION"
sudo chmod -R 755 "$APACHE_PROJECT_DESTINATION"

echo "Creating self singed ssl"
sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout /etc/ssl/private/apache-selfsigned.key -out /etc/ssl/certs/apache-selfsigned.crt -subj "/C=VN/ST=HoChiMinh/L=District1/O=ExampleCompany/OU=IT/CN=nguyenvominhduy.vn"

OUTPUT_FILE="/etc/apache2/sites-available/$PROJECT_NAME.conf"

echo "Creating config file"
sudo tee "$OUTPUT_FILE" > /dev/null <<EOF
<VirtualHost *:80>
    ServerAdmin ubuntu@coursepro1
    ServerName ubuntu
    # Redirect / https://nguyenvominhduy.vn/
    ServerAlias www.nguyenvominhduy.vn
    DocumentRoot /var/www/$PROJECT_NAME
    ErrorLog /var/log/apache2/error.log
    CustomLog /var/log/apache2/access.log combined
</VirtualHost>

<VirtualHost *:443>
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

sudo mysql << 'EOF'
ALTER USER 'root'@'localhost'
  IDENTIFIED VIA mysql_native_password
  USING PASSWORD('30112004');
FLUSH PRIVILEGES;
EOF


elapsed=$SECONDS
echo "Elapsed: ${elapsed}s"