<x-filament-panels::page.simple>
<div class="flex min-h-screen">

    <!-- Left Side -->
    <div class="hidden lg:flex w-1/2 bg-indigo-600 text-white items-center justify-center">
        <div class="text-center p-10">
            <h1 class="text-4xl font-bold mb-4">WebMon</h1>
            <p class="text-lg opacity-90">
                Monitor your servers and websites in real-time.
            </p>
        </div>
    </div>

    <!-- Right Side -->
    <div class="flex w-full lg:w-1/2 items-center justify-center bg-gray-100">

        <div class="w-full max-w-md bg-white shadow-xl rounded-xl p-8">

            <h2 class="text-2xl font-bold text-center mb-6">
                Login
            </h2>

            {{ $this->form }}

            <x-filament::button
                type="submit"
                form="form"
                class="w-full mt-4"
            >
                Sign In
            </x-filament::button>

        </div>

    </div>

</div>
</x-filament-panels::page.simple>