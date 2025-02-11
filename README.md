# Laravel Project Starter Kit

This is a Laravel project that integrates a variety of useful packages to enhance functionality, improve development workflows, and support additional features. Below is an overview of the packages and custom Artisan commands included in this starter kit, and how to use them effectively in your Laravel applications.

---

## Requirements

Before running the project, make sure you have the following installed:

- PHP >= 8.0
- Composer
- MySQL or any database system supported by Laravel
- Node.js and NPM (for asset compilation)

## Installation

Clone the repository and install the necessary dependencies:

```bash
git clone https://github.com/MHasnainJafri/LaravelStarterKit.git
cd LaravelStarterKit
composer install
npm install
```

---


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

## Custom Artisan Commands

This starter kit includes custom Artisan commands for generating boilerplate code and working with models and services efficiently.

### 1. **Updating Models with Relations, Fillables, and Casts**

You can use the following command to quickly update your model with the table structure, relationships, fillables, and casts:

```bash
php artisan update:model User --table=users
```

- **`--table=users`**: Specifies the database table for the model.
- This command will automatically generate the relationships, fillable attributes, and casts for the specified model based on the corresponding database table.

### 2. **Service Pattern Generation Command**

To help you generate a well-structured service pattern, you can use the `make:service-pattern` Artisan command. This command scaffolds a service structure that includes a model, controller, service, request, and response classes. You can also customize it with options for API or admin views.

#### Syntax:

```bash
php artisan make:service-pattern 
    {name : The name of the service (e.g., UserService)} 
    {--model= : The name of the model (e.g., User)} 
    {--table= : The name of the table (e.g., users)} 
    {--foreignKeys= : Comma-separated foreign keys (e.g., role_id,company_id)} 
    {--api : Generate API-specific controller and service}
    {--view : Generate API-specific controller and service}
```

#### Example usage:

```bash
php artisan make:service-pattern BusinessService --model=Business --api --view
```

This will:
- Create a **BusinessService** class.
- Generate a **Business** model.
- Create a controller and service tailored for **API** use.
- Add admin panel views if the `--view` flag is passed.

The command automatically generates files and structures that follow best practices for API or admin panel services, allowing you to focus on implementing business logic.

---

## Service Pattern File Structure

The `make:service-pattern` command will generate the following structure for each service:

- **Model** (`app/Models/ModelName.php`)
- **Controller** (`app/Http/Controllers/Api/ModelNameController.php` or `app/Http/Controllers/Admin/ModelNameController.php` based on the `--api` or `--view` flag)
- **Service** (`app/Services/ModelNameService.php`)
- **Request** (`app/Http/Requests/ModelNameRequest.php`)
- **Response** (`app/Http/Responses/ModelNameResponse.php`)

This pattern ensures a clear separation of concerns between different components and follows the standard Laravel structure.

---

## Key Features and Packages
---
### 1. **Caching and Securing .htaccess**

This starter kit includes configurations to handle caching and secure your `.htaccess` file. These are useful for enhancing security and improving performance in your application.

- **Caching:** Automatically caches routes, views, and configuration settings for faster loading times.
- **Securing .htaccess:** Protects sensitive areas of the application by automatically configuring .htaccess rules for security.

### 2. **Progressive Web App (PWA) Support**

This project integrates PWA functionality for improved performance on mobile devices and offline capabilities. By leveraging service workers and a manifest file, your Laravel application can function as a native app on supported browsers.

---

## Custom Middleware

The starter kit comes with some custom security middleware to improve application security by enforcing HTTP security headers.

### 1. **SecurityHeadersMiddleware**
This middleware adds various HTTP security headers to your responses to protect against common security vulnerabilities.

### 2. **ContentSecurityPolicyMiddleware**
This middleware helps in implementing a Content Security Policy (CSP) to mitigate the risks of cross-site scripting (XSS) and other malicious attacks. It allows you to define where resources can be loaded from.


## Conclusion

This Laravel starter kit is designed to speed up development by providing common features and custom Artisan commands that generate well-structured code. The security-focused middleware and PWA integration further enhance the functionality of your application. By leveraging the included commands and patterns, you can quickly scaffold out new services, models, and controllers to help keep your codebase clean and maintainable.



## Some Important Packages 
#### Not installed in repo but usefull to know

The following Laravel packages are included in the project:

### **1. Laravel IDE Helper [Installed]**
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

### **4. Socialite [Installed]**
- Handles authentication via social networks such as Facebook, Google, GitHub, etc.

### **5. Laravel Telescope**
- Provides debugging and monitoring tools for your Laravel application.
  ```bash
  php artisan telescope:install
  ```

### **6. Orchestral Testbench [Installed]**
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

### **11. mhasnainjafri/restapikit [Installed]**
- A set of tools to make building RESTful APIs easier within Laravel.

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
