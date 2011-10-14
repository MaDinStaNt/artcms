<?php
require_once('../includes/app.php');

require_once(BASE_CLASSES_PATH . "ajax/ajaxhandlerpage.php");
$page = new CAjaxHandlerPage($app);
$page->no_html();
$page->parse_data();
$page->parse_state();
$page->output_page();
?>