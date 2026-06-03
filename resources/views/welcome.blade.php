<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Jana Prints — professional printing, branding, packaging, and design services with fast turnaround and quality you can trust.">

    <title>Jana Prints — Professional Printing Services</title>

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-slate-800 bg-white">

    {{-- Navigation --}}
    <header class="fixed top-0 inset-x-0 z-50 bg-white/90 backdrop-blur-md border-b border-slate-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="/" class="flex items-center gap-2.5 group">
                    <span class="flex items-center justify-center w-9 h-9 rounded-lg bg-gradient-to-br from-rose-600 to-orange-500 text-white font-bold text-sm shadow-sm">JP</span>
                    <span class="font-semibold text-slate-900 tracking-tight group-hover:text-rose-600 transition-colors">Jana Prints</span>
                </a>

                <nav class="hidden sm:flex items-center gap-8 text-sm font-medium text-slate-600">
                    <a href="#services" class="hover:text-rose-600 transition-colors">Services</a>
                    <a href="#workflow" class="hover:text-rose-600 transition-colors">How It Works</a>
                    <a href="#why-us" class="hover:text-rose-600 transition-colors">Why Us</a>
                    <a href="#contact" class="hover:text-rose-600 transition-colors">Contact</a>
                </nav>

                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="text-sm font-medium text-slate-600 hover:text-rose-600 transition-colors hidden sm:inline">Dashboard</a>
                    @else
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}" class="text-sm font-medium text-slate-600 hover:text-rose-600 transition-colors hidden sm:inline">Log in</a>
                        @endif
                    @endauth
                    <a href="#contact" class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-rose-600 to-orange-500 rounded-lg shadow-sm hover:from-rose-700 hover:to-orange-600 transition-all">
                        Request Quote
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main>

        {{-- Hero --}}
        <section class="relative pt-16 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900"></div>
            <div class="absolute inset-0 opacity-30">
                <div class="absolute top-20 left-10 w-72 h-72 bg-rose-500 rounded-full blur-3xl"></div>
                <div class="absolute bottom-10 right-10 w-96 h-96 bg-orange-500 rounded-full blur-3xl"></div>
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-cyan-500 rounded-full blur-3xl opacity-20"></div>
            </div>
            <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 32px 32px;"></div>

            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 sm:py-32 lg:py-40">
                <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                    <div>
                        <p class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-rose-300 text-sm font-medium mb-6 border border-white/10">
                            <span class="w-2 h-2 rounded-full bg-rose-400 animate-pulse"></span>
                            Professional Print &amp; Brand Solutions
                        </p>
                        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-white leading-tight tracking-tight">
                            Print with precision.<br>
                            <span class="bg-gradient-to-r from-rose-400 to-orange-400 bg-clip-text text-transparent">Deliver with confidence.</span>
                        </h1>
                        <p class="mt-6 text-lg sm:text-xl text-slate-300 leading-relaxed max-w-xl">
                            Jana Prints is your trusted partner for high-quality commercial printing, branding, and design — from concept to delivery, managed seamlessly through our ERP platform.
                        </p>
                        <div class="mt-10 flex flex-col sm:flex-row gap-4">
                            @if (Route::has('login'))
                                <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-6 py-3.5 text-base font-semibold text-slate-900 bg-white rounded-xl shadow-lg hover:bg-slate-100 transition-colors">
                                    Login
                                </a>
                            @endif
                            <a href="#contact" class="inline-flex items-center justify-center px-6 py-3.5 text-base font-semibold text-white border-2 border-white/30 rounded-xl hover:bg-white/10 transition-colors">
                                Request Quote
                            </a>
                        </div>
                    </div>

                    {{-- Hero visual placeholder --}}
                    <div class="relative hidden lg:block">
                        <div class="relative w-full aspect-square max-w-md mx-auto">
                            <div class="absolute inset-0 rounded-3xl bg-gradient-to-br from-rose-500/20 to-orange-500/20 border border-white/10 backdrop-blur-sm"></div>
                            <div class="absolute top-8 left-8 right-8 h-48 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center">
                                <div class="text-center">
                                    <div class="w-16 h-16 mx-auto mb-3 rounded-xl bg-gradient-to-br from-cyan-400 to-rose-500 opacity-80"></div>
                                    <p class="text-white/60 text-sm font-medium">Print Preview</p>
                                </div>
                            </div>
                            <div class="absolute bottom-8 left-8 w-32 h-40 rounded-xl bg-white/10 border border-white/20"></div>
                            <div class="absolute bottom-8 right-8 w-40 h-28 rounded-xl bg-gradient-to-br from-orange-400/30 to-rose-500/30 border border-white/20"></div>
                            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-24 h-24 rounded-full border-4 border-dashed border-white/20"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Services --}}
        <section id="services" class="py-20 sm:py-28 bg-slate-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-2xl mx-auto mb-16">
                    <h2 class="text-3xl sm:text-4xl font-bold text-slate-900 tracking-tight">Our Services</h2>
                    <p class="mt-4 text-lg text-slate-600">End-to-end print and brand solutions tailored for businesses of every size.</p>
                </div>

                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @php
                        $services = [
                            ['title' => 'Branding', 'desc' => 'Logos, brand guidelines, and visual identity systems that make your business stand out.', 'color' => 'from-rose-500 to-pink-500'],
                            ['title' => 'Large Format Printing', 'desc' => 'Banners, posters, signage, and exhibition displays with vivid color and durability.', 'color' => 'from-orange-500 to-amber-500'],
                            ['title' => 'Corporate Stationery', 'desc' => 'Business cards, letterheads, envelopes, and folders crafted to professional standards.', 'color' => 'from-cyan-500 to-blue-500'],
                            ['title' => 'Packaging', 'desc' => 'Custom boxes, labels, and product packaging that protects and promotes your brand.', 'color' => 'from-emerald-500 to-teal-500'],
                            ['title' => 'Promotional Materials', 'desc' => 'Flyers, brochures, catalogs, and marketing collateral for campaigns that convert.', 'color' => 'from-violet-500 to-purple-500'],
                            ['title' => 'Design Services', 'desc' => 'Expert layout, pre-press, and artwork preparation to ensure print-ready perfection.', 'color' => 'from-slate-600 to-slate-800'],
                        ];
                    @endphp

                    @foreach ($services as $service)
                        <article class="group relative bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 hover:shadow-md hover:border-rose-200 transition-all">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br {{ $service['color'] }} mb-5 flex items-center justify-center shadow-sm">
                                <div class="w-6 h-6 rounded bg-white/30"></div>
                            </div>
                            <h3 class="text-lg font-semibold text-slate-900 group-hover:text-rose-600 transition-colors">{{ $service['title'] }}</h3>
                            <p class="mt-2 text-sm text-slate-600 leading-relaxed">{{ $service['desc'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- Workflow --}}
        <section id="workflow" class="py-20 sm:py-28 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-2xl mx-auto mb-16">
                    <h2 class="text-3xl sm:text-4xl font-bold text-slate-900 tracking-tight">How It Works</h2>
                    <p class="mt-4 text-lg text-slate-600">A streamlined process from first quote to final delivery.</p>
                </div>

                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
                    @php
                        $steps = [
                            ['num' => '01', 'title' => 'Request Quote', 'desc' => 'Tell us about your project — quantity, specs, and timeline. We respond with a clear, competitive quote.'],
                            ['num' => '02', 'title' => 'Approve Artwork', 'desc' => 'Review proofs digitally, request revisions, and sign off when everything looks perfect.'],
                            ['num' => '03', 'title' => 'Production', 'desc' => 'Our team runs your job through quality-controlled presses and finishing processes.'],
                            ['num' => '04', 'title' => 'Delivery', 'desc' => 'Finished products delivered on schedule — ready to impress your customers.'],
                        ];
                    @endphp

                    @foreach ($steps as $index => $step)
                        <div class="relative text-center lg:text-left">
                            @if ($index < count($steps) - 1)
                                <div class="hidden lg:block absolute top-8 left-[calc(50%+2rem)] w-[calc(100%-4rem)] h-0.5 bg-gradient-to-r from-rose-300 to-orange-300"></div>
                            @endif
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-rose-600 to-orange-500 text-white font-bold text-lg shadow-lg mb-5">
                                {{ $step['num'] }}
                            </div>
                            <h3 class="text-lg font-semibold text-slate-900">{{ $step['title'] }}</h3>
                            <p class="mt-2 text-sm text-slate-600 leading-relaxed">{{ $step['desc'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- Why Choose Us --}}
        <section id="why-us" class="py-20 sm:py-28 bg-slate-900 relative overflow-hidden">
            <div class="absolute inset-0 opacity-20">
                <div class="absolute top-0 right-0 w-80 h-80 bg-rose-500 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 left-0 w-80 h-80 bg-orange-500 rounded-full blur-3xl"></div>
            </div>

            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-2xl mx-auto mb-16">
                    <h2 class="text-3xl sm:text-4xl font-bold text-white tracking-tight">Why Choose Us</h2>
                    <p class="mt-4 text-lg text-slate-400">Built for businesses that demand reliability, quality, and transparency.</p>
                </div>

                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 max-w-5xl mx-auto">
                    @php
                        $benefits = [
                            ['title' => 'Fast Turnaround', 'desc' => 'Rush jobs and standard orders handled with equal care — we respect your deadlines.'],
                            ['title' => 'Quality Control', 'desc' => 'Every job passes through rigorous checks before it leaves our facility.'],
                            ['title' => 'Customer Approvals', 'desc' => 'Digital proofing keeps you in control at every stage of production.'],
                            ['title' => 'Order Tracking', 'desc' => 'Real-time status updates so you always know where your order stands.'],
                            ['title' => 'Professional Design Support', 'desc' => 'Our in-house designers help refine artwork for flawless print results.'],
                        ];
                    @endphp

                    @foreach ($benefits as $benefit)
                        <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10 hover:bg-white/10 transition-colors">
                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-rose-500 to-orange-500 mb-4 flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <h3 class="text-base font-semibold text-white">{{ $benefit['title'] }}</h3>
                            <p class="mt-2 text-sm text-slate-400 leading-relaxed">{{ $benefit['desc'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- Customer CTA --}}
        <section class="py-20 sm:py-24 bg-gradient-to-r from-rose-600 to-orange-500 relative overflow-hidden">
            <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 24px 24px;"></div>
            <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white tracking-tight">Start your print job today</h2>
                <p class="mt-4 text-lg text-rose-100 max-w-2xl mx-auto">Get a free quote and experience printing done right — quality materials, expert craftsmanship, and service you can count on.</p>
                <div class="mt-10 flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="#contact" class="inline-flex items-center justify-center px-8 py-3.5 text-base font-semibold text-rose-600 bg-white rounded-xl shadow-lg hover:bg-slate-50 transition-colors">
                        Request Quote
                    </a>
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-8 py-3.5 text-base font-semibold text-white border-2 border-white/40 rounded-xl hover:bg-white/10 transition-colors">
                            Login to Portal
                        </a>
                    @endif
                </div>
            </div>
        </section>

    </main>

    {{-- Footer --}}
    <footer id="contact" class="bg-slate-950 text-slate-400">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-10">
                <div class="lg:col-span-2">
                    <div class="flex items-center gap-2.5 mb-4">
                        <span class="flex items-center justify-center w-9 h-9 rounded-lg bg-gradient-to-br from-rose-600 to-orange-500 text-white font-bold text-sm">JP</span>
                        <span class="font-semibold text-white text-lg">Jana Prints</span>
                    </div>
                    <p class="text-sm leading-relaxed max-w-md">Your partner for professional printing, branding, and design. Quality you can see, service you can trust.</p>
                </div>

                <div>
                    <h4 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">Contact</h4>
                    <ul class="space-y-3 text-sm">
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 mt-0.5 text-rose-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>123 Print Street, City, Country</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <span>+1 (000) 000-0000</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <span>hello@janaprints.com</span>
                        </li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">Follow Us</h4>
                    <ul class="space-y-3 text-sm">
                        <li><a href="#" class="hover:text-rose-400 transition-colors">Facebook</a></li>
                        <li><a href="#" class="hover:text-rose-400 transition-colors">Instagram</a></li>
                        <li><a href="#" class="hover:text-rose-400 transition-colors">LinkedIn</a></li>
                        <li><a href="#" class="hover:text-rose-400 transition-colors">Twitter / X</a></li>
                    </ul>
                    @if (Route::has('login'))
                        <div class="mt-6 pt-6 border-t border-slate-800">
                            <a href="{{ route('login') }}" class="text-sm font-medium text-white hover:text-rose-400 transition-colors">Login &rarr;</a>
                        </div>
                    @endif
                </div>
            </div>

            <div class="mt-12 pt-8 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs">
                <p>&copy; {{ date('Y') }} Jana Prints. All rights reserved.</p>
                <p class="text-slate-600">Powered by Jana Prints ERP</p>
            </div>
        </div>
    </footer>

</body>
</html>
