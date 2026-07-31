<?php
// dashboard.php
require_once 'database/db.php';
start_secure_session();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Statistics Engine queries using secure PDO
$userId = $_SESSION['user_id'];
$totalScans = $pdo->prepare("SELECT COUNT(*) FROM scans WHERE user_id = ?");
$totalScans->execute([$userId]);
$scansCount = $totalScans->fetchColumn();

$uniquePlates = $pdo->prepare("SELECT COUNT(DISTINCT(plate_number)) FROM scans WHERE user_id = ? AND plate_number != 'UNKNOWN'");
$uniquePlates->execute([$userId]);
$uniqueCount = $uniquePlates->fetchColumn();

// Get last 3 activities
$recentStmt = $pdo->prepare("SELECT * FROM scans WHERE user_id = ? ORDER BY scanned_at DESC LIMIT 3");
$recentStmt->execute([$userId]);
$recentScans = $recentStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ANPR Operations Command - Dashboard</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap');

        :root {
            --bg-dark: #070a12;
            --text-light: #eef2f7;
            --text-muted: #64748b;
            --text-dim: #3f4a5e;
            --accent: #35e08c;
            --accent-soft: rgba(53, 224, 140, 0.1);
            --accent-glow: rgba(53, 224, 140, 0.3);
            --cyan: #22d3ee;
            --danger: #fb5a5a;
            --glass-bg: rgba(255, 255, 255, 0.025);
            --glass-border: rgba(255, 255, 255, 0.09);
            --grid-line: rgba(148, 163, 184, 0.05);
            --transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            --font-display: 'Space Grotesk', system-ui, sans-serif;
            --font-mono: 'JetBrains Mono', 'Courier New', monospace;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            -webkit-text-size-adjust: 100%;
        }

        body {
            background-color: var(--bg-dark);
            background-image:
                linear-gradient(var(--grid-line) 1px, transparent 1px),
                linear-gradient(90deg, var(--grid-line) 1px, transparent 1px),
                radial-gradient(circle at 90% 0%, rgba(53, 224, 140, 0.07) 0%, transparent 45%),
                radial-gradient(circle at 0% 100%, rgba(34, 211, 238, 0.05) 0%, transparent 45%);
            background-size: 42px 42px, 42px 42px, 100% 100%, 100% 100%;
            color: var(--text-light);
            font-family: var(--font-display);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
            line-height: 1.5;
        }

        /* Header */
        nav {
            background: rgba(7, 10, 18, 0.78);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--glass-border);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        nav a.brand {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-light);
            text-decoration: none;
            letter-spacing: -0.02em;
            display: flex;
            align-items: center;
            gap: 0.55rem;
            flex-shrink: 0;
        }

        nav a.brand .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--accent);
            box-shadow: 0 0 8px var(--accent-glow);
            flex-shrink: 0;
        }

        nav a.brand span {
            color: var(--accent);
            font-family: var(--font-mono);
            font-weight: 600;
            font-size: 0.95rem;
        }

        nav .nav-links {
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        nav .nav-links a {
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            transition: var(--transition);
            padding: 0.5rem 0.85rem;
            border-radius: 7px;
        }

        nav .nav-links a:hover {
            color: var(--text-light);
            background: rgba(255, 255, 255, 0.04);
        }

        nav .nav-links a.active {
            color: var(--accent);
            background: var(--accent-soft);
        }

        nav .nav-links a.logout-btn {
            color: var(--danger);
        }

        nav .nav-links a.logout-btn:hover {
            color: #fca5a5;
            background: rgba(251, 90, 90, 0.08);
        }

        /* Core Workspace Layout */
        .container {
            width: 100%;
            max-width: 1180px;
            margin: 2.25rem auto;
            padding: 0 1.5rem 2rem;
            flex: 1;
        }

        .welcome-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 2.25rem;
        }

        .welcome-header h2 {
            font-size: clamp(1.5rem, 4vw, 2.1rem);
            font-weight: 700;
            letter-spacing: -0.03em;
            margin-bottom: 0.4rem;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8rem;
            color: var(--text-muted);
            font-family: var(--font-mono);
            letter-spacing: 0.02em;
        }

        .status-dot {
            width: 7px;
            height: 7px;
            background-color: var(--accent);
            border-radius: 50%;
            box-shadow: 0 0 8px var(--accent-glow);
            animation: pulse 2s ease-in-out infinite;
            flex-shrink: 0;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(0.8); }
        }

        .scan-cta {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--accent);
            color: #05140c;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.85rem;
            padding: 0.65rem 1.1rem;
            border-radius: 8px;
            box-shadow: 0 4px 16px var(--accent-glow);
            transition: var(--transition);
            white-space: nowrap;
        }

        .scan-cta:hover {
            background: #4bf29d;
            transform: translateY(-2px);
        }

        .scan-cta svg {
            width: 16px;
            height: 16px;
            stroke: #05140c;
        }

        /* Stats Grid */
        .grid-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 1.15rem;
            margin-bottom: 1.75rem;
        }

        .stat-card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            padding: 1.5rem 1.6rem;
            border-radius: 14px;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.25);
            position: relative;
            overflow: hidden;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 1.1rem;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            border-color: rgba(53, 224, 140, 0.25);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 3px;
            height: 100%;
            background: var(--accent);
            box-shadow: 0 0 12px var(--accent-glow);
        }

        .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: var(--accent-soft);
            border: 1px solid rgba(53, 224, 140, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .stat-icon svg {
            width: 21px;
            height: 21px;
            stroke: var(--accent);
        }

        .stat-card h3 {
            font-size: 0.7rem;
            letter-spacing: 0.09em;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 0.3rem;
            font-family: var(--font-mono);
            font-weight: 600;
        }

        .stat-card p {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-light);
            letter-spacing: -0.02em;
            font-family: var(--font-mono);
        }

        /* Log Panel */
        .card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-radius: 16px;
            padding: 1.85rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .card-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.4rem;
        }

        .card-head h3 {
            font-size: 1.1rem;
            font-weight: 700;
            letter-spacing: -0.01em;
        }

        .card-head a {
            font-size: 0.8rem;
            font-family: var(--font-mono);
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
            white-space: nowrap;
        }

        .card-head a:hover {
            text-decoration: underline;
        }

        /* Table */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            border-radius: 10px;
            border: 1px solid var(--glass-border);
            background-color: rgba(7, 10, 18, 0.45);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.88rem;
            text-align: left;
        }

        th, td {
            padding: 0.9rem 1.15rem;
            border-bottom: 1px solid var(--glass-border);
        }

        th {
            background: rgba(255, 255, 255, 0.02);
            font-weight: 600;
            color: var(--text-muted);
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            font-family: var(--font-mono);
        }

        td {
            font-family: var(--font-mono);
            color: var(--text-light);
            font-size: 0.83rem;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background: rgba(255, 255, 255, 0.015);
        }

        .plate-tag {
            background: var(--accent-soft);
            color: var(--accent);
            padding: 0.3rem 0.7rem;
            border-radius: 6px;
            font-weight: 700;
            font-size: 0.8rem;
            letter-spacing: 0.06em;
            border: 1px solid rgba(53, 224, 140, 0.25);
            display: inline-block;
        }

        table a {
            color: var(--cyan);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            font-family: var(--font-display);
        }

        table a:hover {
            color: var(--text-light);
            text-decoration: underline;
        }

        .empty-row {
            text-align: center;
            color: var(--text-muted);
            padding: 2.25rem 1rem;
            font-family: var(--font-display);
            font-size: 0.92rem;
        }

        .action-link {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
            margin-left: 0.35rem;
        }

        .na-tag {
            color: var(--text-dim);
            font-family: var(--font-display);
            font-size: 0.85rem;
        }

        /* Tablet */
        @media (max-width: 860px) {
            .container {
                margin: 1.75rem auto;
            }
        }

        /* Mobile nav + layout */
        @media (max-width: 700px) {
            nav {
                flex-wrap: wrap;
                gap: 0.75rem;
                padding: 0.9rem 1.1rem;
            }

            nav .nav-links {
                width: 100%;
                justify-content: space-between;
                gap: 0.25rem;
            }

            nav .nav-links a {
                font-size: 0.78rem;
                padding: 0.45rem 0.55rem;
                flex: 1;
                text-align: center;
            }

            .welcome-header {
                flex-direction: column;
                align-items: stretch;
            }

            .scan-cta {
                justify-content: center;
            }

            .card {
                padding: 1.3rem;
                border-radius: 14px;
            }
        }

        /* Card-collapse table for small screens: perfect mobile readability */
        @media (max-width: 640px) {
            .table-responsive {
                border: none;
                background: none;
                overflow: visible;
            }

            table, thead, tbody, th, td, tr {
                display: block;
                width: 100%;
            }

            thead {
                display: none;
            }

            tbody tr {
                background: var(--glass-bg);
                border: 1px solid var(--glass-border);
                border-radius: 12px;
                margin-bottom: 0.85rem;
                padding: 0.9rem 1rem;
            }

            tbody tr:last-child {
                margin-bottom: 0;
            }

            td {
                border-bottom: 1px dashed var(--glass-border);
                padding: 0.6rem 0.15rem;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                text-align: right;
            }

            td:last-child {
                border-bottom: none;
            }

            td::before {
                content: attr(data-label);
                font-family: var(--font-mono);
                font-size: 0.65rem;
                letter-spacing: 0.07em;
                text-transform: uppercase;
                color: var(--text-muted);
                text-align: left;
                flex-shrink: 0;
            }

            .empty-row {
                text-align: left;
                padding: 1.5rem 0.25rem;
            }

            .empty-row::before {
                content: none;
            }
        }

        @media (max-width: 420px) {
            .stat-card {
                padding: 1.25rem 1.3rem;
            }

            .stat-card p {
                font-size: 1.65rem;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .status-dot { animation: none !important; }
        }

        a:focus-visible,
        button:focus-visible {
            outline: 2px solid var(--accent);
            outline-offset: 2px;
        }
    </style>
</head>
<body>

    <nav>
        <a href="dashboard.php" class="brand">
            <span class="dot"></span>
            ANPR<span>Command</span>
        </a>
        <div class="nav-links">
            <a href="dashboard.php" class="active">Dashboard</a>
            <a href="upload.php">Scan Engine</a>
            <a href="history.php">Operations Log</a>
            <a href="logout.php" class="logout-btn" data-confirm-title="Disconnect session" data-confirm="Are you sure you want to disconnect from the dashboard?" data-confirm-text="Disconnect" data-confirm-intent="danger">Disconnect</a>
        </div>
    </nav>

    <div class="container">
        <div class="welcome-header">
            <div>
                <h2>Welcome back, <?= sanitize($_SESSION['username']) ?></h2>
                <div class="status-badge">
                    <div class="status-dot"></div>
                    <span>ANPR NODE ENGINE &bull; OPERATIONAL</span>
                </div>
            </div>
            <a href="upload.php" class="scan-cta">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                New Scan
            </a>
        </div>

        <div class="grid-stats">
            <div class="stat-card">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12h18M3 6h18M3 18h18"/></svg>
                </div>
                <div>
                    <h3>Total Processed Vehicles</h3>
                    <p><?= $scansCount ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="7" width="18" height="10" rx="2"/><path d="M7 7V5a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v2"/></svg>
                </div>
                <div>
                    <h3>Unique License Plates</h3>
                    <p><?= $uniqueCount ?></p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-head">
                <h3>Recent Pipeline Activity</h3>
                <a href="history.php">View full log &rarr;</a>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Capture Timestamp</th>
                            <th>Target Identification</th>
                            <th>Source Payload</th>
                            <th>Cropped Matrix</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($recentScans)): ?>
                            <tr>
                                <td colspan="4" class="empty-row">
                                    No records run yet down current pipeline sequence.
                                    <a href="upload.php" class="action-link">Perform your first scan &rarr;</a>
                                </td>
                            </tr>
                        <?php else: foreach($recentScans as $scan): ?>
                            <tr>
                                <td data-label="Timestamp"><?= $scan['scanned_at'] ?></td>
                                <td data-label="Plate"><span class="plate-tag"><?= sanitize($scan['plate_number']) ?></span></td>
                                <td data-label="Source"><a href="<?= sanitize($scan['vehicle_image_path']) ?>" target="_blank">Open source view</a></td>
                                <td data-label="Matrix">
                                    <?php if($scan['plate_image_path']): ?>
                                        <a href="<?= sanitize($scan['plate_image_path']) ?>" target="_blank">View extracted target</a>
                                    <?php else: ?>
                                        <span class="na-tag">N/A</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>