<?php

namespace App\Repositories;

use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class UsersRepository extends BaseRepository
{
    protected static string $model = User::class;
    protected static string $resourceClass = UserResource::class;
    protected static array $searchable = ['name', 'email'];
    protected static array $sortable = ['name', 'email', 'created_at'];
    protected static array $defaultRelations = ['roles'];
    protected static array $allowedRelations = ['roles', 'posts'];
    protected static int $defaultPerPage = 20;
    protected static string|null $cacheTag = 'users';
    protected static bool $useSoftDeletes = true;
    public static function validationRules(string $operation): array
    {
        return $operation === 'store' ? [
           
        ] : [
           
        ];
    }

    public static function customRoutes(string $modelName)
    {
        // Custom bulk insert route
        Route::post("$modelName/bulk", [static::class, 'bulkStore']);
        
        // Custom password reset route
        Route::post("$modelName/{id}/reset-password", [static::class, 'resetPassword']);
    }

    public static function bulkStore(Request $request)
    {
        $validated = $request->validate([
            'users' => 'required|array|max:100',
            'users.*.name' => 'required|string|max:255',
            'users.*.email' => 'required|email|unique:users,email',
            'users.*.password' => 'required|string|min:8',
        ]);

        collect($validated['users'])->each(function ($userData) {
            static::store(new Request($userData));
        });

        return static::formatResponse(null, 'Bulk users created', 201);
    }

    public static function resetPassword(Request $request, $id)
    {
        $user = static::$model::findOrFail($id);
        $user->update(['password' => bcrypt($request->new_password)]);
        
        return static::formatResponse($user, 'Password reset successfully');
    }

    protected static function beforeSave(Model $model, array $data, string $operation): Model
    {
        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }
        $model->fill($data);
        return $model;
    }

  
}