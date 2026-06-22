CREATE DATABASE IF NOT EXISTS locallink_market
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE locallink_market;

ALTER TABLE users
  ADD COLUMN IF NOT EXISTS role ENUM('buyer', 'seller', 'admin') NOT NULL DEFAULT 'buyer' AFTER password_hash,
  ADD COLUMN IF NOT EXISTS status ENUM('active', 'disabled') NOT NULL DEFAULT 'active' AFTER role,
  ADD COLUMN IF NOT EXISTS last_login_at TIMESTAMP NULL DEFAULT NULL AFTER is_admin;

ALTER TABLE users
  MODIFY COLUMN role ENUM('buyer', 'seller', 'admin') NOT NULL DEFAULT 'buyer';

UPDATE users
SET role = CASE
      WHEN is_admin = 1 THEN 'admin'
      WHEN role IS NULL OR role = '' THEN 'buyer'
      ELSE role
    END,
    status = CASE
      WHEN status IS NULL OR status = '' THEN 'active'
      ELSE status
    END;

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

ALTER TABLE products
  ADD COLUMN IF NOT EXISTS seller_id INT UNSIGNED DEFAULT NULL AFTER id,
  ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

ALTER TABLE products
  MODIFY COLUMN status ENUM('active', 'inactive', 'pending_review', 'archived') NOT NULL DEFAULT 'active';

ALTER TABLE orders
  ADD COLUMN IF NOT EXISTS seller_id INT UNSIGNED DEFAULT NULL AFTER user_id,
  ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

ALTER TABLE orders
  MODIFY COLUMN status ENUM('pending', 'paid', 'processing', 'ready', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
  MODIFY COLUMN product_id INT UNSIGNED NULL DEFAULT NULL;

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

INSERT INTO users (id, full_name, email, password_hash, role, status, is_admin)
SELECT 3, 'Anele Mokoena', 'seller@locallink.market', 'pbkdf2_sha256$200000$8H3xWtbRQC0m8AK02FsaXA==$BMLy5H34dZq1jDUxqDqSpq9zywh7R4YJkLPrDqYB5aQ=', 'seller', 'active', 0
FROM DUAL
WHERE NOT EXISTS (
  SELECT 1
  FROM users
  WHERE email = 'seller@locallink.market'
);

UPDATE products
SET seller_id = (
  SELECT u.id
  FROM users u
  WHERE u.email = 'seller@locallink.market'
  LIMIT 1
)
WHERE seller_id IS NULL;

UPDATE orders o
LEFT JOIN order_items oi ON oi.order_id = o.id
LEFT JOIN products p ON p.id = oi.product_id
SET o.seller_id = COALESCE(o.seller_id, p.seller_id)
WHERE o.seller_id IS NULL;

INSERT INTO seller_profiles (user_id, business_name, location, phone_number, bio, verification_status, verification_notes, requested_at, reviewed_at)
SELECT u.id, 'Anele Finds', 'Soweto, Johannesburg', '0825550138', 'Affordable study, fashion, and tech items sourced from local sellers.', 'approved', 'Approved for demo seller access.', '2026-06-10 09:00:00', '2026-06-10 10:00:00'
FROM users u
WHERE u.email = 'seller@locallink.market'
  AND NOT EXISTS (
    SELECT 1
    FROM seller_profiles sp
    WHERE sp.user_id = u.id
  );

UPDATE products
SET image_path = REPLACE(image_path, '.svg', '.jpg')
WHERE image_path LIKE 'assets/images/%.svg';

UPDATE product_images
SET image_path = REPLACE(image_path, '.svg', '.jpg')
WHERE image_path LIKE 'assets/images/%.svg';

