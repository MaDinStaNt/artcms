<?php
require_once('../includes/app.php');

function reloadStructure() {
        global $app;

        // adding manually back-office branch for corresponding usergroup
        $user = $app->get_module('User');
        if ($user->is_logged()) {
                $fileName = $GLOBALS['FilePath'].'includes/config/site-structure-'.strtolower($user->UserData['user_role_id']).'.php';
                if (file_exists($fileName)) 
               	{
                        include($fileName);
                        $navi =& $app->get_module('Navi');
                        $navi->ss['children'][7] = $ss;
                        if (isset($sc))
                            $navi->ss['children'][8] = $sc; 
                        if (isset($sn))
                            $navi->ss['children'][9] = $sn;
                }
        } 
        else 
        {
            $navi =& $app->get_module('Navi');
            if (isset($navi->ss['children'][6]))  unset($navi->ss['children'][6]);
            if (isset($navi->ss['children'][5]))  unset($navi->ss['children'][5]);
        }
}


// request analysis
reloadStructure();
$navi =& $app->get_module('Navi');
if ($navi->analyse_request()) 
{
    $className =& $navi->className;
} else 
{
    require_once(CUSTOM_CLASSES_PATH."admin/404page.php");

    $page = new C404Page($app, "admin/404page.tpl");
    $app->tv['is_404page'] = true;
    $page->parse_data();
    $page->parse_state();
    $page->output_page();
   die();
}
// loading codebehind and processing
require_once(CUSTOM_CLASSES_PATH.$navi->classPath);
$page = new $className($app, $navi->templatePath);

$page->parse_data();
$page->parse_state();
$page->output_page();
?>