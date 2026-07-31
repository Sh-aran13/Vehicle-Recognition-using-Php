<?php
// upload.php
require_once 'database/db.php';
start_secure_session();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ANPR Operations - Scan Interface</title>
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
            color: var(--danger) !important;
        }

        nav .nav-links a.logout-btn:hover {
            color: #fca5a5 !important;
            background: rgba(251, 90, 90, 0.08);
        }

        /* Main Workspace */
        .container {
            width: 100%;
            max-width: 800px;
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
            padding: 2.25rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            margin-bottom: 1.75rem;
            animation: fadeInScale 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .scan-tag {
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
            margin-bottom: 1.1rem;
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

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(0.8); }
        }

        .card h2 {
            font-size: clamp(1.35rem, 4vw, 1.65rem);
            font-weight: 700;
            letter-spacing: -0.02em;
            margin-bottom: 0.4rem;
        }

        .card .subtitle {
            color: var(--text-muted);
            margin-bottom: 1.85rem;
            font-size: 0.9rem;
            font-family: var(--font-mono);
        }

        /* Mode Selector */
        .mode-selector {
            display: flex;
            background: rgba(7, 10, 18, 0.65);
            border: 1px solid var(--glass-border);
            padding: 0.3rem;
            border-radius: 10px;
            gap: 0.25rem;
            margin-bottom: 1.75rem;
        }

        .mode-btn {
            flex: 1;
            padding: 0.7rem 0.5rem;
            border: none;
            background: transparent;
            color: var(--text-muted);
            border-radius: 7px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.85rem;
            font-family: var(--font-display);
            transition: var(--transition);
        }

        .mode-btn.active {
            background: var(--accent);
            color: #05140c;
            box-shadow: 0 4px 14px var(--accent-glow);
        }

        /* Drag & Drop */
        .drop-zone {
            border: 2px dashed rgba(255, 255, 255, 0.14);
            border-radius: 14px;
            padding: 3.5rem 1.5rem;
            text-align: center;
            cursor: pointer;
            background: rgba(7, 10, 18, 0.4);
            transition: var(--transition);
        }

        .drop-zone:hover, .drop-zone--over {
            border-color: var(--accent);
            background: var(--accent-soft);
            box-shadow: 0 0 24px rgba(53, 224, 140, 0.08);
        }

        .drop-zone__icon {
            width: 42px;
            height: 42px;
            margin: 0 auto 1rem;
            border-radius: 10px;
            background: var(--accent-soft);
            border: 1px solid rgba(53, 224, 140, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .drop-zone__icon svg {
            width: 20px;
            height: 20px;
            stroke: var(--accent);
        }

        .drop-zone__prompt {
            font-weight: 500;
            font-size: 0.92rem;
            color: var(--text-muted);
            display: block;
        }

        .drop-zone__hint {
            font-size: 0.75rem;
            color: var(--text-dim);
            font-family: var(--font-mono);
            margin-top: 0.5rem;
            display: block;
        }

        .drop-zone input {
            display: none;
        }

        /* Camera */
        .camera-container {
            display: none;
            text-align: center;
            background: #000;
            border-radius: 14px;
            overflow: hidden;
            position: relative;
            max-width: 100%;
            border: 1px solid var(--glass-border);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        video {
            width: 100%;
            max-height: 420px;
            display: block;
            background: #05070c;
        }

        .capture-overlay {
            position: absolute;
            bottom: 1.25rem;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
            width: calc(100% - 2.5rem);
            display: flex;
            justify-content: center;
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
            padding: 0.75rem 1.6rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 700;
            font-size: 0.9rem;
            font-family: var(--font-display);
            text-decoration: none;
            transition: var(--transition);
            box-shadow: 0 4px 16px var(--accent-glow);
            white-space: nowrap;
        }

        .btn svg {
            width: 17px;
            height: 17px;
            stroke: #05140c;
        }

        .btn:hover {
            background: #4bf29d;
            transform: translateY(-2px);
            box-shadow: 0 8px 22px var(--accent-glow);
        }

        /* Results */
        .card-head {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            margin-bottom: 1.1rem;
        }

        .card-head h3 {
            font-size: 1rem;
            font-weight: 700;
            letter-spacing: -0.01em;
        }

        .pulse-live {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--accent);
            box-shadow: 0 0 8px var(--accent-glow);
            animation: pulse 1.6s ease-in-out infinite;
            flex-shrink: 0;
        }

        hr.sep {
            margin: 0 0 1.4rem;
            border: 0;
            border-top: 1px solid var(--glass-border);
        }

        .result-layout {
            display: flex;
            gap: 2.25rem;
            flex-wrap: wrap;
        }

        .result-data-pane {
            flex: 1;
            min-width: 220px;
        }

        .result-preview-pane {
            flex: 1;
            min-width: 220px;
        }

        .pane-label {
            color: var(--text-muted);
            font-size: 0.7rem;
            font-weight: 600;
            font-family: var(--font-mono);
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .plate-output-display {
            background: var(--accent-soft);
            border: 1px solid rgba(53, 224, 140, 0.25);
            color: var(--accent);
            font-size: clamp(1.8rem, 6vw, 2.5rem);
            font-weight: 700;
            padding: 0.7rem 1.25rem;
            border-radius: 10px;
            letter-spacing: 0.05em;
            display: inline-block;
            margin-top: 0.6rem;
            font-family: var(--font-mono);
            word-break: break-word;
            max-width: 100%;
        }

        .matrix-img-wrap img {
            max-width: 100%;
            border-radius: 8px;
            margin-top: 0.7rem;
            border: 1px solid var(--glass-border);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
            display: block;
        }

        /* Mobile */
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

            .container {
                margin: 1.5rem auto;
            }

            .card {
                padding: 1.5rem 1.35rem;
                border-radius: 14px;
            }

            .drop-zone {
                padding: 2.5rem 1rem;
            }

            .result-layout {
                flex-direction: column;
                gap: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .mode-btn {
                font-size: 0.78rem;
                padding: 0.65rem 0.35rem;
            }

            .plate-output-display {
                font-size: 1.6rem;
                padding: 0.6rem 1rem;
            }

            video {
                max-height: 300px;
            }

            .capture-overlay {
                bottom: 0.85rem;
            }

            .btn {
                padding: 0.7rem 1.2rem;
                font-size: 0.85rem;
            }
        }

        @keyframes fadeInScale {
            from { opacity: 0; transform: translateY(12px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        @media (prefers-reduced-motion: reduce) {
            .card, .scan-tag::before, .pulse-live { animation: none !important; }
        }

        a:focus-visible,
        button:focus-visible,
        .drop-zone:focus-visible {
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
            <a href="dashboard.php">Dashboard</a>
            <a href="upload.php" class="active">Scan Engine</a>
            <a href="history.php">Operations Log</a>
            <a href="logout.php" class="logout-btn" data-confirm-title="Disconnect session" data-confirm="Are you sure you want to disconnect from the scan engine?" data-confirm-text="Disconnect" data-confirm-intent="danger">Disconnect</a>
        </div>
    </nav>

    <div class="container">
        <div class="card">
            <span class="scan-tag">Recognition Engine Ready</span>
            <h2>Intelligent ANPR Engine Scan</h2>
            <p class="subtitle">// Choose your capture pipeline mode to begin target recognition processing</p>

            <div class="mode-selector">
                <button type="button" class="mode-btn active" id="btnFileMode">Drag &amp; Drop Upload</button>
                <button type="button" class="mode-btn" id="btnCameraMode">Live Video Stream</button>
            </div>

            <div class="drop-zone" id="dropZone" tabindex="0">
                <div class="drop-zone__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                </div>
                <span class="drop-zone__prompt">Drag vehicle image here or click to choose file</span>
                <span class="drop-zone__hint">JPG, PNG &bull; up to 10MB</span>
                <input type="file" name="vehicle_image" id="fileInput" accept="image/*">
            </div>

            <div class="camera-container" id="cameraContainer">
                <video id="videoFeed" autoplay playsinline></video>
                <div class="capture-overlay">
                    <button type="button" class="btn" id="btnCapture">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                        Capture and Analyze
                    </button>
                </div>
            </div>

            <canvas id="captureCanvas" style="display: none;"></canvas>
        </div>

        <div class="card" id="resultCard" style="display: none;">
            <div class="card-head">
                <span class="pulse-live"></span>
                <h3>Processing Engine Output</h3>
            </div>
            <hr class="sep">
            <div class="result-layout">
                <div class="result-data-pane">
                    <p class="pane-label">Detected Number Plate Context</p>
                    <div id="plateOutput" class="plate-output-display">- - -</div>
                </div>
                <div class="result-preview-pane" id="imagePreviews"></div>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
    <script>
        const btnFileMode = document.getElementById('btnFileMode');
        const btnCameraMode = document.getElementById('btnCameraMode');
        const dropZone = document.getElementById('dropZone');
        const cameraContainer = document.getElementById('cameraContainer');
        const fileInput = document.getElementById('fileInput');
        const videoFeed = document.getElementById('videoFeed');
        const btnCapture = document.getElementById('btnCapture');
        const captureCanvas = document.getElementById('captureCanvas');

        const resultCard = document.getElementById('resultCard');
        const plateOutput = document.getElementById('plateOutput');
        const imagePreviews = document.getElementById('imagePreviews');

        let streamInstance = null;

        // Toggle Mechanics to Change Input Modes
        btnFileMode.addEventListener('click', () => {
            btnFileMode.classList.add('active');
            btnCameraMode.classList.remove('active');
            dropZone.style.display = 'block';
            cameraContainer.style.display = 'none';
            stopCamera();
        });

        btnCameraMode.addEventListener('click', () => {
            btnCameraMode.classList.add('active');
            btnFileMode.classList.remove('active');
            dropZone.style.display = 'none';
            cameraContainer.style.display = 'block';
            startCamera();
        });

        // 🎥 Native Hardware Stream Integration
        function startCamera() {
            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" }, audio: false })
                .then(stream => {
                    streamInstance = stream;
                    videoFeed.srcObject = stream;
                })
                .catch(err => {
                    Toast.show("Camera initialization blocked or unsupported.", "error");
                    console.error(err);
                });
            } else {
                Toast.show("Webcam hardware execution layers not supported.", "error");
            }
        }

        function stopCamera() {
            if (streamInstance) {
                streamInstance.getTracks().forEach(track => track.stop());
                streamInstance = null;
            }
        }

        // 📷 Frame Grab Manipulation
        btnCapture.addEventListener('click', () => {
            if (!streamInstance) return;

            const context = captureCanvas.getContext('2d');
            captureCanvas.width = videoFeed.videoWidth;
            captureCanvas.height = videoFeed.videoHeight;
            context.drawImage(videoFeed, 0, 0, captureCanvas.width, captureCanvas.height);

            captureCanvas.toBlob((blob) => {
                if (blob) {
                    const fileObject = new File([blob], "camera_capture.jpg", { type: "image/jpeg" });
                    sendPayloadToBackend(fileObject);
                }
            }, 'image/jpeg', 0.95);
        });

        // 📁 Standard Drop-zone Listeners
        dropZone.addEventListener('click', () => fileInput.click());
        dropZone.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); fileInput.click(); }
        });
        fileInput.addEventListener('change', () => {
            if (fileInput.files.length) sendPayloadToBackend(fileInput.files[0]);
        });
        dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.classList.add('drop-zone--over'); });
        ['dragleave', 'dragend'].forEach(type => dropZone.addEventListener(type, () => dropZone.classList.remove('drop-zone--over')));
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('drop-zone--over');
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                sendPayloadToBackend(e.dataTransfer.files[0]);
            }
        });

        // 🔄 Common AJAX Gateway Integration
        function sendPayloadToBackend(file) {
            const formData = new FormData();
            formData.append('vehicle_image', file);

            Toast.show('Uploading matrix payload downstream...', 'success');
            plateOutput.innerText = "Processing...";
            resultCard.style.display = 'block';

            fetch('api/detect.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    Toast.show('Computer Vision Verification Complete!');
                    plateOutput.innerText = data.plate_number;
                    imagePreviews.innerHTML = `
                        <p class="pane-label">Extracted Matrix Segment Region</p>
                        <div class="matrix-img-wrap"><img src="${data.plate_path}"></div>
                    `;
                } else {
                    Toast.show(data.error || 'Failed localized plate evaluation.', 'error');
                    plateOutput.innerText = "RE-CAPTURE";
                    imagePreviews.innerHTML = '';
                }
            })
            .catch(err => {
                Toast.show('Server API Route Engine Error.', 'error');
                console.error(err);
            });
        }
    </script>
</body>
</html>