# IYORAA HMS - PRIORITIZED TODO (Updated Jan 17, 2026)

## 📊 PHASE 1 PROGRESS: 60% COMPLETE

### ✅ What's Done:
1. **Validation Layer** ✅ - Validator + PatientValidator **INTEGRATED** into PatientManager
2. **Exception Handling** ✅ - 5 custom exception classes created (not yet fully integrated)
3. **Repository Pattern** ✅ - BaseRepository + PatientRepository created (not yet integrated)
4. **DTOs** ✅ - PatientDTO created (not yet integrated)
5. **Cache Management** ✅ - CacheManager created (not yet integrated)
6. **Database Indexes** ✅ - 6 tables optimized with 35+ composite indexes

### ⚠️ What's NOT Connected:
- PatientRepository (created but not used by PatientManager)
- PatientDTO (created but not used by PatientManager)
- CacheManager (created but not integrated into repository)
- Custom Exceptions (created but not thrown by PatientManager)

### 🎯 Next Steps to Complete Phase 1:
1. Integrate PatientRepository into PatientManager (replace direct wpdb queries)
2. Use PatientDTO for type-safe data transfer
3. Connect CacheManager to PatientRepository
4. Replace WP_Error with custom exceptions
5. Test all integrations

---

## ✅ COMPLETED
- [x] Patient Management Module (100%)
- [x] REST API for patients (6 endpoints)
- [x] React SPA (list, create, edit, delete, search)
- [x] Database schema (19 tables created)
- [x] License management system
- [x] Rate limiting (60 req/min)
- [x] Plugin deployed to production
- [x] SFTP deployment configuration
- [x] Build system (webpack)
- [x] Comprehensive audit completed

---

## 🔥 PHASE 1: FOUNDATIONAL IMPROVEMENTS (IN PROGRESS - 60%)
**Timeline: 1-2 weeks**  
**Goal: Create solid foundation for all future features**

### A. Security Hardening (Priority: CRITICAL) ✅ PARTIALLY COMPLETE
**Duration: 2-3 days**

- [x] **Input Validation Enhancement** ✅ COMPLETED
  - [x] Create centralized validation class (`inc/Validation/Validator.php`)
  - [x] Add email format validation
  - [x] Add phone number format validation (international support)
  - [x] Add sanitization for all text inputs
  - [x] Validate blood group against allowed values
  - [x] Add age range validation (0-150)
  - [x] **Integration:** Connected PatientValidator to PatientManager
  - [x] Replaced manual validation with centralized validator
  - [x] Using PatientValidator::validate() and sanitize() methods
  
- [ ] **Output Escaping**
  - [ ] Audit all `echo` statements in templates
  - [ ] Replace with `esc_html()`, `esc_attr()`, `esc_url()`
  - [ ] Add KSES for rich text fields
  - [ ] Escape React data properly

- [ ] **CSRF Protection**
  - [ ] Verify all REST API endpoints use nonce
  - [ ] Add nonce verification to all forms
  - [ ] Implement nonce refresh mechanism
  
- [ ] **SQL Injection Prevention**
  - [ ] Audit all database queries
  - [ ] Ensure 100% prepared statements
  - [ ] Add query logging for debugging
  
- [ ] **File Upload Security** (for future features)
  - [ ] Create `inc/Security/FileUploader.php`
  - [ ] Validate file types (whitelist)
  - [ ] Scan for malware
  - [ ] Limit file sizes
  - [ ] Store outside web root

- [ ] **License Bypass Prevention**
  - [ ] Implement license server validation
  - [ ] Add daily license checks
  - [ ] Encrypt license keys in database
  - [ ] Add grace period (7 days after expiry)
  - [ ] Log license validation attempts

**Prerequisites:** None  
**Blocks:** Nothing (can do now)  
**Files to Create:**
- `inc/Validation/Validator.php`
- `inc/Validation/PatientValidator.php`
- `inc/Security/FileUploader.php`
- `inc/Security/LicenseValidator.php`

---

### B. Code Quality Enhancements (Priority: HIGH) ✅ PARTIALLY COMPLETE
**Duration: 3-4 days**

- [x] **Repository Pattern** ✅ CREATED (Not yet integrated)
  - [x] Create `inc/Repositories/BaseRepository.php`
  - [x] Create `inc/Repositories/PatientRepository.php`
  - [ ] Refactor `PatientManager` to use repository (NEXT STEP)
  - [x] Add caching layer to repositories
  - [ ] Write unit tests for repositories

- [x] **DTO (Data Transfer Objects)** ✅ CREATED (Not yet integrated)
  - [x] Create `inc/DTOs/PatientDTO.php`
  - [ ] Update PatientManager to use DTOs (NEXT STEP)
  - [x] Add validation in DTOs
  - [ ] Create DTOs for Appointment, Invoice

