#!/bin/bash
yum update -y
yum install -y httpd php php-mysqlnd git

systemctl enable httpd
systemctl start httpd

cd /var/www/html
rm -rf *

git clone YOUR_GITHUB_REPOSITORY_URL .

chown -R apache:apache /var/www/html
chmod -R 755 /var/www/html

systemctl restart httpd
