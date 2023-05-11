<?php

namespace App\Http\Requests\UserRequest;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *      properties={
 *          @OA\Property(property="avatar", type="file")
 *      }
 * )
 */
class UserUpdateAvatarRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'avatar' => [
                "file",
                'image',
                'required',
            ],
        ];
    }

    public function messages() {
        return [
            'required' => ":attribute không thể để trống",
        ];
    }

    public function attributes()
    {
        return [
            'avatar' => '"Ảnh đại diện"',
        ];
    }
}
