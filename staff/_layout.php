<?php
require_once __DIR__.'/../includes/config.php';
if(is_customer())go(BASE.'/index.php');
if(!is_staff())go(BASE.'/staff/login.php');
$stq=$pdo->prepare('SELECT * FROM staff WHERE id=?');$stq->execute([$_SESSION['staff_id']]);$staff=$stq->fetch();
if(!$staff||(int)($staff['active']??1)===0){unset($_SESSION['staff_id']);go(BASE.'/staff/login.php');}
if(($_POST['action']??'')==='logout'){csrf_check();unset($_SESSION['staff_id']);go(BASE.'/staff/login.php');}
$staffName=$staff['full_name']?:$staff['username'];
function oid($n){return 'TF-'.str_pad((string)$n,4,'0',STR_PAD_LEFT);}
function pill($t,$c=''){return '<span class="pill '.$c.'">'.e($t).'</span>';}
function stat_card($l,$v,$s=''){return '<div class="stat"><small>'.e(strtoupper($l)).'</small><b>'.$v.'</b><span>'.e($s).'</span></div>';}
function qurl($o=[]){$q=array_merge($_GET,$o);foreach($q as $k=>$v)if($v===null||$v==='')unset($q[$k]);return basename($_SERVER['SCRIPT_NAME']).($q?'?'.http_build_query($q):'');}
function tabs($cur,$opts){$h='<div class="tabs">';foreach($opts as $k=>$l)$h.='<a class="'.((string)$k===(string)$cur?'on':'').'" href="'.e(qurl(['tab'=>$k,'id'=>null,'new'=>null])).'">'.e($l).'</a>';return $h.'</div>';}
function tools($ph,$selects=[]){$h='<form class="tools" method="get"><input type="hidden" name="tab" value="'.e($_GET['tab']??'').'"><input name="q" value="'.e($_GET['q']??'').'" placeholder="'.e($ph).'">';
 foreach($selects as $n=>$opts){$h.='<select name="'.e($n).'" onchange="this.form.submit()">';foreach($opts as $k=>$l)$h.='<option value="'.e($k).'"'.(($_GET[$n]??'')===(string)$k?' selected':'').'>'.e($l).'</option>';$h.='</select>';}return $h.'</form>';}
function ptag($n){$n=strtolower($n);if(strpos($n,'espresso')!==false)return 'espresso';if(preg_match('/americano|aerocano/',$n))return 'americano';if(strpos($n,'matcha')!==false)return 'matcha';return 'latte';}
function cms_head($active,$crumb,$h1,$sub,$action=''){global $pdo,$staff,$staffName;
 $new=(int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetchColumn();
 $nav=['orders'=>['index.php','Orders'],'menu'=>['menu.php','Menu'],'users'=>['users.php','Users'],'payments'=>['payments.php','Payments'],'inventory'=>['inventory.php','Inventory'],'staff'=>['staff.php','Staff']]; ?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=e($crumb)?> — Twofold Staff</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"><link rel="stylesheet" href="<?=BASE?>/assets/cms.css"></head><body><div class="app">
<aside class="side"><a class="brand" href="index.php"><?php if($lg=logo_url()): ?><img src="<?=$lg?>" alt="Twofold Coffee"><?php else: ?><b>TWOFOLD COFFEE</b><?php endif; ?></a><small>STAFF WORKSPACE</small>
<nav><?php foreach($nav as $k=>$n): ?><a class="<?=$k===$active?'on':''?>" href="<?=$n[0]?>"><?=$n[1]?><?php if($k==='orders'&&$new): ?><span class="badge"><?=$new?></span><?php endif; ?></a><?php endforeach; ?></nav>
<a href="<?=BASE?>/index.php" style="font-size:.75rem;margin:.8rem .3rem">↗ View storefront</a>
<div class="tagc">Good coffee.<br>Great teamwork.<br><small>PANIQUI, TARLAC</small></div>
<div class="me"><b><?=e($staffName)?></b><br><span><?=e($staff['role']??'Staff')?></span><form method="post"><?=csrf_field()?><input type="hidden" name="action" value="logout"><button>Sign out ↗</button></form></div></aside>
<main class="main"><div class="top">Twofold Coffee / <?=e($crumb)?></div><div class="head"><div><h1><?=e($h1)?></h1><p class="sub"><?=date('l, j F Y')?> · <?=e($sub)?></p></div><div><?=$action?></div></div>
<?php }
function cms_foot(){ ?></main></div><script>document.querySelectorAll('tr[data-href]').forEach(function(r){r.addEventListener('click',function(){location.href=r.dataset.href;});});</script></body></html><?php }
function greet(){$h=(int)date('G');return $h<12?'Good morning':($h<18?'Good afternoon':'Good evening');}
