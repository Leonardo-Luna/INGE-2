# Acá la idea es poner todo lo necesario (en el orden correcto) para poder levantar la DB sin drama :D

echo ">>> Instalando dependencias <<<";
composer install
echo ">>> Actualizando esquema <<<";
bin/console doctrine:schema:update --force
echo ">>> Cargando fixture de roles <<<";
bin/console doctrine:fixtures:load --group="fixtureRoles" --append
echo ">>> Cargando fixture de gerentes <<<";
bin/console doctrine:fixtures:load --group="fixtureGerentes" --append
