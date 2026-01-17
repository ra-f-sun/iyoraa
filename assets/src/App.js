import { useEffect } from "react";
import {
  HashRouter as Router,
  Routes,
  Route,
  Navigate,
  useNavigate,
  useLocation,
  Link,
} from "react-router-dom";
import PatientList from "./components/PatientList";
import PatientForm from "./components/PatientForm";
import PatientDetail from "./components/PatientDetail";

function AppContent() {
  const navigate = useNavigate();
  const location = useLocation();
  
  // Redirect to /patients if at root
  useEffect(() => {
    if (location.pathname === '/') {
      navigate('/patients', { replace: true });
    }
  }, [location, navigate]);
  
  return (
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
            <Link to="/patients" className={`nav-link ${location.pathname.startsWith('/patients') ? 'active' : ''}`}>
              Patients
            </Link>
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
            <Route path="/" element={<PatientList />} />
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
  );
}

function App() {
  console.log('Iyoraa App mounted successfully!');
  console.log('iyoraaData:', window.iyoraaData);
  
  return (
    <Router>
      <AppContent />
    </Router>
  );
}

export default App;
