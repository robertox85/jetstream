<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Udienza;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UdienzaTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_create_Udienza()
    {
        $model = Udienza::factory()->create();

        $this->assertDatabaseHas('udienze', [
            'id' => $model->id
        ]);
    }

    /** @test */
    public function can_update_Udienza()
    {
        $model = Udienza::factory()->create();

        $model->update([
            // Add update fields
        ]);

        $this->assertDatabaseHas('udienze', [
            'id' => $model->id,
            // Add assertions
        ]);
    }

    /** @test */
    public function can_delete_Udienza()
    {
        $model = Udienza::factory()->create();

        $model->delete();

        $this->assertSoftDeleted($model);
    }

        /** @test */
    public function can_set_pratica_id_field()
    {
        $value = $this->faker->word;
        $model = Udienza::factory()->create([
            'pratica_id' => $value
        ]);

        $this->assertEquals($value, $model->pratica_id);
    }
    /** @test */
    public function can_set_user_id_field()
    {
        $value = $this->faker->word;
        $model = Udienza::factory()->create([
            'user_id' => $value
        ]);

        $this->assertEquals($value, $model->user_id);
    }
    /** @test */
    public function can_set_assigned_to_field()
    {
        $value = $this->faker->word;
        $model = Udienza::factory()->create([
            'assigned_to' => $value
        ]);

        $this->assertEquals($value, $model->assigned_to);
    }
    /** @test */
    public function can_set_data_ora_field()
    {
        $value = $this->faker->word;
        $model = Udienza::factory()->create([
            'data_ora' => $value
        ]);

        $this->assertEquals($value, $model->data_ora);
    }
    /** @test */
    public function can_set_motivo_field()
    {
        $value = $this->faker->word;
        $model = Udienza::factory()->create([
            'motivo' => $value
        ]);

        $this->assertEquals($value, $model->motivo);
    }
    /** @test */
    public function can_set_luogo_field()
    {
        $value = $this->faker->word;
        $model = Udienza::factory()->create([
            'luogo' => $value
        ]);

        $this->assertEquals($value, $model->luogo);
    }
    /** @test */
    public function can_set_stato_field()
    {
        $value = $this->faker->word;
        $model = Udienza::factory()->create([
            'stato' => $value
        ]);

        $this->assertEquals($value, $model->stato);
    }
    /** @test */
    public function can_set_reminder_at_field()
    {
        $value = $this->faker->word;
        $model = Udienza::factory()->create([
            'reminder_at' => $value
        ]);

        $this->assertEquals($value, $model->reminder_at);
    }
    /** @test */
    public function can_set_email_notification_field()
    {
        $value = $this->faker->word;
        $model = Udienza::factory()->create([
            'email_notification' => $value
        ]);

        $this->assertEquals($value, $model->email_notification);
    }
    /** @test */
    public function can_set_browser_notification_field()
    {
        $value = $this->faker->word;
        $model = Udienza::factory()->create([
            'browser_notification' => $value
        ]);

        $this->assertEquals($value, $model->browser_notification);
    }


        /** @test */
    public function it_has_pratica_relation()
    {
        $model = Udienza::factory()->create();
        $this->assertInstanceOf(Relation::class, $model->pratica());
    }
    /** @test */
    public function it_has_creator_relation()
    {
        $model = Udienza::factory()->create();
        $this->assertInstanceOf(Relation::class, $model->creator());
    }
    /** @test */
    public function it_has_avvocatoAssegnato_relation()
    {
        $model = Udienza::factory()->create();
        $this->assertInstanceOf(Relation::class, $model->avvocatoAssegnato());
    }

}