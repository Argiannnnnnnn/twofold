-- Run once in phpMyAdmin (database: twofold). Safe to re-run. Needs XAMPP's MariaDB.
USE twofold;
ALTER TABLE products ADD COLUMN IF NOT EXISTS temperature ENUM('iced','hot','both') NOT NULL DEFAULT 'iced';
ALTER TABLE users ADD COLUMN IF NOT EXISTS active TINYINT(1) NOT NULL DEFAULT 1;
ALTER TABLE staff ADD COLUMN IF NOT EXISTS full_name VARCHAR(80) NULL;
ALTER TABLE staff ADD COLUMN IF NOT EXISTS role VARCHAR(40) NOT NULL DEFAULT 'Barista';
ALTER TABLE staff ADD COLUMN IF NOT EXISTS active TINYINT(1) NOT NULL DEFAULT 1;
ALTER TABLE orders ADD COLUMN IF NOT EXISTS prepared_by INT NULL;
ALTER TABLE orders ADD COLUMN IF NOT EXISTS rider VARCHAR(80) NULL;
ALTER TABLE orders ADD COLUMN IF NOT EXISTS payment_status ENUM('due','paid') NOT NULL DEFAULT 'due';
ALTER TABLE orders ADD COLUMN IF NOT EXISTS payment_ref VARCHAR(60) NULL;
UPDATE staff SET full_name='Shop Admin',role='Manager' WHERE full_name IS NULL;
CREATE TABLE IF NOT EXISTS inventory(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(100) NOT NULL,category VARCHAR(40) NOT NULL,unit VARCHAR(20) NOT NULL,qty DECIMAL(10,2) NOT NULL DEFAULT 0,low_at DECIMAL(10,2) NOT NULL DEFAULT 0,updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP);
INSERT INTO inventory(name,category,unit,qty,low_at)
SELECT t.a,t.b,t.c,t.d,t.e FROM (
 SELECT 'Espresso beans' a,'Coffee' b,'kg' c,8 d,2 e UNION ALL SELECT 'Fresh milk','Dairy','L',30,10
 UNION ALL SELECT 'Brown sugar syrup','Syrups','L',6,2 UNION ALL SELECT 'Matcha powder','Matcha & ube','kg',3,1
 UNION ALL SELECT 'Ube powder','Matcha & ube','kg',3,1 UNION ALL SELECT 'Strawberry puree','Syrups','L',5,2
 UNION ALL SELECT 'Coconut cream','Dairy','L',4,1 UNION ALL SELECT 'Cups (16 oz)','Packaging','pcs',300,100
 UNION ALL SELECT 'Dome lids','Packaging','pcs',300,100 UNION ALL SELECT 'Straws','Packaging','pcs',400,150) t
WHERE NOT EXISTS (SELECT 1 FROM inventory);
ALTER TABLE products ADD COLUMN IF NOT EXISTS drink_type VARCHAR(20) NOT NULL DEFAULT 'coffee';
UPDATE products SET drink_type='matcha' WHERE drink_type='coffee' AND name LIKE '%matcha%';
UPDATE products SET drink_type='ube' WHERE drink_type='coffee' AND name LIKE '%ube%';
UPDATE products SET drink_type='americano' WHERE drink_type='coffee' AND (name LIKE '%americano%' OR name LIKE '%aerocano%');
USE twofold;
ALTER TABLE products ADD COLUMN IF NOT EXISTS drink_type VARCHAR(20) NOT NULL DEFAULT 'latte';
ALTER TABLE products MODIFY drink_type VARCHAR(20) NOT NULL DEFAULT 'latte';
UPDATE products SET drink_type='espresso' WHERE drink_type IN ('coffee','ube') AND name LIKE '%espresso%';
UPDATE products SET drink_type='americano' WHERE drink_type IN ('coffee','ube') AND (name LIKE '%americano%' OR name LIKE '%aerocano%');
UPDATE products SET drink_type='matcha' WHERE drink_type IN ('coffee','ube') AND name LIKE '%matcha%';
UPDATE products SET drink_type='latte' WHERE drink_type IN ('coffee','ube');
