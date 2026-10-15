CREATE DATABASE IF NOT EXISTS twofold CHARACTER SET utf8mb4;
USE twofold;
CREATE TABLE users(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(80) NOT NULL,email VARCHAR(120) NOT NULL UNIQUE,phone VARCHAR(30),password_hash VARCHAR(255) NOT NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE staff(id INT AUTO_INCREMENT PRIMARY KEY,username VARCHAR(50) NOT NULL UNIQUE,password_hash VARCHAR(255) NOT NULL);
CREATE TABLE products(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(100) NOT NULL,category VARCHAR(40) NOT NULL,price DECIMAL(8,2) NOT NULL,badge VARCHAR(40),is_favorite TINYINT(1) DEFAULT 0,image VARCHAR(120),available TINYINT(1) DEFAULT 1);
CREATE TABLE orders(id INT AUTO_INCREMENT PRIMARY KEY,user_id INT NOT NULL,fulfillment ENUM('pickup','delivery') NOT NULL,address VARCHAR(255),phone VARCHAR(30) NOT NULL,payment ENUM('cash','gcash') NOT NULL,total DECIMAL(9,2) NOT NULL,status ENUM('pending','preparing','ready','completed','cancelled') DEFAULT 'pending',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(user_id) REFERENCES users(id));
CREATE TABLE order_items(id INT AUTO_INCREMENT PRIMARY KEY,order_id INT NOT NULL,product_id INT,name VARCHAR(100) NOT NULL,price DECIMAL(8,2) NOT NULL,qty INT NOT NULL,FOREIGN KEY(order_id) REFERENCES orders(id) ON DELETE CASCADE);
INSERT INTO products(name,category,price,badge,is_favorite,image) VALUES
('Spanish Latte','Latte favorites',129,NULL,0,NULL),
('Iced Shaken Brown Sugar Latte','Latte favorites',129,'Neighborhood pick',1,'brown-sugar.png'),
('Seasalt Latte','Latte favorites',139,NULL,0,NULL),
('Coconut Cream Latte','Latte favorites',139,'Matcha series',1,'coconut-cream.png'),
('Strawberry Cloud Latte','Latte favorites',139,NULL,0,NULL),
('Honey Cinnamon Shaken Espresso','Latte favorites',139,NULL,0,NULL),
('Iced Americano','Americano series',109,NULL,0,NULL),
('Orange Americano','Americano series',129,'Neighborhood pick',1,'orange-americano.jpg'),
('Lychee Aerocano','Americano series',129,NULL,0,NULL),
('Matcha Latte','Matcha & ube',139,NULL,0,NULL),
('Dirty Matcha Latte','Matcha & ube',149,NULL,0,NULL),
('Strawberry Matcha','Matcha & ube',149,'Neighborhood pick',1,'strawberry-matcha.png'),
('Ube Matcha','Matcha & ube',149,NULL,0,NULL),
('Ube Latte','Matcha & ube',139,NULL,0,NULL),
('Dirty Ube Latte','Matcha & ube',149,NULL,0,NULL);
CREATE TABLE IF NOT EXISTS pending_registrations(id INT AUTO_INCREMENT PRIMARY KEY,email VARCHAR(120) NOT NULL UNIQUE,name VARCHAR(80) NOT NULL,phone VARCHAR(30),password_hash VARCHAR(255) NOT NULL,code_hash VARCHAR(255) NOT NULL,attempts INT DEFAULT 0,locked_until DATETIME NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
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
