<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminMenuTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->actingAs(User::factory()->create(['role' => 'admin']));
    }

    public function test_admin_can_create_category(): void
    {
        $this->post('/admin/categories', ['name' => 'Espresso Based', 'group' => 'kopi', 'is_active' => '1'])
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', ['slug' => 'espresso-based', 'group' => 'kopi', 'is_active' => true]);
    }

    public function test_category_with_products_cannot_be_deleted(): void
    {
        $category = Category::create(['name' => 'Kopi', 'slug' => 'kopi', 'group' => 'kopi']);
        Product::create(['category_id' => $category->id, 'name' => 'Latte', 'slug' => 'latte', 'price' => 28000]);

        $this->delete("/admin/categories/{$category->id}")->assertSessionHas('error');

        $this->assertModelExists($category);
    }

    public function test_admin_can_create_product_with_photo(): void
    {
        $category = Category::create(['name' => 'Kopi', 'slug' => 'kopi', 'group' => 'kopi']);

        $this->post('/admin/products', [
            'category_id' => $category->id,
            'name' => 'Caffe Latte',
            'price' => 28000,
            'is_available' => '1',
            'image' => UploadedFile::fake()->image('latte.jpg', 800, 600),
        ])->assertRedirect(route('admin.products.index'));

        $product = Product::firstWhere('slug', 'caffe-latte');
        $this->assertNotNull($product->image_path);
        Storage::disk('public')->assertExists($product->image_path);

        $this->get('/admin/products')->assertOk()->assertSee('Caffe Latte')->assertSee('Rp28.000');
    }

    public function test_replacing_photo_deletes_the_old_file(): void
    {
        $category = Category::create(['name' => 'Kopi', 'slug' => 'kopi', 'group' => 'kopi']);
        $old = UploadedFile::fake()->image('old.jpg')->store('products', 'public');
        $product = Product::create(['category_id' => $category->id, 'name' => 'Latte', 'slug' => 'latte', 'price' => 28000, 'image_path' => $old]);

        $this->put("/admin/products/{$product->id}", [
            'category_id' => $category->id,
            'name' => 'Latte',
            'price' => 30000,
            'image' => UploadedFile::fake()->image('new.jpg'),
        ])->assertRedirect();

        Storage::disk('public')->assertMissing($old);
        Storage::disk('public')->assertExists($product->fresh()->image_path);
        $this->assertSame(30000, $product->fresh()->price);
    }

    public function test_non_image_upload_is_rejected(): void
    {
        $category = Category::create(['name' => 'Kopi', 'slug' => 'kopi', 'group' => 'kopi']);

        $this->post('/admin/products', [
            'category_id' => $category->id,
            'name' => 'Latte',
            'price' => 28000,
            'image' => UploadedFile::fake()->create('virus.php', 10, 'application/x-php'),
        ])->assertSessionHasErrors('image');
    }
}
