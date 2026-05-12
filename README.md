# Sistema HHRR

Aplicación Laravel 12 / PHP 8.2 para la gestión administrativa de una
organización de salud conductual: pacientes, empleados, departamentos,
clínicas, pagadores, contratos, citas médicas, nómina, facturación y
planificación de rutas para visitas a domicilio.

## Stack

- Laravel 12 · PHP 8.2+
- SQLite single-file (`database/database.sqlite`)
- Spatie Permission para roles y permisos granular
- Blade · Alpine.js · Tailwind CSS
- Leaflet + OSRM para el planificador de rutas

## Setup inicial

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve --port=8001
```

Abrir http://127.0.0.1:8001/login.

## Cuentas demo

| Email                          | Contraseña    | Rol            |
|--------------------------------|---------------|----------------|
| `superadmin@tesis.local`       | `super123`    | Super Admin    |
| `admin@demo-bh.local`          | `admin123`    | Client Admin   |
| `hhrr-admin@demo-bh.local`     | `hhrr123`     | HHRR Admin     |
| `david.martinez@demo-bh.local` | `password123` | Therapist      |
| `jorge.perez@demo-bh.local`    | `password123` | Biller         |
| `ana.torres@demo-bh.local`     | `password123` | Receptionist   |

## Funcionalidades

- **Pacientes** — demografía, múltiples seguros por paciente, inscripción
  a clínicas, geolocalización para el planificador
- **Empleados** — datos RRHH, NPI lookup contra registro CMS NPPES,
  asignación a departamentos
- **Departamentos** — clasificación organizacional
- **Pagadores** — catálogo de aseguradoras con datalist Florida
- **Contratos** — vigencia con estados activo / por vencer / vencido
- **Clínicas** — sedes con coordenadas
- **Citas médicas** — calendario con tope de 20 pacientes/día por proveedor
- **Nómina** — bi-semanal o mensual derivada de citas completadas
- **Facturas** — líneas dinámicas, numeración automática, registro de pagos
- **Planificador de rutas** — split AM/PM, TSP nearest-neighbor, ruta real
  por carreteras vía OSRM, cálculo de combustible

## Re-sembrar datos demo

```bash
php artisan migrate:fresh --seed
```
