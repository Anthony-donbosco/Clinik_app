<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Clini-K el Sistema de gestion clinica médica. Inicia sesion para continuar.">
    <title>Iniciar Sesión en Clini-K</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/main.css">
    <style>
        body.login-page {
            background: linear-gradient(135deg, #0f172a 0%, #1a2744 50%, #0f172a 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            overflow: hidden;
        }

        /* Animated background orbs */
        body.login-page::before,
        body.login-page::after {
            content: '';
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            animation: float 8s ease-in-out infinite;
            pointer-events: none;
        }
        body.login-page::before {
            width: 500px; height: 500px;
            background: rgba(6, 182, 212, 0.12);
            top: -100px; left: -100px;
        }
        body.login-page::after {
            width: 400px; height: 400px;
            background: rgba(59, 130, 246, 0.10);
            bottom: -80px; right: -80px;
            animation-delay: -4s;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0) scale(1); }
            50%       { transform: translateY(-30px) scale(1.05); }
        }

        .login-wrapper {
            width: 100%;
            max-width: 440px;
            padding: 1rem;
            position: relative;
            z-index: 1;
        }

        /* Logo / Brand */
        .brand {
            text-align: center;
            margin-bottom: 2rem;
        }
        .brand-icon {
            font-size: 2.8rem;
            display: block;
            margin-bottom: 0.4rem;
            filter: drop-shadow(0 0 20px rgba(6,182,212,0.5));
            animation: pulse-icon 3s ease-in-out infinite;
        }
        @keyframes pulse-icon {
            0%, 100% { filter: drop-shadow(0 0 20px rgba(6,182,212,0.5)); }
            50%       { filter: drop-shadow(0 0 40px rgba(6,182,212,0.9)); }
        }
        .brand-name {
            font-size: 2rem;
            font-weight: 700;
            color: #f1f5f9;
            letter-spacing: -1px;
        }
        .brand-name span { color: #06b6d4; }
        .brand-tagline {
            font-size: 0.875rem;
            color: #64748b;
            margin-top: 0.25rem;
            font-weight: 300;
        }

        /* Card */
        .login-card {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.10);
            border-radius: 24px;
            padding: 2.5rem;
            backdrop-filter: blur(20px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.4);
        }
        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #f1f5f9;
            margin-bottom: 0.375rem;
        }
        .card-subtitle {
            font-size: 0.875rem;
            color: #64748b;
            margin-bottom: 2rem;
        }

        /* Alert de error */
        .alert-error {
            background: rgba(239, 68, 68, 0.12);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 12px;
            padding: 0.875rem 1rem;
            color: #fca5a5;
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        /* Form */
        .form-group { margin-bottom: 1.25rem; }
        .form-label {
            display: block;
            font-size: 0.8rem;
            font-weight: 500;
            color: #94a3b8;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .form-input {
            width: 100%;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.10);
            border-radius: 12px;
            padding: 0.875rem 1rem;
            color: #f1f5f9;
            font-size: 0.95rem;
            font-family: 'Inter', sans-serif;
            transition: border-color 0.25s, box-shadow 0.25s, background 0.25s;
            outline: none;
        }
        .form-input::placeholder { color: #475569; }
        .form-input:focus {
            border-color: rgba(6, 182, 212, 0.6);
            background: rgba(6, 182, 212, 0.05);
            box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.15);
        }

        /* Password toggle */
        .input-group { position: relative; }
        .toggle-password {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #475569;
            cursor: pointer;
            font-size: 1.1rem;
            padding: 0;
            transition: color 0.2s;
        }
        .toggle-password:hover { color: #06b6d4; }

        /* Submit button */
        .btn-login {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #06b6d4 0%, #3b82f6 100%);
            border: none;
            border-radius: 12px;
            color: #fff;
            font-size: 1rem;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: opacity 0.25s, transform 0.2s, box-shadow 0.25s;
            margin-top: 0.5rem;
            box-shadow: 0 4px 20px rgba(6, 182, 212, 0.3);
            letter-spacing: 0.02em;
        }
        .btn-login:hover {
            opacity: 0.92;
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(6, 182, 212, 0.45);
        }
        .btn-login:active { transform: translateY(0); }

        /* Footer del card */
        .card-footer {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.8rem;
            color: #475569;
        }
        .card-footer strong { color: #64748b; }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        /* Aseguramos que el html y body ocupen el 100% correctamente */
        html, body {
            width: 100%;
            height: 100%;
        }
    </style>
</head>
<body class="login-page">

<main class="login-wrapper" aria-label="Formulario de acceso al sistema Clini-K">

    <div class="brand">
        <span class="brand-icon"></span>
        <div class="brand-name">Clini<span>-K</span></div>
        <p class="brand-tagline">Sistema de Gestión de Clínica Médica</p>
    </div>

    <div class="login-card">
        <h1 class="card-title">Bienvenido de vuelta</h1>
        <p class="card-subtitle">Ingresa tus credenciales para continuar</p>

        <?php if (!empty($error)): ?>
            <div class="alert-error" role="alert" id="login-error-msg">
                <span>⚠️</span>
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form id="login-form" method="POST" action="<?= defined('BASE_PATH') ? BASE_PATH : '' ?>/login" novalidate>

            <div class="form-group">
                <label for="credencial" class="form-label">Cédula o Correo electrónico</label>
                <input
                    type="text"
                    id="credencial"
                    name="credencial"
                    class="form-input"
                    placeholder="123456789 o correo@ejemplo.com"
                    autocomplete="username"
                    required
                >
            </div>

            <div class="form-group">
            <label for="contrasena" class="form-label">Contraseña</label>
            <div class="input-group">
                <input
                    type="password"
                    id="contrasena"
                    name="contrasena"
                    class="form-input"
                    placeholder="Contraseña"
                    autocomplete="current-password"
                    required
                >
                <button
                    type="button"
                    class="toggle-password"
                    id="toggle-pass-btn"
                    aria-label="Mostrar u ocultar contraseña"
                    title="Mostrar contraseña"
                >
                    <!-- Icono de Ojo (Mostrar) -->
                    <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    <!-- Icono de Ojo Tachado -->
                    <svg id="eye-off-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                        <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"></path>
                        <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"></path>
                        <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"></path>
                        <line x1="2" y1="2" x2="22" y2="22"></line>
                    </svg>
                </button>
            </div>
        </div>

            <button type="submit" id="login-submit-btn" class="btn-login">
                Iniciar Sesión
            </button>

        </form>

        <div class="card-footer">
            <strong>Clini-K</strong> &copy; <?= date('Y') ?> &middot; Sistema médico seguro
        </div>
        <div class="card-footer" style="margin-top:.75rem;border-top:1px solid rgba(255,255,255,.06);padding-top:.75rem;">
            ¿Eres paciente nuevo?
            <a href="<?= defined('BASE_PATH') ? BASE_PATH : '' ?>/registro"
               id="link-registro"
               style="color:#06b6d4;font-weight:600;margin-left:.25rem;">
               Créate una cuenta →
            </a>
        </div>
    </div>

</main>

<script>
    document.getElementById('toggle-pass-btn').addEventListener('click', function () {
        const input  = document.getElementById('contrasena');
        const eyeOn  = document.getElementById('eye-icon');
        const eyeOff = document.getElementById('eye-off-icon');
        const isHidden = input.type === 'password';
        input.type           = isHidden ? 'text' : 'password';
        eyeOn.style.display  = isHidden ? 'none' : '';
        eyeOff.style.display = isHidden ? '' : 'none';
        this.title = isHidden ? 'Ocultar contraseña' : 'Mostrar contraseña';
    });

    document.getElementById('login-form').addEventListener('submit', function () {
        const btn = document.getElementById('login-submit-btn');
        btn.textContent = 'Verificando...';
        btn.disabled = true;
    });
</script>

</body>
</html>

