# Iyoraa - Hospital Management System for WordPress

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-green.svg)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)
![License](https://img.shields.io/badge/license-GPL--2.0-orange.svg)

Complete hospital management solution for WordPress with patient management, appointments, billing, and more.

---

## 🏥 Features

### FREE Tier
- ✅ **Patient Management** - Up to 100 active patients
- ✅ **Basic Demographics** - Name, age, gender, contact info
- ✅ **Medical History** - Simple text-based records
- ✅ **Search & Filter** - Find patients quickly
- ✅ **Unique Patient IDs** - Auto-generated (HOS-YYYY-####)
- ✅ **Audit Logging** - Track all changes

### PRO Tiers (Coming Soon)
- 🚀 **Unlimited Patients** - No restrictions
- 🏥 **IPD Management** - In-patient department
- 🧪 **Laboratory Management** - Tests and results
- 💊 **Pharmacy Management** - Medicine inventory
- 📅 **Advanced Appointments** - Unlimited bookings
- 💰 **Complete Billing** - Invoicing and payments
- 📊 **Advanced Reports** - Analytics and insights
- 👥 **HR Management** - Staff and attendance
- 🔐 **GDPR Compliance** - Data protection tools

---

## 📋 Requirements

- **WordPress:** 5.8 or higher
- **PHP:** 7.4 or higher
- **MySQL:** 5.7 or higher
- **Node.js:** 18+ (for development only)
- **Composer:** 2.0+ (for development only)

---

## 🚀 Installation

### For Users (Production)

1. **Download the plugin**
   ```
   Download the latest release from your account
   ```

2. **Upload to WordPress**
   - Go to `Plugins → Add New → Upload Plugin`
   - Choose the downloaded ZIP file
   - Click "Install Now"

3. **Activate**
   - Click "Activate Plugin"
   - The plugin will automatically create 19 database tables

4. **Access the plugin**
   - Navigate to `Iyoraa` in your WordPress admin menu
   - Start managing your hospital!

### For Developers (Development)

1. **Clone the repository**
   ```bash
   cd /path/to/wordpress/wp-content/plugins/
   git clone https://github.com/your-org/iyoraa-hms.git hospital-management-system
   cd hospital-management-system
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install JavaScript dependencies**
   ```bash
   npm install
   ```

4. **Build assets**
   ```bash
   # Production build
   npm run build
   
   # Development build (watch mode)
   npm run start
   ```

5. **Activate in WordPress**
   - Go to WordPress admin → Plugins
   - Find "Iyoraa - Hospital Management System"
   - Click "Activate"

---

## 🛠️ Development

### Project Structure

```
hospital-management-system/
├── assets/
│   ├── src/               # React source files
│   │   ├── components/    # React components
│   │   ├── hooks/         # Custom React hooks
│   │   ├── App.js         # Main React app
│   │   └── index.js       # Entry point
│   └── dist/              # Compiled assets (auto-generated)
├── inc/
│   ├── Core/              # Core plugin classes
│   ├── API/               # REST API controllers
│   ├── Admin/             # Admin UI classes
│   └── Helpers/           # Helper functions
├── templates/             # PHP templates
├── vendor/                # Composer dependencies
├── docs/                  # Documentation
├── iyoraa.php            # Main plugin file
├── composer.json          # PHP dependencies
└── package.json          # JavaScript dependencies
```

### Available Commands

```bash
# JavaScript
npm run start          # Development mode with hot reload
npm run build          # Production build
npm run lint:js        # Lint JavaScript files
npm run format         # Format code with Prettier

# PHP
composer run phpcs     # Check coding standards
composer run phpcbf    # Auto-fix coding standards
composer run test      # Run PHPUnit tests (when available)

# Git
git checkout develop   # Switch to development branch
git checkout -b feature/your-feature  # Create feature branch
```

### Development Workflow

1. **Create a feature branch**
   ```bash
   git checkout develop
   git checkout -b feature/your-feature-name
   ```

2. **Make your changes**
   - Edit PHP files in `inc/`
   - Edit React files in `assets/src/`

3. **Test your changes**
   ```bash
   # Build assets
   npm run build
   
   # Run linting
   composer run phpcs
   npm run lint:js
   
   # Test in WordPress
   # - Activate plugin
   # - Test functionality
   ```

4. **Commit your changes**
   ```bash
   git add .
   git commit -m "feat: add your feature description"
   ```

5. **Push and create PR**
   ```bash
   git push origin feature/your-feature-name
   # Create Pull Request to develop branch
   ```

### Commit Convention

Follow conventional commits:
- `feat:` New feature
- `fix:` Bug fix
- `refactor:` Code refactoring
- `docs:` Documentation changes
- `test:` Adding tests
- `chore:` Maintenance tasks
- `style:` Code formatting

---

## 🔌 Usage

### Managing Patients

1. **Add New Patient**
   - Navigate to `Iyoraa → Patients`
   - Click "Add New Patient"
   - Fill in required information:
     - Full Name (required)
     - Age (required)
     - Gender (required)
     - Phone (required)
     - Email (optional)
     - Address (optional)
   - Click "Save Patient"

2. **Search Patients**
   - Use the search box at the top
   - Search by name, phone, or patient ID
   - Results update in real-time

3. **Edit Patient**
   - Click on patient name or "Edit" button
   - Update information
   - Click "Update Patient"

4. **View Patient Details**
   - Click on patient card
   - View complete information
   - See medical history
   - Check appointment history (coming soon)

5. **Delete Patient**
   - Click "Delete" button
   - Confirm deletion
   - Patient is permanently removed

### REST API Usage

The plugin provides REST API endpoints for programmatic access:

```bash
# Base URL
https://your-site.com/wp-json/iyoraa/v1

# Authentication (send nonce in header)
X-WP-Nonce: {your_nonce}

# List all patients
GET /patients?page=1&per_page=20

# Get single patient
GET /patients/123

# Create patient
POST /patients
{
  "full_name": "John Doe",
  "age": 30,
  "gender": "male",
  "phone": "1234567890",
  "email": "john@example.com"
}

# Update patient
PUT /patients/123
{
  "phone": "0987654321"
}

# Delete patient
DELETE /patients/123

# Search patients
GET /patients/search?q=john
```

---

## 🔒 Security

### Best Practices

1. **Keep Updated**
   - Always use the latest version
   - Enable automatic updates

2. **Use Strong License Keys**
   - Never share your license key
   - Store securely

3. **Regular Backups**
   - Backup database regularly
   - Export patient data periodically

4. **User Permissions**
   - Only grant access to trusted users
   - Use WordPress user roles properly

### Security Features

- ✅ Nonce verification on all AJAX requests
- ✅ SQL injection prevention (prepared statements)
- ✅ Input sanitization
- ✅ Output escaping
- ✅ Capability checks on all operations
- ✅ Audit logging for all changes

### Reporting Security Issues

If you discover a security vulnerability, please email:
**security@iyoraa.com**

Do not create public GitHub issues for security problems.

---

## 🐛 Troubleshooting

### Plugin Won't Activate

**Problem:** Error on activation  
**Solution:**
1. Check PHP version (must be 7.4+)
2. Check WordPress version (must be 5.8+)
3. Verify file permissions
4. Check error logs in `wp-content/debug.log`

### React App Not Loading

**Problem:** Blank page in admin  
**Solution:**
1. Ensure `assets/dist/` folder exists
2. Run `npm run build` to rebuild assets
3. Clear browser cache (Ctrl+F5)
4. Check browser console for errors

### Database Tables Not Created

**Problem:** Plugin activated but tables missing  
**Solution:**
1. Deactivate and reactivate plugin
2. Check database user has CREATE TABLE permission
3. Check WordPress debug log
4. Manually run activation from phpMyAdmin

### Patient Limit Reached (FREE Tier)

**Problem:** Can't add more patients  
**Solution:**
- FREE tier limited to 100 active patients
- Delete inactive patients or upgrade to PRO
- Contact sales for enterprise options

---

## 📚 Documentation

- **User Guide:** [View Full Guide](docs/user-guide.md) _(coming soon)_
- **API Documentation:** [View API Docs](docs/api-documentation.md) _(coming soon)_
- **Developer Guide:** [View Dev Guide](docs/developer-guide.md) _(coming soon)_
- **Architecture:** [View Architecture](docs/comprehensive-architecture.txt)
- **Database Schema:** [View Schema](docs/database-architecture.txt)

---

## 🤝 Contributing

We welcome contributions! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'feat: add some amazing feature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request to the `develop` branch

### Code Standards

- Follow [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- Write PHPDoc comments for all functions
- Write tests for new features
- Ensure all tests pass before submitting PR

---

## 🧪 Testing

### Manual Testing

1. Activate plugin in WordPress
2. Navigate to Iyoraa menu
3. Test patient CRUD operations
4. Verify data persistence
5. Check audit logs

### Automated Testing (Coming Soon)

```bash
# PHP Unit Tests
composer run test

# JavaScript Tests
npm test

# E2E Tests
npm run test:e2e
```

---

## 📈 Roadmap

### v1.0.0 - MVP (Current)
- [x] Patient Management
- [ ] Basic Appointments
- [ ] Simple Billing
- [ ] Basic Reports

### v1.1.0 - Q1 2026
- [ ] Complete Appointment System
- [ ] Advanced Billing & Invoicing
- [ ] Payment Gateway Integration
- [ ] Email Notifications

### v2.0.0 - PRO Release (Q2 2026)
- [ ] IPD Management
- [ ] Laboratory Management
- [ ] Pharmacy Module
- [ ] Advanced Reports
- [ ] HR Management

---

## 📞 Support

- **Documentation:** [https://docs.iyoraa.com](https://docs.iyoraa.com)
- **Support Email:** support@iyoraa.com
- **Community Forum:** [https://community.iyoraa.com](https://community.iyoraa.com)
- **Bug Reports:** [GitHub Issues](https://github.com/your-org/iyoraa-hms/issues)

### Support Hours
- Monday - Friday: 9 AM - 6 PM (EST)
- Saturday: 10 AM - 4 PM (EST)
- Sunday: Closed

---

## 📄 License

This plugin is licensed under the GNU General Public License v2 or later.

```
Copyright (C) 2026 Rafsun Jani / WPHelpZone

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

See [LICENSE](LICENSE) file for full text.

---

## 👏 Credits

### Author
**Rafsun Jani**  
[WPHelpZone](https://wphelpzone.com)

### Contributors
- [Contributor List](CONTRIBUTORS.md) _(coming soon)_

### Built With
- [WordPress](https://wordpress.org/)
- [React](https://reactjs.org/)
- [WordPress Scripts](https://www.npmjs.com/package/@wordpress/scripts)
- [Composer](https://getcomposer.org/)

---

## 🌟 Show Your Support

If you find this plugin helpful, please:
- ⭐ Star this repository
- 🐦 Share on social media
- 📝 Write a review
- 💬 Spread the word

---

## 📊 Stats

![GitHub stars](https://img.shields.io/github/stars/your-org/iyoraa-hms?style=social)
![GitHub forks](https://img.shields.io/github/forks/your-org/iyoraa-hms?style=social)
![GitHub watchers](https://img.shields.io/github/watchers/your-org/iyoraa-hms?style=social)

---

**Made with ❤️ for healthcare professionals worldwide**
