#!/bin/bash
#
# Cryonix Panel Installer
# IPTV Management Panel
# Copyright 2026 XProject-Hub
#
# Usage: curl -fsSL https://install.cryonix.io/panel | sudo bash -s -- --license=YOUR_LICENSE_KEY
#

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

VERSION="1.0.0"
INSTALL_DIR="/var/www/cryonix"
LICENSE_KEY=""

while [[ $# -gt 0 ]]; do
    case $1 in
        --license=*) LICENSE_KEY="${1#*=}"; shift ;;
        *) shift ;;
    esac
done

log_info() { echo -e "${CYAN}[INFO]${NC} $1"; }
log_success() { echo -e "${GREEN}[OK]${NC} $1"; }
log_error() { echo -e "${RED}[ERROR]${NC} $1"; }

print_banner() {
    echo -e "${CYAN}"
    echo "   ██████╗██████╗ ██╗   ██╗ ██████╗ ███╗   ██╗██╗██╗  ██╗"
    echo "  ██╔════╝██╔══██╗╚██╗ ██╔╝██╔═══██╗████╗  ██║██║╚██╗██╔╝"
    echo "  ██║     ██████╔╝ ╚████╔╝ ██║   ██║██╔██╗ ██║██║ ╚███╔╝ "
    echo "  ██║     ██╔══██╗  ╚██╔╝  ██║   ██║██║╚██╗██║██║ ██╔██╗ "
    echo "  ╚██████╗██║  ██║   ██║   ╚██████╔╝██║ ╚████║██║██╔╝ ██╗"
    echo "   ╚═════╝╚═╝  ╚═╝   ╚═╝    ╚═════╝ ╚═╝  ╚═══╝╚═╝╚═╝  ╚═╝"
    echo -e "${NC}"
    echo -e "  ${CYAN}IPTV Panel v${VERSION} - Copyright 2026 XProject-Hub${NC}"
    echo ""
}

check_root() {
    if [[ $EUID -ne 0 ]]; then
        log_error "Run as root: sudo bash install.sh"
        exit 1
    fi
}

install_deps() {
    log_info "Installing dependencies..."
    apt-get update -qq
    DEBIAN_FRONTEND=noninteractive apt-get install -y \
        nginx mariadb-server redis-server \
        php8.2-fpm php8.2-mysql php8.2-redis php8.2-curl php8.2-zip \
        php8.2-mbstring php8.2-xml php8.2-bcmath php8.2-gd \
        ffmpeg curl unzip git 2>/dev/null || \
    DEBIAN_FRONTEND=noninteractive apt-get install -y \
        php8.1-fpm php8.1-mysql php8.1-redis php8.1-curl php8.1-zip \
        php8.1-mbstring php8.1-xml php8.1-bcmath php8.1-gd 2>/dev/null
    log_success "Dependencies installed"
}

setup_database() {
    log_info "Setting up database..."
    DB_PASS=$(openssl rand -base64 24 | tr -dc 'a-zA-Z0-9' | head -c 24)
    systemctl start mariadb
    systemctl enable mariadb
    mysql -e "CREATE DATABASE IF NOT EXISTS cryonix_panel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    mysql -e "CREATE USER IF NOT EXISTS 'cryonix'@'localhost' IDENTIFIED BY '${DB_PASS}';"
    mysql -e "GRANT ALL PRIVILEGES ON cryonix_panel.* TO 'cryonix'@'localhost';"
    mysql -e "FLUSH PRIVILEGES;"
    log_success "Database configured"
}

setup_panel() {
    log_info "Setting up Cryonix Panel..."
    mkdir -p ${INSTALL_DIR}
    
    # In production, download from GitHub
    if [[ -d "./public" ]]; then
        cp -r ./* ${INSTALL_DIR}/
    fi
    
    # Create .env
    cat > ${INSTALL_DIR}/.env << EOF
APP_URL=http://$(hostname -I | awk '{print $1}')
APP_DEBUG=false
APP_SECRET=$(openssl rand -base64 32)
DB_HOST=localhost
DB_NAME=cryonix_panel
DB_USER=cryonix
DB_PASS=${DB_PASS}
EOF

    # Import schema
    if [[ -f "${INSTALL_DIR}/database/schema.sql" ]]; then
        mysql cryonix_panel < ${INSTALL_DIR}/database/schema.sql
    fi
    
    # Seed
    if [[ -f "${INSTALL_DIR}/database/seed.php" ]]; then
        cd ${INSTALL_DIR} && php database/seed.php
    fi
    
    chown -R www-data:www-data ${INSTALL_DIR}
    chmod 600 ${INSTALL_DIR}/.env
    log_success "Panel installed"
}

configure_nginx() {
    log_info "Configuring Nginx..."
    PHP_VER=$(php -v | head -1 | awk '{print $2}' | cut -d. -f1,2)
    
    cat > /etc/nginx/sites-available/cryonix << EOF
server {
    listen 80;
    server_name _;
    root ${INSTALL_DIR}/public;
    index index.php;
    
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php${PHP_VER}-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.(ht|git|env) { deny all; }
}
EOF

    ln -sf /etc/nginx/sites-available/cryonix /etc/nginx/sites-enabled/
    rm -f /etc/nginx/sites-enabled/default
    nginx -t && systemctl reload nginx
    log_success "Nginx configured"
}

activate_license() {
    if [[ -n "$LICENSE_KEY" ]]; then
        log_info "Activating license..."
        cd ${INSTALL_DIR}
        php -r "
            require 'core/Database.php';
            require 'core/License.php';
            \$lic = new CryonixPanel\Core\License();
            \$result = \$lic->activate('${LICENSE_KEY}');
            echo \$result['success'] ? 'License activated!' : 'Activation failed: ' . \$result['error'];
        "
    fi
}

print_summary() {
    SERVER_IP=$(hostname -I | awk '{print $1}')
    echo ""
    echo -e "${GREEN}════════════════════════════════════════════${NC}"
    echo -e "${GREEN}  Cryonix Panel Installation Complete!${NC}"
    echo -e "${GREEN}════════════════════════════════════════════${NC}"
    echo ""
    echo -e "  ${CYAN}Panel URL:${NC}  http://${SERVER_IP}/admin"
    echo ""
    echo -e "  ${CYAN}Login:${NC}"
    echo -e "    Username: ${YELLOW}admin${NC}"
    echo -e "    Password: ${YELLOW}Cryonix${NC}"
    echo ""
    echo -e "${YELLOW}  Change password after first login!${NC}"
    echo ""
}

main() {
    print_banner
    check_root
    install_deps
    setup_database
    setup_panel
    configure_nginx
    activate_license
    print_summary
}

main "$@"

