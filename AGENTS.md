# AGENTS.md - RNM Hardware Management System

Guidance for AI agents working on this Laravel hardware management & POS system.

## Project Overview

This is a **multi-module business management system** built with Laravel 13 (latest), featuring:
- **Point of Sale (POS)** system with session-based cart management
- **Inventory Management** with branch-specific stock tracking
- **Purchasing & Invoicing** for supplier orders
- **Audit Logs** for user activity and system events
- **Role-Based Access Control (RBAC)** using Spatie Permission package

**Database**: SQLite (single file: `database/database.sqlite`)  
**Frontend**: Blade templates + Tailwind CSS + Alpine.js  
**Test Framework**: Pest.php (v4)

---

## Architecture & Critical Data Flows

### Core Entity Relationships

```
Branch (store locations)
  └─ BranchInventory (per-branch stock levels)
      ├─ Product (hardware catalog)
      └─ Sales (point-of-sale transactions)
           └─ SaleItem (individual line items)

Product (shared catalog)
  ├─ BranchInventory (qty per branch)
  └─ SaleItem (sales references)

User
  ├─ Sales (cashier who made transaction)
  ├─ Roles (via Spatie\Permission)
  └─ Branch (assigned branch, if multi-branch user)

Invoice
  └─ Purchase (supplier orders)
```

### POS System (Session-Based, Not DB-Persistent)

**Cart Storage**: Session key `pos_cart` (array of `{product_id, quantity}`)

**Data Flow**:
1. **Product Search/Browse** (`Pos\ProductController::{search,browse}`) → Search active products, attach `available_quantity` from `BranchInventory` (branch-scoped when `branch_id` is provided)
2. **Cart Mutations** (`Pos\PosController`) → Add/update/remove items in session (no DB write until checkout)
3. **Checkout Prepare** (`Pos\CheckoutController::prepare`) → Hydrate cart with unit price and totals (preview)
4. **Checkout Finalize** (`Pos\CheckoutController::finalize`) → **Atomic transaction**:
   - Lock branch inventory rows (`lockForUpdate()`)
   - Validate stock availability
   - Create `Sale` record
   - Create `SaleItem` records (one per product)
   - Decrement `BranchInventory.quantity`
   - Clear session cart

**Key Detail**: Pricing uses `Product.capital` (cost) for sales price (no separate markup column on Product).

### Permission-Based Access Control

Routes use Spatie Permission middleware: `->middleware('permission:MODULE.ACTION')`

**Example Permissions**:
- `pos.access` → View POS page
- `sales.view-history` → View past transactions
- `inventory.update` → Perform stock-in/out
- `purchases.create` → Create new invoices
- `audit.user-activity.view` → View user logs

Permissions are seeded in `database/seeders/DatabaseSeeder.php` when running seeders (for example `php artisan migrate --seed`).

---

## Project-Specific Conventions

### Naming & Structure

| Aspect | Convention | Example |
|--------|-----------|---------|
| **Routes** | Slugified module names | `/pos`, `/purchasing`, `/inventory`, `/audit-logs` |
| **View Folders** | `resources/views/modules/{module_name}/**` | `modules/pos/new-sale.blade.php` |
| **API Routes** | Prefix `{module}/api` under auth middleware | `pos/api/cart/add`, `pos/api/products/search` |
| **Controllers** | Namespace `App\Http\Controllers\{Module}` | `Pos\ProductController`, `Pos\CheckoutController` |
| **Models** | Mixed fillable styles: PHP attributes and `$fillable` arrays | `User` uses `#[Fillable(...)]`; `Product` uses `protected $fillable = [...]` |

### Blade Component Patterns

**Sidebar System** (`resources/views/components/sidebar/`):
- `<x-sidebar.container>` — Wraps entire sidebar (handles mobile/desktop toggle via Alpine)
- `<x-sidebar.dropdown>` — Collapsible menu group (check with `@canany(['perm1', 'perm2'])`)
- `<x-sidebar.item>` — Individual menu link (use `request()->routeIs('pattern*')` for active state)

