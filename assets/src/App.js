import {
  BrowserRouter as Router,
  Routes,
  Route,
  Navigate,
} from "react-router-dom";
import PatientList from "./components/PatientList";
import PatientForm from "./components/PatientForm";
import PatientDetail from "./components/PatientDetail";

function App() {
  return (
    <Router basename="/wp-admin/admin.php?page=iyoraa">
      <div className="iyoraa-app">
        <header className="iyoraa-header">
          <div className="header-container">
            <h1>Iyoraa Hospital Management System</h1>
            <div className="tier-badge">
              {window.iyoraaData?.currentTier || "FREE"}
            </div>
          </div>
        </header>

        <nav className="iyoraa-nav">
          <div className="nav-container">
            <a href="#!" className="nav-link active">
              Patients
            </a>
            <a href="#!" className="nav-link disabled">
              Appointments
            </a>
            <a href="#!" className="nav-link disabled">
              Billing
            </a>
            <a href="#!" className="nav-link disabled">
              Reports
            </a>
          </div>
        </nav>

        <main className="iyoraa-main">
          <Routes>
            <Route path="/" element={<Navigate to="/patients" replace />} />
            <Route path="/patients" element={<PatientList />} />
            <Route path="/patients/new" element={<PatientForm />} />
            <Route path="/patients/:id" element={<PatientDetail />} />
            <Route path="/patients/:id/edit" element={<PatientForm />} />
          </Routes>
        </main>

        <footer className="iyoraa-footer">
          <p>
            Iyoraa Hospital Management System v
            {window.iyoraaData?.version || "1.0.0"}
          </p>
        </footer>
      </div>
    </Router>
  );
}

export default App;
