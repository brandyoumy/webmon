<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use BackedEnum;
use UnitEnum;

class TwoFactorSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLockClosed;

    protected static string|UnitEnum|null $navigationGroup = 'Settings & System';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.two-factor-settings';

    public bool $isEnabling = false;
    public ?string $newSecret = null;
    public ?string $qrCodeSvg = null;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function getHeading(): string
    {
        return 'Two-Factor Authentication';
    }

    public function isTwoFactorEnabled(): bool
    {
        $user = Auth::user();
        return $user->two_factor_secret && $user->two_factor_confirmed_at;
    }

    public function startTwoFactorEnable(): void
    {
        $user = Auth::user();
        $google2fa = new Google2FA();

        $this->newSecret = $google2fa->generateSecretKey();
        
        // Generate QR code SVG using BaconQrCode ImageRenderer
        $renderer = new ImageRenderer(
            new RendererStyle(180),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $this->newSecret
        );
        $this->qrCodeSvg = $writer->writeString($qrCodeUrl);
        $this->isEnabling = true;
        
        $this->form->fill();
    }

    public function cancelTwoFactorEnable(): void
    {
        $this->isEnabling = false;
        $this->newSecret = null;
        $this->qrCodeSvg = null;
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        if ($this->isTwoFactorEnabled()) {
            return $schema
                ->components([
                    TextInput::make('password')
                        ->label('Current Password')
                        ->password()
                        ->required()
                        ->placeholder('Enter password to disable 2FA'),
                ])
                ->statePath('data');
        }

        return $schema
            ->components([
                TextInput::make('code')
                    ->label('Verification Code')
                    ->placeholder('6-digit code')
                    ->required()
                    ->numeric()
                    ->maxLength(6)
                    ->extraInputAttributes(['autocomplete' => 'off']),
            ])
            ->statePath('data');
    }

    public function confirmTwoFactor(): void
    {
        $state = $this->form->getState();
        $user = Auth::user();

        if ($this->isTwoFactorEnabled()) {
            // Disabling 2FA
            if (!Hash::check($state['password'], $user->password)) {
                throw ValidationException::withMessages([
                    'data.password' => 'The password you entered is incorrect.',
                ]);
            }

            $user->update([
                'two_factor_secret' => null,
                'two_factor_confirmed_at' => null,
            ]);

            session()->forget('two_factor_verified');

            Notification::make()
                ->title('Two-Factor Authentication Disabled')
                ->success()
                ->send();

            $this->form->fill();
            return;
        }

        // Confirming/Enabling 2FA
        if (!$this->newSecret) {
            return;
        }

        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey($this->newSecret, $state['code']);

        if (!$valid) {
            throw ValidationException::withMessages([
                'data.code' => 'The verification code is incorrect.',
            ]);
        }

        $user->update([
            'two_factor_secret' => $this->newSecret,
            'two_factor_confirmed_at' => now(),
        ]);

        session(['two_factor_verified' => true]);

        $this->isEnabling = false;
        $this->newSecret = null;
        $this->qrCodeSvg = null;
        $this->form->fill();

        Notification::make()
            ->title('Two-Factor Authentication Enabled')
            ->body('Your account is now secured with TOTP.')
            ->success()
            ->send();
    }
}
