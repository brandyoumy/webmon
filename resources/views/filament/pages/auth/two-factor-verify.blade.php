<x-filament-panels::page.simple>
    <div class="space-y-6">
        <div class="text-center">
            <h2 class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white">
                Two-Factor Verification
            </h2>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Enter the 6-digit verification code from your authenticator app to access your account.
            </p>
        </div>

        <form wire:submit="submit" class="space-y-6">
            {{ $this->form }}

            <x-filament::button
                type="submit"
                class="w-full"
            >
                Verify Code
            </x-filament::button>
        </form>
    </div>
</x-filament-panels::page.simple>
