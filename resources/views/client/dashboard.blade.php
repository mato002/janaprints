<x-layouts.public title="Client Portal — Jana Prints">
    <section class="public-section public-section--compact">
        <div class="public-container max-w-3xl">
            <div class="rounded-brand-xl border border-white/10 bg-white/5 p-8 shadow-brand-lg backdrop-blur-sm">
                <div class="mb-6">
                    <h1 class="font-display text-2xl font-bold text-white">Client Portal</h1>
                    <p class="mt-2 text-sm text-white/70">
                        Welcome, {{ auth()->user()->name }}. Track quotes, orders, and artwork approvals for your business.
                    </p>
                </div>

                <p class="text-sm text-white/60">
                    Your portal workspace is being prepared. Contact your Jana Prints account manager if you need immediate assistance.
                </p>

                <form method="POST" action="{{ route('logout') }}" class="mt-8">
                    @csrf
                    <button type="submit" class="text-sm text-brand-cyan hover:text-white">Sign out</button>
                </form>
            </div>
        </div>
    </section>
</x-layouts.public>
