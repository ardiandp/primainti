<?php

namespace Database\Factories;

use App\Models\Facility;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Facility>
 */
class FacilityFactory extends Factory
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
            'status' => $status,
            'published_at' => $status === 'published' ? now() : null,
        ];
    }

    /**
     * Konfigurasikan kondisi setelah pembuatan (data pengembangan).
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Facility $facility) {
            if ($facility->translations()->exists()) {
                return;
            }

            $name = Str::title(fake()->unique()->words(3, true));

            $facility->translations()->create([
                'locale' => config('app.locale'),
                'name' => $name,
                'slug' => Str::slug($name),
                'description' => fake()->paragraph(),
            ]);
        });
    }
}
