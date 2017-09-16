FROM mariadb:5.5
ENV MYSQL_ROOT_PASSWORD changeme
ENV MYSQL_DATABASE fa
ENV MYSQL_USER fa
ENV MYSQL_PASSWORD changeme
COPY fa.sql /docker-entrypoint-initdb.d
