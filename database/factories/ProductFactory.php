<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(['draft', 'published']);

        return [
            'product_category_id' => ProductCategory::factory(),
            'sku' => strtoupper(fake()->unique()->bothify('SKU-####??')),
            'status' => $status,
            'published_at' => $status === 'published' ? now() : null,
        ];
    }

    /**
     * Konfigurasikan kondisi setelah pembuatan (data pengembangan).
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Product $product) {
            if ($product->translations()->exists()) {
                return;
            }

            $name = Str::title(fake()->unique()->words(3, true));

            $product->translations()->create([
                'locale' => config('app.locale'),
                'name' => $name,
                'slug' => Str::slug($name),
                'short_description' => fake()->sentence(),
                'description' => fake()->paragraphs(3, true),
            ]);
        });
    }
}
