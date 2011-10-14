<?
require_once(BASE_CLASSES_PATH .'adminpage.php');

class C404Page extends CAdminPage
{
        function C404Page(&$app, $template)
        {
              parent::CAdminPage($app, $template);
       }

        function parse_data()
        {
                if (!parent::parse_data())
                        return false;
                return true;
        }
}
?>