# php:8.2-cli é suficiente para php -S (dev). Em produção: php-fpm + nginx.
FROM php:8.2-cli

RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /app
COPY . .
EXPOSE 8080

# -t public      → document root em public/; src/ nunca fica acessível por URL
# public/index.php → router script: força todos os requests a passar pelo Front Controller
# 0.0.0.0        → escuta em todas as interfaces do container (necessário para acesso do host)
CMD ["php", "-S", "0.0.0.0:8080", "-t", "public", "public/index.php"]