- [ ] **Service Layer** (POSTPONED - Do after Phase 2)
  - [ ] Create `inc/Services/PatientService.php`
  - [ ] Move business logic from Manager to Service
  - [ ] Implement dependency injection
  - [ ] Add service container

- [x] **Error Handling** ✅ COMPLETED
  - [x] Create `inc/Exceptions/` directory
  - [x] Create custom exceptions:
    - [x] `ValidationException`
    - [x] `LicenseException`
    - [x] `DatabaseException`
    - [x] `NotFoundException`
  - [ ] Integrate exceptions into PatientManager (NEXT STEP)
  - [ ] Add global exception handler
  - [ ] Log errors to `iyoraa_error_log` table

- [ ] **Code Standards**
  - [ ] Run `composer run-script phpcs` and fix all issues
  - [ ] Add PHPDoc to all methods
  - [ ] Remove unused code
  - [ ] Fix long methods (split into smaller ones)

**Prerequisites:** None  
**Blocks:** Nothing (improves existing code)  
**Files to Create:**
- `inc/Repositories/BaseRepository.php`
- `inc/Repositories/PatientRepository.php`
- `inc/DTOs/PatientDTO.php`
- `inc/Services/PatientService.php`
- `inc/Exceptions/*.php`

---

### C. Performance Optimization (Priority: HIGH) ✅ PARTIALLY COMPLETE
**Duration: 2-3 days**

- [x] **Database Query Optimization** ✅ COMPLETED
  - [x] Add indexes to frequently queried columns
  - [x] Optimize patient search query (FULLTEXT index on name, phone, patient_id)
  - [x] Add composite indexes for date ranges
  - [x] Optimized 6 critical tables: patients, appointments, invoices, payments, audit_log, error_log
  - [x] Added 35+ composite indexes for common query patterns
  - [ ] Analyze slow queries (enable query logging) - OPTIONAL

- [x] **Caching Implementation** ✅ COMPLETED
  - [x] Create `inc/Cache/CacheManager.php`
  - [x] Cache patient list (5 minutes TTL)
  - [x] Cache patient counts
  - [x] Cache license tier data
  - [x] Use WordPress transients API
  - [x] Add cache invalidation on updates
  - [ ] Integrate CacheManager into PatientRepository (NEXT STEP)

- [ ] **Asset Optimization**
  - [ ] Minify CSS (already done by webpack)
  - [ ] Minify JavaScript (already done by webpack)
  - [ ] Add cache busting for assets
  - [ ] Lazy load images (future)
  - [ ] Use CDN for assets (future)

- [ ] **Pagination Optimization**
  - [ ] Implement cursor-based pagination (instead of offset)
  - [ ] Add "Load More" button option
  - [ ] Cache page results

- [ ] **API Response Optimization**
  - [ ] Add HTTP caching headers
  - [ ] Implement ETag support
  - [ ] Add compression
  - [ ] Reduce payload size (exclude unnecessary fields)

**Prerequisites:** None  
**Blocks:** Nothing  
**Files to Create:**
- `inc/Cache/CacheManager.php`
- `inc/Performance/QueryOptimizer.php`

---

### D. Testing Infrastructure (Priority: HIGH)
**Duration: 3-4 days**

- [ ] **PHPUnit Setup**
  - [ ] Install PHPUnit via Composer
  - [ ] Create `phpunit.xml` configuration
  - [ ] Create `tests/` directory structure
  - [ ] Write sample test for PatientManager
  - [ ] Setup CI/CD for automated testing

- [ ] **Unit Tests**
  - [ ] Test PatientManager CRUD operations
  - [ ] Test PatientRepository
  - [ ] Test LicenseManager tier checks
  - [ ] Test validation logic
  - [ ] Test ID generation

- [ ] **Integration Tests**
  - [ ] Test REST API endpoints
  - [ ] Test database operations
  - [ ] Test authentication
  - [ ] Test rate limiting

- [ ] **React Testing**
  - [ ] Install Jest and React Testing Library
  - [ ] Test PatientList component
  - [ ] Test PatientForm validation
  - [ ] Test usePatients hook
  - [ ] Test API integration

**Prerequisites:** None  
**Blocks:** Nothing  
**Target:** 80% code coverage  
**Files to Create:**
- `tests/Unit/PatientManagerTest.php`
- `tests/Integration/PatientAPITest.php`
- `assets/src/__tests__/PatientList.test.js`

---

## 🚀 PHASE 2: NEW MODULES (After Phase 1 Complete)
**Timeline: 4-6 weeks**

