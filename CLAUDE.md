# CLAUDE.md - AI Assistant Guide for SubdiPass Project

## 🚨 CRITICAL: START OF EVERY SESSION

**ALWAYS execute these commands at the beginning of each new conversation:**

```bash
# 1. Read the planning document
cat PLANNING.md

# 2. Check current tasks
cat TASKS.md

# 3. Review recent changes (if exists)
cat CHANGELOG.md 2>/dev/null || echo "No changelog found"

# 4. Check project structure
ls -la
```

## 📋 PROJECT OVERVIEW

**Project Name:** SubdiPass - Digital Pass Management System  
**Purpose:** Web-based PWA for managing temporary digital passes in residential subdivisions using QR codes instead of traditional stickers for non-residents.

### Key Features
- Multi-subdivision support
- Dynamic pass types (job order, visitor, delivery, event)
- QR code generation and scanning
- Manual PIN fallback
- Comprehensive audit logging
- Progressive Web App (PWA) optimized for mobile

## 🛠 TECHNOLOGY STACK

```yaml
Backend:
  Framework: Laravel 11.x
  Language: PHP 8.2+
  API: RESTful with Laravel Sanctum
  
Database:
  Primary: MySQL 8.0+
  Cache: Redis
  Queue: Laravel Queue with Redis
  
Frontend:
  Framework: Vue.js 3.x with Inertia.js
  PWA: Workbox, Service Workers
  UI: Tailwind CSS
  
QR System:
  Generation: Endroid QR Code 5.x
  Scanning: ZXing-js (browser-based)
  
Development:
  Local: Laragon
  Version Control: Git
  Testing: PHPUnit, Pest
  
Hosting:
  Provider: Hostinger
  Environments: Development, Staging, Production
  Domain: *.subdivipass.com
```

## 👥 USER ROLES & PERMISSIONS

| Role | Key Permissions | Scope |
|------|----------------|-------|
| **Super Admin** | Full system access, all subdivisions | Global |
| **Admin/PM** | Manage assigned subdivisions | Subdivision-specific |
| **Employee** | Create passes, basic reporting | Assigned subdivisions |
| **Guard** | Scan/validate, report issues, manual entry | Assigned gates |
| **Requester** | Request passes, view history | Self-service |

## 📁 PROJECT STRUCTURE

```
subdivipass/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/           # API controllers
│   │   │   ├── Admin/         # Admin panel controllers
│   │   │   ├── Guard/         # Guard-specific controllers
│   │   │   └── Guest/         # Public controllers
│   │   ├── Middleware/
│   │   └── Requests/          # Form requests
│   ├── Models/
│   ├── Services/
│   │   ├── PassService.php
│   │   ├── QRService.php
│   │   └── ValidationService.php
│   └── Repositories/
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── js/
│   │   ├── Components/
│   │   ├── Pages/
│   │   └── app.js
│   └── views/
├── routes/
│   ├── api.php
│   ├── web.php
│   └── guard.php              # Guard-specific routes
├── tests/
│   ├── Feature/
│   └── Unit/
├── PLANNING.md               # Project planning document
├── TASKS.md                  # Current task list
├── CHANGELOG.md              # Change history
└── CLAUDE.md                 # This file
```

## 🗄 DATABASE SCHEMA

### Core Tables

```sql
-- Primary entities
subdivisions (id, name, address, settings, status, created_at, updated_at)
gates (id, subdivision_id, name, location, status, created_at, updated_at)
users (id, name, email, password, role, subdivision_ids, created_at, updated_at)
pass_types (id, name, config_json, subdivision_id, created_at, updated_at)

-- Pass management
passes (
    id, type_id, requester_id, approver_id, 
    qr_code, pin, visitor_details,
    valid_from, valid_to, status,
    created_at, updated_at
)

-- Tracking
pass_scans (id, pass_id, gate_id, guard_id, scan_type, timestamp)
pass_logs (id, pass_id, action, user_id, gate_id, details, timestamp)
audit_logs (id, table, action, user_id, changes_json, ip_address, timestamp)
```

## 🔐 SECURITY REQUIREMENTS

### Implementation Checklist
- [ ] Laravel Sanctum for API authentication
- [ ] 2FA for Admin roles (TOTP)
- [ ] HMAC-SHA256 signature for QR codes
- [ ] AES-256 encryption for sensitive data
- [ ] Rate limiting on all endpoints
- [ ] CSRF protection enabled
- [ ] XSS protection headers
- [ ] SQL injection prevention (use Eloquent)
- [ ] Input validation on all forms
- [ ] Session timeout (30 minutes)

### QR Code Security Structure
```php
[
    'pass_id' => 'UUID',
    'subdivision_id' => 'SUB001',
    'type' => 'visitor',
    'valid_from' => '2024-11-09T08:00:00',
    'valid_to' => '2024-11-09T18:00:00',
    'signature' => 'HMAC-SHA256-hash',
    'pin' => '6-digit-backup'
]
```

## 📱 PWA REQUIREMENTS

### Guard Mobile Interface
- Full-screen QR scanner
- Offline capability
- Manual PIN entry
- Recent scans history
- Issue reporting
- Torch/flashlight control
- Network status indicator

### Service Worker Implementation
```javascript
// Key features to implement
- Offline pass validation (cache last 100 passes)
- Background sync for offline scans
- Push notifications for urgent updates
- App shell caching
- Dynamic content caching
```

## ✅ TASK MANAGEMENT PROTOCOL

