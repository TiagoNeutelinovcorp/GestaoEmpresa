<!doctype html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Gestão App</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-neutral-950 text-neutral-100">
    <div class="relative flex min-h-screen items-center justify-center overflow-hidden px-4 py-10">
        <div class="pointer-events-none absolute inset-0 opacity-60">
            <div class="absolute -left-16 top-16 h-56 w-56 rounded-full bg-neutral-800 blur-3xl"></div>
            <div class="absolute -right-16 bottom-20 h-56 w-56 rounded-full bg-neutral-700 blur-3xl"></div>
        </div>

        <div class="relative w-full rounded-2xl border border-neutral-800 bg-black/90 p-8 shadow-2xl backdrop-blur" style="max-width: 480px;">
            <div class="mb-6 text-center">
                <h1 class="text-3xl font-bold">Bem-vindo</h1>
                <p class="mt-1 text-sm text-neutral-400">Entra com a tua conta para aceder ao painel.</p>
            </div>

            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-700 bg-red-950/70 p-3 text-sm text-red-100">
                    {{ $errors->first() }}
                </div>
            @endif

            @if (session('status'))
                <div class="mb-4 rounded-lg border border-emerald-700 bg-emerald-950/70 p-3 text-sm text-emerald-100">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="mb-1 block text-sm font-medium text-neutral-300">Email</label>
                    <input
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        class="flex h-11 w-full rounded-lg border border-neutral-700 bg-neutral-900 px-3 py-2 text-sm text-neutral-100 outline-none transition focus:border-neutral-500 focus:ring-2 focus:ring-neutral-700/70"
                    >
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-neutral-300">Password</label>
                    <input
                        name="password"
                        type="password"
                        required
                        class="flex h-11 w-full rounded-lg border border-neutral-700 bg-neutral-900 px-3 py-2 text-sm text-neutral-100 outline-none transition focus:border-neutral-500 focus:ring-2 focus:ring-neutral-700/70"
                    >
                </div>

                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center gap-2 text-neutral-300">
                        <input class="rounded border-neutral-700 bg-neutral-900 text-neutral-100" type="checkbox" name="remember">
                        Lembrar sessão
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-neutral-400 hover:text-neutral-200">
                            Esqueceste a password?
                        </a>
                    @endif
                </div>

                <button
                    type="submit"
                    class="w-full rounded-lg bg-neutral-100 px-4 py-2.5 text-sm font-semibold text-neutral-900 transition hover:bg-white"
                >
                    Entrar
                </button>

                <p class="text-center text-sm text-neutral-400">
                    Ainda não tens conta?
                    <a href="{{ route('register') }}" class="text-neutral-200 hover:text-white">Criar conta</a>
                </p>
            </form>
        </div>
    </div>
</body>
</html>
