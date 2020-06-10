FROM debian:stretch

ENV DEBIAN_FRONTEND noninteractive


# BASIC INSTALL
# =============

RUN apt-get update && apt-get -y upgrade && apt-get install -y \
    apt-transport-https \
    apt-utils \
    ca-certificates \
    curl \
    git \
    gnupg2 \
    lsb-release \
    netcat \
    wget \
    zip

RUN usermod -s /bin/bash -u 1000 www-data


# PHP INSTALL
# ===========

# Add PHP repository
RUN wget https://packages.sury.org/php/apt.gpg
RUN apt-key add apt.gpg
RUN echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | tee /etc/apt/sources.list.d/php7.list

# Install PHP-FPM
RUN apt update && apt install -y \
    php7.4 \
    php7.4-apcu \
    php7.4-common \
    php7.4-curl \
    php7.4-fpm \
    php7.4-intl \
    php7.4-json \
    php7.4-mbstring \
    php7.4-opcache \
    php7.4-pgsql \
# Uncomment in order to enable xdebug
#    php7.4-xdebug \
    php7.4-xml \
    php7.4-zip

# Configure PHP
COPY fpm-pool.conf /etc/php/7.4/fpm/pool.d/www.conf
RUN touch /var/log/php7.4-fpm.log && chown www-data:www-data /var/log/php7.4-fpm.log
RUN mkdir -p /run/php/ /var/www/.composer/
RUN chown www-data:www-data -R /run/php/ /var/www

# Configure XDebug
# Uncomment in order to enable xdebug
#RUN echo "xdebug.remote_enable=1" >> /etc/php/7.4/fpm/conf.d/20-xdebug.ini && \
#    echo "xdebug.idekey=PHPSTORM" >> /etc/php/7.4/fpm/conf.d/20-xdebug.ini && \
#    echo "xdebug.default_enable=0" >> /etc/php/7.4/fpm/conf.d/20-xdebug.ini && \
#    echo "xdebug.remote_connect_back=0" >> /etc/php/7.4/fpm/conf.d/20-xdebug.ini && \
#    echo "xdebug.remote_autostart=0" >> /etc/php/7.4/fpm/conf.d/20-xdebug.ini && \
#    echo "xdebug.remote_enable=1" >> /etc/php/7.4/fpm/conf.d/20-xdebug.ini && \
#    echo "xdebug.remote_host=docker.for.mac.host.internal" >> /etc/php/7.4/fpm/conf.d/20-xdebug.ini && \
#    echo "xdebug.profiler_enable=0" >> /etc/php/7.4/fpm/conf.d/20-xdebug.ini && \
#    echo "xdebug.profiler_enable_trigger=1" >> /etc/php/7.4/fpm/conf.d/20-xdebug.ini && \
#    echo "xdebug.profiler_output_dir=/var/www/html/var/log" >> /etc/php/7.4/fpm/conf.d/20-xdebug.ini

# Composer
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN chmod +x /usr/local/bin/composer
RUN su www-data -c "composer global require hirak/prestissimo"


# FINISH CONFIGURATION
# ====================

# Clean APT
RUN rm -rf /var/lib/apt/lists/*

# Define Projet configuration
USER www-data
WORKDIR /var/www/gh-archive-keyword

# Launch PHP-FPM
CMD ["/usr/sbin/php-fpm7.4", "-F"]
