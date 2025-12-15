<?php
require_once __DIR__ . "/../../shared/includes/simple_session.php";
require_once __DIR__ . "/../../portal/auth/middleware.php";

// Require admin access (roles 1 or 2)
checkRole([1, 2]);

$db = getDB();
$success = '';
$error = '';

// Handle sponsor actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF protection would go here

    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $name = trim($_POST['name'] ?? '');
        $tier = $_POST['tier'] ?? '';
        $description = trim($_POST['description'] ?? '');
        $website = trim($_POST['website'] ?? '');
        $contact_name = trim($_POST['contact_name'] ?? '');
        $contact_email = trim($_POST['contact_email'] ?? '');
        $contact_phone = trim($_POST['contact_phone'] ?? '');
        $logo_url = trim($_POST['logo_url'] ?? '');
        $contribution_amount = $_POST['contribution_amount'] ?? null;
        $start_date = $_POST['start_date'] ?? '';
        $end_date = $_POST['end_date'] ?? '';
        $status = $_POST['status'] ?? 'active';
        $notes = trim($_POST['notes'] ?? '');

        if ($name && $tier) {
            try {
                $stmt = $db->prepare("
                    INSERT INTO sponsors (name, tier, description, website, contact_name, contact_email, contact_phone, logo_url, contribution_amount, start_date, end_date, status, notes) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$name, $tier, $description, $website, $contact_name, $contact_email, $contact_phone, $logo_url, $contribution_amount, $start_date ?: null, $end_date ?: null, $status, $notes]);

                // Log the action
                logAction($_SESSION['user_id'], "Created sponsor: {$name} ({$tier})");

                $success = "Sponsor created successfully!";
            } catch (PDOException $e) {
                $error = "Error creating sponsor: " . $e->getMessage();
            }
        } else {
            $error = "Please fill in all required fields.";
        }
    } elseif ($action === 'edit') {
        $sponsor_id = $_POST['sponsor_id'] ?? '';
        $name = trim($_POST['name'] ?? '');
        $tier = $_POST['tier'] ?? '';
        $description = trim($_POST['description'] ?? '');
        $website = trim($_POST['website'] ?? '');
        $contact_name = trim($_POST['contact_name'] ?? '');
        $contact_email = trim($_POST['contact_email'] ?? '');
        $contact_phone = trim($_POST['contact_phone'] ?? '');
        $logo_url = trim($_POST['logo_url'] ?? '');
        $contribution_amount = $_POST['contribution_amount'] ?? null;
        $start_date = $_POST['start_date'] ?? '';
        $end_date = $_POST['end_date'] ?? '';
        $status = $_POST['status'] ?? 'active';
        $notes = trim($_POST['notes'] ?? '');

        if ($sponsor_id && $name && $tier) {
            try {
                $stmt = $db->prepare("
                    UPDATE sponsors 
                    SET name=?, tier=?, description=?, website=?, contact_name=?, contact_email=?, contact_phone=?, logo_url=?, contribution_amount=?, start_date=?, end_date=?, status=?, notes=?, updated_at=NOW()
                    WHERE id=?
                ");
                $stmt->execute([$name, $tier, $description, $website, $contact_name, $contact_email, $contact_phone, $logo_url, $contribution_amount, $start_date ?: null, $end_date ?: null, $status, $notes, $sponsor_id]);

                // Log the action
                logAction($_SESSION['user_id'], "Updated sponsor: {$name} ({$tier})");

                $success = "Sponsor updated successfully!";
            } catch (PDOException $e) {
                $error = "Error updating sponsor: " . $e->getMessage();
            }
        } else {
            $error = "Please fill in all required fields.";
        }
    } elseif ($action === 'delete') {
        $sponsor_id = $_POST['sponsor_id'] ?? '';

        if ($sponsor_id) {
            try {
                // Get sponsor details for logging
                $stmt = $db->prepare("SELECT name, tier FROM sponsors WHERE id = ?");
                $stmt->execute([$sponsor_id]);
                $sponsor = $stmt->fetch(PDO::FETCH_ASSOC);

                // Delete the sponsor
                $stmt = $db->prepare("DELETE FROM sponsors WHERE id = ?");
                $stmt->execute([$sponsor_id]);

                // Log the action
                if ($sponsor) {
                    logAction($_SESSION['user_id'], "Deleted sponsor: {$sponsor['name']} ({$sponsor['tier']})");
                }

                $success = "Sponsor deleted successfully!";
            } catch (PDOException $e) {
                $error = "Error deleting sponsor: " . $e->getMessage();
            }
        }
    }
}

