FROM php:7.1-fpm

LABEL MAINTAINER="Patrick McLain <pat@pmclain.com>"

# Install dependencies
RUN apt-get update \
  && apt-get install -y \
    libfreetype6-dev \ 
    libicu-dev \ 
    libjpeg62-turbo-dev \ 
    libmcrypt-dev \ 
    libpng-dev \ 
    libxslt1-dev \
    openssl \
    sudo \
    cron \
    rsyslog \
    mysql-client \
    git \
    ssmtp \
    unzip \
    procps

# Configure the gd library
RUN docker-php-ext-configure \
  gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/

# Install required PHP extensions

RUN docker-php-ext-install \
  dom \ 
  gd \ 
  intl \ 
  mbstring \ 
  pdo_mysql \ 
  xsl \ 
  zip \ 
  soap \ 
  bcmath \
  mcrypt

RUN pecl install -o -f xdebug

VOLUME /root/.composer/cache
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

ENV PHP_MEMORY_LIMIT 756M
ENV XDEBUG_REMOTE_HOST docker.for.mac.localhost
ENV MAGENTO_ROOT /var/www/magento
ENV MAGENTO_CLEAN_DATABASE 0
ENV XDEBUG_ENABLE false

ADD etc/php-fpm.ini /usr/local/etc/php/conf.d/zz-magento.ini
ADD etc/php-xdebug.ini /usr/local/etc/php/conf.d/zz-xdebug.ini
RUN echo "sendmail_path = /usr/sbin/ssmtp -t" > /usr/local/etc/php/conf.d/zz-sendmail.ini
RUN echo "mailhub=mailcatcher:25\nUseTLS=NO\nFromLineOverride=YES" > /etc/ssmtp/ssmtp.conf

ADD bin/* /usr/local/bin/

RUN ["chmod", "+x", "/usr/local/bin/magento"]
RUN ["chmod", "+x", "/usr/local/bin/magento-installer"]
RUN ["chmod", "+x", "/usr/local/bin/module-installer"]
RUN ["chmod", "+x", "/usr/local/bin/test-unit"]
RUN ["chmod", "+x", "/usr/local/bin/test-integration"]
RUN ["chmod", "+x", "/usr/local/bin/test-acceptance"]

ADD docker-entrypoint.sh /docker-entrypoint.sh

RUN ["chmod", "+x", "/docker-entrypoint.sh"]

ENTRYPOINT ["/docker-entrypoint.sh"]

ADD etc/php-fpm.conf /usr/local/etc/

CMD ["php-fpm", "-F", "-R"]