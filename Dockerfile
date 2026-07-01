FROM dunglas/frankenphp:php8.4

RUN install-php-extensions mysqli pdo_mysql

WORKDIR /app

COPY . /app

EXPOSE 8080

CMD ["php", "-S", "0.0.0.0:8080", "-t", "."]