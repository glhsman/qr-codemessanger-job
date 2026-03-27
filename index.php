<?php
require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portrait | Standort-Administrator</title>
    <!-- Favicon logic -->
    <!-- Favicon logic -->
    <?php
    foreach (["ico","png","jpg","svg"] as $ext) {
        if (file_exists(__DIR__ . "/admin/favicon/favicon.$ext")) {
            $mtime = filemtime(__DIR__ . "/admin/favicon/favicon.$ext");
            $type = ($ext === 'ico') ? 'x-icon' : $ext;
            echo '<link rel="icon" type="image/' . $type . '" href="admin/favicon/favicon.' . $ext . '?v=' . $mtime . '">';
            break;
        }
    }
    ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #667eea;
            --secondary: #764ba2;
            --text-main: #1f2937;
            --text-muted: #6b7280;
            --bg-body: #f3f4f6;
            --bg-card: #ffffff;
            --accent: #3b82f6;
        }
        
        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 1.5rem;
            line-height: 1.6;
        }
        
        .container {
            background: var(--bg-card);
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1);
            max-width: 600px;
            width: 100%;
            transition: transform 0.3s ease;
        }
        
        .container:hover {
            transform: translateY(-5px);
        }
        
        h1 {
            font-size: 1.8rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 1rem;
        }
        
        p.intro {
            font-size: 1.1rem;
            color: var(--text-muted);
            margin-bottom: 2rem;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 140px 1fr;
            gap: 12px;
            margin-top: 1.5rem;
        }
        
        .label {
            font-weight: 600;
            color: var(--text-muted);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .value {
            color: var(--text-main);
            font-weight: 500;
        }
        
        .tech-stack {
            margin-top: 2rem;
            padding: 1.5rem;
            background: #f9fafb;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
        }
        
        .tech-stack strong {
            display: block;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            color: var(--text-muted);
            text-transform: uppercase;
        }
        
        .tag-container {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        
        .tag {
            background: #fff;
            color: var(--primary);
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            transition: all 0.2s;
        }
        
        .tag:hover {
            border-color: var(--primary);
            background: #f5f7ff;
        }
        
        .quote {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e5e7eb;
            font-style: italic;
            font-size: 0.95rem;
            color: var(--text-muted);
            text-align: center;
        }
        
        .admin-link {
            display: block;
            text-align: center;
            margin-top: 2rem;
            font-size: 0.85rem;
            color: var(--text-muted);
            text-decoration: none;
            transition: color 0.2s;
        }
        
        .admin-link:hover {
            color: var(--primary);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Profil</h1>
        <p class="intro">IT-Professional mit Fokus auf Standort-Administration und Softwareentwicklung.</p>
        
        <div class="info-grid">
            <div class="label">Geburtsdatum</div>
            <div class="value">17. August 1964</div>
            
            <div class="label">Position</div>
            <div class="value">Standort-Administrator</div>
            
            <div class="label">Unternehmen</div>
            <div class="value">Drinkport KG</div>
            
            <div class="label">Fokus</div>
            <div class="value">Präzision, Struktur, Objektivität</div>
        </div>
        
        <div class="tech-stack">
            <strong>Technische Schwerpunkte</strong>
            <div class="tag-container">
                <span class="tag">twinBasic</span>
                <span class="tag">System-Administration</span>
                <span class="tag">Strukturierte Logik</span>
                <span class="tag">COM-Programmierung</span>
            </div>
        </div>
        
        <div class="quote">
            "Effizienz durch klare Strukturen und ehrliche Analyse."
        </div>
        
        <a href="admin/" class="admin-link">Zum Admin-Bereich</a>
    </div>
</body>
</html>
