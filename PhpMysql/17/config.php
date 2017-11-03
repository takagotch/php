<?php
// Smarty.class.phpへのフルパス
require('/usr/share/php/Smarty/Smarty.class.php');
$smarty = new Smarty( );

$smarty->template_dir = '/home/www/htmlkb/smarty/templates';
$smarty->compile_dir = '/home/www/htmlkb/smarty/templates_c';
$smarty->cache_dir = '/home/www/htmlkb/smarty/cache';
$smarty->config_dir = '/home/www/htmlkb/smarty/configs';

$blog_title="Coffee Talk Blog";
?>

