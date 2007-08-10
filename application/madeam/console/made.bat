@echo.
@echo off

SET app=%0
SET lib=%~dp0

php -q "%lib%made.php" %* -working "%CD%\"

echo.
echo.