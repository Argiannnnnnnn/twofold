<?php require 'includes/config.php'; if(is_staff())go(BASE.'/staff/index.php');
if(empty($_SESSION['user_id']))go(BASE.'/login.php?next=orders.php');
$s=$pdo->prepare('SELECT * FROM orders WHERE user_id=? ORDER BY id DESC LIMIT 30');$s->execute([$_SESSION['user_id']]);$orders=$s->fetchAll();
$it=$pdo->prepare('SELECT name,price,qty FROM order_items WHERE order_id=?');
$active=false;foreach($orders as $o)if(in_array($o['status'],['pending','preparing','ready'],true))$active=true;
$headExtra=$active?'<meta http-equiv="refresh" content="30">':'';
$title='My orders — Twofold Coffee';$simpleFooter=true;require 'includes/header.php'; ?>
<main class="auth wide"><h2>My orders<i class="o">.</i></h2>
<p class="mute">Follow your order from preparation to handoff.<?=$active?' This page refreshes on its own.':''?></p>
<?php if(!$orders): ?><p>You haven’t placed an order yet.</p><a class="btn orange" href="cart.php">Order coffee</a><?php endif; ?>
<?php foreach($orders as $o): $it->execute([$o['id']]);$items=$it->fetchAll();
 $steps=['pending'=>'Received','preparing'=>'Preparing','ready'=>$o['fulfillment']==='pickup'?'Ready for pickup':'On the way','completed'=>'Completed'];
 $idx=array_search($o['status'],array_keys($steps)); ?>
<article class="ocard"><div class="between"><div><b>Order #<?=$o['id']?></b><div class="tiny mute"><?=e(date('M j, Y · g:i A',strtotime($o['created_at'])))?> · <?=e(ucfirst($o['fulfillment']))?> · <?=$o['payment']==='gcash'?'GCash':'Cash'?></div></div>
<span class="stat s-<?=e($o['status'])?>"><?=$o['status']==='cancelled'?'Cancelled':e($steps[$o['status']])?></span></div>
<?php if($o['status']==='cancelled'): ?><p class="tiny mute">This order was cancelled. Contact the shop if you have questions.</p>
<?php else: ?><ol class="trk"><?php $i=0;foreach($steps as $lbl): ?><li class="<?=$i<$idx?'done':($i===$idx?'now':'')?>"><i></i><span><?=e($lbl)?></span></li><?php $i++;endforeach; ?></ol><?php endif; ?>
<ul class="oi"><?php foreach($items as $r): ?><li><span><?=(int)$r['qty']?> × <?=e($r['name'])?></span><span><?=peso($r['price']*$r['qty'])?></span></li><?php endforeach; ?></ul>
<div class="between"><span class="tiny mute"><?=e($o['address']?:($o['fulfillment']==='pickup'?'Pickup at '.STORE_ADDRESS:''))?></span><b><?=peso($o['total'])?></b></div></article>
<?php endforeach; ?></main>
<?php require 'includes/footer.php'; ?>
