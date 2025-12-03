#!/bin/bash
yum update -y
yum install -y httpd php git mariadb -y


# echo "DB_HOST='${db_endpoint}'" > /var/www/html/config.php

rm -rf /var/www/html/

git clone https://github.com/saccanif/systechpump.git /var/www/html

cd /var/www/html

cat > index.php << EOF
<?php
header("Location: ./public/index.php");
?>
EOF

mysql -u ${db_username} -p${db_password} -h ${db_endpoint} systechpump < /var/www/html/database/systechpump.sql

rm ./config/connection.php

cat > ./config/connection.php << EOF
<?php
   function conectarBD(){
        $conexao = mysqli_connect("${db_endpoint}","${db_username}","${db_password}","systechpump") or die ("Erro ao conectar com o Banco!");
        
        mysqli_query($conexao, "SET NAMES 'utf8'");
        mysqli_query($conexao, "SET character_set_connection=utf8");
        mysqli_query($conexao, "SET character_set_client=utf8");
        mysqli_query($conexao, "SET character_set_results=utf8");

        return $conexao;
    }

    $conexao = conectarBD();
?>
EOF

systemctl enable httpd
systemctl start httpd