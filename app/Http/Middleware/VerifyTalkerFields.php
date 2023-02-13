<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VerifyTalkerFields
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {

        $rules = [
            'name' => ['required', 'min:3'],
            'age' => ['required', 'integer', 'min:18'],
            'talk' => ['required', 'array'],
            'talk.rate' => ['required', 'integer', 'min:1', 'max:5'],
            'talk.watchedAt' => ['required', 'date_format:d/m/Y']
        ];

        $messages = [
            'required' => 'O campo :attribute é obrigatório!',
            'name.min' => 'O name deve ter pelo menos 3 caracteres',
            'age.min' => 'A pessoa palestrante deve ser maior de idade',
            'talk.rate.min' => 'O campo rate deve ser um número de 1 à 5',
            'talk.rate.max' => 'O campo rate deve ser um número de 1 à 5',
            'talk.watchedAt.date_format' => 'O campo watchedAt deve ter o formato dd/mm/aaaa',
        ];

        $attributes = [
            'talk.rate' => 'rate',
            'talk.watchedAt' => 'watchedAt'
        ];

        $validator = Validator::make($request->all(), $rules, $messages, $attributes);

        if ($validator->fails()) {
            throw new HttpResponseException(response()->json([
                'message' => implode('', collect($validator->errors())->first())
            ]));
        }

        return $next($request);
    }
}