<?
define('VK_API_ID', '2757030');
define('VK_SECRET_KEY', 'HJvLSFlOGVNAFGVsuN8x');
define('VK_STATUS_PENDING_ANSWER', '1');
define('USER_LEVEL_VKUSER', 10);

class VKapi
{
	protected $Application;
	protected $DataBase;
	protected $tv;
	protected $last_error;
	protected $rules = 'notify,friends';
	protected $methods_path = 'https://api.vkontakte.ru/method/';
	
	function VKapi(&$app)
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

	function get_user($uids, $fields = 'nickname,screen_name,sex,photo_big', $name_case = 'nom')
	{
		if(((is_string($uids) || is_numeric($uids)) && strlen($uids) == 0) || ((!is_string($uids) && !is_numeric($uids)) && (!is_array($uids) || empty($uids))))
			system_die('Invalid input $uids (string list $uids separatet "," or array)', 'VKapi -> get_user()');
		if(!is_string($name_case) || strlen($name_case) == 0 || !in_array($name_case, array('nom', 'gen', 'dat', 'acc', 'ins', 'abl')))
			system_die('Invalid input $name_case (string list values => nom, gen, dat, acc, ins, abl)', 'VKapi -> get_user()');
			
		if(is_array($uids))
			$uids = join(',', $uids);
			
		if(is_array($fields))
			$fields = join(',', $fields);
			
		$access_token = $this->Application->VKauth->get_access_token();
			
		$json = file_get_contents($this->methods_path.'getProfiles?uid='.urlencode($uids).'&name_case='.urlencode($name_case).((strlen($fields) > 0) ? '&fields='.($fields) : null).(($access_token !== false) ? '&access_token='.urlencode($access_token) : null));
		$res = parse_json($json);
		if($res !== false)
		{
			if(isset($res['error']))
			{
				$this->last_error = $this->Application->Localizer->get_string('vk_error_code_'.$res['error']['error_code']);
				return false;
			}
			elseif(!isset($res['response'][0]['uid']) || !isset($res['response'][0]['first_name']) || !isset($res['response'][0]['last_name']))
			{
				$this->last_error = $this->Application->Localizer->get_string('internal_error');
				return false;
			} 
			
			return $res['response'];
		}
	}
}

class VKauth
{
	protected $Application;
	protected $DataBase;
	protected $tv;
	protected $last_error;
	protected $rules = 'notify,friends';
	protected $oauth_path = 'https://oauth.vkontakte.ru/';
	protected $access_token;
	protected $expires_in;
	public $UserData;
	
