# Changelog

All notable changes to the SubdiPass project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.8.0] - 2024-11-10

### Added
- **Pass Type Management System - Complete CRUD Implementation**
  - **PassTypePolicy:** Comprehensive authorization with subdivision-scoped access
    - `before()` method grants super-admin full access
    - viewAny, view, create, update, delete methods with permission checks
    - Subdivision-based filtering: Admin/Employee can only manage their subdivision's pass types
    - changeStatus() method for activating/deactivating pass types
    - forceDelete() restricted to super-admin only
    - Prevents deletion of pass types with active passes

  - **PassTypeController:** Full CRUD with 9 controller methods
    - index() - List pass types with filtering (subdivision, status, approval, search)
    - create() - Pass type creation form with subdivision selection
    - store() - Create pass type with validation and activity logging
    - show() - View pass type details with recent passes
    - edit() - Edit pass type form
    - update() - Update pass type configuration
    - destroy() - Delete pass type (checks for active passes first)
    - changeStatus() - Toggle active/inactive status
    - updateOrder() - Reorder pass types by sort_order
    - Eager loading of subdivision relationship
    - Activity logging for all operations

  - **Form Request Validators:**
    - StorePassTypeRequest: Create validation with 14 validation rules
      - Subdivision existence validation
      - Name and slug validation (slug auto-generated from name)
      - Slug uniqueness and format (lowercase, numbers, dashes only)
      - Color hex code validation (#RRGGBB format)
      - Validity hours validation (1-8760 hours / 1 year max)
      - Max validity must be >= default validity
      - Config array validation for custom fields
      - Auto-set defaults: color (#3B82F6), requires_approval (false), is_active (true)
    - UpdatePassTypeRequest: Update validation with unique slug ignore
      - Same validation rules as create
      - Slug uniqueness ignores current pass type ID
      - All fields optional (use "sometimes" rule)

  - **Routes:** 9 pass type management routes (admin/super-admin only)
    - RESTful resource routes: index, create, store, show, edit, update, destroy
    - POST /pass-types/{pass_type}/change-status - Toggle active status
    - POST /pass-types/update-order - Bulk update sort order
    - All routes protected with role:admin|super-admin middleware

  - **PassTypes/Index.vue:** Complete listing component
    - Responsive table layout with color-coded pass types
    - 4-column filter section: Search, Subdivision, Status, Approval
    - Debounced search (500ms delay)
    - Pass type display: name, slug, color indicator, subdivision
    - Validity hours display: default and max validity
    - Approval badge: Required (yellow) or Auto (green)
    - Status badge: Active (green) or Inactive (gray)
    - Action buttons: Edit, Toggle Status, Delete
    - Delete confirmation with active passes check
    - Pagination with query string preservation
    - Filter state preservation on navigation

  - **PassTypes/Create.vue:** Pass type creation form
    - 3-section form layout: Basic Info, Validity Settings, Approval & Status
    - Subdivision dropdown (active subdivisions only)
    - Name and slug fields (auto-slug generation from name)
    - Color picker with hex input (dual input: color picker + text)
    - Description textarea (1000 char limit)
    - Default validity hours (1-8760 range, default: 24)
    - Maximum validity hours (optional, must be >= default)
    - Requires approval checkbox
    - Is active checkbox (default: true)
    - Real-time slug generation from name
    - Inline validation error display
    - Cancel and Submit buttons with loading state

  - **PassTypes/Edit.vue:** Pass type edit form
    - Same layout and features as Create form
    - Pre-filled with existing pass type data
    - Slug validation with unique ignore
    - Update button instead of Create
    - Preserves scroll position on submit

### Changed
- **routes/web.php:** Added pass type routes in admin group
  - 9 new routes added after user management routes
  - All routes protected with role:admin|super-admin middleware
  - RESTful naming: pass-types.index, pass-types.create, etc.

### Technical Details
- **Policy Methods:** 7 authorization methods with subdivision-scoped access
- **Controller Methods:** 9 methods with authorization, validation, activity logging
- **Validators:** 2 form request classes with 14 validation rules each
- **Routes:** 9 protected routes with role-based middleware
- **Vue Components:** 3 full-featured components (Index, Create, Edit)
- **Security:** Activity logging, permission-based access, active passes check before deletion
- **Auto-generation:** Slug auto-generated from name in create form
- **Color Support:** Hex color codes for UI distinction
- **Validity Control:** Configurable default and max validity hours (1-8760)

### Features Implemented
- **Pass Type CRUD:** Full create, read, update, delete operations
- **Status Management:** Activate/deactivate pass types
- **Order Management:** Reorder pass types with sort_order
- **Filtering:** Search by name, slug, description; filter by subdivision, status, approval
- **Authorization:** Role and subdivision-based access control
- **Activity Logging:** All pass type operations logged
- **Validation:** Comprehensive validation for all fields
- **Active Passes Check:** Prevents deletion of pass types with active passes
- **Color Coding:** Visual distinction with custom color per pass type
- **Approval Workflow:** Configure whether pass type requires approval

### Database Schema
- **pass_types table:** subdivision_id, name, slug, description, config (JSON), default_validity_hours, max_validity_hours, requires_approval, color, icon, is_active, sort_order, timestamps, soft_deletes

### Next Steps
- Test pass type creation, editing, deletion in browser
- Verify subdivision-scoped access for admin users
- Test active passes check before deletion
- Add navigation link to Dashboard for Pass Types

## [0.7.0] - 2024-11-10

### Added
- **User Management System - Complete Backend Implementation**
  - **Database Migration:** Added `last_login_ip` column to users table
    - Fixes login authentication error
    - Tracks user IP addresses for security auditing

  - **UserPolicy:** Comprehensive authorization with role-based access control
    - `before()` method grants super-admin full access to all operations
    - viewAny, view, create, update, delete methods with permission checks
    - Special protection: Users cannot delete themselves
    - Special protection: Admins cannot edit/delete super-admin accounts
    - changeStatus() method for activating/deactivating users
    - Custom authorization logic for profile updates (users can edit own profile)

  - **UserController:** Full CRUD with 10 controller methods
    - index() - List users with filtering (role, status, subdivision, search)
    - create() - User creation form with roles, subdivisions, gates
    - store() - Create user with role assignment and activity logging
    - show() - View user details with subdivisions, gates, activity logs
    - edit() - Edit user form
    - update() - Update user with conditional password hashing and role sync
    - destroy() - Soft delete user
    - changeStatus() - Activate/deactivate/suspend users
    - resetPassword() - Admin password reset functionality
    - Eager loading of roles and permissions
    - Activity logging for all operations

  - **Form Request Validators:**
    - StoreUserRequest: Create validation with 12 validation rules
      - Email uniqueness check
      - Password confirmation (min 8 characters)
      - Role validation against Spatie roles table
      - Subdivision and gate existence validation
      - JSON encoding for array fields (subdivision_ids, gate_ids)
    - UpdateUserRequest: Update validation with unique email ignore
      - Optional password update
      - Same subdivision/gate validation as create
      - Authorization: users can update own profile or need permission

  - **Routes:** 9 user management routes (admin/super-admin only)
    - RESTful resource routes: index, create, store, show, edit, update, destroy
    - POST /users/{user}/change-status - Change user status
    - POST /users/{user}/reset-password - Admin password reset
    - All routes protected with role:admin|super-admin middleware

  - **Users/Index.vue:** Minimal test component for backend verification
    - Backend connectivity test display
    - Simple user listing table
    - Name, Email, Role badges, Status badges
    - Pagination information
    - Confirms data flow from controller to view

### Changed
- **app/Http/Kernel.php:** Registered Spatie Permission middleware
  - Added 'role' => RoleMiddleware::class
  - Added 'permission' => PermissionMiddleware::class
  - Added 'role_or_permission' => RoleOrPermissionMiddleware::class
  - Fixed namespace: Spatie\Permission\Middleware (singular, not Middlewares)

### Fixed
- **Login Issue:** Added missing `last_login_ip` column to users table
  - Created migration: 2025_11_09_213751_add_last_login_ip_to_users_table
  - Resolves "Column not found: 1054 Unknown column 'last_login_ip'" error
  - Also updated original migration for future reference
- **Role Assignment:** Fixed super-admin user missing role assignment
  - Manually assigned 'super-admin' role to admin@subdivipass.com
  - Users now properly authenticated with correct role
- **403 Authorization Error:** Super-admin now has full access via UserPolicy before() method

### Technical Details
- **Models Enhanced:** User model with Spatie traits (HasRoles, LogsActivity)
- **Policy Methods:** 8 authorization methods with granular permission checks
- **Controller Methods:** 10 methods with authorization, validation, activity logging
- **Validators:** 2 form request classes with 12+ validation rules each
- **Routes:** 9 protected routes with role-based middleware
- **Middleware:** 3 Spatie Permission middleware registered globally
- **Security:** Soft deletes, activity logging, password hashing, permission-based access

### Features Implemented
- **User CRUD:** Full create, read, update, delete operations
- **Role Management:** Assign and sync user roles
- **Status Management:** Activate, deactivate, suspend users
- **Password Management:** Admin can reset user passwords
- **Filtering:** Search by name, email, phone; filter by role, status, subdivision
- **Authorization:** Role-based access control with policy protection
- **Activity Logging:** All user operations logged with Spatie Activity Log
- **Subdivision Scoping:** Users can be assigned to specific subdivisions
- **Gate Assignment:** Guards can be assigned to specific gates
- **Pagination:** 15 users per page with query string preservation

### Security Features
- ✅ Super-admin bypass via Policy before() method
- ✅ Users cannot delete themselves
- ✅ Admins cannot modify super-admin accounts
- ✅ Permission checks on all operations
- ✅ Password confirmation required
- ✅ Activity logging for audit trail
- ✅ Soft deletes for data retention
- ✅ IP address tracking on login

## [0.6.0] - 2024-11-10

### Added
- **Pass Management UI - Complete Vue.js Implementation**
  - **Passes/Index.vue:** Comprehensive pass listing interface
    - Advanced filtering system (search, status, subdivision, pass type)
    - Active filter chips with individual and bulk clear options
    - Debounced search input for better UX
    - Responsive data table with pagination
    - Status badges with color-coded indicators
    - Action buttons (View, Edit, Approve, Reject, Revoke, Download QR)
    - Role-based action visibility (admin/super-admin for approve/reject)
    - Modal dialogs for reject and revoke operations with reason input
    - Empty state with helpful messaging
    - 15 passes per page with query string preservation

  - **Passes/Create.vue:** Pass creation form with dynamic fields
    - Multi-step form layout with organized sections
    - Subdivision and pass type cascading selection
    - Visitor information form (name, contact, email, company)
    - Vehicle information (optional plate and model)
    - Visit details (purpose, destination, notes)
    - Validity period with datetime-local inputs
    - Approval requirement notice for pass types needing approval
    - Default validity hints from pass type configuration
    - Form validation with inline error messages
    - Loading states during submission
    - Professional Tailwind CSS styling

  - **Passes/Show.vue:** Detailed pass view with comprehensive information
    - Three-column responsive layout (QR/PIN sidebar + details)
    - Large QR code display with download capability
    - Prominent 6-digit PIN display with monospace font
    - Action sidebar with context-aware buttons
    - Pass information section (number, type, subdivision, status, validity)
    - Visitor information display
    - Conditional vehicle information section
    - Visit details (purpose, destination, notes)
    - Approval information (requester, approver, dates, rejection reason)
    - Scan history with timeline visualization
    - Entry/Exit scan type indicators
    - Statistics cards (scan count, last scanned, created date)
    - Modal dialogs for approve, reject, and revoke actions
    - Role-based action permissions

  - **Passes/Edit.vue:** Pass editing form with change tracking
    - Pre-populated form fields from existing pass data
    - Read-only subdivision and pass type fields (cannot be changed)
    - Info banner explaining edit restrictions
    - All editable fields: visitor info, vehicle info, visit details, validity
    - Validity change detection with QR regeneration notice
    - Status badge display in header
    - Form validation and error handling
    - Consistent styling with Create form
    - PUT request to update endpoint

### Features Implemented
- **Filtering & Search:**
  - Real-time search across pass number, visitor name, contact, and PIN
  - Status filter (draft, pending, approved, active, expired, revoked, rejected)
  - Subdivision filter with user scope enforcement
  - Pass type filter
  - Active filter display with clear options
  - Debounced search (500ms) for reduced API calls

- **Role-Based Access Control:**
  - Admin/Super-admin exclusive: Approve and Reject actions
  - All roles with edit permission: Edit passes (draft/pending only)
  - All roles with revoke permission: Revoke passes (approved/active only)
  - View and Download QR: Available to all with view permission

- **User Experience:**
  - Professional, consistent Tailwind CSS styling across all components
  - Responsive design (mobile, tablet, desktop)
  - Loading states during async operations
  - Inline validation error messages
  - Empty states with helpful calls-to-action
  - Modal confirmations for destructive actions
  - Success/error flash messages
  - Preserve scroll position on filter changes
  - Query string preservation for pagination and filters

- **Data Presentation:**
  - Color-coded status badges (7 states with unique colors)
  - Formatted dates and times (MMM DD, YYYY HH:MM format)
  - Scan history timeline with entry/exit indicators
  - Statistics visualization with colored cards
  - Conditional section rendering (only show if data exists)

### Technical Details
- **Components:** 4 Vue.js components (Index, Create, Show, Edit)
- **Total Lines:** ~1,800 lines of Vue SFC code
- **Inertia.js Integration:** Full integration with server-side rendering
- **Form Management:** useForm composable with error handling
- **Router:** Inertia router with preserveState and preserveScroll
- **State Management:** Reactive refs and computed properties
- **Icons:** SVG icons from Heroicons library
- **Date Formatting:** Client-side formatting with JavaScript Date API
- **Modals:** Custom modal implementations with backdrop and escape handling

### UI/UX Highlights
- **Index Page:** Professional data table with comprehensive filtering
- **Create Page:** Clean multi-section form with helpful hints
- **Show Page:** Information-dense detail view with prominent QR code
- **Edit Page:** Smart form with change detection and restrictions
- **Consistency:** Unified design language across all components
- **Accessibility:** Semantic HTML and ARIA attributes
- **Performance:** Debounced search and optimized re-renders

## [0.5.0] - 2024-11-09

### Added
- **Core Pass Management System - Complete Implementation**
  - **Eloquent Models (4 models):**
    - Subdivision model with relationships (gates, passTypes, passes, users)
    - Gate model with entry/exit validation and guard assignments
    - PassType model with configurable fields and validation rules
    - Pass model with auto-generation (UUID, PIN, pass number), full workflow methods

  - **Service Layer:**
    - QRService: QR code generation with HMAC-SHA256 signatures
      - 400x400 PNG with high error correction
      - Secure signature verification
      - QR regeneration capability
    - PassService: Complete business logic for pass lifecycle
      - Create, update, approve, reject, revoke, extend operations
      - PIN and QR validation methods
      - Statistics generation
      - Transaction-safe operations with activity logging

  - **PassController:** Full CRUD with additional actions
    - index() - List passes with filtering (status, subdivision, type, search)
    - create() - Pass creation form with subdivisions/types
    - store() - Create pass with QR generation
    - show() - View pass details with QR code
    - edit() - Edit pass form
    - update() - Update pass (regenerates QR if validity changed)
    - destroy() - Soft delete pass
    - approve() - Approve pending passes (admin only)
    - reject() - Reject with reason (admin only)
    - revoke() - Revoke active passes
    - downloadQR() - Download QR code as PNG

  - **Form Request Validation:**
    - StorePassRequest: Create validation with permission check
    - UpdatePassRequest: Update validation with permission check

  - **Routes:** RESTful resource routes plus custom actions
    - GET/POST /passes - List and create passes
    - GET /passes/create - Creation form
    - GET/PUT/DELETE /passes/{pass} - View, update, delete
    - POST /passes/{pass}/approve - Approve (admin)
    - POST /passes/{pass}/reject - Reject (admin)
    - POST /passes/{pass}/revoke - Revoke
    - GET /passes/{pass}/qr-download - Download QR

### Features Implemented
- **Pass Number Auto-Generation:** SUB-YYYYMMDD-####  format
- **6-Digit PIN Generation:** Automatic secure PIN creation
- **UUID Generation:** Unique identifier per pass
- **QR Code Security:**
  - HMAC-SHA256 signed payload
  - Contains: pass_id, pass_number, subdivision, visitor_name, validity, PIN
  - Organized storage: qrcodes/{subdivision_id}/{uuid}_{timestamp}.png
  - Pass number label on QR image
- **Status Workflow:** draft → pending → approved → active → expired/rejected/revoked
- **Approval System:** Configurable per pass type
- **Auto-Activation:** Approved passes auto-activate if within validity period
- **Validity Management:** Calculated from pass type defaults or custom dates
- **Search & Filters:** By status, subdivision, type, pass number, visitor name, PIN
- **Permission-Based Access:** Role-based filtering and authorization
- **Activity Logging:** All pass actions logged via Spatie Activity Log
- **Subdivision Scoping:** Users see only their assigned subdivisions

### Technical Details
- **Models:** 4 core models with 20+ helper methods total
- **Services:** 2 service classes with 15+ business logic methods
- **Controller:** 12 controller methods with proper authorization
- **Validation:** 2 form request classes with detailed rules
- **Routes:** 11 pass management routes
- **Security:** Permission-based access, HMAC signatures, soft deletes
- **Performance:** Eager loading, query optimization, pagination (15/page)

### Database Relationships
- Pass belongsTo: Subdivision, PassType, User (requester), User (approver)
- Pass hasMany: PassScans, PassLogs
- Subdivision hasMany: Gates, PassTypes, Passes
- PassType hasMany: Passes
- Gate belongsTo: Subdivision

## [0.4.0] - 2024-11-09

### Added
- **Authentication System - Complete Implementation**
  - Implemented Laravel Sanctum configuration for API authentication
  - Created AuthController with login/logout/user endpoints
  - Created LoginRequest with:
    - Email and password validation
    - Rate limiting (5 attempts per throttle key)
    - Account status checking
    - Session regeneration on successful login
  - Built Login.vue page with:
    - Clean, professional design with gradient background
    - Form validation and error display
    - Remember me functionality
    - Test credentials display
    - Loading states during submission
  - Created Dashboard.vue with:
    - Navigation bar with logout
    - User information display
    - Role badges
    - Quick stats placeholders
  - Set up role-based routing:
    - Super Admin/Admin → /dashboard
    - Guard → /guard/scanner
    - Employee → /passes
    - Requester → /my-passes
  - Configured HandleInertiaRequests middleware to share:
    - Authenticated user data (id, name, email, roles, permissions)
    - Flash messages (success, error)

### Changed
- Updated web routes with authentication middleware
- Added guest middleware for login routes
- Configured auth middleware for protected routes
- Enhanced User model with last login tracking
- Updated Sanctum configuration for stateful domains

### Security
- Implemented rate limiting on login (5 attempts)
- Added CSRF protection on all forms
- Session regeneration on login
- Account status validation
- IP address tracking for login attempts

### Technical Details
- **Routes:** 4 authentication routes (login GET/POST, logout POST, dashboard GET)
- **Middleware:** guest, auth, role-based access control
- **Components:** 2 Vue pages (Login, Dashboard)
- **Form Validation:** Email format, required fields, rate limiting
- **Session Management:** 30-minute timeout, remember me support

## [0.3.0] - 2024-11-09

### Added
- **Database Seeders - Complete Implementation**
  - Created RolePermissionSeeder with 36 granular permissions and 5 role definitions
  - Created SubdivisionSeeder with 3 test subdivisions (Greenfield Heights, Sunset Village, Lakeside Residences)
  - Created GateSeeder with 8 gates across all subdivisions (entry, exit, both types)
  - Created PassTypeSeeder with 4 pass types per subdivision:
    - Visitor Pass (12-24 hours validity)
    - Job Order Pass (7-30 days validity, requires approval)
    - Delivery Pass (2-4 hours validity)
    - Event Pass (2-3 days validity, requires approval)
  - Created UserSeeder with 11 test users covering all roles:
    - 1 Super Admin with global access
    - 2 Admins (one per major subdivision)
    - 2 Employees for pass management
    - 3 Guards assigned to specific gates
    - 2 Requesters (residents)

- **User Model Enhancements**
  - Added HasRoles trait from Spatie Permission package
  - Added LogsActivity trait from Spatie Activity Log
  - Added SoftDeletes trait for data retention
  - Configured proper mass assignment fields
  - Added JSON casting for subdivision_ids and gate_ids
  - Added helper methods: isActive(), hasTwoFactorEnabled()
  - Added relationships: subdivisions(), gates()
  - Configured activity logging for audit trail

### Technical Details
- **Seeders:**
  - 36 permissions across 7 modules (User, Subdivision, Gate, Pass Type, Pass, Scanning, Reporting)
  - 5 roles with appropriate permission assignments
  - 3 subdivisions with unique settings and configurations
  - 8 gates with proper type classifications (entry, exit, both)
  - 12 pass types (4 per subdivision) with detailed configurations
  - 11 users with proper role assignments and subdivision/gate mapping
- **Test Credentials:**
  - Super Admin: admin@subdivipass.com / password
  - Admin: john.smith@greenfieldheights.com / password
  - Employee: anna.rodriguez@greenfieldheights.com / password
  - Guard: pedro.cruz@greenfieldheights.com / password
  - Requester: sofia.fernandez@gmail.com / password

### Changed
- Enhanced User model with Spatie traits and relationships
- Updated fillable fields to include all new user attributes
- Added proper casting for boolean and JSON fields

## [0.2.0] - 2024-11-09

### Added
- **Database Schema - Complete Implementation**
  - Created subdivisions table with settings, logo, and status management
  - Created gates table with location tracking and type classification
  - Extended users table with subdivision assignments, gate assignments, and 2FA support
  - Created pass_types table for configurable pass categories with approval workflows
  - Created passes table (main entity) with:
    - UUID and pass number for identification
    - Complete visitor information fields
    - QR code path and signature for security
    - 6-digit PIN for manual validation
    - Validity period tracking
    - Multiple status states (draft, pending, approved, active, expired, revoked, rejected)
    - Scan count and last scanned tracking
  - Created pass_scans table for detailed scan event tracking with offline support
  - Created pass_logs table for comprehensive audit trail
  - Integrated Spatie Permission package (roles & permissions tables)
  - Integrated Spatie Activity Log package (system-wide audit logging)

### Changed
- Updated TASKS.md to mark all database design tasks as completed
- All tables include soft deletes for data retention
- Comprehensive indexes added for optimal query performance
- Foreign key constraints with appropriate cascade rules

### Technical Details
- **Total Tables Created:** 15
- **Migration Status:** All migrations ran successfully
- **Indexes:** Performance-optimized with composite and single-column indexes
- **JSON Fields:** Used for flexible configuration and metadata
- **Enums:** Controlled state management for status fields
- **Soft Deletes:** Enabled on all core tables

## [0.1.0] - 2024-11-09

### Added
- **Project Foundation**
  - Initialized Laravel 10.49.1 project with PHP 8.1.10
  - Configured MySQL 8.0.43 database (subdivipass)
  - Configured Redis 5.0.14.1 for cache, queue, and sessions
  - Set up Git repository with initial commit

- **Backend Dependencies**
  - Laravel Sanctum (included in Laravel 10) - API authentication
  - Spatie Laravel Permission v6.23.0 - Role & permission management
  - Spatie Laravel Activity Log v4.10.2 - Audit logging
  - Endroid QR Code v5.1.0 - QR code generation
  - Predis v3.2.0 - Redis client
  - Inertia Laravel v2.0.10 - Server-side rendering alternative

- **Frontend Stack**
  - Vue.js 3.4 - Progressive JavaScript framework
  - Inertia.js - Modern monolith approach
  - Tailwind CSS 3.x - Utility-first CSS framework
  - Vite 5.4 - Fast build tool
  - @vitejs/plugin-vue v5.0.0 - Vue plugin for Vite (compatible with Node 18)
  - @zxing/browser & @zxing/library - QR code scanning

- **Configuration**
  - Configured Vite with Vue plugin and path aliases
  - Set up Tailwind CSS with proper content paths
  - Created Inertia root template (app.blade.php)
  - Added HandleInertiaRequests middleware to web middleware group
  - Set session lifetime to 30 minutes (security requirement)
  - Changed cache driver to Redis
  - Changed queue connection to Redis
  - Changed session driver to Redis

- **Documentation**
  - Created PLANNING.md - Comprehensive project planning document
  - Created CLAUDE.md - AI assistant guide with project overview
  - Created TASKS.md - Detailed task tracking (400+ tasks)
  - Created CHANGELOG.md - This file

- **Development**
  - Created Welcome.vue component to demonstrate setup
  - Configured web route for Inertia rendering
  - Successfully built production assets

### Changed
- Modified Laravel requirements from PHP 8.2+ to PHP 8.1 for compatibility
- Modified Laravel version from 11.x to 10.x for compatibility
- APP_NAME changed to "SubdiPass"
- APP_URL changed to "http://subdivipass.test"
- DB_DATABASE changed to "subdivipass"

### Technical Details
- **Environment:**
  - PHP 8.1.10
  - MySQL 8.0.43
  - Redis 5.0.14.1
  - Node.js 18.8.0
  - Composer 2.8.12
  - NPM 8.18.0

- **Build Status:**
  - ✓ All dependencies installed
  - ✓ Database created
  - ✓ Configuration cached
  - ✓ Assets compiled successfully
  - ✓ Git repository initialized

### Next Steps
- Design and implement database schema
- Set up authentication system
- Implement role & permission structure
- Create pass management system
- Build QR code generation & scanning features
- Develop guard mobile interface (PWA)

---

## Legend
- **Added** - New features
- **Changed** - Changes in existing functionality
- **Deprecated** - Soon-to-be removed features
- **Removed** - Removed features
- **Fixed** - Bug fixes
- **Security** - Security improvements

---

**Note:** This changelog will be updated with each significant change to the project.
