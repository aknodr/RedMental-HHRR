<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in — RedMental</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <style>body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", system-ui, sans-serif; }</style>
</head>
<body class="bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-950 min-h-screen flex items-center justify-center p-6">

    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-indigo-600 text-white mb-4 shadow-xl">
                <i data-lucide="stethoscope" class="w-8 h-8"></i>
            </div>
            <h1 class="text-white text-2xl font-bold">RedMental</h1>
            <p class="text-slate-400 text-sm mt-1">HHRR &amp; Clinical management</p>
        </div>

        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <h2 class="text-xl font-bold text-slate-900 mb-1">Sign in</h2>
            <p class="text-sm text-slate-500 mb-6">Enter your credentials to continue</p>

            @if($errors->any())
                <div class="mb-4 p-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-lg text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Password</label>
                    <input type="password" name="password" required
                           class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    Remember me
                </label>

                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 rounded-lg text-sm transition">
                    Sign in
                </button>
            </form>
        </div>

        <div class="text-center mt-6 text-xs text-slate-500">
            © {{ date('Y') }} RedMental — Tesis Demo
        </div>
    </div>

    <script>lucide.createIcons();</script>
</body>
</html>
