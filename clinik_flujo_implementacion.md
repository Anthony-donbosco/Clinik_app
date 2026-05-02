# 📂 Clini-K — Flujo de Implementación por Diagrama

> Trazabilidad completa: cada diagrama del sistema mapeado a los archivos PHP/JS que lo implementan.

---

## Diagrama 1 — DFD Nivel 0: Sistema de Gestión Médica

**Describe:** Entradas y salidas de alto nivel entre el sistema y los 3 actores externos (Paciente, Doctora, Secretaria).

### Flujo por entidad

#### Paciente → Consultar horarios
```
GET /api/citas/disponibilidad
  └─ public/index.php (router)
  └─ CitasApiController::disponibilidad()
  └─ CitaService::getHorariosDisponibles()
  └─ CitaRepository::getHorasOcupadas()
  └─ BD: tabla `cita`
  └─ views/citas/index.php (renderiza grilla AJAX)
```

#### Paciente → Agendar / Cancelar cita
```
POST /citas/crear  |  POST /api/citas/cancelar
  └─ CitaController::crear()  |  CitasApiController::cancelar()
  └─ CitaService::validarYCrearCita()  |  CitaService::cancelarCita()
  └─ CitaRepository::crearCita()  |  CitaRepository::cancelarCita()
  └─ BD: tabla `cita`
  └─ Flash message / JSON response
```

#### Paciente ← Historial Médico (Salida)
```
GET /historial
  └─ HistorialController::index()
  └─ HistorialService::getHistorialPaciente()
  └─ HistorialRepository::getHistorialByPaciente()
  └─ BD: historial + diagnostico + tratamiento + receta
  └─ views/historial/paciente_historial.php
```

#### Paciente ← Factura (Salida) `[Fase 1 — nuevo]`
```
GET /facturas
  └─ FacturaController::index()  [NUEVO]
  └─ FacturaRepository::getFacturasByPaciente()  [NUEVO]
  └─ BD: tabla `factura`
  └─ views/facturas/index.php  [NUEVO]
```

#### Secretaria → Generar facturas `[Fase 1 — nuevo]`
```
POST /facturas/crear
  └─ FacturaController::crear()  [NUEVO]
  └─ FacturaRepository::crearFactura()  [NUEVO]
  └─ BD: tabla `factura`
  └─ Redirect + flash_ok
```

#### Doctora → Diagnóstico + Tratamiento + Receta `[Fase 2: receta obligatoria]`
```
POST /historial/guardar
  └─ HistorialController::guardar()
  └─ HistorialService::guardarHistorial()
     ├─ Valida: diagnostico (≥10), tratamiento (≥5), notas/receta (obligatorio) [Fase 2]
  └─ HistorialRepository::crearHistorial()  ← transacción atómica
     ├─ INSERT historial
     ├─ INSERT diagnostico
     ├─ INSERT tratamiento
     └─ INSERT receta
  └─ BD: 4 tablas
```

**Estado final:** ✅ 100%

---

## Diagrama 2 — DFD Nivel 1: CLINI-K

**Describe:** Procesos internos, almacenes de datos y flujos entre ellos.

### Proceso: Gestión de Citas
```
views/citas/index.php
  └─ AJAX fetch → CitasApiController::disponibilidad()
  └─ CitaService::validarYCrearCita()
     ├─ CitaRepository::existeConflicto()
     ├─ CitaRepository::getHorasOcupadas()
     └─ CitaRepository::crearCita()  → BD: `cita` (id_estado=1 Pendiente)
```

### Proceso: Validar disponibilidad
```
CitaService::getHorariosDisponibles()
  └─ CitaRepository::getHorasOcupadas()
  └─ Genera bloques 08:00–17:00, marca ocupados
  └─ Retorna JSON {hora, disponible}
```

### Proceso: Control de Citas (Secretaria) `[Fase 2: aprobar/rechazar nuevo]`
```
POST /api/citas/aprobar  |  /api/citas/rechazar
  └─ CitasApiController::aprobar()  |  ::rechazar()  [NUEVO]
  └─ CitaRepository::aprobarCita()  |  ::rechazarCita()  [NUEVO]
  └─ BD: UPDATE cita SET id_estado = 2|3
  └─ views/citas/index.php: botones ✔ Aprobar / ✖ Rechazar (solo rol=3)
```

