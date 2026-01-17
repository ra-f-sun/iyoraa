# Appointment API Testing Guide

## Prerequisites
- WordPress site running locally or on staging
- Iyoraa HMS plugin activated
- User with `edit_posts` capability
- REST API client (Postman, Insomnia, or cURL)

## Base URL
```
http://your-wordpress-site.com/wp-json/iyoraa/v1
```

## Authentication
All endpoints require authentication. Use WordPress cookie authentication or Application Passwords.

### Generate Application Password (Recommended)
1. Go to Users → Profile
2. Scroll to "Application Passwords"
3. Generate new password
4. Use HTTP Basic Auth with username and app password

## Test Endpoints

### 1. Check API Status
```http
GET /status
```

**Expected Response:**
```json
{
  "status": "active",
  "version": "1.0.0",
  "tier": "FREE"
}
```

---

### 2. Create Appointment
```http
POST /appointments
Content-Type: application/json

{
  "patient_id": 1,
  "doctor_id": 1,
  "appointment_date": "2026-01-25",
  "appointment_time": "10:00:00",
  "duration": 30,
  "appointment_type": "consultation",
  "purpose": "Regular checkup",
  "notes": "Patient complained of headaches"
}
```

**Expected Response (201):**
```json
{
  "success": true,
  "message": "Appointment created successfully.",
  "data": {
    "id": 1,
    "appointment_id": "APT-2026-0001",
    "patient_id": 1,
    "doctor_id": 1,
    "appointment_date": "2026-01-25",
    "appointment_time": "10:00:00",
    "duration": 30,
    "appointment_type": "consultation",
    "status": "scheduled",
    "purpose": "Regular checkup",
    "notes": "Patient complained of headaches",
    "reminder_sent": false,
    "created_at": "2026-01-18 10:30:00",
    "updated_at": "2026-01-18 10:30:00"
  }
}
```

**Test Cases:**
- ✅ Valid appointment creation
- ❌ Missing required fields (patient_id, doctor_id, date, time)
- ❌ Past date (should fail validation)
- ❌ Invalid time format
- ❌ Time outside business hours (before 8 AM or after 6 PM)
- ❌ Conflicting appointment (same doctor, overlapping time)
- ❌ Monthly limit reached (FREE tier: 50 appointments/month)

---

### 3. Get Single Appointment
```http
GET /appointments/1
```

**Expected Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "appointment_id": "APT-2026-0001",
    "patient_id": 1,
    ...
  }
}
```

**Test Cases:**
- ✅ Valid appointment ID
- ❌ Non-existent ID (404 error)

---

### 4. List Appointments (with filters)
```http
GET /appointments?page=1&per_page=20
GET /appointments?status=scheduled
GET /appointments?patient_id=1
GET /appointments?doctor_id=1
GET /appointments?date_from=2026-01-20&date_to=2026-01-31
```

**Expected Response (200):**
```json
{
  "success": true,
  "data": {
    "appointments": [
      { "id": 1, "appointment_id": "APT-2026-0001", ... },
      { "id": 2, "appointment_id": "APT-2026-0002", ... }
    ],
    "total": 25,
    "page": 1,
    "per_page": 20,
    "total_pages": 2
  }
}
```

**Test Cases:**
- ✅ List all appointments
- ✅ Filter by status
- ✅ Filter by patient
- ✅ Filter by doctor
- ✅ Filter by date range
- ✅ Pagination works correctly

---

### 5. Update Appointment
```http
PUT /appointments/1
Content-Type: application/json

{
  "patient_id": 1,
  "doctor_id": 1,
  "appointment_date": "2026-01-25",
  "appointment_time": "11:00:00",
  "duration": 45,
  "appointment_type": "follow-up",
  "purpose": "Follow-up checkup",
  "notes": "Updated notes"
}
```

**Expected Response (200):**
```json
{
  "success": true,
  "message": "Appointment updated successfully.",
  "data": {
    "id": 1,
    "appointment_time": "11:00:00",
    "duration": 45,
    ...
  }
}
```

**Test Cases:**
- ✅ Update time slot (no conflict)
- ❌ Update with conflicting time
- ❌ Update to past date
- ❌ Non-existent appointment ID

---

### 6. Update Appointment Status
```http
PUT /appointments/1/status
Content-Type: application/json

