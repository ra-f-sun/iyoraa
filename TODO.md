# IYORAA HMS - TODO & Action Items

**Last Updated:** January 17, 2026  
**Plugin Status:** MVP Phase 2 Complete (Patient Module) - 40% Overall Completion

---

## 🔴 CRITICAL - DO IMMEDIATELY

### Build & Deployment
- [x] Run `npm install` to install all Node dependencies ✅
- [x] Run `npm run build` to compile React app to assets/dist/ ✅
- [ ] Test plugin activation in WordPress (check for errors)
- [ ] Verify React app loads in wp-admin
- [ ] Test patient CRUD operations end-to-end

### Documentation
- [x] Create README.md with installation instructions ✅
- [x] Document build process and commands ✅
- [ ] Add development environment setup guide
- [ ] Create quick start guide for users

### Security Audit
- [x] Verify nonce checking in all REST API endpoints ✅ (WordPress handles automatically)
- [x] Test permission callbacks for all API routes ✅
- [x] Add rate limiting to prevent API abuse ✅
- [x] Review SQL queries for injection vulnerabilities ✅ (All use $wpdb->prepare)
- [ ] Add CSRF protection to forms (future enhancement)

---

## 🟡 HIGH PRIORITY - THIS WEEK

### Complete MVP Features

#### Appointments Module (Week 1-2)
- [ ] Create `inc/Core/AppointmentManager.php`
- [ ] Implement appointment CRUD operations
- [ ] Generate appointment IDs (APT-YYYY-####)
- [ ] Add doctor/staff assignment
- [ ] Create `inc/API/AppointmentAPI.php`
- [ ] Register REST endpoints (/appointments)
- [ ] Create React components:
  - [ ] AppointmentList.jsx
  - [ ] AppointmentForm.jsx
  - [ ] AppointmentCalendar.jsx
  - [ ] useAppointments.js hook
- [ ] Implement appointment status workflow (pending → confirmed → completed)
- [ ] Add appointment limits for FREE tier (50/month)

#### Billing/Invoice Module (Week 3)
- [ ] Create `inc/Core/InvoiceManager.php`
- [ ] Implement invoice generation
- [ ] Create `inc/Core/PaymentManager.php`
- [ ] Add payment recording
- [ ] Create `inc/API/InvoiceAPI.php`
- [ ] Register REST endpoints (/invoices, /payments)
- [ ] Create React components:
  - [ ] InvoiceList.jsx
  - [ ] InvoiceForm.jsx
  - [ ] InvoiceDetail.jsx
  - [ ] PaymentForm.jsx
  - [ ] useInvoices.js hook
- [ ] Add invoice PDF generation (basic)
- [ ] Implement payment methods (cash, card, insurance)

### Testing Foundation
- [ ] Setup PHPUnit configuration
- [ ] Write 5 critical unit tests:
  - [ ] PatientManager::create_patient()
  - [ ] PatientManager::generate_patient_id()
  - [ ] LicenseManager::has_feature()
  - [ ] Database::create_tables()
  - [ ] API permission callbacks
- [ ] Setup Jest for React testing
- [ ] Write 3 frontend tests:
  - [ ] usePatients hook
  - [ ] PatientForm validation
  - [ ] PatientList rendering
- [ ] Create test database seeder

---

## 🟢 MEDIUM PRIORITY - NEXT 2 WEEKS

### Code Quality Improvements

#### Refactor Singleton Pattern
- [ ] Create Service Container class
- [ ] Implement Dependency Injection
- [ ] Refactor PatientManager to use DI
- [ ] Refactor LicenseManager to use DI
- [ ] Update tests for new architecture

#### Add Type Safety
- [ ] Add PHP 7.4+ type declarations to all methods
- [ ] Create DTOs (Data Transfer Objects):
  - [ ] PatientDTO
  - [ ] AppointmentDTO
  - [ ] InvoiceDTO
- [ ] Add return type hints
- [ ] Add parameter type hints

#### Extract Validation Layer
- [ ] Create `inc/Validators/` directory
- [ ] Create `BaseValidator.php`
- [ ] Create `PatientValidator.php`
- [ ] Create `AppointmentValidator.php`
- [ ] Create `InvoiceValidator.php`
- [ ] Implement ValidationResult class
- [ ] Add custom validation rules

#### Repository Pattern
- [ ] Create `inc/Repositories/` directory
- [ ] Create `BaseRepository.php`
- [ ] Create `PatientRepository.php`
- [ ] Create `AppointmentRepository.php`
- [ ] Create `InvoiceRepository.php`
- [ ] Move all DB queries to repositories

### Frontend Improvements

#### State Management
- [ ] Create AppContext for global state
- [ ] Create TierContext for license info
- [ ] Create UserContext for current user
- [ ] Implement Context providers in App.js
- [ ] Refactor components to use contexts

#### UI/UX Enhancements
- [ ] Add loading skeletons
- [ ] Add error boundaries
- [ ] Implement toast notifications
- [ ] Add confirmation modals
- [ ] Improve form validation UX
- [ ] Add keyboard shortcuts
- [ ] Mobile responsive design

#### Performance
- [ ] Implement React.lazy() for code splitting
- [ ] Add memoization with React.memo()
- [ ] Optimize re-renders with useMemo/useCallback
- [ ] Add virtual scrolling for large lists
- [ ] Implement infinite scroll

### Error Handling
- [ ] Create `inc/Core/ErrorHandler.php`
- [ ] Create `inc/Exceptions/IyoraaException.php`
- [ ] Implement error logging to database
- [ ] Add user-friendly error messages
- [ ] Create error reporting UI
- [ ] Integrate with Sentry/Rollbar (optional)

### Database Optimization
- [ ] Add soft deletes to all tables
- [ ] Implement database caching strategy
- [ ] Add query result caching with wp_cache
- [ ] Optimize slow queries
- [ ] Add database indexes analysis
- [ ] Create database seeder for demo data

---

## 🔵 LOW PRIORITY - FUTURE ENHANCEMENTS

### Advanced Features
- [ ] Implement real license server integration
- [ ] Add license key activation UI
- [ ] Create setup wizard
- [ ] Add data import/export
- [ ] Implement backup/restore
- [ ] Add audit trail viewer
- [ ] Create activity dashboard

### Developer Experience
- [ ] Setup Git hooks (pre-commit, pre-push)
- [ ] Create GitHub Actions CI/CD pipeline
- [ ] Add automated version bumping
- [ ] Create plugin distribution script
- [ ] Add code coverage reporting
- [ ] Setup automated testing on push

### Documentation
- [ ] Create API documentation (REST endpoints)
- [ ] Add PHPDoc to all classes/methods
- [ ] Create architecture decision records (ADRs)
- [ ] Write developer guide
- [ ] Create user manual
- [ ] Add inline code comments
- [ ] Create video tutorials

### Internationalization
- [ ] Create .pot file for translations
- [ ] Add translation functions to all strings
- [ ] Test with RTL languages
- [ ] Add language switcher

### Accessibility
- [ ] WCAG 2.1 AA compliance
- [ ] Keyboard navigation
- [ ] Screen reader testing
- [ ] ARIA labels
- [ ] Color contrast fixes

---

## 📋 PHASE-SPECIFIC TASKS

### Phase 3: Appointments (In Progress)
**Status:** Not Started  
**Duration:** 2-3 weeks  
**Tier:** FREE (limited), PRO (full features)

#### Backend
- [ ] AppointmentManager class
- [ ] Appointment status workflow
- [ ] Doctor scheduling logic
- [ ] Conflict detection
- [ ] Appointment reminders (email/SMS)
- [ ] REST API endpoints
- [ ] Permission checks
- [ ] Tier-based limits

#### Frontend
- [ ] Calendar view integration
- [ ] Appointment booking form
- [ ] Appointment list with filters
- [ ] Status management UI
- [ ] Doctor selection
- [ ] Time slot picker
- [ ] Appointment details modal

#### Testing
- [ ] Unit tests for AppointmentManager
- [ ] API integration tests
- [ ] Frontend component tests
- [ ] E2E booking flow test

### Phase 4: Billing/Invoicing
**Status:** Not Started  
**Duration:** 2 weeks  
**Tier:** FREE (basic), PRO (advanced)

#### Backend
- [ ] InvoiceManager class
- [ ] PaymentManager class
- [ ] Invoice number generation
- [ ] Payment processing
- [ ] Refund handling
- [ ] Tax calculation
- [ ] Discount logic
- [ ] PDF generation (Dompdf)

#### Frontend
- [ ] Invoice creation wizard
- [ ] Invoice list with filters
- [ ] Payment recording UI
- [ ] Invoice preview/print
- [ ] Payment history
- [ ] Receipt generation

#### Testing
- [ ] Invoice calculation tests
- [ ] Payment processing tests
- [ ] PDF generation tests
- [ ] Integration tests

### Phase 5: Reports & Analytics
**Status:** Not Started  
**Duration:** 1-2 weeks  
**Tier:** FREE (basic), PRO (advanced)

#### Backend
- [ ] ReportManager class
- [ ] Data aggregation queries
- [ ] Export to CSV/Excel
- [ ] Scheduled reports
- [ ] Chart data APIs

#### Frontend
- [ ] Dashboard with widgets
- [ ] Report filters
- [ ] Chart components (Chart.js)
- [ ] Data export UI
- [ ] Print reports

---

## 🎯 PERFORMANCE OPTIMIZATIONS

### Database
- [ ] Analyze slow queries with Query Monitor
- [ ] Add composite indexes
- [ ] Implement query result caching
- [ ] Use prepared statements everywhere
- [ ] Optimize JOIN queries
- [ ] Add pagination to all lists

### React/Frontend
- [ ] Bundle size analysis
- [ ] Code splitting by route
- [ ] Lazy load heavy components
- [ ] Optimize images/assets
- [ ] Minimize CSS/JS
- [ ] Add service worker (PWA)

### WordPress
- [ ] Minimize admin AJAX calls
- [ ] Use transients for expensive operations
- [ ] Implement object caching
- [ ] Optimize asset loading
- [ ] Defer non-critical JS

---

## 🔒 SECURITY HARDENING

### Input Validation
- [ ] Validate all user inputs
- [ ] Sanitize all outputs
- [ ] Escape HTML/JS/SQL
- [ ] Add CSRF tokens
- [ ] Implement rate limiting

### API Security
- [ ] Verify nonces on all endpoints
- [ ] Check user capabilities
- [ ] Add request throttling
- [ ] Log suspicious activities
- [ ] Implement API key authentication (PRO)

### Data Protection
- [ ] Hash sensitive data
- [ ] Encrypt stored passwords
- [ ] Implement GDPR compliance
- [ ] Add data anonymization
- [ ] Secure file uploads

---

## 📦 DEPLOYMENT CHECKLIST

### Pre-Release
- [ ] Run all tests
- [ ] Fix all linting errors
- [ ] Build production assets
- [ ] Update version numbers
- [ ] Update CHANGELOG.md
- [ ] Create git tag
- [ ] Build plugin ZIP

### WordPress.org Submission
- [ ] Create plugin assets (banner, icon)
- [ ] Write plugin description
- [ ] Create screenshots
- [ ] Submit for review
- [ ] Address reviewer feedback

### Post-Release
- [ ] Monitor error logs
- [ ] Gather user feedback
- [ ] Create support documentation
- [ ] Plan next version features

---

## 📊 PROGRESS TRACKING

### Overall Completion: 40%
- ✅ Foundation (95%)
- ✅ Patient Module (100%)
- ⏳ Appointments Module (0%)
- ⏳ Billing Module (0%)
- ⏳ Reports Module (0%)
- ⏳ Testing (0%)
- ⏳ Documentation (30%)

### Weekly Goals
**Week 1:** Build assets, security audit, start Appointments backend  
**Week 2:** Complete Appointments module  
**Week 3:** Implement Billing module  
**Week 4:** Testing & bug fixes  
**Week 5-6:** Polish & documentation  

---

**Next Immediate Action:** Run `npm install && npm run build`
