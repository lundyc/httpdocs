<?php
$pageTitleText = isset($pageTitle) ? "{$pageTitle} - My Club Hub" : "My Club Hub";
$pageDescriptionText = $pageDescription ?? 'My Club Hub - modern community football experiences for every supporter.';
$bodyClass = isset($currentPage) ? "page-{$currentPage}" : 'page-default';
$showBanner = isset($pageBanner) ? (bool) $pageBanner : false;
$bannerEyebrow = $pageBannerEyebrow ?? null;
$bannerTitle = $headerTitle ?? 'My Club Hub';
$bannerSubtitle = $headerSubtitle ?? null;

$navItems = [
    ['href' => '/', 'label' => 'Home', 'key' => 'home'],
    ['href' => '/news/', 'label' => 'News', 'key' => 'news'],
    ['href' => '/fixtures/', 'label' => 'Fixtures', 'key' => 'fixtures'],
    ['href' => '/history/', 'label' => 'History', 'key' => 'history'],
    ['href' => '/sponsors/', 'label' => 'Partners', 'key' => 'sponsors'],
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitleText); ?></title>
    <meta name="description" content="<?= htmlspecialchars($pageDescriptionText); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap"
        rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=typography,line-clamp"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        maroon: '#000814',
                        burgundy: '#001d3d',
                        gold: '#ffc300',
                        cream: '#f6f8ff',
                        charcoal: '#003566',
                        teal: '#ffd60a',
                        tan: '#ffd60a',
                    },
                    fontFamily: {
                        display: ['Poppins', 'Inter', 'ui-sans-serif', 'system-ui'],
                        sans: ['Inter', 'ui-sans-serif', 'system-ui'],
                    },
                    boxShadow: {
                        'maroon-glow': '0 25px 50px -12px rgba(0, 13, 61, 0.38)',
                        'gold-soft': '0 20px 30px -15px rgba(255, 195, 0, 0.45)',
                    },
                    backgroundImage: {
                        'hero-gradient': 'linear-gradient(135deg, rgba(0,8,20,0.96), rgba(0,53,102,0.9))',
                        'maroon-noise': 'linear-gradient(145deg, rgba(0,8,20,0.92), rgba(0,29,61,0.94))',
                    },
                    transitionTimingFunction: {
                        swift: 'cubic-bezier(0.22, 0.61, 0.36, 1)',
                    },
                }
            }
        }
    </script>
    <link rel="stylesheet" href="/assets/css/public.css?v=20251020">
    <?php if (!empty($extraStyles)): ?>
        <?= $extraStyles; ?>
    <?php endif; ?>
</head>

<body class="bg-cream text-charcoal font-sans antialiased <?= htmlspecialchars($bodyClass); ?>">
    <div class="flex min-h-screen flex-col bg-cream">
        <header class="sticky top-0 z-50 border-b border-gold/10 bg-maroon/95 backdrop-blur">
            <div class="mx-auto flex w-full max-w-7xl items-center justify-between gap-6 px-4 py-4 md:px-6 lg:px-8">
                <a href="/" class="flex items-center gap-3 text-cream transition hover:text-gold" aria-label="My Club Hub home">
                    <img src="/shared/assets/logo.png" alt="My Club Hub crest" class="h-12 w-12 rounded-full border border-gold/60 bg-cream/10 p-2">
                    <span class="flex flex-col leading-tight">
                        <span class="text-lg font-semibold uppercase tracking-[0.18em]">My Club Hub</span>
                        <span class="text-xs font-medium uppercase tracking-[0.4em] text-gold/80">Club Hub</span>
                    </span>
                </a>

                <button type="button"
                    class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-gold/30 text-cream transition hover:bg-gold/10 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold md:hidden"
                    data-nav-toggle aria-expanded="false" aria-controls="mobile-nav">
                    <span class="sr-only">Toggle navigation</span>
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" />
                    </svg>
                </button>

                <nav class="hidden md:flex md:items-center md:gap-2 lg:gap-4">
                    <?php foreach ($navItems as $item): ?>
                        <?php
                        $isActive = (isset($currentPage) && $currentPage === $item['key']) ||
                            (!isset($currentPage) && $item['key'] === 'home');
                        ?>
                        <a href="<?= htmlspecialchars($item['href']); ?>"
                            class="rounded-full px-4 py-2 text-sm font-semibold uppercase tracking-[0.18em] transition
                            <?= $isActive ? 'bg-gold text-maroon shadow-gold-soft' : 'text-cream/80 hover:text-gold' ?>">
                            <?= htmlspecialchars($item['label']); ?>
                        </a>
                    <?php endforeach; ?>
                    <a href="/portal/"
                        class="rounded-full border border-gold/40 px-5 py-2 text-sm font-semibold uppercase tracking-[0.2em] text-gold transition hover:bg-gold hover:text-maroon">
                        Team Portal
                    </a>
                </nav>
            </div>

            <div id="mobile-nav"
                class="md:hidden hidden border-t border-gold/10 bg-maroon/95 px-4 pb-6 pt-3 shadow-maroon-glow">
                <nav class="flex flex-col gap-2">
                    <?php foreach ($navItems as $item): ?>
                        <?php
                        $isActive = (isset($currentPage) && $currentPage === $item['key']) ||
                            (!isset($currentPage) && $item['key'] === 'home');
                        ?>
                        <a href="<?= htmlspecialchars($item['href']); ?>"
                            class="rounded-xl px-4 py-3 text-sm font-semibold uppercase tracking-[0.2em] transition
                            <?= $isActive ? 'bg-gold text-maroon shadow-gold-soft' : 'text-cream/80 hover:bg-cream/10 hover:text-gold' ?>">
                            <?= htmlspecialchars($item['label']); ?>
                        </a>
                    <?php endforeach; ?>
                    <a href="/portal/"
                        class="rounded-xl border border-gold/40 px-4 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-gold transition hover:bg-gold hover:text-maroon">
                        Team Portal
                    </a>
                </nav>
            </div>
        </header>

        <?php if ($showBanner): ?>
            <section class="bg-maroon text-cream">
                <div class="mx-auto flex max-w-6xl flex-col gap-4 px-4 py-14 text-center md:px-6 lg:px-8">
                    <?php if (!empty($bannerEyebrow)): ?>
                        <span class="mx-auto max-w-xl rounded-full border border-gold/40 px-4 py-1 text-xs font-semibold uppercase tracking-[0.28em] text-gold/80">
                            <?= htmlspecialchars($bannerEyebrow); ?>
                        </span>
                    <?php endif; ?>
                    <h1 class="text-3xl font-display font-semibold leading-tight tracking-tight text-cream md:text-4xl">
                        <?= htmlspecialchars($bannerTitle); ?>
                    </h1>
                    <?php if (!empty($bannerSubtitle)): ?>
                        <p class="mx-auto max-w-2xl text-sm font-medium uppercase tracking-[0.3em] text-cream/70">
                            <?= htmlspecialchars($bannerSubtitle); ?>
                        </p>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>

        <main class="flex-1">

