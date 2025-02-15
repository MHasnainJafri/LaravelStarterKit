<?php
namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use ReflectionClass;

class RestApiKitServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerRoutes();
    }

    protected function registerRoutes()
    {
        Route::prefix('api')->middleware('api')->group(function () {
            $repositoryNamespace = 'App\\Repositories\\';

            $repoPath = __DIR__ . '/../Repositories';
            if (!File::exists($repoPath)) {
                return;
            }

            foreach (File::files($repoPath) as $file) {
                $className = pathinfo($file->getFilename(), PATHINFO_FILENAME);
                $repositoryClass = $repositoryNamespace . $className;

                if (!class_exists($repositoryClass)) {
                    continue;
                }

                $reflection = new ReflectionClass($repositoryClass);
                if (!$reflection->isSubclassOf('App\Repositories\BaseRepository')) {
                    continue;
                }

                $modelName = strtolower(str_replace('Repository', '', $className));
                Route::get($modelName, fn (Request $req) => $repositoryClass::index($req));
                Route::get("$modelName/{id}", fn (Request $req, $id) => $repositoryClass::show($req, $id));
                Route::post($modelName, fn (Request $req) => $repositoryClass::store($req));
                Route::put("$modelName/{id}", fn (Request $req, $id) => $repositoryClass::update($req, $id));
                Route::delete("$modelName/{id}", fn ($id) => $repositoryClass::destroy($id));
            }
        });
    }
}
