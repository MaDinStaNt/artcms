<?
class  CRouter  {
    var $Application;
    var $tv;
	var $Variables;
	var $CurrentPage;
    var $last_error;
    var $_routes;

	function CRouter(&$app)
	{
        $this->Application = &$app;
        $this->tv = &$app->template_vars;
        if (!array_key_exists('CurrentPage', $_SESSION)) $_SESSION['CurrentPage'] = array();
        $this->CurrentPage = &$_SESSION['CurrentPage'];
        $this->_routes = array();
        $this->route_arr = array();
        $this->uri_arr = array();
        $this->segments_count = 0;
        $this->arg_index = 1;
	}
	
	function add_route($template, $class, $php_path, $tpl_path, $args_arr = null)
	{
		$parts = array();
		$parts_tmp = explode(".", $template);
		$path = substr($parts_tmp[0], 1, strlen($parts_tmp[0]));
		if (strlen($path) > 0) {
			$parts = explode("/", $path);
		}
		$this->_routes[] = array('template' => $template, 'class' => $class, 'php_path' => $php_path, 'tpl_path' => $tpl_path, 'args_arr' => $args_arr, 'parts' => $parts);
	}
	
	function parse_uri($route_str, $uri_str, $key = 0, $args_arr)
	{
		if (preg_match('/^' . $route_str . '$/', $uri_str)) {
			if ($route_str != $uri_str) {
				$uri_var_arr = explode('.', $uri_str);
				$a = preg_split('/^([\w-_]+)$/', $uri_var_arr[0]);
				
				$this->Variables[$args_arr[$this->arg_index]] = $uri_var_arr[0];
				$this->arg_index ++;
			}
			if ($this->segments_count == $key) {
				return true;
			}
			$key = $key + 1;
			if (isset($this->route_arr[$key])&&isset($this->uri_arr[$key])) {
				return $this->parse_uri($this->route_arr[$key], $this->uri_arr[$key], $key, $args_arr);
			}
		}
	}
	
	function get_permalink_id($class = null)
	{
		if (is_null($class)) {
			if (isset($this->CurrentPage['permalink_id']))
				return $this->CurrentPage['permalink_id'];
		}
		else {
			$permalink_rs = $this->Application->DataBase->select_sql('permalink', array('system_name' => $class));
			if (($permalink_rs !== false)&&(!$permalink_rs->eof())) {
				return $permalink_rs->get_field('id');
			}
		}
		
		return null;
	}

	function get_current_page($key = 0, $route_str = null)
	{
		$temp = explode('?', $_SERVER['REQUEST_URI']);
		if (isset($temp[0])) {
			$this->uri_arr = explode('/', $temp[0]);
			$this->segments_count = sizeof($this->uri_arr) - 1;
			foreach ($this->_routes as $route_key => $route) {
				$this->route_arr = explode('/', $route['template']);
				$this->Variables = array();
				$this->arg_index = 1;
				if ($this->parse_uri($this->route_arr[0], $this->uri_arr[0], 0, $route['args_arr'])) {
					$this->CurrentPage = $route;
					require_once(CUSTOM_CLASSES_PATH . $route['php_path']);
					return new $route['class']($this->Application, $route['tpl_path']);
				}
			}
		}
		return null;
	}
}
?>