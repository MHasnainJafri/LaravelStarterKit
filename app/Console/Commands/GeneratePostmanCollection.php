<?php

namespace App\Console\Commands;

use App\Repositories\UsersRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use ReflectionClass;

class GeneratePostmanCollection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'postman:generate {--output=postman_collection.json}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a Postman collection dynamically based on repositories';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $outputFile = $this->option('output');
        $repoNamespace = 'App\\Repositories\\';
        $repoPath = app_path('Repositories');

        if (!File::exists($repoPath)) {
            $this->error("Repositories directory not found.");
            return;
        }

        $postmanCollection = [
            'info' => [
                'name' => 'Laravel RestApiKit',
                'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
            ],
            'item' => [],
        ];

        foreach (File::files($repoPath) as $file) {
            $className = pathinfo($file->getFilename(), PATHINFO_FILENAME);
            $repositoryClass = $repoNamespace . $className;

            if (!class_exists($repositoryClass)) {
                continue;
            }

            $reflection = new ReflectionClass($repositoryClass);
            if (!$reflection->isSubclassOf('App\Repositories\BaseRepository')) {
                continue;
            }

            $modelName = strtolower(str_replace('Repository', '', $className));
            $postmanCollection['item'][] = $this->generateResourceEndpoints($repositoryClass, $modelName);
        }

        File::put($outputFile, json_encode($postmanCollection, JSON_PRETTY_PRINT));
        $this->info("Postman collection generated successfully at {$outputFile}");
    }

    /**
     * Generate Postman endpoints for a resource.
     *
     * @param string $repositoryClass
     * @param string $resourceName
     * @return array
     */
    protected function generateResourceEndpoints(string $repositoryClass, string $resourceName): array
    {
        // Extract validation rules for POST and PUT requests
        $storeRules = $this->extractValidationRules($repositoryClass, 'storeValidationRules');
        $updateRules = $this->extractValidationRules($repositoryClass, 'updateValidationRules');

        return [
            'name' => ucfirst($resourceName),
            'item' => [
                $this->createEndpoint('GET', "/api/{$resourceName}", 'Fetch all ' . $resourceName,'index'),
                $this->createEndpoint('GET', "/api/{$resourceName}/{id}", 'Fetch a single ' . $resourceName,'show'),
                $this->createEndpointWithBody('POST', "/api/{$resourceName}", 'Create a new ' . $resourceName, $storeRules),
                $this->createEndpointWithBody('PUT', "/api/{$resourceName}/{id}", 'Update an existing ' . $resourceName, $updateRules),
                $this->createEndpoint('DELETE', "/api/{$resourceName}/{id}", 'Delete a ' . $resourceName,'destroy'),
            ],
        ];
    }

    /**
     * Extract validation rules from a repository method.
     *
     * @param string $repositoryClass
     * @param string $methodName
     * @return array|null
     */
    protected function extractValidationRules(string $repositoryClass, string $methodName): ?array
    {
        if (!method_exists($repositoryClass, $methodName)) {
            return null;
        }

        // Call the public method directly
        $formRequestClass = $repositoryClass::$methodName();
        if (!class_exists($formRequestClass)) {
            return null;
        }

        // Instantiate the Form Request class
        $formRequestInstance = new $formRequestClass();

        // Call the rules() method on the instance
        if (!method_exists($formRequestInstance, 'rules')) {
            return null;
        }

        $rules = $formRequestInstance->rules();
        return $this->convertRulesToJsonSchema($rules);
    }

    /**
     * Convert Laravel validation rules to JSON schema.
     *
     * @param array $rules
     * @return array
     */
    protected function convertRulesToJsonSchema(array $rules): array
    {
        $schema = [
            'type' => 'object',
            'properties' => [],
            'required' => [],
        ];

        foreach ($rules as $field => $rule) {
            $ruleParts = explode('|', $rule);

            $schema['properties'][$field] = [
                'type' => $this->getTypeFromRule($ruleParts),
            ];

            if (in_array('required', $ruleParts)) {
                $schema['required'][] = $field;
            }

            // Check if the field is a file or image
            if (in_array('file', $ruleParts) || in_array('image', $ruleParts)) {
                $schema['properties'][$field]['type'] = 'file';
            }
        }

        return $schema;
    }

    /**
     * Get the JSON schema type from a Laravel validation rule.
     *
     * @param array $ruleParts
     * @return string
     */
    protected function getTypeFromRule(array $ruleParts): string
    {
        if (in_array('string', $ruleParts)) {
            return 'string';
        } elseif (in_array('integer', $ruleParts)) {
            return 'integer';
        } elseif (in_array('boolean', $ruleParts)) {
            return 'boolean';
        } elseif (in_array('array', $ruleParts)) {
            return 'array';
        } elseif (in_array('numeric', $ruleParts)) {
            return 'number';
        }

        return 'string'; // Default to string
    }

    /**
     * Create a Postman endpoint with a request body.
     *
     * @param string $method
     * @param string $url
     * @param string $description
     * @param array|null $bodySchema
     * @return array
     */
    protected function createEndpointWithBody(string $method, string $url, string $description, ?array $bodySchema): array
    {
        $endpoint = $this->createEndpoint($method, $url, $description);

        if ($bodySchema) {
            // Check if any of the fields are files or images
            $hasFile = $this->hasFileValidation($bodySchema);

            if ($hasFile) {
                // Use form-data for file uploads
                $endpoint['request']['body'] = [
                    'mode' => 'formdata',
                    'formdata' => $this->generateFormData($bodySchema),
                ];
            } else {
                // Use raw JSON for other cases
                $endpoint['request']['body'] = [
                    'mode' => 'raw',
                    'raw' => json_encode($this->generateExampleData($bodySchema), JSON_PRETTY_PRINT),
                    'options' => [
                        'raw' => [
                            'language' => 'json',
                        ],
                    ],
                ];
            }
        }

        return $endpoint;
    }

    /**
     * Check if the schema contains any file or image validation rules.
     *
     * @param array $schema
     * @return bool
     */
    protected function hasFileValidation(array $schema): bool
    {
        foreach ($schema['properties'] as $field => $property) {
            if (isset($property['type']) && $property['type'] === 'file') {
                return true;
            }
        }
        return false;
    }

    /**
     * Generate form-data for file uploads.
     *
     * @param array $schema
     * @return array
     */
    protected function generateFormData(array $schema): array
    {
        $formData = [];

        foreach ($schema['properties'] as $field => $property) {
           if ($property['type'] === 'file') {
                $formData[] = [
                    'key' => $field,
                    'type' => 'file',
                    'src' => '', // You can leave this empty or provide a placeholder
                ];
            } else {
                $formData[] = [
                    'key' => $field,
                    'value' => $this->getExampleValue($property['type']),
                    'type' => 'text',
                ];
            }
        }

        return $formData;
    }

    /**
     * Generate example data based on JSON schema.
     *
     * @param array $schema
     * @return array
     */
    protected function generateExampleData(array $schema): array
    {
        $exampleData = [];

        foreach ($schema['properties'] as $field => $property) {
            switch ($property['type']) {
                case 'string':
                    $exampleData[$field] = 'example_string';
                    break;
                case 'integer':
                    $exampleData[$field] = 123;
                    break;
                case 'boolean':
                    $exampleData[$field] = true;
                    break;
                case 'array':
                    $exampleData[$field] = ['example_item'];
                    break;
                case 'number':
                    $exampleData[$field] = 123.45;
                    break;
                default:
                    $exampleData[$field] = 'example_value';
            }
        }

        return $exampleData;
    }

    /**
     * Create a Postman endpoint.
     *
     * @param string $method
     * @param string $url
     * @param string $description
     * @return array
     */
   /**
 * Create a Postman endpoint.
 *
 * @param string $method
 * @param string $url
 * @param string $description
 * @param string|null $functionName
 * @return array
 */
