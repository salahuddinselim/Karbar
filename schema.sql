-- Hishab (single-store business manager) — MySQL schema
-- Run this once against an empty database:
--   mysql -u root -p hishab < schema.sql

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS users (
  id CHAR(36) PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('owner', 'staff') NOT NULL DEFAULT 'owner',
  can_manage_products TINYINT(1) NOT NULL DEFAULT 1,
  can_manage_parties TINYINT(1) NOT NULL DEFAULT 1,
  can_record_transactions TINYINT(1) NOT NULL DEFAULT 1,
  can_view_reports TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS store_settings (
  id VARCHAR(20) PRIMARY KEY DEFAULT 'singleton',
  store_name VARCHAR(255) NOT NULL DEFAULT 'My Store',
  category VARCHAR(100) NULL,
  address VARCHAR(255) NOT NULL DEFAULT '',
  phone VARCHAR(50) NOT NULL DEFAULT '',
  currency VARCHAR(10) NOT NULL DEFAULT 'BDT',
  invoice_note VARCHAR(255) NOT NULL DEFAULT 'Thank you for your business!'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS products (
  id CHAR(36) PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  sku VARCHAR(100) NULL,
  unit VARCHAR(50) NOT NULL DEFAULT 'pcs',
  category VARCHAR(100) NULL,
  cost_price DECIMAL(12,2) NOT NULL DEFAULT 0,
  sell_price DECIMAL(12,2) NOT NULL DEFAULT 0,
  stock_qty DECIMAL(12,2) NOT NULL DEFAULT 0,
  low_stock_at DECIMAL(12,2) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_products_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS parties (
  id CHAR(36) PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  phone VARCHAR(50) NULL,
  type ENUM('CUSTOMER', 'SUPPLIER') NOT NULL,
  opening_balance DECIMAL(12,2) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_parties_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS transactions (
  id CHAR(36) PRIMARY KEY,
  type ENUM('SALE', 'PURCHASE', 'EXPENSE') NOT NULL,
  date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  party_id CHAR(36) NULL,
  description VARCHAR(500) NULL,
  subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
  total DECIMAL(12,2) NOT NULL DEFAULT 0,
  paid_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  due_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  payment_method VARCHAR(50) NOT NULL DEFAULT 'cash',
  receipt_image_path VARCHAR(255) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_transactions_party FOREIGN KEY (party_id) REFERENCES parties(id) ON DELETE SET NULL,
  INDEX idx_transactions_type_date (type, date),
  INDEX idx_transactions_party (party_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS transaction_items (
  id CHAR(36) PRIMARY KEY,
  transaction_id CHAR(36) NOT NULL,
  product_id CHAR(36) NULL,
  name VARCHAR(255) NOT NULL,
  qty DECIMAL(12,2) NOT NULL,
  unit_price DECIMAL(12,2) NOT NULL,
  subtotal DECIMAL(12,2) NOT NULL,
  CONSTRAINT fk_items_transaction FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE CASCADE,
  CONSTRAINT fk_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS payments (
  id CHAR(36) PRIMARY KEY,
  party_id CHAR(36) NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  direction ENUM('IN', 'OUT') NOT NULL,
  date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  note VARCHAR(255) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_payments_party FOREIGN KEY (party_id) REFERENCES parties(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS ledger_entries (
  id CHAR(36) PRIMARY KEY,
  party_id CHAR(36) NOT NULL,
  transaction_id CHAR(36) NULL,
  payment_id CHAR(36) NULL,
  date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  debit DECIMAL(12,2) NOT NULL DEFAULT 0,
  credit DECIMAL(12,2) NOT NULL DEFAULT 0,
  note VARCHAR(255) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_ledger_party FOREIGN KEY (party_id) REFERENCES parties(id) ON DELETE CASCADE,
  CONSTRAINT fk_ledger_transaction FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE SET NULL,
  CONSTRAINT fk_ledger_payment FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE SET NULL,
  INDEX idx_ledger_party_date (party_id, date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
