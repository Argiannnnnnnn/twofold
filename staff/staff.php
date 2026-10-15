<?php require '_layout.php';
$ROLES=['Manager','Shift lead','Barista','Cashier','Rider'];$err='';
if($_SERVER['REQUEST_METHOD']==='POST'&&($_POST['action']??'')==='staff'){csrf_check();$id=(int)($_POST['id']??0);
 $fn=trim($_POST['full_name']??'');$role=in_array($_POST['role']??'',$ROLES,true)?$_POST['role']:'Barista';$act=isset($_POST['active'])?1:0;$pw=$_POST['password']??'';
 if($id===(int)$staff['id'])$act=1;
 if($fn==='')$err='Enter a full name.';
 elseif($id){$pdo->prepare('UPDATE staff SET full_name=?,role=?,active=? WHERE id=?')->execute([$fn,$role,$act,$id]);
  if($pw!==''){if(strlen($pw)<8)$err='New password needs at least 8 characters.';else $pdo->prepare('UPDATE staff SET password_hash=? WHERE id=?')->execute([password_hash($pw,PASSWORD_DEFAULT),$id]);}
  if(!$err)go('staff.php?id='.$id);}
 else{$un=strtolower(trim($_POST['username']??''));
  if(!preg_match('/^[a-z0-9._-]{3,30}$/',$un))$err='Username: 3–30 letters, numbers, dot, dash or underscore.';
  elseif(strlen($pw)<8)$err='Password needs at least 8 characters.';
  else{$d=$pdo->prepare('SELECT id FROM staff WHERE username=?');$d->execute([$un]);
   if($d->fetch())$err='That username is taken.';
   else{$pdo->prepare('INSERT INTO staff(username,password_hash,full_name,role,active) VALUES(?,?,?,?,1)')->execute([$un,password_hash($pw,PASSWORD_DEFAULT),$fn,$role]);go('staff.php?id='.$pdo->lastInsertId());}}}}
$tab=$_GET['tab']??'all';$q=trim($_GET['q']??'');$where=['1=1'];$args=[];
if($tab==='active')$where[]='s.active=1';if($tab==='inactive')$where[]='s.active=0';
if($q!==''){$where[]='(s.full_name LIKE ? OR s.username LIKE ? OR s.role LIKE ?)';array_push($args,"%$q%","%$q%","%$q%");}
$base='SELECT s.*,(SELECT COUNT(*) FROM orders WHERE prepared_by=s.id) prepared,(SELECT COUNT(*) FROM orders WHERE prepared_by=s.id AND DATE(created_at)=CURDATE()) today FROM staff s';
$st=$pdo->prepare("$base WHERE ".implode(' AND ',$where).' ORDER BY s.id');$st->execute($args);$rows=$st->fetchAll();
$new=isset($_GET['new'])||($err&&empty($_POST['id']));$sel=null;
if(!$new){$sid=(int)($_GET['id']??($_POST['id']??($rows[0]['id']??0)));if($sid){$st=$pdo->prepare("$base WHERE s.id=?");$st->execute([$sid]);$sel=$st->fetch();}}
$c=$pdo->query('SELECT COUNT(*) t,SUM(active=1) a,(SELECT COUNT(*) FROM orders WHERE prepared_by IS NOT NULL AND DATE(created_at)=CURDATE()) p,(SELECT COUNT(*) FROM orders WHERE status IN (\'pending\',\'preparing\') AND prepared_by IS NULL) u FROM staff')->fetch();
cms_head('staff','Staff management','Great teamwork, every shift.','People, roles, and who prepared what.','<a class="btn" href="staff.php?new=1">Add staff member +</a>');
echo '<div class="stats">'.stat_card('Staff members',(int)$c['t'],'Accounts in the workspace').stat_card('Active',(int)$c['a'],'Can sign in').stat_card('Prepared today',(int)$c['p'],'Orders with a preparer').stat_card('Unassigned orders',(int)$c['u'],'New or preparing, no preparer').'</div>'; ?>
<div class="cols"><section class="card"><h3>Staff directory</h3><?=tabs($tab,['all'=>'All staff','active'=>'Active','inactive'=>'Inactive'])?><?=tools('Search name, role or username')?>
<div style="overflow-x:auto"><table class="t"><tr><th>STAFF</th><th>ROLE</th><th>USERNAME</th><th>ORDERS PREPARED</th><th>STATUS</th></tr>
<?php foreach($rows as $r): ?><tr class="<?=$sel&&$r['id']==$sel['id']?'sel':''?>" data-href="<?=e(qurl(['id'=>$r['id'],'new'=>null]))?>"><td><b><?=e($r['full_name']?:$r['username'])?></b></td><td><?=e($r['role'])?></td><td><?=e($r['username'])?></td><td><?=(int)$r['prepared']?> <small>(<?=(int)$r['today']?> today)</small></td><td><?=pill($r['active']?'Active':'Inactive',$r['active']?'g':'r')?></td></tr>
<?php endforeach; ?></table></div></section>
<?php if($new||$sel): $r=$sel?:['id'=>0,'username'=>'','full_name'=>'','role'=>'Barista','active'=>1]; ?>
<aside class="panel"><div class="ph"><small><?=$new?'NEW STAFF MEMBER':'SELECTED STAFF'?></small><h2><?=e($r['full_name']?:'Add staff member')?></h2><?php if($r['id'])echo '<span>@'.e($r['username']).' · '.e($r['role']).'</span>'; ?></div><div class="pb">
<?php if($err)echo '<p class="err">'.e($err).'</p>'; ?>
<form method="post"><?=csrf_field()?><input type="hidden" name="action" value="staff"><input type="hidden" name="id" value="<?=$r['id']?>">
<?php if(!$r['id']): ?><label>Username<input name="username" required></label><?php endif; ?>
<label>Full name<input name="full_name" value="<?=e($r['full_name'])?>" required></label>
<label>Role<select name="role"><?php foreach($ROLES as $ro)echo '<option'.($ro===$r['role']?' selected':'').'>'.$ro.'</option>'; ?></select></label>
<label><?=$r['id']?'New password (leave blank to keep)':'Password'?><input type="password" name="password" <?=$r['id']?'':'required'?> minlength="8" autocomplete="new-password"></label>
<?php if($r['id']&&$r['id']!=$staff['id']): ?><label class="chk"><input type="checkbox" name="active" <?=$r['active']?'checked':''?>> Account active</label><?php endif; ?>
<button class="btn full">Save staff details →</button></form></div></aside><?php endif; ?></div>
<?php cms_foot(); ?>
