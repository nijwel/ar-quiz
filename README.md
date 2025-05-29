1.Clone your project

Go to the folder application using cd command on your cmd or terminal

2.Run -> composer install (if version problem "composer install --ignore-platform-reqs")

3.Run -> npm install && npm run dev

4.Run -> cp .env.example .env

5.Run -> php artisan key:generate

6.Run -> php artisan migrate

**if has seeder

php artisan db:seed (Command to run seeder)
php artisan db:seed --class=UserSeeder (Command for single seeder run)
7.Run -> php artisan serve

8.You can now access your project at localhost:8000 :)

I Hope your file will be running :)
