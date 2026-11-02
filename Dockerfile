# Usa come base un'immagine con PHP 8.2.12 e il server web Apache.
FROM php:8.2.12-apache

# Installa l'estensione PDO MySQL per collegare PHP al database.
RUN docker-php-ext-install pdo_mysql

# Attiva la configurazione PHP predefinita per l'ambiente di produzione.
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Copia i file del progetto nella cartella da cui Apache serve le pagine web.
COPY . /var/www/html

# Esegue i comandi successivi e il processo del container come utente
# www-data, senza i privilegi di amministratore dell'utente root.
USER www-data