// Get sponsors with pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Get filters
$tier_filter = $_GET['tier'] ?? '';
$status_filter = $_GET['status'] ?? '';

// Build WHERE clause
$whereConditions = [];
$whereParams = [];

if ($tier_filter) {
    $whereConditions[] = "tier = ?";
    $whereParams[] = $tier_filter;
}
if ($status_filter) {
    $whereConditions[] = "status = ?";
    $whereParams[] = $status_filter;
}

$whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

// Get total count
$countStmt = $db->prepare("SELECT COUNT(*) FROM sponsors $whereClause");
$countStmt->execute($whereParams);
$totalSponsors = $countStmt->fetchColumn();
$totalPages = ceil($totalSponsors / $perPage);

// Get sponsors
$stmt = $db->prepare("SELECT * FROM sponsors $whereClause ORDER BY tier DESC, name ASC LIMIT ? OFFSET ?");
$stmt->execute(array_merge($whereParams, [$perPage, $offset]));
$sponsors = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get tier counts for dashboard
$tierCounts = $db->query("SELECT tier, COUNT(*) as count FROM sponsors WHERE status = 'active' GROUP BY tier")->fetchAll(PDO::FETCH_KEY_PAIR);

// Using PHP sessions now
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sponsors Management - My Club Hub Admin</title>
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

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: radial-gradient(circle at 18% 22%, rgba(255, 195, 0, 0.1) 0, transparent 42%),
                radial-gradient(circle at 82% 16%, rgba(0, 53, 102, 0.18) 0, transparent 48%),
                linear-gradient(135deg, #000814, #001d3d 45%, #003566);
            color: var(--frost);
        }

        .header {
            background: linear-gradient(135deg, var(--ink), var(--oxford));
            color: var(--frost);
            padding: 20px;
            margin: -20px -20px 30px -20px;
            border-bottom: 2px solid rgba(255, 195, 0, 0.4);
            box-shadow: 0 12px 30px rgba(0, 13, 61, 0.35);
        }

        .header h1 {
            margin: 0;
            color: var(--school-bus);
        }

        .header p {
            margin: 6px 0 0 0;
            color: var(--muted);
        }

        .nav {
            margin: 20px 0;
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .nav a {
            color: var(--school-bus);
            text-decoration: none;
            font-weight: 600;
        }

        .nav a:hover {
            color: var(--gold);
            text-decoration: underline;
        }

        .alert {
            padding: 15px;
            margin: 20px 0;
            border-radius: 10px;
            border: 1px solid rgba(255, 195, 0, 0.25);
            background: rgba(0, 29, 61, 0.18);
            color: var(--frost);
            box-shadow: 0 10px 18px rgba(0, 8, 20, 0.35);
        }

        .alert-success {
            background: rgba(0, 53, 102, 0.16);
            color: var(--frost);
            border: 1px solid rgba(255, 195, 0, 0.35);
        }

        .alert-error {
            background: rgba(255, 195, 0, 0.14);
            color: var(--ink);
            border: 1px solid rgba(255, 195, 0, 0.45);
        }

        .card {
            background: rgba(246, 248, 255, 0.95);
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 18px 38px rgba(0, 8, 20, 0.25);
            color: #001d3d;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.94);
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 10px 18px rgba(0, 8, 20, 0.2);
            border: 1px solid rgba(18, 48, 79, 0.15);
        }

        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: var(--school-bus);
        }

        .stat-label {
            color: var(--oxford);
            margin-top: 10px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: var(--prussian);
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid var(--border);
            border-radius: 8px;
            box-sizing: border-box;
            background: rgba(246, 248, 255, 0.8);
            color: var(--prussian);
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--gold);
            box-shadow: 0 0 0 2px rgba(255, 195, 0, 0.25);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .form-row-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-weight: 600;
            transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--school-bus), var(--gold));
            color: var(--ink);
        }

        .btn-secondary {
            background: linear-gradient(135deg, var(--oxford), var(--prussian));
            color: var(--frost);
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--ink), var(--prussian));
            color: var(--frost);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--gold), var(--school-bus));
            color: var(--ink);
        }

        .btn:hover {
            opacity: 0.95;
            transform: translateY(-1px);
            box-shadow: 0 10px 20px rgba(0, 13, 61, 0.25);
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .table th,
        .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid rgba(18, 48, 79, 0.2);
        }

        .table th {
            background: rgba(0, 53, 102, 0.08);
            font-weight: bold;
            color: #001d3d;
        }

        .table tr:hover {
            background: rgba(255, 195, 0, 0.08);
        }

        .filters {
            background: rgba(246, 248, 255, 0.95);
            padding: 20px;
            margin: 20px 0;
            border-radius: 12px;
            border: 1px solid rgba(18, 48, 79, 0.15);
            box-shadow: 0 10px 22px rgba(0, 8, 20, 0.2);
        }

        .filters h3 {
            margin: 0 0 15px 0;
            color: #001d3d;
        }

        .filter-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            align-items: end;
        }

        .pagination {
            text-align: center;
            margin: 20px 0;
        }

        .pagination a,
        .pagination span {
            padding: 8px 12px;
            margin: 0 5px;
            text-decoration: none;
            border: 1px solid var(--school-bus);
            color: var(--school-bus);
            border-radius: 8px;
            background: rgba(0, 8, 20, 0.05);
        }

        .pagination .current {
            background: var(--school-bus);
            color: var(--ink);
        }

        .pagination a:hover {
            background: rgba(255, 195, 0, 0.18);
            color: var(--ink);
        }

        .tier-badge {
            padding: 4px 12px;
            border-radius: 16px;
            font-size: 12px;
            font-weight: bold;
            color: white;
        }

        .tier-platinum {
            background: #003566;
            color: #f6f8ff;
        }

        .tier-gold {
            background: #ffc300;
            color: #000814;
        }

        .tier-silver {
            background: #001d3d;
            color: #f6f8ff;
        }

        .tier-bronze {
            background: #ffd60a;
            color: #000814;
        }

        .tier-community {
            background: rgba(0, 53, 102, 0.7);
            color: #f6f8ff;
            border: 1px solid rgba(255, 195, 0, 0.45);
        }

        .status-active {
            color: #ffc300;
            font-weight: bold;
        }

        .status-inactive {
            color: rgba(246, 248, 255, 0.7);
            font-weight: bold;
        }

        .status-pending {
            color: #ffd60a;
            font-weight: bold;
        }

        .sponsor-logo {
            width: 50px;
            height: 50px;
            object-fit: contain;
            border-radius: 4px;
        }

        .sponsor-actions {
            white-space: nowrap;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>🤝 Sponsors Management</h1>
        <p>Manage club sponsors and partnerships</p>
    </div>

    <div class="nav">
        <a href="../index.php">← Admin Dashboard</a>
        <a href="../news/">News</a>
        <a href="../fixtures/">Fixtures</a>
        <a href="../users/">Users</a>
        <span style="float: right; color: #666;">
            Welcome, <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
        </span>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Stats Dashboard -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?php echo $tierCounts['Platinum'] ?? 0; ?></div>
            <div class="stat-label">🏆 Platinum Sponsors</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $tierCounts['Gold'] ?? 0; ?></div>
            <div class="stat-label">🥇 Gold Sponsors</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $tierCounts['Silver'] ?? 0; ?></div>
            <div class="stat-label">🥈 Silver Sponsors</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $tierCounts['Bronze'] ?? 0; ?></div>
            <div class="stat-label">🥉 Bronze Sponsors</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $tierCounts['Community'] ?? 0; ?></div>
            <div class="stat-label">🏘️ Community Sponsors</div>
        </div>
    </div>

    <!-- Create New Sponsor -->
    <div class="card">
        <h2>➕ Add New Sponsor</h2>
        <form method="POST">
            <input type="hidden" name="action" value="create">

            <div class="form-row">
                <div class="form-group">
                    <label for="name">Sponsor Name *</label>
                    <input type="text" id="name" name="name" required placeholder="e.g., Local Business Ltd">
                </div>
                <div class="form-group">
                    <label for="tier">Sponsorship Tier *</label>
                    <select id="tier" name="tier" required>
                        <option value="">Select Tier</option>
                        <option value="Platinum">🏆 Platinum</option>
                        <option value="Gold">🥇 Gold</option>
                        <option value="Silver">🥈 Silver</option>
                        <option value="Bronze">🥉 Bronze</option>
                        <option value="Community">🏘️ Community</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" placeholder="Brief description of the sponsor and their business..."></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="website">Website</label>
                    <input type="url" id="website" name="website" placeholder="https://www.example.com">
                </div>
                <div class="form-group">
                    <label for="logo_url">Logo URL</label>
                    <input type="url" id="logo_url" name="logo_url" placeholder="https://www.example.com/logo.png">
                </div>
            </div>

            <h3>Contact Information</h3>
            <div class="form-row-3">
                <div class="form-group">
                    <label for="contact_name">Contact Name</label>
                    <input type="text" id="contact_name" name="contact_name" placeholder="John Smith">
                </div>
                <div class="form-group">
                    <label for="contact_email">Contact Email</label>
                    <input type="email" id="contact_email" name="contact_email" placeholder="john@example.com">
                </div>
                <div class="form-group">
                    <label for="contact_phone">Contact Phone</label>
                    <input type="tel" id="contact_phone" name="contact_phone" placeholder="01294 123456">
                </div>
            </div>

            <h3>Sponsorship Details</h3>
            <div class="form-row-3">
                <div class="form-group">
                    <label for="contribution_amount">Contribution Amount (£)</label>
                    <input type="number" id="contribution_amount" name="contribution_amount" step="0.01" placeholder="500.00">
                </div>
                <div class="form-group">
                    <label for="start_date">Start Date</label>
                    <input type="date" id="start_date" name="start_date">
                </div>
                <div class="form-group">
                    <label for="end_date">End Date</label>
                    <input type="date" id="end_date" name="end_date">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="active">Active</option>
                        <option value="pending">Pending</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" placeholder="Additional notes about the sponsorship..."></textarea>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Create Sponsor</button>
        </form>
    </div>

    <!-- Filters -->
    <div class="filters">
        <h3>🔍 Filter Sponsors</h3>
        <form method="GET">
            <div class="filter-row">
                <div class="form-group">
                    <label for="tier">Tier</label>
                    <select id="tier" name="tier">
                        <option value="">All Tiers</option>
                        <option value="Platinum" <?php echo $tier_filter === 'Platinum' ? 'selected' : ''; ?>>🏆 Platinum</option>
                        <option value="Gold" <?php echo $tier_filter === 'Gold' ? 'selected' : ''; ?>>🥇 Gold</option>
                        <option value="Silver" <?php echo $tier_filter === 'Silver' ? 'selected' : ''; ?>>🥈 Silver</option>
                        <option value="Bronze" <?php echo $tier_filter === 'Bronze' ? 'selected' : ''; ?>>🥉 Bronze</option>
                        <option value="Community" <?php echo $tier_filter === 'Community' ? 'selected' : ''; ?>>🏘️ Community</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">All Statuses</option>
                        <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-secondary">Apply Filters</button>
                    <a href="/admin/sponsors/" class="btn btn-secondary">Clear</a>
                </div>
            </div>
        </form>
    </div>

    <!-- Sponsors List -->
    <div class="card">
        <h2>🤝 All Sponsors (<?php echo $totalSponsors; ?> total)</h2>

        <?php if (empty($sponsors)): ?>
            <p>No sponsors found. Create your first sponsor above!</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Logo</th>
                        <th>Name</th>
                        <th>Tier</th>
                        <th>Contact</th>
                        <th>Contribution</th>
                        <th>Period</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sponsors as $sponsor): ?>
                        <tr>
                            <td>
                                <?php if ($sponsor['logo_url']): ?>
                                    <img src="<?php echo htmlspecialchars($sponsor['logo_url']); ?>"
                                        alt="<?php echo htmlspecialchars($sponsor['name']); ?>"
                                        class="sponsor-logo"
                                        onerror="this.style.display='none';">
                                <?php else: ?>
                                    <div style="width: 50px; height: 50px; background: #e9ecef; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 12px; color: #666;">No Logo</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($sponsor['name']); ?></strong>
                                <?php if ($sponsor['website']): ?>
                                    <br><a href="<?php echo htmlspecialchars($sponsor['website']); ?>" target="_blank" style="font-size: 12px; color: #ffc300;">🌐 Website</a>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="tier-badge tier-<?php echo strtolower($sponsor['tier']); ?>">
                                    <?php
                                    $tiers = ['Platinum' => '🏆', 'Gold' => '🥇', 'Silver' => '🥈', 'Bronze' => '🥉', 'Community' => '🏘️'];
                                    echo ($tiers[$sponsor['tier']] ?? '') . ' ' . $sponsor['tier'];
                                    ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($sponsor['contact_name']): ?>
                                    <strong><?php echo htmlspecialchars($sponsor['contact_name']); ?></strong><br>
                                <?php endif; ?>
                                <?php if ($sponsor['contact_email']): ?>
                                    <a href="mailto:<?php echo htmlspecialchars($sponsor['contact_email']); ?>" style="font-size: 12px;">📧 Email</a><br>
                                <?php endif; ?>
                                <?php if ($sponsor['contact_phone']): ?>
                                    <a href="tel:<?php echo htmlspecialchars($sponsor['contact_phone']); ?>" style="font-size: 12px;">📞 Phone</a>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($sponsor['contribution_amount']): ?>
                                    £<?php echo number_format($sponsor['contribution_amount'], 2); ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($sponsor['start_date']): ?>
                                    <?php echo date('M Y', strtotime($sponsor['start_date'])); ?>
                                    <?php if ($sponsor['end_date']): ?>
                                        <br>to <?php echo date('M Y', strtotime($sponsor['end_date'])); ?>
                                    <?php endif; ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="status-<?php echo $sponsor['status']; ?>">
                                    <?php echo ucfirst($sponsor['status']); ?>
                                </span>
                            </td>
                            <td class="sponsor-actions">
                                <button onclick="editSponsor(<?php echo $sponsor['id']; ?>)" class="btn btn-sm btn-primary">Edit</button>
                                <button onclick="deleteSponsor(<?php echo $sponsor['id']; ?>, '<?php echo addslashes($sponsor['name']); ?>')" class="btn btn-sm btn-danger">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&tier=<?php echo urlencode($tier_filter); ?>&status=<?php echo urlencode($status_filter); ?>">← Previous</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?>&tier=<?php echo urlencode($tier_filter); ?>&status=<?php echo urlencode($status_filter); ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&tier=<?php echo urlencode($tier_filter); ?>&status=<?php echo urlencode($status_filter); ?>">Next →</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Edit Modal (Hidden) -->
    <div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 8px; width: 90%; max-width: 800px; max-height: 90%; overflow-y: auto;">
            <h3>✏️ Edit Sponsor</h3>
            <form id="editForm" method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="sponsor_id" id="edit_sponsor_id">

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_name">Sponsor Name *</label>
                        <input type="text" id="edit_name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_tier">Sponsorship Tier *</label>
                        <select id="edit_tier" name="tier" required>
                            <option value="Platinum">🏆 Platinum</option>
                            <option value="Gold">🥇 Gold</option>
                            <option value="Silver">🥈 Silver</option>
                            <option value="Bronze">🥉 Bronze</option>
                            <option value="Community">🏘️ Community</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="edit_description">Description</label>
                    <textarea id="edit_description" name="description"></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_website">Website</label>
                        <input type="url" id="edit_website" name="website">
                    </div>
                    <div class="form-group">
                        <label for="edit_logo_url">Logo URL</label>
                        <input type="url" id="edit_logo_url" name="logo_url">
                    </div>
                </div>

                <h4>Contact Information</h4>
                <div class="form-row-3">
                    <div class="form-group">
                        <label for="edit_contact_name">Contact Name</label>
                        <input type="text" id="edit_contact_name" name="contact_name">
                    </div>
                    <div class="form-group">
                        <label for="edit_contact_email">Contact Email</label>
                        <input type="email" id="edit_contact_email" name="contact_email">
                    </div>
                    <div class="form-group">
                        <label for="edit_contact_phone">Contact Phone</label>
                        <input type="tel" id="edit_contact_phone" name="contact_phone">
                    </div>
                </div>

                <h4>Sponsorship Details</h4>
                <div class="form-row-3">
                    <div class="form-group">
                        <label for="edit_contribution_amount">Contribution Amount (£)</label>
                        <input type="number" id="edit_contribution_amount" name="contribution_amount" step="0.01">
                    </div>
                    <div class="form-group">
                        <label for="edit_start_date">Start Date</label>
                        <input type="date" id="edit_start_date" name="start_date">
                    </div>
                    <div class="form-group">
                        <label for="edit_end_date">End Date</label>
                        <input type="date" id="edit_end_date" name="end_date">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_status">Status</label>
                        <select id="edit_status" name="status">
                            <option value="active">Active</option>
                            <option value="pending">Pending</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_notes">Notes</label>
                        <textarea id="edit_notes" name="notes"></textarea>
                    </div>
                </div>

                <div style="margin-top: 20px;">
                    <button type="submit" class="btn btn-primary">Update Sponsor</button>
                    <button type="button" onclick="closeEditModal()" class="btn btn-secondary">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // JavaScript for editing sponsors
        function editSponsor(sponsorId) {
            const sponsors = <?php echo json_encode($sponsors); ?>;
            const sponsor = sponsors.find(s => s.id == sponsorId);

            if (sponsor) {
                document.getElementById('edit_sponsor_id').value = sponsor.id;
                document.getElementById('edit_name').value = sponsor.name;
                document.getElementById('edit_tier').value = sponsor.tier;
                document.getElementById('edit_description').value = sponsor.description || '';
                document.getElementById('edit_website').value = sponsor.website || '';
                document.getElementById('edit_logo_url').value = sponsor.logo_url || '';
                document.getElementById('edit_contact_name').value = sponsor.contact_name || '';
                document.getElementById('edit_contact_email').value = sponsor.contact_email || '';
                document.getElementById('edit_contact_phone').value = sponsor.contact_phone || '';
                document.getElementById('edit_contribution_amount').value = sponsor.contribution_amount || '';
                document.getElementById('edit_start_date').value = sponsor.start_date || '';
                document.getElementById('edit_end_date').value = sponsor.end_date || '';
                document.getElementById('edit_status').value = sponsor.status;
                document.getElementById('edit_notes').value = sponsor.notes || '';

                document.getElementById('editModal').style.display = 'block';
            }
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        function deleteSponsor(sponsorId, name) {
            if (confirm(`Are you sure you want to delete the sponsor "${name}"?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="sponsor_id" value="${sponsorId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Close modal when clicking outside
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });
    </script>
</body>

</html>
