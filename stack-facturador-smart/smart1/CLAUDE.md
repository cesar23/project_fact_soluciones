# CLAUDE.md

v7
This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Repository Overview

**Facturador Smart** is a multi-tenant electronic invoicing system built with Laravel 8 and Vue.js. This is a comprehensive billing platform designed primarily for the Peruvian market (SUNAT electronic invoicing), featuring modular architecture to support invoices, receipts, credit/debit notes, dispatch guides, quotations, POS, inventory management, and more.

## Technology Stack

- **Backend**: Laravel 8 (PHP 7.3+)
- **Frontend**: Vue.js 2.x with Element UI, Vue Router, Vuex
- **Database**: MariaDB 10.5.6
- **Cache**: Redis (Alpine)
- **Multi-tenancy**: hyn/multi-tenant package
- **Modules**: nwidart/laravel-modules for modular structure
- **PDF Generation**: dompdf, mpdf
- **Excel**: maatwebsite/excel
- **QR Codes**: endroid/qr-code
- **Payment Integration**: MercadoPago SDK
- **Containerization**: Docker with docker-compose

## Architecture

### Multi-Tenancy System

The application uses database-based multi-tenancy with the `hyn/multi-tenant` package:
- **System Database**: `tenancy` (stores tenants configuration and hosts)
- **Tenant Databases**: Each client gets their own database with prefix `tenancy_` (e.g., `tenancy_smart1`)
- Tenant isolation is handled automatically by the tenancy middleware
- Each tenant has isolated data for documents, customers, products, inventory, etc.

### Modular Architecture

The system is organized into **42+ independent modules** located in the `modules/` directory. Each module follows Laravel's standard structure with its own:
- Controllers (`Http/Controllers/`)
- Models (`Models/`)
- Views (`Resources/views/`)
- Routes (`Routes/web.php`, `Routes/api.php`)
- Assets (`Resources/assets/`)
- Configuration (`Config/`)
- Service Providers (`Providers/`)

#### Key Modules:
- **Document**: Core module for electronic invoices, receipts, credit/debit notes
- **Item**: Product and service management
- **Inventory**: Warehouse and stock management
- **Purchase**: Purchase orders and supplier management
- **Sale**: Sales management
- **Pos**: Point of sale system
- **Dashboard**: Main dashboard and statistics
- **Report**: Reporting system
- **Finance**: Financial management, payments, collections
- **Account**: Multi-company and user management
- **Dispatch**: Dispatch guides (guías de remisión)
- **Certificate**: Digital certificate management for electronic invoicing
- **Sire**: SUNAT integration for electronic records
- **Ecommerce**: E-commerce integration (WooCommerce)
- **MobileApp**: Mobile app API endpoints
- **Restaurant**: Restaurant-specific features
- **Hotel**: Hotel management
- **Production**: Manufacturing/production management
- **Payroll**: Payroll and employee management
- **WhatsAppApi**: WhatsApp integration

### Core Application Structure

- **`app/CoreFacturalo/`**: Core electronic invoicing logic
  - `Facturalo.php`: Main invoicing engine (23k lines)
  - `Core.php`: Core business logic
  - `Template.php`: PDF template generation
  - `WS/`: Web service integrations for SUNAT
  - `Services/`: Business services
  - `Helpers/`: Helper utilities

- **`app/Models/`**: Eloquent models for system-level entities
- **`app/Http/Controllers/Tenant/`**: Tenant-specific controllers
- **`app/Http/Controllers/System/`**: System-level controllers
- **`app/Services/`**: Application services
- **`app/Traits/`**: Reusable traits
- **`app/helper.php`**: Global helper functions (autoloaded)

### Frontend Architecture

- **Vue.js 2.x** with single-file components
- **Element UI** for component library
- **Vuex** for state management
- Assets compiled with **Laravel Mix** (webpack)
- Charts with **Chart.js** and **vue-chartjs**
- Real-time features with **Socket.IO**

## Common Development Commands

### Initial Setup

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install

# Generate application key
php artisan key:generate

# Run migrations and seed database
php artisan migrate:refresh --seed

# Link storage
php artisan storage:link
```

### Development Workflow

```bash
# Serve the application
php artisan serve

# Watch and compile assets
npm run watch

# Build assets for production
npm run prod

# Run tests
php artisan test
# or
vendor/bin/phpunit
```

### Database Commands

```bash
# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

# Refresh database with seed
php artisan migrate:refresh --seed

# Create new migration
php artisan make:migration create_table_name
```

### Module Commands

```bash
# List all modules
php artisan module:list

# Create new module
php artisan module:make ModuleName

# Generate module controller
php artisan module:make-controller ControllerName ModuleName

# Generate module model
php artisan module:make-model ModelName ModuleName

# Migrate module
php artisan module:migrate ModuleName

