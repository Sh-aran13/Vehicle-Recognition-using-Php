<?php
// history.php
require_once 'database/db.php';
start_secure_session();

if (!isset($_SESSION['user_id'])) { 
    header('Location: login.php'); 
    exit; 
}
$userId = $_SESSION['user_id'];

// --- SECURE ACTION CONTROLLER: RECORD DELETION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $scanId = filter_input(INPUT_POST, 'scan_id', FILTER_VALIDATE_INT);
    
    if ($scanId) {
        // Fetch files first to remove them physically from server disk storage arrays
        $fileStmt = $pdo->prepare("SELECT vehicle_image_path, plate_image_path FROM scans WHERE id = ? AND user_id = ?");
        $fileStmt->execute([$scanId, $userId]);
        $files = $fileStmt->fetch();

        if ($files) {
            // Safely unlink assets from disk if they exist
            if (!empty($files['vehicle_image_path']) && file_exists('../' . $files['vehicle_image_path'])) {
                @unlink('../' . $files['vehicle_image_path']);
            }
            if (!empty($files['plate_image_path']) && file_exists('../' . $files['plate_image_path'])) {
                @unlink('../' . $files['plate_image_path']);
            }

            // Purge the row entry from PostgreSQL index metrics safely
            $deleteStmt = $pdo->prepare("DELETE FROM scans WHERE id = ? AND user_id = ?");
            $deleteStmt->execute([$scanId, $userId]);
            
            header("Location: history.php?msg=deleted");
            exit;
        }
    }
}

// Search & Filter parameters mapping
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// Dynamic Pagination Matrix Configuration
$limit = 5; 
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$countQuery = "SELECT COUNT(*) FROM scans WHERE user_id = :user_id";
$dataQuery  = "SELECT * FROM scans WHERE user_id = :user_id";

if (!empty($search)) {
    $countQuery .= " AND plate_number ILIKE :search";
    $dataQuery  .= " AND plate_number ILIKE :search";
}
$dataQuery .= " ORDER BY scanned_at DESC LIMIT :limit OFFSET :offset";

$stmtCount = $pdo->prepare($countQuery);
$stmtCount->bindValue(':user_id', $userId, PDO::PARAM_INT);
if (!empty($search)) $stmtCount->bindValue(':search', "%$search%", PDO::PARAM_STR);
$stmtCount->execute();
$totalRows = $stmtCount->fetchColumn();
$totalPages = ceil($totalRows / $limit);

