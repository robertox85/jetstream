<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Pratica;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PraticaTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fillable_fields()
    {
        $model = new Pratica;
        $expected = [
            // Add expected fillable fields
        ];

        $this->assertEquals($expected, $model->getFillable());
    }

        /** @test */
    public function can_set_numero_pratica_field()
    {
        $value = $this->faker->word;
        $model = Pratica::factory()->create([
            'numero_pratica' => $value
        ]);

        $this->assertEquals($value, $model->numero_pratica);
    }
    /** @test */
    public function can_set_nome_field()
    {
        $value = $this->faker->word;
        $model = Pratica::factory()->create([
            'nome' => $value
        ]);

        $this->assertEquals($value, $model->nome);
    }
    /** @test */
    public function can_set_tipologia_field()
    {
        $value = $this->faker->word;
        $model = Pratica::factory()->create([
            'tipologia' => $value
        ]);

        $this->assertEquals($value, $model->tipologia);
    }
    /** @test */
    public function can_set_competenza_field()
    {
        $value = $this->faker->word;
        $model = Pratica::factory()->create([
            'competenza' => $value
        ]);

        $this->assertEquals($value, $model->competenza);
    }
    /** @test */
    public function can_set_ruolo_generale_field()
    {
        $value = $this->faker->word;
        $model = Pratica::factory()->create([
            'ruolo_generale' => $value
        ]);

        $this->assertEquals($value, $model->ruolo_generale);
    }
    /** @test */
    public function can_set_giudice_field()
    {
        $value = $this->faker->word;
        $model = Pratica::factory()->create([
            'giudice' => $value
        ]);

        $this->assertEquals($value, $model->giudice);
    }
    /** @test */
    public function can_set_stato_field()
    {
        $value = $this->faker->word;
        $model = Pratica::factory()->create([
            'stato' => $value
        ]);

        $this->assertEquals($value, $model->stato);
    }
    /** @test */
    public function can_set_altri_riferimenti_field()
    {
        $value = $this->faker->word;
        $model = Pratica::factory()->create([
            'altri_riferimenti' => $value
        ]);

        $this->assertEquals($value, $model->altri_riferimenti);
    }
    /** @test */
    public function can_set_priority_field()
    {
        $value = $this->faker->word;
        $model = Pratica::factory()->create([
            'priority' => $value
        ]);

        $this->assertEquals($value, $model->priority);
    }
    /** @test */
    public function can_set_data_apertura_field()
    {
        $value = $this->faker->word;
        $model = Pratica::factory()->create([
            'data_apertura' => $value
        ]);

        $this->assertEquals($value, $model->data_apertura);
    }
    /** @test */
    public function can_set_team_id_field()
    {
        $value = $this->faker->word;
        $model = Pratica::factory()->create([
            'team_id' => $value
        ]);

        $this->assertEquals($value, $model->team_id);
    }


        /** @test */
    public function it_has_team_relation()
    {
        $model = Pratica::factory()->create();
        $this->assertInstanceOf(Relation::class, $model->team());
    }
    /** @test */
    public function it_has_udienze_relation()
    {
        $model = Pratica::factory()->create();
        $this->assertInstanceOf(Relation::class, $model->udienze());
    }
    /** @test */
    public function it_has_scadenze_relation()
    {
        $model = Pratica::factory()->create();
        $this->assertInstanceOf(Relation::class, $model->scadenze());
    }
    /** @test */
    public function it_has_documenti_relation()
    {
        $model = Pratica::factory()->create();
        $this->assertInstanceOf(Relation::class, $model->documenti());
    }
    /** @test */
    public function it_has_note_relation()
    {
        $model = Pratica::factory()->create();
        $this->assertInstanceOf(Relation::class, $model->note());
    }
    /** @test */
    public function it_has_anagrafiche_relation()
    {
        $model = Pratica::factory()->create();
        $this->assertInstanceOf(Relation::class, $model->anagrafiche());
    }
    /** @test */
    public function it_has_assistiti_relation()
    {
        $model = Pratica::factory()->create();
        $this->assertInstanceOf(Relation::class, $model->assistiti());
    }
    /** @test */
    public function it_has_controparti_relation()
    {
        $model = Pratica::factory()->create();
        $this->assertInstanceOf(Relation::class, $model->controparti());
    }

}