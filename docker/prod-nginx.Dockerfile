FROM nginxinc/nginx-unprivileged:stable-alpine
COPY ./docker/prod-nginx.conf /etc/nginx/conf.d/default.conf
COPY ./src /var/www/html