### Proceso: Facturación `[Fase 1 — nuevo completo]`
```
src/Repositories/FacturaRepository.php  [NUEVO]
src/Controllers/Web/FacturaController.php  [NUEVO]
views/facturas/index.php  [NUEVO]
  └─ BD: tabla `factura` (ya existía con 20 registros seed)
```

### Almacenes de Datos
| BD Diagrama | Tabla | Repositorio |
|-------------|-------|-------------|
| Pacientes | `paciente` | `UsuarioRepository` |
| Facturas | `factura` | `FacturaRepository` ✅ Fase 1 |
| Citas | `cita` | `CitaRepository` |
| Historial Clínico | `historial` + 3 tablas | `HistorialRepository` |

**Estado final:** ✅ 100%

---

## Diagrama 3 — Casos de Uso

**Describe:** Qué puede hacer cada actor y las relaciones `<<include>>`.

### Actor: Paciente

| Caso de Uso | Archivos involucrados |
|-------------|----------------------|
| Registrarse | `RegistroController` → `UsuarioRepository::registrarPaciente()` |
| **Actualizar datos** `[Fase 1]` | `PacienteController::perfil/actualizarPerfil()` → `UsuarioRepository::actualizarPerfilPaciente()` → `views/paciente/perfil.php` |
| Agendar cita | `CitaController` → `CitaService` → `CitaRepository` |
| `<<include>>` Consultar disponibilidad | `CitasApiController::disponibilidad()` (AJAX automático) |
| Cancelar cita | `CitasApiController::cancelar()` |
| Consultar citas | `CitaController::index()` → `views/citas/index.php` |
| Recibir historial | `HistorialController::index()` → `views/historial/paciente_historial.php` |
| **Recibir factura** `[Fase 1]` | `FacturaController::index()` → `views/facturas/index.php` |

### Actor: Secretaria

| Caso de Uso | Archivos involucrados |
|-------------|----------------------|
| Monitorear citas | `CitaController::index()` → `views/citas/index.php` (ve todas) |
| **Actualizar estado** `[Fase 2]` | `CitasApiController::aprobar()/rechazar()` → `CitaRepository::aprobarCita()/rechazarCita()` |
| **Generar factura** `[Fase 1]` | `FacturaController::crear()` → `FacturaRepository::crearFactura()` |

### Actor: Doctor

| Caso de Uso | Archivos involucrados |
|-------------|----------------------|
| Consultar agenda | `DoctorController::dashboard()` → `DashboardRepository::getAgendaDoctor()` |
| Registrar diagnóstico | `HistorialController::guardar()` → `HistorialService` → `HistorialRepository` |
| Registrar tratamiento `<<include>>` diag | Mismo form, campo `tratamiento` obligatorio |
| **Registrar receta** `<<include>>` diag `[Fase 2]` | Campo `notas` ahora **obligatorio** (≥5 chars) en PHP + JS |
| `<<include>>` Consultar historial `[Fase 1]` | `HistorialService::getHistorialPaciente()` pasado a `atender_cita.php` (sección colapsable) |
| Consultar historial | `HistorialController::index()` → `views/historial/doctor_historial.php` |

**Estado final:** ✅ 100%

---

## Diagrama 4 — Flujo General de Atención Completa

**Describe:** Ciclo completo: disponibilidad → agendar → atender → historial → factura.