# Seed module
php artisan module:seed ModuleName
```

### Cache Commands

```bash
# Clear application cache
php artisan cache:clear

# Clear config cache
php artisan config:clear

# Cache configuration
php artisan config:cache

# Clear view cache
php artisan view:clear

# Optimize application
php artisan optimize
```

### Queue Commands

```bash
# Start queue worker
php artisan queue:work

# Process queue jobs
php artisan queue:listen

# List failed jobs
php artisan queue:failed
```

### Custom Artisan Commands

```bash
# Backup database
php artisan bk:bd

# Backup storage files
php artisan bk:files

# Backup from remote server
php artisan bk:remote

# Consult voided documents with SUNAT
php artisan consult:voided-documents

# Fill accounting ledger asynchronously
php artisan account_ledger:fill

# Update changelog
php artisan changelog:update
```

### Docker Environment

```bash
# Start Docker containers
docker-compose up -d

# Stop containers
docker-compose down

# View logs
docker-compose logs -f

# Execute commands in FPM container
docker-compose exec fpm1 php artisan migrate

# Access FPM container shell
docker-compose exec fpm1 bash

# Restart services
docker-compose restart
```

## Docker Services

The application runs in Docker with these services:
- **nginx1**: Nginx web server (rash07/nginx)
- **fpm1**: PHP-FPM 7.4 (rash07/php-fpm:7.4)
- **mariadb1**: MariaDB 10.5.6 database
- **redis1**: Redis cache (alpine)
- **scheduling1**: Laravel task scheduler (rash07/scheduling)
- **supervisor1**: Supervisor for queue workers (rash07/php7.4-supervisor)

All services connect to the `proxynet` external network and data is persisted in Docker volumes.

## Environment Configuration

Key environment variables (`.env`):
- `APP_URL_BASE`: Base domain for the application
- `DB_DATABASE`: System database name (default: `tenancy`)
- `PREFIX_DATABASE`: Tenant database prefix (default: `tenancy`)
- `TENANCY_DATABASE_AUTO_DELETE`: Auto-delete tenant databases
- `FORCE_HTTPS`: Force HTTPS connections
- `QUEUE_CONNECTION`: Queue driver (default: `database`)
- `ITEMS_PER_PAGE`: Pagination limit
- `SIGNATURE_NOTE_OSE`: OSE signature identifier
- `DOCUMENT_TYPE_03_FILTER`: Filter for receipt type documents

## Key Features & Business Logic

### Electronic Invoicing (SUNAT Integration)
- Full integration with Peru's SUNAT tax authority
- Electronic invoices (Facturas), receipts (Boletas)
- Credit and debit notes
- Voiding documents (Anulaciones)
- Daily summaries (Resúmenes)
- Dispatch guides (Guías de remisión)
- Electronic signatures with digital certificates
- XML generation and PDF templates

### Multi-Company Support
- Single installation supports multiple companies
- Each company can have multiple users
- Company-specific configurations and certificates
- Isolated data per tenant

### POS System
- Point of sale interface
- Receipt printing
- Cash register management
- Real-time inventory updates

### Inventory Management
- Multi-warehouse support
- Stock movements and transfers
- Low stock alerts
- Batch and serial number tracking

## Development Guidelines

### Adding New Modules

When creating new modules, follow the established pattern:
1. Use `php artisan module:make ModuleName`
2. Create `module.json` with proper configuration
3. Register routes in `Routes/web.php` and `Routes/api.php`
4. Create service provider in `Providers/`
5. Add models in `Models/` directory
6. Keep module-specific logic isolated

### Working with Multi-Tenancy

- Always use tenant-aware models for tenant-specific data
- System models are in `app/Models/System/`
- Tenant models are typically in `app/Models/Tenant/` or module models
- Test with multiple tenants to ensure isolation
- Use `Hyn\Tenancy\Contracts\CurrentHostname` to get current tenant

### Testing

- Unit tests go in `tests/Unit/`
- Feature tests go in `tests/Feature/`
- Module tests go in each module's `Tests/` directory
- Run tests with `php artisan test` or `vendor/bin/phpunit`

### Code Style

- Follow PSR-12 coding standards
- Use Laravel conventions (StudlyCase for classes, camelCase for methods)
- Keep controllers thin, move business logic to services
- Use Eloquent relationships instead of manual joins
- Document complex business logic

## Important Notes

- This is a production billing system handling legal documents; changes should be tested thoroughly
- Electronic invoicing requires valid digital certificates from SUNAT
- Backup databases regularly before migrations
- The `Facturalo.php` file contains core invoicing logic - modify with extreme care
- Multi-tenancy means database changes affect all tenants
- Queue workers must be running for asynchronous tasks (invoicing, emails)
- Supervisor configuration is in `supervisor.conf` for queue workers
