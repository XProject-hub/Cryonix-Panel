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

VERSION="1.1.0"
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
    
    # Drop user if exists and recreate with correct password
    mysql -e "DROP USER IF EXISTS '${DB_USER}'@'localhost';" 2>/dev/null || true
    mysql -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    mysql -e "CREATE USER '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
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
    
    # Generate unique admin path
    ADMIN_PATH="panel_$(openssl rand -hex 4)"
    
    cat > ${INSTALL_DIR}/.env << EOF
APP_URL=http://${SERVER_IP}
APP_DEBUG=false
APP_SECRET=$(openssl rand -base64 48)
DB_HOST=localhost
DB_NAME=${DB_NAME}
DB_USER=${DB_USER}
DB_PASS=${DB_PASS}
ADMIN_PATH=${ADMIN_PATH}
GITHUB_REPO=${GITHUB_REPO}
LICENSE_KEY=${LICENSE_KEY}
EOF

    chown -R www-data:www-data ${INSTALL_DIR}
    chmod 600 ${INSTALL_DIR}/.env
    
    # Import schema using app credentials
    if [[ -f "${INSTALL_DIR}/database/schema.sql" ]]; then
        mysql -u"${DB_USER}" -p"${DB_PASS}" ${DB_NAME} < ${INSTALL_DIR}/database/schema.sql 2>/dev/null || \
        mysql ${DB_NAME} < ${INSTALL_DIR}/database/schema.sql
    fi
    
    # Store admin path in database
    mysql -u"${DB_USER}" -p"${DB_PASS}" ${DB_NAME} -e "INSERT INTO settings (\`key\`, \`value\`, \`type\`) VALUES ('admin_path', '${ADMIN_PATH}', 'string') ON DUPLICATE KEY UPDATE \`value\`='${ADMIN_PATH}';" 2>/dev/null || true
    
    # Seed admin
    if [[ -f "${INSTALL_DIR}/database/seed.php" ]]; then
        cd ${INSTALL_DIR}
        php database/seed.php 2>/dev/null || true
    fi
    
    # Save admin path to file for reference
    echo "${ADMIN_PATH}" > ${INSTALL_DIR}/.admin_path
    chmod 600 ${INSTALL_DIR}/.admin_path
    
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
    
    # Live stream handler
    location ~ ^/live/(.*)$ {
        fastcgi_pass unix:/var/run/php/php${PHP_VER}-fpm.sock;
        fastcgi_param SCRIPT_FILENAME ${INSTALL_DIR}/public/live.php;
        fastcgi_param PATH_INFO \$1;
        include fastcgi_params;
        fastcgi_buffering off;
        fastcgi_request_buffering off;
        proxy_buffering off;
    }
    
    # Get playlist handler
    location = /get.php {
        fastcgi_pass unix:/var/run/php/php${PHP_VER}-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location / { try_files \$uri \$uri/ /index.php?\$query_string; }
    location ~ \.php\$ {
        fastcgi_pass unix:/var/run/php/php${PHP_VER}-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }
    location ~ /\.(ht|git|env|admin_path) { deny all; }
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
        
        DB_PASS=$(cat /tmp/db_pass 2>/dev/null || grep DB_PASS ${INSTALL_DIR}/.env | cut -d'=' -f2)
        SERVER_IP=$(hostname -I | awk '{print $1}')
        
        # Calculate expiry (1 month from now for initial activation)
        EXPIRES_AT=$(date -d "+1 month" '+%Y-%m-%d %H:%M:%S')
        
        # Insert license directly into database
        mysql -u"${DB_USER}" -p"${DB_PASS}" ${DB_NAME} -e "
            DELETE FROM license_info;
            INSERT INTO license_info (license_key, status, max_connections, max_channels, expires_at, last_check_at) 
            VALUES ('${LICENSE_KEY}', 'active', 999999, 999999, '${EXPIRES_AT}', NOW());
        " 2>/dev/null
        
        if [[ $? -eq 0 ]]; then
            log_success "License activated!"
        else
            echo -e "${YELLOW}[!]${NC} License saved to .env - activate in panel settings"
        fi
        
        # Also try Cloud API (non-blocking)
        curl -s -X POST "https://cryonix.io/api/v1/license/activate" \
            -H "Content-Type: application/json" \
            -d "{\"license_key\":\"${LICENSE_KEY}\",\"server_ip\":\"${SERVER_IP}\"}" &>/dev/null &
    else
        echo -e "${YELLOW}[!]${NC} No license provided - activate in panel settings"
    fi
}

print_summary() {
    SERVER_IP=$(hostname -I | awk '{print $1}')
    ADMIN_PATH=$(cat ${INSTALL_DIR}/.admin_path 2>/dev/null || echo "admin")
    
    echo ""
    echo -e "${GREEN}════════════════════════════════════════════════════════════${NC}"
    echo -e "${GREEN}       CRYONIX PANEL INSTALLATION COMPLETE!${NC}"
    echo -e "${GREEN}════════════════════════════════════════════════════════════${NC}"
    echo ""
    echo -e "  ${CYAN}Panel URL:${NC}     http://${SERVER_IP}/${ADMIN_PATH}"
    echo ""
    echo -e "  ${CYAN}Login:${NC}"
    echo -e "      Username:  ${YELLOW}admin${NC}"
    echo -e "      Password:  ${YELLOW}Cryonix${NC}"
    echo ""
    echo -e "  ${YELLOW}⚠ IMPORTANT: Save your admin path!${NC}"
    echo -e "  ${YELLOW}  Your secret admin URL: /${ADMIN_PATH}${NC}"
    echo ""
    [[ -z "$LICENSE_KEY" ]] && echo -e "  ${YELLOW}⚠ Activate your license at: /${ADMIN_PATH}/settings${NC}"
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
