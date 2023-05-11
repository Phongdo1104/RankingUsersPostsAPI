<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Http\Requests\UserRequest\UserLoginRequest;
use App\Http\Requests\UserRequest\UserLogoutRequest;
use App\Http\Requests\UserRequest\UserSignInRequest;
use App\Http\Requests\UserRequest\UserUpdateAvatarRequest;
use App\Http\Requests\UserRequest\UserUpdateRequest;
use App\Http\Resources\UserDetailResource;
use App\Http\Resources\UserRankDetailResource;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    // public function __connstruc() {
    //     $this->middleware('auth:api', ['except' => 'login']);
    // }
    /**
     * Leaderboard User's Rank
     * 
     * @OA\Get(
     *      path="/api/ranks",
     *      tags={"User"},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     * @return UserRankDetailResource
     */
    public function ranks($data = "")
    {
        // Calculate all User's Ranks based on the Number of Posts User has posted
        $posts = User::select("users.*", "user_id", DB::raw('count( posts.user_id) as total_post'))
            ->leftJoin("posts", "users.id", "=", "posts.user_id")
            ->groupBy("users.id")
            ->orderByDesc("total_post")
            ->take(100)
            ->get();

        // Data value is used for calculate 1 User's Rank
        if (!empty($data)) {
            $rank = 0;

            // Check entire list if User index in Array
            foreach ($posts as $key => $item) {
                if ($item->user_id === $data) {
                    $rank = $key;
                    return [
                        $rank + 1,
                        $item->total_post
                    ];
                }
            }
            return;
        }

        return Response(UserRankDetailResource::collection($posts), 200);
    }

    /**
     * User's Rank
     * 
     * @OA\Get(
     *      path="/api/user/rank",
     *      tags={"User"},
     *      operationId="userRank",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      security={
     *         {"bearerAuth": {}}
     *      }
     * )
     * @return UserRankDetailResource
     */
    public function rank(UserRequest $request): Response
    {
        $user = Auth::user();
        $rank_info = $this->ranks($request->user()->id); // Calculate User's Rank

        $rank_value = [];

        // Checking if one user has created and no Post has been posted
        if (is_null($rank_info)) {
            $rank_value = [
                'rank' => 1,
                'totalPost' => 0,
            ];
        // If There is more than 1 post has been posted
        } else {
            $rank_value = [
                'rank' => $rank_info[0],
                'totalPost' => $rank_info[1]
            ];
        }

        return Response([
            'name' => $user->first_name . " " . $user->last_name,
            'email' => $user->email,
            'avatar' => $user->avatar,
            'rank' => $rank_value['rank'],
            'totalPost' => $rank_value['totalPost']
        ], 200);
    }

    /**
     * Sign Up User
     * 
     * @OA\Post(
     *      path="/api/signup",
     *      tags={"Login"},
     *      @OA\Parameter(
     *          name="firstName",
     *          in="query",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="lastName",
     *          in="query",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="email",
     *          in="query",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="password",
     *          in="query",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *              format="password"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     * @param UserSignInRequest $request
     */

    public function signup(UserSignInRequest $request): Response
    {
        // Get only these value below from request
        $credentials = $request->only(['first_name', 'last_name', 'email', 'password']);

        // Checking if Email has already existed
        $user = User::where("email", "=", $credentials['email'])->first();
        if ($user) {
            return Response([
                'message' => "Email đã tồn tại."
            ], 403);
        }

        $data = User::create([
            'first_name' => $credentials['first_name'],
            'last_name' => $credentials['last_name'],
            'email' => $credentials['email'],
            'password' => Hash::make($credentials['password'])
        ]);

        // Only happend when server is busy or time out
        if (empty($data)) {
            return Response([
                'message' => "Internal Server Error"
            ], 500);
        }

        return Response([
            'message' => 'Đăng ký thành công.'
        ], 200);
    }

    /**
     * Log In User
     * 
     * @OA\Post(
     *      path="/api/login",
     *      tags={"Login"},
     *      operationId="login",
     *      @OA\Parameter(
     *          name="email",
     *          in="query",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="password",
     *          in="query",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *              format="password",
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found",
     *      )
     * )
     * @param UserLoginRequest $request
     */

    public function login(UserLoginRequest $request): Response
    {
        // Get only these below values from request
        $credentials = $request->only(['email', 'password']);

        // Checking if user has created account
        if (!Auth::attempt($credentials)) {
            return Response([
                'message' => 'Đăng nhập không thành công, vui lòng kiểm tra lại tài khoản và mật khẩu.'
            ], 404);
        }

        $user = Auth::user();
        $user = User::where("email", "=", $request->email)->first();
        $token = $user->createToken('User Token', ['user-token'])->accessToken;

        return Response([
            'id' => $user->id,
            'name' => $user->first_name . " " . $user->last_name,
            'token' => $token
        ], 200);
    }

    /**
     * Review User Info
     * 
     * @OA\Get(
     *      path="/api/user",
     *      tags={"User"},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found",
     *      ),
     *      security={
     *         {"bearerAuth": {}}
     *     }
     * )
     * @param UserRequest $request
     */
    public function get(UserRequest $request): Response
    {
        $user = Auth::user();

        return Response(new UserDetailResource($user), 200);
    }
    /**
     * Update Info User
     * 
     * @OA\Put(
     *      path="/api/user/edited",
     *      tags={"User"},
     *      @OA\Parameter(
     *          name="firstName",
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="lastName",
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="email",
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="password",
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *              format="password"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found",
     *      ),
     *      security={
     *         {"bearerAuth": {}}
     *      }
     * )
     * @param UserUpdateRequest $request
     */
    public function update(UserUpdateRequest $request): Response
    {
        // Get only these below values from request
        $credentials = $request->only(['first_name', 'last_name']);

        $user = Auth::user();

        // Checking if email has been created from other users
        $email_check = User::where("email", "=", $request->email)
            ->where("id", "<>", $user->id)
            ->exists();

        if ($email_check)
            return Response([
                'message' => "Email đã tồn tại."
            ], 403);

        $object = [
            'first_name' => $credentials['first_name'] === null ? $user->first_name : $credentials['first_name'],
            'last_name' => $credentials['last_name'] === null ? $user->last_name : $credentials['last_name'],
            'password' => $request->password === null ? $user->password : Hash::make($request->password),
            'email' => $request->email === null ? $user->email : $request->email,
        ];

        $update = $user->update($object);

        if (empty($update))
            return Response([
                'message' => "Internal Server Error"
            ], 500);

        return Response([
            'message' => 'Cập nhật thông tin người dùng thành công.'
        ], 200);
    }
    
    // This function is deprecated
    /**
     * Log Out - Revoke Token (Deprecated)
     * 
     * @OA\Post(
     *      path="/api/user/logout",
     *      deprecated=true,
     *      tags={"User"},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found",
     *      ),
     *      security={
     *         {"bearerAuth": {}}
     *     }
     * )
     * @param UserRequest $request
     */
    public function logout(UserLogoutRequest $request): Response
    {
        // Auth::guard('api')->logout();

        $user = $request->user()->token();

        $user->revoke();

        return Response([
            'message' => "Đăng xuất tài khoản thành công."
        ], 200);
    }
}
