<?php require '_layout.php';
if($_SERVER['REQUEST_METHOD']==='POST'){csrf_check();$a=$_POST['action']??'';$id=(int)($_POST['id']??0);
 if($a==='inv'){$n=trim($_POST['name']??'');$cat=trim($_POST['category']??'');$un=trim($_POST['unit']??'');$qt=max(0,(float)($_POST['qty']??0));$lo=max(0,(float)($_POST['low_at']??0));
  if($n!==''){if($id)$pdo->prepare('UPDATE inventory SET name=?,category=?,unit=?,qty=?,low_at=? WHERE id=?')->execute([$n,$cat?:'General',$un?:'pcs',$qt,$lo,$id]);
   else{$pdo->prepare('INSERT INTO inventory(name,category,unit,qty,low_at) VALUES(?,?,?,?,?)')->execute([$n,$cat?:'General',$un?:'pcs',$qt,$lo]);$id=(int)$pdo->lastInsertId();}}go('inventory.php?id='.$id);}
 if($a==='adj'&&$id){$pdo->prepare('UPDATE inventory SET qty=GREATEST(0,qty+?) WHERE id=?')->execute([(float)($_POST['delta']??0),$id]);go('inventory.php?id='.$id.'&tab='.urlencode($_POST['tab']??'all'));}
 if($a==='del'&&$id){$pdo->prepare('DELETE FROM inventory WHERE id=?')->execute([$id]);go('inventory.php');}}
function ist($r){return $r['qty']<=0?['Out of stock','r']:($r['qty']<=$r['low_at']?['Low','o']:['In stock','g']);}
$tab=$_GET['tab']??'all';$q=trim($_GET['q']??'');$all=$pdo->query('SELECT * FROM inventory ORDER BY category,name')->fetchAll();
$rows=array_values(array_filter($all,function($r)use($tab,$q){return ($tab!=='low'||$r['qty']<=$r['low_at'])&&($q===''||stripos($r['name'].' '.$r['category'],$q)!==false);}));
$new=isset($_GET['new']);$sel=null;if(!$new){$sid=(int)($_GET['id']??($rows[0]['id']??0));foreach($all as $r)if($r['id']==$sid)$sel=$r;}
$low=count(array_filter($all,function($r){return $r['qty']>0&&$r['qty']<=$r['low_at'];}));$out=count(array_filter($all,function($r){return $r['qty']<=0;}));
cms_head('inventory','Inventory management','Always stocked, never stressed.','Track ingredients and packaging before they run out.','<a class="btn" href="inventory.php?new=1">Add item +</a>');
echo '<div class="stats">'.stat_card('Items tracked',count($all),'Ingredients and packaging').stat_card('Low stock',$low,'At or below the alert level').stat_card('Out of stock',$out,'Needs restocking now').stat_card('Categories',count(array_unique(array_column($all,'category'))),'Coffee, dairy, syrups…').'</div>'; ?>
<div class="cols"><section class="card"><h3>Stock list</h3><?=tabs($tab,['all'=>'All items · '.count($all),'low'=>'Low or out'])?><?=tools('Search item or category')?>
<div style="overflow-x:auto"><table class="t"><tr><th>ITEM</th><th>CATEGORY</th><th>IN STOCK</th><th>ALERT AT</th><th>STATUS</th></tr>
<?php foreach($rows as $r): $s=ist($r); ?><tr class="<?=$sel&&$r['id']==$sel['id']?'sel':''?>" data-href="<?=e(qurl(['id'=>$r['id'],'new'=>null]))?>"><td><b><?=e($r['name'])?></b></td><td><?=e($r['category'])?></td><td><?=rtrim(rtrim(number_format($r['qty'],2),'0'),'.')?> <?=e($r['unit'])?></td><td><?=rtrim(rtrim(number_format($r['low_at'],2),'0'),'.')?> <?=e($r['unit'])?></td><td><?=pill($s[0],$s[1])?></td></tr>
<?php endforeach; if(!$rows)echo '<tr><td colspan="5" class="note">Nothing here.</td></tr>'; ?></table></div></section>
<?php if($new||$sel): $r=$sel?:['id'=>0,'name'=>'','category'=>'','unit'=>'','qty'=>0,'low_at'=>0]; ?>
<aside class="panel"><div class="ph"><small><?=$new?'NEW ITEM':'SELECTED ITEM'?></small><h2><?=e($r['name']?:'Add an item')?></h2><?php if($r['id']){$s=ist($r);echo pill($s[0],$s[1]);} ?></div><div class="pb">
<?php if($r['id']): ?><h4>QUICK ADJUST</h4><form method="post" class="chk" style="gap:.4rem"><?=csrf_field()?><input type="hidden" name="action" value="adj"><input type="hidden" name="id" value="<?=$r['id']?>"><input type="hidden" name="tab" value="<?=e($tab)?>"><input type="number" step="any" name="delta" placeholder="+10 or -2" required><button class="btn">Apply</button></form><h4>EDIT ITEM</h4><?php endif; ?>
<form method="post"><?=csrf_field()?><input type="hidden" name="action" value="inv"><input type="hidden" name="id" value="<?=$r['id']?>">
<label>Name<input name="name" value="<?=e($r['name'])?>" required></label><label>Category<input name="category" value="<?=e($r['category'])?>" placeholder="Coffee, Dairy, Syrups, Packaging…"></label>
<label>Unit<input name="unit" value="<?=e($r['unit'])?>" placeholder="kg, L, pcs"></label><label>Quantity in stock<input type="number" step="any" min="0" name="qty" value="<?=e($r['qty']+0)?>"></label><label>Low-stock alert at<input type="number" step="any" min="0" name="low_at" value="<?=e($r['low_at']+0)?>"></label>
<button class="btn full">Save item →</button></form>
<?php if($r['id']): ?><form method="post" onsubmit="return confirm('Delete this item?')" style="margin-top:.6rem"><?=csrf_field()?><input type="hidden" name="action" value="del"><input type="hidden" name="id" value="<?=$r['id']?>"><button class="link">Delete item</button></form><?php endif; ?></div></aside><?php endif; ?></div>
<?php cms_foot(); ?>
