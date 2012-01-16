<?php
global $ajaxValidator;
$router = $app->get_module('Router');
$router->add_route('/', 'CIndexPage', 'CIndexPage.php', 'indexpage.tpl');
$router->add_route('/index.html', 'CIndexPage', 'CIndexPage.php', 'indexpage.tpl');

$router->add_route('/vk-auth', 'CVKapiAuthPage', 'CVKapiAuthPage.php');
$router->add_route('/fb-auth', 'CFBapiAuthPage', 'CFBapiAuthPage.php');
$router->add_route('/page-not-found.html', 'CNotFoundPage', 'CNotFoundPage.php', 'page_not_found.tpl');

$page = $router->get_current_page();
if (!is_null($page)) {
	$page->parse_data();
	$page->parse_state();
	$page->output_page();
}
elseif(!$ajaxValidator) {
	@header('HTTP/1.0 302 Moved Temporarily');
	@header('Status: 302 Moved Temporarily');
	@header('Location: http://'.$_SERVER['SERVER_NAME'].'/page-not-found.html');
}
?>