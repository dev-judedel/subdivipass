# SubdiPass Database Schema Documentation

**Version:** 1.0
**Last Updated:** November 9, 2024
**Status:** ✅ All Migrations Completed

## Overview

The SubdiPass database consists of 15 tables organized into three categories:
1. **Core Application Tables** (8 tables)
2. **Third-Party Integration Tables** (7 tables)

Total database size estimation: ~50-100MB for 10,000 active passes

---

## Core Application Tables

### 1. subdivisions
Stores residential subdivision/community information.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| name | VARCHAR(255) | Subdivision name |
| code | VARCHAR(255) UNIQUE | Unique code (e.g., SUB001) |
| address | TEXT NULL | Physical address |
| contact_person | VARCHAR(255) NULL | Contact person name |
| contact_email | VARCHAR(255) NULL | Contact email |
| contact_phone | VARCHAR(255) NULL | Contact phone |
| settings | JSON NULL | Subdivision-specific settings |
| logo_path | VARCHAR(255) NULL | Logo file path |
| status | ENUM | active, inactive, suspended |
| notes | TEXT NULL | Additional notes |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update timestamp |
| deleted_at | TIMESTAMP NULL | Soft delete timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE KEY (code)
- KEY (status)
- KEY (created_at)

---

### 2. gates
Entry/exit points for each subdivision.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| subdivision_id | BIGINT UNSIGNED | Foreign key to subdivisions |
| name | VARCHAR(255) | Gate name (e.g., "Main Gate") |
| code | VARCHAR(255) UNIQUE | Unique gate code (e.g., GATE001) |
| location | TEXT NULL | Physical location description |
| coordinates | JSON NULL | GPS coordinates |
| type | ENUM | entry, exit, both |
| status | ENUM | active, inactive, maintenance |
| notes | TEXT NULL | Additional notes |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update timestamp |
| deleted_at | TIMESTAMP NULL | Soft delete timestamp |

**Indexes:**
- PRIMARY KEY (id)
- FOREIGN KEY (subdivision_id) REFERENCES subdivisions(id) ON DELETE CASCADE
- UNIQUE KEY (code)
- KEY (subdivision_id)
- KEY (status)
- COMPOSITE KEY (subdivision_id, status)

---

### 3. users (extended)
System users with role and subdivision assignments.

**Default Laravel Fields:**
- id, name, email, email_verified_at, password, remember_token, created_at, updated_at

**Extended Fields:**

| Column | Type | Description |
|--------|------|-------------|
| phone | VARCHAR(255) NULL | Contact phone |
| subdivision_ids | JSON NULL | Array of assigned subdivision IDs |
| primary_subdivision_id | BIGINT UNSIGNED NULL | Primary subdivision assignment |
| gate_ids | JSON NULL | For guards - assigned gates |
| status | ENUM | active, inactive, suspended |
| last_login_at | TIMESTAMP NULL | Last login timestamp |
| avatar_path | VARCHAR(255) NULL | Profile picture path |
| two_factor_enabled | BOOLEAN | 2FA activation status |
| two_factor_secret | TEXT NULL | 2FA secret key |
| deleted_at | TIMESTAMP NULL | Soft delete timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE KEY (email)
- FOREIGN KEY (primary_subdivision_id) REFERENCES subdivisions(id) ON DELETE SET NULL
- KEY (status)
- KEY (primary_subdivision_id)
- KEY (last_login_at)

---