### Module 1: Appointments (Week 1-2)
- [ ] Create AppointmentManager.php
- [ ] Create AppointmentAPI.php (REST endpoints)
- [ ] Create React components (AppointmentList, AppointmentForm, Calendar)
- [ ] Implement appointment ID generation (APT-YYYY-####)
- [ ] Add appointment status workflow (scheduled → completed → cancelled)
- [ ] Add email notifications
- [ ] Add calendar view (FullCalendar.js)
- [ ] Implement recurring appointments
- [ ] Add appointment reminders

### Module 2: Billing (Week 3-4)
- [ ] Create InvoiceManager.php
- [ ] Create PaymentManager.php
- [ ] Create BillingAPI.php
- [ ] Create React components (InvoiceList, InvoiceForm, PaymentForm)
- [ ] Implement invoice generation
- [ ] Add payment recording
- [ ] Generate PDF invoices
- [ ] Add tax calculations
- [ ] Implement payment methods (cash, card, insurance)
- [ ] Add payment receipts

### Module 3: Laboratory (Week 5)
- [ ] Create LabTestManager.php
- [ ] Create LabAPI.php
- [ ] Create React components (TestList, TestForm, ResultEntry)
- [ ] Implement test catalog
- [ ] Add test results entry
- [ ] Generate test reports
- [ ] Add normal range indicators
- [ ] Email test results

### Module 4: Reports & Analytics (Week 6)
- [ ] Patient statistics dashboard
- [ ] Revenue reports
- [ ] Appointment analytics
- [ ] Doctor performance reports
- [ ] Export to PDF/Excel
- [ ] Date range filtering
- [ ] Charts and graphs (Chart.js)

---

## 📊 PHASE SUMMARY

### Phase 1 Benefits (Foundational Improvements)
✅ **Security:** Production-ready security measures  
✅ **Performance:** 3-5x faster with caching  
✅ **Code Quality:** Maintainable, testable architecture  
✅ **Testing:** 80% coverage, fewer bugs  
✅ **Developer Experience:** Faster development of new features  

### Why Phase 1 First?
1. **Prevents Technical Debt:** Fix architecture now before it's too late
2. **Faster Module Development:** Clean foundation = faster builds
3. **Fewer Bugs:** Testing catches issues early
4. **Better Performance:** Users notice speed improvements
5. **Production Ready:** Can confidently launch

### Estimated Timeline
- Phase 1 (Improvements): **1-2 weeks**
- Phase 2 (New Modules): **4-6 weeks**
- **Total:** 6-8 weeks to complete MVP

---

## 🎯 RECOMMENDED APPROACH

### Option A: "Foundation First" (RECOMMENDED)
```
Week 1-2:  Phase 1 (Security + Code Quality + Performance + Testing)
Week 3-4:  Appointments Module
Week 5-6:  Billing Module
Week 7:    Laboratory Module
Week 8:    Reports & Polish
```

**Pros:**
- ✅ Solid foundation
- ✅ Faster module development
- ✅ Production-ready from day 1
- ✅ Easier to maintain

**Cons:**
- ❌ Takes 2 weeks before new features

### Option B: "Features First"
```
Week 1-2:  Appointments Module
Week 3-4:  Billing Module
Week 5-6:  Laboratory Module
Week 7-8:  Phase 1 Improvements (Technical Debt Cleanup)
```

**Pros:**
- ✅ New features faster
- ✅ Can demo more quickly

**Cons:**
- ❌ Will need refactoring later
- ❌ More bugs initially
- ❌ Harder to add security/testing later
- ❌ Performance issues

---

## ✅ DECISION NEEDED

**Should we do Phase 1 (Foundation) before Phase 2 (New Modules)?**

**My Recommendation: YES - Do Phase 1 First**

**Reasons:**
1. Current patient module is working - use this time to solidify
2. New modules will be built on clean architecture
3. Testing infrastructure prevents future bugs
4. Performance optimization benefits all modules
5. Security hardening is critical before launch

**Next Steps:**
1. Confirm approach (Foundation First vs Features First)
2. Start with Security Hardening (2-3 days)
3. Move to Code Quality (3-4 days)
4. Add Performance Optimization (2-3 days)
5. Setup Testing Infrastructure (3-4 days)
6. Begin Appointments Module

---

## 📝 NOTES

- All Phase 1 work is **non-breaking** - existing features keep working
- Each improvement can be done **independently**
- Testing infrastructure pays dividends immediately
- Performance gains are measurable
- Security improvements reduce risk

**Total Investment:** 10-12 days  
**Return:** Solid foundation for 6+ months of development
