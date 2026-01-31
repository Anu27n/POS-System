@echo off
REM ============================================
REM POS System - Create Deployment ZIP
REM ============================================
REM This script creates a complete ZIP file for 
REM clients WITHOUT CLI access - includes vendor
REM ============================================

echo ============================================
echo   POS System - Create Deployment Package
echo   (Full package with vendor - No CLI needed)
echo ============================================
echo.

REM Set the output filename with timestamp
for /f "tokens=2 delims==" %%I in ('wmic os get localdatetime /format:list') do set datetime=%%I
set ZIPNAME=pos-system-full-%datetime:~0,8%.zip

REM Delete old zip if exists
if exist "%ZIPNAME%" del "%ZIPNAME%"

echo [1/6] Cleaning up temporary files...

REM Create a temporary directory for clean files
if exist "deploy_temp" rmdir /s /q "deploy_temp"
mkdir deploy_temp

echo [2/6] Copying all project files INCLUDING vendor...
echo       (This may take a minute)

REM Copy all files INCLUDING vendor - exclude only dev stuff
robocopy . deploy_temp /E /XD ".git" "node_modules" ".vscode" ".idea" ".fleet" ".nova" ".zed" ".phpunit.cache" "deploy_temp" "tests" /XF ".env" ".env.backup" ".env.production" ".env.local" "*.log" ".gitattributes" "phpunit.xml" "*.zip" "create-deployment-zip.bat" ".phpactor.json" ".phpunit.result.cache" "auth.json" "Homestead.json" "Homestead.yaml" >nul

echo [3/6] Removing installation markers and sensitive files...

REM Remove installed marker so installer will run
if exist "deploy_temp\storage\installed" del "deploy_temp\storage\installed"
if exist "deploy_temp\storage\license_key" del "deploy_temp\storage\license_key"

REM Remove any cached bootstrap files (these get regenerated)
if exist "deploy_temp\bootstrap\cache\config.php" del "deploy_temp\bootstrap\cache\config.php"
if exist "deploy_temp\bootstrap\cache\routes-v7.php" del "deploy_temp\bootstrap\cache\routes-v7.php"
if exist "deploy_temp\bootstrap\cache\events.php" del "deploy_temp\bootstrap\cache\events.php"

echo [4/6] Clearing cache, sessions and compiled views...

REM Clear ALL session files (keep directory and .gitignore)
if exist "deploy_temp\storage\framework\sessions" (
    for %%f in (deploy_temp\storage\framework\sessions\*) do (
        if /I not "%%~nxf"==".gitignore" del "%%f" 2>nul
    )
)

REM Clear ALL compiled view files (keep directory and .gitignore)
if exist "deploy_temp\storage\framework\views" (
    for %%f in (deploy_temp\storage\framework\views\*) do (
        if /I not "%%~nxf"==".gitignore" del "%%f" 2>nul
    )
)

REM Clear cache data directory
if exist "deploy_temp\storage\framework\cache\data" rmdir /s /q "deploy_temp\storage\framework\cache\data"

REM Clear log files
if exist "deploy_temp\storage\logs" (
    for %%f in (deploy_temp\storage\logs\*.log) do del "%%f" 2>nul
)

echo [5/6] Creating proper directory structure...

REM Create necessary directories with .gitkeep files
if not exist "deploy_temp\storage\framework\cache" mkdir "deploy_temp\storage\framework\cache"
if not exist "deploy_temp\storage\framework\sessions" mkdir "deploy_temp\storage\framework\sessions"
if not exist "deploy_temp\storage\framework\views" mkdir "deploy_temp\storage\framework\views"
if not exist "deploy_temp\storage\framework\testing" mkdir "deploy_temp\storage\framework\testing"

REM Create .gitkeep files to preserve directory structure
echo. > "deploy_temp\storage\framework\cache\.gitkeep"
echo. > "deploy_temp\storage\framework\sessions\.gitkeep"
echo. > "deploy_temp\storage\framework\views\.gitkeep"
echo. > "deploy_temp\storage\framework\testing\.gitkeep"

REM storage/app directories
if not exist "deploy_temp\storage\app\public" mkdir "deploy_temp\storage\app\public"
if not exist "deploy_temp\storage\app\private" mkdir "deploy_temp\storage\app\private"
echo. > "deploy_temp\storage\app\.gitkeep"
echo. > "deploy_temp\storage\app\public\.gitkeep"
echo. > "deploy_temp\storage\app\private\.gitkeep"

REM storage/logs directory
if not exist "deploy_temp\storage\logs" mkdir "deploy_temp\storage\logs"
echo. > "deploy_temp\storage\logs\.gitkeep"

REM bootstrap/cache directory
if not exist "deploy_temp\bootstrap\cache" mkdir "deploy_temp\bootstrap\cache"
echo. > "deploy_temp\bootstrap\cache\.gitkeep"

REM Ensure .env.example exists
if not exist "deploy_temp\.env.example" (
    if exist ".env.example" copy ".env.example" "deploy_temp\.env.example" >nul
)

echo [6/6] Creating ZIP file: %ZIPNAME%...
echo       (This may take several minutes due to vendor folder)

REM Create the ZIP file using PowerShell
powershell -Command "Compress-Archive -Path 'deploy_temp\*' -DestinationPath '%ZIPNAME%' -Force"

if %ERRORLEVEL% equ 0 (
    echo.
    echo ============================================
    echo   SUCCESS! Full deployment package created:
    echo   %ZIPNAME%
    echo ============================================
    echo.
    echo WHAT'S INCLUDED:
    echo - All source code and assets
    echo - vendor/ folder ^(NO composer needed!^)
    echo - Clean storage directory structure
    echo - .env.example for configuration reference
    echo.
    echo WHAT'S EXCLUDED:
    echo - .env file ^(client configures via installer^)
    echo - storage/installed ^(installer will run^)
    echo - storage/license_key ^(client enters their own^)
    echo - Cached sessions, views, and data
    echo.
    echo ============================================
    echo   INSTRUCTIONS FOR CLIENT ^(NO CLI NEEDED^):
    echo ============================================
    echo 1. Extract ZIP to hosting directory
    echo 2. Set folder permissions via cPanel:
    echo    storage/ and bootstrap/cache/ to 755 or 775
    echo 3. Visit domain to run the installer
    echo 4. Follow the installation wizard
    echo.
    echo NO COMPOSER OR CLI ACCESS REQUIRED!
    echo ============================================
) else (
    echo.
    echo [ERROR] Failed to create ZIP file
    echo Make sure PowerShell is available
)

REM Cleanup temporary directory
rmdir /s /q "deploy_temp"

echo.
pause
