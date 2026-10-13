<?php

namespace Database\Factories;

use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
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
            'template' => fake()->randomElement([null, 'default']),
            'status' => $status,
            'published_at' => $status === 'published' ? now() : null,
        ];
    }

    /**
     * Konfigurasikan kondisi setelah pembuatan (data pengembangan).
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Page $page) {
            if ($page->translations()->exists()) {
                return;
            }

            $title = Str::title(fake()->unique()->words(3, true));

            $page->translations()->create([
                'locale' => config('app.locale'),
                'title' => $title,
                'slug' => Str::slug($title),
                'summary' => fake()->sentence(),
                'content' => fake()->paragraphs(3, true),
            ]);
        });
    }
}
