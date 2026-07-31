<?php
// register.php
require_once 'database/db.php';
start_secure_session();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $email    = sanitize($_POST['email']);
    $password = $_POST['password'];

    if (!empty($username) && !empty($email) && !empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $hashedPassword]);
            $success = "Registration successful! You can now login.";
        } catch (PDOException $e) {
            $error = "Username or Email already exists.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Registration - ANPR Portal</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap');

        :root {
            --bg-dark: #070a12;
            --bg-panel: #0c111d;
            --text-light: #eef2f7;
            --text-muted: #64748b;
            --text-dim: #3f4a5e;
            --accent: #35e08c;
            --accent-soft: rgba(53, 224, 140, 0.12);
            --accent-glow: rgba(53, 224, 140, 0.35);
            --cyan: #22d3ee;
            --error: #fb5a5a;
            --success: #35e08c;
            --glass-bg: rgba(255, 255, 255, 0.025);
            --glass-border: rgba(255, 255, 255, 0.09);
            --grid-line: rgba(148, 163, 184, 0.06);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
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
                radial-gradient(circle at 15% 12%, rgba(53, 224, 140, 0.09) 0%, transparent 45%),
                radial-gradient(circle at 88% 90%, rgba(34, 211, 238, 0.07) 0%, transparent 50%);
            background-size: 42px 42px, 42px 42px, 100% 100%, 100% 100%;
            color: var(--text-light);
            font-family: var(--font-display);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
            line-height: 1.6;
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: linear-gradient(180deg, transparent 0%, rgba(53, 224, 140, 0.05) 50%, transparent 100%);
            height: 140px;
            width: 100%;
            pointer-events: none;
            animation: sweep 7s linear infinite;
            z-index: 1;
        }

        @keyframes sweep {
            0% { transform: translateY(-160px); }
            100% { transform: translateY(100vh); }
        }

        /* Header */
        nav {
            background: rgba(7, 10, 18, 0.7);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            padding: 1.15rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--glass-border);
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 100;
        }

        nav a.brand {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--text-light);
            text-decoration: none;
            letter-spacing: -0.02em;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        nav a.brand .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--accent);
            box-shadow: 0 0 8px var(--accent-glow);
            animation: pulse 2s ease-in-out infinite;
            flex-shrink: 0;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.55; transform: scale(0.8); }
        }

        nav a.brand span.tag {
            color: var(--accent);
            font-family: var(--font-mono);
            font-weight: 600;
        }

        nav .nav-actions a {
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        nav .nav-actions a:hover {
            color: var(--accent);
        }

        nav .nav-actions a svg {
            width: 15px;
            height: 15px;
            stroke: currentColor;
        }

        /* Main viewport */
        .wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 6.5rem 1.25rem 2.5rem;
            position: relative;
            z-index: 2;
        }

        .frame {
            position: relative;
            width: 100%;
            max-width: 460px;
        }

        .frame::before,
        .frame::after,
        .frame .corner-tr,
        .frame .corner-br {
            content: '';
            position: absolute;
            width: 26px;
            height: 26px;
            border: 2px solid var(--accent);
            opacity: 0.7;
            filter: drop-shadow(0 0 4px var(--accent-glow));
        }

        .frame::before { top: -10px; left: -10px; border-right: none; border-bottom: none; }
        .frame::after { bottom: -10px; left: -10px; border-right: none; border-top: none; }
        .frame .corner-tr { top: -10px; right: -10px; border-left: none; border-bottom: none; }
        .frame .corner-br { bottom: -10px; right: -10px; border-left: none; border-top: none; }

        .register-card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border-radius: 14px;
            padding: 2.75rem 2.5rem 2.5rem;
            width: 100%;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
            animation: fadeInScale 0.55s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .scan-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-family: var(--font-mono);
            font-size: 0.68rem;
            letter-spacing: 0.14em;
            color: var(--accent);
            background: var(--accent-soft);
            border: 1px solid rgba(53, 224, 140, 0.25);
            padding: 0.3rem 0.65rem;
            border-radius: 100px;
            margin-bottom: 1.4rem;
            text-transform: uppercase;
        }

        .scan-tag::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--accent);
            animation: pulse 1.6s ease-in-out infinite;
        }

        .register-card h2 {
            font-size: clamp(1.6rem, 5vw, 2rem);
            font-weight: 700;
            letter-spacing: -0.03em;
            margin-bottom: 0.4rem;
        }

        .register-card .subtitle {
            color: var(--text-muted);
            font-size: 0.92rem;
            margin-bottom: 2rem;
            font-family: var(--font-mono);
        }

        /* Form */
        .form-group {
            margin-bottom: 1.35rem;
        }

        .form-group label {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            margin-bottom: 0.55rem;
            font-weight: 500;
            font-size: 0.72rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--text-muted);
            font-family: var(--font-mono);
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap svg {
            position: absolute;
            left: 0.95rem;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            stroke: var(--text-dim);
            transition: var(--transition);
            pointer-events: none;
        }

        .form-control {
            width: 100%;
            padding: 0.85rem 1rem 0.85rem 2.75rem;
            border: 1px solid var(--glass-border);
            border-radius: 8px;
            font-size: 0.95rem;
            font-family: var(--font-mono);
            color: var(--text-light);
            background-color: rgba(7, 10, 18, 0.65);
            transition: var(--transition);
        }

        .form-control::placeholder {
            color: var(--text-dim);
            font-family: var(--font-display);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-soft);
            background-color: rgba(7, 10, 18, 0.85);
        }

        .input-wrap:focus-within svg {
            stroke: var(--accent);
        }

        .toggle-pass {
            position: absolute;
            right: 0.85rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--text-dim);
            padding: 0.3rem;
            display: flex;
            align-items: center;
            transition: var(--transition);
        }

        .toggle-pass:hover {
            color: var(--accent);
        }

        .toggle-pass svg {
            width: 18px;
            height: 18px;
            stroke: currentColor;
        }

        .field-hint {
            font-size: 0.72rem;
            color: var(--text-dim);
            font-family: var(--font-mono);
            margin-top: 0.45rem;
        }

        /* Banners */
        .banner {
            padding: 0.8rem 1rem;
            border-radius: 8px;
            font-size: 0.85rem;
            font-family: var(--font-mono);
            font-weight: 500;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.55rem;
        }

        .banner svg {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
        }

        .banner.error {
            background: rgba(251, 90, 90, 0.08);
            border: 1px solid rgba(251, 90, 90, 0.25);
            color: var(--error);
            animation: shake 0.4s ease;
        }

        .banner.error svg {
            stroke: var(--error);
        }

        .banner.success {
            background: var(--accent-soft);
            border: 1px solid rgba(53, 224, 140, 0.25);
            color: var(--accent);
        }

        .banner.success svg {
            stroke: var(--accent);
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20% { transform: translateX(-6px); }
            40% { transform: translateX(6px); }
            60% { transform: translateX(-4px); }
            80% { transform: translateX(4px); }
        }

        /* CTA button */
        .btn {
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background: var(--accent);
            color: #05140c;
            border: none;
            padding: 0.9rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 700;
            font-size: 0.95rem;
            font-family: var(--font-display);
            letter-spacing: 0.01em;
            text-decoration: none;
            transition: var(--transition);
            box-shadow: 0 4px 18px var(--accent-glow);
            margin-top: 0.6rem;
        }

        .btn svg {
            width: 18px;
            height: 18px;
            stroke: #05140c;
            transition: var(--transition);
        }

        .btn:hover {
            background: #4bf29d;
            transform: translateY(-2px);
            box-shadow: 0 8px 26px var(--accent-glow);
        }

        .btn:hover svg {
            transform: translateX(3px);
        }

        .btn:active {
            transform: translateY(0);
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 1.75rem 0 1.5rem;
            color: var(--text-dim);
            font-family: var(--font-mono);
            font-size: 0.7rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--glass-border);
        }

        .footer-text {
            text-align: center;
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .footer-text a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
        }

        .footer-text a:hover {
            text-decoration: underline;
        }

        .footprint {
            text-align: center;
            margin-top: 1.75rem;
            font-family: var(--font-mono);
            font-size: 0.68rem;
            color: var(--text-dim);
            letter-spacing: 0.06em;
        }

        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: translateY(18px) scale(0.97);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Tablet */
        @media (max-width: 640px) {
            .wrapper {
                padding-top: 5.5rem;
            }
        }

        /* Mobile */
        @media (max-width: 480px) {
            nav {
                padding: 0.9rem 1.1rem;
            }
            nav a.brand {
                font-size: 1.1rem;
            }
            nav .nav-actions a {
                font-size: 0.8rem;
            }
            .wrapper {
                padding: 5rem 0.9rem 2rem;
            }
            .frame::before,
            .frame::after,
            .frame .corner-tr,
            .frame .corner-br {
                width: 18px;
                height: 18px;
            }
            .register-card {
                padding: 2rem 1.35rem 1.85rem;
                border-radius: 12px;
            }
            .register-card h2 {
                font-size: 1.5rem;
            }
            .register-card .subtitle {
                font-size: 0.82rem;
                margin-bottom: 1.6rem;
            }
            .form-control {
                padding: 0.78rem 1rem 0.78rem 2.6rem;
                font-size: 0.92rem;
            }
            .btn {
                padding: 0.82rem;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 360px) {
            .register-card {
                padding: 1.75rem 1.1rem 1.6rem;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            body::before,
            .scan-tag::before,
            nav a.brand .dot,
            .register-card,
            .banner.error {
                animation: none !important;
            }
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
        <a href="index.php" class="brand">
            <span class="dot"></span>
            ANPR<span class="tag">.portal</span>
        </a>
        <div class="nav-actions">
            <a href="login.php">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                Sign In Instead
            </a>
        </div>
    </nav>

    <div class="wrapper">
        <div class="frame">
            <span class="corner-tr"></span>
            <span class="corner-br"></span>
            <div class="register-card">
                <span class="scan-tag">New Operator Access</span>
                <h2>Create account</h2>
                <p class="subtitle">// Join the automated vehicle scanning system</p>

                <?php if(!empty($error)): ?>
                    <div class="banner error">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <?= $error ?>
                    </div>
                <?php endif; ?>

                <?php if(!empty($success)): ?>
                    <div class="banner success">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        <?= $success ?>
                    </div>
                <?php endif; ?>

                <form action="register.php" method="POST">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <div class="input-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <input type="text" id="username" name="username" class="form-control" placeholder="Choose a username" autocomplete="username" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <div class="input-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16v16H4z" opacity="0"/><path d="M22 6l-10 7L2 6"/><rect x="2" y="4" width="20" height="16" rx="2"/></svg>
                            <input type="email" id="email" name="email" class="form-control" placeholder="you@example.com" autocomplete="email" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            <input type="password" id="password" name="password" class="form-control" placeholder="Create a strong password" autocomplete="new-password" required>
                            <button type="button" class="toggle-pass" id="togglePass" aria-label="Show password">
                                <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                        <p class="field-hint">Use 8+ characters with a mix of letters and numbers</p>
                    </div>

                    <button type="submit" class="btn">
                        Register new operator
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </button>
                </form>

                <div class="divider">Secure Registration</div>
                <p class="footer-text">Already have an account? <a href="login.php">Login here</a></p>
            </div>
            <p class="footprint">ENCRYPTED SESSION &bull; ANPR-CORE v2.4</p>
        </div>
    </div>

    <script>
        const toggleBtn = document.getElementById('togglePass');
        const passInput = document.getElementById('password');
        toggleBtn.addEventListener('click', () => {
            const isPassword = passInput.type === 'password';
            passInput.type = isPassword ? 'text' : 'password';
            toggleBtn.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
            toggleBtn.style.color = isPassword ? 'var(--accent)' : '';
        });
    </script>

</body>
</html>