@echo off
setlocal

set "PROJECT_DIR=c:\Users\Neutel\Herd\gestao-app"
set "APP_PORT=9000"

echo ==========================================
echo   GESTAO APP - DEV START
echo ==========================================
echo.

cd /d "%PROJECT_DIR%"
if errorlevel 1 (
  echo [ERRO] Nao foi possivel aceder a "%PROJECT_DIR%".
  pause
  exit /b 1
)

echo [1/8] A parar processos antigos (php/node)...
powershell -NoProfile -ExecutionPolicy Bypass -Command "Get-Process php,node -ErrorAction SilentlyContinue | Stop-Process -Force"

echo [1.1/8] A limpar estado antigo do Vite...
if exist "public\hot" del /f /q "public\hot"

echo [2/8] A garantir .env...
if not exist ".env" (
  copy ".env.example" ".env" >nul
)

echo [3/8] Composer install...
call composer install
if errorlevel 1 goto :error

echo [4/8] NPM install...
call npm install
if errorlevel 1 goto :error

echo [5/8] Key generate...
call php artisan key:generate
if errorlevel 1 goto :error

echo [6/8] Migrate + seed...
call php artisan migrate --seed
if errorlevel 1 goto :error

echo [7/8] A abrir Vite (novo terminal)...
start "Gestao App - Vite" cmd /k "cd /d %PROJECT_DIR% && npm run dev"

echo [8/8] A iniciar Laravel em http://127.0.0.1:%APP_PORT% ...
echo.
echo Login: test@example.com / password
echo.
call php -S 127.0.0.1:%APP_PORT% -t public
goto :eof

:error
echo.
echo [ERRO] O arranque falhou. Verifica as mensagens acima.
pause
exit /b 1
