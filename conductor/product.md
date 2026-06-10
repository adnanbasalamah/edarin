# Initial Concept

Edarin - Distribution Management System

# Product Guide

## Product Overview
Edarin is a distribution management system designed to streamline the flow of goods from central administration to field distributors. It enables admins to manage products, stores, and distributor accounts, while empowering distributors to submit store data, sales reports, and returns directly from their mobile devices.

## Target Users
- **Admin:** Internal staff managing the distribution network on desktop. Responsible for creating distributor accounts, managing product catalogs, and overseeing all sales data.
- **Distributors (Pengedar):** Field workers who visit stores, collect sales data, and submit reports via their mobile phones.

## Core Features (MVP)
- **Authentication & User Management:** JWT-based login system. Admins can create, edit, and manage distributor accounts.
- **Store Management:** Full CRUD for store data including store name, owner name, address, phone number, and automatic coordinate capture.
- **Sales & Returns Input:** Distributors can input sales quantities per product per store, along with return quantities. Each submission automatically generates a Nota (invoice) grouping all products for that store.
- **Reports & Analytics:** Distributors can view their transaction history as Notas (invoices) with detailed product breakdowns, and download reports (daily/weekly/monthly). Admins can view all distributor data and Notas across the network.

## User Experience
- **Admin Interface:** Desktop-optimized with comprehensive dashboards, data tables, and management panels.
- **Distributor Interface:** Mobile-first, touch-friendly interface optimized for on-the-go data entry.

## Deployment
- **Architecture:** Single Page Application (web-based) wrapped as an Android homescreen button for mobile access.
- **Backend:** REST API served by CodeIgniter 4 with JWT authentication.
- **Database:** MariaDB/MySQL

## Success Metrics
- Distributors can complete a sales report in under 2 minutes per store visit
- Admin can onboard a new distributor in under 5 minutes
- Reports are accessible with sub-2-second load times