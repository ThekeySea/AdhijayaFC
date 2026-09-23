<x-guest-layout>
    <div class="mb-6">
        <p class="text-xs font-semibold uppercase tracking-wider text-primary">Akun</p>
        <h1 class="mt-1 text-xl font-bold tracking-tight text-foreground">Verifikasi email</h1>
        <p class="mt-2 text-sm leading-relaxed text-muted">
            Sebelum mulai, buka tautan verifikasi yang kami kirim ke email Anda. Belum menerima email?
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 rounded-lg border border-primary-line bg-primary-soft px-3 py-2 text-sm font-medium text-foreground">
            Tautan verifikasi baru telah dikirim ke email Anda.
        </div>
    @endif

    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    Kirim ulang email
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="rounded-md text-sm font-medium text-muted underline transition hover:text-foreground focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2">
                Keluar
            </button>
        </form>
    </div>
</x-guest-layout>
