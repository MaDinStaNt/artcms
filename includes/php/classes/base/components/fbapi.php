<?
define('FB_API_ID', '212462748837019');
define('FB_SECRET_KEY', '5fd2d8200cb6cf16be7eda50750f1b40');
define('FB_STATUS_PENDING_ANSWER', '1');
define('USER_LEVEL_FBUSER', 10);

class FBauth
{
	protected $Application;
	protected $DataBase;
	protected $tv;
	protected $last_error;
	protected $rules = 'user_about_me,friends_about_me,user_photos,friends_photos,read_friendlists';
	protected $oauth_path = 'https://www.facebook.com/dialog/';
	protected $graph_oauth_path = 'https://graph.facebook.com/oauth/';
	protected $access_token;
	protected $expires_in;
	public $UserData;
	
	function FBauth(&$app)
	{
		$this->Application = &$app;
		$this->tv = &$app->tv;
		$this->DataBase = &$this->Application->DataBase;
		if($app->User->is_logged())
			return true;
		else 
		{
			if(is_object($this->VKauth) && $this->VKauth->is_logged())
				return true;
				
			if (!array_key_exists('UserData', $_SESSION)) $_SESSION['UserData'] = array();
				$this->UserData = &$_SESSION['UserData'];
			
			if($this->UserData['is_fb'] === true)	
				$this->synchronize();
		}
	}
	
	function get_last_error()
	{
		return $this->last_error;
	}
	
	function get_rules()
	{
		return urlencode($this->rules);
	}
	
	function get_access_token()
	{
		return (($this->access_token !== false && strlen($this->access_token) > 0) ? $this->access_token : false);
	}
	
	private function synchronize() 
    {
        if (isset($this->UserData['id']))
        {
            $rs = $this->DataBase->select_sql('fb_user', array('id' => $this->UserData['id'], 'access_token' => $this->UserData['access_token']));
            if (($rs!==false) && (!$rs->eof()))
            {
            	if(time() < intval($rs->get_field('expires_in')))
            	{
			    	row_to_vars($rs, $this->UserData);
			    	$this->UserData['is_fb'] = true; 
			    	$this->access_token = $this->UserData['access_token'];
			    	$this->expires_in = $this->UserData['expires_in'];
            	}
            	else 
            	{
            		$this->DataBase->update_sql('fb_user', array('access_token' => ''), array('id' => $rs->get_field('id')));
            		$this->UserData = array();
            	}
            }
            else
                $this->UserData = array();
        }
        else
            $this->UserData = array();
    }
    
    function is_logged($role_id = null)
	{
		if (is_null($role_id))
            return ( (intval($this->UserData['id']) > 0) && (strlen($this->UserData['access_token']) > 0) && $this->UserData['is_fb'] === true);
        else
            return ( (intval($this->UserData['id']) > 0) && (strlen($this->UserData['access_token']) > 0) && $this->UserData['is_fb'] === true && $this->UserData['user_role_id'] == $role_id);
	}
	
	function login($code, $return_url)
	{
		if(strlen($return_url) == 0 || !preg_match('/^(http(s)?:\/\/){1}([a-z,0-9]*([-][a-z,0-9]+)*\.)+[a-z]+(:[0-9]+)?(\/.*)?$/i', $return_url))
			system_die('Invalid input data $return_url (string)', 'FBauth -> login();');
		
		if(!is_string($code) || strlen($code) == 0)
			system_die('Invalid input data $code (string)', 'FBauth -> login()');
			
		$json_curl = curl_get_contents($this->graph_oauth_path.'access_token?client_id='.FB_API_ID.'&client_secret='.FB_SECRET_KEY.'&code='.urlencode($code).'&redirect_uri='.$return_url);
		$res = array();
		parse_str($json_curl,$res);
		if($res !== false && !empty($res))
		{
			if(!isset($res['access_token']) || !isset($res['expires']))
			{
				$this->last_error[] = $this->Application->Localizer->get_string('fb_login_error');
				return false;
			}
			$this->access_token = $res['access_token'];
			$this->expires_in = 86400;
			return $res;
		}
		$this->last_error[] = $this->Application->Localizer->get_string('internal_error');
		return false;
	}
	
