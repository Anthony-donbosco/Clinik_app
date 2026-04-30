# 📋 Clini-K — Documentación Técnica del Sistema

> Estado actual: **Fases 1–4 completadas + módulo Admin + Auto-registro**

---

## 1. ¿Qué es Clini-K?

Sistema de gestión de clínica médica desarrollado en **PHP 8 OOP puro** (sin frameworks), arquitectura **MVC** con un Front Controller centralizado. Permite a pacientes agendar citas, a doctores registrar diagnósticos y a una secretaria/admin gestionar todo el sistema.

---

## 2. Estructura del Proyecto

```
Clinik_app/
│
├── public/                         ← Único punto de acceso web
│   ├── index.php                   ← FRONT CONTROLLER (enrutador)
│   └── assets/
│       ├── css/
│       │   └── main.css            ← CSS global del sistema (design system)
│       ├── js/
│       │   └── notificaciones.js   ← Polling AJAX cada 10s
│       └── img/                    ← Logos corporativos
│
├── src/                            ← Backend PHP (lógica de negocio)
│   ├── Config/
│   │   └── Database.php            ← Singleton PDO a MySQL
│   │
│   ├── Controllers/
│   │   ├── Web/
│   │   │   ├── BaseController.php  ← requireAuth(), requireRole(), render()
│   │   │   ├── AuthController.php  ← Login / Logout
│   │   │   ├── RegistroController.php  ← Auto-registro de pacientes
│   │   │   ├── PacienteController.php  ← Dashboard del paciente
│   │   │   ├── DoctorController.php    ← Dashboard del doctor
│   │   │   ├── SecretariaController.php← Dashboard de secretaria
│   │   │   ├── AdminController.php     ← Panel de administración
│   │   │   ├── CitaController.php      ← Módulo de citas (todos los roles)
│   │   │   └── HistorialController.php ← Módulo de historial médico
│   │   └── Api/
│   │       ├── NotificacionesApiController.php  ← GET /api/notificaciones
│   │       └── CitasApiController.php           ← Disponibilidad horaria
│   │
│   ├── Services/
│   │   ├── CitaService.php         ← Validaciones de negocio de citas
│   │   └── HistorialService.php    ← Bloqueo de 24h, validaciones historial
│   │
│   └── Repositories/
│       ├── AuthRepository.php      ← Login por cédula o correo
│       ├── CitaRepository.php      ← CRUD citas + JOINs
│       ├── DashboardRepository.php ← Stats por rol (conteos, listados)
│       ├── HistorialRepository.php ← CRUD HistorialMedico
│       └── UsuarioRepository.php   ← Registro pacientes, creación staff
│
├── views/                          ← Interfaces HTML (separadas por rol)
│   ├── layout/
│   │   ├── head.php                ← <head>, CSS, apertura del layout
│   │   ├── sidebar.php             ← Menú lateral dinámico por rol
│   │   └── foot.php                ← Cierre layout + carga de JS
│   ├── auth/
│   │   ├── login.php               ← Página de inicio de sesión
│   │   └── registro.php            ← Auto-registro de pacientes
│   ├── paciente/
│   │   └── dashboard.php           ← Dashboard del paciente
│   ├── doctor/
│   │   └── dashboard.php           ← Dashboard + agenda del doctor
│   ├── secretaria/
│   │   └── dashboard.php           ← Dashboard global de secretaria
│   ├── admin/
│   │   ├── dashboard.php           ← Lista de usuarios del sistema
│   │   └── crear_usuario.php       ← Formulario crear Doctor/Secretaria/Admin
│   ├── citas/
│   │   └── index.php               ← Vista de citas (comportamiento por rol)
│   ├── historial/
│   │   ├── atender_cita.php        ← Formulario diagnóstico (solo Doctor)
│   │   ├── doctor_historial.php    ← Lista historiales del doctor
│   │   └── paciente_historial.php  ← Timeline de historial del paciente
│   └── errors/
│       ├── 403.php / 404.php / 500.php
│
└── database/
    └── seeders/
        ├── seed_usuarios.php        ← Secretaria, Doctores, Pacientes de prueba
        └── seed_admin.php           ← Usuario Admin (id_rol=4)
```

---

## 3. Mapa de Rutas Completo

### 🔓 Rutas Públicas (sin sesión)

| Método | Ruta | Controlador → Método | Descripción |
|--------|------|---------------------|-------------|
| `GET` | `/` | AuthController → showLogin | Redirige al login |
| `GET` | `/login` | AuthController → showLogin | Formulario de login |
| `POST` | `/login` | AuthController → processLogin | Procesa credenciales |
| `GET` | `/logout` | AuthController → logout | Destruye sesión |
| `GET` | `/registro` | RegistroController → showForm | Formulario registro paciente |
| `POST` | `/registro/procesar` | RegistroController → procesar | Crea paciente + usuario |