**Table Components** (`resources/views/components/table/`):
- `<x-table.sortable-header>` — Reusable sortable column header with toggle arrows. Props: `label`, `sortBy`, `sortDir`, `column`, `route`, `params` (array), `align` ('left'|'center'|'right'). **Example usage**:
  ```blade
  <x-table.sortable-header 
      label="Product Name"
      :sortBy="$sortBy"
      :sortDir="$sortDir"
      column="name"
      route="inventory.overview"
      :params="['search' => $search]"
  />
  ```
- `<x-table.pagination>` — Reusable pagination UI with results count and page links. Props: `paginator` (Paginator instance). Shows "Showing X to Y of Z results" with prev/next/page-number links.
- `<x-table.empty-state>` — Reusable empty table message. Props: `colspan` (int), `message` (string).

**Filter Components** (`resources/views/components/filters/`):
- `<x-filters.dropdown-filter>` — **Generic** reusable filter dropdown for any model/field combination. Props: `items` (Collection), `selected` (value), `route`, `params` (array), `label`, `filterName` (query param name), `valueField` (model field for option value, default 'id'), `displayField` (model field for display text, default 'name'), `minCount` (show dropdown if items >= minCount, default 2), `class` (extra CSS). Auto-submits on change. **Examples**:
  ```blade
  {{-- Branch filter --}}
  <x-filters.dropdown-filter
      :items="$branches"
      :selected="$filterBranchId"
      route="inventory.overview"
      :params="['search' => $search]"
      label="Filter by Branch"
      filterName="branch_id"
  />

  {{-- Supplier filter --}}
  <x-filters.dropdown-filter
      :items="$suppliers"
      :selected="$filterSupplierId"
      route="suppliers.index"
      label="Filter by Supplier"
      filterName="supplier_id"
      displayField="supplier_name"
  />

  {{-- Status filter (show even with 1 item) --}}
  <x-filters.dropdown-filter
      :items="$statuses"
      :selected="$filterStatus"
      route="purchases.index"
      label="Filter by Status"
      filterName="status"
      valueField="value"
      displayField="label"
      :minCount="1"
  />
  ```

- `<x-filters.branch-select>` — **Branch-specific** wrapper around `dropdown-filter` (convenience component). Props: `branches` (Collection), `selected` (branch_id), `route`, `params` (array to preserve during filter), `label`. **Example**:
  ```blade
  <x-filters.branch-select 
      :branches="$allBranches"
      :selected="$filterBranchId"
      route="inventory.overview"
      :params="['search' => $search, 'sort_by' => $sortBy]"
      label="Filter by Branch"
  />
  ```

**Layout**:
- `app.blade.php` — Main authenticated layout with sidebar, header, content area
- Uses Alpine.js state: `mobileOpen` (mobile menu toggle) and `sidebarOpen` (desktop sidebar width)
- CSRF token and Vite asset loading in head

### Model Conventions

**Casting & Relationships**:
```php
#[Fillable(['name', 'price'])]
protected $casts = ['price' => 'float', 'date' => 'datetime'];
public function items() { return $this->hasMany(Item::class); }
```

**Query Scopes**:
```php
public function scopeSearch(Builder $query, ?string $term) {
    // Case-insensitive LIKE search, handles null gracefully
}
```

**Table Name Override**:
```php
protected $table = 'branch_inventory'; // Explicit if not Model pluralized
```

### Testing & Development Commands

| Command | Purpose |
|---------|---------|
| `php artisan migrate` | Run all pending migrations (SQLite) |
| `php artisan migrate:fresh --seed` | Reset DB + re-run seeders (use carefully!) |
| `php artisan tinker` | Interactive shell for testing models/queries |
| `./vendor/bin/pest` | Run Pest tests (Unit + Feature) |
| `composer test` | Clear config and run Laravel test suite (`php artisan test`) |
| `./vendor/bin/pint` | Format code (PSR-12 Laravel style, 4-space indent) |
| `composer run dev` | Start app stack with `serve`, `queue:listen`, `pail`, and Vite via `concurrently` |
| `composer run setup` | Bootstrap app (`install`, `.env`, `key:generate`, `migrate --force`, `npm install`, `npm run build`) |
| `npm run dev` | Start Vite dev server (HMR for CSS/JS) |
| `npm run build` | Compile assets for production |

