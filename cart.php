<?php require 'includes/config.php'; if(is_staff())go(BASE.'/staff/index.php'); // staff accounts can't order
$prods=$pdo->query('SELECT * FROM products WHERE available=1 ORDER BY id')->fetchAll();
$byId=array_column($prods,null,'id');
function clean_cart($raw,$byId){$c=[];foreach((is_array($raw)?$raw:[]) as $id=>$q){$id=(int)$id;$q=(int)$q;if($q>0&&isset($byId[$id]))$c[$id]=min($q,20);}return $c;}
function label($n){$n=strtolower($n);if(strpos($n,'espresso')!==false)return 'espresso';if(preg_match('/americano|aerocano/',$n))return 'americano';if(strpos($n,'matcha')!==false)return 'matcha';return 'latte';}
function dtype($p){return $p['drink_type']??label($p['name']);}
function tags($n,$temp='iced'){$n=strtolower($n);$t=[];foreach(['matcha','ube'] as $w)if(strpos($n,$w)!==false)$t[]=$w;
 if(preg_match('/americano|aerocano/',$n))$t[]='americano';if(!in_array('matcha',$t)&&!in_array('ube',$t))$t[]='coffee';if($temp!=='iced')$t[]='hot';if($temp!=='hot')$t[]='iced';return implode(' ',$t);}
