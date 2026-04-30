<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso denegado — Clini-K</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #f1f5f9;
        }
        .error-card {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 20px;
            padding: 3rem 4rem;
            text-align: center;
            max-width: 520px;
            backdrop-filter: blur(12px);
        }
        .error-code {
            font-size: 6rem;
            font-weight: 700;
            background: linear-gradient(135deg, #ef4444, #f97316);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1;
        }
        .error-title { font-size: 1.5rem; font-weight: 600; margin: 1rem 0 0.5rem; }
        .error-desc { color: #94a3b8; font-size: 0.95rem; line-height: 1.6; }
        .back-btn {
            display: inline-block;
            margin-top: 2rem;
            padding: 0.75rem 2rem;
            background: linear-gradient(135deg, #ef4444, #f97316);
            color: #fff;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: opacity 0.2s;
        }
        .back-btn:hover { opacity: 0.85; }
        .logo { font-size: 1.3rem; font-weight: 700; color: #06b6d4; margin-bottom: 2rem; letter-spacing: -0.5px; }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="logo">⚕ Clini-K</div>
        <div class="error-code">403</div>
        <h1 class="error-title">Acceso denegado</h1>
        <p class="error-desc">
            No tienes los permisos necesarios para acceder a esta sección.
            Si crees que esto es un error, contacta al administrador del sistema.
        </p>
        <a href="/clinik_app" class="back-btn">Volver al inicio</a>
    </div>
</body>
</html>
