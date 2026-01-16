import React from "react";
import { createRoot } from "react-dom/client";
import App from "./App";
import "./index.css";

// Get the root element
const container = document.getElementById("iyoraa-app");

if (container) {
  const root = createRoot(container);
  root.render(<App />);
}
