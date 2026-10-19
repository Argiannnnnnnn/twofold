<?php
session_start();
date_default_timezone_set('Asia/Manila');
// Auto-detects the folder URL (works in htdocs/twofold, htdocs/app_dev/twofold, etc.)
$__root=str_replace('\\','/',realpath($_SERVER['DOCUMENT_ROOT']));
$__app=str_replace('\\','/',dirname(__DIR__));
define('BASE',rtrim(substr($__app,strlen($__root)),'/'));
const DB_HOST='localhost', DB_NAME='twofold', DB_USER='root', DB_PASS='';
const STORE_ADDRESS='471 Sta. Rosa Street, Paniqui, Tarlac, Philippines';
const MAPS_URL='https://www.google.com/maps/place/471+Santa+Rosa+St,+Poblacion+Sur,+Paniqui,+2307+Tarlac/@15.667948,120.5792716,17z/data=!3m1!4b1!4m6!3m5!1s0x3396cb2cf01d5101:0xc156eca5427bb681!8m2!3d15.6679428!4d120.5818465!16s%2Fg%2F11rdc8svrk?entry=ttu&g_ep=EgoyMDI2MTAwNy4wIKXMDSoASAFQAw%3D%3D';
const EXTRA_ESPRESSO=30;
const DELIVERY_FEE=30; // shown as an estimate; shop confirms
const DEV_SHOW_CODE=true; // true = show the verification code on screen (no email set up). Set false once mail() works.
$pdo=new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',DB_USER,DB_PASS,
  [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
function e($s){return htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8');}
function peso($n){return '₱'.number_format((float)$n,0);}
function cart_count(){return array_sum($_SESSION['cart']??[]);}
function csrf_field(){$_SESSION['csrf']=$_SESSION['csrf']??bin2hex(random_bytes(16));return '<input type="hidden" name="csrf" value="'.$_SESSION['csrf'].'">';}
function csrf_check(){if(!hash_equals($_SESSION['csrf']??'',$_POST['csrf']??'')){http_response_code(400);exit('Invalid request. Go back and try again.');}}
function go($url){header('Location: '.$url);exit;}

function logo_url(){foreach(['logo.jpg','logo.jpeg','logo.png'] as $f)if(file_exists(dirname(__DIR__).'/assets/img/'.$f))return BASE.'/assets/img/'.$f;return '';}
function drink_url($p){$slug=trim(preg_replace('/[^a-z0-9]+/','-',strtolower($p['name'])),'-');$d=dirname(__DIR__).'/assets/img/';
 foreach([$p['image']??'','drinks/'.$slug.'.jpg','drinks/'.$slug.'.png','drinks/'.$slug.'.webp'] as $f)if($f&&file_exists($d.$f))return BASE.'/assets/img/'.$f;return '';}
function drink_html($p){$u=drink_url($p);return $u?'<img src="'.e($u).'" alt="'.e($p['name']).'" loading="lazy">':'<div class="ph">☕</div>';}
function is_customer(){return !empty($_SESSION['user_id']);}
function is_staff(){return !empty($_SESSION['staff_id']);}
