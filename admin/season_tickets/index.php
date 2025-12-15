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
            case 'create':
                $holder_name = trim($_POST['holder_name']);
                $email = trim($_POST['email']);
                $phone = trim($_POST['phone']);
                $seat_section = trim($_POST['seat_section']);
                $seat_number = trim($_POST['seat_number']);
                $price = (float)$_POST['price'];
                $status = $_POST['status'];

                $stmt = $db->prepare("INSERT INTO season_tickets (holder_name, email, phone, seat_section, seat_number, price, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$holder_name, $email, $phone, $seat_section, $seat_number, $price, $status, $_SESSION['user_id']]);

                logAction($_SESSION['user_id'], "Created season ticket: $holder_name ($seat_section $seat_number)");
                $message = "Season ticket created successfully!";
                break;

            case 'update':
                $id = (int)$_POST['id'];
                $holder_name = trim($_POST['holder_name']);
                $email = trim($_POST['email']);
                $phone = trim($_POST['phone']);
                $seat_section = trim($_POST['seat_section']);
                $seat_number = trim($_POST['seat_number']);
                $price = (float)$_POST['price'];
                $status = $_POST['status'];

                $stmt = $db->prepare("UPDATE season_tickets SET holder_name=?, email=?, phone=?, seat_section=?, seat_number=?, price=?, status=? WHERE id=?");
                $stmt->execute([$holder_name, $email, $phone, $seat_section, $seat_number, $price, $status, $id]);

                logAction($_SESSION['user_id'], "Updated season ticket: $holder_name ($seat_section $seat_number)");
                $message = "Season ticket updated successfully!";
                break;

            case 'delete':
                $id = (int)$_POST['id'];

                // Get season ticket details for logging
                $stmt = $db->prepare("SELECT holder_name, seat_section, seat_number FROM season_tickets WHERE id = ?");
                $stmt->execute([$id]);
                $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

                $stmt = $db->prepare("DELETE FROM season_tickets WHERE id = ?");
                $stmt->execute([$id]);

                logAction($_SESSION['user_id'], "Deleted season ticket: {$ticket['holder_name']} ({$ticket['seat_section']} {$ticket['seat_number']})");
                $message = "Season ticket deleted successfully!";
                break;
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Get all season tickets
$tickets = $db->query("
    SELECT st.*, u.name as created_by_name 
    FROM season_tickets st 
    LEFT JOIN users u ON st.created_by = u.id 
    ORDER BY st.seat_section, st.seat_number
")->fetchAll(PDO::FETCH_ASSOC);

// Get statistics
$totalTickets = count($tickets);
$activeTickets = count(array_filter($tickets, function ($t) {
    return $t['status'] === 'active';
}));
$totalRevenue = array_sum(array_map(function ($t) {
    return $t['status'] === 'active' ? $t['price'] : 0;
}, $tickets));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Season Tickets Management - My Club Hub Admin</title>
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

        .header p {
            color: var(--muted);
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

        .main-card {
            background: rgba(246, 248, 255, 0.94);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 13, 61, 0.2);
            backdrop-filter: blur(10px);
            margin-bottom: 30px;
            border: 1px solid rgba(18, 48, 79, 0.15);
            color: #001d3d;
        }

        .card-title {
            font-size: 1.5em;
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

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
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

        .btn.danger {
            background: linear-gradient(45deg, var(--ink), var(--prussian));
            color: var(--frost);
        }

        .btn.danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 13, 61, 0.25);
        }

        .btn.secondary {
            background: linear-gradient(135deg, var(--oxford), var(--prussian));
            color: var(--frost);
        }

        .btn.secondary:hover {
            background: linear-gradient(135deg, var(--prussian), var(--oxford));
            transform: translateY(-2px);
        }

        .table-container {
            overflow-x: auto;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 13, 61, 0.2);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(246, 248, 255, 0.98);
        }

        th {
            background: rgba(0, 53, 102, 0.1);
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #001d3d;
            border-bottom: 2px solid rgba(18, 48, 79, 0.18);
        }

        td {
            padding: 12px 15px;
            border-bottom: 1px solid rgba(18, 48, 79, 0.12);
            color: #001d3d;
        }

        tr:hover {
            background: rgba(255, 195, 0, 0.08);
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-badge.active {
            background: rgba(0, 53, 102, 0.14);
            color: #003566;
        }

        .status-badge.expired {
            background: rgba(255, 195, 0, 0.16);
            color: #000814;
        }

        .status-badge.suspended {
            background: rgba(0, 29, 61, 0.16);
            color: #ffc300;
        }

        .action-buttons {
            display: flex;
            gap: 5px;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.8em;
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>🎫 Season Tickets Management</h1>
            <p>Manage season ticket holders and seat allocations</p>
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
                <span class="stat-number"><?php echo $totalTickets; ?></span>
                <div class="stat-label">Total Tickets</div>
            </div>
            <div class="stat-card">
                <span class="stat-number"><?php echo $activeTickets; ?></span>
                <div class="stat-label">Active Tickets</div>
            </div>
            <div class="stat-card">
                <span class="stat-number">£<?php echo number_format($totalRevenue, 0); ?></span>
                <div class="stat-label">Season Revenue</div>
            </div>
        </div>

        <!-- Add New Season Ticket -->
        <div class="main-card">
            <h2 class="card-title">
                <span class="card-icon">➕</span>
                <span id="form-title">Add New Season Ticket</span>
            </h2>
            <form method="POST" id="ticket-form">
                <input type="hidden" name="action" value="create" id="form-action">
                <input type="hidden" name="id" id="ticket-id">

                <div class="form-grid">
                    <div class="form-group">
                        <label>Holder Name *:</label>
                        <input type="text" name="holder_name" id="holder_name" required>
                    </div>
                    <div class="form-group">
                        <label>Email:</label>
                        <input type="email" name="email" id="email">
                    </div>
                    <div class="form-group">
                        <label>Phone:</label>
                        <input type="tel" name="phone" id="phone">
                    </div>
                    <div class="form-group">
                        <label>Seat Section *:</label>
                        <select name="seat_section" id="seat_section" required>
                            <option value="">Select Section</option>
                            <option value="Main Stand">Main Stand</option>
                            <option value="East Stand">East Stand</option>
                            <option value="West Stand">West Stand</option>
                            <option value="Family Stand">Family Stand</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Seat Number *:</label>
                        <input type="text" name="seat_number" id="seat_number" required>
                    </div>
                    <div class="form-group">
                        <label>Price (£) *:</label>
                        <input type="number" name="price" id="price" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label>Status *:</label>
                        <select name="status" id="status" required>
                            <option value="active">Active</option>
                            <option value="expired">Expired</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn primary" id="submit-btn">Add Season Ticket</button>
                    <button type="button" class="btn secondary" onclick="resetForm()" id="cancel-btn" style="display: none;">Cancel Edit</button>
                </div>
            </form>
        </div>

        <!-- Season Tickets List -->
        <div class="main-card">
            <h2 class="card-title">
                <span class="card-icon">📋</span>
                Current Season Tickets
            </h2>

            <?php if (empty($tickets)): ?>
                <p style="text-align: center; color: #c7d3e3; padding: 40px;">
                    No season tickets found. Add your first season ticket above! 🎫
                </p>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Holder Name</th>
                                <th>Contact</th>
                                <th>Seat</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tickets as $ticket): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($ticket['holder_name']); ?></strong></td>
                                    <td>
                                        <?php if ($ticket['email']): ?>
                                            <div><?php echo htmlspecialchars($ticket['email']); ?></div>
                                        <?php endif; ?>
                                        <?php if ($ticket['phone']): ?>
                                            <div><?php echo htmlspecialchars($ticket['phone']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($ticket['seat_section']); ?></strong><br>
                                        Seat <?php echo htmlspecialchars($ticket['seat_number']); ?>
                                    </td>
                                    <td><strong>£<?php echo number_format($ticket['price'], 2); ?></strong></td>
                                    <td>
                                        <span class="status-badge <?php echo $ticket['status']; ?>">
                                            <?php echo ucfirst($ticket['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($ticket['created_at'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn secondary btn-sm" onclick="editTicket(<?php echo htmlspecialchars(json_encode($ticket)); ?>)">
                                                Edit
                                            </button>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this season ticket?')">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $ticket['id']; ?>">
                                                <button type="submit" class="btn danger btn-sm">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function editTicket(ticket) {
            document.getElementById('form-title').textContent = 'Edit Season Ticket';
            document.getElementById('form-action').value = 'update';
            document.getElementById('ticket-id').value = ticket.id;
            document.getElementById('holder_name').value = ticket.holder_name;
            document.getElementById('email').value = ticket.email || '';
            document.getElementById('phone').value = ticket.phone || '';
            document.getElementById('seat_section').value = ticket.seat_section;
            document.getElementById('seat_number').value = ticket.seat_number;
            document.getElementById('price').value = ticket.price;
            document.getElementById('status').value = ticket.status;
            document.getElementById('submit-btn').textContent = 'Update Season Ticket';
            document.getElementById('cancel-btn').style.display = 'inline-block';

            // Scroll to form
            document.getElementById('ticket-form').scrollIntoView({
                behavior: 'smooth'
            });
        }

        function resetForm() {
            document.getElementById('form-title').textContent = 'Add New Season Ticket';
            document.getElementById('form-action').value = 'create';
            document.getElementById('ticket-id').value = '';
            document.getElementById('ticket-form').reset();
            document.getElementById('submit-btn').textContent = 'Add Season Ticket';
            document.getElementById('cancel-btn').style.display = 'none';
        }
    </script>
</body>

</html>

