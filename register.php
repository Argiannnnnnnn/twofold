<?php require 'includes/config.php'; if(is_staff())go(BASE.'/staff/index.php'); $err=[]; $v=['name'=>'','email'=>'','phone'=>'']; $now=time();
function pend($pdo,$em){$s=$pdo->prepare('SELECT * FROM pending_registrations WHERE email=?');$s->execute([$em]);return $s->fetch();}
function locked($p,$now){return $p&&$p['locked_until']&&strtotime($p['locked_until'])>$now;}
if(isset($_GET['reset'])){unset($_SESSION['pending_email'],$_SESSION['demo_code']);go('register.php');}
if($_SERVER['REQUEST_METHOD']==='POST'){csrf_check();$a=$_POST['action']??'';
 if($a==='register'){
  $v=['name'=>trim($_POST['name']??''),'email'=>strtolower(trim($_POST['email']??'')),'phone'=>trim($_POST['phone']??'')];$pw=$_POST['password']??'';
  if($v['name']==='')$err[]='Enter your full name.';
  if(!filter_var($v['email'],FILTER_VALIDATE_EMAIL))$err[]='Enter a valid email address.';
  if(!preg_match('/^[0-9+\- ]{7,20}$/',$v['phone']))$err[]='Enter a valid phone number.';
  if(strlen($pw)<8)$err[]='Password needs at least 8 characters.';
  if($pw!==($_POST['confirm']??''))$err[]='Passwords do not match.';
  if(empty($_POST['terms']))$err[]='Agree to the ordering terms and privacy policy to continue.';
  if(!$err){$s=$pdo->prepare('SELECT id FROM users WHERE email=?');$s->execute([$v['email']]);
   if($s->fetch())$err[]='That email already has an account. Sign in instead.';
   else{if(!locked(pend($pdo,$v['email']),$now)){
     $code=str_pad((string)random_int(0,999999),6,'0',STR_PAD_LEFT);
     $pdo->prepare('REPLACE INTO pending_registrations(email,name,phone,password_hash,code_hash,attempts,locked_until) VALUES(?,?,?,?,?,0,NULL)')
      ->execute([$v['email'],$v['name'],$v['phone'],password_hash($pw,PASSWORD_DEFAULT),password_hash($code,PASSWORD_DEFAULT)]);
     $_SESSION['demo_code']=$code; if(!DEV_SHOW_CODE)@mail($v['email'],'Your Twofold verification code','Your verification code is '.$code);}
    $_SESSION['pending_email']=$v['email'];go('register.php');}}
 }elseif($a==='verify'){
  $em=$_SESSION['pending_email']??'';$p=$em?pend($pdo,$em):null;
  if(!$p||locked($p,$now))go('register.php');
  if(password_verify(preg_replace('/\D/','',$_POST['code']??''),$p['code_hash'])){
   try{$pdo->beginTransaction();
    $pdo->prepare('INSERT INTO users(name,email,phone,password_hash) VALUES(?,?,?,?)')->execute([$p['name'],$p['email'],$p['phone'],$p['password_hash']]);$uid=$pdo->lastInsertId();
    $pdo->prepare('DELETE FROM pending_registrations WHERE id=?')->execute([$p['id']]);$pdo->commit();
   }catch(PDOException $x){$pdo->rollBack();go('register.php?reset=1');}
   session_regenerate_id(true);$_SESSION['user_id']=(int)$uid;$_SESSION['user_name']=$p['name'];$_SESSION['reg_done']=1;
   unset($_SESSION['pending_email'],$_SESSION['demo_code']);go('register.php');}
  $n=$p['attempts']+1;
  if($n>=3)$pdo->prepare('UPDATE pending_registrations SET attempts=?,locked_until=? WHERE id=?')->execute([$n,date('Y-m-d H:i:s',$now+300),$p['id']]);
  else{$pdo->prepare('UPDATE pending_registrations SET attempts=? WHERE id=?')->execute([$n,$p['id']]);$_SESSION['flash']='That code is incorrect. '.(3-$n).' attempt'.(3-$n==1?'':'s').' left.';}
  go('register.php');}
}
$state='form';$left=0;
if(isset($_SESSION['reg_done'])){$state='success';unset($_SESSION['reg_done']);}
else{$em=$_SESSION['pending_email']??'';$p=$em?pend($pdo,$em):null;
 if($p&&$p['locked_until']){
  if(locked($p,$now)){$state='locked';$left=(int)ceil((strtotime($p['locked_until'])-$now)/60);}
  else{$pdo->prepare('DELETE FROM pending_registrations WHERE id=?')->execute([$p['id']]);unset($_SESSION['pending_email']);}
 }elseif($p)$state='verify';
 elseif($em)unset($_SESSION['pending_email']);
 if($state==='form'&&!empty($_SESSION['user_id']))go(BASE.'/index.php');}
