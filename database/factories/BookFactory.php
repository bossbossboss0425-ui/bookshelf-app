<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookFactory extends Factory
{
    protected $model = Book::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->realText(20),
            'author' => $this->faker->name(),
            'isbn' => $this->faker->isbn13(),
            'published_date' => $this->faker->date(),
            'description' => $this->faker->realText(100),
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=1',
        ];
    }
}
