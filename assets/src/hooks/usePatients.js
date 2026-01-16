import { useState, useEffect, useCallback } from "react";

/**
 * Custom hook for patient management operations.
 *
 * @return {Object} Patient data and operations.
 */
const usePatients = () => {
  const [patients, setPatients] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [totalPatients, setTotalPatients] = useState(0);
  const [totalPages, setTotalPages] = useState(1);

  const apiBase = window.iyoraaData?.restUrl || "/wp-json/iyoraa/v1";
  const nonce = window.iyoraaData?.nonce || "";

  /**
   * Fetch patients list.
   *
   * @param {number} page     Current page.
   * @param {number} perPage  Items per page.
   */
  const fetchPatients = useCallback(
    async (page = 1, perPage = 20) => {
      setLoading(true);
      setError(null);

      try {
        const response = await fetch(
          `${apiBase}/patients?page=${page}&per_page=${perPage}`,
          {
            headers: {
              "X-WP-Nonce": nonce,
            },
          }
        );

        if (!response.ok) {
          throw new Error("Failed to fetch patients");
        }

        const data = await response.json();

        if (data.success) {
          setPatients(data.data.patients);
          setTotalPatients(data.data.total);
          setTotalPages(data.data.total_pages);
        }
      } catch (err) {
        setError(err.message);
      } finally {
        setLoading(false);
      }
    },
    [apiBase, nonce]
  );

  /**
   * Fetch single patient.
   *
   * @param {number} id Patient ID.
   * @return {Promise<Object>} Patient data.
   */
  const fetchPatient = useCallback(
    async (id) => {
      setLoading(true);
      setError(null);

      try {
        const response = await fetch(`${apiBase}/patients/${id}`, {
          headers: {
            "X-WP-Nonce": nonce,
          },
        });

        if (!response.ok) {
          throw new Error("Failed to fetch patient");
        }

        const data = await response.json();

        if (data.success) {
          return data.data;
        }
      } catch (err) {
        setError(err.message);
        return null;
      } finally {
        setLoading(false);
      }
    },
    [apiBase, nonce]
  );

  /**
   * Create new patient.
   *
   * @param {Object} patientData Patient data.
   * @return {Promise<Object>} Created patient data.
   */
  const createPatient = useCallback(
    async (patientData) => {
      setLoading(true);
      setError(null);

      try {
        const response = await fetch(`${apiBase}/patients`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-WP-Nonce": nonce,
          },
          body: JSON.stringify(patientData),
        });

        const data = await response.json();

        if (!response.ok) {
          throw new Error(data.message || "Failed to create patient");
        }

        if (data.success) {
          return data.data;
        }
      } catch (err) {
        setError(err.message);
        throw err;
      } finally {
        setLoading(false);
      }
    },
    [apiBase, nonce]
  );

  /**
   * Update patient.
   *
   * @param {number} id          Patient ID.
   * @param {Object} patientData Updated patient data.
   * @return {Promise<Object>} Updated patient data.
   */
  const updatePatient = useCallback(
    async (id, patientData) => {
      setLoading(true);
      setError(null);

      try {
        const response = await fetch(`${apiBase}/patients/${id}`, {
          method: "PUT",
          headers: {
            "Content-Type": "application/json",
            "X-WP-Nonce": nonce,
          },
          body: JSON.stringify(patientData),
        });

        const data = await response.json();

        if (!response.ok) {
          throw new Error(data.message || "Failed to update patient");
        }

        if (data.success) {
          return data.data;
        }
      } catch (err) {
        setError(err.message);
        throw err;
      } finally {
        setLoading(false);
      }
    },
    [apiBase, nonce]
  );

  /**
   * Delete patient.
   *
   * @param {number} id Patient ID.
   * @return {Promise<boolean>} Success status.
   */
  const deletePatient = useCallback(
    async (id) => {
      setLoading(true);
      setError(null);

      try {
        const response = await fetch(`${apiBase}/patients/${id}`, {
          method: "DELETE",
          headers: {
            "X-WP-Nonce": nonce,
          },
        });

        const data = await response.json();

        if (!response.ok) {
          throw new Error(data.message || "Failed to delete patient");
        }

        return data.success;
      } catch (err) {
        setError(err.message);
        throw err;
      } finally {
        setLoading(false);
      }
    },
    [apiBase, nonce]
  );

  /**
   * Search patients.
   *
   * @param {string} query   Search query.
   * @param {number} page    Current page.
   * @param {number} perPage Items per page.
   */
  const searchPatients = useCallback(
    async (query, page = 1, perPage = 20) => {
      setLoading(true);
      setError(null);

      try {
        const response = await fetch(
          `${apiBase}/patients/search?q=${encodeURIComponent(
            query
          )}&page=${page}&per_page=${perPage}`,
          {
            headers: {
              "X-WP-Nonce": nonce,
            },
          }
        );

        if (!response.ok) {
          throw new Error("Failed to search patients");
        }

        const data = await response.json();

        if (data.success) {
          setPatients(data.data.patients);
          setTotalPatients(data.data.total);
          setTotalPages(data.data.total_pages);
        }
      } catch (err) {
        setError(err.message);
      } finally {
        setLoading(false);
      }
    },
    [apiBase, nonce]
  );

  /**
   * Refresh patients list.
   */
  const refreshPatients = useCallback(() => {
    fetchPatients();
  }, [fetchPatients]);

  return {
    patients,
    loading,
    error,
    totalPatients,
    totalPages,
    fetchPatients,
    fetchPatient,
    createPatient,
    updatePatient,
    deletePatient,
    searchPatients,
    refreshPatients,
  };
};

export default usePatients;
