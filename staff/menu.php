<?php require '_layout.php';
$TP=['iced'=>'Iced','hot'=>'Hot','both'=>'Hot or iced'];$TY=['espresso'=>'Espresso','latte'=>'Latte','americano'=>'Americano','matcha'=>'Matcha'];
function drop_photo($img){if($img&&strpos($img,'drinks/')===0&&strpos($img,'..')===false){$f=dirname(__DIR__).'/assets/img/'.$img;if(is_file($f))@unlink($f);}}
function save_photo($tmp,$id){$info=@getimagesize($tmp);if(!$info)return [null,'That file is not an image.'];
 $types=[IMAGETYPE_JPEG=>'jpg',IMAGETYPE_PNG=>'png',IMAGETYPE_WEBP=>'webp'];if(!isset($types[$info[2]]))return [null,'Use a JPG, PNG or WebP photo.'];
 $dir=dirname(__DIR__).'/assets/img/drinks/';if(!is_dir($dir))@mkdir($dir,0777,true);$name='drink-'.$id.'-'.time();$ext=$types[$info[2]];
 if(function_exists('imagecreatetruecolor')){$src=$info[2]===IMAGETYPE_JPEG?@imagecreatefromjpeg($tmp):($info[2]===IMAGETYPE_PNG?@imagecreatefrompng($tmp):(function_exists('imagecreatefromwebp')?@imagecreatefromwebp($tmp):false));
  if($src){$w=imagesx($src);$h=imagesy($src);$nw=min(800,$w);$nh=(int)round($h*$nw/$w);$dst=imagecreatetruecolor($nw,$nh);
   imagefill($dst,0,0,imagecolorallocate($dst,255,255,255));imagecopyresampled($dst,$src,0,0,0,0,$nw,$nh,$w,$h);
   if(imagejpeg($dst,$dir.$name.'.jpg',85))return ['drinks/'.$name.'.jpg',null];}}
 return move_uploaded_file($tmp,$dir.$name.'.'.$ext)?['drinks/'.$name.'.'.$ext,null]:[null,'Could not save the photo.'];}
if($_SERVER['REQUEST_METHOD']==='POST'){csrf_check();$a=$_POST['action']??'';$id=(int)($_POST['id']??0);
 if($a==='drink'){$n=trim($_POST['name']??'');$cat=trim($_POST['category']??'');$pr=max(0,(float)($_POST['price']??0));$tp=isset($TP[$_POST['temperature']??''])?$_POST['temperature']:'iced';$av=isset($_POST['available'])?1:0;$fv=isset($_POST['fav'])?1:0;$dt=isset($TY[$_POST['drink_type']??''])?$_POST['drink_type']:'latte';
  if($n!==''&&$cat!==''){if($id)$pdo->prepare('UPDATE products SET name=?,category=?,drink_type=?,price=?,temperature=?,available=?,is_favorite=? WHERE id=?')->execute([$n,$cat,$dt,$pr,$tp,$av,$fv,$id]);
   else{$pdo->prepare('INSERT INTO products(name,category,drink_type,price,temperature,available,is_favorite) VALUES(?,?,?,?,?,?,?)')->execute([$n,$cat,$dt,$pr,$tp,$av,$fv]);$id=(int)$pdo->lastInsertId();}}
  if($id){$o=$pdo->prepare('SELECT image FROM products WHERE id=?');$o->execute([$id]);$oldImg=(string)$o->fetchColumn();$up=$_FILES['photo']??null;$newImg=null;
   if($up&&$up['error']===UPLOAD_ERR_OK){if($up['size']>5*1024*1024)$_SESSION['menu_err']='That photo is too big (max 5 MB).';else{[$r,$er]=save_photo($up['tmp_name'],$id);if($r)$newImg=$r;else $_SESSION['menu_err']=$er;}}
   elseif($up&&$up['error']!==UPLOAD_ERR_NO_FILE)$_SESSION['menu_err']='The photo could not be uploaded (check the file size).';
   if($newImg||isset($_POST['remove_photo'])){drop_photo($oldImg);$pdo->prepare('UPDATE products SET image=? WHERE id=?')->execute([$newImg,$id]);}}
  go('menu.php?id='.$id);}
 if($a==='delete'&&$id){$o=$pdo->prepare('SELECT image FROM products WHERE id=?');$o->execute([$id]);drop_photo((string)$o->fetchColumn());$pdo->prepare('DELETE FROM products WHERE id=?')->execute([$id]);go('menu.php');}}
$tab=$_GET['tab']??'all';$q=trim($_GET['q']??'');$temp=$_GET['temp']??'';$avf=$_GET['av']??'';
$all=$pdo->query('SELECT * FROM products ORDER BY id')->fetchAll();
$rows=array_values(array_filter($all,function($p)use($tab,$q,$temp,$avf){$t=$p['temperature']??'iced';
 return ($tab==='all'||($p['drink_type']??ptag($p['name']))===$tab)&&($q===''||stripos($p['name'],$q)!==false)&&($temp===''||$t===$temp||$t==='both')&&($avf===''||(string)$p['available']===$avf);}));
