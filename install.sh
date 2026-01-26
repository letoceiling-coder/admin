#!/bin/bash

# Скрипт для первоначальной установки проекта на сервере
# Использование: ./install.sh [опции]
# Опции:
#   --git-url=URL     - URL git репозитория (если нужно клонировать)
#   --branch=BRANCH   - Ветка для клонирования (по умолчанию: main)
#   --skip-git        - Пропустить клонирование (если проект уже клонирован)
#   --skip-build      - Пропустить сборку фронтенда
#   --with-seed       - Выполнить seeders
#   --no-interaction  - Неинтерактивный режим

set -e  # Прерывать выполнение при ошибке

# Цвета для вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Переменные по умолчанию
GIT_URL=""
BRANCH="main"
SKIP_GIT=false
SKIP_BUILD=false
WITH_SEED=false
NO_INTERACTION=false
PROJECT_DIR=$(pwd)

# Парсинг аргументов
for arg in "$@"; do
    case $arg in
        --git-url=*)
            GIT_URL="${arg#*=}"
            shift
            ;;
        --branch=*)
            BRANCH="${arg#*=}"
            shift
            ;;
        --skip-git)
            SKIP_GIT=true
            shift
            ;;
        --skip-build)
            SKIP_BUILD=true
            shift
            ;;
        --with-seed)
            WITH_SEED=true
            shift
            ;;
        --no-interaction)
            NO_INTERACTION=true
            shift
            ;;
        *)
            echo -e "${RED}Неизвестный аргумент: $arg${NC}"
            exit 1
            ;;
    esac
done

# Функция для вывода сообщений
info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

success() {
    echo -e "${GREEN}✅ $1${NC}"
}

warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

error() {
    echo -e "${RED}❌ $1${NC}"
}

# Функция для проверки наличия команды
check_command() {
    if ! command -v $1 &> /dev/null; then
        error "$1 не установлен. Установите $1 и повторите попытку."
        exit 1
    fi
}

# Функция для подтверждения
confirm() {
    if [ "$NO_INTERACTION" = true ]; then
        return 0
    fi
    read -p "$1 (y/n): " -n 1 -r
    echo
    [[ $REPLY =~ ^[Yy]$ ]]
}

# Проверка требований
info "Проверка требований..."
check_command php
check_command git

# Проверка версии PHP
PHP_VERSION=$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')
info "PHP версия: $PHP_VERSION"
if [ "$(printf '%s\n' "8.1" "$PHP_VERSION" | sort -V | head -n1)" != "8.1" ]; then
    error "Требуется PHP >= 8.1, установлена версия $PHP_VERSION"
    exit 1
fi

# Проверка Node.js (опционально, если не пропущена сборка)
if [ "$SKIP_BUILD" = false ]; then
    if command -v node &> /dev/null; then
        NODE_VERSION=$(node -v)
        info "Node.js версия: $NODE_VERSION"
    else
        warning "Node.js не установлен. Сборка фронтенда будет пропущена."
        SKIP_BUILD=true
    fi
fi

success "Все требования выполнены"
echo

# Шаг 1: Клонирование проекта (если нужно)
if [ "$SKIP_GIT" = false ] && [ -n "$GIT_URL" ]; then
    info "Шаг 1: Клонирование проекта из git..."
    
    if [ -d ".git" ]; then
        warning "Проект уже является git репозиторием. Пропускаем клонирование."
    else
        if [ -z "$GIT_URL" ]; then
            error "Не указан URL git репозитория. Используйте --git-url=URL"
            exit 1
        fi
        
        info "Клонирование из $GIT_URL (ветка: $BRANCH)..."
        git clone -b "$BRANCH" "$GIT_URL" .
        success "Проект успешно клонирован"
    fi
    echo
elif [ "$SKIP_GIT" = true ]; then
    info "Шаг 1: Пропуск клонирования (--skip-git)"
    echo
else
    info "Шаг 1: Проверка git репозитория..."
    if [ -d ".git" ]; then
        success "Git репозиторий найден"
    else
        warning "Git репозиторий не найден. Продолжаем установку..."
    fi
    echo
fi

# Шаг 2: Установка Composer
info "Шаг 2: Проверка и установка Composer..."

COMPOSER_PATH="$PROJECT_DIR/bin/composer"
BIN_DIR="$PROJECT_DIR/bin"

if [ ! -f "$COMPOSER_PATH" ]; then
    info "Composer не найден в bin/composer, выполняется установка..."
    
    # Создаем директорию bin, если её нет
    mkdir -p "$BIN_DIR"
    
    # Скачиваем composer installer
    info "Скачивание composer installer..."
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    
    # Устанавливаем composer
    info "Установка composer в bin/composer..."
    php composer-setup.php --install-dir="$BIN_DIR" --filename=composer
    
    # Удаляем installer
    rm -f composer-setup.php
    
    # Делаем файл исполняемым
    chmod +x "$COMPOSER_PATH"
    
    success "Composer успешно установлен в bin/composer"
else
    success "Composer найден в bin/composer"
fi

# Проверяем работоспособность composer
if [ -f "$COMPOSER_PATH" ]; then
    COMPOSER_VERSION=$($COMPOSER_PATH --version 2>/dev/null | head -n1 || echo "unknown")
    info "Composer версия: $COMPOSER_VERSION"
fi

echo

# Шаг 3: Установка PHP зависимостей
info "Шаг 3: Установка PHP зависимостей (composer install)..."

if [ -f "$COMPOSER_PATH" ]; then
    php "$COMPOSER_PATH" install --no-dev --optimize-autoloader --no-interaction
