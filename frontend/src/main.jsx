import React from "react";
import ReactDOM from "react-dom/client";
import { BrowserRouter, Routes, Route } from "react-router-dom";
import App from "./App";
import Success from "./pages/Success";
import Cancel from "./pages/Cancel";

ReactDOM.createRoot(document.getElementById("root")).render(
  <React.StrictMode>
    <BrowserRouter>
      <Routes>
        <Route path="/" element={<App />} />
        <Route path="/success/:orderId" element={<Success />} />
        <Route path="/cancel/:orderId" element={<Cancel />} />
      </Routes>
    </BrowserRouter>
  </React.StrictMode>
);