<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Clini-K — Crea tu cuenta de paciente de forma gratuita.">
    <title>Crear Cuenta — Clini-K</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= defined('BASE_PATH') ? BASE_PATH : '' ?>/assets/css/main.css">
    <style>
        body.registro-page {
            background: linear-gradient(135deg, #0f172a 0%, #1a2744 50%, #0f172a 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 2rem 1rem;
        }
        body.registro-page::before {
            content: '';
            position: fixed;
            width: 500px; height: 500px;
            background: rgba(139,92,246,0.10);
            top: -120px; right: -120px;
            border-radius: 50%;
            filter: blur(90px);
            pointer-events: none;
        }
        .registro-wrapper {
            width: 100%; max-width: 520px;
            position: relative; z-index: 1;
        }
        .brand {
            text-align: center;
            margin-bottom: 1.75rem;
        }
        .brand-icon { font-size: 2.5rem; display: block; margin-bottom: .3rem; }
        .brand-name { font-size: 1.8rem; font-weight: 700; color: #f1f5f9; letter-spacing: -1px; }
        .brand-name span { color: #06b6d4; }
        .brand-tagline { font-size: .85rem; color: #64748b; margin-top: .2rem; }

        .reg-card {
            background: rgba(255,255,255,.04);
            border: 1px solid rgba(255,255,255,.10);
            border-radius: 24px;
            padding: 2.25rem 2.5rem;
            backdrop-filter: blur(20px);
            box-shadow: 0 25px 50px rgba(0,0,0,.4);
        }
        .reg-title {
            font-size: 1.2rem; font-weight: 700; color: #f1f5f9;
            margin-bottom: .3rem;
        }
        .reg-subtitle { font-size: .85rem; color: #64748b; margin-bottom: 1.75rem; }

        .alert-box {
            border-radius: 12px; padding: .875rem 1rem;
            font-size: .875rem; margin-bottom: 1.25rem;
            display: flex; align-items: flex-start; gap: .6rem;
        }
        .alert-error   { background:rgba(239,68,68,.12);  border:1px solid rgba(239,68,68,.3);  color:#fca5a5; }
        .alert-success { background:rgba(16,185,129,.12); border:1px solid rgba(16,185,129,.3); color:#6ee7b7; }

        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .form-group { margin-bottom: 1.1rem; }
        .form-label {
            display: block; font-size: .78rem; font-weight: 600;
            color: #94a3b8; margin-bottom: .45rem;
            text-transform: uppercase; letter-spacing: .05em;
        }
        .form-label .req { color: #f87171; }
        .form-input {
            width: 100%;
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.10);
            border-radius: 12px;
            padding: .8rem 1rem;
            color: #f1f5f9; font-size: .92rem;
            font-family: 'Inter', sans-serif;
            transition: border-color .25s, box-shadow .25s, background .25s;
            outline: none;
        }
        .form-input::placeholder { color: #475569; }
        .form-input:focus {
            border-color: rgba(139,92,246,.6);
            background: rgba(139,92,246,.05);
            box-shadow: 0 0 0 3px rgba(139,92,246,.15);
        }
        .form-hint { font-size: .72rem; color: #64748b; margin-top: .3rem; }

        .btn-registro {
            width: 100%; padding: .95rem;
            background: linear-gradient(135deg, #8b5cf6 0%, #06b6d4 100%);
            border: none; border-radius: 12px; color: #fff;
            font-size: 1rem; font-weight: 700;
            font-family: 'Inter', sans-serif;
            cursor: pointer; margin-top: .5rem;
            box-shadow: 0 4px 20px rgba(139,92,246,.3);
            transition: opacity .25s, transform .2s;
            letter-spacing: .02em;
        }
        .btn-registro:hover { opacity: .9; transform: translateY(-2px); }
        .btn-registro:active { transform: translateY(0); }

        .reg-footer {
            text-align: center; margin-top: 1.25rem;
            font-size: .82rem; color: #64748b;
        }
        .reg-footer a { color: #06b6d4; font-weight: 600; }

        @media (max-width: 500px) {
            .form-row { grid-template-columns: 1fr; }
            .reg-card { padding: 1.75rem 1.25rem; }
        }
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

        body.registro-page {
            background: linear-gradient(135deg, #0f172a 0%, #1a2744 50%, #0f172a 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 2rem 1rem; /* <- ESTO PUEDE AFECTAR EL CENTRADO */
        }
    </style>
</head>
<body class="registro-page">

<?php
$bp = defined('BASE_PATH') ? BASE_PATH : '';
$old = $old ?? [];
?>

<div class="registro-wrapper">

    <div class="brand">
        <span class="brand-icon">⚕️</span>
        <div class="brand-name">Clini<span>-K</span></div>
        <p class="brand-tagline">Crea tu cuenta de paciente</p>
    </div>

    <div class="reg-card">
        <h1 class="reg-title">Regístrate gratis</h1>
        <p class="reg-subtitle">Completa el formulario para acceder al sistema de citas.</p>

        <?php if (!empty($error)): ?>
            <div class="alert-box alert-error" role="alert" id="registro-error">
                <span>⚠️</span>
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert-box alert-success" role="alert" id="registro-success">
                <span>✅</span>
                <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
                <br><a href="<?= $bp ?>/login" style="color:#6ee7b7;font-weight:600;margin-top:.5rem;display:inline-block;">
                    Ir al inicio de sesión →
                </a>
            </div>
        <?php endif; ?>

        <?php if (empty($success)): ?>
        <form method="POST" action="<?= $bp ?>/registro/procesar" id="form-registro" novalidate>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="reg-nombre">Nombre <span class="req">*</span></label>
                    <input type="text" id="reg-nombre" name="nombre" class="form-input"
                           placeholder="Juan" required autocomplete="given-name"
                           value="<?= htmlspecialchars($old['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label" for="reg-apellido">Apellido <span class="req">*</span></label>
                    <input type="text" id="reg-apellido" name="apellido" class="form-input"
                           placeholder="López" required autocomplete="family-name"
                           value="<?= htmlspecialchars($old['apellido'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="reg-nid">Cédula / Identificación <span class="req">*</span></label>
                <input type="text" id="reg-nid" name="numero_identificacion" class="form-input"
                       placeholder="00000001-1" required autocomplete="off"
                       value="<?= htmlspecialchars($old['nid'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <p class="form-hint">Esta será tu credencial de acceso al sistema.</p>
            </div>

            <div class="form-group">
                <label class="form-label" for="reg-correo">Correo electrónico <span class="req">*</span></label>
                <input type="email" id="reg-correo" name="correo" class="form-input"
                       placeholder="tucorreo@ejemplo.com" required autocomplete="email"
                       value="<?= htmlspecialchars($old['correo'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="form-group">
                <label class="form-label" for="reg-telefono">Teléfono</label>
                <input type="tel" id="reg-telefono" name="telefono" class="form-input"
                       placeholder="+504 9999-9999" autocomplete="tel"
                       value="<?= htmlspecialchars($old['telefono'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="reg-pass">Contraseña <span class="req">*</span></label>
                    <input type="password" id="reg-pass" name="contrasena" class="form-input"
                           placeholder="Mínimo 8 caracteres" required minlength="8"
                           autocomplete="new-password">
                </div>
                <div class="form-group">
                    <label class="form-label" for="reg-pass2">Confirmar <span class="req">*</span></label>
                    <input type="password" id="reg-pass2" name="contrasena_confirm" class="form-input"
                           placeholder="Repite la contraseña" required
                           autocomplete="new-password">
                </div>
            </div>

            <button type="submit" id="btn-registrarse" class="btn-registro">
                Crear cuenta →
            </button>

        </form>
        <?php endif; ?>

        <div class="reg-footer">
            ¿Ya tienes cuenta?
            <a href="<?= $bp ?>/login" id="link-volver-login">Iniciar sesión</a>
        </div>
    </div>

</div>

<script>
document.getElementById('form-registro')?.addEventListener('submit', function(e) {
    const pass  = document.getElementById('reg-pass').value;
    const pass2 = document.getElementById('reg-pass2').value;
    if (pass.length < 8) {
        e.preventDefault();
        alert('La contraseña debe tener al menos 8 caracteres.');
        document.getElementById('reg-pass').focus();
        return;
    }
    if (pass !== pass2) {
        e.preventDefault();
        alert('Las contraseñas no coinciden.');
        document.getElementById('reg-pass2').focus();
        return;
    }
    const btn = document.getElementById('btn-registrarse');
    btn.textContent = 'Creando cuenta...';
    btn.disabled = true;
});
</script>

</body>
</html>
