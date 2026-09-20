<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Services\AuditService;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Custom validation messages in pt_BR.
     */
    public function messages(): array
    {
        return [
            'email.required' => 'O campo e-mail é obrigatório.',
            'email.email' => 'Informe um endereço de e-mail válido.',
            'password.required' => 'O campo senha é obrigatório.',
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $user = User::where('email', $this->input('email'))->first();

        // Checagem de usuário bloqueado antes de tentar autenticação
        if ($user && $user->status === 'blocked') {
            AuditService::logFailedLogin($this->input('email'), 'Conta bloqueada pela administração regional');
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'Este usuário está bloqueado pela administração regional.',
            ]);
        }

        // Checagem de usuário inativo
        if ($user && $user->status === 'inactive') {
            AuditService::logFailedLogin($this->input('email'), 'Conta inativa');
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'Esta conta de usuário está desativada.',
            ]);
        }

        if (!Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());
            AuditService::logFailedLogin($this->input('email'), 'Credenciais incorretas');

            throw ValidationException::withMessages([
                'email' => 'As credenciais fornecidas não conferem com nossos registros.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        AuditService::logFailedLogin($this->input('email'), "Bloqueio temporário por excesso de tentativas ({$seconds} segundos restantes)");

        throw ValidationException::withMessages([
            'email' => "Muitas tentativas de login incorretas. Por segurança, tente novamente em {$seconds} segundos.",
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('email')).'|'.$this->ip());
    }
}
