<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeModelTests extends Command
{
    protected $signature = 'make:model-tests {model : Il nome del model}';
    protected $description = 'Crea test automatici per un model';

    public function handle()
    {
        $modelName = $this->argument('model');
        $modelClass = "App\\Models\\$modelName";

        if (!class_exists($modelClass)) {
            $this->error("Il model $modelName non esiste!");
            return;
        }

        $model = new $modelClass;
        $table = $model->getTable();
        $fillable = $model->getFillable();
        $relations = $this->getModelRelations($model);

        // Crea Feature Test
        $this->createFeatureTest($modelName, $table, $fillable, $relations);

        // Crea Unit Test
        $this->createUnitTest($modelName, $table, $fillable, $relations);

        $this->info("Test creati con successo per il model $modelName!");
    }

    protected function createFeatureTest($modelName, $table, $fillable, $relations)
    {
        $stub = File::get(__DIR__ . '/stubs/feature-test.stub');

        $stub = str_replace(
            ['{{modelName}}', '{{tableName}}', '{{fillableFields}}', '{{relations}}'],
            [
                $modelName,
                $table,
                $this->generateFillableTests($fillable),
                $this->generateRelationTests($relations)
            ],
            $stub
        );

        $testPath = base_path("tests/Feature/{$modelName}Test.php");
        File::put($testPath, $stub);
    }

    protected function createUnitTest($modelName, $table, $fillable, $relations)
    {
        $stub = File::get(__DIR__ . '/stubs/unit-test.stub');

        $stub = str_replace(
            ['{{modelName}}', '{{tableName}}', '{{fillableFields}}', '{{relations}}'],
            [
                $modelName,
                $table,
                $this->generateFillableTests($fillable),
                $this->generateRelationTests($relations)
            ],
            $stub
        );

        $testPath = base_path("tests/Unit/{$modelName}Test.php");
        File::put($testPath, $stub);
    }

    protected function getModelRelations($model)
    {
        $relations = [];
        $methods = get_class_methods($model);

        foreach ($methods as $method) {
            $reflection = new \ReflectionMethod($model, $method);
            $contents = File::get($reflection->getFileName());
            $methodContent = $this->getMethodContent($contents, $method);

            if (
                str_contains($methodContent, 'hasOne(') ||
                str_contains($methodContent, 'hasMany(') ||
                str_contains($methodContent, 'belongsTo(') ||
                str_contains($methodContent, 'belongsToMany(')
            ) {
                $relations[] = $method;
            }
        }

        return $relations;
    }

    protected function getMethodContent($contents, $methodName)
    {
        if (preg_match("/{$methodName}\s*\(.*?\)\s*\{(.*?)\}/s", $contents, $matches)) {
            return $matches[1];
        }
        return '';
    }

    protected function generateFillableTests($fillable)
    {
        $tests = "";
        foreach ($fillable as $field) {
            $tests .= <<<EOT
    /** @test */
    public function can_set_{$field}_field()
    {
        \$value = \$this->faker->word;
        \$model = {$this->argument('model')}::factory()->create([
            '$field' => \$value
        ]);

        \$this->assertEquals(\$value, \$model->$field);
    }

EOT;
        }
        return $tests;
    }

    protected function generateRelationTests($relations)
    {
        $tests = "";
        foreach ($relations as $relation) {
            $tests .= <<<EOT
    /** @test */
    public function it_has_{$relation}_relation()
    {
        \$model = {$this->argument('model')}::factory()->create();
        \$this->assertInstanceOf(Relation::class, \$model->$relation());
    }

EOT;
        }
        return $tests;
    }
}