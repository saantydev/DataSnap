@echo off
echo Desplegando DataSnap...

REM Subir archivos excluyendo vendor
rsync -avz --exclude "vendor/" --exclude "node_modules/" --exclude ".git/" --exclude "uploads/*" ./ usuario@servidor:/var/www/datasnap/

REM Ejecutar composer en el servidor
ssh usuario@servidor "cd /var/www/datasnap && composer install --no-dev"

echo Despliegue completado!