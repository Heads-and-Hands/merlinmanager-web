FROM richarvey/nginx-php-fpm

RUN rm -Rf /etc/nginx/sites-enabled/*
RUN docker-php-ext-configure intl
RUN docker-php-ext-install intl
ADD conf/nginx.conf /etc/nginx/sites-available/nginx.conf
RUN ln -s /etc/nginx/sites-available/nginx.conf /etc/nginx/sites-enabled/nginx.conf
WORKDIR /app
COPY . /app
RUN composer install && php init --env=Development --overwrite=y