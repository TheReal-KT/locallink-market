# LocalLink Market

LocalLink Market is a simplified PHP ecommerce project for a college submission. The app now focuses on three things only:

- Storefront pages for browsing products
- Customer registration, login, checkout, and order history
- A small admin dashboard for product management and basic reporting

## Stack

- PHP
- MySQL / MariaDB
- Vanilla CSS and JavaScript

## What was removed

- Seller registration and seller dashboard
- Multi-role approval workflows
- Extra moderation, review, and audit tables
- Google or social authentication
- Large custom styling that was not needed for the project

## Pages

- `index.php`: homepage
- `products.php`: product listing
- `product.php`: product detail
- `register.php`, `login.php`, `logout.php`: account flow
- `checkout.php`: place an order
- `buyer-dashboard.php`: customer account and orders
- `admin/dashboard.php`: admin overview
- `add-product.php`: admin product creation

## Database setup

1. Import `database/schema.sql`.
2. Import `database/seed.sql`.
3. Update database settings if needed:
   - `LOCALLINK_DB_HOST`
   - `LOCALLINK_DB_PORT`
   - `LOCALLINK_DB_NAME`
   - `LOCALLINK_DB_USER`
   - `LOCALLINK_DB_PASS`

## Demo accounts

- Customer: `buyer@locallink.market` / `Buyer123!`
- Admin: `admin@locallink.market` / `Admin123!`

## Notes

- If MySQL is not available, sample data is used so the pages still render.
- Session files are stored in `tmp/sessions`.
- Product images are local SVG placeholders in `assets/images/`.
