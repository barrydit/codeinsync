@echo off
setlocal ENABLEEXTENSIONS ENABLEDELAYEDEXPANSION

rem ============================================================
rem  init-wsl.bat  — Windows-side helper for WSL + maintenance
rem  - Starts WSL/LxssManager service if needed
rem  - Detects installed distro(s)
rem  - Prints current user + SID
rem  - Optional cleanup (Recycle Bin, Temp, etc.)
rem  - Optional: create GodMode
rem  - Optional: DISM health check
rem  - Optional: winget upgrade
rem  - Updates WSL and runs distro init script if available
rem ============================================================

rem ---------- Config ----------
set "DISTRO_PREFERRED=Debian"
set "ROOT_INIT_PATH=/etc/init-wsl"
set "LOG=%TEMP%\init-wsl.log"

rem ---------- Banner ----------
echo.
echo ============================================================
echo  Init WSL (Windows helper)
echo  User    : %USERNAME%
echo  Host    : %COMPUTERNAME%
echo  Log     : %LOG%
echo  Time    : %date% %time%
echo ============================================================
echo.> "%LOG%"
echo [%date% %time%] Init start >> "%LOG%"

rem ---------- Ensure WSL / LxssManager service ----------
echo.
echo Ensuring WSL service is running...
sc start LxssManager >nul 2>&1
if errorlevel 1 sc start WSLService >nul 2>&1

rem ---------- Get SID for current user ----------
for /f "usebackq delims=" %%S in (`
  powershell -NoProfile -Command ^
    "(New-Object System.Security.Principal.NTAccount($env:USERNAME)).Translate([System.Security.Principal.SecurityIdentifier]).Value"
`) do set "SID=%%S"

if not defined SID (
  echo [WARN] Could not resolve SID for %USERNAME%.
  echo [WARN] Could not resolve SID for %USERNAME%. >> "%LOG%"
) else (
  echo Current user SID: %SID%
  echo Current user SID: %SID% >> "%LOG%"
)

rem ---------- Show Wi-Fi profiles (read-only) ----------
echo.
echo --- Wi-Fi Profiles (read-only) ---
netsh wlan show profiles
echo Hint: To view a specific key: netsh wlan show profile name="ProfileName" key=clear
echo.>> "%LOG%"
netsh wlan show profiles >> "%LOG%" 2>&1

rem ---------- Optional: open cleanup locations ----------
call :prompt_yesno "Open cleanup locations (Recycle Bin, Recent, Temp, Prefetch)?" N
if errorlevel 1 call :open_cleanup_views

rem ---------- Optional: create GodMode on Desktop ----------
call :prompt_yesno "Create GodMode folder on Desktop?" N
if errorlevel 1 call :create_godmode

rem ---------- Optional: DISM CheckHealth (elevated) ----------
call :prompt_yesno "Run DISM /CheckHealth (requires elevation)?" N
if errorlevel 1 call :run_dism_checkhealth

rem ---------- Optional: winget upgrade ----------
where winget >nul 2>&1
if errorlevel 1 (
  echo [INFO] winget not found. Skipping upgrades.
  echo [INFO] winget not found. Skipping upgrades. >> "%LOG%"
) else (
  echo.
  winget upgrade
  echo.
  call :prompt_yesno "Run 'winget upgrade --all --include-unknown'?" N
  if errorlevel 1 (
    echo Running winget upgrade --all --include-unknown ...
    echo Running winget upgrade --all --include-unknown ... >> "%LOG%"
    winget upgrade --all --include-unknown >> "%LOG%" 2>&1
  )
)

rem ============================================================
rem  DROP-IN FIX (encoding-safe): Detect distro & init via PowerShell
rem ============================================================

set "DISTRO="
set "DISTRO_PREFERRED=Debian"

rem Ask PowerShell for a clean, trimmed, non-empty list and pick the right one.
for /f "usebackq delims=" %%D in (`
  powershell -NoProfile -Command ^
    "$pref='%DISTRO_PREFERRED%';" ^
    "$names = wsl -l -q 2>$null | ForEach-Object { $_.ToString().Trim() } | Where-Object { $_ -ne '' };" ^
    "if (-not $names -or $names.Count -eq 0) { exit 3 }" ^
    "if ($names -icontains $pref) { $pref } else { $names | Select-Object -First 1 }"
`) do set "DISTRO=%%D"

