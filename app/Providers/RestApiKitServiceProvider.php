<?php

namespace App\Providers;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use ReflectionClass;
use Str;

class RestApiKitServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerRoutes();
        $this->registerExceptionHandler();

    }
    protected function registerExceptionHandler()
{
    $this->app->bind(
        \Illuminate\Contracts\Debug\ExceptionHandler::class,
        \App\Exceptions\CustomHandler::class
    );
}

    protected function registerRoutes()
    {
        Route::prefix('api')
            // ->middleware('api')
            ->group(function () {
                $this->registerCrudRoutes();
                $this->registerCustomRoutes();
            });
    }

    protected function registerCrudRoutes()
    {
        $repositories = $this->discoverRepositories();
        
        foreach ($repositories as $repositoryClass) {
            $modelName = $this->getRouteName($repositoryClass);
            
            // Standard CRUD Routes
            Route::get($modelName, [$repositoryClass, 'index']);
            Route::get("$modelName/{id}", [$repositoryClass, 'show']);
            Route::post($modelName, [$repositoryClass, 'store']);
            Route::put("$modelName/{id}", [$repositoryClass, 'update']);
            Route::delete("$modelName/{id}", [$repositoryClass, 'destroy']);

            // Auto-register soft delete routes if supported
            if ($this->supportsSoftDeletes($repositoryClass)) {
                Route::patch("$modelName/{id}/restore", [$repositoryClass, 'restore']);
            }
        }
    }

    protected function registerCustomRoutes()
    {
        foreach ($this->discoverRepositories() as $repositoryClass) {
            $reflection = new ReflectionClass($repositoryClass);
            
            if ($reflection->hasMethod('customRoutes')) {
                $modelName = $this->getRouteName($repositoryClass);
                $repositoryClass::customRoutes($modelName);
            }
        }
    }

    protected function discoverRepositories(): array
    {
        $repoPath = app_path('Repositories');
        if (!File::exists($repoPath)) return [];

        return collect(File::files($repoPath))
            ->map(fn ($file) => 'App\\Repositories\\' . pathinfo($file, PATHINFO_FILENAME))
            ->filter(fn ($class) => class_exists($class) && is_subclass_of($class, BaseRepository::class))
            ->toArray();
    }

    protected function getRouteName(string $repositoryClass): string
    {
        return Str::kebab(str_replace('Repository', '', class_basename($repositoryClass)));
    }

    protected function supportsSoftDeletes(string $repositoryClass): bool
    {
        return (new ReflectionClass($repositoryClass))->getStaticPropertyValue('useSoftDeletes', false);
    }
}