$flash=$_SESSION['flash']??'';unset($_SESSION['flash']);
$title='Create account — Twofold Coffee'; $authPage=true; require 'includes/header.php'; ?>
<main class="awrap"><a class="back" href="<?=BASE?>/index.php#menu">← Back to the menu</a>
<div class="agrid"><?php $l1='Good coffee.';$l2='Better together';$sub='A familiar face or a first-time visitor — there’s room for you at Twofold Coffee.'; require 'includes/auth_side.php'; ?>
<section class="acard"><small class="eyebrow">YOUR TWOFOLD ACCOUNT</small>
<?php if($state==='form'): ?><h2>Make yourself at home<i>.</i></h2><p class="mute">Create your customer account to get started.</p>
<?php foreach($err as $m)echo '<p class="err">'.e($m).'</p>'; ?>
<form method="post"><?=csrf_field()?><input type="hidden" name="action" value="register">
<label>Full name<input name="name" value="<?=e($v['name'])?>" placeholder="Enter your full name" required></label>
<label>Email address<input type="email" name="email" value="<?=e($v['email'])?>" placeholder="Enter your email address" required></label>
<label>Phone number<input name="phone" value="<?=e($v['phone'])?>" placeholder="Enter your phone number" required></label>
<label>Password<div class="pw"><input type="password" name="password" minlength="8" placeholder="Create a password" required><button type="button">Show</button></div><small class="mute hint">For a stronger password, use a long, unique mix of letters, numbers and symbols.</small></label>
<label>Confirm password<div class="pw"><input type="password" name="confirm" placeholder="Re-enter your password" required><button type="button">Show</button></div></label>
<label class="chkrow"><input type="checkbox" name="terms" required> <span>I agree to the <a href="#" data-modal="terms">ordering terms</a> and acknowledge the <a href="#" data-modal="privacy">privacy policy</a>.</span></label>
<button class="btn orange full">Create account →</button></form>
<p class="center mute">Already have an account? <a href="login.php"><b>Sign in</b></a></p>

<?php elseif($state==='verify'): ?><h2>Check your code<i>.</i></h2><p class="mute">Enter the 6-digit verification code for <b><?=e($p['email'])?></b>.</p>
<?php if(DEV_SHOW_CODE&&!empty($_SESSION['demo_code']))echo '<p class="demo">Demo mode: no email is sent. Your code is <b>'.e($_SESSION['demo_code']).'</b></p>'; ?>
<?php if($flash)echo '<p class="err">'.e($flash).'</p>'; ?>
<form method="post" autocomplete="off"><?=csrf_field()?><input type="hidden" name="action" value="verify">
<label>Verification code<input class="code" name="code" inputmode="numeric" maxlength="6" pattern="[0-9]{6}" placeholder="••••••" required autofocus></label>
<button class="btn orange full">Verify →</button></form><p class="center mute"><a href="register.php?reset=1">Use a different email</a></p>

<?php elseif($state==='success'): ?><h2>You’re all set<i>.</i></h2><p class="ok">Your account was created successfully.</p><p class="mute">Welcome to Twofold Coffee. Your favorites are one tap away.</p>
<a class="btn orange full" href="<?=BASE?>/index.php">Go to the homepage →</a>

<?php else: ?><h2>Code not accepted<i>.</i></h2><p class="err">The verification code was incorrect 3 times.</p>
<p class="mute">For your security, please register again after <?=$left?> minute<?=$left==1?'':'s'?>.</p>
<a class="btn orange full" href="register.php?reset=1">Back to the registration form</a><?php endif; ?></section></div></main>
<?php require 'includes/footer.php'; ?>
