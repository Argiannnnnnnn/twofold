<?php require '_layout.php';
if($_SERVER['REQUEST_METHOD']==='POST'&&($_POST['action']??'')==='user'){csrf_check();$id=(int)$_POST['id'];
 $pdo->prepare('UPDATE users SET name=?,phone=?,active=? WHERE id=?')->execute([trim($_POST['name']??''),trim($_POST['phone']??''),isset($_POST['active'])?1:0,$id]);go('users.php?id='.$id.'&tab='.urlencode($_POST['tab']??'active'));}
$tab=$_GET['tab']??'active';$q=trim($_GET['q']??'');$where=['1=1'];$args=[];
if($tab==='active')$where[]='u.active=1';if($tab==='inactive')$where[]='u.active=0';
if($q!==''){$where[]='(u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)';array_push($args,"%$q%","%$q%","%$q%");}
$base='SELECT u.*,(SELECT COUNT(*) FROM orders WHERE user_id=u.id) n,(SELECT MAX(id) FROM orders WHERE user_id=u.id) last_id,(SELECT COALESCE(SUM(total),0) FROM orders WHERE user_id=u.id AND status<>\'cancelled\') spent FROM users u';
$s=$pdo->prepare("$base WHERE ".implode(' AND ',$where).' ORDER BY u.id DESC LIMIT 100');$s->execute($args);$rows=$s->fetchAll();
$sid=(int)($_GET['id']??($rows[0]['id']??0));$sel=null;if($sid){$s=$pdo->prepare("$base WHERE u.id=?");$s->execute([$sid]);$sel=$s->fetch();}
$c=$pdo->query("SELECT COUNT(*) t,SUM(active=1) a,SUM(active=0) i,SUM(created_at>=NOW()-INTERVAL 7 DAY) w FROM users")->fetch();
cms_head('users','User management','Know the people behind the orders.','Customer accounts linked to the order queue.');
echo '<div class="stats">'.stat_card('Customers',(int)$c['t'],'Registered accounts').stat_card('Active',(int)$c['a'],'Can sign in and order').stat_card('New this week',(int)$c['w'],'Joined in the last 7 days').stat_card('Inactive',(int)$c['i'],'Sign-in turned off').'</div>'; ?>
<div class="cols"><section class="card"><h3>Customer directory</h3>
<?=tabs($tab,['active'=>'Active','all'=>'All users','inactive'=>'Inactive'])?><?=tools('Search name, email or mobile')?>
<div style="overflow-x:auto"><table class="t"><tr><th>CUSTOMER</th><th>LATEST ORDER</th><th>ORDERS</th><th>SPENT</th><th>STATUS</th></tr>
<?php foreach($rows as $r): ?><tr class="<?=$sel&&$r['id']==$sel['id']?'sel':''?>" data-href="<?=e(qurl(['id'=>$r['id']]))?>"><td><b><?=e($r['name'])?></b><br><small><?=e($r['phone'])?></small></td><td><?=$r['last_id']?oid($r['last_id']):'—'?></td><td><?=(int)$r['n']?></td><td><?=peso($r['spent'])?></td><td><?=pill($r['active']?'Active':'Inactive',$r['active']?'g':'r')?></td></tr>
<?php endforeach; if(!$rows)echo '<tr><td colspan="5" class="note">No customers here.</td></tr>'; ?></table></div></section>
<?php if($sel): $o=$pdo->prepare('SELECT id,status,total,created_at FROM orders WHERE user_id=? ORDER BY id DESC LIMIT 5');$o->execute([$sel['id']]);$o=$o->fetchAll(); ?>
<aside class="panel"><div class="ph"><small>SELECTED USER</small><h2><?=e($sel['name'])?></h2><span>Account #<?=$sel['id']?> · Joined <?=date('j M Y',strtotime($sel['created_at']))?></span></div><div class="pb">
<h4>ACCOUNT</h4><div class="kv"><span>Email</span><span><?=e($sel['email'])?></span></div><div class="kv"><span>Mobile</span><span><?=e($sel['phone'])?></span></div><div class="kv"><span>Orders</span><span><?=(int)$sel['n']?> · <?=peso($sel['spent'])?></span></div>
<h4>RECENT ORDERS</h4><ul class="oi"><?php foreach($o as $r): ?><li><a href="index.php?id=<?=$r['id']?>&tab=all"><b><?=oid($r['id'])?></b> · <?=date('j M',strtotime($r['created_at']))?></a><span><?=peso($r['total'])?> <?=e(ucfirst($r['status']))?></span></li><?php endforeach; if(!$o)echo '<li>No orders yet.</li>'; ?></ul>
<h4>EDIT USER</h4><form method="post"><?=csrf_field()?><input type="hidden" name="action" value="user"><input type="hidden" name="id" value="<?=$sel['id']?>"><input type="hidden" name="tab" value="<?=e($tab)?>">
<label>Name<input name="name" value="<?=e($sel['name'])?>" required></label><label>Mobile<input name="phone" value="<?=e($sel['phone'])?>"></label>
<label class="chk"><input type="checkbox" name="active" <?=$sel['active']?'checked':''?>> Account active (can sign in)</label><button class="btn full">Save user details →</button></form></div></aside><?php endif; ?></div>
<?php cms_foot(); ?>
