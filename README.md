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

The current visual system uses a warm editorial commerce direction inspired by the Wonder prototype: beige surfaces, hard borders, uppercase utility typography, stronger hierarchy, and compact responsive panels that can still map cleanly to later PHP/MySQL implementation.

## Run Locally

```bash
php -S localhost:8000
```

Then open `http://localhost:8000`. Make sure PHP is installed and available on your `PATH`.

## Next Implementation Steps

1. Replace sample arrays in `includes/data.php` with MySQL queries.
2. Add PHP auth, sessions, and RBAC checks.
3. Build CRUD actions for products, orders, users, roles, categories, and reviews.
4. Capture desktop, tablet, and mobile screenshots for the report.
