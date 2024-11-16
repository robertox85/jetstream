<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Nota;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NotaTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_create_Nota()
    {
        $model = Nota::factory()->create();

        $this->assertDatabaseHas('note', [
            'id' => $model->id
        ]);
    }

    /** @test */
    public function can_update_Nota()
    {
        $model = Nota::factory()->create();

        $model->update([
            // Add update fields
        ]);

        $this->assertDatabaseHas('note', [
            'id' => $model->id,
            // Add assertions
        ]);
    }

    /** @test */
    public function can_delete_Nota()
    {
        $model = Nota::factory()->create();

        $model->delete();

        $this->assertSoftDeleted($model);
    }

        /** @test */
    public function can_set_pratica_id_field()
    {
        $value = $this->faker->word;
        $model = Nota::factory()->create([
            'pratica_id' => $value
        ]);

        $this->assertEquals($value, $model->pratica_id);
    }
    /** @test */
    public function can_set_user_id_field()
    {
        $value = $this->faker->word;
        $model = Nota::factory()->create([
            'user_id' => $value
        ]);

        $this->assertEquals($value, $model->user_id);
    }
    /** @test */
    public function can_set_last_edited_by_field()
    {
        $value = $this->faker->word;
        $model = Nota::factory()->create([
            'last_edited_by' => $value
        ]);

        $this->assertEquals($value, $model->last_edited_by);
    }
    /** @test */
    public function can_set_oggetto_field()
    {
        $value = $this->faker->word;
        $model = Nota::factory()->create([
            'oggetto' => $value
        ]);

        $this->assertEquals($value, $model->oggetto);
    }
    /** @test */
    public function can_set_nota_field()
    {
        $value = $this->faker->word;
        $model = Nota::factory()->create([
            'nota' => $value
        ]);

        $this->assertEquals($value, $model->nota);
    }
    /** @test */
    public function can_set_tipologia_field()
    {
        $value = $this->faker->word;
        $model = Nota::factory()->create([
            'tipologia' => $value
        ]);

        $this->assertEquals($value, $model->tipologia);
    }
    /** @test */
    public function can_set_visibilita_field()
    {
        $value = $this->faker->word;
        $model = Nota::factory()->create([
            'visibilita' => $value
        ]);

        $this->assertEquals($value, $model->visibilita);
    }


        /** @test */
    public function it_has_pratica_relation()
    {
        $model = Nota::factory()->create();
        $this->assertInstanceOf(Relation::class, $model->pratica());
    }
    /** @test */
    public function it_has_creator_relation()
    {
        $model = Nota::factory()->create();
        $this->assertInstanceOf(Relation::class, $model->creator());
    }
    /** @test */
    public function it_has_lastEditor_relation()
    {
        $model = Nota::factory()->create();
        $this->assertInstanceOf(Relation::class, $model->lastEditor());
    }

}