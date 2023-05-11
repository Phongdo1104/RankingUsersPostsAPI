<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostRequest\DeletePostsRequest;
use App\Http\Requests\PostRequest\GetPostsRequest;
use App\Http\Requests\PostRequest\StorePostsRequest;
use App\Http\Requests\PostRequest\UpdatePostsRequest;
use App\Http\Resources\PostDetailResource;
use App\Models\Post;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth:api', ['except' => 'search']);
    // }

    // This function is used for checking if post with id provided is existed
    public function isExisted($data)
    {
        $query = Post::where("id", "=", $data);

        if (!$query->exists()) {
            return False;
        }

        return True;
    }

    /**
     * All User's Posts
     * 
     * @OA\Get(
     *      path="/api/post",
     *      tags={"Post"},
     *      summary="All User's Posts",
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
     * @return PostDetailResource
     */
    public function index(GetPostsRequest $request)
    {
        // Checking if user has ever created any posts
        $posts = Post::where("user_id", "=", $request->user()->id);

        if ($posts->count() === 0) {
            return Response([
                'message' => 'Người dùng chưa tạo bài viết.'
            ], 404);
        }

        return PostDetailResource::collection($posts->paginate(2));
    }

    /**
     * Add Post
     * 
     * @OA\Post(
     *      path="/api/post/add",
     *      tags={"Post"},
     *      summary="Add Post",
     *      description="Return Post's Insert Status value",
     *      operationId="addNewPost",
     *      @OA\Parameter(
     *          name="title",
     *          in="query",
     *          description="Title Post",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="tags",
     *          in="query",
     *          description="Tag for post, and each tag seperated by ';'",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="content",
     *          in="query",
     *          description="Post Content",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful Opearation"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      security={
     *          {"bearerAuth": {}}
     *      }
     * )
     * 
     * @param StorePostsRequest $request
     */
    public function store(StorePostsRequest $request): Response
    {
        $object = [
            "title" => $request->title,
            // "tags" => implode(";", $request->tags),
            "tags" => $request->tags,
            "content" => $request->content,
            "user_id" => $request->user()->id
        ];

        $data = Post::create($object);

        if (empty($data)) {
            return Response([
                'message' => "Internal Server Error"
            ], 500);
        }

        return Response([
            'message' => 'Thêm bài viết thành công.'
        ], 200);
    }

    /**
     * Find Post by ID
     * 
     * @OA\Get(
     *     path="/api/post/{postId}",
     *     tags={"Post"},
     *     summary="Find Post by ID",
     *     description="Returns a single Post",
     *     operationId="getPostById",
     *     @OA\Parameter(
     *         name="postId",
     *         in="path",
     *         description="ID Post",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Post not found"
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */
    public function get(GetPostsRequest $request) : Response
    {
        // Checking if post with provided ID is existed
        if (!$this->isExisted($request->id)) {
            return Response([
                'message' => 'Không tìm thấy bài đăng.'
            ], 404);
        }

        $data = Post::where("id", "=", $request->id)->first();

        return Response(new PostDetailResource($data), 200);
    }

    // Function in progress (maybe abandone)
    public function search(Request $request) : Response
    {
        if (!empty($request->get('q'))) {
            $value = "%" . $request->get('q') . "%";

            $search = Post::where(function($query) use ($value) {
                $query->where("title", "like", $value)
                    ->orWhere("content", "like", $value)
                    ->orWhere('tags', "like", $value);
            })
            ->get();

            dd($value);
        }

        // Configure Search API
        if (!$this->isExisted($request->id)) {
            return Response([
                'message' => 'Không tìm thấy bài đăng.'
            ], 404);
        }

        $data = Post::where("id", "=", $request->id)->first();

        return Response(new PostDetailResource($data), 200);
    }

    /**
     * Update Post based on id
     * 
     * @OA\Put(
     *     path="/api/post/{postId}/edited",
     *     tags={"Post"},
     *     summary="Update Post based on id",
     *     description="Returns a Post's Update Status",
     *     operationId="updatePostById",
     *     @OA\Parameter(
     *          name="postId",
     *          in="path",
     *          description="ID Post",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="title",
     *          in="query",
     *          description="Title Post",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="tags",
     *          in="query",
     *          description="Tag for post, and each tag seperated by ';'",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="content",
     *          in="query",
     *          description="Post Content",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful Opearation"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      security={
     *          {"bearerAuth": {}}
     *      }
     * )
     *
     * @param UpdatePostsRequest $request
     */
    public function update(UpdatePostsRequest $request): Response
    {
        // Checking Post with provided ID is exited first?
        if (!$this->isExisted($request->id)) {
            return Response([
                'message' => 'Không tìm thấy bài đăng.'
            ], 404);
        }

        $data = Post::where("id", "=", $request->id)->first();

        // Checking a certain request field is empty
        // If it's not replace with old data
        $object = [
            'title' => $request->title === null ? $data->title : $request->title,
            'tags' => $request->tags === null ? $data->tags : $request->tags,
            'content' => $request->content === null ? $data->content : $request->content,
        ];

        $update = $data->update($object);

        if (empty($update))
            return Response([
                'message' => "Internal Server Error"
            ], 500);

        return Response([
            'message' => 'Cập nhật bài viết thành công.'
        ], 200);
    }

    /**
     * Delete Post based on id
     * 
     * @OA\Delete(
     *     path="/api/post/{postId}/delete",
     *     tags={"Post"},
     *     summary="Delete Post based on id",
     *     description="Returns a Post's Delete Status",
     *     operationId="deletePostById",
     *     @OA\Parameter(
     *          name="postId",
     *          in="path",
     *          description="ID Post",
     *          @OA\Schema(
     *              type="string"
     *          )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Successful Opearation"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found"
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     *
     */
    public function delete(DeletePostsRequest $request): Response
    {
        if (!$this->isExisted($request->id)) {
            return Response([
                'message' => 'Không tìm thấy bài đăng.'
            ], 404);
        }

        $data = Post::where("id", "=", $request->id)->first();

        $update = $data->delete();

        if (empty($update))
            return Response([
                'message' => "Internal Server Error"
            ], 500);

        return Response([
            'message' => 'Xoá bài viết thành công.'
        ], 200);
    }
}