if errorlevel 3 (
  echo [ERROR] No WSL distributions found!
  echo Run this to install Debian:  wsl --install -d Debian
  pause
  goto :eof
)

if not defined DISTRO (
  echo [ERROR] Could not resolve a WSL distro name.
  echo Tip: Run  wsl -l -q  to see installed distributions.
  goto :eof
)

echo [INFO] Using WSL distro: "%DISTRO%"

echo.
echo Updating WSL...
wsl --update

echo.
echo Running init script on "%DISTRO%"...
rem Use sh -lc so Linux path rules apply; guard existence/executable
rem wsl -d %DISTRO% -- sh -lc "test -x /etc/init-wsl && /etc/init-wsl || echo '/etc/init-wsl not found or not executable'"
rem wsl -d %DISTRO% -- sh -lc "if [ -x /etc/init-wsl ]; then /etc/init-wsl || echo 'init-wsl ran but failed with code $?'; else echo '/etc/init-wsl not found or not executable'; fi"
rem wsl -d %DISTRO% -- bash -lc 'if [ -x /etc/init-wsl ]; then /etc/init-wsl; rc=$?; if [ $rc -ne 0 ]; then echo "init-wsl exited with code $rc"; exit $rc; fi; else echo "/etc/init-wsl not found or not executable"; fi'
wsl -d %DISTRO% -u root -- bash -lc "if [ -x /etc/init-wsl ]; then /etc/init-wsl || { rc=\$?; echo init-wsl exited with code \$rc; exit \$rc; }; else echo /etc/init-wsl not found or not executable; fi"

echo.
echo Opening "%DISTRO%" root shell...
wsl -d %DISTRO% -u root

echo.
echo Done. See log: %LOG%
echo [%date% %time%] Init done >> "%LOG%"
goto :eof


rem ===================== Helpers ==============================

:prompt_yesno
rem Usage: call :prompt_yesno "Question?" [DefaultY|DefaultN]
rem Returns: ERRORLEVEL 1 if Yes, 0 if No
set "QUESTION=%~1"
set "DEFAULT=%~2"

if /I "%DEFAULT%"=="Y" (
  choice /C YN /D Y /T 10 /M "%QUESTION% (Y/N) [default=Y]"
) else (
  choice /C YN /D N /T 10 /M "%QUESTION% (Y/N) [default=N]"
)
if errorlevel 2 (
  exit /b 0
)
if errorlevel 1 (
  exit /b 1
)


:open_cleanup_views
echo.
echo Opening cleanup locations...
echo Opening cleanup locations... >> "%LOG%"

if defined SID (
  echo Recycle Bin (user)...
  echo Recycle Bin (user)... >> "%LOG%"
  start "" explorer.exe "C:\$Recycle.Bin\%SID%\"
)

echo Recent...
echo Recent... >> "%LOG%"
start "" explorer.exe "C:\Users\%USERNAME%\Recent\"

echo Temp...
echo Temp... >> "%LOG%"
start "" explorer.exe "C:\Users\%USERNAME%\AppData\Local\Temp"

echo Prefetch...
echo Prefetch... >> "%LOG%"
start "" explorer.exe "C:\Windows\prefetch\"

exit /b 0


:create_godmode
set "GODMODE_DIR=C:\Users\%USERNAME%\Desktop\GodMode.{ED7BA470-8E54-465E-825C-99712043E01C}"
if exist "%GODMODE_DIR%" (
  echo GodMode already exists at: %GODMODE_DIR%
  echo GodMode already exists at: %GODMODE_DIR% >> "%LOG%"
) else (
  mkdir "%GODMODE_DIR%" 2>nul
  if exist "%GODMODE_DIR%" (
    echo Created GodMode at: %GODMODE_DIR%
    echo Created GodMode at: %GODMODE_DIR% >> "%LOG%"
  ) else (
    echo [WARN] Could not create GodMode at: %GODMODE_DIR%
    echo [WARN] Could not create GodMode at: %GODMODE_DIR% >> "%LOG%"
  )
)
exit /b 0


:run_dism_checkhealth
echo.
echo Launching elevated PowerShell to run:
echo   dism /Online /Cleanup-Image /CheckHealth
echo (You will see a UAC prompt.)
echo. >> "%LOG%"
powershell -NoProfile -Command ^
  "Start-Process PowerShell -Verb RunAs -ArgumentList 'dism /Online /Cleanup-Image /CheckHealth'" >> "%LOG%" 2>&1
exit /b 0