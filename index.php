<?php $title='Twofold Coffee — Good coffee. Great company.'; require 'includes/header.php';
$favs=$pdo->query('SELECT * FROM products WHERE is_favorite=1 AND available=1 ORDER BY id')->fetchAll();
$menu=[]; foreach($pdo->query('SELECT * FROM products ORDER BY id') as $p){$menu[$p['category']][]=$p;}
function img($f){return $f&&file_exists(__DIR__.'/assets/img/'.$f)?'<img src="'.BASE.'/assets/img/'.e($f).'" alt="">':'<div class="ph">☕</div>';}
function addform($p){return '<form method="post" action="'.BASE.'/cart.php">'.csrf_field().'<input type="hidden" name="action" value="add"><input type="hidden" name="id" value="'.$p['id'].'"><button class="link" '.($p['available']?'':'disabled').'>'.($p['available']?'Add to bag ↗':'Sold out').'</button></form>';}
?>
<section class="hero"><div><span class="pill">YOUR NEIGHBORHOOD COFFEE FIX</span>
<h1>Good coffee<i>.</i><br>Great company<i>.</i></h1>
<p class="lead">Your daily pause, your favorite people, your kind of cup. Meet Twofold Coffee — brewed for the neighborhood in Paniqui, Tarlac.</p>
<a class="btn orange" href="#favorites">Find your next favorite ↗</a> <a class="btn light" href="#menu">Explore the menu</a>
<p class="tiny"><i>✱</i> GOOD COFFEE GOES FURTHER.</p></div>
<a class="hero-card" href="#favorites"><?php if(file_exists(__DIR__.'/assets/img/hero.jpg')): ?><img src="<?=BASE?>/assets/img/hero.jpg" alt="Paniqui's favorites this week"><?php else: ?><div class="ph big">Add assets/img/hero.jpg</div><?php endif; ?><span>THE CUPS PANIQUI KEEPS COMING BACK FOR ↗</span></a></section>

<div class="strip"><span>GOOD COFFEE</span><i>✱</i><span>LOCAL LOVE</span><i>✱</i><span>TWO FOLD, ONE COMMUNITY</span><i>✱</i><span>PANIQUI, TARLAC</span></div>

<section class="sec tan" id="favorites"><small class="eyebrow">THE USUALS, FOR A REASON</small>
<div class="between"><div><h2>Paniqui’s favorites.</h2><p class="mute">Start with a crowd favorite. Stay for the next one.</p></div><a class="btn light" href="#menu">See the full menu →</a></div>
<div class="grid4"><?php foreach($favs as $p): ?><article class="card"><?=drink_html($p)?><div class="cb"><span class="pill"><?=e($p['badge'])?></span><h3><?=e($p['name'])?></h3><div class="between"><span><?=peso($p['price'])?></span></div></div></article><?php endforeach; ?></div></section>

<section class="sec cream" id="menu"><small class="eyebrow">A CUP FOR EVERY KIND OF DAY</small>
<div class="between"><h2>What’s your coffee mood?</h2><span class="tiny mute">All prices in Philippine pesos (₱)</span></div>
<div class="cols"><?php foreach($menu as $cat=>$items): ?><div><h3 class="cat"><?=e($cat)?></h3>
<?php foreach($items as $p): ?><div class="item"><span><?=e($p['name'])?></span><span class="price"><?=peso($p['price'])?></span></div><?php endforeach; ?></div><?php endforeach; ?></div>
<p class="tiny"><b>MAKE IT YOURS</b> &nbsp; <span class="mute">Extra espresso + <?=peso(EXTRA_ESPRESSO)?></span></p></section>

<section class="sec soft split" id="story"><div class="ph photo"><?= file_exists(__DIR__.'/assets/img/story.jpg')?'<img src="'.BASE.'/assets/img/story.jpg" alt="">':'Add assets/img/story.jpg' ?></div>
<div><small class="eyebrow">ROOTED IN PANIQUI</small><h2>More than a cup.<br>A little connection.</h2>
<p class="mute">From your first sip before work to that long catch-up with a friend, there’s room for both at Twofold. Familiar favorites, playful flavors, and good coffee that goes further.</p>
<p>Made for your everyday. Shared with your people.</p><a class="btn light" href="<?=MAPS_URL?>" target="_blank" rel="noopener">Find your Twofold moment ↗</a></div></section>

<section class="sec cream"><small class="eyebrow">LESS SCROLLING. MORE SIPPING.</small><h2>Your next cup, in three steps.</h2>
<div class="steps"><div><b>01</b><h4>Pick your kind of cup</h4><p class="mute">Coffee, matcha, or ube? Browse the menu and add your favorites to your bag.</p></div>
<div><b>02</b><h4>Make a little plan</h4><p class="mute">Choose pickup or delivery, share your details, and select how you’ll pay.</p></div>
<div><b>03</b><h4>We’ll take it from here</h4><p class="mute">Review your order, then follow its progress from preparation to handoff.</p></div></div>
<div class="two"><div class="card pad"><h4>Swing by. Sip happy.</h4><p class="mute">Collect your order at Twofold Coffee in Paniqui. A quick stop, a familiar face, a fresh cup.</p></div>
<div class="card pad"><h4>Your coffee, on the way.</h4><p class="mute">Add your Paniqui address at checkout. Delivery availability and fees are confirmed with your order.</p></div></div></section>

<section class="sec navy split" id="visit"><div><small class="eyebrow">YOUR LOCAL COFFEE STOP</small><h2 class="white">Meet you in Paniqui.</h2>
<p>Bring a friend. Pick a favorite. Make a regular day feel a little more special at Twofold Coffee.</p><p>📍 <?=e(STORE_ADDRESS)?></p>
<p class="tiny">For the exact pickup point and today’s shop hours, confirm with Twofold before you head over.</p>
<a class="btn orange" href="<?=BASE?>/cart.php">Order for pickup ↗</a> <a class="btn light" href="<?=MAPS_URL?>" target="_blank" rel="noopener">Open in Google Maps ↗</a></div>
<div class="hero-card"><?php if(file_exists(__DIR__.'/assets/img/hero.jpg')): ?><img src="<?=BASE?>/assets/img/hero.jpg" alt=""><?php else: ?><div class="ph big">hero.jpg</div><?php endif; ?><span>FAMILIAR FACES. NEW FAVORITES.</span></div></section>

<section class="sec cream split"><div><small class="eyebrow">A FEW THINGS TO KNOW</small><h2>Before your<br>first sip.</h2><p class="mute">A little help with your next order.</p></div>
<div class="faq"><details><summary>Can I order without coffee?</summary><p>Absolutely. Explore the matcha and ube series, including Matcha Latte, Strawberry Matcha, and Ube Latte.</p></details>
<details><summary>Can I choose pickup or delivery?</summary><p>Both options are shown at checkout. Confirm delivery coverage and any fee with the shop before payment.</p></details>
<details><summary>How do I pay for my order?</summary><p>Choose a payment preference at checkout. The shop confirms the available method and payment details for your order.</p></details></div></section>

<section class="cta"><div><h2 class="white">Your next good day starts here.</h2><p>One favorite cup. A whole lot of local love.</p></div><a class="btn navybtn" href="<?=BASE?>/cart.php">Let’s order coffee ↗</a></section>
<?php require 'includes/footer.php'; ?>