else
    # Пробуем глобальный composer
    if command -v composer &> /dev/null; then
        composer install --no-dev --optimize-autoloader --no-interaction
    else
        error "Composer не найден. Установите composer и повторите попытку."
        exit 1
    fi
fi

success "PHP зависимости установлены"
echo

# Шаг 4: Настройка .env
info "Шаг 4: Настройка окружения (.env)..."

if [ ! -f ".env" ]; then
    if [ -f ".env.example" ]; then
        cp .env.example .env
        success ".env файл создан из .env.example"
    else
        warning ".env.example не найден. Создайте .env файл вручную."
    fi
else
    info ".env файл уже существует"
    
    if ! confirm "Перезаписать существующий .env файл?"; then
        info "Пропускаем настройку .env"
    else
        if [ -f ".env.example" ]; then
            cp .env.example .env
            success ".env файл обновлен из .env.example"
        fi
    fi
fi

# Генерация APP_KEY, если его нет
if ! grep -q "APP_KEY=base64:" .env 2>/dev/null; then
    info "Генерация APP_KEY..."
    php artisan key:generate --force
    success "APP_KEY сгенерирован"
else
    info "APP_KEY уже настроен"
fi

echo

# Шаг 5: Настройка базы данных
info "Шаг 5: Настройка базы данных..."

if ! confirm "Настроить подключение к базе данных сейчас? (можно пропустить и настроить вручную в .env)"; then
    warning "Настройка БД пропущена. Настройте DB_* переменные в .env вручную."
else
    read -p "DB_HOST [127.0.0.1]: " DB_HOST
    DB_HOST=${DB_HOST:-127.0.0.1}
    
    read -p "DB_PORT [3306]: " DB_PORT
    DB_PORT=${DB_PORT:-3306}
    
    read -p "DB_DATABASE: " DB_DATABASE
    if [ -z "$DB_DATABASE" ]; then
        warning "Имя базы данных не указано. Пропускаем настройку БД."
    else
        read -p "DB_USERNAME: " DB_USERNAME
        read -sp "DB_PASSWORD: " DB_PASSWORD
        echo
        
        # Обновляем .env
        if [ -f ".env" ]; then
            sed -i.bak "s/DB_HOST=.*/DB_HOST=$DB_HOST/" .env
            sed -i.bak "s/DB_PORT=.*/DB_PORT=$DB_PORT/" .env
            sed -i.bak "s/DB_DATABASE=.*/DB_DATABASE=$DB_DATABASE/" .env
            sed -i.bak "s/DB_USERNAME=.*/DB_USERNAME=$DB_USERNAME/" .env
            sed -i.bak "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASSWORD/" .env
            rm -f .env.bak
            
            success "Настройки БД обновлены в .env"
        fi
    fi
fi

echo

# Шаг 6: Выполнение миграций
info "Шаг 6: Выполнение миграций базы данных..."

if confirm "Выполнить миграции базы данных?"; then
    php artisan migrate --force
    success "Миграции выполнены"
else
    warning "Миграции пропущены. Выполните: php artisan migrate"
fi

echo

# Шаг 7: Выполнение seeders (опционально)
if [ "$WITH_SEED" = true ]; then
    info "Шаг 7: Выполнение seeders..."
    
    if confirm "Выполнить seeders базы данных?"; then
        php artisan db:seed --force
        success "Seeders выполнены"
    else
        info "Seeders пропущены"
    fi
    echo
fi

# Шаг 8: Установка npm зависимостей и сборка фронтенда
if [ "$SKIP_BUILD" = false ]; then
    info "Шаг 8: Установка npm зависимостей..."
    
    if [ -f "package.json" ]; then
        if [ ! -d "node_modules" ]; then
            npm install
            success "npm зависимости установлены"
        else
            info "node_modules уже существует. Пропускаем npm install"
        fi
        
        echo
        
        info "Шаг 9: Сборка фронтенда..."
        npm run build
        success "Фронтенд собран"
    else
        warning "package.json не найден. Пропускаем сборку фронтенда."
    fi
    echo
else
    info "Шаг 8: Сборка фронтенда пропущена (--skip-build)"
    echo
fi

# Шаг 9: Настройка прав доступа
info "Шаг 9: Настройка прав доступа..."

# Устанавливаем права для storage и bootstrap/cache
chmod -R 775 storage bootstrap/cache 2>/dev/null || true
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

success "Права доступа настроены"
echo

# Шаг 10: Очистка и оптимизация
info "Шаг 10: Очистка и оптимизация приложения..."

php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Оптимизация
php artisan config:cache
php artisan route:cache
php artisan view:cache

success "Приложение оптимизировано"
echo

# Шаг 11: Создание администратора (опционально)
info "Шаг 11: Создание администратора..."

if confirm "Создать администратора сейчас?"; then
    php artisan user:create
    success "Администратор создан"
else
    info "Создание администратора пропущено. Выполните: php artisan user:create"
fi

echo

# Финальное сообщение
success "=========================================="
success "Установка проекта завершена успешно!"
success "=========================================="
echo

info "Следующие шаги:"
echo "  1. Проверьте настройки в .env файле"
echo "  2. Убедитесь, что база данных настроена правильно"
echo "  3. Проверьте права доступа к storage и bootstrap/cache"
echo "  4. Настройте веб-сервер (Apache/Nginx) для работы с проектом"
echo "  5. Для дальнейших деплоев используйте: php artisan deploy"
echo

info "Полезные команды:"
echo "  - Проверка конфигурации: php artisan config:show"
echo "   - Очистка кешей: php artisan optimize:clear"
echo "   - Создание администратора: php artisan user:create"
echo "   - Деплой: php artisan deploy"
echo

success "Готово к работе! 🚀"
