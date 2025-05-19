# Acá la idea es poner todo lo necesario (en el orden correcto) para poder levantar la DB sin drama :D

bin/console doctrine:fixtures:load --group="fixtureRoles" --append
bin/console doctrine:fixtures:load --group="fixtureGerentes" --append
