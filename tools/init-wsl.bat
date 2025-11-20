@echo off

:: admin.bat
:: Set __COMPAT_LAYER=RunAsInvoker
:: Start Executable.exe    (admin rights)

:: Set the username
set userName=%USERNAME%

echo WSL Service started.

sc start WSLService

:: Display the username
echo Current username: %userName%
:: passwd for hotmail

:: Use WMIC to get the SID for the specified user
for /f "usebackq tokens=2 delims==" %%a in (
  `wmic useraccount where "name='%userName%'" get sid /value`
) do set "sid=%%a"

:: Display the SID
echo SID for user %userName%: %sid%

netsh wlan show profiles
echo netsh wlan show profile name="ProfileName" key=clear

echo Dump History? (Y/N)
choice /C YN /M "Press Y for Yes, N=default for No." /D N /T 10

if errorlevel 2 goto No
if errorlevel 1 goto Yes

:Yes
echo You chose Yes!

echo C:\Recycle Bin\%sid%
explorer C:\$Recycle.Bin\%sid%\

echo C:\Users\%userName%\Recent\
explorer C:\Users\%userName%\Recent\

echo C:\Users\%userName%\AppData\Local\Temp
explorer C:\Users\%userName%\AppData\Local\Temp

echo C:\Windows\prefetch\
explorer C:\Windows\prefetch\

goto End

:No
echo You chose No!
REM Add your No code here
goto End

:End

:: Path to check
set directory=C:\Users\%username%\Desktop\GodMode.{ED7BA470-8E54-465E-825C-99712043E01C}

:: Check if the directory exists
if exist "%directory%" (
    echo GodMode directory already exists.
) else (
    echo GodMode directory does not exist.
    set /p userinput="Do you want to create GodMode? (Y/N): "

    if /i "%userinput%"=="Y" (
        mkdir "%directory%"
        echo GodMode directory created at %directory%.
    ) else (
        echo GodMode directory not created.
    )
)

rem net user ***EMAIL***@hotmail.com 
rem net user ***EMAIL***@hotmail.com /logonpasswordchg:yes
echo Speak friend and enter.
:: runas /user:***EMAIL*** "dism /Online /Cleanup-Image /CheckHealth"
runas /user:%userName% "dism /Online /Cleanup-Image /CheckHealth"
:: runas /user:Administrator "dism /Online /Cleanup-Image /CheckHealth"

winget upgrade

echo winget upgrade --all? (Y/N)
choice /C YN /M "Press Y for Yes, N=default for No." /D N /T 10

if errorlevel 2 goto NoUpgrade
if errorlevel 1 goto YesUpgrade

:YesUpgrade

winget upgrade --all --include-unknown
goto End

:NoUpgrade

echo You chose No!
REM Add your No code here
goto End

:End

wsl --update
wsl -d Debian -u root /etc/init-wsl
wsl -d Debian -u root

:: NOTICE: Not enabling PHP 8.1 FPM by default.
:: NOTICE: To enable PHP 8.1 FPM in Apache2 do:
:: NOTICE: a2enmod proxy_fcgi setenvif
:: NOTICE: a2enconf php8.1-fpm
:: NOTICE: You are seeing this message because you have apache2 package installed.

# LxssManager restart
# Get-Service LxssManager | Restart-Service