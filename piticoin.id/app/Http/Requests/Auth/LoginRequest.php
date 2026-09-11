<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'whatsapp' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $rawIdentifier = (string) $this->input('whatsapp');
        $identifier = trim($rawIdentifier);
        $cleanUsername = ltrim($identifier, '@');
        $cleanPhone = preg_replace('/[^0-9]/', '', $identifier);
        $password = (string) $this->input('password');

        $user = \App\Models\User::query()
            ->where(function ($query) use ($identifier, $cleanUsername, $cleanPhone) {
                $query->whereRaw('LOWER(username) = ?', [strtolower($cleanUsername)])
                    ->orWhere('whatsapp', $identifier);
                if (!empty($cleanPhone)) {
                    $query->orWhere('whatsapp', $cleanPhone)
                          ->orWhere('whatsapp', 'like', '%' . substr($cleanPhone, -9));
                }
            })
            ->first();

        if (! $user || ! \Illuminate\Support\Facades\Hash::check($password, $user->password)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'whatsapp' => __('auth.failed'),
            ]);
        }

        Auth::login($user, $this->boolean('remember'));

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'whatsapp' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('whatsapp')).'|'.$this->ip());
    }
}
