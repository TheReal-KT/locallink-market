USE locallink_market;

ALTER TABLE products
  ADD COLUMN status ENUM('active', 'archived') NOT NULL DEFAULT 'active' AFTER stock;

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

ALTER TABLE orders
  ADD COLUMN payment_status ENUM('pending', 'awaiting_confirmation', 'paid', 'failed') NOT NULL DEFAULT 'pending' AFTER status,
  ADD COLUMN subtotal_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER payment_status,
  ADD COLUMN delivery_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER subtotal_amount;

UPDATE products
SET status = 'active'
WHERE status IS NULL OR status = '';

INSERT INTO product_images (product_id, image_path, is_primary, sort_order)
SELECT p.id, p.image_path, 1, 1
FROM products p
LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
WHERE p.image_path IS NOT NULL AND p.image_path <> '' AND pi.id IS NULL;

UPDATE orders
SET delivery_fee = CASE delivery_method
    WHEN 'express_delivery' THEN 85.00
    WHEN 'standard_delivery' THEN 45.00
    ELSE 0.00
  END,
  subtotal_amount = GREATEST(total_amount - CASE delivery_method
    WHEN 'express_delivery' THEN 85.00
    WHEN 'standard_delivery' THEN 45.00
    ELSE 0.00
  END, 0.00),
  payment_status = CASE payment_method
    WHEN 'card' THEN 'paid'
    WHEN 'eft' THEN 'awaiting_confirmation'
    ELSE 'pending'
  END;

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

INSERT INTO order_items (order_id, product_id, quantity, unit_price, line_total, created_at)
SELECT o.id,
       o.product_id,
       o.quantity,
       CASE WHEN o.quantity > 0 THEN ROUND(o.subtotal_amount / o.quantity, 2) ELSE 0.00 END,
       o.subtotal_amount,
       o.created_at
FROM orders o
LEFT JOIN order_items oi ON oi.order_id = o.id
WHERE oi.id IS NULL;

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

INSERT INTO order_addresses (order_id, contact_name, phone_number, collection_note, created_at)
SELECT o.id,
       u.full_name,
       'Not captured',
       o.buyer_note,
       o.created_at
FROM orders o
INNER JOIN users u ON u.id = o.user_id
LEFT JOIN order_addresses oa ON oa.order_id = o.id
WHERE oa.id IS NULL;

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

INSERT INTO order_payments (order_id, payment_method, payment_status, provider_reference, paid_at, created_at)
SELECT o.id,
       o.payment_method,
       o.payment_status,
       CONCAT('LEGACY-', o.order_number),
       CASE WHEN o.payment_status = 'paid' THEN o.created_at ELSE NULL END,
       o.created_at
FROM orders o
LEFT JOIN order_payments op ON op.order_id = o.id
WHERE op.id IS NULL;

CREATE INDEX idx_products_category_status ON products(category_id, status);
CREATE INDEX idx_product_images_product_primary ON product_images(product_id, is_primary, sort_order);
CREATE INDEX idx_orders_user_status ON orders(user_id, status);
CREATE INDEX idx_orders_payment_status ON orders(payment_status);
CREATE INDEX idx_order_items_order ON order_items(order_id);
CREATE INDEX idx_order_items_product ON order_items(product_id);
