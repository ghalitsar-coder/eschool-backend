@echo off
echo Running Laravel Seeders...
echo.

echo 1. Running UserSeeder...
@REM php artisan db:seed --class=UserSeeder

echo 2. Running SchoolSeeder...
php artisan db:seed --class=SchoolSeeder

echo 3. Running EschoolSeeder...
php artisan db:seed --class=EschoolSeeder

echo 4. Running MemberSeeder...
php artisan db:seed --class=MemberSeeder

echo 5. Running KasRecordSeeder...
php artisan db:seed --class=KasRecordSeeder

echo 6. Running KasPaymentSeeder...
php artisan db:seed --class=KasPaymentSeeder

echo.
echo All seeders completed successfully!
pause