@echo off
cd /d "%~dp0"
echo [%date% %time%] Starting Unitecture Biometric Bridge... >> bridge_log.txt
:loop
:: Run Python in unbuffered mode (-u) so logs are written immediately
python -u bridge.py >> bridge_log.txt 2>&1
echo [%date% %time%] Bridge crashed or stopped. Restarting in 5 seconds... >> bridge_log.txt
timeout /t 5
goto loop
