<?php // Run ONCE in the browser AFTER importing sql/cms_upgrade.sql, then DELETE this file.
require 'includes/config.php';
$s=$pdo->query('SELECT COUNT(*) c FROM staff')->fetch();
if($s['c']==0){$pdo->prepare('INSERT INTO staff(username,password_hash,full_name,role) VALUES(?,?,?,?)')->execute(['admin',password_hash('ChangeMe123!',PASSWORD_DEFAULT),'Shop Admin','Manager']);echo 'Created admin / ChangeMe123! — sign in at /staff/login.php, change the password in Staff, then delete this file.';}
else echo 'Staff already exists. Delete this file.';