protected function createEndpoint(string $method, string $url, string $description, ?string $functionName = null): array
{
    // Handle search filters for the index endpoint
    if ($functionName === 'index') {
        $repositoryClass = $this->getRepositoryClassFromUrl($url);
        if ($repositoryClass) {
            // Access static variables for searchable and sortable fields
            $searchableFields = $repositoryClass::$searchable ?? [];
            $sortableFields = $repositoryClass::$sortable ?? [];

            // Build search and sort query parameters
            $queryParams = [];
            if (!empty($searchableFields)) {
                // Example search query: name=John (matches your index method logic)
                $queryParams['name'] = 'John'; 
            }
            if (!empty($sortableFields)) {
                $queryParams['sort'] = 'created_at';
                $queryParams['direction'] = 'desc';
            }
            $queryParams['paginate'] = 'true';
            $queryParams['per_page'] = '10';

            // Append query parameters to the URL only once
            $url = strtok($url, '?'); // Strip existing query parameters
            $url .= '?' . http_build_query($queryParams);
        }
    }

    return [
        'name' => $description,
        'request' => [
            'method' => strtoupper($method),
            'header' => [
                [
                    'key' => 'Accept',
                    'value' => 'application/json',
                ],
            ],
            'url' => [
                'raw' => '{{base_url}}' . $url,
                'host' => ['{{base_url}}'],
                'path' => explode('/', trim($url, '/')),
                'query' => $this->extractQueryParams($url),
            ],
        ],
    ];
}

/**
 * Extract query parameters from a URL.
 *
 * @param string $url
 * @return array
 */
protected function extractQueryParams(string $url): array
{
    $queryParams = [];
    $queryString = parse_url($url, PHP_URL_QUERY);
    if ($queryString) {
        parse_str($queryString, $queryParams);
    }

    $query = [];
    foreach ($queryParams as $key => $value) {
        $query[] = [
            'key' => $key,
            'value' => $value,
        ];
    }

    return $query;
}

/**
 * Get the repository class from the URL.
 *
 * @param string $url
 * @return string|null
 */
protected function getRepositoryClassFromUrl(string $url): ?string
{
    $path = explode('/', trim($url, '/'));
    $resourceName = $path[1] ?? null;
    if ($resourceName) {
        $repositoryClass = 'App\\Repositories\\' . ucfirst($resourceName) . 'Repository';
        if (class_exists($repositoryClass)) {
            return $repositoryClass;
        }
    }
    return null;
}

    protected function getExampleValue(string $type)
{
    
    switch ($type) {
        case 'string':
            return 'example_string';
        case 'integer':
            return 123;
        case 'boolean':
            return true;
        case 'array':
            return ['example_item'];
        case 'number':
            return 123.45;
        default:
            return 'example_value';
    }
}
}