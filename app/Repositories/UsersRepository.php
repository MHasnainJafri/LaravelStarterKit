<?php
namespace App\Repositories;

use App\Models\User;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;

class UsersRepository extends BaseRepository
{
    protected static string $model = User::class;

    protected static array $searchable = ['name', 'email', 'phone'];
    protected static array $with = [];
    protected static array $sortable = ['name', 'created_at'];
    // ✅ Define custom index query dynamically
    protected static function defineCustomIndexQuery()
    {
        return function ($query) {
            return $query->where('id', "<", 100); // Example: Exclude banned users
        };
    }

    protected static function storeValidationRules(): ?string
    {
        return StoreUserRequest::class;
    }

    protected static function updateValidationRules(): ?string
    {
        return UpdateUserRequest::class;
    }
}


//mhasnianjafri lib is change for pagination ssssuuuu