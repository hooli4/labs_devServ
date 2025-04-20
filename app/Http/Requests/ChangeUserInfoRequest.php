<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class ChangeUserInfoRequest extends FormRequest
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
                'string',
                'min:7',
                'regex:/^[A-Z][a-zA-Z]*$/',
                'unique:users,name,' . $this->route('id'),
            ],
            'new_password' => [
                'string',
                'min:8',
                'regex:/[0-9]/',
                'regex:/[!@#$%^&*(),.?":{}|<>]/',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
            ],
            'email' => [
                'string',
                'email:rfc',
                'unique:users,email,' . $this->route('id'),
            ],
            'c_password' => [
                'required',
                'string',
            ],
            'birthday' => [
                'date_format:Y-m-d',
                'before_or_equal:' . Carbon::now()->subYears(14)->toDateString(),
            ],
        ];
    }
}
