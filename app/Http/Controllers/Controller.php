<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

// use OpenApi\Annotations as OA;

/**
 * @OA\OpenApi(
 *     @OA\Info(
 *         version="1.0",
 *         title="User's Rank API",
 *         description="Demo User's Rank based on the Number of Posts API",
 *     )
 * ),
 * @OA\Tag(
 *     name="Login",
 *     description="User Tag",
 * ),
 * @OA\Tag(
 *     name="User",
 *     description="User Tag",
 * ),
 * @OA\Tag(
 *     name="Post",
 *     description="Post Tag",
 * )
 */

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
