@echo off

::set code page
@chcp 1251 > nul

if "%1"=="" (
    php -f dbmigrator.php help
) else (
    php -f dbmigrator.php %*
)
