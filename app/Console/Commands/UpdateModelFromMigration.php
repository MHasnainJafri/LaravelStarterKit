<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class UpdateModelFromMigration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:model {model} {--table=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recreate a model based on its database table schema';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $model = $this->argument('model');
        $table = $this->option('table') ?? strtolower($model.'s');

        if (! $this->validateTable($table)) {
            $this->error("Table '{$table}' does not exist.");

            return;
        }

        $columns = $this->getTableColumns($table);
        $foreignKeys = $this->getTableForeignKeys($table);
        $columnTypes = $this->getTableColumnTypes($table);

        $this->recreateModel($model, $columns, $foreignKeys, $columnTypes);
    }

    protected function validateTable($table)
    {
        return DB::getSchemaBuilder()->hasTable($table);
    }

    protected function getTableColumns($table)
    {
        return DB::getSchemaBuilder()->getColumnListing($table);
    }

    protected function getTableForeignKeys($table)
    {
        $foreignKeys = [];
        $schema = DB::select("SHOW CREATE TABLE {$table}");
        if (! empty($schema)) {
            $createTableQuery = $schema[0]->{'Create Table'};
            preg_match_all('/CONSTRAINT.*FOREIGN KEY \(`(.*?)`\)/', $createTableQuery, $matches);
            $foreignKeys = $matches[1] ?? [];
        }

        return $foreignKeys;
    }

    protected function getTableColumnTypes($table)
    {
        $columns = DB::select("DESCRIBE {$table}");
        $columnTypes = [];
        foreach ($columns as $column) {
            $type = strtok($column->Type, '('); // Extract type before '('
            $columnTypes[$column->Field] = $type;
        }

        return $columnTypes;
    }

    protected function recreateModel($model, $columns, $foreignKeys, $columnTypes)
    {
        $modelPath = app_path("Models/{$model}.php");

        $newContent = '';

        if (File::exists($modelPath)) {
            $existingContent = File::get($modelPath);

            // Check for `fillable` and append if missing
            $fillable = implode("', '", $columns);
            $fillableProperty = <<<EOL
        /**
         * The attributes that are mass assignable.
         *
         * @var list<string>
         */
        protected \$fillable = ['{$fillable}'];
    EOL;

            if (! str_contains($existingContent, '$fillable')) {
                $newContent .= "\n\n{$fillableProperty}";
            }

            // Check for `casts` and append if missing
            $casts = $this->generateCastsArray($columnTypes);
            $castsProperty = <<<EOL
        /**
         * The attributes that should be cast.
         *
         * @var array<string, string>
         */
        protected \$casts = {$this->formatArray($casts)};
    EOL;

            if (! str_contains($existingContent, '$casts')) {
                $newContent .= "\n\n{$castsProperty}";
            }

            // Append new relationships
            $relationships = '';
            $this->reverseRelationGenerator($foreignKeys);

            foreach ($foreignKeys as $foreignKey) {
                $relatedModel = $this->guessModelName($foreignKey);
                $relationName = lcfirst($relatedModel);

                if (! str_contains($existingContent, "function {$relationName}")) {
                    $relationships .= <<<EOL
    
        /**
         * Get the related {$relatedModel}.
         */
        public function {$relationName}()
        {
            return \$this->belongsTo({$relatedModel}::class);
        }
    EOL;
                }
            }

            $newContent .= $relationships;

            // Insert new content before the closing class brace
            $existingContent = preg_replace('/}\s*$/', "{$newContent}\n}", $existingContent);

            File::put($modelPath, $existingContent);
            $this->info("Model '{$model}' has been updated successfully.");

            // Run pint for linting the generated model file
            $this->info('Running Pint for linting...');
            exec(__DIR__.'/../../../vendor/bin/pint '.escapeshellarg($modelPath), $output, $status);
            // Show output of Pint command
            if ($status === 0) {
                $this->info('Pint linting passed successfully!');
            } else {
                $this->error('Pint linting failed:');
                $this->error(implode("\n", $output));
            }
        } else {
            // If the file doesn't exist, create it from scratch
            $this->info("Model '{$model}' does not exist. Creating a new one...");
            $this->createNewModel($model, $columns, $foreignKeys, $columnTypes);
        }
    }

    protected function createNewModel($model, $columns, $foreignKeys, $columnTypes)
    {
        $modelPath = app_path("Models/{$model}.php");

        // Prepare namespace and class definition
        $namespace = "namespace App\Models;";
        $useStatements = <<<EOL
    use Illuminate\Database\Eloquent\Model;
    EOL;

        $classDefinition = <<<EOL
    class {$model} extends Model
    {
    EOL;

        // Generate properties
        $fillable = implode("', '", $columns);
        $fillableProperty = <<<EOL
        /**
         * The attributes that are mass assignable.
         *
         * @var list<string>
         */
        protected \$fillable = ['{$fillable}'];
    EOL;

        $casts = $this->generateCastsArray($columnTypes);
        $castsProperty = <<<EOL
        /**
         * The attributes that should be cast.
         *
         * @var array<string, string>
         */
        protected \$casts = {$this->formatArray($casts)};
    EOL;

        // Add relationships
        $relationships = '';
        $this->reverseRelationGenerator($foreignKeys);
        foreach ($foreignKeys as $foreignKey) {
            $relatedModel = $this->guessModelName($foreignKey);
            $relationName = lcfirst($relatedModel);
            $relationships .= <<<EOL
    
        /**
         * Get the related {$relatedModel}.
         */
        public function {$relationName}()
        {
            return \$this->belongsTo({$relatedModel}::class);
        }
    EOL;
        }

        // Generate the final model content
        $modelContent = <<<EOL
    <?php
    
    {$namespace}
    
    {$useStatements}
    
    {$classDefinition}
    
    {$fillableProperty}
    
    
    {$castsProperty}
    
    {$relationships}
    
    }
    EOL;

        File::put($modelPath, $modelContent);
        $this->info("Model '{$model}' has been created successfully at {$modelPath}");

        // Run pint for linting the generated model file
        $this->info('Running Pint for linting...');
        exec(__DIR__.'/../../../vendor/bin/pint '.escapeshellarg($modelPath), $output, $status);
        // Show output of Pint command
        if ($status === 0) {
            $this->info('Pint linting passed successfully!');
        } else {
            $this->error('Pint linting failed:');
            $this->error(implode("\n", $output));
        }

    }

    protected function formatArray(array $array)
    {
        return var_export($array, true);
    }

    protected function generateCastsArray($columnTypes)
    {
        $casts = [];
        foreach ($columnTypes as $column => $type) {
            switch ($type) {
                case 'integer':
                case 'bigint':
                case 'smallint':
                    $casts[$column] = 'integer';
                    break;
                case 'decimal':
                case 'float':
                case 'double':
                    $casts[$column] = 'float';
                    break;
                case 'boolean':
                    $casts[$column] = 'boolean';
                    break;
                case 'datetime':
                case 'timestamp':
                    $casts[$column] = 'datetime';
                    break;
                case 'json':
                    $casts[$column] = 'array';
                    break;
                default:
                    break;
            }
        }

        return $casts;
    }

    private function reverseRelationGenerator($foreignKeys)
    {
        foreach ($foreignKeys as $foreignKey) {
            $relatedModel = $this->guessModelName($foreignKey);
            $relationName = $this->ask("Please enter the relation name for {$relatedModel} [{$foreignKey}]");
            $relationType = $this->ask("Please enter the relation name for {$foreignKey} [{$relatedModel}] (default: hasOne)", 'hasOne');
            $reverseRelation = <<<EOL
    
        /**
         * Get the related {$relatedModel}.
         */
        public function {$relationName}()
        {
            return \$this->{$relationType}({$this->guessModelName($foreignKey)}::class);
        }
    }
    EOL;
            $existingContent = File::get(app_path("Models/{$relatedModel}.php"));
            if (! str_contains($existingContent, "\$this->{$relationType}({$this->guessModelName($foreignKey)}::class)")) {
                $filePath = app_path("Models/{$relatedModel}.php");

                // Read the existing content of the file
                $existingContent = File::get($filePath);

                // Ensure we find the last closing brace `}` and insert $reverseRelation before it
                $existingContent = preg_replace('/}\s*$/', "{$reverseRelation}\n", $existingContent);

                // Write the modified content back to the file
                File::put($filePath, $existingContent);

            }

        }
    }

    protected function guessModelName($foreignKey)
    {
        return ucfirst(str_replace('_id', '', $foreignKey));
    }
}