	function get_login_url($return_url, $display = 'page')
	{
		if(strlen($return_url) == 0 || !preg_match('/^(http(s)?:\/\/){1}([a-z,0-9]*([-][a-z,0-9]+)*\.)+[a-z]+(:[0-9]+)?(\/.*)?$/i', $return_url))
			system_die('Invalid input data $return_url (string)', 'FBauth -> get_login_url();');
			
		if(strlen($display) == 0 || !in_array($display, array('page', 'popup', 'touch', 'wap')))
			system_die('Invalid input data $display (string list values => page, popup, touch, wap', 'FBauth -> get_login_url();');
			
		return $this->oauth_path.'oauth?client_id='.FB_API_ID.'&scope='.$this->rules.'&redirect_uri='.$return_url.'&display='.$display.'';
	}
	
	function dblogin_user($fb_user_data)
	{
		if(!is_array($fb_user_data) || !isset($fb_user_data['id']) || !isset($fb_user_data['first_name']) || !isset($fb_user_data['last_name']))
		{
			$this->last_error = $this->Application->Localizer->get_string('invalid_input');
			return false;
		}
		
		$rs = $this->DataBase->select_sql('fb_user', array('id' => $fb_user_data['id']));
		if($rs === false)
		{
			$this->last_error = $this->Application->Localizer->get_string('internal_error');
			return false;
		}
		if(!file_exists(ROOT .'pub/FBusers/'))
		{
			@mkdir(ROOT .'pub/FBusers/');
			@chmod(ROOT .'pub/FBusers/', 0777);
		}
		if($rs->eof())
		{
			$insert_arr = array(
				'id' => $fb_user_data['id'],
				'user_role_id' => USER_LEVEL_FBUSER,
				'name' => $fb_user_data['name'],
				'last_name' => $fb_user_data['last_name'],
				'first_name' => $fb_user_data['first_name'],
				'link' => $fb_user_data['link'],
				'gender' => (($fb_user_data['gender'] && $fb_user_data['gender'] === 'female') ? 'female' : 'male'),
				'access_token' => $this->access_token,
				'expires_in' => time() + intval($this->expires_in),
				'last_login_date' => date('Y-m-d H:i:s')
			);
			$res = $this->Application->FBapi->get_user_picture($fb_user_data['id']);
			if($res !== false)
			{
				if(!file_exists(ROOT .'pub/FBusers/'. $fb_user_data['id'] .'/'))
				{
					@mkdir(ROOT .'pub/FBusers/'. $fb_user_data['id'] .'/');
					@chmod(ROOT .'pub/FBusers/'. $fb_user_data['id'] .'/', 0777);
				}
				if(file_exists(ROOT .'pub/FBusers/'. $fb_user_data['id'] .'/'. $fb_user_data['id'] .'png'))
				{
					$Images->delete('fbuser_photo', ROOT .'pub/FBusers/'. $fb_user_data['id'] .'/'.$fb_user_data['id'].'.png', array('fb_user_id' => $fb_user_data['id']));
					@unlink(ROOT .'pub/FBusers/'. $fb_user_data['id'] .'/');
				}
				$Images = $this->Application->get_module('Images');
				$sf_res = imagepng($res, ROOT .'pub/FBusers/'. $fb_user_data['id'] .'/'.$fb_user_data['id'].'.png');
				imagedestroy($res);
				if($sf_res !== false)
				{
					$insert_arr['photo_filename'] = $fb_user_data['id'].'.png';
					$Images->create('fbuser_photo', ROOT .'pub/FBusers/'. $fb_user_data['id'] .'/'.$fb_user_data['id'].'.png', array('fb_user_id' => $fb_user_data['id']));
				}
				else 
					$insert_arr['photo_filename'] = '';
			}
			else 
				$insert_arr['photo_filename'] = '';
				
			if(!$this->DataBase->insert_sql('fb_user', $insert_arr, false))
			{
				$this->last_error = $this->Application->Localizer->get_string('database_error');
				return false;
			}
		}
		else 
		{
			$update_arr = array(
				'id' => $fb_user_data['id'],
				'user_role_id' => USER_LEVEL_VKUSER,
				'name' => $fb_user_data['name'],
				'last_name' => $fb_user_data['last_name'],
				'first_name' => $fb_user_data['first_name'],
				'link' => $fb_user_data['link'],
				'gender' => (($fb_user_data['gender'] && $fb_user_data['gender'] === 'female') ? 'female' : 'male'),
				'access_token' => $this->access_token,
				'expires_in' => time() + intval($this->expires_in),
				'last_login_date' => date('Y-m-d H:i:s')
			);
			$res = $this->Application->FBapi->get_user_picture($fb_user_data['id']);
			if($res !== false)
			{
				if(!file_exists(ROOT .'pub/FBusers/'. $fb_user_data['id'] .'/'))
				{
					@mkdir(ROOT .'pub/FBusers/'. $fb_user_data['id'] .'/');
					@chmod(ROOT .'pub/FBusers/'. $fb_user_data['id'] .'/', 0777);
				}
				$Images = $this->Application->get_module('Images');
				$sf_res = imagepng($res, ROOT .'pub/FBusers/'. $fb_user_data['id'] .'/'.$fb_user_data['id'].'.png');
				imagedestroy($res);
				if($sf_res !== false)
				{
					$update_arr['photo_filename'] = $fb_user_data['id'].'.png';
					$Images->create('fbuser_photo', ROOT .'pub/FBusers/'. $fb_user_data['id'] .'/'.$fb_user_data['id'].'.png', array('fb_user_id' => $fb_user_data['id']));
				}
				else 
					$update_arr['photo_filename'] = '';
			}
			else 
				$update_arr['photo_filename'] = '';
				
			if(!$this->DataBase->update_sql('fb_user', $update_arr, array('id' => $fb_user_data['id'])))
			{
				$this->last_error = $this->Application->Localizer->get_string('database_error');
				return false;
			}
		}
		$this->UserData['id'] = $fb_user_data['id'];
		$this->UserData['access_token'] = $this->access_token;
		$this->UserData['is_fb'] = true;
		return true;
	}
	
