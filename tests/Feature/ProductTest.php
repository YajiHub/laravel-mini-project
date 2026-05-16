<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_admin_can_create_product()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::factory()->create();

        $response = $this->actingAs($admin)->post('/products', [
            'name'                => 'Portland Cement 40kg',
            'sku'                 => 'CEM-001',
            'category_id'         => $category->id,
            'price'               => 285.00,
            'quantity'            => 50,
            'low_stock_threshold' => 10,
            'unit'                => 'bag',
        ]);

        $response->assertRedirect('/products');
        $this->assertDatabaseHas('products', ['sku' => 'CEM-001']);
    }

    public function test_guest_cannot_access_products()
    {
        $response = $this->get('/products');
        $response->assertRedirect('/login');
    }

}
