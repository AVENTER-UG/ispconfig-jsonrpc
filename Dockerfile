FROM alpine
MAINTAINER Andreas Peters <noreply@aventer.biz>

WORKDIR /var/www/html

RUN set -xe && \
    apk add --no-cache ca-certificates \
        gzip \
        nginx \
        openssl \
        libintl \
        php-fpm \
        php-openssl \
        php-pdo_sqlite \
        php-sqlite3 \
        php-xml \
        php-zlib \
		php-session \
  		php-gd \
  		php-curl \
  		php-zip \
  		php-mbstring \
  		php-soap \
        php \
		php-json && \
        apk add --virtual build_deps gettext && \
        cp /usr/bin/envsubst /usr/local/bin/envsubst && \
        apk del build_deps

COPY nginx.conf /etc/nginx/nginx.conf
COPY src/ /var/www/html
COPY src/lib /var/www/html/lib
COPY run.sh /run.sh
COPY php-fpm.conf /etc/php7/php-fpm.conf

RUN rm -rf /var/www/html/callback


EXPOSE 8888

CMD /run.sh
