<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use Hotash\Authable\Registrar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Jetstream\Features;
use Tests\TestCase;

class DeleteAccountTest extends TestCase
{
    use RefreshDatabase;

    protected ?string $guard = 'admin';

    public function test_admin_accounts_can_be_deleted()
    {
        if (! in_array(Features::accountDeletion(), Registrar::features(guard: $this->guard, key: 'jetstream'))) {
            return $this->markTestSkipped('Account deletion is not enabled.');
        }

        $this->actingAs($user = Admin::factory()->create(), $this->guard);

        $response = $this->delete('/user', [
            'password' => 'password',
        ]);

        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_before_account_can_be_deleted()
    {
        if (! in_array(Features::accountDeletion(), Registrar::features(guard: $this->guard, key: 'jetstream'))) {
            return $this->markTestSkipped('Account deletion is not enabled.');
        }

        $this->actingAs($user = Admin::factory()->create(), $this->guard);

        $response = $this->delete('/user', [
            'password' => 'wrong-password',
        ]);

        $this->assertNotNull($user->fresh());
    }
}
