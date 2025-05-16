
# 📝 Task Management Application

A Laravel-based task management application created as a technical assessment for **Coalition Technologies**.

## 🚀 Features

- Task management (Reorder with drag-and-drop)
- User authentication
- Real-time updates using Pusher
- Responsive UI with Blade components
- API endpoints for frontend-backend communication
- Compact Menu Navigation im the navbar

## Task Order Feature

The application provides two distinct views for managing tasks:

### 1. Project View Tab
- Displays tasks grouped by their respective projects
- Tasks can be reordered within each project via drag-and-drop
- Visual hierarchy shows project > task relationships
- Ideal for focused work on specific projects

### 2. All Tasks Tab
- Shows a comprehensive list of all tasks across all projects
- Supports global drag-and-drop reordering
- Includes project labeling for context
- Perfect for overall prioritization and cross-project management

## 🛠️ Tech Stack

- **PHP 8.2+**
- **Laravel 12.x**
- **Laravel Sanctum** – API authentication
- **Pusher** – Real-time updates
- **Blade UI Kit** – UI components
- **Laravel Tinker** – REPL
- **OpenRouter** – AI recommendations

## 📦 Dependencies

### Main Dependencies

- `laravel/framework`: Laravel framework
- `laravel/sanctum`: API authentication
- `pusher/pusher-php-server`: Real-time functionality
- `blade-ui-kit/blade-ui-kit`: UI component library
- `blade-ui-kit/blade-heroicons`: Heroicons for Blade
- `spatie/laravel-sitemap`: Sitemap generation

### Development Dependencies

- `pestphp/pest`: Testing framework
- `barryvdh/laravel-ide-helper`: IDE helpers
- `laravel/pint`: PHP code style fixer
- `mockery/mockery`: Mocking library for tests

## ⚙️ Installation

Run the following commands to set up the application:

```bash
git clone [repository-url]
cd [project-directory]
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
npm run dev
```

## 🧪 Testing

Run the test suite:

```bash
composer test
```

## 🔧 Development Commands

Run all services (server, queue, logs, Vite):

```bash
composer run dev
```

Re-run database migrations:

```bash
php artisan migrate:refresh --seed
```

Clear configuration cache:

```bash
php artisan config:clear
```

## 📝 Notes for Reviewers

- Follows Laravel best practices
- Uses Blade components for reusable UI
- Implements API endpoints for frontend communication
- Real-time updates powered by Pusher
- AI-based project recommendations using openrouter
- PSR-12 coding standards

## 📄 License

This project is open-source software licensed under the [MIT license](LICENSE).