---

## Integration Points & External Dependencies

### Key Packages

| Package | Purpose | Note |
|---------|---------|------|
| `spatie/laravel-permission` | RBAC roles/permissions | Config: `config/permission.php`, migrations auto-created |
| `tailwindcss`, `@tailwindcss/forms` | UI styling | Configured in `tailwind.config.js` |
| `alpinejs` | Frontend interactivity | Loaded via Vite in layouts |
| `laravel-vite-plugin` | Asset bundling | Entry point: `resources/css/app.css`, `resources/js/app.js` |
| `laravel/pail` | Local log tailing in dev workflow | Used in `composer run dev` |
| `pestphp/pest`, `mockery` | Testing | Test suites in `tests/Unit` + `tests/Feature` |

### Middleware Stack

Registered in `bootstrap/app.php`:
```php
$middleware->alias([
    'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
    'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
    'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
]);
```

Usage: `->middleware('permission:pos.access')` or `->middleware('role:admin')`

### Database Configuration

- **Driver**: SQLite
- **Location**: `database/database.sqlite`
- **Migrations**: In `database/migrations/`, includes Laravel base migrations (`0001_*`) plus app domain migrations (`2026_03_28_*`)
- **Factories**: `UserFactory` and `ProductFactory` available for testing

---

## Critical Developer Workflows

### Adding a New Module (e.g., "Reporting")

1. **Routes** (`routes/web.php`):
   ```php
   Route::middleware('auth')->group(function () {
       Route::get('/reporting/dashboard', fn() => view('modules.reporting.dashboard'))
           ->middleware('permission:reporting.view')->name('reporting.dashboard');
   });
   ```

2. **View** (`resources/views/modules/reporting/dashboard.blade.php`):
   ```blade
   <x-app-layout>
       <x-slot name="header"><h2>...</h2></x-slot>
       {{-- Content --}}
   </x-app-layout>
   ```

3. **Sidebar** (`resources/views/components/sidebar/_content.blade.php`):
   ```blade
   @can('reporting.view')
   <x-sidebar.item href="{{ route('reporting.dashboard') }}" 
       :active="request()->routeIs('reporting.*')">
       Reporting
   </x-sidebar.item>
   @endcan
   ```

4. **Permissions**: Ensure permission exists in database (check `permissions` table).

### Modifying POS Checkout Logic

**File**: `app/Http/Controllers/Pos/CheckoutController.php::finalize()`

**Critical**: Always wrap DB changes in `DB::transaction()` to ensure stock decrements are atomic.

**Stock Validation Pattern**:
```php
$inventories = BranchInventory::where('branch_id', $data['branch_id'])
    ->whereIn('product_id', $productIds)
    ->lockForUpdate()  // Prevent race conditions
    ->get()->keyBy('product_id');

if (!$inv || $inv->quantity < $qty) {
    abort(422, 'Insufficient stock for product '.$pid);
}
```

### Adding Product Search Filters

**File**: `app/Models/Product.php::scopeSearch()`

Current: Simple LIKE on `name` and `unit`. Extend with:
```php
public function scopeActive(Builder $query) {
    return $query->where('status', 'active');
}
// Usage: Product::search($q)->active()->get();
```

---

## Refactoring & Reusability Patterns

### Table UI Components Strategy

Instead of duplicating sort/filter/pagination UI across views, use reusable Blade components:

**Sorting**: Use `<x-table.sortable-header>` for every sortable column. Benefits:
- Consistent sort arrow UI across all tables
- Single source of truth for sort URL generation
- Eliminates duplicated route parameter handling

