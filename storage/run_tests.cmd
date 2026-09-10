@echo off
cd /d c:\laragon\www\e-fasiliti2
set PHP=C:\laragon\bin\php\php-8.4.25-nts-Win32-vs17-x64\php.exe
set EXT=C:\laragon\bin\php\php-8.4.25-nts-Win32-vs17-x64\ext
set GIT=C:\laragon\bin\git\cmd
path %GIT%;%PATH%
"%PHP%" -d extension_dir="%EXT%" -d extension=mbstring -d extension=openssl -d extension=pdo_mysql -d extension=pdo_sqlite -d extension=sqlite3 -d extension=fileinfo -d extension=curl -d extension=gd vendor\phpunit\phpunit\phpunit %* > storage\run_out.txt 2>&1
echo EXIT=%ERRORLEVEL% >> storage\run_out.txt