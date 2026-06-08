<x-layouts.public title="Client Login — Jana Prints">
    <section class="public-section public-section--compact">
        <div class="public-container max-w-md">
            <div class="rounded-brand-xl border border-white/10 bg-white/5 p-8 shadow-brand-lg backdrop-blur-sm">
                <div class="mb-6 text-center">
                    <x-public.brand-logo full size="lg" class="mx-auto" />
                    <h1 class="mt-4 font-display text-2xl font-bold text-white">Client Login</h1>
                    <p class="mt-2 text-sm text-white/70">Track quotes, orders, and artwork approvals for your business.</p>
                </div>

                @if (session('status'))
                    <div class="mb-4 rounded-lg border border-emerald-400/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-100" role="status">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 rounded-lg border border-rose-400/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-100" role="alert">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('client.login') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="email" class="mb-1 block text-xs font-medium text-white/80">Email</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="username"
                            class="w-full rounded-brand-lg border border-white/15 bg-white/10 px-4 py-2.5 text-white placeholder-white/40 focus:border-brand-cyan focus:outline-none focus:ring-2 focus:ring-brand-cyan/30"
                            placeholder="you@company.com"
                        >
                    </div>

                    <div>
                        <label for="password" class="mb-1 block text-xs font-medium text-white/80">Password</label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            class="w-full rounded-brand-lg border border-white/15 bg-white/10 px-4 py-2.5 text-white placeholder-white/40 focus:border-brand-cyan focus:outline-none focus:ring-2 focus:ring-brand-cyan/30"
                            placeholder="Enter your password"
                        >
                    </div>

                    <div class="text-sm">
                        <label class="flex items-center gap-2 text-white/80">
                            <input type="checkbox" name="remember" class="rounded border-white/20 bg-white/10 text-brand-cyan focus:ring-brand-cyan/30" @checked(old('remember'))>
                            Remember me
                        </label>
                    </div>

                    <button type="submit" class="public-btn--primary w-full justify-center">
                        Sign in to Client Portal
                    </button>
                </form>

                <p class="mt-6 text-center text-xs text-white/60">
                    Jana Prints staff?
                    <a href="{{ route('admin.login') }}" class="font-medium text-brand-cyan hover:text-white">ERP sign in</a>
                </p>

                <p class="mt-3 text-center">
                    <a href="{{ url('/') }}" class="text-sm text-white/70 hover:text-white">&larr; Back to website</a>
                </p>
            </div>
        </div>
    </section>
</x-layouts.public>
