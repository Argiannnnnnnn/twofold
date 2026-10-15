-- Run once in phpMyAdmin (database: twofold). Safe to re-run.
-- Drink types become: espresso, latte, americano, matcha.
USE twofold;
ALTER TABLE products ADD COLUMN IF NOT EXISTS drink_type VARCHAR(20) NOT NULL DEFAULT 'latte';
ALTER TABLE products MODIFY drink_type VARCHAR(20) NOT NULL DEFAULT 'latte';
UPDATE products SET drink_type='espresso' WHERE drink_type IN ('coffee','ube') AND name LIKE '%espresso%';
UPDATE products SET drink_type='americano' WHERE drink_type IN ('coffee','ube') AND (name LIKE '%americano%' OR name LIKE '%aerocano%');
UPDATE products SET drink_type='matcha' WHERE drink_type IN ('coffee','ube') AND name LIKE '%matcha%';
UPDATE products SET drink_type='latte' WHERE drink_type IN ('coffee','ube');
