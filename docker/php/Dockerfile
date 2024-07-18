FROM php:7.4-fpm

# Allow downstream installation of packages
RUN apt-get update

RUN docker-php-ext-install mysqli
RUN docker-php-ext-configure calendar && docker-php-ext-install calendar

# Installs sendmail
# Installs git for stripe integration
RUN apt-get install -q -y ssmtp mailutils nmap git unzip && rm -rf /var/lib/apt/lists/*

# Installs php modules
RUN docker-php-ext-install mysqli pdo pdo_mysql 

# Copy Composer to path for stripe integration
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# set up sendmail config, see http://linux.die.net/man/5/ssmtp.conf for options
RUN echo "hostname=php" > /etc/ssmtp/ssmtp.conf
RUN echo "root=root@php" >> /etc/ssmtp/ssmtp.conf
RUN echo "mailhub=maildev" >> /etc/ssmtp/ssmtp.conf
# The above 'maildev' is the name you used for the link command
# in your docker-compose file or docker link command.
# Docker automatically adds that name in the hosts file
# of the container you're linking MailDev to.

# Set up php sendmail config
RUN echo "sendmail_path=sendmail -i -t" >> /usr/local/etc/php/conf.d/php-sendmail.ini

# Fully qualified domain name configuration for sendmail on localhost.
# Without this sendmail will not work.
# This must match the value for 'hostname' field that you set in ssmtp.conf.
RUN echo "php" >> /etc/hosts