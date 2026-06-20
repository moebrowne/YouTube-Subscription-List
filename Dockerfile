FROM php:8.5-cli

RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    && docker-php-ext-install curl

WORKDIR /var/www/html

COPY . .

EXPOSE 80

CMD ["php", "-S", "0.0.0.0:80", "-t", "public"]
