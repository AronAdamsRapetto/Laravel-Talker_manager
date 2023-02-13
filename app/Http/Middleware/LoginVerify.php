<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class LoginVerify
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
            'email' => ['required', 'email:rfc,dns'],
            'password' => ['required', 'min:6'],
        ];

        $messages = [
            'required' => 'O campo :attribute e obrigatorio!',
            'email' => 'O email deve ser valido',
            'min' => 'O password deve conter pelo menos 6 caracteres'
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            throw new HttpResponseException(response()->json([
                'message' => implode('', collect($validator->errors())->first()),
            ], 400));
        }

        return $next($request);
    }
}