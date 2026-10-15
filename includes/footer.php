<?php $authPage=$authPage??false; ?>
<?php if($authPage||!empty($simpleFooter)): ?>
<footer class="foot"><div class="row small"><span>TWOFOLD COFFEE · GOOD COFFEE GOES FURTHER.</span><span class="mute"><a href="#" data-modal="privacy">Privacy policy</a> &nbsp; <a href="#" data-modal="terms">Ordering terms</a> &nbsp; Paniqui, Tarlac</span></div></footer>
<?php else: ?><footer class="foot"><div class="row"><div class="brand"><?php if($lg=logo_url()): ?><img class="logo-f" src="<?=$lg?>" alt="Twofold Coffee, Paniqui Tarlac"><?php else: ?><b class="logo-s">TWOFOLD COFFEE</b><?php endif; ?><span class="tag">GOOD COFFEE GOES FURTHER.</span></div>
<nav><a href="<?=BASE?>/index.php#menu">Our menu</a><a href="<?=MAPS_URL?>" target="_blank" rel="noopener">Visit us</a><?php if(is_staff()): ?><a href="<?=BASE?>/staff/index.php">Staff dashboard</a><?php elseif(is_customer()): ?><a href="<?=BASE?>/cart.php">Order coffee</a><a href="<?=BASE?>/orders.php">My orders</a><?php else: ?><a href="<?=BASE?>/cart.php">Order coffee</a><a href="<?=BASE?>/login.php">Customer sign in</a><a href="<?=BASE?>/register.php">Create account</a><a href="<?=BASE?>/staff/login.php">Staff sign in</a><?php endif; ?></nav></div>
<div class="row small mute"><span>© <?=date('Y')?> Twofold Coffee · Paniqui, Tarlac</span><span><a href="#" data-modal="privacy">Privacy policy</a> · <a href="#" data-modal="terms">Ordering terms</a></span></div></footer><?php endif; ?>

<dialog id="m-privacy"><h3>Privacy policy</h3>
<p>We collect only what we need to take your order: your name, email, phone number, delivery address (for delivery) and order history.</p>
<p>We use it to confirm and deliver your orders, contact you about them, and verify your account. We do not sell your information.</p>
<p>Passwords are stored as one-way hashes. Nobody at Twofold, including staff, can read your password.</p>
<p>To correct or delete your details, contact the shop in Paniqui.</p><button class="btn orange" data-close>Close</button></dialog>
<dialog id="m-terms"><h3>Ordering terms</h3>
<p>Prices are in Philippine pesos (₱) and may change. Extra espresso is +<?=peso(EXTRA_ESPRESSO)?>.</p>
<p>An order is confirmed once the shop accepts it. Items may sell out, and the shop will contact you if something is unavailable.</p>
<p>Delivery is available in Paniqui only. Delivery fees and payment details are confirmed by the shop before you pay.</p>
<p>To change or cancel an order, contact the shop as soon as possible. Orders already being prepared may not be cancelled.</p><button class="btn orange" data-close>Close</button></dialog>

<button id="toTop" type="button" aria-label="Back to top">↑</button>
<script>
(function(){var b=document.getElementById('toTop');
function chk(){var max=document.documentElement.scrollHeight-window.innerHeight;b.classList.toggle('show',max>0&&window.scrollY/max>=0.25);}
window.addEventListener('scroll',chk,{passive:true});chk();
b.addEventListener('click',function(){window.scrollTo({top:0,behavior:'smooth'});});
document.querySelectorAll('[data-modal]').forEach(function(a){a.addEventListener('click',function(e){e.preventDefault();document.getElementById('m-'+a.dataset.modal).showModal();});});
document.querySelectorAll('dialog').forEach(function(d){d.addEventListener('click',function(e){if(e.target===d||e.target.hasAttribute('data-close'))d.close();});});
document.querySelectorAll('.pw button').forEach(function(t){t.addEventListener('click',function(){var i=t.parentNode.querySelector('input');var s=i.type==='password';i.type=s?'text':'password';t.textContent=s?'Hide':'Show';});});
})();
</script></body></html>
