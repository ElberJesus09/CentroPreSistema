# Centro Pre Sistema

Sistema Laravel para el Centro Pre Universitario UNPRG. Incluye portal publico de informacion e inscripcion de alumnos, panel administrativo, gestion de staff, ciclos academicos, sedes, turnos, aulas, notas, reportes, permisos y auditoria de acciones.

## Requisitos

- PHP 8.3 o superior.
- Composer 2.
- Node.js 20 o superior y npm.
- Base de datos: MySQL/MariaDB recomendado para produccion; SQLite es util para desarrollo y pruebas.
- Extensiones PHP habituales de Laravel: `ctype`, `curl`, `dom`, `fileinfo`, `filter`, `hash`, `mbstring`, `openssl`, `pdo`, `tokenizer`, `xml`, `zip`.
- Servidor web con HTTPS en produccion: Nginx o Apache.
- Supervisor o systemd para colas.

## Instalacion Local

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
php artisan serve
```

El panel administrativo queda disponible en `/admin/login`.

## Configuracion Del `.env`

Configura como minimo:

```env
APP_NAME=CPU-UNPRG
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://tu-dominio.edu.pe
APP_TIMEZONE=America/Lima

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=centropre
DB_USERNAME=usuario
DB_PASSWORD=clave-segura

SESSION_DRIVER=database
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

QUEUE_CONNECTION=database
CACHE_STORE=database
LOG_CHANNEL=stack
LOG_STACK=daily,security
```

No publiques `.env`. Mantener `APP_DEBUG=false` evita exponer trazas, rutas internas, consultas SQL y variables sensibles.

## Migraciones Y Seeders

```bash
php artisan migrate
php artisan db:seed
```

En produccion:

```bash
php artisan migrate --force
php artisan db:seed --force
```

El seeder crea el usuario inicial `superadmin`. Define `SUPERADMIN_INITIAL_PASSWORD` antes de ejecutar seeders en produccion y cambia la clave despues del primer acceso.

## Comandos Importantes

```bash
php artisan test
php artisan route:list
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear
php artisan queue:work --tries=3 --timeout=120
```

Optimizacion para produccion:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

## Despliegue

1. Subir el codigo al servidor.
2. Configurar `.env` con credenciales reales y `APP_DEBUG=false`.
3. Instalar dependencias con `composer install --no-dev --optimize-autoloader`.
4. Compilar assets con `npm ci && npm run build`.
5. Ejecutar `php artisan migrate --force`.
6. Publicar enlace de storage si se requiere: `php artisan storage:link`.
7. Aplicar caches de Laravel.
8. Configurar workers de cola.
9. Configurar backups automaticos de base de datos y archivos.
10. Verificar `/up`, `/`, `/registration` y `/admin/login`.

## Colas

El proyecto usa `QUEUE_CONNECTION=database` por defecto. Para procesos pesados como correos, PDFs, importaciones o reportes, mantener un worker activo:

```ini
[program:centropre-worker]
command=php /var/www/centropre/artisan queue:work database --sleep=3 --tries=3 --timeout=120
directory=/var/www/centropre
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/centropre/storage/logs/worker.log
```

## Arquitectura

- `app/Http/Controllers`: controladores del portal y panel administrativo.
- `app/Http/Requests`: validaciones centralizadas de formularios.
- `app/Services`: reglas de negocio, transacciones, reportes, PDFs y correos.
- `app/Policies`: autorizacion por modulo y accion.
- `app/Models`: modelos Eloquent con asignacion masiva restringida.
- `app/Observers`: auditoria automatica de cambios relevantes.
- `routes/web.php`: rutas web publicas y administrativas.
- `database/migrations`: estructura e indices de base de datos.
- `database/seeders`: datos iniciales de roles, staff, turnos y catalogos.
- `resources/views`: vistas Blade.
- `resources/js` y `resources/css`: assets compilados por Vite.

## Seguridad

El sistema incluye:

- Proteccion CSRF del grupo web de Laravel.
- Validaciones con Form Requests.
- Consultas Eloquent y Query Builder para evitar SQL Injection.
- Escape automatico de Blade para mitigar XSS.
- Sanitizacion de datos de inscripcion antes de persistir.
- Rate limiting por IP y usuario para login e inscripcion publica.
- Bloqueo temporal de abuso de formularios.
- Headers HTTP de seguridad: `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy` y HSTS bajo HTTPS.
- Cookies de sesion configurables como seguras, HTTP only y SameSite.
- Policies y middlewares de modulo para rutas privadas.
- Activity logs para acciones administrativas.
- Canal `security` para intentos fallidos y bloqueos por tasa.
- Validacion de archivos por extension, MIME y tamano.

Recomendaciones:

- Usar HTTPS obligatorio.
- Rotar claves y credenciales periodicamente.
- No permitir escritura en `public/` salvo assets controlados.
- Revisar `storage/logs/security-*.log` con alertas operativas.
- Hacer backups diarios y probar restauraciones.
- Ejecutar actualizaciones de Composer y npm en una rama de pruebas antes de produccion.

## Rendimiento

- Usar `config:cache`, `route:cache`, `view:cache` y `event:cache`.
- Mantener paginacion en listados grandes.
- Usar colas para correos, PDFs, importaciones y reportes pesados.
- Mantener indices sobre filtros frecuentes: DNI, ciclo academico, fecha de registro, relaciones y estados.
- Evitar exportar reportes sin limites; usar filtros por fecha/ciclo y procesos en cola si crecen.
- Comprimir imagenes publicas antes de subirlas.
- Revisar consultas lentas desde el motor de base de datos en produccion.

## Backups

Programa backups fuera de la carpeta publica:

```bash
mysqldump -u usuario -p centropre | gzip > /backups/centropre-$(date +%F).sql.gz
tar -czf /backups/centropre-storage-$(date +%F).tar.gz storage/app
```

Conserva varias copias y prueba restauracion en un entorno alterno.

## Usuarios Por Defecto

- Usuario: `superadmin`
- Clave: valor de `SUPERADMIN_INITIAL_PASSWORD`

Si no defines la variable, el seeder usa una clave de desarrollo. En produccion debes definirla antes del despliegue y cambiarla al iniciar sesion.

## Pruebas

```bash
php artisan test
```

Las pruebas cubren registro de alumnos por ciclo, roles de staff, reportes de actividad y endurecimiento de seguridad. Antes de publicar, verificar manualmente:

- Login y logout.
- Wizard publico de inscripcion.
- Validaciones de formularios.
- Permisos por rol.
- Reportes PDF/Excel.
- Importacion de archivos academicos.
- Responsive en movil y escritorio.
- Logs de `storage/logs`.

## Errores Comunes

- `APP_KEY` vacio: ejecutar `php artisan key:generate`.
- Cambios de `.env` no aplican: ejecutar `php artisan config:clear` y volver a cachear.
- Rutas antiguas: ejecutar `php artisan route:clear`.
- Vistas desactualizadas: ejecutar `php artisan view:clear`.
- Colas sin procesar: iniciar `php artisan queue:work` o revisar Supervisor.
- Error 419: revisar CSRF, dominio, HTTPS y configuracion de cookies.
- Error de permisos en storage: asignar escritura a `storage/` y `bootstrap/cache/`.
- Correos no salen: validar SMTP, `MAIL_FROM_ADDRESS`, credenciales y logs `student-mail`.

## Licencia

Proyecto institucional CPU UNPRG. Las dependencias mantienen sus licencias originales.
