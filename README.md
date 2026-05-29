# LocalLink Market

Responsive C2C marketplace prototype for the ITECA Deliverable 2 project.

## Current Scope

- Customer-facing home page.
- Product listing page with search and filter UI.
- Product detail page with seller trust summary.
- Register and login pages.
- Buyer dashboard.
- Seller dashboard.
- Add product page.
- Checkout mock flow.
- Admin dashboard preview for moderation and RBAC evidence.

## Design Direction

The current visual system is deliberately black, white, and grayscale only. It uses compact layouts, plain borders, restrained radius, and responsive CSS grid patterns so it can later map cleanly to PHP/MySQL implementation.

## Run Locally

```bash
php -S localhost:8000
```

Then open `http://localhost:8000`.

## Next Implementation Steps

1. Replace sample arrays in `includes/data.php` with MySQL queries.
2. Add PHP auth, sessions, and RBAC checks.
3. Build CRUD actions for products, orders, users, roles, categories, and reviews.
4. Capture desktop, tablet, and mobile screenshots for the report.