	function VKauth(&$app)
	{
		$this->Application = &$app;
		$this->tv = &$app->tv;
		$this->DataBase = &$this->Application->DataBase;
		if($app->User->is_logged())
			return true;
		else 
		{
			if(is_object($this->FBauth) && $this->FBauth->is_logged())
				return true;
			
			if (!array_key_exists('UserData', $_SESSION)) $_SESSION['UserData'] = array();
				$this->UserData = &$_SESSION['UserData'];
			
			if($this->UserData['is_vk'] === true)	
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
            $rs = $this->DataBase->select_sql('vk_user', array('id' => $this->UserData['id'], 'access_token' => $this->UserData['access_token']));
            if (($rs!==false) && (!$rs->eof()))
            {
            	if(time() < intval($rs->get_field('expires_in')))
            	{
			    	row_to_vars($rs, $this->UserData);
			    	$this->UserData['is_vk'] = true; 
			    	$this->access_token = $this->UserData['access_token'];
			    	$this->expires_in = $this->UserData['expires_in'];
            	}
            	else 
            	{
            		$this->DataBase->update_sql('vk_user', array('access_token' => ''), array('id' => $rs->get_field('id')));
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
            return ( (intval($this->UserData['id']) > 0) && (strlen($this->UserData['access_token']) > 0) && $this->UserData['is_vk'] === true);
        else
            return ( (intval($this->UserData['id']) > 0) && (strlen($this->UserData['access_token']) > 0) && $this->UserData['is_vk'] === true && $this->UserData['user_role_id'] == $role_id);
	}
	
	function login($code)
	{
		if(!is_string($code) || strlen($code) == 0)
			system_die('Invalid input data $code (string)', 'VKauth -> login()');
			
		$json = file_get_contents($this->oauth_path.'access_token?client_id='.VK_API_ID.'&client_secret='.VK_SECRET_KEY.'&code='.urlencode($code));
		$res = parse_json($json);
		if($res !== false)
		{
			if(!isset($res['access_token']) || !isset($res['expires_in']) || !isset($res['user_id']))
			{
				$this->last_error[] = $this->Application->Localizer->get_string('vk_'.$res['error']);
				return false;
				
			}
			$this->access_token = $res['access_token'];
			$this->expires_in = $res['expires_in'];
			return $res;
		}
		$this->last_error[] = $this->Application->Localizer->get_string('internal_error');
		return false;
	}
	
	function get_login_url($return_url, $display = 'page')
	{
		if(strlen($return_url) == 0 || !preg_match('/^(http(s)?:\/\/){1}([a-z,0-9]*([-][a-z,0-9]+)*\.)+[a-z]+(:[0-9]+)?(\/.*)?$/i', $return_url))
			system_die('Invalid input data $return_url (string)', 'VKauth -> get_login_url();');
			
		if(strlen($display) == 0 || !in_array($display, array('page', 'popup', 'touch', 'wap')))
			system_die('Invalid input data $display (string list values => page, popup, touch, wap', 'VKauth -> get_login_url();');
			
		return $this->oauth_path.'authorize?client_id='.VK_API_ID.'&scope='.$this->rules.'&redirect_uri='.$return_url.'&display='.$display.'&response_type=code';
	}
	
	function dblogin_user($vk_user_data)
	{
		if(!is_array($vk_user_data) || !isset($vk_user_data['uid']) || !isset($vk_user_data['first_name']) || !isset($vk_user_data['last_name']))
		{
			$this->last_error = $this->Application->Localizer->get_string('invalid_input');
			return false;
		}
		
		$rs = $this->DataBase->select_sql('vk_user', array('id' => $vk_user_data['uid']));
		if($rs === false)
		{
			$this->last_error = $this->Application->Localizer->get_string('internal_error');
			return false;
		}
		if(!file_exists(ROOT .'pub/VKusers/'))
		{
			@mkdir(ROOT .'pub/VKusers/');
			@chmod(ROOT .'pub/VKusers/', 0777);
		}
		if($rs->eof())
		{
			$insert_arr = array(
				'id' => $vk_user_data['uid'],
				'user_role_id' => USER_LEVEL_VKUSER,
				'last_name' => $vk_user_data['last_name'],
				'first_name' => $vk_user_data['first_name'],
				'nickname' => $vk_user_data['nickname'],
				'screen_name' => $vk_user_data['screen_name'],
				'sex' => intval($vk_user_data['sex']),
				'access_token' => $this->access_token,
				'expires_in' => time() + intval($this->expires_in),
				'last_login_date' => date('Y-m-d H:i:s')
			);
			if(isset($vk_user_data['photo_big']) && strlen($vk_user_data['photo_big']) > 0)
			{
				if(!file_exists(ROOT .'pub/VKusers/'. $vk_user_data['uid'] .'/'))
				{
					@mkdir(ROOT .'pub/VKusers/'. $vk_user_data['uid'] .'/');
					@chmod(ROOT .'pub/VKusers/'. $vk_user_data['uid'] .'/', 0777);
				}
				$filename = save_file_to_folder_from_url($vk_user_data['photo_big'], ROOT .'pub/VKusers/'. $vk_user_data['uid'] .'/');
				if($filename !== false)
				{
					$insert_arr['photo_filename'] = $filename;
					$Images = $this->Application->get_module('Images');
					$Images->create('vkuser_photo', ROOT .'pub/VKusers/'. $vk_user_data['uid'] .'/'.$filename, array('vk_user_id' => $vk_user_data['uid']));
				}
				else 
					$insert_arr['photo_filename'] = '';
			}
			if(!$this->DataBase->insert_sql('vk_user', $insert_arr, false))
			{
				$this->last_error = $this->Application->Localizer->get_string('database_error');
				return false;
			}
		}
		else 
		{
			$update_arr = array(
				'id' => $vk_user_data['uid'],
				'user_role_id' => USER_LEVEL_VKUSER,
				'last_name' => $vk_user_data['last_name'],
				'first_name' => $vk_user_data['first_name'],
				'nickname' => $vk_user_data['nickname'],
				'screen_name' => $vk_user_data['screen_name'],
				'sex' => intval($vk_user_data['sex']),
				'access_token' => $this->access_token,
				'expires_in' => time() + intval($this->expires_in),
				'last_login_date' => date('Y-m-d H:i:s')
			);
			if(isset($vk_user_data['photo_big']) && strlen($vk_user_data['photo_big']) > 0)
			{
				if(!file_exists(ROOT .'pub/VKusers/'. $vk_user_data['uid'] .'/'))
				{
					@mkdir(ROOT .'pub/VKusers/'. $vk_user_data['uid'] .'/');
					@chmod(ROOT .'pub/VKusers/'. $vk_user_data['uid'] .'/', 0777);
				}
				$new_fn = basename($vk_user_data['photo_big']);
				$old_fn = $rs->get_field('photo_filename');
				$Images = $this->Application->get_module('Images');
				if($new_fn !== $old_fn)
				{
					if(file_exists(ROOT .'pub/VKusers/'. $vk_user_data['uid'] .'/'.$old_fn))
					{
						$Images->delete('vkuser_photo', ROOT .'pub/VKusers/'. $vk_user_data['uid'] .'/'.$old_fn, array('vk_user_id' => $vk_user_data['uid']));
						@unlink(ROOT .'pub/VKusers/'. $vk_user_data['uid'] .'/'.$old_fn);
					}
					$filename = save_file_to_folder_from_url($vk_user_data['photo_big'], ROOT .'pub/VKusers/'. $vk_user_data['uid'] .'/');
					if($filename !== false)
					{
						$update_arr['photo_filename'] = $filename;
						$Images->create('vkuser_photo', ROOT .'pub/VKusers/'. $vk_user_data['uid'] .'/'.$filename, array('vk_user_id' => $vk_user_data['uid']));
					}
					else 
						$update_arr['photo_filename'] = '';
				}
			}
			else 
				$update_arr['photo_filename'] = '';
				
			if(!$this->DataBase->update_sql('vk_user', $update_arr, array('id' => $vk_user_data['uid'])))
			{
				$this->last_error = $this->Application->Localizer->get_string('database_error');
				return false;
			}
		}
		$this->UserData['id'] = $vk_user_data['uid'];
		$this->UserData['access_token'] = $this->access_token;
		$this->UserData['is_vk'] = true;
		return true;
	}
	
	public function set_logged_vars(&$tv)
    {
        if ($this->is_logged())
        {
            $tv['is_logged'] = true;
            foreach($this->UserData as $k => $v)
                $tv['logged_user_'.$k] = $v;
                
            $tv['is_VKlogged'] = true;
        }
    }
    
    public function get_infos()
    {
    	return ((isset($_SESSION['vk_success'])) ? $_SESSION['vk_success'] : false);
    }
    
    public function get_errors()
    {
    	return ((isset($_SESSION['vk_error'])) ? $_SESSION['vk_error'] : false);
    }
    
    public function clear_messages()
    {
    	if(isset($_SESSION['vk_error']))
    		unset($_SESSION['vk_error']);
    		
    	if(isset($_SESSION['vk_success']))
    		unset($_SESSION['vk_success']);
    }
}
?>