{
  "status": "confirmed"
}
```

**Valid Status Values:**
- `scheduled`
- `confirmed`
- `in-progress`
- `completed`
- `cancelled`
- `no-show`

**Expected Response (200):**
```json
{
  "success": true,
  "message": "Appointment status updated successfully."
}
```

**Test Cases:**
- ✅ Status workflow: scheduled → confirmed → in-progress → completed
- ❌ Invalid status value

---

### 7. Cancel Appointment
```http
POST /appointments/1/cancel
```

**Expected Response (200):**
```json
{
  "success": true,
  "message": "Appointment cancelled successfully."
}
```

**Test Cases:**
- ✅ Cancel scheduled appointment
- ✅ Status changes to "cancelled"
- ❌ Non-existent appointment ID

---

### 8. Delete Appointment
```http
DELETE /appointments/1
```

**Expected Response (200):**
```json
{
  "success": true,
  "message": "Appointment deleted successfully."
}
```

**Test Cases:**
- ✅ Hard delete appointment
- ❌ Non-existent appointment ID

---

### 9. Get Today's Appointments
```http
GET /appointments/today
GET /appointments/today?doctor_id=1
```

**Expected Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "appointment_id": "APT-2026-0001",
      "appointment_date": "2026-01-18",
      "appointment_time": "10:00:00",
      ...
    }
  ]
}
```

---

### 10. Get Upcoming Appointments
```http
GET /appointments/upcoming?limit=10
```

**Expected Response (200):**
```json
{
  "success": true,
  "data": [
    { "id": 1, "appointment_date": "2026-01-20", ... },
    { "id": 2, "appointment_date": "2026-01-21", ... }
  ]
}
```

---

### 11. Get Appointment Statistics
```http
GET /appointments/statistics
```

**Expected Response (200):**
```json
{
  "success": true,
  "data": {
    "total_appointments": 150,
    "scheduled": 45,
    "confirmed": 30,
    "in-progress": 5,
    "completed": 60,
    "cancelled": 8,
    "no-show": 2
  }
}
```

---

### 12. Calendar View
```http
GET /appointments/calendar?start_date=2026-01-01&end_date=2026-01-31
GET /appointments/calendar?start_date=2026-01-01&end_date=2026-01-31&doctor_id=1
```

**Expected Response (200) - FullCalendar.js Format:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Regular checkup",
      "start": "2026-01-25T10:00:00",
      "end": "2026-01-25T10:30:00",
      "backgroundColor": "#3788d8",
      "borderColor": "#3788d8",
      "extendedProps": {
        "appointment_id": "APT-2026-0001",
        "patient_id": 1,
        "doctor_id": 1,
        "status": "scheduled",
        "notes": "Patient complained of headaches"
      }
    }
  ]
}
```

---

## Rate Limiting

### Write Operations (POST, PUT, DELETE)
- **Limit:** 60 requests per minute
- **Response Headers:**
  - `X-RateLimit-Limit: 60`
  - `X-RateLimit-Remaining: 45`
  - `X-RateLimit-Reset: 1737198000`

### Read Operations (GET)
- **Limit:** 120 requests per minute

### Rate Limit Exceeded (429)
```json
{
  "code": "rate_limit_exceeded",
  "message": "Rate limit exceeded. Please try again in 45 seconds.",
  "data": {
    "status": 429,
    "reset_time": 45,
    "remaining": 0
  }
}
```

---

## Business Logic Testing

### 1. Conflict Detection
Create two appointments with overlapping times for the same doctor:

**Appointment 1:**
```json
{
  "doctor_id": 1,
  "appointment_date": "2026-01-25",
  "appointment_time": "10:00:00",
  "duration": 60
}
```

**Appointment 2 (should fail):**
```json
{
  "doctor_id": 1,
  "appointment_date": "2026-01-25",
  "appointment_time": "10:30:00",
  "duration": 60
}
```

**Expected Error (409):**
```json
{
  "code": "appointment_conflict",
  "message": "This time slot is already booked. Please choose a different time.",
  "data": {
    "status": 409
  }
}
```

---

### 2. Business Hours Validation
Try creating appointment outside business hours (before 8 AM or after 6 PM):

```json
{
  "appointment_time": "07:00:00"
}
```

**Expected Error (400):**
```json
{
  "code": "validation_failed",
  "message": "Appointment time must be during business hours (8:00 AM - 6:00 PM).",
  "data": {
    "status": 400
  }
}
```

---

### 3. Monthly Limit (FREE Tier)
Create 51 appointments in the current month. The 51st should fail:

**Expected Error (403):**
```json
{
  "code": "appointment_limit_reached",
  "message": "Monthly appointment limit reached. Upgrade to PRO for unlimited appointments.",
  "data": {
    "status": 403
  }
}
```

---

### 4. Appointment ID Generation
Create multiple appointments and verify:
- First appointment: `APT-2026-0001`
- Second appointment: `APT-2026-0002`
- 100th appointment: `APT-2026-0100`

---

## Testing Checklist

### Functional Tests
- [ ] Create appointment successfully
- [ ] Get single appointment
- [ ] List all appointments
- [ ] Filter appointments by status
- [ ] Filter appointments by patient
- [ ] Filter appointments by doctor
- [ ] Filter appointments by date range
- [ ] Update appointment
- [ ] Update appointment status
- [ ] Cancel appointment
- [ ] Delete appointment
- [ ] Get today's appointments
- [ ] Get upcoming appointments
- [ ] Get appointment statistics
- [ ] Get calendar view

### Validation Tests
- [ ] Required fields validation
- [ ] Date format validation
- [ ] Time format validation
- [ ] Business hours validation (8 AM - 6 PM)
- [ ] Duration validation (max 480 minutes)
- [ ] Status enum validation
- [ ] Appointment type enum validation

### Business Logic Tests
- [ ] Conflict detection works
- [ ] Appointment ID generation (APT-YYYY-####)
- [ ] Monthly limit enforcement (FREE: 50)
- [ ] Status workflow tracking
- [ ] Audit log entries created

### Security Tests
- [ ] Unauthenticated requests rejected (403)
- [ ] Rate limiting enforced (60/min write, 120/min read)
- [ ] Permission checks work
- [ ] SQL injection prevention
- [ ] XSS prevention

### Performance Tests
- [ ] List 1000+ appointments (pagination)
- [ ] Calendar view with 1 month data
- [ ] Statistics calculation speed
- [ ] Cache hit rate for repeated queries

---

## Common Error Codes

| Code | Status | Description |
|------|--------|-------------|
| `validation_failed` | 400 | Input validation failed |
| `rest_forbidden` | 403 | No permission |
| `appointment_limit_reached` | 403 | Monthly limit reached |
| `appointment_not_found` | 404 | Appointment not found |
| `appointment_conflict` | 409 | Time slot conflict |
| `rate_limit_exceeded` | 429 | Rate limit exceeded |
| `db_insert_error` | 500 | Database error |
| `unexpected_error` | 500 | Server error |

---

## cURL Examples

### Create Appointment
```bash
curl -X POST \
  http://localhost/wordpress/wp-json/iyoraa/v1/appointments \
  -u username:application_password \
  -H "Content-Type: application/json" \
  -d '{
    "patient_id": 1,
    "doctor_id": 1,
    "appointment_date": "2026-01-25",
    "appointment_time": "10:00:00",
    "duration": 30,
    "appointment_type": "consultation",
    "purpose": "Regular checkup"
  }'
