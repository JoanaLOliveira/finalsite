FROM php:8.2-apache

# Instala os drivers do MySQL (pdo_mysql e mysqli)
RUN docker-php-ext-install pdo_mysql mysqli

# Copia todos os arquivos do seu projeto para a pasta do servidor Apache
COPY . /var/www/html/

# Habilita o módulo de reescrita de URL do Apache
RUN a2enmod rewrite

EXPOSE 80