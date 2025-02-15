<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Mhasnainjafri\RestApiKit\API;

/**
 * Class BaseRepository
 *
 * Abstract class to provide base CRUD functionality for repositories.
 * Extend this class in specific repository implementations.
 *
 * @package App\Repositories
 */
abstract class BaseRepository
{
    /**
     * @var string The model class associated with the repository.
     */
    protected static string $model;

    /**
     * @var array Fields that can be searched via query parameters.
     */
    protected static array $searchable = [];

    /**
     * @var array Fields that can be sorted via query parameters.
     */
    protected static array $sortable = [];

    /**
     * @var array Relationships to load with the model.
     */
    protected static array $with = [];

    /**
     * @var callable|null Custom query logic for index method.
     */
    protected static $customIndexQuery = null;

    /**
     * @var callable|null Custom query logic for show method.
     */
    protected static $customshowQuery = null;
    protected static  $defineCustomIndexQuery = null;

    /**
     * Delete a record by ID.
     *
     * @param int|string $id The ID of the record to delete.
     * @return \Illuminate\Http\JsonResponse API response.
     */
    public static function destroy($id)
    {
        $response = static::$model::destroy($id);
        return API::response($response, "Data deleted successfully");
    }

    /**
     * Retrieve a single record by ID with optional relations.
     *
     * @param Request $request HTTP request instance.
     * @param int|string $id The ID of the record to fetch.
     * @return \Illuminate\Http\JsonResponse API response.
     */
    public static function show(Request $request, $id)
    {
        $query = static::$model::query()->with(static::$with);

        if (static::$customshowQuery) {
            $query = (static::$customshowQuery)($query);
        }

        $response = $query->findOrFail($id);
        return API::response($response, "Data fetched successfully");
    }

    /**
     * Retrieve all or paginated records with optional search & sorting.
     *
     * @param Request $request HTTP request instance.
     * @return \Illuminate\Http\JsonResponse API response.
     */
    public static function index(Request $request)
    {
        $query = static::$model::query()->with(static::$with);

        // Apply search filters
        foreach (static::$searchable as $field) {
            if ($value = $request->query($field)) {
                $query->where($field, 'like', "%$value%");
            }
        }

        // Apply sorting
        if ($sort = $request->query('sort')) {
            $direction = $request->query('direction', 'asc');
            if (in_array($sort, static::$sortable)) {
                $query->orderBy($sort, $direction);
            }
        }

        // Apply dynamic filters
        if ($filters = $request->query('filters')) {
            foreach ($filters as $key => $value) {
                if (method_exists(static::$model, 'scope' . ucfirst($key))) {
                    $query->$key($value);
                } else {
                    $query->where($key, $value);
                }
            }
        }
        // ✅ Ensure customIndexQuery is initialized before using it
        if (is_null(static::$customIndexQuery) && method_exists(static::class, 'defineCustomIndexQuery')) {
            static::$customIndexQuery = static::defineCustomIndexQuery();
        }

        if (static::$customIndexQuery) {
            $query = (static::$customIndexQuery)($query);
        }
        $response = filter_var($request->query('paginate', true), FILTER_VALIDATE_BOOLEAN) ? $query->paginate($request->query('per_page', 10)) : $query->get();
        return API::response($response, "Data fetched successfully");
    }

    /**
     * Store a new record with validation.
     *
     * @param Request $request HTTP request instance.
     * @return \Illuminate\Http\JsonResponse API response.
     */
    public static function store(Request $request)
    {
        $validated = static::validateRequest($request, 'store');

        $model = static::$model::create($validated);
        return API::success($model, "Data created successfully");
    }

   

    /**
     * Update an existing record by ID.
     *
     * @param Request $request HTTP request instance.
     * @param int|string $id The ID of the record to update.
     * @return \Illuminate\Http\JsonResponse API response.
     */
    public static function update(Request $request, $id)
    {
        $validated = static::validateRequest($request, 'update');

        $model = static::$model::findOrFail($id);
        $model->update($validated);

        return API::success($model, "Data updated successfully");
    }

    /**
     * Validate request data using a custom validation class if provided.
     *
     * @param Request $request
     * @return array Validated data.
     */
   

     protected static function validateRequest(Request $request, string $type): array
     {
         $validationClass = null;
         if ($type === 'store') {
             $validationClass = static::storeValidationRules();
         } elseif ($type === 'update') {
             $validationClass = static::updateValidationRules();
         }
 
         return $validationClass ? $request->validate(app($validationClass)->rules()) : $request->all();
     }
 
     protected static function storeValidationRules(): ?string
     {
         return null;
     }
 
     protected static function updateValidationRules(): ?string
     {
         return null;
     }

  
   
}
