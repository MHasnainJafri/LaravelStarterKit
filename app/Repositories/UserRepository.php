<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class UserRepository extends BaseRepository
{
    protected static string $model = User::class;
    protected static string $resourceClass = JsonResource::class;
    protected static array $searchable = [];
    protected static array $sortable = [];
    protected static array $defaultRelations = [];
    protected static array $allowedRelations = [];
    protected static int $defaultPerPage = 15;
    protected static string|null $cacheTag = null;

    public static function validationRules(string $operation): array
    {
        return $operation === 'store' ? [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'email_verified_at' => 'required|date',
            'password' => 'required|string|max:255',
            'two_factor_secret' => 'required|string|max:255',
            'two_factor_recovery_codes' => 'required|string|max:255',
            'two_factor_confirmed_at' => 'required|date',
            'remember_token' => 'required|string|max:255'
        ] : [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users',
            'email_verified_at' => 'sometimes|required|date',
            'password' => 'sometimes|required|string|max:255',
            'two_factor_secret' => 'sometimes|required|string|max:255',
            'two_factor_recovery_codes' => 'sometimes|required|string|max:255',
            'two_factor_confirmed_at' => 'sometimes|required|date',
            'remember_token' => 'sometimes|required|string|max:255'
        ];
    }
}