```
[Paciente]
  1. views/citas/index.php
     └─ AJAX /api/citas/disponibilidad → grilla de horarios
  2. POST /citas/crear → CitaController → CitaService → CitaRepository
     └─ BD: cita (id_estado=1)

[Secretaria] — Fase 2
  3. GET /citas → views/citas/index.php
     └─ Botones ✔ Aprobar / ✖ Rechazar → /api/citas/aprobar|rechazar
     └─ BD: cita (id_estado=2|3)

[Doctor]
  4. GET /dashboard/doctor → DashboardRepository::getAgendaDoctor()
  5. GET /historial/atender?cita=ID → HistorialController::atender()
     └─ HistorialService::getCitaParaAtender()
     └─ HistorialService::getHistorialPaciente()  [Fase 1: historial previo]
     └─ views/historial/atender_cita.php
        ├─ Sección colapsable: historial previo del paciente
        └─ Form: diagnostico* + tratamiento* + receta*  [Fase 2: receta obligatoria]
  6. POST /historial/guardar → HistorialService → HistorialRepository (transacción)
     └─ BD: historial + diagnostico + tratamiento + receta
     └─ UPDATE cita SET id_estado=4 (Atendida)

[Secretaria] — Fase 1
  7. GET /facturas → FacturaController::index()
     └─ Select: citas atendidas sin factura → FacturaRepository::getCitasSinFactura()
  8. POST /facturas/crear → FacturaRepository::crearFactura()
     └─ BD: INSERT factura

[Paciente]
  9. GET /facturas → FacturaController::index() (rol=1)
     └─ FacturaRepository::getFacturasByPaciente()
     └─ views/facturas/index.php (solo sus facturas)
```

**Estado final:** ✅ 100%

---

## Diagrama 5 — Flujo: Agendar Citas

**Describe:** Pasos del paciente para reservar una cita con verificación de disponibilidad.

```
public/index.php  →  GET /citas  →  CitaController::index()
  └─ CitaRepository::getCitasByPaciente()
  └─ CitaRepository::getDoctoresDisponibles()
  └─ views/citas/index.php
       ├─ #select-doctor + #input-fecha (JS)
       ├─ onChange → fetch /api/citas/disponibilidad
       │    └─ CitasApiController::disponibilidad()
       │    └─ CitaService::getHorariosDisponibles()
       │    └─ renderHorarios() → botones .horario-btn / .ocupado
       ├─ Click horario → selección, muestra #form-submit-area
       └─ POST /citas/crear
            └─ CitaController::crear()
            └─ CitaService::validarYCrearCita()
               ├─ Bloquea: fines de semana, fechas pasadas
               ├─ CitaRepository::existeConflicto()
               └─ CitaRepository::crearCita() → id_estado=1
            └─ Redirect /citas + flash_ok
```

**Estado final:** ✅ 100% (ya estaba completo, sin cambios)

---

## Diagrama 6 — Flujo: Iniciar Sesión

**Describe:** Autenticación con redirección por rol y cierre de sesión.

```
GET /login
  └─ AuthController::showLogin()
  └─ views/auth/login.php
       └─ form: cedula/correo + contrasena

POST /login
  └─ AuthController::processLogin()
  └─ UsuarioRepository::findActiveUserByCredential()
       └─ BD: SELECT usuarios WHERE correo|numero_identificacion
  └─ password_verify() con bcrypt
  └─ session_regenerate_id(true)
  └─ $_SESSION: id_usuario, id_rol, nombre, id_referencia, nombre_rol
  └─ Redirect por rol:
       ├─ rol=1 → /dashboard/paciente
       ├─ rol=2 → /dashboard/doctor
       ├─ rol=3 → /dashboard/secretaria
       └─ rol=4 → /admin

GET /logout
  └─ AuthController::logout()
  └─ session_destroy()
  └─ Redirect /login
```

**Estado final:** ✅ 100% (ya estaba completo, sin cambios)

---

## Diagrama 7 — Flujo: Registro de Historial Médico

**Describe:** Proceso de la doctora para registrar diagnóstico, tratamiento y receta tras atender a un paciente.

