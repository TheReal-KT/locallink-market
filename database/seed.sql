USE locallink_market;

INSERT INTO users (id, full_name, email, password_hash, role, status, is_admin) VALUES
  (1, 'Nandi P.', 'buyer@locallink.market', 'pbkdf2_sha256$200000$9Rqg9lnpGIQ4mVGqfIpm0A==$0TcwwWQErN4W8mR3rqXsE2XnTrEBV7KftSOs85uvdPg=', 'buyer', 'active', 0),
  (2, 'Admin User', 'admin@locallink.market', 'pbkdf2_sha256$200000$LcEaCRQ6IWNgkoBxxSA0Cg==$4NWQj6ETMe6mm6PZp8hWeUjx9re8YHPaqudT5N9XRD8=', 'admin', 'active', 1)
ON DUPLICATE KEY UPDATE
  full_name = VALUES(full_name),
  password_hash = VALUES(password_hash),
  role = VALUES(role),
  status = VALUES(status),
  is_admin = VALUES(is_admin);

INSERT INTO categories (id, name) VALUES
  (1, 'Phones'),
  (2, 'Fashion'),
  (3, 'Homeware'),
  (4, 'Study')
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO products (id, category_id, title, description, price, stock, status, image_path) VALUES
  (1, 1, 'Refurbished smartphone', 'Unlocked Android phone with charger included.', 2450.00, 3, 'active', 'assets/images/product-phone.svg'),
  (2, 2, 'Canvas street backpack', 'Everyday backpack with laptop sleeve and side pockets.', 380.00, 5, 'active', 'assets/images/product-bag.svg'),
  (3, 3, 'Minimal desk lamp', 'Compact desk lamp for study rooms and small offices.', 220.00, 4, 'active', 'assets/images/product-lamp.svg'),
  (4, 4, 'Accounting textbook set', 'Second-year accounting books in good condition.', 640.00, 2, 'active', 'assets/images/product-books.svg')
ON DUPLICATE KEY UPDATE
  category_id = VALUES(category_id),
  title = VALUES(title),
  description = VALUES(description),
  price = VALUES(price),
  stock = VALUES(stock),
  status = VALUES(status),
  image_path = VALUES(image_path);

INSERT INTO product_images (id, product_id, image_path, is_primary, sort_order) VALUES
  (1, 1, 'assets/images/product-phone.svg', 1, 1),
  (2, 2, 'assets/images/product-bag.svg', 1, 1),
  (3, 3, 'assets/images/product-lamp.svg', 1, 1),
  (4, 4, 'assets/images/product-books.svg', 1, 1)
ON DUPLICATE KEY UPDATE
  image_path = VALUES(image_path),
  is_primary = VALUES(is_primary),
  sort_order = VALUES(sort_order);

INSERT INTO orders (id, order_number, user_id, status, payment_status, subtotal_amount, delivery_fee, total_amount, delivery_method, buyer_note, created_at) VALUES
  (1, 'LLM-1038', 1, 'paid', 'paid', 380.00, 45.00, 425.00, 'standard_delivery', 'Please message before delivery.', '2026-05-29 10:15:00'),
  (2, 'LLM-1031', 1, 'completed', 'pending', 220.00, 0.00, 220.00, 'collection', 'Collecting after class.', '2026-05-28 09:00:00')
ON DUPLICATE KEY UPDATE
  user_id = VALUES(user_id),
  status = VALUES(status),
  payment_status = VALUES(payment_status),
  subtotal_amount = VALUES(subtotal_amount),
  delivery_fee = VALUES(delivery_fee),
  total_amount = VALUES(total_amount),
  delivery_method = VALUES(delivery_method),
  buyer_note = VALUES(buyer_note),
  created_at = VALUES(created_at);

INSERT INTO order_items (id, order_id, product_id, quantity, unit_price, line_total, created_at) VALUES
  (1, 1, 2, 1, 380.00, 380.00, '2026-05-29 10:15:00'),
  (2, 2, 3, 1, 220.00, 220.00, '2026-05-28 09:00:00')
ON DUPLICATE KEY UPDATE
  product_id = VALUES(product_id),
  quantity = VALUES(quantity),
  unit_price = VALUES(unit_price),
  line_total = VALUES(line_total),
  created_at = VALUES(created_at);

INSERT INTO order_addresses (id, order_id, contact_name, phone_number, address_line_1, address_line_2, city, postal_code, collection_note, created_at) VALUES
  (1, 1, 'Nandi P.', '0812345678', '12 Orange Street', 'Room 4', 'Johannesburg', '2000', NULL, '2026-05-29 10:15:00'),
  (2, 2, 'Nandi P.', '0812345678', NULL, NULL, NULL, NULL, 'Collect after 16:00.', '2026-05-28 09:00:00')
ON DUPLICATE KEY UPDATE
  contact_name = VALUES(contact_name),
  phone_number = VALUES(phone_number),
  address_line_1 = VALUES(address_line_1),
  address_line_2 = VALUES(address_line_2),
  city = VALUES(city),
  postal_code = VALUES(postal_code),
  collection_note = VALUES(collection_note),
  created_at = VALUES(created_at);

INSERT INTO order_payments (id, order_id, payment_method, payment_status, provider_reference, paid_at, created_at) VALUES
  (1, 1, 'card', 'paid', 'SIM-CARD-103801', '2026-05-29 10:15:00', '2026-05-29 10:15:00'),
  (2, 2, 'cash', 'pending', 'SIM-CASH-103102', NULL, '2026-05-28 09:00:00')
ON DUPLICATE KEY UPDATE
  payment_method = VALUES(payment_method),
  payment_status = VALUES(payment_status),
  provider_reference = VALUES(provider_reference),
  paid_at = VALUES(paid_at),
  created_at = VALUES(created_at);