function clean_extra($raw,$cart){$x=[];foreach((is_array($raw)?$raw:[]) as $id=>$v){$v=max(0,min(4,(int)$v));if($v>0&&isset($cart[(int)$id]))$x[(int)$id]=$v;}return $x;}
$_SESSION['cart']=clean_cart($_SESSION['cart']??[],$byId);
$_SESSION['extra']=clean_extra($_SESSION['extra']??[],$_SESSION['cart']);
$logged=!empty($_SESSION['user_id']);$err=[];$me=['name'=>'','phone'=>''];
if($logged){$s=$pdo->prepare('SELECT name,phone FROM users WHERE id=?');$s->execute([$_SESSION['user_id']]);$me=$s->fetch()?:$me;}
if($_SERVER['REQUEST_METHOD']==='POST'){csrf_check();$a=$_POST['action']??'';
 if($a==='set'){$id=(int)($_POST['id']??0);$q=(int)($_POST['qty']??0);$c=$_SESSION['cart'];
  if($q>0&&isset($byId[$id]))$c[$id]=min($q,20);else unset($c[$id]);$_SESSION['cart']=$c;
  $_SESSION['extra']=clean_extra(array_replace($_SESSION['extra'],[$id=>(int)($_POST['extra']??0)]),$c);
  header('Content-Type: application/json');exit(json_encode(['count'=>array_sum($c)]));}
 if($a==='checkout'){if(!$logged)go(BASE.'/login.php?next=cart.php');
  $cart=clean_cart(json_decode($_POST['cart_json']??'[]',true),$byId);$_SESSION['cart']=$cart;$extra=clean_extra(json_decode($_POST['extra_json']??'[]',true),$cart);$_SESSION['extra']=$extra;
  $f=$_POST['fulfillment']??'';$pay=$_POST['payment']??'';$name=trim($_POST['name']??'');$ph=trim($_POST['phone']??'');
  $addr=trim($_POST['address']??'');$note=trim($_POST['note']??'');
  if(!$cart)$err[]='Your bag is empty.';
  if(!in_array($f,['pickup','delivery'],true))$err[]='Choose pickup or delivery.';
  if($f==='delivery'&&$addr==='')$err[]='Add your Paniqui delivery address.';
  if(!in_array($pay,['cash','gcash'],true))$err[]='Choose a payment preference.';
  if(!preg_match('/^[0-9+\- ]{7,20}$/',$ph))$err[]='Enter a valid mobile number.';
  if($name==='')$err[]='Enter your full name.';
  if(!$err){$sub=0;foreach($cart as $id=>$q)$sub+=($byId[$id]['price']+($extra[$id]??0)*EXTRA_ESPRESSO)*$q;$total=$sub+($f==='delivery'?DELIVERY_FEE:0);
   $parts=[];if($name!==$me['name'])$parts[]='For: '.$name;if($f==='delivery')$parts[]=$addr;if($note!=='')$parts[]='Note: '.$note;
   $full=$parts?mb_substr(implode(' | ',$parts),0,255):null;
   $pdo->beginTransaction();
   $pdo->prepare('INSERT INTO orders(user_id,fulfillment,address,phone,payment,total) VALUES(?,?,?,?,?,?)')->execute([$_SESSION['user_id'],$f,$full,$ph,$pay,$total]);
   $oid=$pdo->lastInsertId();$ins=$pdo->prepare('INSERT INTO order_items(order_id,product_id,name,price,qty) VALUES(?,?,?,?,?)');
   foreach($cart as $id=>$q){$ex=$extra[$id]??0;$ins->execute([$oid,$id,$byId[$id]['name'].($ex?' (+ '.$ex.' extra espresso)':''),$byId[$id]['price']+$ex*EXTRA_ESPRESSO,$q]);}
   $pdo->commit();$_SESSION['cart']=[];go(BASE.'/cart.php?done='.$oid);}}
}
$old=function($k,$d=''){return e($_POST[$k]??$d);};$f0=$_POST['fulfillment']??'pickup';$p0=$_POST['payment']??'gcash';
$pj=[];foreach($prods as $p)$pj[$p['id']]=['name'=>$p['name'],'price'=>(float)$p['price']];
$order=['Iced Shaken Brown Sugar Latte','Spanish Latte','Seasalt Latte','Orange Americano','Strawberry Cloud Latte','Matcha Latte','Ube Latte','Ube Matcha','Honey Cinnamon Shaken Espresso'];
$desc=['Iced Shaken Brown Sugar Latte'=>'Shaken, sweet & smooth','Spanish Latte'=>'Creamy everyday favorite','Seasalt Latte'=>'A little sweet, a little salty','Orange Americano'=>'Bright citrus meets espresso','Strawberry Cloud Latte'=>'Berry good, cloud soft','Matcha Latte'=>'Earthy, creamy, balanced','Ube Latte'=>'Your purple pick-me-up','Ube Matcha'=>'Two favorites, one cup','Honey Cinnamon Shaken Espresso'=>'Warm spice, served over ice'];
$cards=[];$rest=[];foreach($prods as $p){if(drink_url($p)!=='')$cards[]=$p;else $rest[]=$p;}
$title='Order coffee — Twofold Coffee';$simpleFooter=true;require 'includes/header.php';
if(isset($_GET['done'])): ?>
<main class="auth"><h2>Order #<?=(int)$_GET['done']?> received<i class="o">.</i></h2><p class="mute">Thank you! The shop will confirm payment details and pickup or delivery with you shortly.</p><a class="btn orange" href="orders.php">Track my order</a> <a class="btn light" href="index.php">Back to home</a></main>
<?php else: ?>
<main class="ord"><div>
<p class="crumb"><a href="index.php">Home</a> / Order coffee</p>
<div class="between"><div><h1 class="oh">Your coffee mood, sorted<i>.</i></h1><p class="mute">Pick your favorites. We’ll make the next part easy.</p></div><span class="pill">PANIQUI, TARLAC</span></div>
<div class="filters"><input id="q" type="search" placeholder="Search for your favorite drink" aria-label="Search drinks">
<div class="dd" id="fdd"><button type="button" class="ddb" id="ddbtn" aria-haspopup="true" aria-expanded="false"><span id="ddlbl">All drinks</span><i>▾</i></button>
<div class="ddp" id="ddp" hidden><p class="ddh">TYPE</p>
<label><input type="checkbox" data-g="type" value="espresso"> Espresso</label><label><input type="checkbox" data-g="type" value="latte"> Latte</label><label><input type="checkbox" data-g="type" value="americano"> Americano</label><label><input type="checkbox" data-g="type" value="matcha"> Matcha</label>
<p class="ddh">HOT OR ICED</p><label><input type="checkbox" data-g="temp" value="iced"> Iced</label><label><input type="checkbox" data-g="temp" value="hot"> Hot</label>
<button type="button" class="link" id="ddclear">Clear all</button></div></div></div>
<div class="between"><h2 class="sm">Find your favorite.</h2><span class="tiny mute" id="shown"></span></div>
<div class="pgrid"><?php foreach($cards as $p): ?>
<article class="pc" data-name="<?=e(strtolower($p['name']))?>" data-type="<?=e(dtype($p))?>" data-temp="<?=e($p['temperature']??'iced')?>">
<?=drink_html($p)?>
<div class="pb"><small class="cat"><?=e(dtype($p))?><?php $tp=$p['temperature']??'iced';echo ' · '.($tp==='both'?'hot or iced':$tp); ?></small><h3><?=e($p['name'])?></h3><p class="desc"><?=e($desc[$p['name']]??'')?></p>
<div class="between"><b><?=peso($p['price'])?></b><div class="qc" data-id="<?=$p['id']?>"></div></div></div></article><?php endforeach; ?></div>
<?php if($rest): ?><section class="more" id="more"><h3>More good things.</h3><div class="mcols"><?php foreach($rest as $p): ?>
<div class="lr" data-name="<?=e(strtolower($p['name']))?>" data-type="<?=e(dtype($p))?>" data-temp="<?=e($p['temperature']??'iced')?>"><span><?=e($p['name'])?></span><span class="price"><?=peso($p['price'])?></span><div class="qc" data-id="<?=$p['id']?>"></div></div><?php endforeach; ?></div></section><?php endif; ?>
<p class="tiny">☕ Want a little extra? Add up to 4 extra espresso shots (+<?=peso(EXTRA_ESPRESSO)?> each) to any drink from your bag.</p></div>

