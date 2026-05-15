# Centro Pre Sistema

Aplicación web CPU UNPRG para portal público de información y registro de alumnos, y panel administrativo bajo el prefijo `/admin`.

## Requisitos

- PHP 8.5+
- Composer
- Base de datos compatible con el framework del proyecto (SQLite, MySQL, etc.)

## Puesta en marcha (resumen)

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

Ajusta `APP_URL` y la conexión de base de datos en `.env`.

---

## Rutas del portal público (usuario visitante)

No requieren sesión. Son la cara visible del sitio: inicio, catálogos y flujo de preinscripción.

| Método | URI | Nombre de ruta | Qué hace |
|--------|-----|----------------|----------|
| `GET` | `/` | `home` | Página de inicio pública (resumen / enlaces al portal). |
| `GET` | `/careers` | `careers` | Listado de carreras activas (catálogo público). |
| `GET` | `/campuses` | `campuses` | Listado de sedes activas (catálogo público). |
| * | `/register` | `register` | Redirige a `/registration` (alias amigable). |
| * | `/pre-registration` | `pre-registration.create` | Redirige a `/registration`. |
| `GET` | `/registration` | `registration.start` | Inicia el asistente de registro (paso inicial). |
| `GET` | `/registration/step/{step}` | `registration.step.show` | Muestra el paso `{step}` del formulario (`{step}` numérico: 1–4). |
| `POST` | `/registration/step/1` | `registration.step1.store` | Guarda datos del paso 1 (p. ej. datos del estudiante). |
| `POST` | `/registration/step/2` | `registration.step2.store` | Guarda paso 2 (p. ej. apoderado). |
| `POST` | `/registration/step/3` | `registration.step3.store` | Guarda paso 3 (p. ej. colegio de procedencia). |
| `POST` | `/registration/step/4` | `registration.step4.store` | Guarda paso 4 (carrera y turno con cupo). |
| `POST` | `/registration/finish` | `registration.finish` | Envío final: valida todo y persiste el registro. |

### Límites de uso (portal público)

- Rutas bajo `throttle:public-registration` (pasos del wizard): **20 peticiones por minuto** por IP.
- `POST /registration/finish` usa `throttle:public-registration-finish`: **5 por minuto** por IP.

---

## Rutas del panel administrativo (`/admin`)

Prefijo común: **`/admin`**. El login de staff vive aquí; el resto exige usuario autenticado.

### Autenticación

| Método | URI | Nombre | Qué hace |
|--------|-----|--------|----------|
| `GET` | `/admin/login` | `login` | Formulario de inicio de sesión (solo invitados: middleware `guest`). |
| `POST` | `/admin/login` | — | Procesa login. **10 intentos por minuto** por IP (`throttle:admin-login`). |
| `POST` | `/admin/logout` | `logout` | Cierra sesión (requiere `auth`). |

Los usuarios no autenticados que intenten acceder a rutas protegidas son redirigidos a `route('login')` (`/admin/login`). Tras iniciar sesión, CPU UNPRG redirige al `dashboard`.

### Área autenticada (todas `GET`/`POST`/`PUT`/`PATCH`/`DELETE` bajo `/admin` con `auth`)

| Método | URI | Nombre | Qué hace |
|--------|-----|--------|----------|
| `GET` | `/admin/dashboard` | `dashboard` | Panel principal tras iniciar sesión. |

#### Módulo Staff (`middleware: staff.module`)

Gestión de usuarios personal / staff (sin ruta `show`).

| Método | URI | Nombre |
|--------|-----|--------|
| `GET` | `/admin/staff` | `staff.index` |
| `GET` | `/admin/staff/create` | `staff.create` |
| `POST` | `/admin/staff` | `staff.store` |
| `GET` | `/admin/staff/{staff}/edit` | `staff.edit` |
| `PUT`/`PATCH` | `/admin/staff/{staff}` | `staff.update` |
| `DELETE` | `/admin/staff/{staff}` | `staff.destroy` |

#### Módulo ciclos académicos (`middleware: academic-cycles.module`)

Prefijo: **`/admin/academic-cycles`**. Nombres de ruta con prefijo `academic-cycles.`.

**Vista general de turnos (índice del módulo)**

| Método | URI | Nombre |
|--------|-----|--------|
| `GET` | `/admin/academic-cycles` | `academic-cycles.index` |

**Ciclos** — recurso `cycles` (parámetro de ruta: `{academic_cycle}`)

