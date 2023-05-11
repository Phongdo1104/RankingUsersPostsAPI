<?php

namespace App\Http\Requests\UserRequest;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *      properties={
 *          @OA\Property(property="email", type="string", example="phongdo1104@gmail.com"),
 *          @OA\Property(property="password", type="string", example="11042001"),
 *      }
 * )
 */

class UserLoginRequest extends FormRequest
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
            'email' => [
                'required',
                'email'
            ],
            'password' => [
                'required',
                'min: 6',
                'max: 50'
            ],
        ];
    }

    public function messages() {
        return [
            'required' => ":attribute không thể để trống",
            'min' => ":attribute chứa quá ít ký tự",
            'max' => ":attribute chứa quá nhiều ký tự",
        ];
    }
}