### 4. pass_types
Configurable pass categories (Visitor, Delivery, Job Order, Event, etc.).

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| subdivision_id | BIGINT UNSIGNED | Foreign key to subdivisions |
| name | VARCHAR(255) | Type name (e.g., "Visitor") |
| slug | VARCHAR(255) UNIQUE | URL-friendly identifier |
| description | TEXT NULL | Type description |
| config | JSON NULL | Configuration: fields, rules, workflow |
| default_validity_hours | INTEGER | Default validity in hours |
| max_validity_hours | INTEGER NULL | Maximum allowed validity |
| requires_approval | BOOLEAN | Approval workflow flag |
| color | VARCHAR(255) | UI color code (default: #3B82F6) |
| icon | VARCHAR(255) NULL | Icon class or path |
| is_active | BOOLEAN | Active status |
| sort_order | INTEGER | Display order |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update timestamp |
| deleted_at | TIMESTAMP NULL | Soft delete timestamp |

**Indexes:**
- PRIMARY KEY (id)
- FOREIGN KEY (subdivision_id) REFERENCES subdivisions(id) ON DELETE CASCADE
- UNIQUE KEY (slug)
- KEY (subdivision_id)
- KEY (is_active)
- COMPOSITE KEY (subdivision_id, is_active)

---

### 5. passes ⭐ (Main Entity)
Digital pass records with visitor information and validation data.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| uuid | UUID UNIQUE | UUID for QR code |
| pass_number | VARCHAR(255) UNIQUE | Human-readable number (PASS-2024-001) |
| subdivision_id | BIGINT UNSIGNED | Foreign key to subdivisions |
| pass_type_id | BIGINT UNSIGNED | Foreign key to pass_types |
| requester_id | BIGINT UNSIGNED | Foreign key to users (requester) |
| approver_id | BIGINT UNSIGNED NULL | Foreign key to users (approver) |
| **Visitor Information** | | |
| visitor_name | VARCHAR(255) | Visitor's name |
| visitor_phone | VARCHAR(255) NULL | Visitor's phone |
| visitor_email | VARCHAR(255) NULL | Visitor's email |
| visitor_id_type | VARCHAR(255) NULL | ID type (License, Passport, etc.) |
| visitor_id_number | VARCHAR(255) NULL | ID number |
| visitor_company | VARCHAR(255) NULL | Company name |
| visitor_address | TEXT NULL | Visitor's address |
| vehicle_plate_number | VARCHAR(255) NULL | Vehicle plate |
| vehicle_model | VARCHAR(255) NULL | Vehicle model |
| visitor_details | JSON NULL | Additional custom fields |
| **Pass Details** | | |
| purpose | VARCHAR(255) NULL | Purpose of visit |
| notes | TEXT NULL | Additional notes |
| qr_code_path | VARCHAR(255) NULL | Path to QR code image |
| qr_signature | VARCHAR(255) | HMAC-SHA256 signature |
| pin | VARCHAR(6) | 6-digit PIN for manual entry |
| **Validity** | | |
| valid_from | TIMESTAMP | Validity start |
| valid_to | TIMESTAMP | Validity end |
| approved_at | TIMESTAMP NULL | Approval timestamp |
| activated_at | TIMESTAMP NULL | First scan timestamp |
| expired_at | TIMESTAMP NULL | Expiration timestamp |
| revoked_at | TIMESTAMP NULL | Revocation timestamp |
| **Status** | | |
| status | ENUM | draft, pending, approved, active, expired, revoked, rejected |
| rejection_reason | TEXT NULL | Reason for rejection |
| revocation_reason | TEXT NULL | Reason for revocation |
| **Tracking** | | |
| scan_count | INTEGER | Number of times scanned |
| last_scanned_at | TIMESTAMP NULL | Last scan timestamp |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update timestamp |
| deleted_at | TIMESTAMP NULL | Soft delete timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE KEY (uuid)
- UNIQUE KEY (pass_number)
- FOREIGN KEY (subdivision_id) REFERENCES subdivisions(id) ON DELETE CASCADE
- FOREIGN KEY (pass_type_id) REFERENCES pass_types(id) ON DELETE RESTRICT
- FOREIGN KEY (requester_id) REFERENCES users(id) ON DELETE CASCADE
- FOREIGN KEY (approver_id) REFERENCES users(id) ON DELETE SET NULL
- Multiple single and composite indexes for performance

---

### 6. pass_scans
Detailed tracking of all scan events.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| pass_id | BIGINT UNSIGNED | Foreign key to passes |
| gate_id | BIGINT UNSIGNED | Foreign key to gates |
| guard_id | BIGINT UNSIGNED | Foreign key to users (guard) |
| scan_type | ENUM | entry, exit, validation |
| scan_method | ENUM | qr, pin, manual |
| result | ENUM | success, failed, warning |
| result_message | TEXT NULL | Result description |
| scan_data | JSON NULL | Additional scan metadata |
| device_id | VARCHAR(255) NULL | Device identifier |
| ip_address | VARCHAR(45) NULL | IP address |
| user_agent | TEXT NULL | Browser/device info |
| location | JSON NULL | GPS coordinates |
| was_offline | BOOLEAN | Offline scan flag |
| scanned_at | TIMESTAMP | Scan timestamp |
| synced_at | TIMESTAMP NULL | Sync timestamp (for offline) |

**Indexes:**
- PRIMARY KEY (id)
- FOREIGN KEY (pass_id) REFERENCES passes(id) ON DELETE CASCADE
- FOREIGN KEY (gate_id) REFERENCES gates(id) ON DELETE CASCADE
- FOREIGN KEY (guard_id) REFERENCES users(id) ON DELETE CASCADE
- Multiple indexes for query optimization

---

### 7. pass_logs
Audit trail for pass lifecycle events.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| pass_id | BIGINT UNSIGNED | Foreign key to passes |
| user_id | BIGINT UNSIGNED NULL | User who performed action |
| gate_id | BIGINT UNSIGNED NULL | Related gate |
| action | VARCHAR(255) | Action type (created, updated, approved, etc.) |
| description | TEXT NULL | Human-readable description |
| old_values | JSON NULL | Previous state |
| new_values | JSON NULL | New state |
| metadata | JSON NULL | Additional context |
| ip_address | VARCHAR(45) NULL | IP address |
| user_agent | TEXT NULL | Browser/device info |
| logged_at | TIMESTAMP | Log timestamp |

**Indexes:**
- PRIMARY KEY (id)
- FOREIGN KEY (pass_id) REFERENCES passes(id) ON DELETE CASCADE
- FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
- FOREIGN KEY (gate_id) REFERENCES gates(id) ON DELETE SET NULL
- Multiple indexes for performance

---

## Third-Party Integration Tables

### Spatie Permission (5 tables)
- `roles` - User roles
- `permissions` - System permissions
- `model_has_permissions` - Direct user permissions
- `model_has_roles` - User role assignments
- `role_has_permissions` - Role permission assignments

**User Roles:**
1. Super Admin - Full system access
2. Admin/PM - Manage assigned subdivisions
3. Employee - Create passes, basic reporting
4. Guard - Scan/validate passes
5. Requester - Request passes, view history

### Spatie Activity Log (3 tables)
- `activity_log` - Main activity log table
- Extensions for event column and batch UUID

---

## Relationships Diagram

```
subdivisions (1) ──┬─> (N) gates
                   ├─> (N) pass_types
                   ├─> (N) passes
                   └─> (N) users (via primary_subdivision_id)

pass_types (1) ───> (N) passes

users (1) ──┬─> (N) passes (as requester)
            ├─> (N) passes (as approver)
            ├─> (N) pass_scans (as guard)
            └─> (N) pass_logs (as user)

passes (1) ──┬─> (N) pass_scans
             └─> (N) pass_logs

gates (1) ──┬─> (N) pass_scans
            └─> (N) pass_logs
```

---

## Query Performance Considerations

### Most Common Queries
1. **Get active passes for subdivision** - Indexed on (subdivision_id, status)
2. **Get passes by date range** - Indexed on (valid_from, valid_to)
3. **Find pass by PIN** - Indexed on (pin)
4. **Find pass by QR UUID** - Unique index on (uuid)
5. **Get gate activity** - Indexed on (gate_id, scanned_at)
6. **Get guard performance** - Indexed on (guard_id, scanned_at)

### Optimization Strategies
- Use eager loading for relationships
- Cache frequently accessed data (pass types, subdivisions)
- Implement query result caching for reports
- Use database indexes effectively
- Monitor slow query log

---

## Data Retention Policy

| Table | Retention | Soft Delete |
|-------|-----------|-------------|
| subdivisions | Indefinite | Yes |
| gates | Indefinite | Yes |
| users | Indefinite | Yes |
| pass_types | Indefinite | Yes |
| passes | 2 years | Yes |
| pass_scans | 1 year | No |
| pass_logs | 2 years | No |
| activity_log | 1 year | No |

---

## Backup Strategy

**Recommended:**
- Daily automated backups at 2 AM
- Retention: 30 daily, 12 monthly, 7 yearly
- Test restoration monthly
- Store backups offsite

**Critical Data:**
- passes table (most important)
- users table
- subdivisions and gates
- audit logs (pass_logs, activity_log)

---

## Security Considerations

1. **Encryption:**
   - QR signatures use HMAC-SHA256
   - 2FA secrets encrypted at rest
   - Sensitive data in JSON fields encrypted

2. **Access Control:**
   - Row-level security via subdivision_id
   - Role-based permissions via Spatie
   - Audit trail for all actions

3. **Data Integrity:**
   - Foreign key constraints
   - Enum validation for status fields
   - Soft deletes for recovery

---

## Migration Commands

```bash
# Run all migrations
php artisan migrate

# Check migration status
php artisan migrate:status

# Rollback last batch
php artisan migrate:rollback

# Fresh migration (CAUTION: Deletes all data)
php artisan migrate:fresh

# Fresh migration with seeders
php artisan migrate:fresh --seed
```

---

**Document Version:** 1.0
**Database Version:** 0.2.0
**Maintained By:** SubdiPass Development Team
