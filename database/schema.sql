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
