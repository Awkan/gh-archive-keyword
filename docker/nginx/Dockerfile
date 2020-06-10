FROM ubuntu:18.04

ENV DEBIAN_FRONTEND noninteractive

RUN apt-get update && apt-get -y upgrade && apt-get install -y \
    curl \
    nginx \
    sudo

COPY nginx.conf /etc/nginx/nginx.conf

RUN chown www-data:www-data /var/www
RUN chown -R www-data:www-data /var/lib/nginx
RUN chown -R www-data:www-data /var/log/nginx

USER www-data

CMD ["nginx", "-g", "daemon off;"]
