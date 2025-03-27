<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ChangePasswordRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[0-9]/',
                'regex:/[!@#$%^&*(),.?":{}|<>]/',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
            ],
            'new_password' => [
                'required',
                'string',
                'different:password',
                'min:8',
                'regex:/[0-9]/',
                'regex:/[!@#$%^&*(),.?":{}|<>]/',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
            ],
            'c_new_password' => [
                'required',
                'string',
                'same:new_password',
            ],
        ];
    }

    public function messages() {
        return [
            'password.required' => 'Поле с паролем обязательно для ввода',
            'new_password.required' => 'Поле с новым паролем обязательно для ввода',
            'new_password.different' => 'Ваш новый пароль не должен совпадать со старым',
            'password.min' => 'Минимальная длина пароля - 8 символов',
            'password.regex' => 'Пароль должен: 
                1) Иметь хотя бы одну цифру;
                2) Иметь хотя бы один специальный символ (!@#$%^&*(),.?":{}|<>);
                3) Иметь хотя бы один символ в верхнем и нижнем регистре.
            ',
            'new_password.min' => 'Минимальная длина нового пароля - 8 символов',
            'new_password.regex' => 'Новый пароль должен: 
                1) Иметь хотя бы одну цифру;
                2) Иметь хотя бы один специальный символ (!@#$%^&*(),.?":{}|<>);
                3) Иметь хотя бы один символ в верхнем и нижнем регистре.
            ',
            'c_new_password.same' => 'Новый пароль не совпадает с введённым',
        ];
    }

}
