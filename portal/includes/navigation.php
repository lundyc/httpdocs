<?php
$activeNav = $activeNav ?? '';
$userRoleId = $userRoleId ?? null;

if (!function_exists('myclubhub_portal_nav_link_classes')) {
          function myclubhub_portal_nav_link_classes($activeNav, $identifier)
          {
                    $base = 'flex items-center gap-3 px-4 py-2 rounded-lg transition';
                    $inactive = 'hover:bg-gold/10 hover:text-gold';
                    $active = 'bg-gold/20 text-gold border border-gold/30';

                    return $base . ' ' . ($activeNav === $identifier ? $active : $inactive);
          }
}
?>
<aside class="hidden md:flex flex-col w-72 bg-gradient-to-b from-slate-950 via-slate-900 to-slate-950 border-r border-white/10 px-5 py-6 shadow-xl">
          <div class="flex items-center gap-3 px-2">
                    <img src="/shared/assets/logo.png" alt="My Club Hub crest" class="h-10 w-10 rounded-full border border-gold/60 bg-cream/10 p-2">
                    <div class="leading-tight">
                              <h1 class="text-sm font-semibold text-cream tracking-[0.15em] uppercase">My Club Hub</h1>
                              <p class="text-xs text-gold/80 tracking-[0.2em] uppercase">Team Portal</p>
                    </div>
          </div>

          <hr class="my-5 border-white/10">

          <nav class="flex-1 overflow-y-auto text-sm text-slate-300">
                    <div class="mb-6">
                              <p class="text-xs font-semibold uppercase tracking-[0.3em] text-gold/70 mb-2">Portal</p>
                              <ul class="space-y-1">
                                        <li>
                                                  <a href="/portal/" class="<?= myclubhub_portal_nav_link_classes($activeNav, 'dashboard'); ?>">
                                                            <i class="fa-solid fa-house text-gold"></i> Dashboard
                                                  </a>
                                        </li>
                                        <li>
                                                  <a href="/portal/profile/" class="<?= myclubhub_portal_nav_link_classes($activeNav, 'profile'); ?>">
                                                            <i class="fa-solid fa-id-badge text-gold"></i> Profile
                                                  </a>
                                        </li>
                                        <li>
                                                  <a href="/portal/players/" class="<?= myclubhub_portal_nav_link_classes($activeNav, 'players'); ?>">
                                                            <i class="fa-solid fa-users text-gold"></i> Players
                                                  </a>
                                        </li>
                                        <li>
                                                  <a href="/portal/training/" class="<?= myclubhub_portal_nav_link_classes($activeNav, 'training'); ?>">
                                                            <i class="fa-solid fa-dumbbell text-gold"></i> Training
                                                  </a>
                                        </li>
                                        <li>
                                                  <a href="/portal/matches/" class="<?= myclubhub_portal_nav_link_classes($activeNav, 'matches'); ?>">
                                                            <i class="fa-solid fa-calendar-days text-gold"></i> Matches
                                                  </a>
                                        </li>
                              </ul>
                    </div>

                    <div class="mb-6">
                              <p class="text-xs font-semibold uppercase tracking-[0.3em] text-gold/70 mb-2">Systems</p>
                              <ul class="space-y-1">
                                        <?php if ($userRoleId !== null && $userRoleId <= 2): ?>
                                                  <li>
                                                            <a href="/admin/" class="<?= myclubhub_portal_nav_link_classes($activeNav, 'admin_dashboard'); ?>" target="_blank">
                                                                      <i class="fa-solid fa-sitemap text-gold"></i> Admin Dashboard
                                                            </a>
                                                  </li>
                                        <?php endif; ?>
                                        <li>
                                                  <a href="/pos/" class="<?= myclubhub_portal_nav_link_classes($activeNav, 'pos'); ?>" target="_blank">
                                                            <i class="fa-solid fa-cash-register text-gold"></i> POS System
                                                  </a>
                                        </li>
                                        <li>
                                                  <a href="/" class="<?= myclubhub_portal_nav_link_classes($activeNav, 'public'); ?>" target="_blank">
                                                            <i class="fa-solid fa-globe text-gold"></i> Public Website
                                                  </a>
                                        </li>
                              </ul>
                    </div>
          </nav>

          <div class="mt-auto border-t border-white/10 pt-5 px-3 text-xs text-gold/80 bg-slate-900/50 rounded-xl">
                    <p class="font-semibold uppercase tracking-[0.2em] mb-1">Support</p>
                    <p>Need help? Email <span class="text-cream font-semibold">tech@myclubhub.co.uk</span></p>
          </div>
</aside>
