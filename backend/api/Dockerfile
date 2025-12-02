FROM php:8.4-apache

# Dossier de travail
WORKDIR /var/www/html

# Activer mod_rewrite pour que Symfony gère les routes
RUN a2enmod rewrite

# Installer Composer depuis l'image officielle
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# On se met en environnement prod AVANT composer install
ENV APP_ENV=prod

# Copier le code de l'API (build context = ./backend)
COPY api/ /var/www/html/
# Copier les données réseau pour le calcul des trajets
COPY data/ /var/www/data/

# Installer les dépendances PHP (prod)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Config Apache : on force le DocumentRoot vers public/ + AllowOverride All
RUN printf '<VirtualHost *:80>\n\
  DocumentRoot /var/www/html/public\n\
  <Directory /var/www/html/public>\n\
  AllowOverride All\n\
  Require all granted\n\
  </Directory>\n\
  SetEnvIf Authorization \"(.*)\" HTTP_AUTHORIZATION=$1\n\
  ErrorLog /var/log/apache2/error.log\n\
  CustomLog /var/log/apache2/access.log combined\n\
  </VirtualHost>\n' > /etc/apache2/sites-available/000-default.conf
EXPOSE 80

CMD ["apache2-foreground"]
