<?php

namespace Database\Factories;

use App\Models\File;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\File>
 */
class FileFactory extends Factory
{
    protected $model = File::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $extensions = ['pdf', 'zip', 'jpg', 'png', 'mp4', 'mp3', 'doc', 'txt'];
        $extension = $this->faker->randomElement($extensions);
        $filename = $this->faker->slug() . '.' . $extension;
        
        return [
            'disk' => $this->faker->randomElement(['public', 'local']),
            'path' => 'uploads/' . $filename,
            'original_name' => $this->faker->words(2, true) . '.' . $extension,
            'size_bytes' => $this->faker->numberBetween(1024, 10485760), // 1KB to 10MB
            'checksum' => $this->faker->sha256(),
        ];
    }

    /**
     * Indicate that the file is stored on public disk.
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'disk' => 'public',
        ]);
    }

    /**
     * Indicate that the file is stored on local disk.
     */
    public function local(): static
    {
        return $this->state(fn (array $attributes) => [
            'disk' => 'local',
        ]);
    }

    /**
     * Create a PDF file.
     */
    public function pdf(): static
    {
        return $this->state(function (array $attributes) {
            $filename = $this->faker->slug() . '.pdf';
            return [
                'path' => 'uploads/' . $filename,
                'original_name' => $this->faker->words(2, true) . '.pdf',
            ];
        });
    }

    /**
     * Create a ZIP file.
     */
    public function zip(): static
    {
        return $this->state(function (array $attributes) {
            $filename = $this->faker->slug() . '.zip';
            return [
                'path' => 'uploads/' . $filename,
                'original_name' => $this->faker->words(2, true) . '.zip',
            ];
        });
    }
}
