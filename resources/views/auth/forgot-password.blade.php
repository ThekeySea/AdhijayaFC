<x-guest-layout>
    <div class="mb-6">
        <p class="text-xs font-semibold uppercase tracking-wider text-primary">Akun</p>
        <h1 class="mt-1 text-xl font-bold tracking-tight text-foreground">Lupa kata sandi?</h1>
        <p class="mt-2 text-sm leading-relaxed text-muted">
            Masukkan email Anda. Kami akan kirim tautan untuk mengatur ulang kata sandi.
        </p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
            <p class="font-semibold">Gagal mengirim tautan</p>
            <ul class="mt-1 list-inside list-disc space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

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
