<x-filament-panels::page>
    <div class="space-y-6 max-w-3xl">
        
        @if ($this->isTwoFactorEnabled())
            <!-- ACTIVE STATE -->
            <div class="p-4 rounded-xl border border-emerald-500/30 bg-emerald-500/10 text-emerald-400">
                <div class="flex items-start gap-3">
                    <x-filament::icon
                        icon="heroicon-o-shield-check"
                        class="w-6 h-6 text-emerald-500"
                    />
                    <div>
                        <h3 class="text-sm font-semibold text-emerald-500">
                            Two-Factor Authentication is Active
                        </h3>
                        <p class="text-xs mt-1 text-emerald-400">
                            Your account is secured with a TOTP temporary passcode. You will be prompted to enter a verification code from your authenticator app each time you sign in.
                        </p>
                    </div>
                </div>
            </div>

            <!-- DISABLE FORM -->
            <x-filament::section>
                <x-slot name="heading">
                    Disable Two-Factor Authentication
                </x-slot>
                <x-slot name="description">
                    To deactivate two-factor authentication, please enter your current account password below.
                </x-slot>

                <form wire:submit="confirmTwoFactor" class="space-y-6 mt-4">
                    {{ $this->form }}

                    <div>
                        <x-filament::button
                            type="submit"
                            color="danger"
                        >
                            Disable 2FA
                        </x-filament::button>
                    </div>
                </form>
            </x-filament::section>

        @elseif ($isEnabling)
            <!-- SETUP STATE (ENABLING) -->
            <x-filament::section>
                <x-slot name="heading">
                    Configure Authenticator App
                </x-slot>
                <x-slot name="description">
                    Use your mobile authenticator app (e.g. Google Authenticator, Microsoft Authenticator, Authy) to secure your account.
                </x-slot>

                <div class="flex flex-col md:flex-row items-center gap-8 py-6 border-y border-gray-200 dark:border-gray-700 my-6">
                    <!-- QR Code -->
                    <div class="p-4 bg-white rounded-xl shadow-sm border border-gray-200">
                        {!! $qrCodeSvg !!}
                    </div>

                    <!-- Setup Directions -->
                    <div class="space-y-4 flex-1">
                        <h4 class="font-semibold text-gray-900 dark:text-white text-sm">
                            Setup Instructions
                        </h4>
                        <ol class="list-decimal list-inside text-sm text-gray-600 dark:text-gray-300 space-y-2">
                            <li>Scan the QR code with your authenticator app.</li>
                            <li>If you cannot scan, manually enter the setup key shown below:</li>
                        </ol>
                        <div class="bg-gray-150 dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg px-4 py-2 font-mono text-sm tracking-wider text-center select-all text-gray-800 dark:text-gray-200 max-w-sm">
                            {{ $newSecret }}
                        </div>
                    </div>
                </div>

                <!-- VERIFY AND CONFIRM FORM -->
                <form wire:submit="confirmTwoFactor" class="space-y-6">
                    {{ $this->form }}

                    <div class="flex items-center gap-3">
                        <x-filament::button type="submit">
                            Confirm & Enable
                        </x-filament::button>

                        <x-filament::button 
                            type="button" 
                            color="gray" 
                            wire:click="cancelTwoFactorEnable"
                        >
                            Cancel
                        </x-filament::button>
                    </div>
                </form>
            </x-filament::section>

        @else
            <!-- INACTIVE STATE (DISABLED) -->
            <x-filament::section>
                <x-slot name="heading">
                    Two-Factor Authentication is Disabled
                </x-slot>
                <x-slot name="description">
                    Add an extra layer of security to your admin account by requiring a dynamic passcode at sign-in.
                </x-slot>

                <div class="mt-4">
                    <x-filament::button
                        type="button"
                        wire:click="startTwoFactorEnable"
                    >
                        Enable Two-Factor Authentication
                    </x-filament::button>
                </div>
            </x-filament::section>
        @endif

    </div>
</x-filament-panels::page>
