<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'author' => $this->author,
            'isbn' => $this->isbn,
            'published_date' => $this->published_date,
            'description' => $this->description,
            'image_url' => $this->image_url,
            'genres' => $this->genres->map(fn($genre) => [
                'id' => $genre->id,
                'name' => $genre->name,
            ]),
            'average_rating' => round($this->reviews()->avg('rating') ?? 0, 1),
            'reviews_count' => $this->reviews()->count(),
            'reviews' => $this->reviews->map(fn($review) => [
                'id' => $review->id,
                'user_name' => $review->user->name ?? '不明なユーザー',
                'rating' => $review->rating,
                'comment' => $review->comment,
                'created_at' => $review->created_at->toDateTimeString(),
            ]),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}