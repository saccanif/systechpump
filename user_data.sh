#!/bin/bash
yum update -y
yum install -y httpd php git -y

echo "DB_HOST='${db_endpoint}'" > /var/www/html/config.php

systemctl enable httpd
systemctl start httpd

cd /var/www/html
rm -rf /var/www/html/*
git clone https://github.com/saccanif/systechpump.git /var/www/html