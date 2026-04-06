# PHP + Nginx container for static QR generator
FROM php:8.5-fpm-alpine

RUN apk add --no-cache nginx

WORKDIR /var/www/html

# Copy app and server configs
COPY app/nginx.conf /etc/nginx/nginx.conf
COPY app /var/www/html

# Ensure data folder exists and right perms, use location outside document root
RUN mkdir -p /var/qrdata && chown -R www-data:www-data /var/qrdata /var/www/html

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=5s --retries=3 \
  CMD wget -qO- http://127.0.0.1/ >/dev/null || exit 1

CMD ["sh", "-c", "php-fpm -D && nginx -g 'daemon off;' "]
