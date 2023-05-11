<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "title" => $this->title,
            "tags" => explode(";", $this->tags),
            "content" => $this->content,
            "createdAt" => date_format($this->created_at, "Y-m-d H:i:s"),
            "UpdatedAt" => date_format($this->updated_at, "Y-m-d H:i:s")
        ];
    }
}
