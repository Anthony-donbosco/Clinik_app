# 🏗️ Rediseño Arquitectónico: Fuente Única de Verdad para Doctores

## Problema raíz diagnosticado

El sistema tenía **dos representaciones del mismo objeto Doctor**:

| Tabla | Rol | Quién la leía |
|-------|-----|---------------|
| `doctor` | Perfil médico (nombre, especialidad, citas) | Pacientes, Secretaria, DashboardRepository |
| `usuarios` | Credenciales de acceso (correo, contraseña, rol) | Admin, AuthController |

El Admin creaba en `usuarios` referenciando a `doctor` (catálogo), pero los cambios del Admin nunca regresaban a `doctor` → los pacientes nunca veían los doctores "editados".

---

## Solución implementada

### Modelo correcto: `doctor` (perfil) ← `usuarios.id_referencia` (credenciales)

```
tabla doctor                    tabla usuarios
─────────────────               ──────────────────────────────
id_doctor (PK)    ←───FK───     id_referencia (para id_rol=2)
primer_nombre                   nombre  (= primer+segundo nombre)
segundo_nombre                  apellido (= primer+segundo apellido)
primer_apellido                 correo
segundo_apellido                numero_identificacion
especialidad                    contrasena_hash
id_estado                       id_rol = 2
                                id_estado (espejo de doctor.id_estado)
```

> **Regla**: La tabla `doctor` es la fuente de verdad del perfil. La tabla `usuarios` es la fuente de verdad de las credenciales. Están vinculadas por `usuarios.id_referencia = doctor.id_doctor`.

---

## Archivos modificados

### `src/Repositories/UsuarioRepository.php`

| Método | Cambio |
|--------|--------|
| `getAllUsuarios()` | Ahora hace `LEFT JOIN doctor` para incluir `especialidad` e `id_doctor` |
| `crearDoctorCompleto()` | **NUEVO** — transacción atómica: INSERT en `doctor` + INSERT en `usuarios` |
| `editarDoctor()` | **NUEVO** — transacción atómica: UPDATE en `doctor` + UPDATE en `usuarios` |
| `desactivarUsuario()` | Ahora también hace `UPDATE doctor SET id_estado=9` si es doctor |
| `reactivarUsuario()` | Ahora también hace `UPDATE doctor SET id_estado=8` si es doctor |
| ~~`getDoctoresLibres()`~~ | **ELIMINADO** — ya no existe el concepto de catálogo separado |
| ~~`crearUsuarioDoctor()`~~ | **ELIMINADO** — reemplazado por `crearDoctorCompleto()` |

### `src/Controllers/Web/AdminController.php`

| Método | Cambio |
|--------|--------|
| `showCrear()` | Ya no carga `getDoctoresLibres()` (eliminado) |
| `procesarCrear()` (rol=2) | Llama a `crearDoctorCompleto()` con datos completos del formulario |
| `showEditar()` | **NUEVO** — carga datos via `getDoctorById()`, renderiza `editar_doctor.php` |
| `procesarEditar()` | **NUEVO** — valida y llama a `editarDoctor()` |

### `views/admin/crear_usuario.php`

- **Panel Doctor**: ahora muestra campos completos (primer/segundo nombre, apellidos, especialidad, correo, cédula, contraseña). **Sin selector de catálogo**.
- **Panel Staff**: nombre simple + correo + cédula + contraseña (sin cambios conceptuales).

### `views/admin/editar_doctor.php` (NUEVO)

- Formulario prellenado con datos de `doctor` + `usuarios`.
- Envía `id_doctor` e `id_usuario` ocultos al controlador.
- Actualiza ambas tablas simultáneamente.
- `POST /admin/editar-doctor`

### `views/admin/dashboard.php`

- Añade columna **Especialidad** para doctores (del LEFT JOIN).
- Añade botón **✏️ Editar** que lleva a `/admin/editar-doctor?id={id_doctor}`.

### `public/index.php`

```
GET  /admin/editar-doctor  → AdminController::showEditar()
POST /admin/editar-doctor  → AdminController::procesarEditar()
```

---

## Flujo correcto "Deber Ser"

```
[Admin] POST /admin/crear-usuario (tipo_rol=2)
  └─ AdminController::procesarCrear()
  └─ UsuarioRepository::crearDoctorCompleto()
     ├─ INSERT INTO doctor (perfil médico)  ← id_doctor nuevo
     └─ INSERT INTO usuarios (credenciales, id_referencia=id_doctor)

[Paciente] GET /citas
  └─ CitaRepository::getDoctoresParaSelect()
     └─ SELECT FROM doctor WHERE id_estado=8  ← ve el doctor recién creado ✅

[Paciente] Agenda cita con ese doctor → cita.id_doctor = nuevo id_doctor

[Doctor] Inicia sesión con sus credenciales
  └─ AuthController → usuarios.id_referencia = id_doctor
  └─ DashboardRepository::getAgendaDoctor(id_doctor) ✅

[Admin] GET /admin/editar-doctor?id={id_doctor}
  └─ UsuarioRepository::getDoctorById() → datos de doctor + usuarios
  └─ POST → UsuarioRepository::editarDoctor()
     ├─ UPDATE doctor SET nombre, especialidad...  ← pacientes ven el cambio ✅
     └─ UPDATE usuarios SET nombre, correo...      ← credenciales actualizadas ✅
```

---

## Integridad de estado: Desactivar/Reactivar

Cuando el Admin desactiva/reactiva un usuario Doctor, el sistema ahora:
1. Actualiza `usuarios.id_estado` (bloquea el login)
2. Actualiza `doctor.id_estado` (oculta al doctor del selector de citas para pacientes)

Ambas tablas permanecen sincronizadas.

---

## Lo que NO cambió (sin impacto)

- `CitaRepository` — lee siempre de `doctor` (correcto por diseño)
- `DashboardRepository` — igual
- `HistorialRepository` — igual
- Flujo de pacientes, secretaria, doctor — sin cambios
- Login / Auth — sin cambios
