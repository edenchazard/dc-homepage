FROM nginx:latest
EXPOSE 8080
COPY ./docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY ./src /var/www/html