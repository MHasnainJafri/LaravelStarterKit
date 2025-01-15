<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeServicePattern extends Command
{
    protected $signature = 'make:service-pattern 
                            {name : The name of the service (e.g., UserService)} 
                            {--model= : The name of the model (e.g., User)} 
                            {--table= : The name of the table (e.g., users)} 
                            {--foreignKeys= : Comma-separated foreign keys (e.g., role_id,company_id)} 
                            {--api : Generate API-specific controller and service}';

    protected $description = 'Generate a service pattern structure with model, controller, service, request, and response classes.';

    public function handle()
    {
        $name = $this->argument('name');
        $model = $this->option('model') ?: $name;
        $table = $this->option('table') ?: strtolower(str_plural($model));
        $foreignKeys = $this->option('foreignKeys') ? explode(',', $this->option('foreignKeys')) : [];
        $isApi = $this->option('api');

        $this->createModel($model, $foreignKeys);
        $this->createController($name, $isApi);
        $this->createService($name, $isApi);
        $this->createRequest($name, $isApi);

        $this->info('Service pattern structure generated successfully!');
    }

    protected function createModel($model, $foreignKeys)
    {
        $this->call('make:model', ['name' => $model]);
        $modelPath = app_path("Models/{$model}.php");

        if (!File::exists($modelPath)) {
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
            $content = str_replace("}", "{$relationship}}", $content);
        }
        File::put($modelPath, $content);
        $this->info("Model {$model} updated with relationships.");
    }

    protected function guessModelName($foreignKey)
    {
        return ucfirst(str_replace('_id', '', $foreignKey));
    }
    protected function createService($name, $isApi)
    {
        $model = $this->option('model') ?: $name;
        $path = app_path("Services/{$name}Service.php");
        File::ensureDirectoryExists(dirname($path));
    
        $content = $this->getStub('Service.stub', [
            'name' => $name,
            'model' => $model,
            'modelVariable' => lcfirst($model),
        ]);
    
        File::put($path, $content);
        $this->info("Service class created at: {$path}");
    }
    
    protected function createController($name, $isApi)
    {
        $model = $this->option('model') ?: $name;
        $type = $isApi ? 'Api/' : '';
        $path = app_path("Http/Controllers/{$type}{$name}Controller.php");
        File::ensureDirectoryExists(dirname($path));
    
        $content = $this->getStub('Controller.stub', [
            'name' => $name,
            'model' => $model,
            'modelVariable' => lcfirst($model),
        ]);
    
        File::put($path, $content);
        $this->info("Controller created at: {$path}");
    }
    
    protected function createRequest($name, $isApi)
    {
        $type = $isApi ? 'Api/' : '';
        $path = app_path("Http/Requests/{$type}{$name}Request.php");
        File::ensureDirectoryExists(dirname($path));
    
        $content = $this->getStub('Request.stub', ['name' => $name]);
    
        File::put($path, $content);
        $this->info("Request class created at: {$path}");
    }
    

    protected function getStub($stub, $replacements)
    {
        $stubPath = resource_path("stubs/{$stub}");
        if (!File::exists($stubPath)) {
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
