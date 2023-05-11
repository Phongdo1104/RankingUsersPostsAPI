<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // 'id' => $this->id,
            'name' => $this->first_name . " " . $this->last_name,
            'email' => $this->email,
            'avatar' => str_contains($this->avatar, "avatars") ? asset("storage/". $this->avatar) : $this->avatar,
            'createdAt' => date_format($this->created_at, "Y-m-d H:i:s"),
            'updatedAt' => date_format($this->updated_at, "Y-m-d H:i:s"),
        ];
    }
}
