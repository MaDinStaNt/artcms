<?php
/*
--------------------------------------------------------------------------------
        Navigation Module v1.0

        public methods:
                analyse_request()

        public properties:
                ss                - site strucure (array)
                sections        - sequence of uri sections
                className
                classPath
                templatePath

        see also:
                - CUSTOM_CONTROLS_PATH . navi.php - control CNavi to insert
                  navigation entities (visual blocks) on the page
                - includes/config/site-structure.php - site's structure static
                  declaration file
--------------------------------------------------------------------------------
*/

class CNaviModule
{
        var $app;
        var $ss;                        // site strucure (array)
        var $requestSeparator;
        var $sections, $titles;                // sequence of uri sections, and corresponding long titles
        var $className, $classPath, $templatePath;


        function CNaviModule(&$app)        {
                $this->app =& $app;
                global $FilePath;

                if ($this->app->User->is_logged()) {
	                if (file_exists($FilePath .'includes/config/site-structure-'.$this->app->User->UserData['user_role_id'].'.php')) {
	                	include($FilePath.'includes/config/site-structure-'.$this->app->User->UserData['user_role_id'].'.php');
	                }
	                else {
	                	include($FilePath.'includes/config/site-structure-empty.php');
	                }
                }
                else {
                	include($FilePath.'includes/config/site-structure-empty.php');
                }
                $this->ss =& $ss;

                // analyse request uri and get tags sequence ($tags)
                $request = isset($_GET['r']) ? $_GET['r'] : '';
                //$_SERVER['PHP_SELF'] = '/';    // quite nasty too ;)
                $this->requestSeparator = '.';
                
                if ($request and substr($request, 0, 1)==$this->requestSeparator) {        // cutting off leading slash
                        $request = substr($request, 1);
                }
                if ($request and substr($request, -1)==$this->requestSeparator) {         // cutting off trailing slash
                        $request = substr($request, 0, strlen($request)-1);
                }
                global $RootPath;
                $mount = str_replace('/', '', $RootPath);
                if ($mount) {
                        if (substr($request, 0, strlen($mount))==$mount) {
                                $request = substr($request, strlen($mount)+1);
                        }
                }

                if ($request) {
                        $sections = explode($this->requestSeparator, $request);
                } else {
                        $sections = array();
                }
                $this->sections =& $sections;
                $this->titles = array();
        }


        function analyse_request() {
                $parent = $this->ss;
                $child = $this->ss;
                $level = 0;
                $cClass = isset($child['class']) ? $child['class'] : false;
                $cTemplate = isset($child['template']) ? $child['template'] : false;
                $this->titles[] = $child['title'];
                $found = true;

                foreach ($this->sections as $section) {
                        $found = false;
                        if ($children =& $parent['children']) {
                                reset($children);
                                while ((bool)($child = current($children)) and !$found) {
                                        if ($child['tag']==$this->sections[$level]) {
                                                $cClass = isset($child['class']) ? $child['class'] : $cClass;
                                                $cTemplate = isset($child['template']) ? $child['template'] : $cTemplate;
                                                $this->titles[] = $child['title'];
                                                $parent =& $child;
                                                $level++;
                                                $found = true;
                                        } else {
                                                next($children);
                                        }
                                }
                        }
                }
                if ($found) {
                        if ($cClass) {
                                // classPath
                                $this->classPath = strtolower($cClass);
                                // className
                                if ($slashPos = strrpos($cClass, '/')) {
                                        $this->className = 'C'.substr($cClass, $slashPos+1, strlen($cClass)-$slashPos-5);
                                } else {
                                        $this->className = 'C'.substr($cClass, 0, strlen($cClass)-$slashPos-4);
                                }
                        } else {
                                $this->className = 'CDefault';
                                $this->classPath = 'default.php';
                        }
                        if ($cTemplate) {
                                $this->templatePath = strtolower($cTemplate);
                        } else {
                                $this->templatePath = 'default.tpl';
                        }
                        $currentPage = urldecode($_SERVER['REQUEST_URI']);
                        $currentPage = substr($currentPage, 0, strcspn($currentPage, '?'));
                        $this->app->tv['this'] = $this->getUri('this');
                        $this->app->tv['thisAnd'] = $this->getUri('this', true);

                        return true;
                } else {
                        return false;
                }
        }

        /*
                parameters:
                        string target                - 'this' | 'parent' | uri
                        boolean and                        - "and-mode" (uri string prepared to add get parameters)
                returns:
                        string uri
        */
        function getUri($target='this', $and=false)
        {
                // get sections

                // . (this)
                if ($target=='this' or $target=='.')
                {
                        $sections = $this->sections;

                }
                // / (home)
                elseif ($target=='home' or $target=='/')
                {
                        $sections = array();
                }
                // .. (parent)
                elseif ($target=='parent' or $target=='..')
                {
                        $sections = $this->sections;
                        array_pop($sections);
                        if($sections)
                        {
	                        $cache = &$_SESSION['cache']['AdminBreadcrumb'];
	                        $vars = array();
	                        if(isset($cache[$sections[0]])) $vars = $cache[$sections[0]][count($sections) - 1];
	                        $vars_str = '';
	                        foreach ($vars as $var => $value) $vars_str .= "&{$var}={$value}";
                        }
                }
                // ./* (relative child)
                elseif (substr($target, 0, 2)=='./')
                {
                        $target = substr($target, 2);
                        if ($target and substr($target, -1)=='/')  $target = substr($target, 0, strlen($target)-1);
                        $sections = explode('/', $target);
                        $sections = array_merge($this->sections, $sections);
                }
                // ../* (relative sibling)
                elseif (substr($target, 0, 3)=='../')
                {
                        $target = substr($target, 3);
                        if ($target and substr($target, -1)=='/')  $target = substr($target, 0, strlen($target)-1);
                        $sections = explode('/', $target);
                        $sectionsCopy = $this->sections;
                        array_pop($sectionsCopy);
                        $sections = array_merge($sectionsCopy, $sections);
                        unset($sectionsCopy);
                }
                // full uri
                else
                {
                        if (substr($target, 0, 1)=='/')  $target = substr($target, 1);
                        if ($target and substr($target, -1)=='/')  $target = substr($target, 0, strlen($target)-1);
                        $sections = explode('/', $target);
                }
                // prepare uri
                global $RootPath;
                $uri = $RootPath . 'admin/';
                if ($sections) {
                        foreach ($sections as $pos => $section) {
                                if ($pos)
                                    $uri .= '.';
                                else
                                	$uri .= '?r=';
                                
                                $uri .= $section;
                        }
                        $uri .= '';
                        if ($and) {
                                $uri .= '&';
                        }
                } else {
                        if ($and)  $uri .= '?';
                }
                return $uri.((isset($vars_str)) ? $vars_str : null);
        }

}
?>