```
[Paso 1] GET /dashboard/doctor
  └─ DoctorController::dashboard()
  └─ DashboardRepository::getAgendaDoctor()  → citas próximos 7 días
  └─ views/doctor/dashboard.php  → botón "✅ Atender" por cada cita

[Paso 2] GET /historial/atender?cita=ID
  └─ HistorialController::atender()
  └─ HistorialService::getCitaParaAtender()  → valida que la cita es del doctor
  └─ HistorialService::getHistorialPaciente()  [Fase 1: nuevo]
  └─ views/historial/atender_cita.php
       └─ Sección colapsable "📋 Historial Previo"  [Fase 1: nuevo]
          └─ Items .historial-previo-item con diagnóstico, tratamiento, receta

[Pasos 4-6] Formulario en atender_cita.php
  ├─ #diagnostico  → required, minlength=10
  ├─ #tratamiento  → required, minlength=5
  └─ #notas        → required, minlength=5  [Fase 2: ahora obligatorio]
       └─ JS valida antes de submit → alert si vacío

[Paso 7] POST /historial/guardar
  └─ HistorialController::guardar()
  └─ HistorialService::guardarHistorial()
     ├─ Valida diagnostico ≥ 10 chars (PHP)
     ├─ Valida tratamiento ≥ 5 chars (PHP)
     └─ Valida notas no vacío  [Fase 2: nuevo]
  └─ HistorialRepository::crearHistorial()  ← transacción PDO
     ├─ INSERT historial
     ├─ INSERT diagnostico (id_historial)
     ├─ INSERT tratamiento (id_historial)
     └─ INSERT receta (id_historial) ← si notas no vacío

[Paso 8] HistorialRepository::marcarCitaAtendida()
  └─ UPDATE cita SET id_estado=4 WHERE id_estado IN (1,2)

[Paso 9] Resumen imprimible  [Fase 2: nuevo]
  └─ views/historial/paciente_historial.php
       ├─ Botón 🖨️ "Imprimir" en topbar → window.print()
       └─ @media print { ... }  → oculta sidebar/nav, cabecera institucional,
                                    timeline en blanco y negro, page-break-inside:avoid
```

**Estado final:** ✅ 100%

---

## Diagrama 8 — Flujo: Registro de Facturas `[Fase 1 — nuevo completo]`

**Describe:** La secretaria genera facturas para citas atendidas; el paciente las consulta.

```
[Secretaria]
GET /facturas
  └─ public/index.php → FacturaController::index()  [NUEVO]
  └─ rol=3:
       ├─ FacturaRepository::getAllFacturas()  → lista todas las facturas
       ├─ FacturaRepository::getCitasSinFactura()  → SELECT citas atendidas sin factura
       └─ views/facturas/index.php
            └─ Form "Emitir Nueva Factura":
                 ├─ #select-cita  → auto-completa detalle con especialidad del doctor
                 ├─ #input-detalle
                 └─ #input-monto

POST /facturas/crear
  └─ FacturaController::crear()
  └─ FacturaRepository::existeFacturaParaCita()  → evita duplicados
  └─ FacturaRepository::crearFactura()
  └─ BD: INSERT factura (id_cita, fecha=CURDATE(), detalle, monto)
  └─ Redirect /facturas + flash_ok

[Paciente]
GET /facturas
  └─ FacturaController::index()
  └─ rol=1:
       ├─ FacturaRepository::getFacturasByPaciente(id_paciente)
       └─ views/facturas/index.php  → tabla solo con sus facturas
            └─ Botón 🖨️ Imprimir → window.print()
```

**Archivos creados:**
| Archivo | Tipo |
|---------|------|
| `src/Repositories/FacturaRepository.php` | Nuevo |
| `src/Controllers/Web/FacturaController.php` | Nuevo |
| `views/facturas/index.php` | Nuevo |
| `public/index.php` | Rutas agregadas |
| `views/layout/sidebar.php` | Links 🧾 agregados |

**Estado final:** ✅ 100%

---

## Extra — Módulo: Perfil del Paciente `[Fase 1 — nuevo]`

**Contexto:** Brecha del Diagrama 3 (Casos de Uso: "Actualizar datos").

```
GET /paciente/perfil
  └─ PacienteController::perfil()
  └─ UsuarioRepository::getPerfilPaciente(id_paciente)
     └─ JOIN paciente + usuarios
  └─ views/paciente/perfil.php
       ├─ Columna izq: datos de identidad (solo lectura)
       └─ Columna der: form editar teléfono + correo

POST /paciente/perfil
  └─ PacienteController::actualizarPerfil()
  └─ UsuarioRepository::actualizarPerfilPaciente()
     ├─ Verifica unicidad del correo
     ├─ UPDATE paciente SET telefono
     └─ UPDATE usuarios SET correo
  └─ Actualiza $_SESSION['correo']
  └─ Redirect /paciente/perfil + flash_ok
```

---

## Extra — Módulo: Notificaciones con Estado Leídas

**Contexto:** El badge volvía a aparecer después de ver las notificaciones porque no había estado persistido.

