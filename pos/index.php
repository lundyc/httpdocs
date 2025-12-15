<?php
require_once __DIR__ . "/../shared/includes/simple_session.php";
require_once __DIR__ . "/../portal/auth/auth_functions.php";
require_once __DIR__ . "/../portal/auth/middleware.php";

// Require login for POS access
requireLogin();

$db = getDB();

// Get current user info
$currentUserId = $_SESSION['user_id'] ?? null;
$stmt = $db->prepare("SELECT u.name, r.name AS role_name FROM users u 
                      JOIN roles r ON u.role_id = r.id 
                      WHERE u.id = ?");
$stmt->execute([$currentUserId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle form submissions
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        switch ($action) {
            case 'open_session':
                $location_id = (int)$_POST['location_id'];
                $start_float = (float)$_POST['start_float'];

                // Check if there's already an open session for this location
                $check = $db->prepare("SELECT id FROM pos_sessions WHERE location_id = ? AND status = 'open'");
                $check->execute([$location_id]);

                if ($check->fetch()) {
                    $error = "There is already an open session for this location.";
                } else {
                    $stmt = $db->prepare("INSERT INTO pos_sessions (location_id, opened_by, start_float, status) VALUES (?, ?, ?, 'open')");
                    $stmt->execute([$location_id, $currentUserId, $start_float]);

                    logAction($currentUserId, "Opened POS session for location ID $location_id with float $start_float");
                    $message = "POS session opened successfully!";
                }
                break;

            case 'close_session':
                $session_id = (int)$_POST['session_id'];
                $end_float = (float)$_POST['end_float'];

                // Get session details for variance calculation
                $sessionStmt = $db->prepare("SELECT start_float, (SELECT COALESCE(SUM(price * quantity), 0) FROM pos_sales WHERE session_id = ? AND payment_method = 'cash') as cash_sales FROM pos_sessions WHERE id = ?");
                $sessionStmt->execute([$session_id, $session_id]);
                $sessionData = $sessionStmt->fetch(PDO::FETCH_ASSOC);

                $expected_float = $sessionData['start_float'] + $sessionData['cash_sales'];
                $variance = $end_float - $expected_float;

                $stmt = $db->prepare("UPDATE pos_sessions SET status = 'closed', end_float = ?, variance = ?, closed_by = ?, closed_at = NOW() WHERE id = ?");
                $stmt->execute([$end_float, $variance, $currentUserId, $session_id]);

                logAction($currentUserId, "Closed POS session ID $session_id with variance $variance");
                $message = "POS session closed successfully! Variance: £" . number_format($variance, 2);
                break;

            case 'record_sale':
                $session_id = (int)$_POST['session_id'];
                $item_name = trim($_POST['item_name']);
                $price = (float)$_POST['amount'];
                $quantity = (int)$_POST['quantity'];
                $payment_method = $_POST['payment_method'];

                $total_amount = $price * $quantity;

                $stmt = $db->prepare("INSERT INTO pos_sales (session_id, item_name, quantity, price, payment_method) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$session_id, $item_name, $quantity, $price, $payment_method]);

                logAction($currentUserId, "Recorded sale: $quantity x $item_name = £$total_amount ($payment_method)");
                $message = "Sale recorded successfully!";
                break;

            case 'record_refund':
                $session_id = (int)$_POST['session_id'];
                $item_name = trim($_POST['item_name']);
                $refund_amount = (float)$_POST['amount'];
                $reason = trim($_POST['reason']);

                $stmt = $db->prepare("INSERT INTO pos_refunds (session_id, refunded_by, reason, refund_amount) VALUES (?, ?, ?, ?)");
                $stmt->execute([$session_id, $currentUserId, $reason, $refund_amount]);

                logAction($currentUserId, "Processed refund: $item_name £$refund_amount - $reason");
                $message = "Refund processed successfully!";
                break;
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Get POS locations
$locations = $db->query("SELECT * FROM pos_locations ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Get open sessions
$openSessions = $db->query("
    SELECT ps.*, pl.name as location_name, u.name as opened_by_name
    FROM pos_sessions ps 
    JOIN pos_locations pl ON ps.location_id = pl.id 
    JOIN users u ON ps.opened_by = u.id 
    WHERE ps.status = 'open' 
    ORDER BY ps.opened_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Get recent sales for open sessions
$recentSales = [];
if (!empty($openSessions)) {
    $sessionIds = array_column($openSessions, 'id');
    $placeholders = str_repeat('?,', count($sessionIds) - 1) . '?';
    $salesStmt = $db->prepare("
        SELECT ps.*, pl.name as location_name 
        FROM pos_sales ps 
        JOIN pos_sessions pss ON ps.session_id = pss.id 
        JOIN pos_locations pl ON pss.location_id = pl.id 
        WHERE ps.session_id IN ($placeholders) 
        ORDER BY ps.created_at DESC 
        LIMIT 10
    ");
    $salesStmt->execute($sessionIds);
    $recentSales = $salesStmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Club Hub POS System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/79f3c02ae0.js" crossorigin="anonymous"></script>
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
                        'maroon-lg': '0 35px 70px -30px rgba(0,13,61,0.45)',
                        'gold-soft': '0 30px 60px -40px rgba(255,195,0,0.5)',
                    }
                }
            }
        };
    </script>
</head>

<body class="min-h-screen bg-maroon font-sans text-cream">
    <div class="container mx-auto max-w-7xl px-6 py-8">
        <!-- Header -->
        <div class="rounded-3xl border border-gold/15 bg-burgundy/80 p-8 mb-8 shadow-maroon-lg backdrop-blur">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
                <div>
                    <h1 class="text-4xl font-bold text-cream mb-2">💰 My Club Hub POS System</h1>
                    <h2 class="text-xl text-gold">Welcome, <?php echo htmlspecialchars($user['name'] ?? 'Guest'); ?>!</h2>
                    <span class="text-sm text-cream/70 font-semibold uppercase tracking-[0.3em]">Point of Sale Operations</span>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="/portal/" class="inline-flex items-center gap-2 rounded-full border border-gold/25 px-4 py-2 text-cream transition hover:bg-gold/10">
                        <i class="fa-solid fa-users text-gold"></i> Team Portal
                    </a>
                    <a href="/admin/" class="inline-flex items-center gap-2 rounded-full border border-gold/25 px-4 py-2 text-cream transition hover:bg-gold/10">
                        <i class="fa-solid fa-sitemap text-gold"></i> Admin
                    </a>
                    <a href="/auth/logout.php" class="inline-flex items-center gap-2 rounded-full border border-gold/25 bg-burgundy/50 px-4 py-2 text-gold transition hover:bg-burgundy/70">
                        <i class="fa-solid fa-power-off"></i> Logout
                    </a>
                </div>
            </div>
        </div>

        <!-- Messages -->
        <?php if ($message): ?>
            <div class="rounded-xl border border-teal/30 bg-teal/10 p-4 mb-6">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-check-circle text-teal text-xl"></i>
                    <span class="font-semibold text-cream"><?php echo htmlspecialchars($message); ?></span>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="rounded-xl border border-red-500/30 bg-red-900/30 p-4 mb-6">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-exclamation-triangle text-red-300 text-xl"></i>
                    <span class="font-semibold text-red-300"><?php echo htmlspecialchars($error); ?></span>
                </div>
            </div>
        <?php endif; ?>

        <!-- Main POS Grid -->
        <div class="grid gap-6 lg:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 mb-8">
            <!-- Open New Session -->
            <div class="rounded-3xl border border-gold/15 bg-burgundy/80 p-6 shadow-maroon-lg">
                <div class="flex items-center gap-4 mb-6">
                    <div class="text-4xl">🔓</div>
                    <div class="text-xl font-bold text-cream">Open New Session</div>
                </div>
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="open_session">
                    <div>
                        <label class="block text-sm font-semibold text-cream/80 mb-2">Location:</label>
                        <select name="location_id" required class="w-full px-4 py-3 bg-burgundy/70 border border-burgundy/40 rounded-xl text-cream focus:border-gold focus:outline-none transition">
                            <option value="">Select Location</option>
                            <?php foreach ($locations as $location): ?>
                                <option value="<?php echo $location['id']; ?>">
                                    <?php echo htmlspecialchars($location['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-cream/80 mb-2">Starting Float (£):</label>
                        <input type="number" name="start_float" step="0.01" min="0" required
                            class="w-full px-4 py-3 bg-burgundy/70 border border-burgundy/40 rounded-xl text-cream focus:border-gold focus:outline-none transition">
                    </div>
                    <button type="submit" class="w-full px-6 py-3 bg-gradient-to-r from-gold to-yellow-500 text-maroon font-bold rounded-xl hover:from-yellow-400 hover:to-gold transition transform hover:scale-105">
                        Open Session
                    </button>
                </form>
            </div>

            <!-- Active Sessions -->
            <?php foreach ($openSessions as $session): ?>
                <div class="rounded-3xl border border-gold/15 bg-burgundy/80 p-6 shadow-maroon-lg">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="text-4xl">🏪</div>
                        <div class="text-xl font-bold text-cream"><?php echo htmlspecialchars($session['location_name']); ?></div>
                    </div>

                    <div class="bg-burgundy/60 rounded-xl p-4 mb-6 border border-gold/15">
                        <div class="inline-flex items-center gap-2 px-3 py-1 bg-teal/20 text-teal rounded-full text-sm font-bold uppercase tracking-wide mb-3">
                            <div class="w-2 h-2 bg-teal rounded-full animate-pulse"></div>
                            OPEN
                        </div>
                        <div class="space-y-2 text-sm">
                            <p><span class="font-semibold text-cream/80">Opened by:</span> <span class="text-cream"><?php echo htmlspecialchars($session['opened_by_name']); ?></span></p>
                            <p><span class="font-semibold text-cream/80">Start Float:</span> <span class="text-gold font-bold">£<?php echo number_format($session['start_float'], 2); ?></span></p>
                            <p><span class="font-semibold text-cream/80">Opened:</span> <span class="text-cream"><?php echo date('H:i', strtotime($session['opened_at'])); ?></span></p>
                        </div>
                    </div>

                    <!-- Quick Sale Buttons -->
                    <div class="grid grid-cols-2 gap-3 mb-6">
                        <button class="p-4 bg-burgundy/70 border border-burgundy/40 rounded-xl hover:border-gold hover:bg-burgundy/80 transition text-center font-semibold"
                            onclick="quickSale(<?php echo $session['id']; ?>, 'Pint', 4.50)">
                            <div class="text-2xl mb-1">🍺</div>
                            <div class="text-xs text-cream/80">Pint</div>
                            <div class="text-gold font-bold">£4.50</div>
                        </button>
                        <button class="p-4 bg-burgundy/70 border border-burgundy/40 rounded-xl hover:border-gold hover:bg-burgundy/80 transition text-center font-semibold"
                            onclick="quickSale(<?php echo $session['id']; ?>, 'Half Pint', 2.50)">
                            <div class="text-2xl mb-1">🍺</div>
                            <div class="text-xs text-cream/80">Half</div>
                            <div class="text-gold font-bold">£2.50</div>
                        </button>
                        <button class="p-4 bg-burgundy/70 border border-burgundy/40 rounded-xl hover:border-gold hover:bg-burgundy/80 transition text-center font-semibold"
                            onclick="quickSale(<?php echo $session['id']; ?>, 'Soft Drink', 2.00)">
                            <div class="text-2xl mb-1">🥤</div>
                            <div class="text-xs text-cream/80">Soft</div>
                            <div class="text-gold font-bold">£2.00</div>
                        </button>
                        <button class="p-4 bg-burgundy/70 border border-burgundy/40 rounded-xl hover:border-gold hover:bg-burgundy/80 transition text-center font-semibold"
                            onclick="quickSale(<?php echo $session['id']; ?>, 'Crisps', 1.50)">
                            <div class="text-2xl mb-1">🥔</div>
                            <div class="text-xs text-cream/80">Crisps</div>
                            <div class="text-gold font-bold">£1.50</div>
                        </button>
                    </div>

                    <!-- Manual Sale Form -->
                    <form method="POST" class="mb-6">
                        <input type="hidden" name="action" value="record_sale">
                        <input type="hidden" name="session_id" value="<?php echo $session['id']; ?>">
                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <input type="text" name="item_name" placeholder="Item name" required
                                class="col-span-2 px-3 py-2 bg-burgundy/70 border border-burgundy/40 rounded-lg text-cream placeholder-cream/60 focus:border-gold focus:outline-none transition">
                            <input type="number" name="amount" placeholder="Price" step="0.01" min="0" required
                                class="px-3 py-2 bg-burgundy/70 border border-burgundy/40 rounded-lg text-cream placeholder-cream/60 focus:border-gold focus:outline-none transition">
                            <input type="number" name="quantity" value="1" min="1" required
                                class="px-3 py-2 bg-burgundy/70 border border-burgundy/40 rounded-lg text-cream focus:border-gold focus:outline-none transition">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <select name="payment_method" required
                                class="px-3 py-2 bg-burgundy/70 border border-burgundy/40 rounded-lg text-cream focus:border-gold focus:outline-none transition">
                                <option value="cash">💵 Cash</option>
                                <option value="card">💳 Card</option>
                            </select>
                            <button type="submit" class="px-4 py-2 bg-gradient-to-r from-gold to-yellow-500 text-maroon font-bold rounded-lg hover:from-yellow-400 hover:to-gold transition transform hover:scale-105">
                                Record Sale
                            </button>
                        </div>
                    </form>

                    <!-- Refund Form -->
                    <details class="mb-4">
                        <summary class="cursor-pointer font-bold text-red-300 flex items-center gap-2 hover:text-red-200 transition">
                            <i class="fa-solid fa-undo"></i> Process Refund
                        </summary>
                        <form method="POST" class="mt-4 space-y-3 p-4 bg-red-900/20 border border-red-500/30 rounded-xl">
                            <input type="hidden" name="action" value="record_refund">
                            <input type="hidden" name="session_id" value="<?php echo $session['id']; ?>">
                            <input type="text" name="item_name" placeholder="Item name" required
                                class="w-full px-3 py-2 bg-burgundy/70 border border-burgundy/40 rounded-lg text-cream placeholder-cream/60 focus:border-gold focus:outline-none transition">
                            <input type="number" name="amount" placeholder="Refund amount" step="0.01" min="0" required
                                class="w-full px-3 py-2 bg-burgundy/70 border border-burgundy/40 rounded-lg text-cream placeholder-cream/60 focus:border-gold focus:outline-none transition">
                            <textarea name="reason" placeholder="Reason for refund" required
                                class="w-full px-3 py-2 bg-burgundy/70 border border-burgundy/40 rounded-lg text-cream placeholder-cream/60 focus:border-gold focus:outline-none transition resize-none"></textarea>
                            <button type="submit" class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg transition">
                                Process Refund
                            </button>
                        </form>
                    </details>

                    <!-- Close Session -->
                    <details>
                        <summary class="cursor-pointer font-bold text-cream/70 flex items-center gap-2 hover:text-cream/80 transition">
                            <i class="fa-solid fa-lock"></i> Close Session
                        </summary>
                        <form method="POST" class="mt-4 space-y-3 p-4 bg-burgundy/60 border border-burgundy/40 rounded-xl">
                            <input type="hidden" name="action" value="close_session">
                            <input type="hidden" name="session_id" value="<?php echo $session['id']; ?>">
                            <div>
                                <label class="block text-sm font-semibold text-cream/80 mb-2">End Float (£):</label>
                                <input type="number" name="end_float" step="0.01" min="0" required
                                    class="w-full px-3 py-2 bg-burgundy/70 border border-burgundy/40 rounded-lg text-cream focus:border-gold focus:outline-none transition">
                            </div>
                            <button type="submit" class="w-full px-4 py-2 bg-charcoal hover:bg-burgundy/80 text-white font-bold rounded-lg transition">
                                Close Session
                            </button>
                        </form>
                    </details>
                </div>
            <?php endforeach; ?>

            <!-- Recent Sales -->
            <?php if (!empty($recentSales)): ?>
                <div class="rounded-3xl border border-gold/15 bg-burgundy/80 p-6 shadow-maroon-lg">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="text-4xl">📊</div>
                        <div class="text-xl font-bold text-cream">Recent Sales</div>
                    </div>
                    <div class="max-h-80 overflow-y-auto space-y-3">
                        <?php foreach ($recentSales as $sale): ?>
                            <div class="flex justify-between items-center p-4 bg-burgundy/60 rounded-xl border border-gold/15">
                                <div>
                                    <div class="font-bold text-cream"><?php echo htmlspecialchars($sale['item_name']); ?></div>
                                    <div class="text-sm text-cream/70"><?php echo htmlspecialchars($sale['location_name']); ?> • <?php echo date('H:i', strtotime($sale['created_at'])); ?></div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-gold text-lg">£<?php echo number_format($sale['price'] * $sale['quantity'], 2); ?></div>
                                    <div class="text-sm text-cream/70"><?php echo ucfirst($sale['payment_method']); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function quickSale(sessionId, itemName, amount) {
            // Create a form and submit it
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="record_sale">
                <input type="hidden" name="session_id" value="${sessionId}">
                <input type="hidden" name="item_name" value="${itemName}">
                <input type="hidden" name="amount" value="${amount}">
                <input type="hidden" name="quantity" value="1">
                <input type="hidden" name="payment_method" value="cash">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    </script>
</body>

</html>
