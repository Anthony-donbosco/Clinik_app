# 🏥 CLINI-K — Contexto Maestro (State of the Project)

> Documento de referencia rápida. Pegar al inicio de una nueva sesión para retomar sin fricción.

---

## 1. Stack Tecnológico

| Capa | Tecnología |
|---|---|
| Backend | PHP 8.0 nativo OOP, PSR-4, Composer |
| Base de datos | MySQL vía XAMPP (`clinik_bd`) |
| Frontend | HTML + CSS Vanilla + JavaScript Vanilla |
| Notificaciones | Polling AJAX cada 10s (sin WebSockets) |
| Correo | PHPMailer (instalado, pendiente de conectar) |
| Control de versiones | Git + GitHub |

---

## 2. Arquitectura

```
Clinik_app/
├── public/
│   ├── index.php          ← Front Controller único (toda petición pasa aquí)
│   ├── .htaccess          ← Enruta todo a index.php (RewriteBase /clinik_app/public/)
│   └── assets/ (css, js)
├── src/
│   ├── Config/Database.php         ← Singleton PDO → clinik_bd
│   ├── Controllers/
│   │   ├── Web/   (AuthController, SecretariaController, DoctorController,
│   │   │           PacienteController, CitaController, BaseController)
│   │   └── Api/   (CitasApiController, NotificacionesApiController)
│   ├── Repositories/
│   │   ├── AuthRepository.php       ← :cred1 / :cred2 (bug PDO resuelto)
│   │   ├── DashboardRepository.php
│   │   └── CitaRepository.php
│   └── Services/
│       └── CitaService.php
├── views/
│   ├── auth/login.php
│   ├── layout/ (head.php, foot.php, sidebar.php)
│   ├── citas/index.php
│   └── errors/ (404.php, 500.php, 403.php)
├── database/
│   ├── migrations/create_auth_tables.sql
│   └── seeders/seed_usuarios.php
└── vendor/  (Composer — 22 clases registradas)
```

**Patrón:** Front Controller → Router manual en `index.php` → Controlador → Servicio → Repositorio → PDO.

---

## 3. Base de Datos (`clinik_bd`)

### Tablas existentes (del dump original)
`Doctor`, `Paciente`, `Cita`, `Estado`, `historial` *(vacía, para Fase 4)*, `Factura`, `HorarioDoctor`, `HorarioDisponible`

### Tablas añadidas en Fase 2
```sql
CREATE TABLE roles (id_rol INT PK, nombre_rol VARCHAR(50));
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PK,
    numero_identificacion VARCHAR(20) UNIQUE,
    nombre / apellido / correo,
    contrasena_hash VARCHAR(255),   -- bcrypt cost 12
    id_rol INT FK → roles,
    id_referencia INT,              -- id_doctor o id_paciente según rol
    id_estado INT DEFAULT 8 FK → Estado  -- 8=Activo, 9=Inactivo
);
```

### Catálogo Estado relevante
| id | nombre |
|---|---|
| 1 | Pendiente |
| 2 | Aprobada |
| 4 | Atendida |
| 5 | Cancelada |
| 8 | Activo (usuarios/doctor/paciente) |
| 9 | Inactivo (soft delete) |

### Credenciales de prueba
| Rol | Credencial | Contraseña |
|---|---|---|
| Secretaria | `SEC-001` | `clinik2024` |
| Doctor | `DOC-001` … `DOC-005` | `clinik2024` |
| Paciente | `00000001-1` … `00000005-5` | `clinik2024` |

---

## 4. RBAC (Control de Acceso por Rol)

- `id_rol 1` → Paciente → redirige a `/dashboard/paciente`
- `id_rol 2` → Doctor → redirige a `/dashboard/doctor`
- `id_rol 3` → Secretaria → redirige a `/dashboard/secretaria`
- `BaseController::requireAuth()` protege todas las rutas privadas
- `session_regenerate_id(true)` en login para prevenir Session Fixation

---

## 5. Routing y BASE_PATH

**Problema resuelto:** La app corre en subdirectorio `/clinik_app` en XAMPP.

**Solución implementada en `public/index.php`:**
```php
$scriptName = $_SERVER['SCRIPT_NAME'];       // /clinik_app/public/index.php
$publicPos  = strrpos($scriptName, '/public/');
$basePath   = ($publicPos !== false) ? substr($scriptName, 0, $publicPos) : '';
define('BASE_PATH', $basePath);              // = '/clinik_app'
```
- Todos los `header('Location: ...')` usan `BASE_PATH . '/ruta'`
- Sidebar, formularios y AJAX usan `BASE_PATH` o `$bp = defined('BASE_PATH') ? BASE_PATH : ''`
- El `notificaciones.js` detecta el path dinámicamente desde `window.location`

**URLs de acceso:**
```
http://localhost/clinik_app/           ← entra por .htaccess raíz
http://localhost/clinik_app/public/    ← acceso directo al Front Controller
```

---

## 6. htaccess Configuration (Apache 2.4 XAMPP)

