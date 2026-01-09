#!/bin/bash
#
# Cryonix Panel - Complete IPTV Panel Installer
# Fresh Ubuntu Install with License Activation
# Copyright 2026 XProject-Hub
#
# Install: curl -fsSL https://raw.githubusercontent.com/XProject-hub/Cryonix-Panel/main/install.sh | sudo bash
#

set +e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

VERSION="1.0.0"
GITHUB_REPO="XProject-hub/Cryonix-Panel"
INSTALL_DIR="/var/www/cryonix-panel"
DB_NAME="cryonix_panel"
DB_USER="cryonix_panel"
LICENSE_KEY=""

# Parse args
while [[ $# -gt 0 ]]; do
    case $1 in
        --license=*) LICENSE_KEY="${1#*=}"; shift ;;
        *) shift ;;
    esac
done

log_info() { echo -e "${CYAN}[INFO]${NC} $1"; }
log_success() { echo -e "${GREEN}[✓]${NC} $1"; }
log_error() { echo -e "${RED}[✗]${NC} $1"; exit 1; }

print_banner() {
    clear
    echo -e "${CYAN}"
    echo "   ██████╗██████╗ ██╗   ██╗ ██████╗ ███╗   ██╗██╗██╗  ██╗"
    echo "  ██╔════╝██╔══██╗╚██╗ ██╔╝██╔═══██╗████╗  ██║██║╚██╗██╔╝"
    echo "  ██║     ██████╔╝ ╚████╔╝ ██║   ██║██╔██╗ ██║██║ ╚███╔╝ "
    echo "  ██║     ██╔══██╗  ╚██╔╝  ██║   ██║██║╚██╗██║██║ ██╔██╗ "
    echo "  ╚██████╗██║  ██║   ██║   ╚██████╔╝██║ ╚████║██║██╔╝ ██╗"
    echo "   ╚═════╝╚═╝  ╚═╝   ╚═╝    ╚═════╝ ╚═╝  ╚═══╝╚═╝╚═╝  ╚═╝"
    echo -e "${NC}"
    echo -e "         ${CYAN}IPTV PANEL v${VERSION}${NC}"
    echo -e "         ${CYAN}Copyright 2026 XProject-Hub${NC}"
    echo ""
}

check_root() {
    [[ $EUID -ne 0 ]] && log_error "Run as root: sudo bash install.sh"
}

detect_os() {
    log_info "Detecting OS..."
    [[ ! -f /etc/os-release ]] && log_error "Cannot detect OS"
    . /etc/os-release
    
    case $ID in
        ubuntu|debian) log_success "Detected: $ID $VERSION_ID" ;;
        *) log_error "Use Ubuntu 18/20/22/24 or Debian 10/11/12" ;;
    esac
}

update_system() {
    log_info "Updating system..."
    export DEBIAN_FRONTEND=noninteractive
    apt-get update -qq
    apt-get upgrade -y -qq
    apt-get autoremove -y -qq
    log_success "System updated"
}

install_deps() {
    log_info "Installing dependencies..."
    export DEBIAN_FRONTEND=noninteractive
    
    apt-get install -y -qq curl wget git unzip software-properties-common ufw htop nano 2>/dev/null
    
    # Nginx
    apt-get install -y -qq nginx
    systemctl enable nginx && systemctl start nginx
    
    # PHP
    add-apt-repository -y ppa:ondrej/php 2>/dev/null || true
    apt-get update -qq
    
    for v in 8.3 8.2 8.1; do
        if apt-cache show php${v}-fpm &>/dev/null; then
            apt-get install -y -qq php${v}-fpm php${v}-mysql php${v}-redis php${v}-curl php${v}-zip php${v}-mbstring php${v}-xml php${v}-bcmath php${v}-gd 2>/dev/null
            echo "$v" > /tmp/php_ver
            break
        fi
    done
    
    # MariaDB
    apt-get install -y -qq mariadb-server
    systemctl enable mariadb && systemctl start mariadb
    
    # Redis
    apt-get install -y -qq redis-server
    systemctl enable redis-server && systemctl start redis-server
    
    # FFmpeg
    apt-get install -y -qq ffmpeg
    
    log_success "Dependencies installed"
}

setup_db() {
    log_info "Setting up database..."
    DB_PASS=$(openssl rand -base64 24 | tr -dc 'a-zA-Z0-9' | head -c 24)
    
    mysql -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    mysql -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
    mysql -e "GRANT ALL ON ${DB_NAME}.* TO '${DB_USER}'@'localhost'; FLUSH PRIVILEGES;"
    
    echo "$DB_PASS" > /tmp/db_pass
    log_success "Database ready"
}

download_panel() {
    log_info "Downloading Cryonix Panel..."
    rm -rf ${INSTALL_DIR}
    git clone --depth 1 https://github.com/${GITHUB_REPO}.git ${INSTALL_DIR}
    rm -rf ${INSTALL_DIR}/.git
    log_success "Downloaded"
}

