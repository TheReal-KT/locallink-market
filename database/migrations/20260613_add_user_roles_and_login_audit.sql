USE locallink_market;

ALTER TABLE users
  ADD COLUMN role ENUM('buyer', 'admin') NOT NULL DEFAULT 'buyer' AFTER password_hash,
  ADD COLUMN status ENUM('active', 'disabled') NOT NULL DEFAULT 'active' AFTER role,
  ADD COLUMN last_login_at TIMESTAMP NULL DEFAULT NULL AFTER is_admin;

UPDATE users
SET role = CASE WHEN is_admin = 1 THEN 'admin' ELSE 'buyer' END,
    status = 'active';

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

CREATE INDEX idx_users_role_status ON users(role, status);
CREATE INDEX idx_user_login_audit_user_created ON user_login_audit(user_id, created_at);
