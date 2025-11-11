# SubdiPass - Project Planning Document

## Project Overview
**Name:** SubdiPass - Digital Pass Management System
**Version:** 1.0.0
**Start Date:** November 2024
**Target Launch:** 16 Weeks
**Status:** In Development

## Executive Summary
SubdiPass is a Progressive Web Application (PWA) designed to modernize temporary pass management for residential subdivisions. It replaces traditional physical stickers with secure QR code-based digital passes, providing a streamlined solution for managing visitors, service providers, delivery personnel, and event attendees.

## Problem Statement
Current subdivision pass management systems rely on physical stickers that are:
- Easily lost or damaged
- Time-consuming to produce and distribute
- Difficult to track and audit
- Susceptible to fraud and duplication
- Inconvenient for both guards and visitors

## Solution
A mobile-first PWA that enables:
- Instant digital pass generation with QR codes
- Real-time pass validation at gates
- Comprehensive audit trails
- Multi-subdivision support
- Offline functionality for guards
- Role-based access control

## Technology Stack

### Backend
- **Framework:** Laravel 10.x
- **Language:** PHP 8.1
- **Authentication:** Laravel Sanctum
- **Database:** MySQL 8.0+
- **Cache/Queue:** Redis 5.0+
- **QR Generation:** Endroid QR Code 5.x

### Frontend
- **Framework:** Vue.js 3.4
- **Stack:** Inertia.js (SSR Alternative)
- **UI Framework:** Tailwind CSS 3.x
- **Build Tool:** Vite
- **QR Scanner:** ZXing-js

### Infrastructure
- **Development:** Laragon (Windows)
- **Hosting:** Hostinger VPS
- **Version Control:** Git
- **Testing:** PHPUnit, Pest

## Core Features

### 1. User Management
- Multi-role system (Super Admin, Admin, Employee, Guard, Requester)
- Subdivision-specific assignments
- Activity tracking and audit logs
- 2FA for administrative roles

### 2. Pass Management
- Dynamic pass types (visitor, job order, delivery, event)
- Configurable validity periods
- Approval workflows
- Batch pass generation
- Pass templates

### 3. QR Code System
- Secure HMAC-SHA256 signed QR codes
- AES-256 encryption for sensitive data
- 6-digit PIN fallback
- QR regeneration capability
- Embedded subdivision/gate validation

### 4. Guard Mobile Interface
- Full-screen QR scanner
- Manual PIN entry
- Recent scan history
- Offline validation
- Issue reporting
- Torch/flashlight control

### 5. Reporting & Analytics
- Daily/weekly/monthly reports
- Gate activity tracking
- Visitor frequency analysis
- Guard performance metrics
- Export to PDF/Excel/CSV

### 6. Security
- End-to-end encryption
- Rate limiting
- CSRF protection
- XSS prevention
- SQL injection prevention
- Comprehensive audit logging

## Project Phases

### Phase 1: Foundation (Weeks 1-2) ✓
- Environment setup
- Laravel initialization
- Database design
- Core dependencies installation

### Phase 2: Authentication & Users (Weeks 2-3)
- User authentication system
- Role and permission management
- User CRUD operations
- 2FA implementation

### Phase 3: Core Functionality (Weeks 3-5)
- Pass type configuration
- Pass creation system
- QR code generation
- Subdivision management ✅ Admin CRUD with logo upload, guard policy settings, and stats (Nov 11, 2025)
- Gate configuration ✅ CRUD with subdivision assignment, coordinates, and status tracking (Nov 11, 2025)

### Phase 4: Guard Interface (Weeks 5-7)
- PWA setup *(completed: manifest, Workbox service worker, install prompt, push opt-in, and app shell caching now live)*
- QR scanner implementation ✅ Guard scanner page with ZXing camera capture + manual fallback
- Pass validation engine *(completed: QR decryption, signature verification, duplicate prevention, and validation attempts logging – Nov 11, 2025)*
- Offline functionality *(completed: guard queue + IndexedDB cache + offline fallback handled via Workbox)*
- Manual PIN validation *(completed: dedicated endpoint, rate limited retries, and audit logging – Nov 11, 2025)*

### Phase 5: Admin Dashboard (Weeks 7-9)
- Dashboard components
- Pass management interface
- Reporting system
- Analytics

### Phase 6: Testing & Security (Weeks 9-13)
- Unit testing
- Feature testing
- Integration testing
- Security hardening
- Performance optimization

### Phase 7: Documentation & Deployment (Weeks 14-16)
- Technical documentation
- User manuals
- Deployment preparation
- Soft launch
- Monitoring setup

## Database Schema Overview