configure_panel() {
    log_info "Configuring panel..."
    
    DB_PASS=$(cat /tmp/db_pass)
    SERVER_IP=$(hostname -I | awk '{print $1}')
    
    cat > ${INSTALL_DIR}/.env << EOF
APP_URL=http://${SERVER_IP}
APP_DEBUG=false
APP_SECRET=$(openssl rand -base64 48)
DB_HOST=localhost
DB_NAME=${DB_NAME}
DB_USER=${DB_USER}
DB_PASS=${DB_PASS}
GITHUB_REPO=${GITHUB_REPO}
EOF

    chown -R www-data:www-data ${INSTALL_DIR}
    chmod 600 ${INSTALL_DIR}/.env
    
    # Import schema
    [[ -f "${INSTALL_DIR}/database/schema.sql" ]] && mysql ${DB_NAME} < ${INSTALL_DIR}/database/schema.sql
    
    # Seed admin
    [[ -f "${INSTALL_DIR}/database/seed.php" ]] && cd ${INSTALL_DIR} && php database/seed.php
    
    log_success "Configured"
}

configure_nginx() {
    log_info "Configuring Nginx..."
    
    # Get PHP version - multiple sources
    if [[ -f /tmp/php_ver ]]; then
        PHP_VER=$(cat /tmp/php_ver)
    else
        PHP_VER=$(php -v 2>/dev/null | head -1 | grep -oP '\d+\.\d+' | head -1)
        [[ -z "$PHP_VER" ]] && PHP_VER="8.2"
    fi
    
    SERVER_IP=$(hostname -I | awk '{print $1}')
    
    cat > /etc/nginx/sites-available/cryonix-panel << EOF
server {
    listen 80;
    server_name ${SERVER_IP} _;
    root ${INSTALL_DIR}/public;
    index index.php;
    client_max_body_size 100M;
    
    location / { try_files \$uri \$uri/ /index.php?\$query_string; }
    location ~ \.php\$ {
        fastcgi_pass unix:/var/run/php/php${PHP_VER}-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }
    location ~ /\.(ht|git|env) { deny all; }
}
EOF

    ln -sf /etc/nginx/sites-available/cryonix-panel /etc/nginx/sites-enabled/
    rm -f /etc/nginx/sites-enabled/default
    
    # Test and fix if needed
    if nginx -t 2>/dev/null; then
        systemctl reload nginx
        log_success "Nginx ready (PHP ${PHP_VER})"
    else
        # Try to find correct PHP socket
        for v in 8.3 8.2 8.1 8.0; do
            if [[ -S "/var/run/php/php${v}-fpm.sock" ]]; then
                sed -i "s/php${PHP_VER}-fpm/php${v}-fpm/g" /etc/nginx/sites-available/cryonix-panel
                PHP_VER=$v
                break
            fi
        done
        nginx -t && systemctl reload nginx
        log_success "Nginx ready (PHP ${PHP_VER})"
    fi
}

configure_firewall() {
    log_info "Configuring firewall..."
    ufw --force reset
    ufw default deny incoming
    ufw default allow outgoing
    ufw allow ssh
    ufw allow 80/tcp
    ufw allow 443/tcp
    ufw --force enable
    log_success "Firewall ready"
}

activate_license() {
    if [[ -n "$LICENSE_KEY" ]]; then
        log_info "Activating license..."
        cd ${INSTALL_DIR}
        php -r "
        require 'core/Database.php';
        require 'core/License.php';
        \$l = new CryonixPanel\Core\License();
        \$r = \$l->activate('${LICENSE_KEY}');
        echo \$r['success'] ? 'License activated!' : 'Failed: '.(\$r['error']??'unknown');
        " 2>/dev/null || echo "License activation skipped"
    fi
}

print_summary() {
    SERVER_IP=$(hostname -I | awk '{print $1}')
    echo ""
    echo -e "${GREEN}════════════════════════════════════════════════════════════${NC}"
    echo -e "${GREEN}       CRYONIX PANEL INSTALLATION COMPLETE!${NC}"
    echo -e "${GREEN}════════════════════════════════════════════════════════════${NC}"
    echo ""
    echo -e "  ${CYAN}Panel URL:${NC}     http://${SERVER_IP}/admin"
    echo ""
    echo -e "  ${CYAN}Login:${NC}"
    echo -e "      Username:  ${YELLOW}admin${NC}"
    echo -e "      Password:  ${YELLOW}Cryonix${NC}"
    echo ""
    [[ -z "$LICENSE_KEY" ]] && echo -e "  ${YELLOW}⚠ Don't forget to activate your license in admin panel!${NC}"
    echo ""
    echo -e "${GREEN}════════════════════════════════════════════════════════════${NC}"
    
    rm -f /tmp/db_pass /tmp/php_ver
}

main() {
    print_banner
    check_root
    detect_os
    update_system
    install_deps
    setup_db
    download_panel
    configure_panel
    configure_nginx
    configure_firewall
    activate_license
    print_summary
}

main "$@"

