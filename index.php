<?php
// index.php
require_once 'database/db.php';
start_secure_session();
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Plate Detection System</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap');

        :root {
            --bg-dark: #070a12;
            --text-light: #eef2f7;
            --text-muted: #64748b;
            --text-dim: #3f4a5e;
            --accent: #35e08c;
            --accent-soft: rgba(53, 224, 140, 0.1);
            --accent-glow: rgba(53, 224, 140, 0.32);
            --cyan: #22d3ee;
            --glass-bg: rgba(255, 255, 255, 0.025);
            --glass-border: rgba(255, 255, 255, 0.09);
            --grid-line: rgba(148, 163, 184, 0.05);
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
            scroll-behavior: smooth;
        }

        body {
            background-color: var(--bg-dark);
            background-image:
                linear-gradient(var(--grid-line) 1px, transparent 1px),
                linear-gradient(90deg, var(--grid-line) 1px, transparent 1px),
                radial-gradient(circle at 85% 10%, rgba(53, 224, 140, 0.1) 0%, transparent 45%),
                radial-gradient(circle at 5% 85%, rgba(34, 211, 238, 0.08) 0%, transparent 50%);
            background-size: 42px 42px, 42px 42px, 100% 100%, 100% 100%;
            color: var(--text-light);
            font-family: var(--font-display);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
            line-height: 1.6;
        }

        /* Header */
        nav {
            background: rgba(7, 10, 18, 0.72);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            padding: 1.15rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--glass-border);
            position: sticky;
            top: 0;
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
            50% { opacity: 0.5; transform: scale(0.8); }
        }

        nav a.brand span {
            color: var(--accent);
            font-family: var(--font-mono);
            font-weight: 600;
        }

        nav .nav-actions {
            display: flex;
            align-items: center;
            gap: 1.35rem;
        }

        nav .login-link {
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.92rem;
            transition: var(--transition);
        }

        nav .login-link:hover {
            color: var(--text-light);
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background: var(--accent);
            color: #05140c;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 700;
            font-size: 0.92rem;
            text-decoration: none;
            transition: var(--transition);
            box-shadow: 0 4px 14px var(--accent-glow);
            white-space: nowrap;
        }

        .btn:hover {
            background: #4bf29d;
            transform: translateY(-2px);
            box-shadow: 0 8px 22px var(--accent-glow);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn-ghost {
            background: transparent;
            border: 1px solid var(--glass-border);
            color: var(--text-light);
            box-shadow: none;
        }

        .btn-ghost:hover {
            background: rgba(255, 255, 255, 0.04);
            border-color: rgba(255, 255, 255, 0.18);
            box-shadow: none;
        }

        .btn svg {
            width: 17px;
            height: 17px;
            stroke: currentColor;
        }

        /* Hero */
        .hero-wrap {
            flex: 1;
            display: flex;
            align-items: center;
            padding: 3.5rem 1.5rem 2.5rem;
            max-width: 1180px;
            margin: 0 auto;
            width: 100%;
        }

        .hero-grid {
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            gap: 3rem;
            align-items: center;
            width: 100%;
        }

        .hero-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            font-family: var(--font-mono);
            font-size: 0.68rem;
            letter-spacing: 0.14em;
            color: var(--accent);
            background: var(--accent-soft);
            border: 1px solid rgba(53, 224, 140, 0.25);
            padding: 0.32rem 0.7rem;
            border-radius: 100px;
            margin-bottom: 1.4rem;
            text-transform: uppercase;
        }

        .hero-tag::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--accent);
            animation: pulse 1.6s ease-in-out infinite;
        }

        .hero-content h1 {
            font-size: clamp(2.1rem, 5.4vw, 3.35rem);
            font-weight: 700;
            line-height: 1.12;
            letter-spacing: -0.03em;
            margin-bottom: 1.25rem;
        }

        .hero-content h1 .accent-word {
            color: var(--accent);
        }

        .hero-content p {
            font-size: clamp(0.95rem, 2vw, 1.08rem);
            color: var(--text-muted);
            margin-bottom: 2rem;
            max-width: 520px;
        }

        .hero-actions {
            display: flex;
            gap: 0.85rem;
            flex-wrap: wrap;
            margin-bottom: 2.25rem;
        }

        .hero-actions .btn {
            padding: 0.9rem 1.7rem;
            font-size: 0.95rem;
        }

        .hero-metrics {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .hero-metrics div {
            display: flex;
            flex-direction: column;
            gap: 0.15rem;
        }

        .hero-metrics .num {
            font-family: var(--font-mono);
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-light);
        }

        .hero-metrics .label {
            font-size: 0.72rem;
            color: var(--text-dim);
            text-transform: uppercase;
            letter-spacing: 0.07em;
            font-family: var(--font-mono);
        }

        /* Hero visual: scan mockup */
        .hero-visual {
            position: relative;
        }

        .scan-panel {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-radius: 18px;
            padding: 1.5rem;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.45);
            animation: fadeInScale 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .scan-panel__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
            font-family: var(--font-mono);
            font-size: 0.68rem;
            color: var(--text-dim);
            letter-spacing: 0.05em;
        }

        .scan-panel__head .live {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            color: var(--accent);
        }

        .scan-panel__head .live::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--accent);
            box-shadow: 0 0 8px var(--accent-glow);
            animation: pulse 1.6s ease-in-out infinite;
        }

        .scan-frame {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            background: linear-gradient(155deg, #101826 0%, #0a0f18 100%);
            border: 1px solid var(--glass-border);
            aspect-ratio: 4 / 3;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .scan-frame svg {
            width: 100%;
            height: 100%;
        }

        .scan-panel__readout {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 1rem;
            padding: 0.85rem 1rem;
            background: rgba(7, 10, 18, 0.55);
            border: 1px solid var(--glass-border);
            border-radius: 10px;
        }

        .scan-panel__readout .plate-tag {
            background: var(--accent-soft);
            color: var(--accent);
            padding: 0.35rem 0.75rem;
            border-radius: 6px;
            font-weight: 700;
            font-size: 0.9rem;
            letter-spacing: 0.06em;
            border: 1px solid rgba(53, 224, 140, 0.25);
            font-family: var(--font-mono);
        }

        .scan-panel__readout .confidence {
            font-family: var(--font-mono);
            font-size: 0.72rem;
            color: var(--text-dim);
            text-align: right;
        }

        .scan-panel__readout .confidence b {
            color: var(--accent);
            display: block;
            font-size: 0.95rem;
        }

        /* Features */
        .features-wrap {
            max-width: 1180px;
            margin: 0 auto;
            padding: 1rem 1.5rem 4.5rem;
            width: 100%;
        }

        .features-head {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .features-head h2 {
            font-size: clamp(1.5rem, 3.5vw, 2rem);
            font-weight: 700;
            letter-spacing: -0.02em;
            margin-bottom: 0.6rem;
        }

        .features-head p {
            color: var(--text-muted);
            font-size: 0.95rem;
            max-width: 480px;
            margin: 0 auto;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.25rem;
        }

        .feature-card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-radius: 14px;
            padding: 1.75rem;
            transition: var(--transition);
        }

        .feature-card:hover {
            transform: translateY(-3px);
            border-color: rgba(53, 224, 140, 0.25);
        }

        .feature-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            background: var(--accent-soft);
            border: 1px solid rgba(53, 224, 140, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.1rem;
        }

        .feature-icon svg {
            width: 20px;
            height: 20px;
            stroke: var(--accent);
        }

        .feature-card h3 {
            font-size: 1.02rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            letter-spacing: -0.01em;
        }

        .feature-card p {
            color: var(--text-muted);
            font-size: 0.88rem;
            line-height: 1.6;
        }

        footer {
            border-top: 1px solid var(--glass-border);
            padding: 1.5rem;
            text-align: center;
            font-family: var(--font-mono);
            font-size: 0.72rem;
            color: var(--text-dim);
            letter-spacing: 0.04em;
        }

        @keyframes fadeInScale {
            from { opacity: 0; transform: translateY(20px) scale(0.97); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        @keyframes sweepY {
            0% { transform: translateY(-10%); }
            100% { transform: translateY(110%); }
        }

        /* Tablet */
        @media (max-width: 940px) {
            .hero-grid {
                grid-template-columns: 1fr;
            }

            .hero-visual {
                max-width: 460px;
                margin: 0 auto;
                width: 100%;
            }

            .hero-content {
                text-align: center;
            }

            .hero-content p {
                margin-left: auto;
                margin-right: auto;
            }

            .hero-actions, .hero-metrics {
                justify-content: center;
            }
        }

        /* Mobile */
        @media (max-width: 640px) {
            nav {
                padding: 0.9rem 1.1rem;
            }

            nav a.brand {
                font-size: 1.1rem;
            }

            nav .nav-actions {
                gap: 0.7rem;
            }

            nav .nav-actions .login-link {
                font-size: 0.82rem;
            }

            nav .nav-actions .btn {
                padding: 0.55rem 1rem;
                font-size: 0.82rem;
            }

            .hero-wrap {
                padding: 2.25rem 1.1rem 2rem;
            }

            .hero-tag {
                font-size: 0.62rem;
            }

            .hero-actions {
                flex-direction: column;
                width: 100%;
            }

            .hero-actions .btn {
                width: 100%;
            }

            .hero-metrics {
                gap: 1.5rem;
                justify-content: space-between;
                width: 100%;
            }

            .features-wrap {
                padding: 0.5rem 1.1rem 3rem;
            }

            .feature-card {
                padding: 1.5rem;
            }
        }

        @media (max-width: 380px) {
            .hero-metrics {
                gap: 1rem;
            }

            .hero-metrics .num {
                font-size: 1.25rem;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
            .hero-tag::before,
            .scan-panel__head .live::before,
            nav a.brand .dot,
            .scan-panel,
            #scanLine {
                animation: none !important;
            }
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
        <a href="index.php" class="brand">
            <span class="dot"></span>
            ANPR<span>Portal</span>
        </a>
        <div class="nav-actions">
            <a href="login.php" class="login-link">Sign In</a>
            <a href="register.php" class="btn">Register</a>
        </div>
    </nav>

    <div class="hero-wrap">
        <div class="hero-grid">
            <div class="hero-content">
                <span class="hero-tag">Computer Vision &bull; Live</span>
                <h1>Automatic vehicle <span class="accent-word">number plate</span> recognition, done right</h1>
                <p>Secure, optimized, and instant vehicle intelligence powered by scalable PHP processing layers and advanced computer vision — built for real operations teams.</p>

                <div class="hero-actions">
                    <a href="login.php" class="btn">
                        Get started
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </a>
                    <a href="register.php" class="btn btn-ghost">Create an account</a>
                </div>

                <div class="hero-metrics">
                    <div>
                        <span class="num">&lt;300ms</span>
                        <span class="label">Detection latency</span>
                    </div>
                    <div>
                        <span class="num">99.2%</span>
                        <span class="label">Plate accuracy</span>
                    </div>
                    <div>
                        <span class="num">24/7</span>
                        <span class="label">Pipeline uptime</span>
                    </div>
                </div>
            </div>

            <div class="hero-visual">
                <div class="scan-panel">
                    <div class="scan-panel__head">
                        <span>CAM_04 &bull; NORTH-GATE</span>
                        <span class="live">LIVE</span>
                    </div>
                    <div class="scan-frame">
                        <svg viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg">
                            <defs>
                                <linearGradient id="carGrad" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#1b2536"/>
                                    <stop offset="100%" stop-color="#111823"/>
                                </linearGradient>
                                <clipPath id="frameClip"><rect x="0" y="0" width="400" height="300" rx="12"/></clipPath>
                            </defs>
                            <g clip-path="url(#frameClip)">
                                <rect width="400" height="300" fill="#0c111c"/>
                                <line x1="0" y1="60" x2="400" y2="60" stroke="#1b2434" stroke-width="1"/>
                                <line x1="0" y1="120" x2="400" y2="120" stroke="#1b2434" stroke-width="1"/>
                                <line x1="0" y1="180" x2="400" y2="180" stroke="#1b2434" stroke-width="1"/>
                                <line x1="0" y1="240" x2="400" y2="240" stroke="#1b2434" stroke-width="1"/>
                                <!-- simple car silhouette -->
                                <rect x="95" y="140" width="210" height="70" rx="14" fill="url(#carGrad)" stroke="#2a3a52" stroke-width="1.5"/>
                                <rect x="130" y="112" width="140" height="45" rx="12" fill="url(#carGrad)" stroke="#2a3a52" stroke-width="1.5"/>
                                <circle cx="140" cy="212" r="16" fill="#0c111c" stroke="#2a3a52" stroke-width="2"/>
                                <circle cx="260" cy="212" r="16" fill="#0c111c" stroke="#2a3a52" stroke-width="2"/>
                                <rect x="168" y="184" width="64" height="18" rx="3" fill="#0c111c" stroke="#35e08c" stroke-width="1.5"/>
                                <!-- detection reticle -->
                                <g stroke="#35e08c" stroke-width="2" fill="none">
                                    <path d="M158 172 L158 158 L174 158"/>
                                    <path d="M242 172 L242 158 L226 158"/>
                                    <path d="M158 210 L158 224 L174 224"/>
                                    <path d="M242 210 L242 224 L226 224"/>
                                </g>
                                <rect x="0" y="0" width="400" height="46" fill="#35e08c" opacity="0.06" id="scanLine">
                                    <animate attributeName="y" values="0;260;0" dur="3.2s" repeatCount="indefinite"/>
                                </rect>
                            </g>
                        </svg>
                    </div>
                    <div class="scan-panel__readout">
                        <span class="plate-tag">AP 39 CX 4471</span>
                        <span class="confidence"><b>98.6%</b>match confidence</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="features-wrap">
        <div class="features-head">
            <h2>Built for real-time recognition pipelines</h2>
            <p>Everything you need to capture, process, and audit vehicle traffic — end to end.</p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                </div>
                <h3>Real-time detection</h3>
                <p>Upload an image or stream live video and get plate extractions back in under a second, powered by a tuned computer vision engine.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </div>
                <h3>Secure by default</h3>
                <p>Sessions, prepared statements, and hashed credentials keep every scan and every account locked down from the ground up.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12h18M3 6h18M3 18h18"/></svg>
                </div>
                <h3>Full operations log</h3>
                <p>Search, filter, and review every processed vehicle with source images and cropped plate matrices archived automatically.</p>
            </div>
        </div>
    </div>

    <footer>
        ANPR PORTAL &bull; SECURE VEHICLE INTELLIGENCE ENGINE
    </footer>

</body>
</html>