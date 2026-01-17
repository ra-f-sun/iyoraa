import React from "react";
import { createRoot } from "react-dom/client";
import App from "./App";
import "./index.css";

console.log('Iyoraa script loaded!');

// Get the root element
const container = document.getElementById("iyoraa-app");

console.log('Container element:', container);

if (container) {
  console.log('Mounting React app...');
  const root = createRoot(container);
  root.render(<App />);
} else {
  console.error('Container element "iyoraa-app" not found!');
}
