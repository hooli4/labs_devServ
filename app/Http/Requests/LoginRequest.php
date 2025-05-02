<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'min:7',
                'regex:/^[A-Z][a-zA-Z]*$/',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[0-9]/',
                'regex:/[!@#$%^&*(),.?":{}|<>]/',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
            ],
        ];
    }

    public function messages(): array {
        return [
            'name.required' => 'Необходимо заполнить поле с именем пользователя',
            'name.regex' => 'Имя пользователя должно начинаться с большой буквы и состоять только из латинских букв',
            'name.min' => 'Минимальная длина имени пользователя - 7 символов',
            'name.unique' => 'Данное имя пользователя занято',
            'password.required' => 'Необходимо заполнить поле с паролем',
            'password.min' => 'Минимальная длина пароля - 8 символов',
            'password.regex' => 'Пароль должен: 
                1) Иметь хотя бы одну цифру;
                2) Иметь хотя бы один специальный символ (!@#$%^&*(),.?":{}|<>);
                3) Иметь хотя бы один символ в верхнем и нижнем регистре.
            ',
        ];
    }

}
