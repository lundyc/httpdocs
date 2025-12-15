        </main>

        <footer class="bg-maroon text-cream">
            <div class="mx-auto w-full max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div class="grid gap-10 md:grid-cols-3">
                    <div class="space-y-4">
                        <a href="/" class="flex items-center gap-3 text-cream transition hover:text-gold"
                            aria-label="My Club Hub home">
                            <img src="/shared/assets/logo.png" alt="My Club Hub crest"
                                class="h-12 w-12 rounded-full border border-gold/60 bg-cream/10 p-2">
                            <span class="flex flex-col leading-tight">
                                <span class="text-base font-semibold uppercase tracking-[0.24em]">My Club Hub</span>
                                <span class="text-xs font-medium uppercase tracking-[0.4em] text-gold/80">Club Hub</span>
                            </span>
                        </a>
                        <p class="max-w-sm text-sm text-cream/70">
                            Modern grassroots football for every community. We unite players, volunteers, supporters,
                            and partners under the maroon and gold.
                        </p>
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2 md:col-span-2">
                        <div class="space-y-3">
                            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-gold">Explore</p>
                            <nav class="flex flex-col gap-2 text-sm font-semibold uppercase tracking-[0.18em] text-cream/80">
                                <a class="transition hover:text-gold" href="/news/">Club News</a>
                                <a class="transition hover:text-gold" href="/fixtures/">Match Centre</a>
                                <a class="transition hover:text-gold" href="/history/">Our Story</a>
                                <a class="transition hover:text-gold" href="/sponsors/">Partnerships</a>
                                <a class="transition hover:text-gold" href="/portal/">Team Portal</a>
                                <a class="transition hover:text-gold" href="/admin/">Admin</a>
                            </nav>
                        </div>
                        <div class="space-y-3">
                            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-gold">Connect</p>
                            <div class="flex flex-wrap gap-3 text-sm font-semibold uppercase tracking-[0.2em] text-cream/80">
                                <a class="inline-flex items-center gap-2 rounded-full border border-gold/40 px-4 py-2 transition hover:bg-gold hover:text-maroon"
                                    href="mailto:hello@myclubhub.co.uk">Email</a>
                                <a class="inline-flex items-center gap-2 rounded-full border border-gold/40 px-4 py-2 transition hover:bg-gold hover:text-maroon"
                                    href="https://facebook.com" target="_blank" rel="noopener">Facebook</a>
                                <a class="inline-flex items-center gap-2 rounded-full border border-gold/40 px-4 py-2 transition hover:bg-gold hover:text-maroon"
                                    href="https://instagram.com" target="_blank" rel="noopener">Instagram</a>
                            </div>
                            <div class="space-y-2 text-sm text-cream/70">
                                <p>Head office: My Club Hub HQ</p>
                                <p>Matchday hotline: <span class="font-semibold text-gold">01294 123 456</span></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-12 flex flex-col gap-3 border-t border-cream/10 pt-6 text-xs font-semibold uppercase tracking-[0.24em] text-cream/70 sm:flex-row sm:items-center sm:justify-between">
                    <span>&copy; <?= date('Y'); ?> My Club Hub. All rights reserved.</span>
                    <span>United Kingdom | Est. 2000</span>
                </div>
            </div>
        </footer>
        </div>

        <script>
            (function() {
                const navToggle = document.querySelector('[data-nav-toggle]');
                const mobileNav = document.getElementById('mobile-nav');
                let iconOpen = true;

                if (!navToggle || !mobileNav) return;

                const iconPaths = {
                    open: '<path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" />',
                    close: '<path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M6 18L18 6" />'
                };

                const updateIcon = () => {
                    const svg = navToggle.querySelector('svg');
                    if (!svg) return;
                    svg.innerHTML = iconOpen ? iconPaths.open : iconPaths.close;
                };

                updateIcon();

                navToggle.addEventListener('click', () => {
                    mobileNav.classList.toggle('hidden');
                    iconOpen = mobileNav.classList.contains('hidden');
                    navToggle.setAttribute('aria-expanded', (!iconOpen).toString());
                    updateIcon();
                });

                document.addEventListener('click', (event) => {
                    if (!mobileNav.contains(event.target) && event.target !== navToggle && !navToggle.contains(event.target)) {
                        if (!mobileNav.classList.contains('hidden')) {
                            mobileNav.classList.add('hidden');
                            iconOpen = true;
                            navToggle.setAttribute('aria-expanded', 'false');
                            updateIcon();
                        }
                    }
                });

                mobileNav.addEventListener('click', (event) => {
                    if (event.target.closest('a')) {
                        mobileNav.classList.add('hidden');
                        iconOpen = true;
                        navToggle.setAttribute('aria-expanded', 'false');
                        updateIcon();
                    }
                });
            })();
        </script>
        </body>

        </html>