### 🔐 Rutas Protegidas — Todos los roles

| Método | Ruta | Controlador → Método | Rol mínimo |
|--------|------|---------------------|-----------|
| `GET` | `/citas` | CitaController → index | Todos |
| `POST` | `/citas/crear` | CitaController → crear | Paciente, Secretaria |
| `GET` | `/api/notificaciones` | NotificacionesApiController → index | Todos |

### 🩺 Rutas — Doctor (id_rol = 2)

| Método | Ruta | Controlador → Método | Descripción |
|--------|------|---------------------|-------------|
| `GET` | `/dashboard/doctor` | DoctorController → dashboard | Panel principal |
| `GET` | `/historial` | HistorialController → index | Lista de consultas registradas |
| `GET` | `/historial/atender?cita=ID` | HistorialController → atender | Formulario diagnóstico |
| `POST` | `/historial/guardar` | HistorialController → guardar | Guarda y cierra la cita |

### 🧑‍⚕️ Rutas — Paciente (id_rol = 1)

| Método | Ruta | Controlador → Método | Descripción |
|--------|------|---------------------|-------------|
| `GET` | `/dashboard/paciente` | PacienteController → dashboard | Panel principal |
| `GET` | `/historial` | HistorialController → index | Timeline de su historial |
| `GET` | `/citas` | CitaController → index | Sus citas |
| `POST` | `/citas/crear` | CitaController → crear | Agendar nueva cita |

### 🗂️ Rutas — Secretaria (id_rol = 3)

| Método | Ruta | Controlador → Método | Descripción |
|--------|------|---------------------|-------------|
| `GET` | `/dashboard/secretaria` | SecretariaController → dashboard | Panel global |
| `GET` | `/citas` | CitaController → index | Todas las citas del sistema |
| `POST` | `/citas/crear` | CitaController → crear | Crear cita para cualquier paciente |

### ⚙️ Rutas — Admin (id_rol = 4)

| Método | Ruta | Controlador → Método | Descripción |
|--------|------|---------------------|-------------|
| `GET` | `/admin` | AdminController → index | Lista todos los usuarios |
| `GET` | `/admin/crear-usuario` | AdminController → showCrear | Formulario crear usuario |
| `POST` | `/admin/crear-usuario` | AdminController → procesarCrear | Guarda nuevo usuario |
| `POST` | `/admin/desactivar` | AdminController → desactivar | Soft delete usuario |
| `POST` | `/admin/reactivar` | AdminController → reactivar | Reactiva usuario |

---

## 4. Perfiles de Usuario — Flujo Visual

### 🔑 Flujo de Autenticación (Todos)

```
                    ┌─────────────────────┐
                    │   /login  (público)  │
                    │  Cédula / Correo     │
                    │  Contraseña          │
                    └─────────┬───────────┘
                              │  POST /login
                              ▼
                    ┌─────────────────────┐
                    │  Verifica en BD     │
                    │  bcrypt verify      │
                    │  id_estado = 8      │
                    └─────────┬───────────┘
              ┌───────────────┼────────────────────┐──────────────┐
              ▼               ▼                    ▼              ▼
         rol = 1          rol = 2             rol = 3        rol = 4
     /dashboard/       /dashboard/        /dashboard/        /admin
      paciente           doctor           secretaria
```

---

### 👤 Paciente — Lo que puede hacer

```
 ┌──────────────────────────────────────────────────────┐
 │  PACIENTE  (id_rol = 1)                               │
 ├──────────────────────────────────────────────────────┤
 │                                                       │
 │  [Dashboard]  /dashboard/paciente                     │
 │   • Ve stats: total citas, atendidas, pendientes      │
 │   • Lista de sus citas recientes                      │
 │                                                       │
 │  [Mis Citas]  /citas                                  │
 │   • Ve todas sus citas con estado (badge de color)    │
 │   • Puede agendar nueva cita → POST /citas/crear      │
 │     · Elige doctor del listado                        │
 │     · Elige fecha y hora                              │
 │                                                       │
 │  [Mi Historial]  /historial                           │
 │   • Timeline visual de todas sus consultas pasadas    │
 │   • Muestra: fecha, doctor, diagnóstico,              │
 │     tratamiento y notas de cada visita                │
 │                                                       │
 │  [🔔 Notificaciones]  polling cada 10s               │
 │   • Citas próximas en 2 días                         │
 │   • Consultas recién completadas                      │
 │                                                       │
 │  También puede:                                       │
 │   • Registrarse él mismo en /registro                 │
 └──────────────────────────────────────────────────────┘
```

