<?php

namespace App\Http\Requests\PostRequest;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *      properties={
 *          @OA\Property(property="title", type="string"),
 *          @OA\Property(property="tags", type="string"),
 *          @OA\Property(property="content", type="string"),
 *      }
 * )
 */
class StorePostsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        $tokenCan = $user->tokenCan("user-token");
        
        return $user != null && $tokenCan;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'min: 3',
                'max: 255'
            ],
            'tags' => [
                "required",
                "string"
                // "array"
            ],
            'content' => [
                'required',
                'string',
                'min: 10',
                'max: 500'
            ]
        ];
    }

    public function messages() : array
    {
        return [
            'required' => ':attribute không thể để trống',
            'min' => ':attribute quá ít ký tự (tối thiểu 2)',
            'max' => ':attribute quá nhiều ký tự (tối đa 100)'
        ];
    }

    public function attributes() : array
    {
        return [
            'name' => 'Tên',
            'tag' => 'Thẻ "tag" công việc'
        ];
    }
}
