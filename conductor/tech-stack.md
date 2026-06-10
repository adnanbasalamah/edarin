# Technology Stack

## Frontend
- **Alpine.js** — Lightweight reactive JavaScript framework for building interactive UIs within the SPA architecture. Ideal for mobile-first experiences with minimal overhead.
- **Tailwind CSS** — Utility-first CSS framework for rapid, consistent UI development. Responsive design built-in for mobile distributor interfaces and desktop admin panels.
- **Leaflet.js + OpenStreetMap** — Pustaka peta interaktif untuk menampilkan lokasi toko dengan marker berdasarkan koordinat latitude/longitude. Gratis dan tidak memerlukan API key.

## Backend
- **CodeIgniter 4** — Lightweight PHP framework serving a REST API. Chosen for its small footprint, strong performance, and straightforward MVC structure, well-suited for the distribution data management workloads.

## Database
- **MariaDB / MySQL** — Relational database for storing structured distribution data: users, stores, products, sales records, and returns. Well-suited for the transactional nature of the application.

## Authentication
- **JWT (JSON Web Tokens)** — Stateless token-based authentication for securing all REST API endpoints. Tokens issued upon login for both admin and distributor roles.

## Infrastructure & Tooling
- **Composer** — PHP dependency manager for installing and maintaining CodeIgniter and third-party packages.
- **Apache / Nginx** — Web server to serve the PHP backend and static frontend assets.
- **Git & GitHub** — Version control and collaboration platform.

## Architecture
- **Single Page Application (SPA):** Alpine.js frontend communicating with CodeIgniter 4 backend via REST API.
- **API-first design:** All data operations go through JSON REST endpoints.
- **Mobile hybrid:** Web app wrappable as an Android homescreen button for distributor access.