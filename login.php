<?php require 'includes/config.php'; if(is_staff())go(BASE.'/staff/index.php'); if(is_customer())go(BASE.'/index.php'); $err='';
$nx=$_GET['next']??$_POST['next']??'';$next=in_array($nx,['cart.php','orders.php'],true)?$nx:'index.php';
if($_SERVER['REQUEST_METHOD']==='POST'){csrf_check();
 $s=$pdo->prepare('SELECT * FROM users WHERE email=?');$s->execute([strtolower(trim($_POST['email']??''))]);$u=$s->fetch();
 if($u&&password_verify($_POST['password']??'',$u['password_hash'])&&(int)($u['active']??1)){
  if(password_needs_rehash($u['password_hash'],PASSWORD_DEFAULT))$pdo->prepare('UPDATE users SET password_hash=? WHERE id=?')->execute([password_hash($_POST['password'],PASSWORD_DEFAULT),$u['id']]);
  session_regenerate_id(true);$_SESSION['user_id']=(int)$u['id'];$_SESSION['user_name']=$u['name'];
  if(!empty($_POST['remember']))setcookie(session_name(),session_id(),['expires'=>time()+2592000,'path'=>'/','httponly'=>true,'samesite'=>'Lax']);
  go(BASE.'/'.$next);}
 $err='Email or password is incorrect, or the account is inactive.';}
$title='Sign in — Twofold Coffee'; $authPage=true; require 'includes/header.php'; ?>
<main class="awrap"><a class="back" href="<?=BASE?>/index.php#menu">← Back to the menu</a>
<div class="agrid"><?php $l1='Your daily pause.';$l2='Your kind of cup';$sub='From your first sip before work to a catch-up with a friend, welcome back to Twofold Coffee.'; require 'includes/auth_side.php'; ?>
<section class="acard"><small class="eyebrow">YOUR TWOFOLD ACCOUNT</small><h2>Welcome back<i>.</i></h2><p class="mute">Sign in to your customer account.</p>
<?php if($err)echo '<p class="err">'.e($err).'</p>'; ?>
<form method="post"><?=csrf_field()?><input type="hidden" name="next" value="<?=e($next)?>">
<label>Email address<input type="email" name="email" placeholder="Enter your email address" required></label>
<label>Password<div class="pw"><input type="password" name="password" placeholder="Enter your password" required><button type="button">Show</button></div></label>
<label class="chkrow"><input type="checkbox" name="remember"> Remember me</label>
<button class="btn orange full">Sign in →</button></form>
<p class="center mute">New to Twofold? <a href="register.php"><b>Create an account</b></a></p></section></div></main>
<?php require 'includes/footer.php'; ?>
