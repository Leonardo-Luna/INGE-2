# Acá la idea es poner todos los comandos útiles que pueden solucioar algún temita de caché o dependencias

echo ">>> Instalando dependencias <<<";
composer install
echo ">>> Actualizando esquema <<<";
bin/console doctrine:schema:update --force
echo ">>> Limpiando caché en entorno DEV <<<";
bin/console cache:clear --env=dev
echo ">>> Limpiando caché en entorno PROD <<<";
bin/console cache:clear --env=prod
