FROM dunglas/frankenphp:php8.4

RUN install-php-extensions mysqli pdo_mysql

WORKDIR /app

COPY . /app

RUN composer install --no-dev --optimize-autoloader

EXPOSE 8080

CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile"]