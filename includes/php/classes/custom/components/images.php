<?
require_once(FUNCTION_PATH . 'functions.files.php');
require_once(FUNCTION_PATH . 'functions.image.php');

class CImages
{
    var $Application;
    var $DataBase;
    var $tv;
    var $last_error;

    function CImages(&$app)
    {
        $this->Application = &$app;
        $this->tv = &$app->template_vars;
        $this->DataBase = &$this->Application->DataBase;
		$this->Thumbnail =& $this->Application->get_module('Thumbnail');
    }

    public function get_last_error()
    {
        return $this->last_error;
    }

	public function create($system_key, $original_image_path, $variables_arr = array()) {
		global $FilePath;
		$image_rs = $this->DataBase->select_sql('image', array('system_key' => $system_key));
		if (($image_rs !== false)&&(!$image_rs->eof())) {
			$image_sizes_rs = $this->DataBase->select_sql('image_size', array('image_id' => $image_rs->get_field('id')));
			if ($image_sizes_rs !== false) {
				while (!$image_sizes_rs->eof()) {
					$input_file = $original_image_path;
					$output_name = basename($original_image_path);
					$new_path = $image_rs->get_field('path');
					if (sizeof($variables_arr) > 0) {
						foreach ($variables_arr as $key => $variable) {
							$new_path = str_replace('{' . $key . '}', $variable, $new_path);
						}
					}
					$output_dir = $FilePath . $new_path . $image_sizes_rs->get_field('image_width') . 'x' . $image_sizes_rs->get_field('image_height') . '/';
					$output_file = $output_dir . $output_name;
					
					$options = array(
						'width'  => $image_sizes_rs->get_field('image_width'),
						'height' => $image_sizes_rs->get_field('image_height'),
					    'method' => $image_sizes_rs->get_field('thumbnail_method'),
					    'valign'  => THUMBNAIL_ALIGN_CENTER,
					);
					if (!file_exists($output_dir)) {
						makedir($output_dir);
					}
					$this->Thumbnail->output($input_file, $output_file, $options); 
					
					$image_sizes_rs->next();
				}
			}
		}
	}

	public function delete($system_key, $original_image_path, $variables_arr = array()) {
		global $FilePath;
		$image_rs = $this->DataBase->select_sql('image', array('system_key' => $system_key));
		if (($image_rs !== false)&&(!$image_rs->eof())) {
			$image_sizes_rs = $this->DataBase->select_sql('image_size', array('image_id' => $image_rs->get_field('id')));
			if ($image_sizes_rs !== false) {
				while (!$image_sizes_rs->eof()) {
					$output_name = basename($original_image_path);
					$new_path = $image_rs->get_field('path');
					if (sizeof($variables_arr) > 0) {
						foreach ($variables_arr as $key => $variable) {
							$new_path = str_replace('{' . $key . '}', $variable, $new_path);
						}
					}
					$output_dir = $FilePath . $new_path . $image_sizes_rs->get_field('image_width') . 'x' . $image_sizes_rs->get_field('image_height') . '/';
					$output_file = $output_dir . $output_name;
					if (!file_exists($output_dir)) {
						makedir($output_dir);
					}
					if (file_exists($output_file)) {
						unlink($output_file);
					}
				
					$image_sizes_rs->next();
				}
			}
		}
		if (file_exists($output_file)) {
			unlink($output_file);
		}
	}

    public function add_image($arr)
    {
        $r = $this->DataBase->select_custom_sql('
        select count(*) as cnt
        from %prefix%image
        where
        system_key = \''.mysql_escape_string($arr['system_key']).'\'');
        if ( ($r===false) || ($r->get_field('cnt') > 0) )
        {
            $this->last_error = $this->Application->Localizer->get_string('system_key_exists');
            return false;
        }
        
		$insert_array = array(
		    'title' => $arr['title'],
		    'system_key' => $arr['system_key'],
		    'path' => $arr['path'],
		);
        return $this->DataBase->insert_sql('image', $insert_array);
    }

    public function update_image($id, $arr)
    {
    	$r = $this->DataBase->select_custom_sql('
        select count(*) cnt
        from %prefix%image
        where
        (
        system_key = \''.mysql_escape_string($arr['system_key']).'\'
        ) and
        id <> '.$id.'');
        if ($r->get_field('cnt') > 0)
        {
            $this->last_error = $this->Application->Localizer->get_string('system_key_exists');
            return false;
        }
    	$update_array = array(
		    'title' => $arr['title'],
		    'system_key' => $arr['system_key'],
		    'path' => $arr['path'],
        );

        if ($this->DataBase->update_sql('image', $update_array, array('id' => $id)))
            return true;
        else
        {
            $this->last_error = $this->Application->Localizer->get_string('internal_error');
            return false;
        }
        return true;
    }

    public function delete_image($id)
    {
        $this->DataBase->delete_sql('image', array('id' => $id));
        return true;
    }

    public function add_image_size($arr)
    {
		$insert_array = array(
		    'image_id' => $arr['image_id'],
		    'system_key' => $arr['system_key'],
		    'image_width' => $arr['image_width'],
		    'image_height' => $arr['image_height'],
		    'thumbnail_method' => $arr['thumbnail_method'],
		);
        return $this->DataBase->insert_sql('image_size', $insert_array);
    }

    public function update_image_size($id, $arr)
    {
    	$update_array = array(
		    'image_width' => $arr['image_width'],
		    'image_height' => $arr['image_height'],
		    'thumbnail_method' => $arr['thumbnail_method'],
        );

        if ($this->DataBase->update_sql('image_size', $update_array, array('id' => $id)))
            return true;
        else
        {
            $this->last_error = $this->Application->Localizer->get_string('internal_error');
            return false;
        }
        return true;
    }

    public function delete_image_size($id)
    {
        $this->DataBase->delete_sql('image_size', array('id' => $id));
        return true;
    }
    
    private function init_directories() {
    	global $FilePath;
		$image_sizes_rs = $this->DataBase->select_sql('image_size', array('image_id' => $image_rs->get_field('id')));
		if ($image_sizes_rs !== false) {
			while (!$image_sizes_rs->eof()) {
				
				$image_sizes_rs->next();
			}
		}
    }

    function check_install() {
    	  return ( ($this->DataBase->is_table('image')) && ($this->DataBase->is_table('image_size')) );
    }

    function install() {
    	$this->DataBase->custom_sql("DROP TABLE IF EXISTS `image_size`");
    	$this->DataBase->custom_sql("DROP TABLE IF EXISTS `image`");
    	
		$this->DataBase->custom_sql("
			CREATE TABLE `image` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`title` varchar(255) NOT NULL,
				`system_key` varchar(255) NOT NULL,
				`path` varchar(255) NOT NULL,
			PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8
		");
    	
		$this->DataBase->custom_sql("
			CREATE TABLE `image_size` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`image_id` int(11) NOT NULL,
				`system_key` varchar(255) NOT NULL,
				`image_width` int(11) DEFAULT NULL,
				`image_height` int(11) DEFAULT NULL,
				`thumbnail_method` int(11) DEFAULT NULL,
			PRIMARY KEY (`id`),
			KEY `image_id` (`image_id`),
			CONSTRAINT `image_size_fk` FOREIGN KEY (`image_id`) REFERENCES `image` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
			) ENGINE=InnoDB DEFAULT CHARSET=utf8
		");
    }
};
?>