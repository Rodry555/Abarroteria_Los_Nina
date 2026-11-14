FROM php:8.2-apache

# Solo las extensiones que necesita nuestro stack (sin Composer, sin npm)
RUN docker-php-ext-install pdo pdo_mysql

# Copiar todo el proyecto
COPY . /var/www/html/

# Apuntar el DocumentRoot a /public y habilitar .htaccess (rewrite + seguridad)
RUN a2enmod rewrite && \
    printf '<VirtualHost *:80>\n    DocumentRoot /var/www/html/public\n    <Directory /var/www/html/public>\n        AllowOverride All\n        Require all granted\n    </Directory>\n</VirtualHost>\n' > /etc/apache2/sites-enabled/000-default.conf

# Koyeb/Render usan el puerto que se configure en el servicio; Apache escucha en 80
EXPOSE 80