$new=isset($_GET['new']);$sel=null;if(!$new){$sid=(int)($_GET['id']??($rows[0]['id']??0));foreach($all as $p)if($p['id']==$sid)$sel=$p;}
$cats=array_values(array_unique(array_column($all,'category')));$prices=array_column($all,'price');
cms_head('menu','Menu management','Good coffee starts here.','Manage drinks, prices, and what’s pouring.','<a class="btn" href="menu.php?new=1">Add drink +</a>');
echo '<div class="stats">'.stat_card('Menu drinks',count($all),'Customer ordering catalog').stat_card('Categories',count($cats),implode(', ',$cats)).stat_card('Price range',$prices?peso(min($prices)).'–'.peso(max($prices)):'—','Listed base drink prices').stat_card('Espresso add-on',peso(EXTRA_ESPRESSO),'Per shot · max 4 per drink').'</div>'; ?>
<div class="cols"><section class="card"><h3>Drink catalog</h3>
<?=tabs($tab,['all'=>'All drinks · '.count($all),'espresso'=>'Espresso','latte'=>'Latte','americano'=>'Americano','matcha'=>'Matcha'])?>
<?=tools('Search drink name',['temp'=>[''=>'Hot or iced','iced'=>'Iced','hot'=>'Hot'],'av'=>[''=>'All availability','1'=>'Available','0'=>'Paused']])?>
<div style="overflow-x:auto"><table class="t"><tr><th>DRINK</th><th>TYPE</th><th>HOT/ICED</th><th>PRICE</th><th>AVAILABILITY</th><th></th></tr>
<?php foreach($rows as $p): $u=drink_url($p); ?><tr class="<?=$sel&&$p['id']==$sel['id']?'sel':''?>" data-href="<?=e(qurl(['id'=>$p['id'],'new'=>null]))?>"><td><span class="thumb"><?=$u?'<img src="'.e($u).'" alt="">':e(strtoupper(substr($p['name'],0,1)))?></span><b><?=e($p['name'])?></b></td><td><?=e($TY[$p['drink_type']??ptag($p['name'])]??'Latte')?></td><td><?=e($TP[$p['temperature']??'iced'])?></td><td><?=peso($p['price'])?></td><td><?=pill($p['available']?'Available':'Paused',$p['available']?'g':'o')?></td><td><span class="link">Edit drink ↗</span></td></tr>
<?php endforeach; if(!$rows)echo '<tr><td colspan="6" class="note">No drinks match.</td></tr>'; ?></table></div></section>
<?php if($new||$sel): $p=$sel?:['id'=>0,'name'=>'','category'=>'','price'=>'','temperature'=>'iced','drink_type'=>'latte','available'=>1,'is_favorite'=>0]; ?>
<aside class="panel"><div class="ph"><small><?=$new?'NEW DRINK':'SELECTED DRINK'?></small><h2><?=e($p['name']?:'Add a drink')?></h2></div><div class="pb">
<?php if(!empty($_SESSION['menu_err'])){echo '<p class="err">'.e($_SESSION['menu_err']).'</p>';unset($_SESSION['menu_err']);} ?>
<form method="post" enctype="multipart/form-data"><?=csrf_field()?><input type="hidden" name="action" value="drink"><input type="hidden" name="id" value="<?=$p['id']?>">
<?php $pu=$p['id']?drink_url($p):''; ?><label>Photo<?php if($pu): ?><img class="ppv" src="<?=e($pu)?>" alt=""><?php endif; ?><input type="file" name="photo" accept="image/jpeg,image/png,image/webp"></label>
<?php if($pu): ?><label class="chk"><input type="checkbox" name="remove_photo"> Remove current photo</label><?php endif; ?>
<label>Name<input name="name" value="<?=e($p['name'])?>" required></label>
<label>Drink type<select name="drink_type"><?php foreach($TY as $k=>$l)echo '<option value="'.$k.'"'.(($p['drink_type']??ptag($p['name']))===$k?' selected':'').'>'.$l.'</option>'; ?></select></label>
<label>Menu section (shown on homepage)<input name="category" list="cats" value="<?=e($p['category'])?>" required></label><datalist id="cats"><?php foreach($cats as $c)echo '<option value="'.e($c).'">'; ?></datalist>
<label>Hot or iced<select name="temperature"><?php foreach($TP as $k=>$l)echo '<option value="'.$k.'"'.(($p['temperature']??'iced')===$k?' selected':'').'>'.$l.'</option>'; ?></select></label>
<label>Base price (₱)<input type="number" step="1" min="0" name="price" value="<?=e($p['price']!==''?(int)$p['price']:'')?>" required></label>
<label class="chk"><input type="checkbox" name="available" <?=$p['available']?'checked':''?>> Available to order</label>
<label class="chk"><input type="checkbox" name="fav" <?=$p['is_favorite']?'checked':''?>> Show in Paniqui’s favorites</label>
<button class="btn full">Save drink changes →</button></form>
<?php if($p['id']): ?><form method="post" onsubmit="return confirm('Delete this drink?')" style="margin-top:.6rem"><?=csrf_field()?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=$p['id']?>"><button class="link">Delete drink</button></form>
<p class="note" style="margin-top:.8rem">Drinks with a photo show at the top of the ordering page. Drinks without one are listed under “More good things”.</p><?php endif; ?></div></aside><?php endif; ?></div>
<?php cms_foot(); ?>
