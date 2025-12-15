<?php
$pageContextLabel = $pageContextLabel ?? 'Team operations';
$pageTitleText = $pageTitleText ?? 'Welcome back';
$pageBadges = $pageBadges ?? [];
$pageActions = $pageActions ?? [];
?>
<header class="border-b border-white/10 bg-slate-900/60 backdrop-blur">
          <div class="mx-auto flex max-w-6xl flex-col gap-6 px-6 py-8 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                              <p class="text-xs font-semibold uppercase tracking-[0.34em] text-gold/70"><?= htmlspecialchars($pageContextLabel); ?></p>
                              <h1 class="mt-2 text-3xl font-semibold tracking-tight text-cream md:text-4xl">
                                        <?= htmlspecialchars($pageTitleText); ?>
                              </h1>
                              <?php if (!empty($pageBadges)): ?>
                                        <div class="mt-3 flex flex-wrap items-center gap-3 text-xs font-semibold uppercase tracking-[0.24em] text-slate-300">
                                                  <?php foreach ($pageBadges as $badge): ?>
                                                            <?php
                                                            $badgeText = htmlspecialchars($badge['text'] ?? '');
                                                            $badgeVariant = $badge['variant'] ?? 'default';
                                                            $badgeClasses = 'rounded-full border px-3 py-1 text-slate-100 ';
                                                            if ($badgeVariant === 'gold') {
                                                                      $badgeClasses .= 'border-gold/40 text-gold';
                                                            } elseif ($badgeVariant === 'neutral') {
                                                                      $badgeClasses .= 'border-white/20';
                                                            } else {
                                                                      $badgeClasses .= $badge['class'] ?? 'border-white/20';
                                                            }
                                                            ?>
                                                            <span class="<?= $badgeClasses; ?>"><?= $badgeText; ?></span>
                                                  <?php endforeach; ?>
                                        </div>
                              <?php endif; ?>
                    </div>
                    <?php if (!empty($pageActions)): ?>
                              <div class="flex flex-wrap items-center gap-3">
                                        <?php foreach ($pageActions as $action): ?>
                                                  <?php
                                                  $actionLabel = htmlspecialchars($action['label'] ?? '');
                                                  $actionHref = htmlspecialchars($action['href'] ?? '#');
                                                  $actionVariant = $action['variant'] ?? 'secondary';
                                                  $actionTarget = isset($action['target']) ? htmlspecialchars($action['target']) : '';
                                                  $baseClasses = 'inline-flex items-center gap-2 rounded-full border px-5 py-2 text-xs font-semibold uppercase tracking-[0.24em] transition';
                                                  if ($actionVariant === 'primary') {
                                                            $classes = $baseClasses . ' border-gold/30 bg-gold text-maroon hover:bg-gold/90';
                                                  } else {
                                                            $classes = $baseClasses . ' border-white/30 text-slate-100 hover:bg-white/10';
                                                  }
                                                  $icon = $action['icon'] ?? null;
                                                  ?>
                                                  <a href="<?= $actionHref; ?>" class="<?= $classes; ?>" <?= $actionTarget ? ' target="' . $actionTarget . '"' : ''; ?>>
                                                            <?php if ($icon): ?>
                                                                      <i class="<?= htmlspecialchars($icon); ?>"></i>
                                                            <?php endif; ?>
                                                            <?= $actionLabel; ?>
                                                  </a>
                                        <?php endforeach; ?>
                              </div>
                    <?php endif; ?>
          </div>
</header>