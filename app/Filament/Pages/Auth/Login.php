<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\RateLimiter;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Facades\Filament;

class Login extends BaseLogin
{
    /**
     * Get the form schema for the login page
     * Accepts both employee_id and email
     */
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getLoginFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getRememberFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    /**
     * Override login field to accept employee_id or email
     */
    protected function getLoginFormComponent(): Component
    {
        return TextInput::make('login')
            ->label('رقم الموظف أو البريد الإلكتروني')
            ->placeholder('أدخل رقم الموظف أو البريد الإلكتروني')
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    /**
     * Get credentials for authentication
     * Determines if input is emp_code or email
     */
    protected function getCredentialsFromFormData(array $data): array
    {
        $login = $data['login'] ?? '';
        
        // Check if input is numeric/alphanumeric code (emp_code) or email
        $loginField = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'emp_code';
        
        return [
            $loginField => $login,
            'password' => $data['password'],
        ];
    }

    /**
     * Authenticate with rate limiting
     * Maximum 5 attempts per minute
     */
    public function authenticate(): ?LoginResponse
    {
        $data = $this->form->getState();
        $throttleKey = strtolower($data['login']) . '|' . request()->ip();

        // Rate limiting: 5 attempts per minute
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            
            throw ValidationException::withMessages([
                'data.login' => trans('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ]),
            ]);
        }

        try {
            if (! Filament::auth()->attempt(
                $this->getCredentialsFromFormData($data),
                $data['remember'] ?? false
            )) {
                // Increment rate limiter on failed attempt
                RateLimiter::hit($throttleKey, 60);

                throw ValidationException::withMessages([
                    'data.login' => __('filament-panels::pages/auth/login.messages.failed'),
                ]);
            }
        } catch (TooManyRequestsException $exception) {
            throw ValidationException::withMessages([
                'data.login' => __('filament-panels::pages/auth/login.messages.throttled', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]),
            ]);
        }

        // Clear rate limiter on successful login
        RateLimiter::clear($throttleKey);

        return app(LoginResponse::class);
    }
}
