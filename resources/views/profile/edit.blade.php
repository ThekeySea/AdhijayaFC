<x-app-layout>
    <x-slot name="header">
        <p class="text-xs font-semibold uppercase tracking-wider text-primary">Akun</p>
        <h1 class="mt-1 text-2xl font-bold tracking-tight text-foreground">Profil</h1>
        <p class="mt-1.5 text-sm text-muted">Perbarui informasi akun dan kata sandi Anda.</p>
    </x-slot>

    <div class="mx-auto max-w-3xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <div class="rounded-2xl border border-border bg-surface p-5 sm:p-6">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="rounded-2xl border border-border bg-surface p-5 sm:p-6">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="rounded-2xl border border-border bg-surface p-5 sm:p-6">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>
