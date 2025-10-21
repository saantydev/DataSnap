@echo off
echo Configurando servidor de email local...

REM Descargar sendmail para Windows
echo Descarga sendmail desde: https://www.glob.com.au/sendmail/
echo O usa XAMPP que incluye sendmail

REM Configurar php.ini
echo.
echo Agrega estas lineas a tu php.ini:
echo [mail function]
echo SMTP = localhost
echo smtp_port = 25
echo sendmail_from = datasnap71@gmail.com
echo sendmail_path = "C:\xampp\sendmail\sendmail.exe -t"

pause