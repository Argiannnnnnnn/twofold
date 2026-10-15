<?php require '_layout.php';
if($_SERVER['REQUEST_METHOD']==='POST'&&($_POST['action']??'')==='pay'){csrf_check();$id=(int)$_POST['id'];$ps=($_POST['payment_status']??'')==='paid'?'paid':'due';$ref=trim($_POST['payment_ref']??'');
 $pdo->prepare('UPDATE orders SET payment_status=?,payment_ref=? WHERE id=?')->execute([$ps,$ref!==''?$ref:null,$id]);go('payments.php?id='.$id.'&tab='.urlencode($_POST['tab']??'all'));}
$tab=$_GET['tab']??'all';$q=trim($_GET['q']??'');$m=$_GET['method']??'';$where=['1=1'];$args=[];
if($tab==='paid')$where[]="o.payment_status='paid'";if($tab==='due')$where[]="o.payment_status='due'";
if(in_array($m,['cash','gcash'],true)){$where[]='o.payment=?';$args[]=$m;}
if($q!==''){$where[]='(o.id=? OR u.name LIKE ? OR o.payment_ref LIKE ?)';array_push($args,(int)preg_replace('/\D/','',$q),"%$q%","%$q%");}
$base='SELECT o.*,u.name cname FROM orders o JOIN users u ON u.id=o.user_id';
$s=$pdo->prepare("$base WHERE ".implode(' AND ',$where).' ORDER BY o.id DESC LIMIT 100');$s->execute($args);$rows=$s->fetchAll();
$sid=(int)($_GET['id']??($rows[0]['id']??0));$sel=null;if($sid){$s=$pdo->prepare("$base WHERE o.id=?");$s->execute([$sid]);$sel=$s->fetch();}
$c=$pdo->query("SELECT COALESCE(SUM(total),0) t,COALESCE(SUM(CASE WHEN payment_status='paid' THEN total END),0) p,COALESCE(SUM(CASE WHEN payment_status='due' THEN total END),0) d,COALESCE(SUM(payment_ref IS NOT NULL AND payment_ref<>''),0) r FROM orders WHERE status<>'cancelled'")->fetch();
cms_head('payments','Payments management','Every peso, accounted for.','Payment records for your orders.');
echo '<div class="stats">'.stat_card('Total order value',peso($c['t']),'Excluding cancelled').stat_card('Paid',peso($c['p']),'Marked as paid').stat_card('Payment due',peso($c['d']),'Awaiting payment').stat_card('References recorded',(int)$c['r'],'GCash or receipt refs').'</div>'; ?>
<div class="cols"><section class="card"><h3>Payment register</h3>
<?=tabs($tab,['all'=>'All records','paid'=>'Paid','due'=>'Due'])?><?=tools('Search order, customer or reference',['method'=>[''=>'All methods','cash'=>'Cash','gcash'=>'GCash']])?>
<div style="overflow-x:auto"><table class="t"><tr><th>ORDER</th><th>CUSTOMER</th><th>METHOD</th><th>REFERENCE</th><th>AMOUNT</th><th>STATUS</th></tr>
<?php foreach($rows as $r): ?><tr class="<?=$sel&&$r['id']==$sel['id']?'sel':''?>" data-href="<?=e(qurl(['id'=>$r['id']]))?>"><td><b><?=oid($r['id'])?></b><br><small><?=date('j M · g:i A',strtotime($r['created_at']))?></small></td><td><?=e($r['cname'])?></td><td><?=$r['payment']==='gcash'?'GCash':'Cash'?></td><td><?=e($r['payment_ref']?:'—')?></td><td><?=peso($r['total'])?></td><td><?=pill($r['payment_status']==='paid'?'Paid':'Due',$r['payment_status']==='paid'?'g':'o')?></td></tr>
<?php endforeach; if(!$rows)echo '<tr><td colspan="6" class="note">No payment records.</td></tr>'; ?></table></div></section>
<?php if($sel): $its=$pdo->prepare('SELECT name,price,qty FROM order_items WHERE order_id=?');$its->execute([$sel['id']]);$its=$its->fetchAll();$sub=0;foreach($its as $i)$sub+=$i['price']*$i['qty']; ?>
<aside class="panel"><div class="ph"><small>SELECTED PAYMENT</small><h2><?=peso($sel['total'])?></h2><span><?=oid($sel['id'])?> · <?=e($sel['cname'])?></span></div><div class="pb">
<h4>PAYMENT RECORD</h4><div class="kv"><span>Method</span><span><?=$sel['payment']==='gcash'?'GCash':'Cash'?></span></div><div class="kv"><span>Reference</span><span><?=e($sel['payment_ref']?:'Not recorded')?></span></div><div class="kv"><span>Status</span><span><?=pill($sel['payment_status']==='paid'?'Paid':'Due',$sel['payment_status']==='paid'?'g':'o')?></span></div>
<h4>ORDER BREAKDOWN</h4><ul class="oi"><?php foreach($its as $i): ?><li><span><?=(int)$i['qty']?> × <?=e($i['name'])?></span><span><?=peso($i['price']*$i['qty'])?></span></li><?php endforeach; ?></ul><div class="kv"><span>Subtotal</span><span><?=peso($sub)?></span></div><?php if($sel['total']>$sub): ?><div class="kv"><span>Delivery fee</span><span><?=peso($sel['total']-$sub)?></span></div><?php endif; ?><div class="kv"><b>Total</b><b><?=peso($sel['total'])?></b></div>
<h4>RECORD PAYMENT</h4><form method="post"><?=csrf_field()?><input type="hidden" name="action" value="pay"><input type="hidden" name="id" value="<?=$sel['id']?>"><input type="hidden" name="tab" value="<?=e($tab)?>">
<label>Status<select name="payment_status"><option value="due"<?=$sel['payment_status']==='due'?' selected':''?>>Due</option><option value="paid"<?=$sel['payment_status']==='paid'?' selected':''?>>Paid</option></select></label>
<label>Reference / receipt no.<input name="payment_ref" value="<?=e($sel['payment_ref'])?>" placeholder="e.g. GCash ref"></label><button class="btn full">Record payment →</button></form>
<p style="margin-top:.8rem"><a class="link" href="index.php?id=<?=$sel['id']?>&tab=all">View linked order ↗</a> · <a class="link" href="#" onclick="window.print();return false">Print receipt</a></p></div></aside><?php endif; ?></div>
<?php cms_foot(); ?>
