<?php require_once __DIR__.'/config.php'; $n=cart_count(); $authPage=$authPage??false; ?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=e($title??'Twofold Coffee')?></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?=BASE?>/assets/style.css"><?=$headExtra??''?></head><body>
<header class="nav"><a class="logo" href="<?=BASE?>/index.php" aria-label="Twofold Coffee home"><?php if($lg=logo_url()): ?><img src="<?=$lg?>" alt="Twofold Coffee, Paniqui Tarlac"><?php else: ?><b>TWOFOLD</b><small>COFFEE · PANIQUI, TARLAC</small><?php endif; ?></a>
<nav><a href="<?=BASE?>/index.php">Home</a><a href="<?=BASE?>/index.php#menu">Our menu</a><a href="<?=BASE?>/index.php#story">Our story</a><a href="<?=MAPS_URL?>" target="_blank" rel="noopener">Visit us</a></nav>
<div class="acct">
<?php if($authPage): ?>
<?php elseif(!empty($_SESSION['user_id'])): ?><a class="myo" href="<?=BASE?>/orders.php">My orders</a><span class="hi">Hi, <?=e($_SESSION['user_name'])?></span><a class="btn light" href="<?=BASE?>/logout.php">Sign out</a>
<?php elseif(!empty($_SESSION['staff_id'])): ?><a class="btn light" href="<?=BASE?>/staff/index.php">Staff dashboard</a>
<?php else: ?><a class="btn light" href="<?=BASE?>/login.php">Sign in</a><a class="btn orange" href="<?=BASE?>/register.php">Create account</a><?php endif; ?>
<?php if(empty($_SESSION['staff_id'])): ?><a class="btn orange" href="<?=BASE?>/cart.php"><?= $n ? 'Your bag · '.$n : 'Order coffee' ?> <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M5 8h14l-1 12H6L5 8z"/><path d="M9 8a3 3 0 0 1 6 0"/></svg></a><?php endif; ?></div></header>
