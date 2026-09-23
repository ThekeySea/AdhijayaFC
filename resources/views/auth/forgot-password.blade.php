<x-guest-layout>
    <div class="mb-6">
        <p class="text-xs font-semibold uppercase tracking-wider text-primary">Akun</p>
        <h1 class="mt-1 text-xl font-bold tracking-tight text-foreground">Lupa kata sandi?</h1>
        <p class="mt-2 text-sm leading-relaxed text-muted">
            Masukkan email Anda. Kami akan kirim tautan untuk mengatur ulang kata sandi.
        </p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-6 flex items-center justify-end">
            <x-primary-button>
                Kirim tautan reset
            </x-primary-button>
        </div>
    </form>

    <p class="mt-6 text-center text-sm text-muted">
        <a href="{{ route('login') }}" class="font-medium text-primary hover:underline">Kembali ke masuk</a>
    </p>
</x-guest-layout>
