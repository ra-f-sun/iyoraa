import { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import usePatients from "../hooks/usePatients";

const PatientList = () => {
  const navigate = useNavigate();
  const {
    patients,
    loading,
    error,
    totalPatients,
    totalPages,
    fetchPatients,
    searchPatients,
    deletePatient,
  } = usePatients();

  const [currentPage, setCurrentPage] = useState(1);
  const [searchQuery, setSearchQuery] = useState("");
  const [isSearching, setIsSearching] = useState(false);

  // Fetch patients on mount
  useEffect(() => {
    fetchPatients(currentPage);
  }, [fetchPatients, currentPage]);

  // Handle search
  const handleSearch = (e) => {
    const query = e.target.value;
    setSearchQuery(query);

    if (query.length > 2) {
      setIsSearching(true);
      searchPatients(query, 1);
      setCurrentPage(1);
    } else if (query.length === 0) {
      setIsSearching(false);
      fetchPatients(1);
      setCurrentPage(1);
    }
  };

  // Handle delete
  const handleDelete = async (id, name) => {
    if (!window.confirm(`Are you sure you want to delete patient "${name}"?`)) {
      return;
    }

    try {
      await deletePatient(id);
      alert("Patient deleted successfully");
      fetchPatients(currentPage);
    } catch (err) {
      alert("Failed to delete patient: " + err.message);
    }
  };

  // Handle pagination
  const handlePageChange = (page) => {
    setCurrentPage(page);
    if (isSearching) {
      searchPatients(searchQuery, page);
    } else {
      fetchPatients(page);
    }
  };

  // Navigate to patient detail
  const handleRowClick = (id) => {
    navigate(`/patients/${id}`);
  };

  // Get tier limit
  const patientLimit = window.iyoraaData?.limits?.patients || 100;
  const isLimitReached = totalPatients >= patientLimit;

  return (
    <div className="patient-list-container">
      <div className="patient-list-header">
        <h1>Patients</h1>
        <button
          className="btn btn-primary"
          onClick={() => navigate("/patients/new")}
          disabled={isLimitReached}
        >
          {isLimitReached ? "Patient Limit Reached" : "Add New Patient"}
        </button>
      </div>

      <div className="patient-list-stats">
        <div className="stat-card">
          <span className="stat-label">Total Patients:</span>
          <span className="stat-value">
            {totalPatients} / {patientLimit === -1 ? "∞" : patientLimit}
          </span>
        </div>
        {isLimitReached && (
          <div className="limit-warning">
            ⚠️ You've reached the FREE tier limit. Upgrade to PRO to add more
            patients.
          </div>
        )}
      </div>

      <div className="patient-list-search">
        <input
          type="text"
          placeholder="Search by name, phone, or patient ID..."
          value={searchQuery}
          onChange={handleSearch}
          className="search-input"
        />
      </div>

      {error && <div className="error-message">{error}</div>}

      {loading && <div className="loading">Loading patients...</div>}

      {!loading && patients.length === 0 && (
        <div className="no-patients">
          {isSearching
            ? "No patients found matching your search."
            : "No patients yet. Add your first patient!"}
        </div>
      )}

      {!loading && patients.length > 0 && (
        <>
          <table className="patient-table">
            <thead>
              <tr>
                <th>Patient ID</th>
                <th>Name</th>
                <th>Age</th>
                <th>Gender</th>
                <th>Phone</th>
                <th>Registration Date</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {patients.map((patient) => (
                <tr key={patient.id}>
                  <td
                    onClick={() => handleRowClick(patient.id)}
                    className="clickable"
                  >
                    {patient.patient_id}
                  </td>
                  <td
                    onClick={() => handleRowClick(patient.id)}
                    className="clickable"
                  >
                    {patient.full_name}
                  </td>
                  <td>{patient.age}</td>
                  <td className="capitalize">{patient.gender}</td>
                  <td>{patient.phone}</td>
                  <td>{new Date(patient.created_at).toLocaleDateString()}</td>
                  <td className="actions">
                    <button
                      className="btn-icon btn-edit"
                      onClick={(e) => {
                        e.stopPropagation();
                        navigate(`/patients/${patient.id}/edit`);
                      }}
                      title="Edit"
                    >
                      ✏️
                    </button>
                    <button
                      className="btn-icon btn-delete"
                      onClick={(e) => {
                        e.stopPropagation();
                        handleDelete(patient.id, patient.full_name);
                      }}
                      title="Delete"
                    >
                      🗑️
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>

          {totalPages > 1 && (
            <div className="pagination">
              <button
                onClick={() => handlePageChange(currentPage - 1)}
                disabled={currentPage === 1}
                className="btn btn-secondary"
              >
                Previous
              </button>
              <span className="page-info">
                Page {currentPage} of {totalPages}
              </span>
              <button
                onClick={() => handlePageChange(currentPage + 1)}
                disabled={currentPage === totalPages}
                className="btn btn-secondary"
              >
                Next
              </button>
            </div>
          )}
        </>
      )}
    </div>
  );
};

export default PatientList;
