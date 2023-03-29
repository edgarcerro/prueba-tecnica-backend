Guia de despliegue backend

1º Configurar el parámetro DATABASE_URL para asignar la base de datos dentro del archivo .env

2º Ejecutar la migración con el comando `php bin/console doctrine:migrations:migrate`

3º Desplegar el servidor de symfony con el comando `symfony server:start`