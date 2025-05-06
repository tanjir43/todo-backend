# 📝 Todo App

### Setup Steps

1. **Clone the repository**

```bash
git clone https://github.com/tanjir43/todo-backend.git
```

2. **Navigate to project directory**

```bash
cd todo-backend
```

3. **Install dependencies**

```bash
composer install
```

4. **Configure environment**

```bash
cp .env.example .env
```

5. **Generate application key**

```bash
php artisan key:generate
```

6. **Set up database**

Edit your `.env` file with your database credentials: Or make it default to SQLite

```bash

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_username
DB_PASSWORD=your_database_password
```
OR 

```bash

DB_CONNECTION=sqlite
```

7. **Run migrations and seeders**

```bash
php artisan migrate:fresh --seed
```

8. **Start the development server**

```bash
php artisan serve
```


9. **Credential**

- **User Default**
  - Email: `test@test.com`
  - Password: `password`

10. **Testing**
```bash
php artisan test
```