**`.htaccess` raíz (`Clinik_app/`):**
```apache
Options -Indexes
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /clinik_app/
    RewriteCond %{REQUEST_URI} !^/clinik_app/public/
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

**`public/.htaccess`:**
```apache
<IfModule mod_rewrite.c>
    Options -Indexes
    RewriteEngine On
    RewriteBase /clinik_app/public/
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^ index.php [QSA,L]
</IfModule>
```

> [!IMPORTANT]
> Todos los archivos `.htaccess` y PHP deben ser **UTF-8 sin BOM**. Usar `[System.Text.UTF8Encoding]::new($false)` al escribir con PowerShell para evitar el error `Namespace declaration has to be the very first statement`.

---

## 7. Estado de Fases

### ✅ Fase 1 — Estructura y Autenticación
- Arquitectura de carpetas PSR-4 completa
- Front Controller + Router manual
- Sistema de login con bcrypt (cost 12)
- RBAC por sesión con `id_rol` e `id_referencia`
- Páginas de error 403, 404, 500 con branding Clini-K
- Seeder idempotente (`seed_usuarios.php`)

### ✅ Fase 2 — Dashboards por Rol
- `SecretariaController`: 4 tarjetas de stats (pacientes, doctores, citas hoy, pendientes) + tabla agenda del día + listas de pacientes y doctores
- `DoctorController`: stats de agenda propia + próximos 7 días
- `PacienteController`: resumen de citas propias + historial
- `DashboardRepository`: todas las queries separadas por rol
- Sidebar dinámico según `id_rol` de sesión
- Polling de notificaciones cada 10s (`notificaciones.js`)

### ✅ Fase 3 — Módulo de Citas
- `CitaService`: 16 bloques de 30 min (08:00–15:30), validaciones estrictas
- Validaciones: fecha pasada, fin de semana, < 2h de anticipación, conflicto de horario
- **Doble capa de seguridad**: checks PHP + UNIQUE constraint MySQL
- `CitaRepository`: soft delete (id_estado=5), disponibilidad, UNIQUE handling
- `CitasApiController`: endpoints JSON `/api/citas/disponibilidad`, `/api/citas/reservar`, `/api/citas/cancelar`
- Vista AJAX: horarios aparecen dinámicamente al seleccionar doctor + fecha
- Cancelación AJAX: elimina la fila visualmente sin reload
- RBAC en citas: Doctor no puede crear, solo ver su agenda

### 🔲 Fase 4 — Historial Médico (PENDIENTE)

**Objetivo:** Permitir que el Doctor registre diagnósticos al atender una cita.

**Tareas pendientes:**

| Tarea | Detalle |
|---|---|
| `HistorialController` (Web) | Vista para que el Doctor llene diagnóstico, tratamiento, notas |
| `HistorialService` | Validación: solo citas con id_estado=2 (Aprobada) pueden atenderse. Al guardar, cambia estado a 4 (Atendida) |
| `HistorialRepository` | `INSERT INTO historial...` + `UPDATE cita SET id_estado=4` |
| Vista Doctor en Citas | Botón "Atender" visible solo para el Doctor en citas Aprobadas |
| Vista Paciente | Mostrar diagnósticos recibidos en su dashboard/historial |
| Ruta nueva | `GET /historial` → `HistorialController::index()` |
| Ruta nueva | `POST /historial/guardar` → `HistorialController::guardar()` ← ya registrada en el router |
| Bloqueo de edición | No permitir modificar un historial después de 24h (regla de negocio) |
| Notificaciones reales | Conectar `NotificacionesApiController` a tabla real en BD (ahora devuelve stub) |

---

## 8. Reglas de Oro del Proyecto

1. **Validación doble:** Primero lógica PHP, luego constraints MySQL (UNIQUE, FK)
2. **Sin rutas absolutas:** Siempre `BASE_PATH . '/ruta'` en redirects y `$bp` en vistas
3. **Sin BOM en PHP:** Escribir archivos con `UTF8Encoding($false)` vía PowerShell o editor con UTF-8 sin BOM
4. **Autoloader:** Después de crear cualquier clase nueva → `composer dump-autoload -o`
5. **Dos repositorios:** Editar siempre en `C:\xampp\htdocs\Clinik_app\` (el activo). El Desktop es copia de respaldo
6. **Soft deletes:** Nunca `DELETE` real; cambiar `id_estado` a 5 (Cancelada) o 9 (Inactivo)
7. **Seguridad:** `htmlspecialchars()` en todo output, prepared statements, no exponer stack traces

---

## 9. Comandos Clave

```powershell
# Regenerar autoloader (OBLIGATORIO al crear nueva clase)
C:\xampp\php\php.exe composer.phar dump-autoload -o --working-dir="C:\xampp\htdocs\Clinik_app"

# Ejecutar seeder
C:\xampp\php\php.exe C:\xampp\htdocs\Clinik_app\database\seeders\seed_usuarios.php

# Verificar sintaxis PHP
C:\xampp\php\php.exe -l "C:\xampp\htdocs\Clinik_app\src\...\Archivo.php"

# Ver errores Apache en tiempo real
Get-Content "C:\xampp\apache\logs\error.log" -Tail 15
```
