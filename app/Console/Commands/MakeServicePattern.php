<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeServicePattern extends Command
{
    protected $signature = 'make:service-pattern 
                            {name : The name of the service (e.g., UserService)} 
                            {--model= : The name of the model (e.g., User)} 
                            {--table= : The name of the table (e.g., users)} 
                            {--foreignKeys= : Comma-separated foreign keys (e.g., role_id,company_id)} 
                            {--api : Generate API-specific controller and service}
                            {--view : Generate API-specific controller and service}';

    protected $description = 'Generate a service pattern structure with model, controller, service, request, and response classes.';

    public function handle()
    {
        $nameWithnamespace = $this->argument('name');

        // Extract the name and namespace
        $lastSlashPosition = strrpos($nameWithnamespace, '/');
        $name = $lastSlashPosition !== false
            ? substr($nameWithnamespace, $lastSlashPosition + 1)
            : $nameWithnamespace;

        $namespace = $lastSlashPosition !== false
            ? substr($nameWithnamespace, 0, $lastSlashPosition)
            : '';
        $namespace = str_replace('/', '\\', $namespace);


        $model = $this->option('model') ?: $name;
        $table = $this->option('table') ?: strtolower(Str::plural($model));
        $foreignKeys = $this->option('foreignKeys') ? explode(',', $this->option('foreignKeys')) : [];
        $isApi = $this->option('api');
        $isView = $this->option('view');
        $this->createModel($model, $foreignKeys);
        $this->createController($name, $isApi, $isView, $namespace);
        $this->createService($name, $isApi, $namespace);
        $this->createRequest($name, $model, $isApi, $namespace);

        $this->info('Service pattern structure generated successfully!');
    }

    protected function createModel($model, $foreignKeys)
    {
        $this->call('make:model', ['name' => $model]);
        $modelPath = app_path("Models/{$model}.php");

        if (! File::exists($modelPath)) {
            $this->error("Model {$model} not found!");

            return;
        }

        $content = File::get($modelPath);
        foreach ($foreignKeys as $key) {
            $relationship = <<<EOL

    public function {$key}()
    {
        return \$this->belongsTo({$this->guessModelName($key)}::class);
    }

EOL;
            $content = str_replace('}', "{$relationship}}", $content);
        }
        File::put($modelPath, $content);
        $this->info("Model {$model} updated with relationships.");
    }

    protected function guessModelName($foreignKey)
    {
        return ucfirst(str_replace('_id', '', $foreignKey));
    }

    protected function createService($name, $isApi, $namespace)
    {
        $model = $this->option('model') ?: $name;
        $path = app_path("Services/{$namespace}/{$name}Service.php");
        File::ensureDirectoryExists(dirname($path));

        $content = $this->getStub('Service.stub', [
            'name' => $name,
            'model' => $model,
            'namespace' => $namespace ? "\\$namespace" : '',
            'modelVariable' => lcfirst($model),
        ]);

        File::put($path, $content);
        $this->info("Service class created at: {$path}");
    }

    protected function createController(string $name, bool $isApi, bool $isView, $namespace): void
    {
        $model = $this->option('model') ?: $name;

        // Generate API Controller if $isApi is true
        if ($isApi) {
            $apiPath = app_path("Http/Controllers/Api/{$namespace}/{$name}Controller.php");
            File::ensureDirectoryExists(dirname($apiPath));

            if (File::exists($apiPath)) {
                $this->warn("API Controller already exists at: {$apiPath}");
            } else {
                $apiContent = $this->getStub('ApiController.stub', [
                    'name' => $name,
                    'model' => $model,
                    'namespace' => $namespace ? "\\$namespace" : '',
                    'modelVariable' => lcfirst($model),
                ]);
                File::put($apiPath, $apiContent);
                $this->info("API Controller created at: {$apiPath}");
            }
        }

        // Generate Regular Controller if $isView is true
        if ($isView || ! $isApi) {
            $viewPath = app_path("Http/Controllers/{$namespace}/{$name}Controller.php");
            File::ensureDirectoryExists(dirname($viewPath));

            if (File::exists($viewPath)) {
                $this->warn("Regular Controller already exists at: {$viewPath}");
            } else {
                $viewContent = $this->getStub('Controller.stub', [
                    'name' => $name,
                    'model' => $model,
                    'namespace' => $namespace ? "\\$namespace" : '',
                    'modelVariable' => lcfirst($model),
                ]);
                File::put($viewPath, $viewContent);
                $this->info("Regular Controller created at: {$viewPath}");
            }
        }
    }

    protected function createRequest($name, $model, $isApi, $namespace)
    {
        $type = $isApi ? 'Api/' : '';
        $path = app_path("Http/Requests/$namespace/{$name}Request.php");
        File::ensureDirectoryExists(dirname($path));

        $content = $this->getStub('Request.stub', [
            'name' => $name,
            'model' => $model,
            'namespace' => $namespace ? "\\$namespace" : '',
        ]);

        File::put($path, $content);
        $this->info("Request class created at: {$path}");
    }

    protected function getStub($stub, $replacements)
    {
        $stubPath = resource_path("stubs/{$stub}");
        if (! File::exists($stubPath)) {
            $this->error("Stub file {$stub} not found!");

            return '';
        }

        $content = File::get($stubPath);
        foreach ($replacements as $key => $value) {
            $content = str_replace("{{{$key}}}", $value, $content);
        }

        return $content;
    }
}
