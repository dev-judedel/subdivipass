# Changelog

All notable changes to the SubdiPass project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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
