import { useState, useEffect } from "react";
import { useNavigate, useParams } from "react-router-dom";
import usePatients from "../hooks/usePatients";

const PatientDetail = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const { fetchPatient, deletePatient, loading } = usePatients();

  const [patient, setPatient] = useState(null);
  const [error, setError] = useState("");

  // Load patient data
  useEffect(() => {
    const loadPatient = async () => {
      const data = await fetchPatient(id);
      if (data) {
        setPatient(data);
      } else {
        setError("Patient not found");
      }
    };
    loadPatient();
  }, [id, fetchPatient]);

  // Handle delete
  const handleDelete = async () => {
    if (
      !window.confirm(
        `Are you sure you want to delete patient "${patient?.full_name}"? This action cannot be undone.`
      )
    ) {
      return;
    }

    try {
      await deletePatient(id);
      alert("Patient deleted successfully");
      navigate("/patients");
    } catch (err) {
      alert("Failed to delete patient: " + err.message);
    }
  };

  if (loading) {
    return <div className="loading">Loading patient details...</div>;
  }

  if (error) {
    return (
      <div className="patient-detail-container">
        <div className="error-message">{error}</div>
        <button
          onClick={() => navigate("/patients")}
          className="btn btn-secondary"
        >
          Back to List
        </button>
      </div>
    );
  }

  if (!patient) {
    return null;
  }

  return (
    <div className="patient-detail-container">
      <div className="patient-detail-header">
        <button
          onClick={() => navigate("/patients")}
          className="btn btn-secondary"
        >
          ← Back to List
        </button>
        <div className="header-actions">
          <button
            onClick={() => navigate(`/patients/${id}/edit`)}
            className="btn btn-primary"
          >
            Edit Patient
          </button>
          <button onClick={handleDelete} className="btn btn-danger">
            Delete Patient
          </button>
        </div>
      </div>

      <div className="patient-detail-card">
        <div className="patient-header">
          <div className="patient-id-badge">{patient.patient_id}</div>
          <h1>{patient.full_name}</h1>
          <p className="patient-meta">
            Registered on {new Date(patient.created_at).toLocaleDateString()}
          </p>
        </div>

        <div className="detail-sections">
          <div className="detail-section">
            <h2>Basic Information</h2>
            <div className="detail-grid">
              <div className="detail-item">
                <span className="detail-label">Age</span>
                <span className="detail-value">{patient.age} years</span>
              </div>
              <div className="detail-item">
                <span className="detail-label">Gender</span>
                <span className="detail-value capitalize">
                  {patient.gender}
                </span>
              </div>
              {patient.blood_group && (
                <div className="detail-item">
                  <span className="detail-label">Blood Group</span>
                  <span className="detail-value">{patient.blood_group}</span>
                </div>
              )}
            </div>
          </div>

          <div className="detail-section">
            <h2>Contact Information</h2>
            <div className="detail-grid">
              <div className="detail-item">
                <span className="detail-label">Phone</span>
                <span className="detail-value">
                  <a href={`tel:${patient.phone}`}>{patient.phone}</a>
                </span>
              </div>
              {patient.email && (
                <div className="detail-item">
                  <span className="detail-label">Email</span>
                  <span className="detail-value">
                    <a href={`mailto:${patient.email}`}>{patient.email}</a>
                  </span>
                </div>
              )}
            </div>
            {patient.address && (
              <div className="detail-item full-width">
                <span className="detail-label">Address</span>
                <span className="detail-value">{patient.address}</span>
              </div>
            )}
          </div>

          {(patient.emergency_contact_name ||
            patient.emergency_contact_phone) && (
            <div className="detail-section">
              <h2>Emergency Contact</h2>
              <div className="detail-grid">
                {patient.emergency_contact_name && (
                  <div className="detail-item">
                    <span className="detail-label">Name</span>
                    <span className="detail-value">
                      {patient.emergency_contact_name}
                    </span>
                  </div>
                )}
                {patient.emergency_contact_phone && (
                  <div className="detail-item">
                    <span className="detail-label">Phone</span>
                    <span className="detail-value">
                      <a href={`tel:${patient.emergency_contact_phone}`}>
                        {patient.emergency_contact_phone}
                      </a>
                    </span>
                  </div>
                )}
              </div>
            </div>
          )}

          {patient.medical_history && (
            <div className="detail-section">
              <h2>Medical History</h2>
              <div className="medical-history">{patient.medical_history}</div>
            </div>
          )}

          <div className="detail-section metadata">
            <div className="detail-grid">
              <div className="detail-item">
                <span className="detail-label">Last Updated</span>
                <span className="detail-value">
                  {new Date(patient.updated_at).toLocaleString()}
                </span>
              </div>
              <div className="detail-item">
                <span className="detail-label">Status</span>
                <span className="detail-value">
                  <span className={`status-badge ${patient.status}`}>
                    {patient.status}
                  </span>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default PatientDetail;