| Método | URI | Nombre |
|--------|-----|--------|
| `GET` | `/admin/academic-cycles/cycles` | `academic-cycles.cycles.index` |
| `GET` | `/admin/academic-cycles/cycles/create` | `academic-cycles.cycles.create` |
| `POST` | `/admin/academic-cycles/cycles` | `academic-cycles.cycles.store` |
| `GET` | `/admin/academic-cycles/cycles/{academic_cycle}/edit` | `academic-cycles.cycles.edit` |
| `PUT`/`PATCH` | `/admin/academic-cycles/cycles/{academic_cycle}` | `academic-cycles.cycles.update` |
| `DELETE` | `/admin/academic-cycles/cycles/{academic_cycle}` | `academic-cycles.cycles.destroy` |

**Sedes (campuses)** — `{campus}`

| Método | URI | Nombre |
|--------|-----|--------|
| `GET` | `/admin/academic-cycles/campuses` | `academic-cycles.campuses.index` |
| `GET` | `/admin/academic-cycles/campuses/create` | `academic-cycles.campuses.create` |
| `POST` | `/admin/academic-cycles/campuses` | `academic-cycles.campuses.store` |
| `GET` | `/admin/academic-cycles/campuses/{campus}/edit` | `academic-cycles.campuses.edit` |
| `PUT`/`PATCH` | `/admin/academic-cycles/campuses/{campus}` | `academic-cycles.campuses.update` |
| `DELETE` | `/admin/academic-cycles/campuses/{campus}` | `academic-cycles.campuses.destroy` |

**Turnos (shifts)** — `{shift}`

| Método | URI | Nombre |
|--------|-----|--------|
| `GET` | `/admin/academic-cycles/shifts` | `academic-cycles.shifts.index` |
| `GET` | `/admin/academic-cycles/shifts/create` | `academic-cycles.shifts.create` |
| `POST` | `/admin/academic-cycles/shifts` | `academic-cycles.shifts.store` |
| `GET` | `/admin/academic-cycles/shifts/{shift}/edit` | `academic-cycles.shifts.edit` |
| `PUT`/`PATCH` | `/admin/academic-cycles/shifts/{shift}` | `academic-cycles.shifts.update` |
| `DELETE` | `/admin/academic-cycles/shifts/{shift}` | `academic-cycles.shifts.destroy` |

**Cronogramas / combinaciones ciclo–sede–turno (`schedules`)** — modelo enlazado como `{schedule}` (`AcademicCycleShift`). Sin `index` ni `show`; solo alta/edición/baja.

| Método | URI | Nombre |
|--------|-----|--------|
| `GET` | `/admin/academic-cycles/schedules/create` | `academic-cycles.schedules.create` |
| `POST` | `/admin/academic-cycles/schedules` | `academic-cycles.schedules.store` |
| `GET` | `/admin/academic-cycles/schedules/{schedule}/edit` | `academic-cycles.schedules.edit` |
| `PUT`/`PATCH` | `/admin/academic-cycles/schedules/{schedule}` | `academic-cycles.schedules.update` |
| `DELETE` | `/admin/academic-cycles/schedules/{schedule}` | `academic-cycles.schedules.destroy` |

#### Módulo alumnos (`middleware: students.module`)

| Método | URI | Nombre |
|--------|-----|--------|
| `GET` | `/admin/students` | `students.index` |
| `GET` | `/admin/students/create` | `students.create` |
| `POST` | `/admin/students` | `students.store` |
| `GET` | `/admin/students/{student}/edit` | `students.edit` |
| `PUT`/`PATCH` | `/admin/students/{student}` | `students.update` |
| `DELETE` | `/admin/students/{student}` | `students.destroy` |

Los middlewares `staff.module`, `academic-cycles.module` y `students.module` restringen el acceso según la lógica de la aplicación (roles/permisos del staff).

---

## Otras rutas del framework

| Método | URI | Notas |
|--------|-----|--------|
| `GET` | `/up` | Comprobación de salud (health check) de CPU UNPRG. |
| `GET`/`PUT` | `/storage/{path}` | Archivos públicos almacenados (enlace simbólico `storage`). |

---

## Referencia rápida: generar URLs en Blade / PHP

```php
route('home');
route('registration.start');
route('registration.step.show', ['step' => 2]);
route('login');
route('dashboard');
route('students.index');
```

Listado completo en consola:

```bash
php artisan route:list
```

---

## Licencia

Proyecto CPU UNPRG; sus dependencias de framework se distribuyen bajo sus respectivas licencias.
