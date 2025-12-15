<?php
require_once "auth_functions.php";

$redirect = $_GET['redirect'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
          $loginResult = login($_POST['email'], $_POST['password']);

          if ($loginResult) {
                    // Check for redirect parameter from GET or POST
                    $redirectUrl = $_POST['redirect'] ?? $_GET['redirect'] ?? '';

                    if (!empty($redirectUrl)) {
                              header("Location: " . $redirectUrl);
                              exit;
                    }
                    // Use relative path for folder-based structure
                    header("Location: ../index.php");
                    exit;
          } else {
                    $error = "Login failed. Please check your credentials and try again.";
          }
}
$existingEmail = $_POST['email'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
          <meta charset="UTF-8">
          <meta name="viewport" content="width=device-width, initial-scale=1.0">
          <title>My Club Hub Portal Login</title>
          <link rel="preconnect" href="https://fonts.googleapis.com">
          <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
          <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
          <script src="https://cdn.tailwindcss.com"></script>
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
                                                  },
                                                  fontFamily: {
                                                            sans: ['Inter', 'ui-sans-serif', 'system-ui'],
                                                  },
                                                  boxShadow: {
                                                            'maroon-xl': '0 35px 70px -30px rgba(0,13,61,0.52)',
                                                            'gold-soft': '0 20px 40px -20px rgba(255,195,0,0.45)',
                                                  },
                                                  backgroundImage: {
                                                            'maroon-gradient': 'linear-gradient(135deg, rgba(0,8,20,0.96), rgba(0,53,102,0.9))',
                                                  }
                                        }
                              }
                    };
          </script>
</head>

<body class="min-h-screen bg-cream font-sans antialiased">
          <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(255,195,0,0.3)_0,_transparent_55%)]"></div>
          <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_90%,_rgba(0,53,102,0.18)_0,_transparent_45%)]"></div>

          <div class="relative flex min-h-screen items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
                    <div class="w-full max-w-md rounded-3xl border border-maroon/10 bg-white/90 p-8 shadow-maroon-xl backdrop-blur">
                              <div class="mb-10 text-center">
                                        <a href="/" class="mx-auto flex w-fit items-center gap-3 text-maroon transition hover:text-gold">
                                                  <img src="/shared/assets/logo.png" alt="My Club Hub crest"
                                                            class="h-12 w-12 rounded-full border border-gold/60 bg-cream/10 p-2">
                                                  <span class="flex flex-col leading-tight">
                                                            <span class="text-lg font-semibold uppercase tracking-[0.2em]">My Club Hub</span>
                                                            <span class="text-xs font-medium uppercase tracking-[0.34em] text-burgundy/70">Player & Staff Portal</span>
                                                  </span>
                                        </a>
                                        <h1 class="mt-6 text-2xl font-semibold tracking-tight text-maroon">Sign in to continue</h1>
                                        <p class="mt-2 text-sm text-charcoal/70">
                                                  Secure access for club staff, coaches, and players. Your session is shared across systems.
                                        </p>
                              </div>

                              <?php if (!empty($error)): ?>
                                        <div class="mb-6 rounded-2xl border border-maroon/20 bg-maroon/05 px-4 py-3 text-sm text-burgundy">
                                                  <?= htmlspecialchars($error); ?>
                                        </div>
                              <?php endif; ?>

                              <form method="POST" class="space-y-6">
                                        <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect); ?>">

                                        <div class="space-y-2">
                                                  <label for="email" class="text-xs font-semibold uppercase tracking-[0.28em] text-charcoal/60">Email Address</label>
                                                  <input id="email" name="email" type="email" autocomplete="email" required autofocus
                                                            value="<?= htmlspecialchars($existingEmail); ?>"
                                                            class="w-full rounded-2xl border border-maroon/20 bg-cream px-4 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-maroon placeholder:text-charcoal/40 focus:border-maroon focus:outline-none focus:ring-2 focus:ring-maroon/20">
                                        </div>

                                        <div class="space-y-2">
                                                  <label for="password" class="text-xs font-semibold uppercase tracking-[0.28em] text-charcoal/60">Password</label>
                                                  <input id="password" name="password" type="password" autocomplete="current-password" required
                                                            class="w-full rounded-2xl border border-maroon/20 bg-cream px-4 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-maroon placeholder:text-charcoal/40 focus:border-maroon focus:outline-none focus:ring-2 focus:ring-maroon/20">
                                        </div>

                                        <button type="submit"
                                                  class="w-full rounded-full bg-maroon px-6 py-3 text-sm font-semibold uppercase tracking-[0.22em] text-cream shadow-maroon-xl transition hover:-translate-y-1 hover:shadow-maroon-xl focus:outline-none focus:ring-2 focus:ring-maroon/30">
                                                  Sign In
                                        </button>
                              </form>

                              <div class="mt-8 space-y-3 text-center text-xs font-semibold uppercase tracking-[0.24em] text-charcoal/50">
                                        <?php if (!empty($session['id']) && empty($session['user_id'])): ?>
                                                  <p>Session ID: <?= htmlspecialchars($session['id']); ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($redirect)): ?>
                                                  <p>After login you will be redirected to: <br><span class="text-burgundy"><?= htmlspecialchars($redirect); ?></span></p>
                                        <?php endif; ?>
                              </div>

                              <div class="mt-10 rounded-3xl border border-maroon/10 bg-cream/60 p-4 text-center text-xs font-semibold uppercase tracking-[0.24em] text-charcoal/50">
                                        Having trouble? Contact the My Club Hub tech team.
                              </div>
                    </div>
          </div>
</body>

</html>
