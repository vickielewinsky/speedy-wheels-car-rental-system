@echo off
echo Cleaning up expired bookings...
php cleanup.php --force
echo.
echo Current status:
php cleanup.php --stats
pause
