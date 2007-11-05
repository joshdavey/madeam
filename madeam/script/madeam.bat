@echo.
@echo off

title Madeam PHP MVC Framework Console

SET app=%0
SET lib=%~dp0

php "%lib%cli.php" %*