<?php require '_layout.php';
$ST=['pending'=>'New','preparing'=>'Preparing','ready'=>'Ready','completed'=>'Completed','cancelled'=>'Cancelled'];
if($_SERVER['REQUEST_METHOD']==='POST'&&($_POST['action']??'')==='order'){csrf_check();
 $id=(int)$_POST['id'];$s=$_POST['status']??'';$pb=(int)($_POST['prepared_by']??0)?:null;$rd=trim($_POST['rider']??'');
 if(isset($ST[$s])){if($s==='preparing'&&!$pb)$pb=(int)$staff['id'];
  $pdo->prepare('UPDATE orders SET status=?,prepared_by=?,rider=? WHERE id=?')->execute([$s,$pb,$rd!==''?$rd:null,$id]);}
 go('index.php?id='.$id.'&tab='.urlencode($_POST['tab']??'active'));}
$tab=$_GET['tab']??'active';$q=trim($_GET['q']??'');$type=$_GET['type']??'';
$w=['active'=>"o.status IN ('pending','preparing','ready')",'completed'=>"o.status='completed'",'cancelled'=>"o.status='cancelled'"];
$where=[$w[$tab]??'1=1'];$args=[];
if($q!==''){$where[]='(o.id=? OR u.name LIKE ? OR o.phone LIKE ?)';array_push($args,(int)preg_replace('/\D/','',$q),"%$q%","%$q%");}
if(in_array($type,['pickup','delivery'],true)){$where[]='o.fulfillment=?';$args[]=$type;}
$base="SELECT o.*,u.name cname,u.email,s.full_name sname,s.username suser,(SELECT COALESCE(SUM(qty),0) FROM order_items WHERE order_id=o.id) cups FROM orders o JOIN users u ON u.id=o.user_id LEFT JOIN staff s ON s.id=o.prepared_by";
$s=$pdo->prepare("$base WHERE ".implode(' AND ',$where).' ORDER BY o.id DESC LIMIT 100');$s->execute($args);$rows=$s->fetchAll();
$sid=(int)($_GET['id']??($rows[0]['id']??0));$sel=null;
if($sid){$s=$pdo->prepare("$base WHERE o.id=?");$s->execute([$sid]);$sel=$s->fetch();}
$c=$pdo->query("SELECT SUM(status='pending') p,SUM(status='preparing') g,SUM(status='ready') r,SUM(status='completed' AND DATE(created_at)=CURDATE()) d FROM orders")->fetch();
$crew=$pdo->query('SELECT id,full_name,username FROM staff WHERE active=1 ORDER BY full_name')->fetchAll();
cms_head('orders','Order management',greet().', team.','Keep every cup moving.');
echo '<div class="stats">'.stat_card('New orders',(int)$c['p'],'Awaiting acceptance').stat_card('Preparing',(int)$c['g'],'With the baristas').stat_card('Ready',(int)$c['r'],'Ready for pickup or delivery').stat_card('Completed today',(int)$c['d'],'Handed off').'</div>'; ?>
<div class="cols"><section class="card"><h3>Order queue</h3>
<?=tabs($tab,['active'=>'Active','completed'=>'Completed','cancelled'=>'Cancelled','all'=>'All'])?>
<?=tools('Search order #, customer or mobile',['type'=>[''=>'All types','pickup'=>'Pickup','delivery'=>'Delivery']])?>
<div style="overflow-x:auto"><table class="t"><tr><th>ORDER</th><th>CUSTOMER</th><th>CUPS</th><th>TYPE</th><th>PAYMENT</th><th>TOTAL</th><th>STATUS</th><th>PREPARED BY</th></tr>
<?php foreach($rows as $r): ?><tr class="<?=$sel&&$r['id']==$sel['id']?'sel':''?>" data-href="<?=e(qurl(['id'=>$r['id']]))?>"><td><b><?=oid($r['id'])?></b><br><small><?=date('j M · g:i A',strtotime($r['created_at']))?></small></td><td><?=e($r['cname'])?></td><td><?=(int)$r['cups']?></td><td><?=ucfirst($r['fulfillment'])?></td><td><?=pill($r['payment_status']==='paid'?'Paid':'Due',$r['payment_status']==='paid'?'g':'o')?></td><td><?=peso($r['total'])?></td><td><?=pill($ST[$r['status']],'s-'.$r['status'])?></td><td><?=e($r['sname']?:($r['suser']?:'—'))?></td></tr>
<?php endforeach; if(!$rows)echo '<tr><td colspan="8" class="note">No orders here yet.</td></tr>'; ?></table></div></section>
<?php if($sel):
 $its=$pdo->prepare('SELECT name,price,qty FROM order_items WHERE order_id=?');$its->execute([$sel['id']]);$its=$its->fetchAll();
 $sub=0;$cups=0;foreach($its as $i){$sub+=$i['price']*$i['qty'];$cups+=$i['qty'];}$fee=max(0,$sel['total']-$sub);
 $flow=['pending'=>'Received','preparing'=>'Preparing','ready'=>'Ready','completed'=>'Done'];$idx=array_search($sel['status'],array_keys($flow));$del=$sel['fulfillment']==='delivery'; ?>
