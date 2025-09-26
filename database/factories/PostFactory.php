<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Post::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(rand(3, 8));
        $createdAt = fake()->dateTimeBetween('-2 years', 'now');
        $isPublished = fake()->boolean(70); // 70% chance of being published
        
        $content = $this->generateBlogContent();
        
        return [
            'title' => rtrim($title, '.'),
            'slug' => Str::slug($title),
            'excerpt' => fake()->optional(0.8)->paragraph(rand(1, 2)),
            'content' => $content,
            'body' => $content,
            'status' => $isPublished ? 'published' : 'draft',
            'published_at' => $isPublished ? fake()->dateTimeBetween($createdAt, 'now') : null,
            'seo_title' => fake()->optional(0.3)->sentence(rand(4, 10)),
            'seo_description' => fake()->optional(0.3)->paragraph(1),
            'author_id' => User::factory(),
            'created_at' => $createdAt,
            'updated_at' => function (array $attributes) {
                return fake()->dateTimeBetween($attributes['created_at'], 'now');
            },
        ];
    }

    /**
     * Indicate that the post should be published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => fake()->dateTimeBetween($attributes['created_at'] ?? '-1 year', 'now'),
        ]);
    }

    /**
     * Indicate that the post should be a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    /**
     * Create a post with a specific title.
     */
    public function withTitle(string $title): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => $title,
            'slug' => Str::slug($title),
        ]);
    }

    /**
     * Create a post by a specific author.
     */
    public function byAuthor(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'author_id' => $user->id,
        ]);
    }

    /**
     * Create a post published on a specific date.
     */
    public function publishedOn(string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => $date,
        ]);
    }

    /**
     * Generate realistic blog content.
     */
    private function generateBlogContent(): string
    {
        $paragraphs = [];
        $numParagraphs = rand(3, 8);
        
        for ($i = 0; $i < $numParagraphs; $i++) {
            $paragraphs[] = fake()->paragraph(rand(3, 8));
        }
        
        // Add some variety with headers and lists
        if (rand(1, 3) === 1) {
            $insertAt = rand(1, count($paragraphs) - 1);
            array_splice($paragraphs, $insertAt, 0, [
                "\n## " . fake()->sentence(rand(2, 5)) . "\n"
            ]);
        }
        
        if (rand(1, 4) === 1) {
            $listItems = [];
            for ($j = 0; $j < rand(3, 6); $j++) {
                $listItems[] = "- " . fake()->sentence(rand(3, 8));
            }
            $insertAt = rand(1, count($paragraphs) - 1);
            array_splice($paragraphs, $insertAt, 0, [
                "\n" . implode("\n", $listItems) . "\n"
            ]);
        }
        
        return implode("\n\n", $paragraphs);
    }
}