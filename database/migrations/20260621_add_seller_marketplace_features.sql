USE locallink_market;

ALTER TABLE users
  MODIFY COLUMN role ENUM('buyer', 'seller', 'admin') NOT NULL DEFAULT 'buyer';

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
  ADD COLUMN seller_id INT UNSIGNED NULL AFTER id;

ALTER TABLE products
  MODIFY COLUMN status ENUM('active', 'inactive', 'pending_review', 'archived') NOT NULL DEFAULT 'active';

ALTER TABLE products
  ADD CONSTRAINT fk_products_seller
    FOREIGN KEY (seller_id) REFERENCES users(id)
    ON DELETE SET NULL;

ALTER TABLE products
  ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

ALTER TABLE orders
  ADD COLUMN seller_id INT UNSIGNED NULL AFTER user_id;

ALTER TABLE orders
  MODIFY COLUMN status ENUM('pending', 'paid', 'processing', 'ready', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
  MODIFY COLUMN product_id INT UNSIGNED NULL DEFAULT NULL;

ALTER TABLE orders
  ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

ALTER TABLE orders
  ADD CONSTRAINT fk_orders_seller
    FOREIGN KEY (seller_id) REFERENCES users(id)
    ON DELETE SET NULL;

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

UPDATE products p
LEFT JOIN order_items oi ON oi.product_id = p.id
LEFT JOIN orders o ON o.id = oi.order_id
SET p.seller_id = COALESCE(p.seller_id, o.seller_id)
WHERE p.seller_id IS NULL;

UPDATE orders o
LEFT JOIN order_items oi ON oi.order_id = o.id
LEFT JOIN products p ON p.id = oi.product_id
SET o.seller_id = COALESCE(o.seller_id, p.seller_id)
WHERE o.seller_id IS NULL;

CREATE INDEX idx_seller_profiles_status ON seller_profiles(verification_status, requested_at);
CREATE INDEX idx_products_seller_status ON products(seller_id, status);
CREATE INDEX idx_orders_seller_status ON orders(seller_id, status);
CREATE INDEX idx_reviews_seller_status ON reviews(seller_id, status);
CREATE INDEX idx_admin_logs_admin_created ON admin_logs(admin_id, created_at);