**Pagination**: Use `<x-table.pagination>` instead of inline pagination logic. Ensures:
- Consistent "Showing X to Y" text across all list views
- Accessible markup (aria-labels, rel attributes)
- Unified styling for page links

**Empty States**: Use `<x-table.empty-state>` to standardize the "no records found" message.

**Filters**: Use `<x-filters.branch-select>` for admin branch filtering. Automatically:
- Hides if only 1 branch exists
- Preserves other query parameters during filter submission
- Auto-submits on selection

### When to Extract Components

Extract a Blade component when the UI pattern appears **2+ times** across different views. Current extractions:
- **Sortable headers** → Used in `pos.transactions`, `inventory.overview`
- **Filter dropdowns** → Generic `dropdown-filter` component for branches, suppliers, statuses, etc.
- **Pagination** → Used in all list views
- **Edit modal** → Used for suppliers, purchases, and other forms
- **Product search typeahead** → Used in POS and inventory

### Extending Table Components

To add new sortable columns to existing tables, simply add a new `<x-table.sortable-header>`:
```blade
<x-table.sortable-header 
    label="Status"
    :sortBy="$sortBy"
    :sortDir="$sortDir"
    column="status"
    route="current.route.name"
    :params="['search' => $search]"
/>
```

No need to modify controller or add new logic—columns are defined in the view.

---

## Common Gotchas & Edge Cases

1. **Cart Persistence**: POS cart lives in session, NOT database. Transactions lost on session expiry.
2. **Floating-Point Math**: All monetary/quantity fields cast to `float`. Avoid decimals in totals without rounding.
3. **Branch Scoping**: `BranchInventory` is branch-specific. Must always filter by `branch_id` in stock queries.
4. **Permissions Depend on Seeding**: RBAC routes will 403 when DB is migrated without seeding; run seeder and verify `permissions` table.
5. **Sale Item Schema Drift**: `Pos\CheckoutController::finalize()` writes `product_name`, `unit`, `unit_price`, and `cost`, but `sales_items` migration/model only include `sale_id`, `product_id`, `quantity`, `markup`, `subtotal`.
6. **Vite Hot Reload**: Only works in development. Run `npm run dev` to enable CSS/JS changes without rebuilds.
7. **Sortable Header Params**: When using `<x-table.sortable-header>`, always pass critical query params (e.g., `search`, `branch_id`) in the `:params` prop to preserve them across sort clicks. Otherwise, filters reset when user sorts.
8. **Filter Dropdown Visibility**: `<x-filters.dropdown-filter>` checks `$items->count() >= $minCount` before rendering (default minCount=2). Control visibility with the `minCount` prop—use `minCount="1"` to always show, even with a single item.
9. **Filter Field Mapping**: Use `valueField` and `displayField` props on `dropdown-filter` to map model attributes to option values/labels. For nested/non-standard fields, use `data_get()` helper in your controller or model accessor.

---

## Quick Reference: File Locations

| Need | File(s) |
|------|---------|
| Add a route | `routes/web.php` (authenticated) or `routes/auth.php` (guest) |
| Create a model | `app/Models/{ModelName}.php` |
| New controller | `app/Http/Controllers/{Module}/{ControllerName}.php` |
| New view | `resources/views/modules/{module}/{view}.blade.php` |
| Sidebar menu | `resources/views/components/sidebar/_content.blade.php` |
| Permissions config | `config/permission.php` |
| Database schema | `database/migrations/{date}_{description}.php` |
| Tests | `tests/Unit/` or `tests/Feature/` (Pest syntax) |

---

## Initial Setup Commands

```bash
# Fresh start
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run dev  # In another terminal

# Run locally
php artisan serve        # http://localhost:8000
npm run dev             # Vite HMR for assets
# Or use combined dev stack (serve + queue + pail + vite)
composer run dev

# Testing
./vendor/bin/pest
./vendor/bin/pint --test
```

---

**Generated**: April 2026  
**Last Updated**: While analyzing codebase at commit scope  
**Audience**: AI agents and collaborative developers

