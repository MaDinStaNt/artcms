<?
//require_once(FUNCTION_PATH . 'functions.pass.php');

define('USER_LEVEL_GUEST', 1);
define('USER_LEVEL_USER', 10);
define('USER_LEVEL_ADMIN', 100);
define('USER_LEVEL_GLOBAL_ADMIN', 255);

class CUser
{
    var $Application;
    var $DataBase;
    var $tv;

    var $UserData;
    var $DefBillAddress;
    var $DefShipAddress;

    var $last_error;

    function CUser(&$app)
    {
        $this->Application = &$app;
        $this->tv = &$app->tv;
        $this->DataBase = &$this->Application->DataBase;
		if (!array_key_exists('UserData', $_SESSION)) $_SESSION['UserData'] = array();
		$this->UserData = &$_SESSION['UserData'];
        //$this->Thumbnail =& $this->Application->get_module('Thumbnail');
        if (InCookie('usd_id') != '')
        {
            $bf = &$this->Application->get_module('BF');
            $this->UserData['id'] = intval(@$bf->getbyid(InCookie('usd_id')), 10);
            if (!is_numeric($this->UserData['id']))
                $this->UserData['id'] = -1;
        }
        
        if(!isset($this->UserData['access_token']) || !$this->UserData['is_vk'])
       		$this->synchronize();
    }

    public function get_last_error()
    {
        return $this->last_error;
    }

    public function get_by_id($user_id)
    {
        $user_id = intval($user_id);
        if ( $user_id < 1 ) {
            $this->last_error = $this->Application->Localizer->get_string('invalid_input_data');
            return false;
        }

        $rs = $this->DataBase->select_sql('user', array('id' => $user_id));

        if ( $rs === false ) {
            $this->last_error = $this->Application->Localizer->get_string('database_error');
            return false;
        }

        $this->last_error = '';
        return $rs;
    }
    
    public function get_role_by_id($user_role_id)
    {
        $user_role_id = intval($user_role_id);
        if ( $user_role_id < 1 ) {
            $this->last_error = $this->Application->Localizer->get_string('invalid_input_data');
            return false;
        }

        $rs = $this->DataBase->select_sql('user_role', array('id' => $user_role_id));

        if ( $rs === false ) {
            $this->last_error = $this->Application->Localizer->get_string('database_error');
            return false;
        }

        $this->last_error = '';
        return $rs;
    }

    public function get_group_by_id($user_group_id)
    {
        $user_group_id = intval($user_group_id);
        if ( $user_role_id < 1 ) {
            $this->last_error = $this->Application->Localizer->get_string('invalid_input_data');
            return false;
        }

        $rs = $this->DataBase->select_sql('user_group', array('id' => $user_group_id));

        if ( $rs === false ) {
            $this->last_error = $this->Application->Localizer->get_string('database_error');
            return false;
        }

        $this->last_error = '';
        return $rs;
    }

    public function get_user_name_by_id($user_id)
    {
        $user_id = intval($user_id);
        if ( $user_id < 1 ) {
            $this->last_error = $this->Application->Localizer->get_string('invalid_input_data');
            return "";
        }

        $rs = $this->DataBase->select_sql('user', array('id' => $user_id));

        if ( $rs === false ) {
            $this->last_error = $this->Application->Localizer->get_string('database_error');
            return "";
        }

        $this->last_error = '';
        return $rs->get_field('user_name');
    }
    
    public function get_user_roles()
    {
    	$roles_rs = $this->DataBase->select_sql('user_role', array(), array('title' => 'ASC'));
    	return (($roles_rs !== false)&&(!$roles_rs->eof())) ? $roles_rs : false;
    }

    public function get_states()
    {
    	$states_rs = $this->DataBase->select_sql('state', array(), array('title' => 'ASC'));
    	return (($states_rs !== false)&&(!$states_rs->eof())) ? $states_rs : false;
    }

    public function get_user_groups()
    {
    	$groups_rs = $this->DataBase->select_sql('user_group', array(), array('title' => 'ASC'));
    	return (($groups_rs !== false)&&(!$groups_rs->eof())) ? $groups_rs : false;
    }
    