<aside class="bag"><div class="bagh"><h3>Your good-coffee bag.</h3><span class="pill" id="cups">0 cups</span></div>
<form method="post" id="orderForm" class="bagform"><?=csrf_field()?><input type="hidden" name="action" value="checkout"><input type="hidden" name="cart_json" id="cartJson"><input type="hidden" name="extra_json" id="extraJson"><div class="bagscroll"><div id="bagItems"></div>
<?php foreach($err as $m)echo '<p class="err">'.e($m).'</p>'; ?>
<h4><span class="step">01</span> How should we get it to you?</h4>
<div class="seg"><label><input type="radio" name="fulfillment" value="pickup" <?=$f0==='pickup'?'checked':''?>>Pickup</label><label><input type="radio" name="fulfillment" value="delivery" <?=$f0==='delivery'?'checked':''?>>Delivery</label></div>
<p class="tiny mute">Delivery is for Paniqui only. Coverage and fee are confirmed by the shop.</p>
<h4><span class="step">02</span> Your details</h4>
<?php if(!$logged): ?><p class="tiny mute">Sign in or create an account to place your order. Your bag will be saved.</p><p><a class="btn light" href="login.php?next=cart.php">Sign in</a> <a class="btn light" href="register.php">Create account</a></p>
<?php else: ?><label>Full name *<input name="name" value="<?=$old('name',$me['name'])?>" required></label>
<label>Mobile number *<input name="phone" value="<?=$old('phone',$me['phone'])?>" required></label>
<label id="addrRow">Delivery address *<input name="address" id="addr" value="<?=$old('address')?>" placeholder="Street, barangay, Paniqui"></label>
<label>Landmark / order note<input name="note" value="<?=$old('note')?>" placeholder="e.g. near the church, less ice"></label><?php endif; ?>
<h4><span class="step">03</span> Payment preference</h4>
<div class="seg"><label><input type="radio" name="payment" value="gcash" <?=$p0==='gcash'?'checked':''?>>GCash</label><label><input type="radio" name="payment" value="cash" <?=$p0==='cash'?'checked':''?>>Cash</label></div>
<p class="tiny mute">The shop confirms payment instructions after you order.</p></div>
<div class="bagfoot">
<div class="tl"><span id="subl">Subtotal</span><span id="subv"></span></div>
<div class="tl" id="feeRow"><span>Delivery · estimated fee</span><span><?=peso(DELIVERY_FEE)?></span></div>
<div class="tl big"><span>Total</span><span id="total"></span></div>
<?php if($logged): ?><button class="btn orange full" id="place">Place order →</button>
<?php else: ?><a class="btn orange full" href="login.php?next=cart.php">Sign in to place order →</a><?php endif; ?>
<p class="tiny mute center">By placing an order, you agree to the <a href="#" data-modal="terms">ordering terms</a> and <a href="#" data-modal="privacy">privacy policy</a>.</p>
</div></form></aside></main>
<script>
(function(){
var P=<?=json_encode($pj,JSON_FORCE_OBJECT|JSON_HEX_TAG|JSON_HEX_AMP)?>,C=<?=json_encode($_SESSION['cart'],JSON_FORCE_OBJECT)?>,X=<?=json_encode($_SESSION['extra'],JSON_FORCE_OBJECT)?>,EX=<?=EXTRA_ESPRESSO?>,FEE=<?=DELIVERY_FEE?>,CSRF=<?=json_encode($_SESSION['csrf']??'')?>;
function $(s){return document.querySelector(s)}function $$(s){return Array.prototype.slice.call(document.querySelectorAll(s))}
function money(n){return '₱'+Number(n).toLocaleString('en-PH')}
function esc(s){return String(s).replace(/[&<>"]/g,function(c){return{'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]})}
function count(){var n=0;for(var k in C)n+=C[k];return n}
function subt(){var s=0;for(var k in C)s+=(P[k].price+(X[k]||0)*EX)*C[k];return s}
function deliv(){var r=$('input[name=fulfillment]:checked');return !!r&&r.value==='delivery'}
function sync(id){var f=new FormData();f.append('action','set');f.append('id',id);f.append('qty',C[id]||0);f.append('extra',X[id]||0);f.append('csrf',CSRF);fetch('cart.php',{method:'POST',body:f}).catch(function(){});}
function setQ(id,q){q=Math.max(0,Math.min(20,q));if(q)C[id]=q;else{delete C[id];delete X[id];}render();sync(id);}
function render(){var n=count(),fee=(deliv()&&n)?FEE:0,h='';
 for(var id in C)h+='<div class="bi"><div class="between"><span>'+esc(P[id].name)+'</span><b>'+money((P[id].price+(X[id]||0)*EX)*C[id])+'</b></div><div class="between"><div class="qc" data-id="'+id+'"></div><button type="button" class="link" data-act="rm" data-id="'+id+'">Remove</button></div><div class="xs"><span>Extra espresso <small>+'+money(EX)+' each · max 4</small></span><div class="xc" data-id="'+id+'"><button type="button" data-act="xdec" aria-label="Fewer shots">−</button><span>'+(X[id]||0)+'</span><button type="button" data-act="xinc" aria-label="More shots">+</button></div></div></div>';
 $('#bagItems').innerHTML=h||'<p class="mute tiny">Your bag is empty. Add a drink to get started.</p>';
 $$('.qc').forEach(function(el){var q=C[el.dataset.id]||0;el.innerHTML=q?'<button type="button" data-act="dec" aria-label="Less">−</button><span>'+q+'</span><button type="button" data-act="inc" aria-label="More">+</button>':'<button type="button" class="add" data-act="inc">+ Add</button>';});
 $('#cups').textContent=n+(n===1?' cup':' cups');$('#subl').textContent='Subtotal · '+n+(n===1?' cup':' cups');$('#subv').textContent=money(subt());
 $('#feeRow').style.display=fee?'':'none';$('#total').textContent=money(subt()+fee);
 var ar=$('#addrRow');if(ar){ar.style.display=deliv()?'':'none';$('#addr').required=deliv();}
 var pl=$('#place');if(pl){pl.textContent='Place order · '+money(subt()+fee)+' →';pl.disabled=!n;}
 var hb=document.querySelector('.acct a[href$="cart.php"]');if(hb){var sv=hb.querySelector('svg');hb.innerHTML=(n?'Your bag · '+n:'Order coffee')+' '+(sv?sv.outerHTML:'');}}
document.addEventListener('click',function(e){var b=e.target.closest('[data-act]');if(!b)return;var id=b.closest('[data-id]').dataset.id,q=C[id]||0,a=b.dataset.act;
 if(a==='xinc'||a==='xdec'){var n=Math.max(0,Math.min(4,(X[id]||0)+(a==='xinc'?1:-1)));if(n)X[id]=n;else delete X[id];render();sync(id);return;}
 setQ(id,a==='inc'?q+1:a==='dec'?q-1:0);});
$$('input[name=fulfillment]').forEach(function(r){r.addEventListener('change',render);});
$('#orderForm').addEventListener('submit',function(e){if(!count()){e.preventDefault();return;}$('#cartJson').value=JSON.stringify(C);$('#extraJson').value=JSON.stringify(X);});
var term='',F={types:[],temps:[]};
function filt(){var v=0,m=0;$$('.pc,.lr').forEach(function(el){var ok=el.dataset.name.indexOf(term)>-1&&(!F.types.length||F.types.indexOf(el.dataset.type)>-1)&&(!F.temps.length||el.dataset.temp==='both'||F.temps.indexOf(el.dataset.temp)>-1);el.style.display=ok?'':'none';if(ok){v++;if(el.classList.contains('lr'))m++;}});
 $('#shown').textContent=v+(v===1?' drink':' drinks')+' · Prices in ₱';var mo=$('#more');if(mo)mo.style.display=m?'':'none';}
$('#q').addEventListener('input',function(){term=this.value.trim().toLowerCase();filt();});
var ddb=$('#ddbtn'),ddp=$('#ddp');
function ddUpdate(){F.types=[];F.temps=[];var nm=[];$$('#ddp input:checked').forEach(function(i){(i.dataset.g==='type'?F.types:F.temps).push(i.value);nm.push(i.parentNode.textContent.trim());});
 $('#ddlbl').textContent=nm.length?(nm.length>2?nm.length+' filters':nm.join(', ')):'All drinks';filt();}
ddb.addEventListener('click',function(e){e.stopPropagation();var o=ddp.hidden;ddp.hidden=!o;ddb.setAttribute('aria-expanded',o?'true':'false');});
ddp.addEventListener('click',function(e){e.stopPropagation();});ddp.addEventListener('change',ddUpdate);
$('#ddclear').addEventListener('click',function(){$$('#ddp input').forEach(function(i){i.checked=false;});ddUpdate();});
document.addEventListener('click',function(){ddp.hidden=true;ddb.setAttribute('aria-expanded','false');});
document.addEventListener('keydown',function(e){if(e.key==='Escape')ddp.hidden=true;});
render();filt();var er=$('.bagscroll .err');if(er)er.scrollIntoView({block:'nearest'});})();
</script>
<?php endif; require 'includes/footer.php'; ?>
