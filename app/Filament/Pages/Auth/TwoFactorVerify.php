<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\TextInput;
use Filament\Pages\SimplePage;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorVerify extends SimplePage
{
    protected static ?string $title = 'Two-Factor Verification';

    protected string $view = 'filament.pages.auth.two-factor-verify';

    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    public function mount(): void
    {
        // If they already verified, redirect to dashboard:
        if (session()->get('two_factor_verified')) {
            redirect()->intended(filament()->getUrl());
        }

        // If they are not authenticated, redirect to login:
        if (!Auth::check()) {
            redirect()->route('filament.app.auth.login');
        }

        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('Verification Code')
                    ->placeholder('6-digit code')
                    ->required()
                    ->numeric()
                    ->maxLength(6)
                    ->extraInputAttributes(['autocomplete' => 'one-time-code']),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $state = $this->form->getState();
        $user = Auth::user();

        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey($user->two_factor_secret, $state['code']);

        if (!$valid) {
            throw ValidationException::withMessages([
                'data.code' => 'The verification code is incorrect.',
            ]);
        }

        session(['two_factor_verified' => true]);

        $this->redirect(filament()->getUrl());
    }
}
