FROM httpd:2-alpine3.21

WORKDIR /usr/local/apache2/htdocs/
COPY ./src/ .