	public function set_logged_vars(&$tv)
    {
        if ($this->is_logged())
        {
            $tv['is_logged'] = true;
            foreach($this->UserData as $k => $v)
                $tv['logged_user_'.$k] = $v;
                
            $tv['is_FBlogged'] = true;
        }
    }
    
    public function get_infos()
    {
    	return ((isset($_SESSION['fb_success'])) ? $_SESSION['fb_success'] : false);
    }
    
    public function get_errors()
    {
    	return ((isset($_SESSION['fb_error'])) ? $_SESSION['fb_error'] : false);
    }
    
    public function clear_messages()
    {
    	if(isset($_SESSION['fb_error']))
    		unset($_SESSION['fb_error']);
    		
    	if(isset($_SESSION['fb_success']))
    		unset($_SESSION['fb_success']);
    }
}

class FBapi
{
	protected $Application;
	protected $DataBase;
	protected $tv;
	protected $last_error;
	protected $methods_path = 'https://graph.facebook.com/';
	
	function FBapi(&$app)
	{
		$this->Application = &$app;
		$this->tv = &$app->tv;
		$this->DataBase = &$this->Application->DataBase;
	}
	
	function get_last_error()
	{
		return $this->last_error;
	}
	
	function set_rules($rules)
	{
		if(!is_array($rules) && !is_string($rules))
			system_die('Invalid input $rules (string or array)', 'VKapi -> set_rules()');
			
		if(is_array($rules))
			$this->rules = join(',', $rules);
		else 
			$this->rules = trim(strval($rules));
	}

	function get_user($id)
	{
		if(((is_string($id) || is_numeric($id)) && strlen($id) == 0) || (!is_numeric($id) && !is_string($id)))
			system_die('Invalid input $id (string)', 'FBapi -> get_user()');
			
		$access_token = $this->Application->FBauth->get_access_token();
			
		$json = curl_get_contents($this->methods_path.urlencode($id).'/'.(($access_token !== false) ? '?access_token='.urlencode($access_token) : null));
		$res = parse_json($json);
		if($res !== false)
		{
			if(isset($res['error']))
			{
				$this->last_error = $this->Application->Localizer->get_string('fb_error_message', $res['error']['message']);
				return false;
			}
			elseif(!isset($res['id']) || !isset($res['first_name']) || !isset($res['last_name']))
			{
				$this->last_error = $this->Application->Localizer->get_string('internal_error');
				return false;
			} 
			
			return $res;
		}
	}
	
	function get_user_picture($id, $type = 'large')
	{
		if(((is_string($id) || is_numeric($id)) && strlen($id) == 0) || (!is_numeric($id) && !is_string($id)))
			system_die('Invalid input $id (string)', 'FBapi -> get_user_pricture()');
			
		$access_token = $this->Application->FBauth->get_access_token();
			
		$res = curl_get_contents($this->methods_path.urlencode($id).'/picture?type='.$type.(($access_token !== false) ? '&access_token='.urlencode($access_token) : null));
		if($res !== false)
		{
			$r = parse_json($res);
			if($r !== false && isset($r['error']))
			{
				$this->last_error = $this->Application->Localizer->get_string('fb_error_message', $r['error']['message']);
				return false;
			}
			$image = imagecreatefromstring($res);
			return (($image !== false) ? $image : false);
		}
		$this->last_error = $this->Application->Localizer->get_string('curl_cannot_get_content');
		return false;
	}
}
?>