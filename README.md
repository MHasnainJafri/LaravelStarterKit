# Laravel Project

This is a Laravel project that integrates a variety of useful packages to enhance functionality, improve development workflows, and support additional features. Below is an overview of the packages included in this project and how to use them.

for caching and securing .htaccess
PWA
security Middleware

SecurityHeadersMiddleware
ContentSecurityPolicyMiddleware

php artisan update:model User --table=users

php artisan make:service V1/Business/Business --model=Business  --api --view


## Requirements

Before running the project, make sure you have the following installed:

- PHP >= 8.0
- Composer
- MySQL or any database system supported by Laravel
- Node.js and NPM (for asset compilation)
- Laravel 10.x or later

## Installation

Clone the repository and install the necessary dependencies:

```bash
git clone https://github.com/your-repository.git
cd your-project-directory
composer install
npm install
```

### Environment Configuration

Copy the `.env.example` file to create your `.env` file:

```bash
cp .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

Update the `.env` file with your database and other service credentials.

### Running the Application

To start the development server, use the following command:

```bash
composer run dev
```

This will set up the environment and start the server.

## Installed Packages

The following Laravel packages are included in the project:

### **1. Laravel IDE Helper**
- Generates IDE helper files to improve autocompletion and code suggestions.
  ```bash
  php artisan ide-helper:generate
  ```

### **2. Laravel Backup**
- Provides functionality to backup database and files, ensuring safe data storage.
  ```bash
  php artisan backup:run
  ```

### **3. Eloquent Sluggable**
- Automatically generates slugs for models based on the specified fields.
  ```php
  use Cviebrock\EloquentSluggable\Sluggable;
  ```

### **4. Socialite**
- Handles authentication via social networks such as Facebook, Google, GitHub, etc.

### **5. Laravel Telescope**
- Provides debugging and monitoring tools for your Laravel application.
  ```bash
  php artisan telescope:install
  ```

### **6. Orchestral Testbench**
- Facilitates the testing of Laravel packages by providing a testing environment for packages.

### **7. spatie/laravel-activitylog**
- Tracks activities within your application and logs them for auditing purposes.
  ```php
  activity()->log('User logged in');
  ```

### **8. nWidart/laravel-modules**
- Enables modular development in Laravel by allowing you to break your app into separate modules.
  ```bash
  php artisan module:make Blog
  ```

### **9. archtechx/tenancy**
- Provides multi-tenancy support in Laravel applications, enabling data isolation for different tenants.

### **10. AndreasElia/laravel-api-to-postman**
- Exports your Laravel API routes to a Postman collection for easier API testing and documentation.

### **11. binarcode/laravel-RestApiKit**
- A set of tools to make building RESTful APIs easier within Laravel.

### **12. ahmedesa/laravel-api-tool-kit**
- Additional tools to simplify and enhance the development of APIs in Laravel.

### **13. spatie/laravel-medialibrary**
- Associate files with Eloquent models and manage file storage with ease.
  ```php
  $user->addMedia($file)->toMediaCollection();
  ```

### **14. spatie/image-optimizer**
- Optimizes images by reducing their size without losing quality.
  ```bash
  php artisan optimize:images
  ```

### **15. beyondcode/laravel-query-detector** *(--dev)*
- Detects problematic database queries and logs slow queries during development.

### **16. spatie/laravel-query-builder**
- A package to help you build and customize queries for Eloquent models easily.

### **17. owen-it/laravel-auditing**
- Audits user activity and data changes across your application.
  ```bash
  php artisan audit:run
  ```

### **18. vcian/laravel-db-auditor** *(--dev)*
- Allows tracking and auditing of changes to the database schema during development.

### **19. Localization**
- Provides support for localization (translations) in your application.
  ```php
  trans('messages.welcome');
  ```

### **20. spatie/laravel-translatable**
- Easily handle multi-language content in your Eloquent models.

### **21. barryvdh/laravel-ide-helper**
- Improves your IDE's ability to understand and auto-complete Laravel's facades and helper functions.

### **22. spatie/laravel-sitemap**
- Easily generate and maintain your site's XML sitemap.
  ```bash
  php artisan sitemap:generate
  ```

### **23. bavix/laravel-wallet**
- Provides wallet functionality within Laravel to store money and perform transactions.

### **24. echolabsdev/prism**
- Package for integrating Large Language Models (LLMs) into your AI applications.

### **25. spatie/laravel-server-side-rendering**
- Adds support for server-side rendering of JavaScript within your Laravel application.

### **26. laraveldaily/laravel-invoices**
- Generates invoices and PDFs within your Laravel application, including customizable templates.

## Development Tools

### **--dev Packages**
Some packages are only installed in the development environment:

- **beyondcode/laravel-query-detector**: Detects slow or inefficient queries during development.
- **vcian/laravel-db-auditor**: Allows schema tracking in development.

## Testing

This project uses [PHPUnit](https://phpunit.de/) for testing. Run the tests with:

```bash
php artisan test
```

For testing modules, use the Orchestral Testbench package.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

This README covers all of the packages you mentioned and provides a brief overview of each package's purpose and usage. Make sure to replace placeholders like `your-repository.git` with the actual repository link, and customize it as necessary for your project.