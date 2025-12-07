# Product Inventory System

A comprehensive inventory management system built with Laravel and Vue.js for managing product stock across multiple warehouses with role-based access control.

## Overview

This system enables efficient stock management with user-specific tasks for stock in/out operations across various warehouses. It implements a hierarchical approval workflow with dedicated roles for stock management.

## Features

- **Multi-Warehouse Support**: Manage inventory across multiple warehouse locations
- **Role-Based Access Control**:
    - **Stock Executives**: Handle stock in/out entries and daily operations
    - **Stock Managers**: Review and approve stock transactions
- **Stock Operations**:
    - Stock In: Add products to warehouse inventory
    - Stock Out: Remove products from warehouse inventory
    - Transaction approval workflow
- **Warehouse-Specific Management**: Each warehouse has dedicated managers and executives

## Tech Stack

- **Backend**: Laravel (PHP Framework)
- **Frontend**: Vue.js

## Installation

```bash
# Clone the repository
git clone <https://github.com/fooysaal/lv-product-inventory-ms.git>

# Navigate to project directory
cd lv-product-inventory-ms

# Install dependencies
composer install
npm install

# Configure environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate --seed

# Compile assets
npm run dev

# Start the server
php artisan serve
```

## User Roles

- **Stock Executive**: Creates and manages stock entries
- **Stock Manager**: Approves/rejects stock transactions

## Auth Credentials
- **Stock Executive**: stock-executive@test.com
- **Stock Manager**: stock-manager@test.com
- **Admin**: admin@test.com
