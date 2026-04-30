<!doctype html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>2FA - Gestão App</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-neutral-950 text-neutral-100">
    <div class="relative flex min-h-screen items-center justify-center overflow-hidden px-4 py-10">
        <div class="pointer-events-none absolute inset-0 opacity-60">
            <div class="absolute -left-16 top-16 h-56 w-56 rounded-full bg-neutral-800 blur-3xl"></div>
            <div class="absolute -right-16 bottom-20 h-56 w-56 rounded-full bg-neutral-700 blur-3xl"></div>
        </div>

        <div class="relative w-full rounded-2xl border border-neutral-800 bg-black/90 p-8 shadow-2xl backdrop-blur" style="max-width: 480px;">
            <h1 class="mb-2 text-2xl font-bold">Autenticação de Dois Fatores</h1>
            <p class="mb-6 text-sm text-neutral-400">Introduz o código da app autenticadora ou um código de recuperação.</p>

            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-700 bg-red-950/70 p-3 text-sm text-red-100">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('two-factor.login') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="mb-1 block text-sm font-medium text-neutral-300">Código</label>
                    <input
                        name="code"
                        type="text"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                        class="flex h-11 w-full rounded-lg border border-neutral-700 bg-neutral-900 px-3 py-2 text-sm text-neutral-100 outline-none transition focus:border-neutral-500 focus:ring-2 focus:ring-neutral-700/70"
                    >
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-neutral-300">Código de recuperação</label>
                    <input
                        name="recovery_code"
                        type="text"
                        class="flex h-11 w-full rounded-lg border border-neutral-700 bg-neutral-900 px-3 py-2 text-sm text-neutral-100 outline-none transition focus:border-neutral-500 focus:ring-2 focus:ring-neutral-700/70"
                    >
                </div>
                <button
                    type="submit"
                    class="w-full rounded-lg bg-neutral-100 px-4 py-2.5 text-sm font-semibold text-neutral-900 transition hover:bg-white"
                >
                    Confirmar
                </button>
            </form>
        </div>
    </div>
</body>
</html>
