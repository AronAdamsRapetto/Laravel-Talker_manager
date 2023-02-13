<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoginRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'email' => ['required', 'email:rfc,dns'],
            'password' => ['required', 'min:6'],
        ];
    }

    public function messages()
    {
        return [
            'required' => 'O campo :attribute e obrigatorio!',
            'email' => 'O email deve ser valido',
            'min' => 'O password deve conter pelo menos 6 caracteres'
        ];
    }

    /**
     * Get the error messages for the defined validation rules.*
     * @return array
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => implode('', collect($validator->errors())->first()),
        ], 400));
    }
}