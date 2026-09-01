<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mandeh.ID - Gerbang Identitas Terfederasi</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-gradient: linear-gradient(135deg, #090d16 0%, #0f172a 50%, #1e1b4b 100%);
            --glass-bg: rgba(255, 255, 255, 0.02);
            --glass-border: rgba(255, 255, 255, 0.08);
            --glass-highlight: rgba(255, 255, 255, 0.15);
            --primary: #8b5cf6;
            --secondary: #ec4899;
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background: var(--bg-gradient);
            min-height: 100vh;
            color: var(--text-primary);
            overflow-x: hidden;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* Ambient lights */
        body::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(139, 92, 246, 0.15) 0%, transparent 70%);
            top: -10%;
            right: -10%;
            z-index: 0;
            pointer-events: none;
        }

        body::after {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(236, 72, 153, 0.1) 0%, transparent 70%);
            bottom: -10%;
            left: -10%;
            z-index: 0;
            pointer-events: none;
        }

        header {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 10;
            position: relative;
        }

        .logo {
            font-size: 24px;
            font-weight: 800;
            background: linear-gradient(to right, #a78bfa, #ec4899);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.5px;
        }

        .nav-links a {
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.2s ease;
            margin-left: 24px;
        }

        .nav-links a:hover {
            color: var(--text-primary);
        }

        .hero-section {
            max-width: 1200px;
            margin: 60px auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            gap: 60px;
            align-items: center;
            z-index: 10;
            position: relative;
            flex-grow: 1;
        }

        @media (max-width: 968px) {
            .hero-section {
                grid-template-columns: 1fr;
                margin: 40px auto;
                text-align: center;
                gap: 40px;
            }
        }

        .hero-content {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        @media (max-width: 968px) {
            .hero-content {
                align-items: center;
            }
        }

        .badge {
            background: rgba(139, 92, 246, 0.1);
            border: 1px solid rgba(139, 92, 246, 0.2);
            color: #a78bfa;
            padding: 6px 16px;
            border-radius: 100px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 24px;
            display: inline-block;
        }

        .hero-title {
            font-size: 56px;
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -2px;
            margin-bottom: 24px;
            background: linear-gradient(135deg, #ffffff 0%, #cbd5e1 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        @media (max-width: 640px) {
            .hero-title {
                font-size: 40px;
            }
        }

        .hero-description {
            font-size: 18px;
            color: var(--text-secondary);
            line-height: 1.6;
            margin-bottom: 36px;
        }

        .ecosystem-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
            width: 100%;
        }

        .ecosystem-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 24px;
            transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
            text-align: left;
        }

        .ecosystem-card:hover {
            transform: translateY(-2px);
            border-color: rgba(255, 255, 255, 0.15);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .ecosystem-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .ecosystem-name {
            font-size: 16px;
            font-weight: 700;
            color: var(--text-primary);
        }

        .status-dot {
            width: 8px;
            height: 8px;
            background: #10b981;
            border-radius: 50%;
            box-shadow: 0 0 8px #10b981;
        }

        .ecosystem-desc {
            font-size: 13px;
            color: var(--text-secondary);
            margin-bottom: 16px;
            line-height: 1.4;
        }

        .ecosystem-link {
            font-size: 13px;
            font-weight: 600;
            color: #a78bfa;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: color 0.2s;
        }

        .ecosystem-link:hover {
            color: var(--text-primary);
        }

        .features-list {
            margin-top: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .feature-tag {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--glass-border);
            padding: 8px 16px;
            border-radius: 12px;
            font-size: 13px;
            color: var(--text-secondary);
            font-weight: 500;
        }

        footer {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
            border-top: 1px solid var(--glass-border);
            text-align: center;
            font-size: 13px;
            color: var(--text-secondary);
            z-index: 10;
            position: relative;
        }
    </style>
</head>
<body>

<header>
    <div class="logo">Mandeh.ID</div>
    <div class="nav-links">
        <a href="/.well-known/openid-configuration">OIDC Config</a>
        <a href="/oauth/jwks">JWKS Keys</a>
    </div>
</header>

<main class="hero-section">
    <div class="hero-content">
        <div class="badge">Ecosystem SSO Provider</div>
        <h1 class="hero-title">Kedaulatan Data Komunitas Terfederasi</h1>
        <p class="hero-description">
            Mandeh.ID adalah gerbang identitas tunggal yang aman, ringan, dan mandiri. Dirancang khusus untuk mengintegrasikan seluruh layanan dalam ekosistem Mandeh tanpa pihak ketiga, menjamin kepemilikan data penuh di tangan komunitas.
        </p>
        
        <div class="features-list">
            <span class="feature-tag">✦ Stateless JWT Token</span>
            <span class="feature-tag">✦ RS256 Asymmetric Cryptography</span>
            <span class="feature-tag">✦ OIDC Discovery Compliant</span>
            <span class="feature-tag">✦ Local-first cPanel Ready</span>
        </div>
    </div>

    <div class="ecosystem-container">
        <h3 style="margin-bottom: 20px; font-weight: 700; letter-spacing: -0.5px;">Ecosystem Apps</h3>
        <div class="ecosystem-grid">
            <div class="ecosystem-card">
                <div class="ecosystem-header">
                    <span class="ecosystem-name">Locomo Learning Platform</span>
                    <span class="status-dot"></span>
                </div>
                <p class="ecosystem-desc">Platform pembelajaran interaktif dan Learning Management System (LMS) ekosistem.</p>
                <a href="http://locomo-web.localhost" class="ecosystem-link">Buka Layanan &rarr;</a>
            </div>

            <div class="ecosystem-card">
                <div class="ecosystem-header">
                    <span class="ecosystem-name">Mandeh Online Portal</span>
                    <span class="status-dot"></span>
                </div>
                <p class="ecosystem-desc">Gerbang pariwisata, reputasi lokal, dan direktori toko komunitas terpadu.</p>
                <a href="http://mandeh.online.localhost" class="ecosystem-link">Buka Layanan &rarr;</a>
            </div>
        </div>
    </div>
</main>

<footer>
    &copy; 2026 Mandeh Identity Ecosystem. Kedaulatan Data Komunitas Lokal.
</footer>

</body>
</html>
