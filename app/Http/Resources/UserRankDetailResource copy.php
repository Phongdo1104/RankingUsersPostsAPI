<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *      properties={
 *          @OA\Property(
 *              property="success",
 *              type="boolean",
 *              example="true"
 *          ),
 *          @OA\Property(
 *              property="data",
 *              type="object",
 *                  @OA\Property(property="name", type="string", example="Phong DO"),
 *                  @OA\Property(property="email", type="string", example="kimphongdo1101@email.com"),
 *                  @OA\Property(property="avatar", type="string", example="avatar from the internet"),
 *                  @OA\Property(property="totalPost", type="integer", example="10"),
 *          ),
 *          @OA\Property(
 *              property="message",
 *              type="string",
 *              example="success"
 *          )
 *      }
 * )
 */
class UserRankDetailResource extends JsonResource
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
            'avatar' => $this->avatar,
            'totalPost' => $this->total_post
        ];
    }
}