$stmtData = $pdo->prepare($dataQuery);
$stmtData->bindValue(':user_id', $userId, PDO::PARAM_INT);
if (!empty($search)) $stmtData->bindValue(':search', "%$search%", PDO::PARAM_STR);
$stmtData->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmtData->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmtData->execute();
$scans = $stmtData->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ANPR Operations - Scan Log History</title>
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
            --danger-soft: rgba(251, 90, 90, 0.12);
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
            color: var(--danger) !important;
        }

        nav .nav-links a.logout-btn:hover {
            color: #fca5a5 !important;
            background: rgba(251, 90, 90, 0.08);
        }

        /* Container */
        .container {
            width: 100%;
            max-width: 1180px;
            margin: 2.25rem auto;
            padding: 0 1.5rem 2rem;
            flex: 1;
        }

        .card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-radius: 18px;
            padding: 2rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            animation: fadeInScale 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .header-flex {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            flex-wrap: wrap;
            gap: 1.25rem;
            margin-bottom: 1.75rem;
        }

        .header-flex .heading-block .scan-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-family: var(--font-mono);
            font-size: 0.65rem;
            letter-spacing: 0.13em;
            color: var(--accent);
            background: var(--accent-soft);
            border: 1px solid rgba(53, 224, 140, 0.25);
            padding: 0.28rem 0.6rem;
            border-radius: 100px;
            margin-bottom: 0.75rem;
            text-transform: uppercase;
        }

        .header-flex .heading-block .scan-tag::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--accent);
            animation: pulse 1.6s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(0.8); }
        }

        .header-flex h2 {
            font-size: clamp(1.35rem, 4vw, 1.65rem);
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        /* Search Form */
        .search-form {
            display: flex;
            gap: 0.5rem;
        }

        .search-input-wrap {
            position: relative;
        }

        .search-input-wrap svg {
            position: absolute;
            left: 0.85rem;
            top: 50%;
            transform: translateY(-50%);
            width: 16px;
            height: 16px;
            stroke: var(--text-dim);
            pointer-events: none;
        }

        .form-control {
            padding: 0.65rem 1rem 0.65rem 2.5rem;
            border: 1px solid var(--glass-border);
            border-radius: 8px;
            font-size: 0.88rem;
            font-family: var(--font-mono);
            color: var(--text-light);
            background-color: rgba(7, 10, 18, 0.6);
            transition: var(--transition);
            width: 240px;
        }

        .form-control::placeholder {
            color: var(--text-dim);
            font-family: var(--font-display);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-soft);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            background: var(--accent);
            color: #05140c;
            border: none;
            padding: 0.65rem 1.35rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 700;
            font-size: 0.85rem;
            font-family: var(--font-display);
            transition: var(--transition);
            white-space: nowrap;
        }

        .btn:hover {
            background: #4bf29d;
            transform: translateY(-1px);
        }

        .btn-danger {
            background: var(--danger-soft);
            border: 1px solid rgba(251, 90, 90, 0.3);
            color: #fca5a5;
            padding: 0.4rem 0.85rem;
            font-size: 0.76rem;
            font-weight: 600;
            font-family: var(--font-display);
        }

        .btn-danger:hover {
            background: var(--danger);
            color: #1a0505;
            border-color: var(--danger);
            transform: none;
        }

        .btn-danger svg {
            width: 13px;
            height: 13px;
            stroke: currentColor;
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
            vertical-align: middle;
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

        .na-tag {
            color: var(--text-dim);
            font-family: var(--font-display);
            font-size: 0.85rem;
        }

        .empty-row {
            text-align: center;
            color: var(--text-muted);
            padding: 2.25rem 1rem;
            font-family: var(--font-display);
            font-size: 0.92rem;
        }

        .actions-cell {
            text-align: center;
        }

        /* Pagination */
        .pagination {
            display: flex;
            gap: 0.35rem;
            margin-top: 1.75rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .pagination a {
            padding: 0.5rem 1rem;
            border: 1px solid var(--glass-border);
            text-decoration: none;
            border-radius: 6px;
            color: var(--text-muted);
            font-weight: 500;
            font-size: 0.85rem;
            font-family: var(--font-mono);
            transition: var(--transition);
        }

        .pagination a:hover {
            border-color: var(--accent);
            color: var(--text-light);
        }

        .pagination a.active {
            background: var(--accent);
            color: #05140c;
            border-color: var(--accent);
        }

        /* Tablet */
        @media (max-width: 860px) {
            .container {
                margin: 1.75rem auto;
            }
        }

        /* Mobile nav + header */
        @media (max-width: 768px) {
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
                font-size: 0.76rem;
                padding: 0.45rem 0.5rem;
                flex: 1;
                text-align: center;
            }

            .header-flex {
                flex-direction: column;
                align-items: stretch;
            }

            .search-form {
                width: 100%;
            }

            .search-input-wrap {
                flex: 1;
            }

            .form-control {
                width: 100%;
            }

            .card {
                padding: 1.4rem 1.25rem;
                border-radius: 14px;
            }
        }

        /* Card-collapse table for small screens */
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

            .actions-cell {
                justify-content: flex-end;
            }

            .empty-row {
                text-align: left;
                padding: 1.5rem 0.25rem;
            }

            .empty-row::before {
                content: none;
            }
        }

        @keyframes fadeInScale {
            from { opacity: 0; transform: translateY(12px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        @media (prefers-reduced-motion: reduce) {
            .card, .scan-tag::before { animation: none !important; }
        }

        a:focus-visible,
        button:focus-visible,
        input:focus-visible {
            outline: 2px solid var(--accent);
            outline-offset: 2px;
        }
    </style>
</head>
<body>

    <nav>
        <a href="dashboard.php" class="brand">
            <span class="dot"></span>
            ANPR<span>.command</span>
        </a>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="upload.php">Scan Engine</a>
            <a href="history.php" class="active">Operations Log</a>
            <a href="logout.php" class="logout-btn" data-confirm-title="Disconnect session" data-confirm="Are you sure you want to disconnect from the operations log?" data-confirm-text="Disconnect" data-confirm-intent="danger">Disconnect</a>
        </div>
    </nav>

    <div class="container">
        <div class="card">

            <div class="header-flex">
                <div class="heading-block">
                    <span class="scan-tag">Archive Query Ready</span>
                    <h2>Historical Execution Records</h2>
                </div>
                <form method="GET" action="history.php" class="search-form">
                    <div class="search-input-wrap">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" name="search" placeholder="Search by plate context..." class="form-control" value="<?= $search ?>">
                    </div>
                    <button type="submit" class="btn">Filter</button>
                </form>
            </div>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Scanned Time</th>
                            <th>License Extraction</th>
                            <th>Source Overview</th>
                            <th>Cropped Matrix</th>
                            <th style="text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($scans)): ?>
                            <tr>
                                <td colspan="5" class="empty-row">
                                    No scanning logs match configuration parameters.
                                </td>
                            </tr>
                        <?php else: foreach($scans as $scan): ?>
                            <tr>
                                <td data-label="Time"><?= $scan['scanned_at'] ?></td>
                                <td data-label="Plate"><span class="plate-tag"><?= sanitize($scan['plate_number']) ?></span></td>
                                <td data-label="Source"><a href="<?= sanitize($scan['vehicle_image_path']) ?>" target="_blank">Full image view</a></td>
                                <td data-label="Matrix">
                                    <?php if($scan['plate_image_path']): ?>
                                        <a href="<?= sanitize($scan['plate_image_path']) ?>" target="_blank">Plate crop</a>
                                    <?php else: ?>
                                        <span class="na-tag">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Actions" class="actions-cell">
                                    <form method="POST" action="history.php" data-confirm-title="Delete scan record" data-confirm="Are you sure you want to permanently delete plate <?= sanitize($scan['plate_number']) ?>? This will remove the record and its stored images." data-confirm-text="Delete" data-confirm-intent="danger" style="display:inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="scan_id" value="<?= $scan['id'] ?>">
                                        <button type="submit" class="btn btn-danger">
                                            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if($totalPages > 1): ?>
                <div class="pagination">
                    <?php for($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="history.php?page=<?= $i ?>&search=<?= urlencode($search) ?>" class="<?= $page === $i ? 'active' : '' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>