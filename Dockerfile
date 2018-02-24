FROM php:7


RUN apt-get update -y && apt-get install -y openssl zip unzip git gnupg
RUN curl -sL https://deb.nodesource.com/setup_8.x | bash -
RUN apt-get install -y nodejs

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /app

COPY . /app

RUN composer install
RUN npm install && npm run production

CMD php artisan serve --host=0.0.0.0 --port=8000

EXPOSE 8000