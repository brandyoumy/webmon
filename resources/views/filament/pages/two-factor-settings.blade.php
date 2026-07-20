<x-filament-panels::page>
    <div class="space-y-6 max-w-3xl">
        
        @if ($this->isTwoFactorEnabled())
            <!-- ACTIVE STATE -->
            <div class="bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 rounded-xl p-6 shadow-sm">
                <div class="flex items-start gap-4">
                    <div class="p-2 bg-emerald-100 dark:bg-emerald-900 rounded-lg text-emerald-600 dark:text-emerald-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-emerald-800 dark:text-emerald-300">
                            Two-Factor Authentication is Active
                        </h3>
                        <p class="text-sm text-emerald-700 dark:text-emerald-400 mt-1">
                            Your account is secured with a TOTP temporary passcode. You will be prompted to enter a verification code from your authenticator app each time you sign in.
                        </p>
                    </div>
                </div>
            </div>

            <!-- DISABLE FORM -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm space-y-4">
                <h4 class="text-md font-bold text-gray-900 dark:text-white">
                    Disable Two-Factor Authentication
                </h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    To disable 2FA, please enter your current account password below.
                </p>

                <form wire:submit="confirmTwoFactor" class="space-y-4">
                    {{ $this->form }}

                    <div class="flex items-center gap-3">
                        <x-filament::button
                            type="submit"
                            color="danger"
                        >
                            Disable 2FA
                        </x-filament::button>
                    </div>
                </form>
            </div>

        @elseif ($isEnabling)
            <!-- SETUP STATE (ENABLING) -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm space-y-6">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                        Configure Authenticator App
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Use your mobile authenticator app (e.g. Google Authenticator, Microsoft Authenticator, Authy) to secure your account.
                    </p>
                </div>

                <div class="flex flex-col md:flex-row items-center gap-8 py-4 border-y border-gray-150 dark:border-gray-700">
                    <!-- QR Code -->
                    <div class="p-4 bg-white border border-gray-200 rounded-xl shadow-inner">
                        {!! $qrCodeSvg !!}
                    </div>

                    <!-- Setup Directions -->
                    <div class="space-y-4">
                        <h4 class="font-semibold text-gray-900 dark:text-white text-sm">
                            Setup Instructions
                        </h4>
                        <ol class="list-decimal list-inside text-sm text-gray-600 dark:text-gray-300 space-y-2">
                            <li>Scan the QR code with your authenticator app.</li>
                            <li>If you cannot scan, manually enter the setup key shown below:</li>
                        </ol>
                        <div class="bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg px-4 py-2 font-mono text-sm tracking-widest text-center text-gray-800 dark:text-gray-200">
                            {{ $newSecret }}
                        </div>
                    </div>
                </div>

                <!-- VERIFY AND CONFIRM FORM -->
                <form wire:submit="confirmTwoFactor" class="space-y-4 max-w-md">
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
            </div>

        @else
            <!-- INACTIVE STATE (DISABLED) -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm space-y-6">
                <div class="flex items-start gap-4">
                    <div class="p-2 bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-500 dark:text-gray-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5V6.75a4.5 4.5 0 119 0v3.75M3.75 21.75h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H3.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Two-Factor Authentication is Disabled
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Add an extra layer of security to your admin account by requiring a dynamic passcode at sign-in.
                        </p>
                    </div>
                </div>

                <div>
                    <x-filament::button
                        type="button"
                        wire:click="startTwoFactorEnable"
                    >
                        Enable Two-Factor Authentication
                    </x-filament::button>
                </div>
            </div>
        @endif

    </div>
</x-filament-panels::page>