```
[Servidor — cada 10s]
GET /api/notificaciones
  └─ NotificacionesApiController::index()
  └─ Según rol:
       ├─ rol=1 → notifPaciente()  → citas próximas 2 días
       ├─ rol=2 → notifDoctor()    → citas de hoy sin atender
       └─ rol=3 → notifSecretaria() → citas pendientes/hoy
  └─ Cada item incluye `id` único estable:
       ├─ pac-cita-{id_cita}
       ├─ doc-cita-{id_cita}
       └─ sec-pending-{fecha}  /  sec-today-{fecha}

[Cliente — localStorage]
notificaciones.js
  ├─ getReadIds()   → lee Set de IDs leídos (con TTL 24h)
  ├─ markRead(id)   → guarda id + timestamp en localStorage
  ├─ markAllRead()  → guarda todos los IDs actuales
  └─ getUnread()    → filtra allItems quitando los leídos

[Panel UI]
Panel dropdown #notif-panel
  ├─ Header: "🔔 Notificaciones" + [✅ Todas leídas] + [✕]
  ├─ Items no leídos: texto normal + botón ✓ (marcar una)
  ├─ Items leídos: atenuados (opacity .55) + checkmark ✓ estático
  └─ Badge y dot: solo cuentan items NO leídos

[main.css — nuevas clases]
  .np-header-actions, .np-mark-all, .np-mark-one,
  .np-item-right, .np-read-badge, .np-item-read
```

---

## Resumen de Completitud

| Diagrama | Fase | Estado |
|----------|------|--------|
| DFD Nivel 0 | Fase 1 (Facturas) | ✅ 100% |
| DFD Nivel 1 | Fase 1 (Facturas + Aprobar/Rechazar) | ✅ 100% |
| Casos de Uso | Fase 1 (Facturas, Perfil) + Fase 2 (Receta, Aprobar/Rechazar) | ✅ 100% |
| Flujo General Atención | Fase 1 + Fase 2 | ✅ 100% |
| Flujo: Agendar Citas | Sin cambios (ya completo) | ✅ 100% |
| Flujo: Iniciar Sesión | Sin cambios (ya completo) | ✅ 100% |
| Flujo: Historial Médico | Fase 1 (historial previo) + Fase 2 (receta oblig. + imprimir) | ✅ 100% |
| Flujo: Registro de Facturas | Fase 1 (nuevo completo) | ✅ 100% |
| Notificaciones | Extra (estado leídas en localStorage) | ✅ Resuelto |

### Archivos nuevos creados en todo el proceso

| Archivo | Propósito |
|---------|-----------|
| `src/Repositories/FacturaRepository.php` | CRUD tabla `factura` |
| `src/Controllers/Web/FacturaController.php` | Módulo de facturación |
| `views/facturas/index.php` | Vista unificada por rol |
| `views/paciente/perfil.php` | Editar teléfono/correo |

### Archivos modificados clave

| Archivo | Cambios |
|---------|---------|
| `src/Controllers/Web/PacienteController.php` | + `perfil()`, `actualizarPerfil()` |
| `src/Repositories/UsuarioRepository.php` | + `getPerfilPaciente()`, `actualizarPerfilPaciente()` |
| `src/Controllers/Web/HistorialController.php` | + carga historial previo en `atender()` |
| `src/Services/HistorialService.php` | + validación receta obligatoria |
| `src/Repositories/CitaRepository.php` | + `aprobarCita()`, `rechazarCita()` |
| `src/Controllers/Api/CitasApiController.php` | + `aprobar()`, `rechazar()` |
| `src/Controllers/Api/NotificacionesApiController.php` | + `id` estable por notificación |
| `views/historial/atender_cita.php` | + historial previo colapsable, receta obligatoria |
| `views/historial/paciente_historial.php` | + botón imprimir, @media print |
| `views/citas/index.php` | + botones Aprobar/Rechazar para Secretaria |
| `views/layout/sidebar.php` | + Facturas (rol 1 y 3), Mi Perfil (rol 1) |
| `public/index.php` | + 6 nuevas rutas |
| `public/assets/js/notificaciones.js` | Reescritura: estado leídas en localStorage |
| `public/assets/css/main.css` | + Estilos panel notificaciones |
