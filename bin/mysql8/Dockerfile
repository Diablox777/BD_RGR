FROM mysql:8

# Добавляем настройки в my.cnf
RUN echo "[mysqld]" >> /etc/mysql/my.cnf
RUN echo "mysql_native_password=ON" >> /etc/mysql/my.cnf
RUN echo "lower_case_table_names=2" >> /etc/mysql/my.cnf