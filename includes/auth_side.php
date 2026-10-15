<div class="aside"><small class="eyebrow">YOUR NEIGHBORHOOD COFFEE FIX</small>
<h1><?=e($l1)?><br><?=e($l2)?><i>.</i></h1><p class="lead"><?=e($sub)?></p>
<div class="figure"><?php $d=dirname(__DIR__).'/assets/img/'; if(file_exists($d.'story.jpg')): ?><img src="<?=BASE?>/assets/img/story.jpg" alt=""><?php elseif(file_exists($d.'hero.jpg')): ?><img class="natural" src="<?=BASE?>/assets/img/hero.jpg" alt="Paniqui's favorite drinks"><?php else: ?><div class="ph big">Add assets/img/hero.jpg</div><?php endif; ?><span>MADE FOR YOUR EVERYDAY. SHARED WITH YOUR PEOPLE.</span></div>
<p class="tiny">📍 Brewed for the neighborhood in Paniqui, Tarlac.</p></div>