<aside class="panel"><div class="ph"><small>SELECTED ORDER</small><h2><?=oid($sel['id'])?></h2><?=pill($ST[$sel['status']],'s-'.$sel['status'])?> <span><?=date('j M Y · g:i A',strtotime($sel['created_at']))?> · <?=ucfirst($sel['fulfillment'])?></span></div>
<div class="pb"><?php if($sel['status']!=='cancelled'): ?><ol class="trk"><?php $i=0;foreach($flow as $l): ?><li class="<?=$i<$idx?'done':($i===$idx?'now':'')?>"><i></i><?=$l?></li><?php $i++;endforeach; ?></ol><?php endif; ?>
<h4>CUSTOMER</h4><p><b><?=e($sel['cname'])?></b><br><?=e($sel['phone'])?><br><?=e($sel['email'])?></p>
<h4>ORDER ITEMS · <?=$cups?> <?=$cups==1?'CUP':'CUPS'?></h4><ul class="oi"><?php foreach($its as $i): ?><li><span><?=(int)$i['qty']?> × <?=e($i['name'])?></span><span><?=peso($i['price']*$i['qty'])?></span></li><?php endforeach; ?></ul>
<h4>PAYMENT</h4><div class="kv"><span>Subtotal</span><span><?=peso($sub)?></span></div><?php if($del): ?><div class="kv"><span>Delivery fee</span><span><?=peso($fee)?></span></div><?php endif; ?><div class="kv"><b>Total</b><b><?=peso($sel['total'])?></b></div><div class="kv"><span><?=$sel['payment']==='gcash'?'GCash':'Cash'?></span><span><?=pill($sel['payment_status']==='paid'?'Paid':'Due',$sel['payment_status']==='paid'?'g':'o')?></span></div>
<h4><?=$del?'DELIVERY INFORMATION':'PICKUP'?></h4><?php if($del): ?><p><b><?=e($sel['address']?:'No address given')?></b></p><p>Mobile: <?=e($sel['phone'])?></p><p>Rider: <?=e($sel['rider']?:'Not assigned')?></p><?php else: ?><p>Pickup at Twofold, <?=e(STORE_ADDRESS)?></p><?php if($sel['address'])echo '<p>'.e($sel['address']).'</p>'; endif; ?>
<h4>UPDATE ORDER</h4><form method="post"><?=csrf_field()?><input type="hidden" name="action" value="order"><input type="hidden" name="id" value="<?=$sel['id']?>"><input type="hidden" name="tab" value="<?=e($tab)?>">
<label>Status<select name="status"><?php foreach($ST as $k=>$l)echo '<option value="'.$k.'"'.($k===$sel['status']?' selected':'').'>'.$l.'</option>'; ?></select></label>
<label>Prepared by<select name="prepared_by"><option value="">— Not assigned —</option><?php foreach($crew as $m)echo '<option value="'.$m['id'].'"'.($m['id']==$sel['prepared_by']?' selected':'').'>'.e($m['full_name']?:$m['username']).'</option>'; ?></select></label>
<?php if($del): ?><label>Rider<input name="rider" value="<?=e($sel['rider'])?>" placeholder="Rider name"></label><?php endif; ?>
<button class="btn full">Save changes</button></form><p style="margin-top:.8rem"><a class="link" href="#" onclick="window.print();return false">Print receipt</a></p></div></aside><?php endif; ?></div>
<?php cms_foot(); ?>
