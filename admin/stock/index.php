<?php
require_once __DIR__ . "/../../shared/includes/simple_session.php";
require_once __DIR__ . "/../../portal/auth/auth_functions.php";
require_once __DIR__ . "/../../portal/auth/middleware.php";

// Allow only Super Admin (1) or Admin (2)
checkRole([1, 2]);

$db = getDB();

// Handle form submissions
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        switch ($action) {
            case 'create_item':
                $name = trim($_POST['name']);
                $category = $_POST['category'];
                $unit = trim($_POST['unit']);
                $min_stock = (int)$_POST['min_stock'];
                $cost_price = (float)$_POST['cost_price'];
                $sell_price = (float)$_POST['sell_price'];

                $stmt = $db->prepare("INSERT INTO stock_items (name, category, unit, min_stock, cost_price, sell_price, current_stock) VALUES (?, ?, ?, ?, ?, ?, 0)");
                $stmt->execute([$name, $category, $unit, $min_stock, $cost_price, $sell_price]);

                logAction($_SESSION['user_id'], "Created stock item: $name");
                $message = "Stock item created successfully!";
                break;

            case 'record_movement':
                $item_id = (int)$_POST['item_id'];
                $movement_type = $_POST['movement_type'];
                $quantity = (int)$_POST['quantity'];
                $reason = trim($_POST['reason']);
                $unit_cost = (float)($_POST['unit_cost'] ?? 0);

                // Get current stock
                $stmt = $db->prepare("SELECT current_stock, name FROM stock_items WHERE id = ?");
                $stmt->execute([$item_id]);
                $item = $stmt->fetch(PDO::FETCH_ASSOC);

                // Calculate new stock level
                $adjustment = ($movement_type === 'in') ? $quantity : -$quantity;
                $new_stock = $item['current_stock'] + $adjustment;

                if ($new_stock < 0) {
                    $error = "Cannot reduce stock below zero. Current stock: {$item['current_stock']}";
                } else {
                    // Record the movement
                    $stmt = $db->prepare("INSERT INTO stock_movements (item_id, movement_type, quantity, reason, unit_cost, created_by) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$item_id, $movement_type, $quantity, $reason, $unit_cost, $_SESSION['user_id']]);

                    // Update current stock
                    $stmt = $db->prepare("UPDATE stock_items SET current_stock = ? WHERE id = ?");
                    $stmt->execute([$new_stock, $item_id]);

                    logAction($_SESSION['user_id'], "Stock movement: {$item['name']} ($movement_type $quantity)");
                    $message = "Stock movement recorded successfully!";
                }
                break;
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Get all stock items
$items = $db->query("SELECT * FROM stock_items ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Get recent stock movements
$recentMovements = $db->query("
    SELECT sm.*, si.name as item_name, u.name as created_by_name 
    FROM stock_movements sm 
    JOIN stock_items si ON sm.item_id = si.id 
    LEFT JOIN users u ON sm.created_by = u.id 
    ORDER BY sm.created_at DESC 
    LIMIT 20
")->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
$totalItems = count($items);
$lowStockItems = count(array_filter($items, function ($item) {
    return $item['current_stock'] <= $item['min_stock'];
}));
$totalValue = array_sum(array_map(function ($item) {
    return $item['current_stock'] * $item['cost_price'];
}, $items));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Management - My Club Hub Admin</title>
    <style>
        :root {
            --ink: #000814;
            --prussian: #001d3d;
            --oxford: #003566;
            --school-bus: #ffc300;
            --gold: #ffd60a;
            --frost: #f6f8ff;
            --muted: #c7d3e3;
            --border: #12304f;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: radial-gradient(circle at 14% 18%, rgba(255, 195, 0, 0.12) 0, transparent 38%),
                radial-gradient(circle at 86% 12%, rgba(0, 53, 102, 0.18) 0, transparent 46%),
                linear-gradient(135deg, #000814 0%, #001d3d 55%, #003566 100%);
            min-height: 100vh;
            color: var(--frost);
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: rgba(0, 8, 20, 0.72);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 14px 32px rgba(0, 13, 61, 0.35);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 195, 0, 0.3);
        }

        .header h1 {
            color: var(--school-bus);
            font-size: 2.5em;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .nav-links {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 15px;
        }

        .nav-link {
            background: linear-gradient(135deg, var(--oxford), var(--prussian));
            color: var(--frost);
            padding: 10px 20px;
            border: none;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 0.9em;
            border: 1px solid rgba(255, 195, 0, 0.25);
        }

        .nav-link:hover {
            background: linear-gradient(135deg, var(--prussian), var(--oxford));
            transform: translateY(-2px);
            box-shadow: 0 10px 22px rgba(0, 13, 61, 0.28);
        }

        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 600;
            border: 1px solid rgba(255, 195, 0, 0.35);
            background: rgba(0, 29, 61, 0.2);
        }

        .alert.success {
            background: rgba(0, 53, 102, 0.18);
            color: var(--frost);
            border-color: rgba(255, 195, 0, 0.35);
        }

        .alert.error {
            background: rgba(255, 195, 0, 0.15);
            color: #000814;
            border-color: rgba(255, 195, 0, 0.45);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: rgba(246, 248, 255, 0.92);
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 13, 61, 0.18);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(18, 48, 79, 0.18);
            color: #001d3d;
        }

        .stat-number {
            font-size: 2.5em;
            font-weight: 700;
            color: var(--school-bus);
            margin-bottom: 10px;
            display: block;
        }

        .stat-label {
            color: var(--oxford);
            font-size: 0.9em;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .main-card {
            background: rgba(246, 248, 255, 0.94);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 13, 61, 0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(18, 48, 79, 0.15);
            color: #001d3d;
        }

        .card-title {
            font-size: 1.3em;
            font-weight: 700;
            color: #001d3d;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }

        .card-icon {
            font-size: 1.5em;
            margin-right: 15px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #001d3d;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid rgba(18, 48, 79, 0.25);
            border-radius: 10px;
            font-size: 1em;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            background: rgba(246, 248, 255, 0.85);
            color: #001d3d;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--gold);
            box-shadow: 0 0 0 2px rgba(255, 195, 0, 0.25);
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            cursor: pointer;
            font-size: 1em;
        }

        .btn.primary {
            background: linear-gradient(45deg, var(--school-bus), var(--gold));
            color: #000814;
        }

        .btn.primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 13, 61, 0.25);
        }

        .table-container {
            overflow-x: auto;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 13, 61, 0.2);
            margin-top: 20px;
            border: 1px solid rgba(18, 48, 79, 0.18);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(246, 248, 255, 0.98);
        }

        th {
            background: rgba(0, 53, 102, 0.1);
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #001d3d;
            border-bottom: 2px solid rgba(18, 48, 79, 0.18);
        }

        td {
            padding: 10px 12px;
            border-bottom: 1px solid rgba(18, 48, 79, 0.12);
            color: #001d3d;
        }

        tr:hover {
            background: rgba(255, 195, 0, 0.08);
        }

        .stock-level {
            font-weight: 600;
        }

        .stock-level.low {
            color: #f87171;
        }

        .stock-level.ok {
            color: #22c55e;
        }

        .movement-type {
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.8em;
            font-weight: 600;
            text-transform: uppercase;
        }

        .movement-type.in {
            background: rgba(0, 53, 102, 0.12);
            color: #003566;
        }

        .movement-type.out {
            background: rgba(255, 195, 0, 0.14);
            color: #000814;
        }

        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>📦 Stock Management</h1>
            <p>Manage inventory, deliveries, and stock movements</p>
            <div class="nav-links">
                <a href="https://admin.myclubhub.co.uk/" class="nav-link">← Admin Dashboard</a>
                <a href="https://portal.myclubhub.co.uk/" class="nav-link">Team Portal</a>
            </div>
        </div>

        <!-- Messages -->
        <?php if ($message): ?>
            <div class="alert success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-number"><?php echo $totalItems; ?></span>
                <div class="stat-label">Total Items</div>
            </div>
            <div class="stat-card">
                <span class="stat-number"><?php echo $lowStockItems; ?></span>
                <div class="stat-label">Low Stock Items</div>
            </div>
            <div class="stat-card">
                <span class="stat-number">£<?php echo number_format($totalValue, 0); ?></span>
                <div class="stat-label">Stock Value</div>
            </div>
        </div>

        <!-- Dashboard Grid -->
        <div class="dashboard-grid">
            <!-- Add New Stock Item -->
            <div class="main-card">
                <h2 class="card-title">
                    <span class="card-icon">➕</span>
                    Add New Stock Item
                </h2>
                <form method="POST">
                    <input type="hidden" name="action" value="create_item">

                    <div class="form-group">
                        <label>Item Name *:</label>
                        <input type="text" name="name" required>
                    </div>

                    <div class="form-group">
                        <label>Category *:</label>
                        <select name="category" required>
                            <option value="">Select Category</option>
                            <option value="Bar">Bar</option>
                            <option value="Food">Food</option>
                            <option value="Merchandise">Merchandise</option>
                            <option value="Equipment">Equipment</option>
                            <option value="Cleaning">Cleaning</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Unit *:</label>
                        <input type="text" name="unit" placeholder="e.g. bottles, cans, each" required>
                    </div>

                    <div class="form-group">
                        <label>Minimum Stock Level *:</label>
                        <input type="number" name="min_stock" min="0" required>
                    </div>

                    <div class="form-group">
                        <label>Cost Price (£) *:</label>
                        <input type="number" name="cost_price" step="0.01" min="0" required>
                    </div>

                    <div class="form-group">
                        <label>Sell Price (£) *:</label>
                        <input type="number" name="sell_price" step="0.01" min="0" required>
                    </div>

                    <button type="submit" class="btn primary">Add Stock Item</button>
                </form>
            </div>

            <!-- Record Stock Movement -->
            <div class="main-card">
                <h2 class="card-title">
                    <span class="card-icon">📋</span>
                    Record Stock Movement
                </h2>
                <form method="POST">
                    <input type="hidden" name="action" value="record_movement">

                    <div class="form-group">
                        <label>Item *:</label>
                        <select name="item_id" required>
                            <option value="">Select Item</option>
                            <?php foreach ($items as $item): ?>
                                <option value="<?php echo $item['id']; ?>">
                                    <?php echo htmlspecialchars($item['name']); ?>
                                    (Current: <?php echo $item['current_stock']; ?> <?php echo htmlspecialchars($item['unit']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Movement Type *:</label>
                        <select name="movement_type" required>
                            <option value="">Select Type</option>
                            <option value="in">Stock In (Delivery/Return)</option>
                            <option value="out">Stock Out (Sale/Wastage/Donation)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Quantity *:</label>
                        <input type="number" name="quantity" min="1" required>
                    </div>

                    <div class="form-group">
                        <label>Unit Cost (for deliveries):</label>
                        <input type="number" name="unit_cost" step="0.01" min="0">
                    </div>

                    <div class="form-group">
                        <label>Reason *:</label>
                        <input type="text" name="reason" placeholder="e.g. Delivery from supplier, Match day sales, Expired items" required>
                    </div>

                    <button type="submit" class="btn primary">Record Movement</button>
                </form>
            </div>
        </div>

        <!-- Current Stock -->
        <div class="main-card">
            <h2 class="card-title">
                <span class="card-icon">📊</span>
                Current Stock Levels
            </h2>

            <?php if (empty($items)): ?>
                <p style="text-align: center; color: #c7d3e3; padding: 40px;">
                    No stock items found. Add your first stock item above! 📦
                </p>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <th>Category</th>
                                <th>Current Stock</th>
                                <th>Min Stock</th>
                                <th>Unit Cost</th>
                                <th>Sell Price</th>
                                <th>Total Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($item['name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($item['category']); ?></td>
                                    <td>
                                        <span class="stock-level <?php echo ($item['current_stock'] <= $item['min_stock']) ? 'low' : 'ok'; ?>">
                                            <?php echo $item['current_stock']; ?> <?php echo htmlspecialchars($item['unit']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $item['min_stock']; ?> <?php echo htmlspecialchars($item['unit']); ?></td>
                                    <td>£<?php echo number_format($item['cost_price'], 2); ?></td>
                                    <td>£<?php echo number_format($item['sell_price'], 2); ?></td>
                                    <td>£<?php echo number_format($item['current_stock'] * $item['cost_price'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Recent Movements -->
        <?php if (!empty($recentMovements)): ?>
            <div class="main-card">
                <h2 class="card-title">
                    <span class="card-icon">📈</span>
                    Recent Stock Movements
                </h2>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Date/Time</th>
                                <th>Item</th>
                                <th>Type</th>
                                <th>Quantity</th>
                                <th>Reason</th>
                                <th>By</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentMovements as $movement): ?>
                                <tr>
                                    <td><?php echo date('M j, H:i', strtotime($movement['created_at'])); ?></td>
                                    <td><strong><?php echo htmlspecialchars($movement['item_name']); ?></strong></td>
                                    <td>
                                        <span class="movement-type <?php echo $movement['movement_type']; ?>">
                                            <?php echo ucfirst($movement['movement_type']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $movement['quantity']; ?></td>
                                    <td><?php echo htmlspecialchars($movement['reason']); ?></td>
                                    <td><?php echo htmlspecialchars($movement['created_by_name'] ?? 'System'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>

