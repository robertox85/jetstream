<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Documento;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DocumentoTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_create_Documento()
    {
        $model = Documento::factory()->create();

        $this->assertDatabaseHas('documenti', [
            'id' => $model->id
        ]);
    }

    /** @test */
    public function can_update_Documento()
    {
        $model = Documento::factory()->create();

        $model->update([
            // Add update fields
        ]);

        $this->assertDatabaseHas('documenti', [
            'id' => $model->id,
            // Add assertions
        ]);
    }

    /** @test */
    public function can_delete_Documento()
    {
        $model = Documento::factory()->create();

        $model->delete();

        $this->assertSoftDeleted($model);
    }

        /** @test */
    public function can_set_pratica_id_field()
    {
        $value = $this->faker->word;
        $model = Documento::factory()->create([
            'pratica_id' => $value
        ]);

        $this->assertEquals($value, $model->pratica_id);
    }
    /** @test */
    public function can_set_user_id_field()
    {
        $value = $this->faker->word;
        $model = Documento::factory()->create([
            'user_id' => $value
        ]);

        $this->assertEquals($value, $model->user_id);
    }
    /** @test */
    public function can_set_categoria_id_field()
    {
        $value = $this->faker->word;
        $model = Documento::factory()->create([
            'categoria_id' => $value
        ]);

        $this->assertEquals($value, $model->categoria_id);
    }
    /** @test */
    public function can_set_file_path_field()
    {
        $value = $this->faker->word;
        $model = Documento::factory()->create([
            'file_path' => $value
        ]);

        $this->assertEquals($value, $model->file_path);
    }
    /** @test */
    public function can_set_original_name_field()
    {
        $value = $this->faker->word;
        $model = Documento::factory()->create([
            'original_name' => $value
        ]);

        $this->assertEquals($value, $model->original_name);
    }
    /** @test */
    public function can_set_mime_type_field()
    {
        $value = $this->faker->word;
        $model = Documento::factory()->create([
            'mime_type' => $value
        ]);

        $this->assertEquals($value, $model->mime_type);
    }
    /** @test */
    public function can_set_size_field()
    {
        $value = $this->faker->word;
        $model = Documento::factory()->create([
            'size' => $value
        ]);

        $this->assertEquals($value, $model->size);
    }
    /** @test */
    public function can_set_descrizione_field()
    {
        $value = $this->faker->word;
        $model = Documento::factory()->create([
            'descrizione' => $value
        ]);

        $this->assertEquals($value, $model->descrizione);
    }
    /** @test */
    public function can_set_hash_file_field()
    {
        $value = $this->faker->word;
        $model = Documento::factory()->create([
            'hash_file' => $value
        ]);

        $this->assertEquals($value, $model->hash_file);
    }


        /** @test */
    public function it_has_pratica_relation()
    {
        $model = Documento::factory()->create();
        $this->assertInstanceOf(Relation::class, $model->pratica());
    }
    /** @test */
    public function it_has_uploader_relation()
    {
        $model = Documento::factory()->create();
        $this->assertInstanceOf(Relation::class, $model->uploader());
    }
    /** @test */
    public function it_has_categoria_relation()
    {
        $model = Documento::factory()->create();
        $this->assertInstanceOf(Relation::class, $model->categoria());
    }

}