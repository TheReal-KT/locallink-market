# LocalLink Market

LocalLink Market is a simplified PHP ecommerce project for a college submission. The app focuses on three working flows:

- Storefront pages for browsing products and listings
- Buyer registration, login, checkout simulation, and order history
- Admin reporting, product creation, and role-based dashboard access

## Stack

- PHP
- MySQL / MariaDB
- Vanilla CSS and JavaScript

## Scope

- Public registration creates buyer accounts only.
- Admin access is controlled by the `users.role` column.
- Checkout is a simulated payment flow with card, EFT, and cash options.
- Orders are stored as an order header plus related order item, address, and payment rows.

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

For a fresh install, import `database/full_setup.sql`.

If you prefer the split files, import:

1. `database/schema.sql`
2. `database/seed.sql`
3. Update database settings if needed:
   - `LOCALLINK_DB_HOST`
   - `LOCALLINK_DB_PORT`
   - `LOCALLINK_DB_NAME`
   - `LOCALLINK_DB_USER`
   - `LOCALLINK_DB_PASS`

If you already have an older localhost database, import `database/upgrade_to_current.sql`. This also makes the legacy `orders.product_id` column nullable so checkout can use the current `order_items` model.

The older migration chain is still available if you want it step by step:

```sql
SOURCE database/migrations/20260613_add_user_roles_and_login_audit.sql;
SOURCE database/migrations/20260620_expand_checkout_relations.sql;
SOURCE database/migrations/20260621_add_seller_marketplace_features.sql;
```

## Main tables

- `users`
- `user_login_audit`
- `categories`
- `products`
- `product_images`
- `orders`
- `order_items`
- `order_addresses`
- `order_payments`

## Demo accounts

- Customer: `buyer@locallink.market` / `Buyer123!`
- Admin: `admin@locallink.market` / `Admin123!`

## Role behavior

- Public registration always creates `role = 'buyer'`.
- Admin access requires `role = 'admin'` and `status = 'active'`.
- Login redirects buyers to `buyer-dashboard.php` and admins to `admin/dashboard.php`.
- Disabled accounts cannot sign in.

## Notes

- If MySQL is not available, sample data is used so the pages still render.
- Session files are stored in `tmp/sessions`.
- Product images are local placeholder assets in `assets/images/` and a primary image row is stored in `product_images`.
