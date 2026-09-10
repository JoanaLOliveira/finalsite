FROM php:8.2-apache

# Copia os arquivos do projeto para o diretório web do Apache
COPY . /var/www/html/

# Habilita o mod_rewrite do Apache (caso use URLs amigáveis)
RUN a2enmod rewrite

# Expoe a porta padrão
EXPOSE 80