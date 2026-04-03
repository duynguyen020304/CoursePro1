import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import './index.css'
import App from './App'

// Validate environment variables early on startup
// This shows a toast error if any VITE_* vars are missing or invalid
import { validateEnvironment } from './utils/env'

// Run validation - toast will show automatically if there's an error
validateEnvironment()

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <App />
  </StrictMode>,
)
