<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Anagrafica;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AnagraficaTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fillable_fields()
    {
        $model = new Anagrafica;
        $expected = [
            // Add expected fillable fields
        ];

        $this->assertEquals($expected, $model->getFillable());
    }

        /** @test */
    public function can_set_type_field()
    {
        $value = $this->faker->word;
        $model = Anagrafica::factory()->create([
            'type' => $value
        ]);

        $this->assertEquals($value, $model->type);
    }
    /** @test */
    public function can_set_tipo_utente_field()
    {
        $value = $this->faker->word;
        $model = Anagrafica::factory()->create([
            'tipo_utente' => $value
        ]);

        $this->assertEquals($value, $model->tipo_utente);
    }
    /** @test */
    public function can_set_denominazione_field()
    {
        $value = $this->faker->word;
        $model = Anagrafica::factory()->create([
            'denominazione' => $value
        ]);

        $this->assertEquals($value, $model->denominazione);
    }
    /** @test */
    public function can_set_nome_field()
    {
        $value = $this->faker->word;
        $model = Anagrafica::factory()->create([
            'nome' => $value
        ]);

        $this->assertEquals($value, $model->nome);
    }
    /** @test */
    public function can_set_cognome_field()
    {
        $value = $this->faker->word;
        $model = Anagrafica::factory()->create([
            'cognome' => $value
        ]);

        $this->assertEquals($value, $model->cognome);
    }
    /** @test */
    public function can_set_indirizzo_field()
    {
        $value = $this->faker->word;
        $model = Anagrafica::factory()->create([
            'indirizzo' => $value
        ]);

        $this->assertEquals($value, $model->indirizzo);
    }
    /** @test */
    public function can_set_codice_postale_field()
    {
        $value = $this->faker->word;
        $model = Anagrafica::factory()->create([
            'codice_postale' => $value
        ]);

        $this->assertEquals($value, $model->codice_postale);
    }
    /** @test */
    public function can_set_citta_field()
    {
        $value = $this->faker->word;
        $model = Anagrafica::factory()->create([
            'citta' => $value
        ]);

        $this->assertEquals($value, $model->citta);
    }
    /** @test */
    public function can_set_provincia_field()
    {
        $value = $this->faker->word;
        $model = Anagrafica::factory()->create([
            'provincia' => $value
        ]);

        $this->assertEquals($value, $model->provincia);
    }
    /** @test */
    public function can_set_telefono_field()
    {
        $value = $this->faker->word;
        $model = Anagrafica::factory()->create([
            'telefono' => $value
        ]);

        $this->assertEquals($value, $model->telefono);
    }
    /** @test */
    public function can_set_cellulare_field()
    {
        $value = $this->faker->word;
        $model = Anagrafica::factory()->create([
            'cellulare' => $value
        ]);

        $this->assertEquals($value, $model->cellulare);
    }
    /** @test */
    public function can_set_email_field()
    {
        $value = $this->faker->word;
        $model = Anagrafica::factory()->create([
            'email' => $value
        ]);

        $this->assertEquals($value, $model->email);
    }
    /** @test */
    public function can_set_pec_field()
    {
        $value = $this->faker->word;
        $model = Anagrafica::factory()->create([
            'pec' => $value
        ]);

        $this->assertEquals($value, $model->pec);
    }
    /** @test */
    public function can_set_codice_fiscale_field()
    {
        $value = $this->faker->word;
        $model = Anagrafica::factory()->create([
            'codice_fiscale' => $value
        ]);

        $this->assertEquals($value, $model->codice_fiscale);
    }
    /** @test */
    public function can_set_partita_iva_field()
    {
        $value = $this->faker->word;
        $model = Anagrafica::factory()->create([
            'partita_iva' => $value
        ]);

        $this->assertEquals($value, $model->partita_iva);
    }
    /** @test */
    public function can_set_codice_univoco_destinatario_field()
    {
        $value = $this->faker->word;
        $model = Anagrafica::factory()->create([
            'codice_univoco_destinatario' => $value
        ]);

        $this->assertEquals($value, $model->codice_univoco_destinatario);
    }
    /** @test */
    public function can_set_nota_field()
    {
        $value = $this->faker->word;
        $model = Anagrafica::factory()->create([
            'nota' => $value
        ]);

        $this->assertEquals($value, $model->nota);
    }


        /** @test */
    public function it_has_pratiche_relation()
    {
        $model = Anagrafica::factory()->create();
        $this->assertInstanceOf(Relation::class, $model->pratiche());
    }

}