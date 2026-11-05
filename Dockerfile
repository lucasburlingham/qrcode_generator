# Nginx container for static QR generator
FROM nginx:alpine

WORKDIR /var/www/html

# Copy app and server configs
COPY app/nginx.conf /etc/nginx/nginx.conf
COPY app /var/www/html

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=5s --retries=3 \
  CMD wget -qO- http://127.0.0.1/ >/dev/null || exit 1

CMD ["nginx", "-g", "daemon off;"]