---

### 🩺 Doctor — Lo que puede hacer

```
 ┌──────────────────────────────────────────────────────┐
 │  DOCTOR  (id_rol = 2)                                 │
 ├──────────────────────────────────────────────────────┤
 │                                                       │
 │  [Dashboard]  /dashboard/doctor                       │
 │   • Stats: citas hoy, pendientes, atendidas,          │
 │     próximas 7 días                                   │
 │   • Agenda de los próximos 7 días                     │
 │   • Botón "✅ Atender" en citas activas               │
 │                                                       │
 │  [Mi Agenda]  /citas                                  │
 │   • Solo ve sus propias citas                         │
 │   • Filtrado por su id_doctor en BD                   │
 │                                                       │
 │  [Atender cita]  /historial/atender?cita=ID           │
 │   • Formulario con:                                   │
 │     · Diagnóstico (mínimo 10 caracteres)              │
 │     · Tratamiento (mínimo 5 caracteres)               │
 │     · Notas adicionales (opcional)                    │
 │   • Al guardar → POST /historial/guardar              │
 │     · Crea registro en HistorialMedico                │
 │     · Cambia cita a estado "Atendida" (id=4)          │
 │     · Bloqueo de edición: 24h tras registro           │
 │                                                       │
 │  [Historiales]  /historial                            │
 │   • Lista de todas las consultas que él registró      │
 │   • Modal de detalle para ver diagnóstico completo    │
 │                                                       │
 │  [🔔 Notificaciones]  polling cada 10s               │
 │   • Citas de HOY pendientes de atender               │
 │   • Alerta si quedan < 30 min para la cita            │
 └──────────────────────────────────────────────────────┘
```

---

### 🗂️ Secretaria — Lo que puede hacer

```
 ┌──────────────────────────────────────────────────────┐
 │  SECRETARIA  (id_rol = 3)                             │
 ├──────────────────────────────────────────────────────┤
 │                                                       │
 │  [Dashboard]  /dashboard/secretaria                   │
 │   • Stats globales:                                   │
 │     · Total pacientes activos                         │
 │     · Total doctores activos                          │
 │     · Citas de hoy                                    │
 │     · Citas pendientes en el sistema                  │
 │   • Agenda de hoy (todos los doctores)                │
 │   • Lista de pacientes activos                        │
 │                                                       │
 │  [Citas]  /citas                                      │
 │   • Ve TODAS las citas de todos los pacientes         │
 │   • Puede crear citas para cualquier paciente         │
 │     · Selecciona paciente del catálogo                │
 │     · Selecciona doctor y horario                     │
 │                                                       │
 │  [🔔 Notificaciones]  polling cada 10s               │
 │   • N citas pendientes de confirmación               │
 │   • N citas programadas para hoy                     │
 └──────────────────────────────────────────────────────┘
```

---

### ⚙️ Admin — Lo que puede hacer

```
 ┌──────────────────────────────────────────────────────┐
 │  ADMIN  (id_rol = 4)                                  │
 ├──────────────────────────────────────────────────────┤
 │                                                       │
 │  [Panel Admin]  /admin                                │
 │   • Lista TODOS los usuarios del sistema              │
 │   • Agrupados por rol (Doctor, Secretaria, etc.)      │
 │   • Puede Desactivar / Reactivar cualquier usuario    │
 │     (soft delete: id_estado 8↔9)                     │
 │   • No puede desactivarse a sí mismo                  │
 │                                                       │
 │  [Crear Usuario]  /admin/crear-usuario                │
 │   • Crea usuarios de tipo:                            │
 │     · Doctor (vinculado a catálogo de doctores BD)    │
 │     · Secretaria                                      │
 │     · Admin                                           │
 │   • Los pacientes se registran solos en /registro     │
 │                                                       │
 │  Credenciales:                                        │
 │   Credencial: ADMIN-001                               │
 │   Correo:     admin@clinik.com                        │
 │   Contraseña: Admin@Clinik2024                        │
 └──────────────────────────────────────────────────────┘
```

---

## 5. Dependencias por Vista

