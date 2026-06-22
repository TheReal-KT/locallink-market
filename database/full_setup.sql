CREATE DATABASE IF NOT EXISTS locallink_market
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE locallink_market;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(120) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('buyer', 'seller', 'admin') NOT NULL DEFAULT 'buyer',
  status ENUM('active', 'disabled') NOT NULL DEFAULT 'active',
  is_admin TINYINT(1) NOT NULL DEFAULT 0,
  last_login_at TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS user_login_audit (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  ip_address VARCHAR(45) DEFAULT NULL,
  user_agent VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_user_login_audit_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS seller_profiles (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL UNIQUE,
  business_name VARCHAR(150) DEFAULT NULL,
  location VARCHAR(150) DEFAULT NULL,
  phone_number VARCHAR(30) DEFAULT NULL,
  bio TEXT DEFAULT NULL,
  verification_status ENUM('not_requested', 'pending', 'approved', 'rejected') NOT NULL DEFAULT 'not_requested',
  verification_notes TEXT DEFAULT NULL,
  requested_at TIMESTAMP NULL DEFAULT NULL,
  reviewed_at TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_seller_profiles_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS products (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  seller_id INT UNSIGNED DEFAULT NULL,
  category_id INT UNSIGNED NOT NULL,
  title VARCHAR(150) NOT NULL,
  description TEXT NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  stock INT UNSIGNED NOT NULL DEFAULT 0,
  status ENUM('active', 'inactive', 'pending_review', 'archived') NOT NULL DEFAULT 'active',
  image_path VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_products_category
    FOREIGN KEY (category_id) REFERENCES categories(id),
  CONSTRAINT fk_products_seller
    FOREIGN KEY (seller_id) REFERENCES users(id)
    ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS product_images (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id INT UNSIGNED NOT NULL,
  image_path VARCHAR(255) NOT NULL,
  is_primary TINYINT(1) NOT NULL DEFAULT 0,
  sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_product_images_product
    FOREIGN KEY (product_id) REFERENCES products(id)
    ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS orders (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_number VARCHAR(20) NOT NULL UNIQUE,
  user_id INT UNSIGNED NOT NULL,
  seller_id INT UNSIGNED DEFAULT NULL,
  status ENUM('pending', 'paid', 'processing', 'ready', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
  payment_status ENUM('pending', 'awaiting_confirmation', 'paid', 'failed') NOT NULL DEFAULT 'pending',
  subtotal_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  delivery_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  total_amount DECIMAL(10,2) NOT NULL,
  delivery_method ENUM('collection', 'standard_delivery', 'express_delivery') NOT NULL DEFAULT 'collection',
  buyer_note TEXT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_orders_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_orders_seller
    FOREIGN KEY (seller_id) REFERENCES users(id)
    ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS order_items (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  quantity INT UNSIGNED NOT NULL DEFAULT 1,
  unit_price DECIMAL(10,2) NOT NULL,
  line_total DECIMAL(10,2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_order_items_order
    FOREIGN KEY (order_id) REFERENCES orders(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_order_items_product
    FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE IF NOT EXISTS order_addresses (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NOT NULL,
  contact_name VARCHAR(120) NOT NULL,
  phone_number VARCHAR(30) NOT NULL,
  address_line_1 VARCHAR(160) DEFAULT NULL,
  address_line_2 VARCHAR(160) DEFAULT NULL,
  city VARCHAR(120) DEFAULT NULL,
  postal_code VARCHAR(20) DEFAULT NULL,
  collection_note VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_order_addresses_order (order_id),
  CONSTRAINT fk_order_addresses_order
    FOREIGN KEY (order_id) REFERENCES orders(id)
    ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS order_payments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NOT NULL,
  payment_method ENUM('eft', 'cash', 'card') NOT NULL DEFAULT 'eft',
  payment_status ENUM('pending', 'awaiting_confirmation', 'paid', 'failed') NOT NULL DEFAULT 'pending',
  provider_reference VARCHAR(60) DEFAULT NULL,
  paid_at TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_order_payments_order (order_id),
  CONSTRAINT fk_order_payments_order
    FOREIGN KEY (order_id) REFERENCES orders(id)
    ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS reviews (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NOT NULL UNIQUE,
  reviewer_id INT UNSIGNED NOT NULL,
  seller_id INT UNSIGNED NOT NULL,
  rating TINYINT UNSIGNED NOT NULL,
  comment TEXT DEFAULT NULL,
  status ENUM('visible', 'hidden') NOT NULL DEFAULT 'visible',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_reviews_order
    FOREIGN KEY (order_id) REFERENCES orders(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_reviews_reviewer
    FOREIGN KEY (reviewer_id) REFERENCES users(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_reviews_seller
    FOREIGN KEY (seller_id) REFERENCES users(id)
    ON DELETE CASCADE,
  CONSTRAINT chk_reviews_rating CHECK (rating BETWEEN 1 AND 5)
);

CREATE TABLE IF NOT EXISTS admin_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  admin_id INT UNSIGNED NOT NULL,
  action VARCHAR(150) NOT NULL,
  entity_type VARCHAR(80) DEFAULT NULL,
  entity_id INT UNSIGNED DEFAULT NULL,
  details TEXT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_admin_logs_admin
    FOREIGN KEY (admin_id) REFERENCES users(id)
    ON DELETE CASCADE
);

CREATE INDEX idx_users_role_status ON users(role, status);
CREATE INDEX idx_user_login_audit_user_created ON user_login_audit(user_id, created_at);
CREATE INDEX idx_seller_profiles_status ON seller_profiles(verification_status, requested_at);
CREATE INDEX idx_products_category_status ON products(category_id, status);
CREATE INDEX idx_products_seller_status ON products(seller_id, status);
CREATE INDEX idx_product_images_product_primary ON product_images(product_id, is_primary, sort_order);
CREATE INDEX idx_orders_user_status ON orders(user_id, status);
CREATE INDEX idx_orders_seller_status ON orders(seller_id, status);
CREATE INDEX idx_orders_payment_status ON orders(payment_status);
CREATE INDEX idx_order_items_order ON order_items(order_id);
CREATE INDEX idx_order_items_product ON order_items(product_id);
CREATE INDEX idx_reviews_seller_status ON reviews(seller_id, status);
CREATE INDEX idx_admin_logs_admin_created ON admin_logs(admin_id, created_at);

USE locallink_market;

INSERT INTO users (id, full_name, email, password_hash, role, status, is_admin) VALUES
  (1, 'Nandi P.', 'buyer@locallink.market', 'pbkdf2_sha256$200000$9Rqg9lnpGIQ4mVGqfIpm0A==$0TcwwWQErN4W8mR3rqXsE2XnTrEBV7KftSOs85uvdPg=', 'buyer', 'active', 0),
  (2, 'Admin User', 'admin@locallink.market', 'pbkdf2_sha256$200000$LcEaCRQ6IWNgkoBxxSA0Cg==$4NWQj6ETMe6mm6PZp8hWeUjx9re8YHPaqudT5N9XRD8=', 'admin', 'active', 1),
  (3, 'Anele Mokoena', 'seller@locallink.market', 'pbkdf2_sha256$200000$8H3xWtbRQC0m8AK02FsaXA==$BMLy5H34dZq1jDUxqDqSpq9zywh7R4YJkLPrDqYB5aQ=', 'seller', 'active', 0)
ON DUPLICATE KEY UPDATE
  full_name = VALUES(full_name),
  password_hash = VALUES(password_hash),
  role = VALUES(role),
  status = VALUES(status),
  is_admin = VALUES(is_admin);

INSERT INTO seller_profiles (id, user_id, business_name, location, phone_number, bio, verification_status, verification_notes, requested_at, reviewed_at) VALUES
  (1, 3, 'Anele Finds', 'Soweto, Johannesburg', '0825550138', 'Affordable study, fashion, and tech items sourced from local sellers.', 'approved', 'Approved for demo seller access.', '2026-06-10 09:00:00', '2026-06-10 10:00:00')
ON DUPLICATE KEY UPDATE
  business_name = VALUES(business_name),
  location = VALUES(location),
  phone_number = VALUES(phone_number),
  bio = VALUES(bio),
  verification_status = VALUES(verification_status),
  verification_notes = VALUES(verification_notes),
  requested_at = VALUES(requested_at),
  reviewed_at = VALUES(reviewed_at);

INSERT INTO categories (id, name) VALUES
  (1, 'Phones'),
  (2, 'Fashion'),
  (3, 'Homeware'),
  (4, 'Study')
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO products (id, seller_id, category_id, title, description, price, stock, status, image_path) VALUES
  (1, 3, 1, 'Refurbished smartphone', 'Unlocked Android phone with charger included.', 2450.00, 3, 'active', 'assets/images/product-phone.jpg'),
  (2, 3, 2, 'Canvas street backpack', 'Everyday backpack with laptop sleeve and side pockets.', 380.00, 5, 'active', 'assets/images/product-bag.jpg'),
  (3, 3, 3, 'Minimal desk lamp', 'Compact desk lamp for study rooms and small offices.', 220.00, 4, 'active', 'assets/images/product-lamp.jpg'),
  (4, 3, 4, 'Accounting textbook set', 'Second-year accounting books in good condition.', 640.00, 2, 'active', 'assets/images/product-books.jpg')
ON DUPLICATE KEY UPDATE
  seller_id = VALUES(seller_id),
  category_id = VALUES(category_id),
  title = VALUES(title),
  description = VALUES(description),
  price = VALUES(price),
  stock = VALUES(stock),
  status = VALUES(status),
  image_path = VALUES(image_path);

INSERT INTO product_images (id, product_id, image_path, is_primary, sort_order) VALUES
  (1, 1, 'assets/images/product-phone.jpg', 1, 1),
  (2, 2, 'assets/images/product-bag.jpg', 1, 1),
  (3, 3, 'assets/images/product-lamp.jpg', 1, 1),
  (4, 4, 'assets/images/product-books.jpg', 1, 1)
ON DUPLICATE KEY UPDATE
  image_path = VALUES(image_path),
  is_primary = VALUES(is_primary),
  sort_order = VALUES(sort_order);

INSERT INTO orders (id, order_number, user_id, seller_id, status, payment_status, subtotal_amount, delivery_fee, total_amount, delivery_method, buyer_note, created_at) VALUES
  (1, 'LLM-1038', 1, 3, 'paid', 'paid', 380.00, 45.00, 425.00, 'standard_delivery', 'Please message before delivery.', '2026-05-29 10:15:00'),
  (2, 'LLM-1031', 1, 3, 'completed', 'pending', 220.00, 0.00, 220.00, 'collection', 'Collecting after class.', '2026-05-28 09:00:00')
ON DUPLICATE KEY UPDATE
  user_id = VALUES(user_id),
  seller_id = VALUES(seller_id),
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

INSERT INTO reviews (id, order_id, reviewer_id, seller_id, rating, comment, status, created_at) VALUES
  (1, 2, 1, 3, 5, 'The lamp matched the listing and collection was smooth.', 'visible', '2026-05-29 15:00:00')
ON DUPLICATE KEY UPDATE
  reviewer_id = VALUES(reviewer_id),
  seller_id = VALUES(seller_id),
  rating = VALUES(rating),
  comment = VALUES(comment),
  status = VALUES(status),
  created_at = VALUES(created_at);

INSERT INTO admin_logs (id, admin_id, action, entity_type, entity_id, details, created_at) VALUES
  (1, 2, 'approved_seller', 'seller_profiles', 1, 'Approved demo seller account.', '2026-06-10 10:00:00')
ON DUPLICATE KEY UPDATE
  action = VALUES(action),
  entity_type = VALUES(entity_type),
  entity_id = VALUES(entity_id),
  details = VALUES(details),
  created_at = VALUES(created_at);