```

### List Appointments
```bash
curl -X GET \
  "http://localhost/wordpress/wp-json/iyoraa/v1/appointments?page=1&per_page=20" \
  -u username:application_password
```

### Update Status
```bash
curl -X PUT \
  http://localhost/wordpress/wp-json/iyoraa/v1/appointments/1/status \
  -u username:application_password \
  -H "Content-Type: application/json" \
  -d '{"status": "confirmed"}'
```

---

## Next Steps After Testing

1. **If all tests pass:**
   - Mark backend as complete
   - Move to frontend development (React components)

2. **If tests fail:**
   - Check PHP error logs: `wp-content/debug.log`
   - Enable WP_DEBUG in `wp-config.php`
   - Check database tables exist
   - Verify plugin is activated

3. **Performance optimization:**
   - Monitor database queries (Query Monitor plugin)
   - Check cache hit rates
   - Optimize indexes if needed

---

## Database Verification

Verify appointment data is stored correctly:

```sql
-- Check appointments table
SELECT * FROM wp_iyoraa_appointments ORDER BY id DESC LIMIT 10;

-- Check audit log
SELECT * FROM wp_iyoraa_audit_log 
WHERE entity_type = 'appointment' 
ORDER BY created_at DESC LIMIT 20;

-- Check appointment statistics
SELECT status, COUNT(*) as count 
FROM wp_iyoraa_appointments 
GROUP BY status;

-- Check for conflicts
SELECT a1.appointment_id, a1.doctor_id, a1.appointment_date, a1.appointment_time
FROM wp_iyoraa_appointments a1
JOIN wp_iyoraa_appointments a2 
  ON a1.doctor_id = a2.doctor_id 
  AND a1.appointment_date = a2.appointment_date
  AND a1.id != a2.id
WHERE a1.status NOT IN ('cancelled', 'no-show')
  AND a2.status NOT IN ('cancelled', 'no-show');
```

---

**Backend Complete! 🎉**
All appointment API endpoints are ready for testing.
