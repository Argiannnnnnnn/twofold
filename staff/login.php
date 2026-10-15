<?php require '../includes/config.php'; if(is_customer())go(BASE.'/index.php'); if(is_staff())go(BASE.'/staff/index.php'); $err='';
if($_SERVER['REQUEST_METHOD']==='POST'){csrf_check();$s=$pdo->prepare('SELECT * FROM staff WHERE username=?');$s->execute([trim($_POST['username']??'')]);$u=$s->fetch();
 if($u&&password_verify($_POST['password']??'',$u['password_hash'])&&(int)($u['active']??1)){session_regenerate_id(true);$_SESSION['staff_id']=(int)$u['id'];go(BASE.'/staff/index.php');}
 $err='Username or password is incorrect.';}
$title='Staff sign in — Twofold Coffee'; require '../includes/header.php'; ?>
<main class="auth"><h2>Staff sign in</h2><?php if($err)echo '<p class="err">'.e($err).'</p>'; ?>
<form method="post"><?=csrf_field()?><label>Username<input name="username" required></label><label>Password<input type="password" name="password" required></label><button class="btn orange">Sign in</button></form></main>
<?php require '../includes/footer.php'; ?>