| Vista | Controller | Service | Repository | Layout |
|-------|-----------|---------|-----------|--------|
| `auth/login.php` | AuthController | — | AuthRepository | standalone |
| `auth/registro.php` | RegistroController | — | UsuarioRepository | standalone |
| `paciente/dashboard.php` | PacienteController | — | DashboardRepository | head+sidebar+foot |
| `doctor/dashboard.php` | DoctorController | — | DashboardRepository | head+sidebar+foot |
| `secretaria/dashboard.php` | SecretariaController | — | DashboardRepository | head+sidebar+foot |
| `admin/dashboard.php` | AdminController | — | UsuarioRepository | head+sidebar+foot |
| `admin/crear_usuario.php` | AdminController | — | UsuarioRepository | head+sidebar+foot |
| `citas/index.php` | CitaController | CitaService | CitaRepository | head+sidebar+foot |
| `historial/atender_cita.php` | HistorialController | HistorialService | HistorialRepository + CitaRepository | head+sidebar+foot |
| `historial/doctor_historial.php` | HistorialController | HistorialService | HistorialRepository | head+sidebar+foot |
| `historial/paciente_historial.php` | HistorialController | HistorialService | HistorialRepository | head+sidebar+foot |

---

## 6. Resumen de Fases Implementadas

### ✅ Fase 1 — Infraestructura y Autenticación
- Front Controller en `public/index.php` con tabla de rutas GET/POST
- Detección dinámica de `BASE_PATH` (funciona en `/` y en `/clinik_app/`)
- `Database.php` Singleton PDO con manejo de excepciones
- `AuthController` → login con bcrypt, logout con destrucción de sesión
- Redirección post-login por rol (`id_rol` 1/2/3/4)
- `BaseController` con `requireAuth()` y `requireRole()`
- Layout compartido: `head.php` + `sidebar.php` + `foot.php`
- Sidebar dinámico por rol con `BASE_PATH` en todos los hrefs

### ✅ Fase 2 — Módulo de Citas
- `CitaRepository`: CRUD completo con JOINs (paciente, doctor, estado)
- `CitaService`: validación de horarios (bloques de 30 min), solapamientos
- `CitaController`: vista por rol (secretaria ve todo, doctor/paciente ven los suyos)
- Vista `citas/index.php`: formulario de nueva cita + listado con badges de estado
- API `CitasApiController`: disponibilidad horaria vía AJAX

### ✅ Fase 3 — Dashboards por Rol
- `DashboardRepository`: stats y listas para cada rol
- `PacienteController`, `DoctorController`, `SecretariaController`
- Cards de estadísticas, tablas de agenda, totales del sistema

### ✅ Fase 4 — Historial Médico
- `HistorialRepository`: INSERT en `HistorialMedico` + marcar cita como Atendida
- `HistorialService`: validaciones (mín. 10 chars diagnóstico, bloqueo 24h, estado cita)
- `HistorialController`: flujo completo atender → guardar → confirmar
- `atender_cita.php`: formulario del doctor con validación doble (JS + PHP)
- `doctor_historial.php`: lista de consultas con modal de detalle
- `paciente_historial.php`: timeline visual cronológico

### ✅ Fase 5 — Notificaciones Inteligentes
- `NotificacionesApiController`: datos reales de BD por rol
  - Paciente: citas próximas 2 días + consultas completadas
  - Doctor: citas de hoy pendientes con alerta de proximidad
  - Secretaria: pendientes globales + total del día
- Panel dropdown tipo mensajería (clic en 🔔)
- Toasts con icono, enlace de acción y botón ✕
- Badge animado en sidebar y campana con pulse

### ✅ Fase 6 — Admin + Auto-registro
- `RegistroController`: registro público de pacientes (crea en `paciente` + `usuarios` en transacción)
- `AdminController`: CRUD de usuarios, soft delete/reactivar
- `UsuarioRepository`: todas las operaciones de gestión de usuarios
- Rol Admin (`id_rol = 4`) creado en tabla `roles`
- Seeder `seed_admin.php` independiente
- Enlace "Créate una cuenta" en el login

---

## 7. Credenciales de Prueba

| Rol | Credencial | Contraseña |
|-----|-----------|-----------|
| Secretaria | `SEC-001` | `clinik2024` |
| Doctor | `DOC-001` | `clinik2024` |
| Paciente | `00000001-1` | `clinik2024` |
| **Admin** | `ADMIN-001` | `Admin@Clinik2024` |

---

## 8. Reglas de Negocio Clave

| Regla | Dónde se aplica |
|-------|----------------|
| Solo citas en estado Pendiente (1) o Aprobada (2) pueden atenderse | `HistorialService::guardarHistorial()` |
| Un historial no puede registrarse dos veces para la misma cita | `HistorialRepository::existeHistorialParaCita()` |
| Bloqueo de edición de historial tras 24h | `HistorialService::estaBloqueoEdicion()` |
| El doctor no puede crear citas (solo ver) | `CitaController::crear()` |
| El Admin no puede desactivarse a sí mismo | `AdminController::desactivar()` |
| Los pacientes solo ven sus propias citas e historial | `CitaController::index()`, `HistorialController::index()` |
| Contraseña mínima 8 caracteres (registro y creación de usuario) | `RegistroController`, `AdminController` |