### Before Starting Work
1. **Always** read PLANNING.md first
2. Check TASKS.md for current priorities
3. Look for tasks marked as `[IN PROGRESS]` by others
4. Choose tasks marked as `[TODO]` or `[PRIORITY]`

### During Work
```markdown
# Update TASKS.md immediately when:
1. Starting a task: Change [TODO] to [IN PROGRESS]
2. Completing a task: Change to [COMPLETED] with date
3. Finding new tasks: Add under "Newly Discovered Tasks"
4. Encountering blockers: Add [BLOCKED] with reason
```

### After Completing Work
1. Mark task as `[COMPLETED]` in TASKS.md
2. Update CHANGELOG.md with changes
3. Document any new dependencies
4. Add discovered tasks to TASKS.md
5. Commit with descriptive message

## 🧪 TESTING REQUIREMENTS

### Test Coverage Targets
- Unit Tests: 80% minimum
- Feature Tests: All critical paths
- API Tests: All endpoints
- Browser Tests: Guard scanning flow

### Testing Commands
```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage

# Run specific test file
php artisan test tests/Feature/PassValidationTest.php
```

## 🚀 DEPLOYMENT CHECKLIST

### Pre-deployment
- [ ] All tests passing
- [ ] Code review completed
- [ ] Database migrations ready
- [ ] Environment variables set
- [ ] Backup current production

### Deployment Commands
```bash
php artisan down --render="503"
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
npm run build
php artisan up
```

## 🎯 CODING STANDARDS

### Laravel Best Practices
1. Use Repository Pattern for data access
2. Implement Service Layer for business logic
3. Use Form Requests for validation
4. Follow PSR-12 coding standards
5. Use Laravel Collections over raw arrays
6. Implement proper error handling
7. Use database transactions for critical operations

### Naming Conventions
```php
// Models: Singular, PascalCase
class Pass extends Model

// Controllers: Plural, PascalCase with Controller suffix
class PassesController extends Controller

// Tables: Plural, snake_case
Schema::create('passes', ...)

// Methods: camelCase
public function validatePass($passId)

// Variables: camelCase
$passData = [];

// Constants: UPPER_SNAKE_CASE
const MAX_PIN_ATTEMPTS = 3;
```

## 🐛 DEBUGGING TIPS

### Common Issues & Solutions
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Regenerate autoload
composer dump-autoload

# Check Laravel logs
tail -f storage/logs/laravel.log

# Debug database queries
DB::enableQueryLog();
// ... run queries
dd(DB::getQueryLog());
```

## 📊 PERFORMANCE TARGETS

| Metric | Target | Critical |
|--------|--------|----------|
| Page Load | < 2s | < 3s |
| API Response | < 200ms | < 500ms |
| QR Scan | < 500ms | < 1s |
| Database Query | < 50ms | < 100ms |
| PWA Cache Size | < 50MB | < 100MB |

## 🔄 WORKFLOW STATES

### Pass Status Flow
```
DRAFT → PENDING → APPROVED → ACTIVE → EXPIRED
         ↓          ↓
      REJECTED   REVOKED
```

### Task Priority Levels
1. `[CRITICAL]` - Security issues, system down
2. `[HIGH]` - Core functionality broken
3. `[MEDIUM]` - Important features
4. `[LOW]` - Nice to have, optimizations

## 📝 DOCUMENTATION TO MAINTAIN

Always update these files when making changes:
- `PLANNING.md` - Strategic changes
- `TASKS.md` - Task status
- `CHANGELOG.md` - Completed work
- `README.md` - Setup/installation changes
- API documentation (if endpoint changes)
- Database migration files

## 🆘 QUICK REFERENCES

### Essential Laravel Commands
```bash
# Create new controller
php artisan make:controller Admin/PassController --resource

# Create model with migration
php artisan make:model Pass -m

# Create service class
php artisan make:class Services/PassService

# Create form request
php artisan make:request StorePassRequest

# Create seeder
php artisan make:seeder PassTypeSeeder
```

### Git Workflow
```bash
# Start new feature
git checkout -b feature/pass-validation

# Commit with conventional commits
git commit -m "feat: add manual PIN validation for guards"
git commit -m "fix: resolve QR scanning timeout issue"
git commit -m "docs: update API documentation for pass endpoints"

# Push and create PR
git push origin feature/pass-validation
```

## ⚠️ IMPORTANT REMINDERS

1. **ALWAYS** read PLANNING.md and TASKS.md first
2. **NEVER** commit sensitive data (API keys, passwords)
3. **ALWAYS** write tests for new features
4. **UPDATE** documentation immediately
5. **MARK** tasks as completed in TASKS.md
6. **ADD** newly discovered tasks to TASKS.md
7. **FOLLOW** Laravel best practices
8. **MAINTAIN** backwards compatibility
9. **CONSIDER** mobile-first for guard interfaces
10. **IMPLEMENT** proper error handling

## 🎯 FOCUS AREAS FOR CURRENT PHASE

### Phase 1: MVP (Current)
- Core user management
- Basic pass creation/validation  
- QR scanning functionality
- Simple reporting

### Phase 2: Enhancement
- Multiple subdivision support
- Advanced pass types
- Comprehensive audit logs
- PWA optimization

### Phase 3: Future
- SMS/Email notifications
- Offline mode with sync
- Resident portal
- API integrations

---

**Remember:** This project is modular and scalable. Always consider future enhancements when implementing current features. Keep code clean, documented, and testable.

**Last Updated:** November 2024  
**Version:** 1.0.0