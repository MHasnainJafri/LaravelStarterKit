<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateRepository extends Command
{
    protected $signature = 'make:repository {model} 
                            {--resourceClass= : The resource class to use} 
                            {--searchable= : Fields that are searchable (comma-separated)} 
                            {--sortable= : Fields that are sortable (comma-separated)} 
                            {--defaultRelations= : Default relations (comma-separated)} 
                            {--allowedRelations= : Allowed relations (comma-separated)} 
                            {--defaultPerPage=20 : Default items per page} 
                            {--cacheTag= : Cache tag for repository} 
                            {--useSoftDeletes=true : Whether to use soft deletes}';

    protected $description = 'Generate a repository for a given model';

    public function handle()
    {
        $model = $this->argument('model');
        $modelClass = "App\\Models\\$model";

        if (!class_exists($modelClass)) {
            $this->error("Model '$model' does not exist.");
            return;
        }

        $repositoryName = "{$model}Repository";
        $repositoryPath = app_path("Repositories/{$repositoryName}.php");

        if (file_exists($repositoryPath)) {
            $this->error("Repository '{$repositoryName}' already exists.");
            return;
        }

        $this->generateRepository($model, $repositoryName, $repositoryPath);
        $this->info("Repository '{$repositoryName}' created successfully.");
    }

    protected function generateRepository($model, $repositoryName, $repositoryPath)
    {
        $resourceClass = $this->option('resourceClass') ?: "{$model}Resource";
        $searchable = $this->option('searchable') ?: '[]';
        $sortable = $this->option('sortable') ?: '[]';
        $defaultRelations = $this->option('defaultRelations') ?: '[]';
        $allowedRelations = $this->option('allowedRelations') ?: '[]';
        $defaultPerPage = $this->option('defaultPerPage');
        $cacheTag = $this->option('cacheTag') ?: 'default';
        $useSoftDeletes = filter_var($this->option('useSoftDeletes'), FILTER_VALIDATE_BOOLEAN);

        $stub = file_get_contents(resource_path('stubs/repository.stub'));

        $stub = str_replace(
            ['{{model}}', '{{repositoryName}}', '{{baseRepository}}'],
            [$model, $repositoryName, 'BaseRepository'],
            $stub
        );

        $stub = str_replace(
            ['{{resourceClass}}', '{{searchable}}', '{{sortable}}', '{{defaultRelations}}', '{{allowedRelations}}', '{{defaultPerPage}}', '{{cacheTag}}', '{{useSoftDeletes}}'],
            [$resourceClass, $searchable, $sortable, $defaultRelations, $allowedRelations, $defaultPerPage, "'$cacheTag'", $useSoftDeletes ? 'true' : 'false'],
            $stub
        );

        file_put_contents($repositoryPath, $stub);
    }
}
