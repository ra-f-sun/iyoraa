import React from "react";

function App() {
  // Access WordPress localized data
  const { tier, tierName } = window.iyoraaData || {
    tier: "free",
    tierName: "FREE",
  };

  return (
    <div className="iyoraa-container">
      <header className="iyoraa-header">
        <h1>Iyoraa - Hospital Management System</h1>
        <div className="tier-badge">{tierName}</div>
      </header>

      <div className="iyoraa-dashboard">
        <div className="welcome-card">
          <h2>Welcome to Iyoraa MVP!</h2>
          <p>Your hospital management system is successfully installed.</p>
          <p className="version">Version 1.0.0 - MVP Release</p>
        </div>

        <div className="quick-stats">
          <div className="stat-card">
            <h3>Patients</h3>
            <p className="stat-number">0</p>
            <p className="stat-label">Total Registered</p>
          </div>

          <div className="stat-card">
            <h3>Appointments</h3>
            <p className="stat-number">0</p>
            <p className="stat-label">This Month</p>
          </div>

          <div className="stat-card">
            <h3>Due Amount</h3>
            <p className="stat-number">৳ 0</p>
            <p className="stat-label">Outstanding</p>
          </div>
        </div>

        <div className="info-section">
          <h3>Next Steps:</h3>
          <ol>
            <li>
              Run <code>composer install</code> to install PHP dependencies
            </li>
            <li>
              Run <code>npm install</code> to install JavaScript dependencies
            </li>
            <li>
              Run <code>npm start</code> to start development mode
            </li>
            <li>Check database - all 19 tables should be created</li>
            <li>Start building Patient Management module (Phase 2)</li>
          </ol>
        </div>
      </div>
    </div>
  );
}

export default App;
