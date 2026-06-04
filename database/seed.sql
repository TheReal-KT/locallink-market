USE locallink_market;

INSERT INTO users (id, full_name, email, password_hash, is_admin) VALUES
  (1, 'Nandi P.', 'buyer@locallink.market', 'pbkdf2_sha256$200000$9Rqg9lnpGIQ4mVGqfIpm0A==$0TcwwWQErN4W8mR3rqXsE2XnTrEBV7KftSOs85uvdPg=', 0),
  (2, 'Admin User', 'admin@locallink.market', 'pbkdf2_sha256$200000$LcEaCRQ6IWNgkoBxxSA0Cg==$4NWQj6ETMe6mm6PZp8hWeUjx9re8YHPaqudT5N9XRD8=', 1)
ON DUPLICATE KEY UPDATE
  full_name = VALUES(full_name),
  password_hash = VALUES(password_hash),
  is_admin = VALUES(is_admin);

INSERT INTO categories (id, name) VALUES
  (1, 'Phones'),
  (2, 'Fashion'),
  (3, 'Homeware'),
  (4, 'Study')
ON DUPLICATE KEY UPDATE
  name = VALUES(name);

INSERT INTO products (id, category_id, title, description, price, stock, image_path) VALUES
  (1, 1, 'Refurbished smartphone', 'Unlocked Android phone with charger included.', 2450.00, 3, 'assets/images/product-phone.svg'),
  (2, 2, 'Canvas street backpack', 'Everyday backpack with laptop sleeve and side pockets.', 380.00, 5, 'assets/images/product-bag.svg'),
  (3, 3, 'Minimal desk lamp', 'Compact desk lamp for study rooms and small offices.', 220.00, 4, 'assets/images/product-lamp.svg'),
  (4, 4, 'Accounting textbook set', 'Second-year accounting books in good condition.', 640.00, 2, 'assets/images/product-books.svg')
ON DUPLICATE KEY UPDATE
  category_id = VALUES(category_id),
  title = VALUES(title),
  description = VALUES(description),
  price = VALUES(price),
  stock = VALUES(stock),
  image_path = VALUES(image_path);

INSERT INTO orders (id, order_number, user_id, product_id, quantity, total_amount, status, delivery_method, payment_method, buyer_note, created_at) VALUES
  (1, 'LLM-1038', 1, 2, 1, 380.00, 'paid', 'standard_delivery', 'eft', 'Please message before delivery.', '2026-05-29 10:15:00'),
  (2, 'LLM-1031', 1, 3, 1, 220.00, 'completed', 'collection', 'cash', 'Collecting after class.', '2026-05-28 09:00:00')
ON DUPLICATE KEY UPDATE
  product_id = VALUES(product_id),
  quantity = VALUES(quantity),
  total_amount = VALUES(total_amount),
  status = VALUES(status),
  delivery_method = VALUES(delivery_method),
  payment_method = VALUES(payment_method),
  buyer_note = VALUES(buyer_note);
