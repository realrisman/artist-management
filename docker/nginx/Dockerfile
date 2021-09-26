FROM nginx:alpine

RUN apk update && apk add \
    openssl \
    bash

COPY ssl/generate-ssl.sh /etc/nginx/generate-ssl.sh
RUN chmod +x /etc/nginx/generate-ssl.sh
RUN cd /etc/nginx && ./generate-ssl.sh

EXPOSE 80
EXPOSE 443