import { useState, useEffect } from "react";
import { useNavigate, useParams } from "react-router-dom";
import usePatients from "../hooks/usePatients";

const PatientForm = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const { fetchPatient, createPatient, updatePatient, loading } = usePatients();

  const [formData, setFormData] = useState({
    full_name: "",
    age: "",
    gender: "",
    phone: "",
    email: "",
    address: "",
    blood_group: "",
    emergency_contact_name: "",
    emergency_contact_phone: "",
    medical_history: "",
  });

  const [errors, setErrors] = useState({});
  const [submitError, setSubmitError] = useState("");

  const isEditMode = !!id;

  // Load patient data for edit mode
  useEffect(() => {
    if (isEditMode) {
      const loadPatient = async () => {
        const patient = await fetchPatient(id);
        if (patient) {
          setFormData({
            full_name: patient.full_name || "",
            age: patient.age || "",
            gender: patient.gender || "",
            phone: patient.phone || "",
            email: patient.email || "",
            address: patient.address || "",
            blood_group: patient.blood_group || "",
            emergency_contact_name: patient.emergency_contact_name || "",
            emergency_contact_phone: patient.emergency_contact_phone || "",
            medical_history: patient.medical_history || "",
          });
        }
      };
      loadPatient();
    }
  }, [id, isEditMode, fetchPatient]);

  // Handle input change
  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({
      ...prev,
      [name]: value,
    }));
    // Clear error for this field
    if (errors[name]) {
      setErrors((prev) => ({
        ...prev,
        [name]: "",
      }));
    }
  };

  // Validate form
  const validate = () => {
    const newErrors = {};

    if (!formData.full_name.trim()) {
      newErrors.full_name = "Full name is required";
    }

    if (!formData.age || formData.age < 0 || formData.age > 150) {
      newErrors.age = "Age must be between 0 and 150";
    }

    if (!formData.gender) {
      newErrors.gender = "Gender is required";
    }

    if (!formData.phone.trim()) {
      newErrors.phone = "Phone number is required";
    } else if (!/^\d{10,15}$/.test(formData.phone.replace(/[\s-]/g, ""))) {
      newErrors.phone = "Invalid phone number";
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  // Handle submit
  const handleSubmit = async (e) => {
    e.preventDefault();
    setSubmitError("");

    if (!validate()) {
      return;
    }

    try {
      if (isEditMode) {
        await updatePatient(id, formData);
        alert("Patient updated successfully");
      } else {
        await createPatient(formData);
        alert("Patient created successfully");
      }
      navigate("/patients");
    } catch (err) {
      setSubmitError(err.message);
    }
  };

  return (
    <div className="patient-form-container">
      <div className="patient-form-header">
        <h1>{isEditMode ? "Edit Patient" : "Add New Patient"}</h1>
        <button
          onClick={() => navigate("/patients")}
          className="btn btn-secondary"
        >
          Back to List
        </button>
      </div>

      {submitError && <div className="error-message">{submitError}</div>}

      <form onSubmit={handleSubmit} className="patient-form">
        <div className="form-section">
          <h2>Basic Information</h2>

          <div className="form-row">
            <div className="form-group">
              <label htmlFor="full_name">
                Full Name <span className="required">*</span>
              </label>
              <input
                type="text"
                id="full_name"
                name="full_name"
                value={formData.full_name}
                onChange={handleChange}
                className={errors.full_name ? "error" : ""}
              />
              {errors.full_name && (
                <span className="error-text">{errors.full_name}</span>
              )}
            </div>

            <div className="form-group">
              <label htmlFor="age">
                Age <span className="required">*</span>
              </label>
              <input
                type="number"
                id="age"
                name="age"
                value={formData.age}
                onChange={handleChange}
                min="0"
                max="150"
                className={errors.age ? "error" : ""}
              />
              {errors.age && <span className="error-text">{errors.age}</span>}
            </div>
          </div>

          <div className="form-row">
            <div className="form-group">
              <label htmlFor="gender">
                Gender <span className="required">*</span>
              </label>
              <select
                id="gender"
                name="gender"
                value={formData.gender}
                onChange={handleChange}
                className={errors.gender ? "error" : ""}
              >
                <option value="">Select Gender</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
              </select>
              {errors.gender && (
                <span className="error-text">{errors.gender}</span>
              )}
            </div>

            <div className="form-group">
              <label htmlFor="blood_group">Blood Group</label>
              <select
                id="blood_group"
                name="blood_group"
                value={formData.blood_group}
                onChange={handleChange}
              >
                <option value="">Select Blood Group</option>
                <option value="A+">A+</option>
                <option value="A-">A-</option>
                <option value="B+">B+</option>
                <option value="B-">B-</option>
                <option value="AB+">AB+</option>
                <option value="AB-">AB-</option>
                <option value="O+">O+</option>
                <option value="O-">O-</option>
              </select>
            </div>
          </div>
        </div>

        <div className="form-section">
          <h2>Contact Information</h2>

          <div className="form-row">
            <div className="form-group">
              <label htmlFor="phone">
                Phone <span className="required">*</span>
              </label>
              <input
                type="tel"
                id="phone"
                name="phone"
                value={formData.phone}
                onChange={handleChange}
                className={errors.phone ? "error" : ""}
              />
              {errors.phone && (
                <span className="error-text">{errors.phone}</span>
              )}
            </div>

            <div className="form-group">
              <label htmlFor="email">Email</label>
              <input
                type="email"
                id="email"
                name="email"
                value={formData.email}
                onChange={handleChange}
              />
            </div>
          </div>

          <div className="form-group">
            <label htmlFor="address">Address</label>
            <textarea
              id="address"
              name="address"
              value={formData.address}
              onChange={handleChange}
              rows="3"
            />
          </div>
        </div>

        <div className="form-section">
          <h2>Emergency Contact</h2>

          <div className="form-row">
            <div className="form-group">
              <label htmlFor="emergency_contact_name">
                Emergency Contact Name
              </label>
              <input
                type="text"
                id="emergency_contact_name"
                name="emergency_contact_name"
                value={formData.emergency_contact_name}
                onChange={handleChange}
              />
            </div>

            <div className="form-group">
              <label htmlFor="emergency_contact_phone">
                Emergency Contact Phone
              </label>
              <input
                type="tel"
                id="emergency_contact_phone"
                name="emergency_contact_phone"
                value={formData.emergency_contact_phone}
                onChange={handleChange}
              />
            </div>
          </div>
        </div>

        <div className="form-section">
          <h2>Medical Information</h2>

          <div className="form-group">
            <label htmlFor="medical_history">Medical History</label>
            <textarea
              id="medical_history"
              name="medical_history"
              value={formData.medical_history}
              onChange={handleChange}
              rows="5"
              placeholder="Any known allergies, chronic conditions, previous surgeries, etc."
            />
          </div>
        </div>

        <div className="form-actions">
          <button
            type="button"
            onClick={() => navigate("/patients")}
            className="btn btn-secondary"
          >
            Cancel
          </button>
          <button type="submit" className="btn btn-primary" disabled={loading}>
            {loading
              ? "Saving..."
              : isEditMode
              ? "Update Patient"
              : "Create Patient"}
          </button>
        </div>
      </form>
    </div>
  );
};

export default PatientForm;
