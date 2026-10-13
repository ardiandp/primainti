<?php

namespace Database\Factories;

use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProductCategory>
 */
class ProductCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'status' => fake()->randomElement(['draft', 'published']),
        ];
    }

    /**
     * Konfigurasikan kondisi setelah pembuatan (data pengembangan).
     */
    public function configure(): static
    {
        return $this->afterCreating(function (ProductCategory $category) {
            if ($category->translations()->exists()) {
                return;
            }

            $name = Str::title(fake()->unique()->words(2, true));

            $category->translations()->create([
                'locale' => config('app.locale'),
                'name' => $name,
                'slug' => Str::slug($name),
            ]);
        });
    }
}