### Core Entities
1. **users** - System users with roles
2. **subdivisions** - Residential communities
3. **gates** - Entry/exit points per subdivision
4. **pass_types** - Configurable pass categories
5. **passes** - Digital pass records
6. **pass_scans** - Validation history
7. **pass_logs** - Activity audit trail
8. **audit_logs** - System-wide audit trail

## Security Architecture

### QR Code Structure
```json
{
  "pass_id": "UUID",
  "subdivision_id": "SUB001",
  "type": "visitor",
  "valid_from": "2024-11-09T08:00:00Z",
  "valid_to": "2024-11-09T18:00:00Z",
  "signature": "HMAC-SHA256",
  "pin": "123456"
}
```

### Security Layers
1. **Authentication:** Laravel Sanctum tokens
2. **Authorization:** Spatie Permission package
3. **Encryption:** AES-256 for sensitive data
4. **Signing:** HMAC-SHA256 for QR codes
5. **Rate Limiting:** Configurable per endpoint
6. **Audit:** Spatie Activity Log

## Performance Targets

| Metric | Target | Critical Threshold |
|--------|--------|-------------------|
| Page Load | < 2s | < 3s |
| API Response | < 200ms | < 500ms |
| QR Scan | < 500ms | < 1s |
| Database Query | < 50ms | < 100ms |
| PWA Cache Size | < 50MB | < 100MB |

## Success Criteria

### Technical
- [ ] 80%+ code coverage
- [ ] All critical paths tested
- [ ] Security audit passed
- [ ] Performance targets met
- [ ] PWA fully functional offline

### Business
- [ ] Guards can scan passes < 1 second
- [ ] Admins can generate passes < 30 seconds
- [ ] System handles 1000+ daily passes
- [ ] 99.9% uptime achieved
- [ ] Zero security breaches

## Risk Management

### Technical Risks
1. **QR Scanner Compatibility** - Mitigation: Extensive device testing
2. **Offline Sync Conflicts** - Mitigation: Conflict resolution logic
3. **Performance at Scale** - Mitigation: Load testing and optimization
4. **Browser Limitations** - Mitigation: Progressive enhancement

### Business Risks
1. **User Adoption** - Mitigation: Training and support
2. **Guard Device Availability** - Mitigation: BYOD policy
3. **Internet Connectivity** - Mitigation: Offline mode
4. **Regulatory Compliance** - Mitigation: Legal review

## Future Enhancements (Phase 2)

1. **SMS/Email Notifications**
   - Pass approval/rejection alerts
   - Expiration reminders
   - Security alerts

2. **Resident Portal**
   - Self-service pass requests
   - Guest management
   - Pass history

3. **Advanced Analytics**
   - Predictive insights
   - Trend analysis
   - Anomaly detection

4. **Integrations**
   - CCTV systems
   - Access control systems
   - Property management software

5. **Mobile Apps**
   - Native iOS app
   - Native Android app
   - Enhanced offline capabilities

## Project Team

### Required Roles
- **Backend Developer** - Laravel, PHP, MySQL
- **Frontend Developer** - Vue.js, Inertia, Tailwind
- **UI/UX Designer** - Mobile-first design
- **QA Engineer** - Testing and quality assurance
- **DevOps Engineer** - Deployment and monitoring

### Current Status
- Development: In Progress
- Team Size: 1 (Full Stack)
- AI Assistant: Claude Code

## Communication Plan

### Documentation
- CLAUDE.md - AI assistant guide
- TASKS.md - Task tracking
- CHANGELOG.md - Change history
- README.md - Setup instructions

### Updates
- Daily task updates in TASKS.md
- Weekly progress reviews
- Milestone completion reports
- Issue tracking in Git

## Budget Considerations

### Development Environment
- Laragon: Free
- VS Code: Free
- Git: Free

### Hosting (Production)
- Hostinger VPS: $5-10/month
- Domain: $10-15/year
- SSL Certificate: Free (Let's Encrypt)

### Third-Party Services
- SMS Gateway: TBD (Phase 2)
- Email Service: TBD
- Monitoring: TBD

## Deployment Strategy

### Environments
1. **Development** - Local (Laragon)
2. **Staging** - Hostinger subdomain
3. **Production** - subdivipass.com

### Deployment Process
1. Code review and testing
2. Database backup
3. Maintenance mode
4. Deploy code
5. Run migrations
6. Clear caches
7. Health check
8. Exit maintenance mode

## Conclusion

SubdiPass represents a significant upgrade to traditional subdivision pass management. By leveraging modern web technologies and mobile-first design, it provides a secure, efficient, and user-friendly solution for all stakeholders.

The project follows Laravel best practices, implements comprehensive security measures, and maintains a clear development roadmap. With proper execution, SubdiPass will modernize pass management and improve security for residential subdivisions.

---

**Document Version:** 1.0
**Last Updated:** November 9, 2024
**Next Review:** Weekly during development