    public function get_group_role_ids($user_group_id) {
    	$group_role_ids_rs = $this->DataBase->select_custom_sql("
    	SELECT ur.* FROM %prefix%user_group_user_role_link ugurl LEFT JOIN %prefix%user_role ur ON (ugurl.user_role_id = ur.id)
    	WHERE ugurl.user_group_id = " . $user_group_id);
    	return (($group_role_ids_rs !== false)&&(!$group_role_ids_rs->eof())) ? $group_role_ids_rs : false;
    }

    public function set_logged_vars(&$tv)
    {
        if ($this->is_logged())
        {
            $tv['is_logged'] = true;
            foreach($this->UserData as $k => $v)
                $tv['logged_user_'.$k] = $v;

            require_once(FUNCTION_PATH . 'functions.format.php');
            $tv['logged_user_formated_name'] = format_name($this->UserData['name'], '', $this->UserData['name']);
            $tv['is_global_admin'] = ($this->UserData['user_role_id'] == USER_LEVEL_GLOBAL_ADMIN);
        }
        else
        {
            $tv['is_logged'] = false;
            $tv['logged_user_id'] = -1;
        }
    }

    public function is_logged($role_id = null)
    {
        if (is_null($role_id))
            return ( (isset($this->UserData['id'])) && ($this->UserData['id'] > 0) && (!isset($this->UserData['is_vk']) || $this->UserData['is_vk'] === false));
        else
            return ( (isset($this->UserData['id'])) && ($this->UserData['id'] > 0) && (!isset($this->UserData['is_vk']) || $this->UserData['is_vk'] === false) && $this->UserData['user_role_id'] == $role_id);
    }

    public function login($l, $p, $store = true)
    {
        $this->last_error = '';

        $r = $this->Application->DataBase->select_sql('user', array('email'=>$l, 'password'=>md5($p)));
        if ( (is_object($r)) && (!$r->eof()) )
        {
            if ($r->get_field('status') == 1)
            {
                $this->set_user_from_db($r);
                $bf = &$this->Application->get_module('BF');
                if ($store)
                        setcookie('usd_id', $bf->makeid($r->get_field('id')), (time()+60*60*24*30), '/');
                else
                        setcookie('usd_id', $bf->makeid($r->get_field('id')), null, '/');

                $this->set_logged_vars($this->Application->tv);
                //update last logeed time
                $this->Application->DataBase->update_sql('user', array('last_login_date' => date('Y-m-d H:i:s')), array('id' => $r->get_field('id')));
                return TRUE;
            }
            elseif ($r->get_field('status') == 2) {
                $loc = &$this->Application->get_module('Localizer');
                $this->last_error = $loc->get_string('login_user_not_confirmed');
            }
            else
            {
                $loc = &$this->Application->get_module('Localizer');
                $this->last_error = $loc->get_string('login_user_disabled');
            }
        }
        else
        {
            $loc = &$this->Application->get_module('Localizer');
            $this->last_error = $loc->get_string('login_no_such_user');
        }

        return false;
    }

    public function logout()
    {
    	//$this->Application->Session->session_kill();
        $this->UserData = array();
        setcookie('usd_id', '', time(), '/');
    }

    public function add_user($arr)
    {
        $r = $this->DataBase->select_custom_sql('
        select count(*) as cnt
        from %prefix%user
        where
        email = \''.mysql_escape_string($arr['email']).'\'');
        if ( ($r===false) || ($r->get_field('cnt') > 0) )
        {
            $this->last_error = $this->Application->Localizer->get_string('email_exists');
            return false;
        }

        $insert_array = array(
            'user_role_id' => $arr['user_role_id'],
            'status' => $arr['status'],
            'email' => $arr['email'],
            'password' => md5($arr['password']),
            'name' => $arr['name'],
            'address' => $arr['address'],
            'city' => $arr['city'],
            'state_id' => (strlen($arr['state_id']) > 0) ? $arr['state_id'] : null,
            'zip' => $arr['zip'],
            'company' => $arr['company'],
            'create_date' => "now()",
         );

        if (!$user_id = $this->DataBase->insert_sql('user', $insert_array))
        {
            $this->last_error = $this->Application->Localizer->get_string('internal_error');
            return false;
        }

        return $user_id;
    }

    public function update_user($user_id, $arr)
    {
    	$old_user_rs = $this->get_by_id($user_id);

    	$r = $this->DataBase->select_custom_sql('
        select count(*) cnt
        from %prefix%user
        where
        (
        './*login = \''.mysql_escape_string($arr['login']).'\' or*/'
        email = \''.mysql_escape_string($arr['email']).'\'
        ) and
        id <> '.$user_id.'');
        if ($r->get_field('cnt') > 0)
        {
            $this->last_error = $this->Application->Localizer->get_string('email_exists');
            return false;
        }
		
        $update_array = array(
            'email' => $arr['email'],
            'name' => $arr['name'],
            'address' => $arr['address'],
            'city' => $arr['city'],
            'state_id' => (strlen($arr['state_id']) > 0) ? $arr['state_id'] : null,
            'zip' => $arr['zip'],
            'company' => $arr['company'],
            );

        if($arr['user_role_id'] != "") {
			$update_array['user_role_id'] = $arr['user_role_id'];
		}

		if($arr['password'] != "") {
			$update_array['password'] = md5($arr['password']);
		}
           
        if($arr['status'] != "") {
			$update_array['status'] = $arr['status'];
		}

		if ($this->DataBase->update_sql('user', $update_array, array('id'=>$user_id)))
            return true;
        else
        {
            $this->last_error = $this->Application->Localizer->get_string('internal_error');
            return false;
        }
        return true;
    }

    public function add_user_role($arr)
    {
        $r = $this->DataBase->select_custom_sql('
        select count(*) as cnt
        from %prefix%user_role
        where
        id = \''.mysql_escape_string($arr['id']).'\'');
        if ( ($r===false) || ($r->get_field('cnt') > 0) )
        {
            $this->last_error = $this->Application->Localizer->get_string('id_exists');
            return false;
        }
        
        $insert_array = array(
            'id' => $arr['id'],
            'title' => $arr['title'],
            'description' => $arr['description'],
         );

        return $this->DataBase->insert_sql('user_role', $insert_array);
    }

    public function update_user_role($user_role_id, $arr)
    {
    	$old_user_role_rs = $this->get_role_by_id($user_role_id);

    	$r = $this->DataBase->select_custom_sql('
        select count(*) cnt
        from %prefix%user_role
        where
        (
        './*login = \''.mysql_escape_string($arr['login']).'\' or*/'
        id = \''.mysql_escape_string($arr['id']).'\'
        ) and
        id <> '.$user_role_id.'');
        if ($r->get_field('cnt') > 0)
        {
            $this->last_error = $this->Application->Localizer->get_string('id_exists');
            return false;
        }

        $update_array = array(
            'id' => $arr['id'],
            'title' => $arr['title'],
            'description' => $arr['description'],
            );

        if ($this->DataBase->update_sql('user_role', $update_array, array('id'=>$user_role_id)))
            return true;
        else
        {
            $this->last_error = $this->Application->Localizer->get_string('internal_error');
            return false;
        }
        return true;
    }

    public function add_user_group($arr)
    {
        $r = $this->DataBase->select_custom_sql('
        select count(*) as cnt
        from %prefix%user_group
        where
        id = \''.mysql_escape_string($arr['id']).'\'');
        if ( ($r===false) || ($r->get_field('cnt') > 0) )
        {
            $this->last_error = $this->Application->Localizer->get_string('id_exists');
            return false;
        }
        
        $insert_array = array(
            'title' => $arr['title'],
            'description' => $arr['description'],
         );

        return $this->DataBase->insert_sql('user_group', $insert_array);
    }

    public function update_user_group($user_group_id, $arr)
    {
    	$old_user_group_rs = $this->get_group_by_id($user_group_id);

    	$r = $this->DataBase->select_custom_sql('
        select count(*) cnt
        from %prefix%user_group
        where
        (
        './*login = \''.mysql_escape_string($arr['login']).'\' or*/'
        id = \''.mysql_escape_string($arr['id']).'\'
        ) and
        id <> '.$user_group_id.'');
        if ($r->get_field('cnt') > 0)
        {
            $this->last_error = $this->Application->Localizer->get_string('id_exists');
            return false;
        }

        $update_array = array(
            'title' => $arr['title'],
            'description' => $arr['description'],
            );

        if ($this->DataBase->update_sql('user_group', $update_array, array('id'=>$user_group_id)))
            return true;
        else
        {
            $this->last_error = $this->Application->Localizer->get_string('internal_error');
            return false;
        }
        return true;
    }

    public function delete_user_group($user_group_id)
    {
        $this->DataBase->delete_sql('user_group', array('id'=>$user_group_id));
        return true;
    }

    public function delete_user($user_id)
    {
        $this->DataBase->delete_sql('user', array('id'=>$user_id));
        return true;
    }

    public function is_email_unique($email, $id = null)
    {
    	if (id == null)
    	{
    		$user_rs = $this->DataBase->select_sql('user', array('email' => $email));
    		return !(($user_rs !== false)&&(!$user_rs->eof()));
    	}
    	else 
    	{
    		$user_rs = $this->DataBase->select_custom_sql("SELECT * FROM %prefix%user WHERE email = '" . $email . "' AND id != '" . $id . "'");
    		return !(($user_rs !== false)&&(!$user_rs->eof()));
    	}
    }

    public function get_admin_names()
    {
        return $this->Application->Localizer->get_string('module_user');
    }

    public function run_admin_interface($mod, $sub_mod)
    {
        return false;
    }

    private function synchronize() 
    {
        if (isset($this->UserData['id']))
        {
            $rcd = $this->DataBase->select_sql('user', array('id'=>$this->UserData['id']));
            if ( ($rcd!==false) && (!$rcd->eof()) )
                $this->set_user_from_db($rcd);
            else
                $this->UserData = array();
        }
        else
            $this->UserData = array();
    }

    private function set_user_from_db($db_row) 
    {
        $this->UserData = array();
        foreach($db_row->Rows[0]->Fields as $k => $v)
            if (strcmp($k, 'password') != 0)
                $this->UserData[$k] = $v;
    }

    function check_install() {
    	return ( 
    			($this->DataBase->is_table('user_role')) && 
    			($this->DataBase->is_table('user_session')) && 
    			($this->DataBase->is_table('user_group')) && 
    			($this->DataBase->is_table('user')) && 
    			($this->DataBase->is_table('user_group_user_role_link')) 
    	);
    }
    
    function install() {
    	$this->DataBase->custom_sql("DROP TABLE IF EXISTS `user_role`");
    	$this->DataBase->custom_sql("DROP TABLE IF EXISTS `user_session`");
    	$this->DataBase->custom_sql("DROP TABLE IF EXISTS `user_group`");
    	$this->DataBase->custom_sql("DROP TABLE IF EXISTS `user`");
    	$this->DataBase->custom_sql("DROP TABLE IF EXISTS `user_group_user_role_link`");
    	
		$this->DataBase->custom_sql("
			CREATE TABLE `user_role` (
				`id` INTEGER(11) NOT NULL,
				`title` VARCHAR(60) COLLATE utf8_general_ci DEFAULT NULL,
				`description` VARCHAR(200) COLLATE utf8_general_ci DEFAULT NULL,
			PRIMARY KEY (`id`)
			)ENGINE=InnoDB
			CHARACTER SET 'utf8' COLLATE 'utf8_general_ci'
		");
    	
		$this->DataBase->custom_sql("
			CREATE TABLE `user_session` (
				`session_id` CHAR(32) COLLATE utf8_general_ci NOT NULL DEFAULT '',
				`user_id` INTEGER(11) NOT NULL,
				`session_start` INTEGER(11) NOT NULL DEFAULT '0',
				`session_time` INTEGER(11) NOT NULL DEFAULT '0',
				`session_ip` VARCHAR(64) COLLATE utf8_general_ci DEFAULT NULL,
			PRIMARY KEY (`session_id`),
			KEY `user_id` (`user_id`),
			KEY `session_id_ip_user_id` (`session_id`, `session_ip`, `user_id`)
			)ENGINE=HEAP
			AUTO_INCREMENT=0 
			CHARACTER SET 'utf8' COLLATE 'utf8_general_ci'
		");
    	
		$this->DataBase->custom_sql("
			CREATE TABLE `user_group` (
				`id` INTEGER(11) NOT NULL AUTO_INCREMENT,
				`title` VARCHAR(255) COLLATE utf8_general_ci NOT NULL DEFAULT '',
				`description` VARCHAR(255) COLLATE utf8_general_ci DEFAULT NULL,
			PRIMARY KEY (`id`)
			)ENGINE=InnoDB
			CHARACTER SET 'utf8' COLLATE 'utf8_general_ci'
		");
    	
		$this->DataBase->custom_sql("
			CREATE TABLE `user` (
				`id` INTEGER(11) NOT NULL AUTO_INCREMENT,
				`user_role_id` INTEGER(11) NOT NULL,
				`status` INTEGER(11) NOT NULL DEFAULT '1',
				`email` VARCHAR(255) COLLATE utf8_general_ci NOT NULL DEFAULT '',
				`password` VARCHAR(255) COLLATE utf8_general_ci NOT NULL DEFAULT '',
				`name` VARCHAR(255) COLLATE utf8_general_ci DEFAULT NULL,
				`company` VARCHAR(255) COLLATE utf8_general_ci DEFAULT NULL,
				`address` VARCHAR(255) COLLATE utf8_general_ci DEFAULT NULL,
				`city` VARCHAR(255) COLLATE utf8_general_ci DEFAULT NULL,
				`state_id` INTEGER(11) DEFAULT NULL,
				`zip` VARCHAR(6) COLLATE utf8_general_ci DEFAULT NULL,
				`create_date` TIMESTAMP(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`last_login_date` DATETIME DEFAULT NULL,
			PRIMARY KEY (`id`),
			UNIQUE KEY `email` (`email`),
			KEY `id_level` (`user_role_id`),
			KEY `state_id` (`state_id`),
			CONSTRAINT `ibs_user_fk` FOREIGN KEY (`user_role_id`) REFERENCES `user_role` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
			CONSTRAINT `user_fk` FOREIGN KEY (`state_id`) REFERENCES `state` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
			)ENGINE=InnoDB
			CHARACTER SET 'utf8' COLLATE 'utf8_general_ci'
		");
		
		$this->DataBase->custom_sql("
			CREATE TABLE `user_group_user_role_link` (
				`user_group_id` INTEGER(11) NOT NULL,
				`user_role_id` INTEGER(11) NOT NULL,
			KEY `user_role_id` (`user_role_id`),
			KEY `user_group_id` (`user_group_id`),
			CONSTRAINT `user_group_user_role_link_fk` FOREIGN KEY (`user_role_id`) REFERENCES `user_role` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
			CONSTRAINT `user_group_user_role_link_fk1` FOREIGN KEY (`user_group_id`) REFERENCES `user_group` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
			)ENGINE=InnoDB
			CHARACTER SET 'utf8' COLLATE 'utf8_general_ci'
		");
		
		$this->DataBase->custom_sql("INSERT INTO `user_role` (`id`, `title`, `description`) VALUES (1,'Guest','Guest account'), (10,'User','User account'), (100,'Administrator','Administrator account'), (255,'Global Administrator','Global Administrator account')");
		$this->DataBase->custom_sql("INSERT INTO `user` (`id`, `user_role_id`, `status`, `email`, `password`, `name`, `company`, `address`, `city`, `state_id`, `zip`, `create_date`, `last_login_date`) VALUES (1,255,1,'admin@admin.com','21232f297a57a5a743894a0e4a801fc3','Admin',NULL,'LLA',NULL,NULL,NULL,'2009-05-10 19:09:24','2010-07-16 09:47:05')");
    }
};
?>