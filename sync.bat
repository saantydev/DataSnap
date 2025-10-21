@echo off
echo Sincronizando archivos (excluyendo vendor)...

rsync -avz --exclude="vendor/" --exclude="node_modules/" --exclude=".git/" --exclude="*.log" --exclude="uploads/" . usuario@hostinger:/ruta/datasnap/

echo Sync completado!