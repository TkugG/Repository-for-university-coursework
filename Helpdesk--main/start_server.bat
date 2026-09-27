@echo off
title Smart IT Helpdesk Server
cd /d "%~dp0"
echo ===================================================
echo   Smart IT Helpdesk - Starting PHP Server...
echo ===================================================
echo.
echo URL: http://localhost:8000/login
echo (กด Ctrl+C เพื่อหยุดการทำงานของ Server)
echo.
start http://localhost:8000/login
php -S localhost:8000 -t public
pause
