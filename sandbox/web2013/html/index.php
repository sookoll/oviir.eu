<?php

// new application
if (!defined('APP_PATH')) {
	define('APP_PATH', dirname(__FILE__));
}

// include and call framework
include APP_PATH.'/library/Framework.php';
$app = new Framework();

// set configuration
$app->setConfig('./library/Framework/Configs/core.ini');

// load support classes if neccessary
$app->loadSupportClasses();

// set public user
if(!isset($_SESSION['Framework']))
	$_SESSION['Framework'] = array();

if(!isset($_SESSION['Framework']['auth_status'])){
	$_SESSION['Framework']['auth_status'] = 'public';
	$_SESSION['Framework']['current_user'] = array(
		id => 0,
		auth_level => 'guest',
		full_name => 'Public viewer',
		status => 'hidden'
	);
}

$tags = array();

// connect to db
$app->db->connect($app->conf['database']['host'],$app->conf['database']['port'],$app->conf['database']['name'],$app->conf['database']['user'],$app->conf['database']['pass']);

// users administration
$app->users = new Framework_Modules_login_modul($app);
$tags = array_merge($tags,$app->users->loadUserContainer());

// add dynamic modules loading via $_GET parameter
$app->enableRequestModulsFromGet();

// menu
$menu = new Framework_Modules_menu_modul($app);
$tags = array_merge($tags,$menu->buildMenu($app->modul->menuitem));

// call front method (reserved for get requests)
$app->modul->front();
$tags = array_merge($tags,$app->modul->getOutput());

$template = FRAMEWORK_PATH.DIRECTORY_SEPARATOR.$app->conf['core']['path'].DIRECTORY_SEPARATOR.$app->conf['core']['templates_path'].DIRECTORY_SEPARATOR.$app->conf['core']['template_name'].DIRECTORY_SEPARATOR.'html'.DIRECTORY_SEPARATOR.'index.html';
$html = $app->templator->parse($template,$tags);

// disconnect from db
$app->db->disconnect();

// default tags for translator
$tags = array(
	'url' =>			$app->conf['core']['url'],
	'ajax-handler' =>	$app->conf['core']['ajax_handler'],
	'modul-param' =>	$app->conf['core']['module_param'],
	'modul' =>			'',
	'libs-path' =>		'/libs',
	'tmpl-path' =>		$app->getTemplateUrl(),
	'js-path' =>		'',
	'title' =>			_('Oviiride suguvõsa'),
	'image-path' =>		$app->conf['core']['url'].'/images/image_src.png',
	'user-styles' =>	'',
	'modul-styles' =>	'',
	'time' =>			'',
	'user' =>			'',
	'menu' =>			'',
	'quotes' =>			'',
	'content' =>		'<div class="inner">Puuduvad õigused sellele lehele! Logi sisse.</div>',
	'user-scripts' =>	'',
	'modul-scripts' =>	'',
	'modal-content' =>	''
);

echo $app->templator->parse($html,$tags);
//print_r($_SESSION);
?>