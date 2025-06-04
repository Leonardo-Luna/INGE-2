# Acá la idea es poner todo lo necesario (en el orden correcto) para poder levantar la DB sin drama :D

echo ">>> Instalando dependencias <<<";
composer install
echo ">>> Actualizando esquema <<<";
bin/console doctrine:schema:update --force
echo ">>> Cargando fixture de roles <<<";
bin/console doctrine:fixtures:load --group="fixtureRoles" --append
echo ">>> Cargando fixture de gerentes <<<";
bin/console doctrine:fixtures:load --group="fixtureGerentes" --append
echo ">>> Cargando fixture de sucursales <<<";
bin/console doctrine:fixtures:load --group="fixtureSucursales" --append
echo ">>> Cargando fixture de maquinaria <<<";
bin/console doctrine:fixtures:load --group="fixtureMaquinaria" --append
echo ">>> Cargando fixture de Estados <<<";
bin/console doctrine:fixtures:load --group="fixtureEstados" --append
