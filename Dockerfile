# PHP + Nginx container for static QR generator + URL shortener
FROM php:8.2-fpm-alpine

RUN apk add --no-cache nginx

WORKDIR /var/www/html

# Copy app and server configs
COPY app/nginx.conf /etc/nginx/nginx.conf
COPY app /var/www/html

# Ensure data folder exists and right perms
RUN mkdir -p /var/www/html/data && chown -R www-data:www-data /var/www/html

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=5s --retries=3 \
  CMD wget -qO- http://127.0.0.1/ >/dev/null || exit 1

CMD ["sh", "-c", "php-fpm -D && nginx -g 'daemon off;' "]
