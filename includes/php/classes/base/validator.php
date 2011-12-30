<?php
$internal_infos = array();
$internal_errors = array();
$internal_metas = array();
$internal_inputs = array();


define('VRT_REGEXP', 1); // add_info1 - pattern, ie '/.../i';
define('VRT_EMAIL', 2); // name@server.ext
define('VRT_PASSWORD', 3); // min - 4 symbols, max - 64 symbols
define('VRT_US_PHONE', 4); // 123 123 1234
define('VRT_WORLD_PHONE', 5); // any combination up to 30 characters
define('VRT_COUNTRY', 6); // any combination of alpha up to 30 characters
define('VRT_IMAGE_FILE', 7); // add_info1 - max width, add_info2 - max height
define('VRT_CUSTOM_FILE', 8); // add_info1 - optional array of mime types
define('VRT_TEXT', 9); // add_info1 - min length, add_info2 - max length, can be null
define('VRT_US_ZIP', 10); // 12345 [6789]
define('VRT_STATE', 25); // abbreviation of US state
define('VRT_PROVINCE', 26); // any combination of alpha up to 30 characters, add_info1-3 - same as enumeration
define('VRT_DATE', 11); // date in format dd.mm.yyyy or mm/dd/yyyy
define('VRT_ODBCDATE', 30); // date in format yyyy-mm-dd
define('VRT_ODBCDATETIME', 33); // date in format yyyy-mm-dd hh:ii:ss
define('VRT_TIME', 12); // time in format hh:mm:ss [am/pm]
define('VRT_DATETIME', 13); // VRT_DATE [space] VRT_TIME
define('VRT_YEAR', 14); // yyyy, add_info1 - min year
define('VRT_MONTH', 15); // mm
define('VRT_DAY', 16); // dd
define('VRT_HOUR', 17); // hh
define('VRT_MINUTE', 18); // mm
define('VRT_SECOND', 19); // ss
define('VRT_MSECOND', 20); // msec
define('VRT_PRICE', 21); // add_info1 - max price
define('VRT_ENUMERATION', 22); // add_info1 - 1. array of possible values
							  //			  2. CRecordSet, then add_info2 - name of field
define('VRT_KEYENUMERATION', 28); // add_info1 - 1. array where keys are possible values
define('VRT_NUMBER', 23); // add_info1 - min value, add_info2 - max value, can be null
define('VRT_FLOAT', 24); // add_info1 - min value, add_info2 - max value, can be null
define('VRT_CALLBACK', 27); // add_info1 - function which check value
define('VRT_CC', 29); // CC
define('VRT_URL_HTTP', 31 ); // web url validation http://mysite.com pattern: '~^(http(s)?:\/\/)?([a-z]+[a-z,0-9]+[-]?[a-z,0-9]+\.)+[a-z]+(:[0-9]+)?(\/.*)?$~i'
define('VRT_UNIQUE', 32 );
define('VRT_NOT_IN_TABLE', 43 ); // add_info1 - table, add_info2 - field, add_info3 - id, can be null
define('VRT_USER_DELETED', 48 ); // add_info1 - table, add_info2 - field, add_info3 - id, can be null
define('VRT_OLD_PASSWORD', 34 );
define('VRT_ZIP', 35 );
define('VRT_NAME', 56 );
define('VRT_NICKNAME', 37 );
define('VRT_IN_TABLE', 45 );
define('VRT_USER_NOT_ACTIVE', 46 );

function is_leap_year($year)
{
	return ( ($year%4 == 0) && !( ($year%100 == 0) && ($year%400 != 0) ) );
}
function js_escape_string($str)
{
	$str = str_replace("\\", "\\\\", strval($str));
	$str = str_replace("'", "\\'", $str);
	$str = str_replace("\r", "\\r", $str);
	$str = str_replace("\n", "\\n", $str);
	$str = str_replace("\n", "\\n", $str);
	$str = str_replace("</script>", "</'+'script>", $str);
	return $str;
}

class CValidator {
	
	function add($name, $type, $unique_error_msg = false, $add_info1 = null, $add_info2 = null, $add_info3 = null){
		global $internal_metas;
		$internal_metas[$name][] = new CValidatorMeta($name, $type, true, $unique_error_msg, '', $add_info1, $add_info2, $add_info3);
	}
	
	function add_nr($name, $type, $unique_error_msg = false, $add_info1 = null, $add_info2 = null, $add_info3 = null){
		global $internal_metas;
		$internal_metas[$name][] = new CValidatorMeta($name, $type, false, $unique_error_msg, '', $add_info1, $add_info2, $add_info3);
	}
	
	function validate_input() {
		global $internal_metas;
		global $input_fields;
		global $internal_errors, $internal_infos;

		$it_fs = InPostGet('_it_fs', '');
		if ( (strlen($it_fs) == 0) || (strcasecmp($it_fs, 'g') == 0) )
			$input_fields = array_keys($_GET);
		else
			$input_fields = array_merge(array_keys($_POST), explode(',', base64_decode(InPostGet('_it_fs', ''))));
		foreach ($input_fields as $k)
			$GLOBALS['app']->tv[$k] = InPostGet($k);
		$valid = true;
		$mts = array_keys($internal_metas);
		foreach ($mts as $k)
		{
			$mts2 = array_keys($internal_metas[$k]);
			foreach ($mts2 as $idx)
				$valid &= $internal_metas[$k][$idx]->validate_input($internal_errors, $internal_infos);
		}

		return $valid;
	}
	
	function get_infos() {
		return $GLOBALS['internal_infos'];
	}

	function get_errors() {
		return $GLOBALS['internal_errors'];
	}

};

class CValidatorMeta{

	var $name;
	var $type;
	var $required;
	var $unique_error_msg;
	var $default_value;
	var $add_info1;
	var $add_info2;
	var $add_info3;
	
	var $checked;
	
	function CValidatorMeta($name, $type, $required, $unique_error_msg, $default, $add_info1, $add_info2, $add_info3){
		$this->checked = false;
		$this->last_check = false;
		
		$this->add($name, $type, $required, $unique_error_msg, $default, $add_info1, $add_info2, $add_info3);
	}
	
	function add($name, $type, $required, $unique_error_msg, $default, $add_info1, $add_info2, $add_info3){
		$this->add_info1 = $add_info1;
		$this->add_info2 = $add_info2;
		$this->add_info3 = $add_info3;
		$this->type = $type;
		$this->default_value = $default;
		$this->required = $required;
		$this->name = $this->display_name = $name;
		$this->unique_error_mess = $unique_error_msg;
		
		switch ($this->type){
			case VRT_CC:
			{
				$this->pattern = '/^[0-9\- \.]+$/';
				$this->min_length = 6;
				$this->max_length = 64;
				break;
			} // end of VRT_CC
			case VRT_REGEXP: //
			{
				$this->pattern = $add_info1;
				break;
			} // end of VRT_REGEXP
			case VRT_US_PHONE:
			{
				$this->pattern = '/^\(?[0-9]{3}\)?[\-\. ]*[0-9]{3}[\-\. ]*[0-9]{4}$/';
				$this->min_length = 10;
				$this->max_length = 64;
				break;
			} // VRT_US_PHONE
			case VRT_WORLD_PHONE:
			{
				$this->pattern = '/^\+[0-9]+$/';
				$this->min_length = 6;
				$this->max_length = 64;
				break;
			} // end of VRT_WORLD_PHONE
			case VRT_CUSTOM_FILE:
			{
				if ( (!is_null($this->add_info1)) && (!is_array($this->add_info1)) )
					$this->add_info1 = explode(',', $this->add_info1);
				break;
			} // end of VRT_CUSTOM_FILE
			case VRT_US_ZIP:
			{
				$this->pattern = '/^[0-9]{5}([0-9]{4}){0,1}$/';
				$this->min_length = 5;
				$this->max_length = 9;
				break;
			} // end of VRT_US_ZIP
			case VRT_ZIP:
			{
				$this->pattern = '/^[0-9A-Za-z-_ ]{3,9}$/';
				$this->min_length = 3;
				$this->max_length = 9;
				break;
			} // end of VRT_US_ZIP
			case VRT_PROVINCE:
			{
				$this->min_length = 0;
				$this->max_length = 30;

				if (is_array($add_info1))
					$this->enumeration = $add_info1;
				elseif (is_object($add_info1)) {
					$this->enumeration = array();
					while (!$add_info1->eof()) {
						$this->enumeration[] = $add_info1->get_field($add_info2);
						$add_info1->next();
					}
				}

				break;
			}
			case VRT_EMAIL:
			{
				$this->pattern = '/^ *([a-z0-9_-]+\.)*[a-z0-9_-]+@([a-z0-9-]+\.)+.*$/i';
				$this->min_length = 5;
				$this->max_length = 255;
				break;
			} // end of VRT_EMAIL
			case VRT_URL_HTTP:{
				$this->min_length = 1;
			//	$this->pattern = '/^([a-z,0-9]*([-][a-z,0-9]+)*\.)+[a-z]+(:[0-9]+)?(\/.*)?$/i';
				$this->pattern = '/^(http(s)?:\/\/){1}([a-z,0-9]*([-][a-z,0-9]+)*\.)+[a-z]+(:[0-9]+)?(\/.*)?$/i';
				$this->max_length = 255;
				break;
			}
			case VRT_PASSWORD:
			{
				$this->min_length = 8;
				$this->max_length = 64;
				break;
			} // end of VRT_PASSWORD
			case VRT_NUMBER:
			{
				$this->pattern = '/^[\-\+]?[0-9]+$/';
				if (is_numeric($add_info1))
					$this->min_numeric_value = $add_info1;
				if (is_numeric($add_info2))
					$this->max_numeric_value = $add_info2;
				break;
			} // end of VRT_NUMBER
			case VRT_FLOAT:
			{
				$this->pattern = '/^[\-\+]{0,1}[0-9]+[\.]{0,1}[0-9]*$/';
				if (is_numeric($add_info1))
					$this->min_numeric_value = $add_info1;
				if (is_numeric($add_info2))
				{
					$this->max_numeric_value = $add_info2;
					$this->max_length = strlen(''.$add_info2) + 3;
				}
				break;
			} //end of VRT_FLOAT
			case VRT_TEXT:
			{
				if (!is_null($add_info1))
					$this->min_length = $add_info1;
				if (!is_null($add_info2))
					$this->max_length = $add_info2;
				break;
			} // end of VRT_TEXT
			case VRT_ENUMERATION:
			{
				if (is_array($add_info1))
					$this->enumeration = $add_info1;
				elseif (is_object($add_info1)) {
					$this->enumeration = array();
					$add_info1->first();
					while (!$add_info1->eof()) {
						$this->enumeration[] = $add_info1->get_field($add_info2);
						$add_info1->next();
					}
				}
				else
					system_die('invalid add_info1');
				break;
			} // end of VRT_ENUMERATION
			case VRT_PRICE:
			{
				$this->pattern = '/^[\-\+]{0,1}[0-9\,]+[\.]{0,1}[0-9]*$/';
				$this->min_numeric_value = 0;
				if (is_numeric($add_info1))
					$this->max_numeric_value = $add_info1;
				break;
			} // end of VRT_PRICE
			case VRT_DATE:
			{
				$this->pattern = '/^[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{4}$/';
				//$this->min_length = 8;
				$this->max_length = 10;
				break;
			} // end of VRT_DATE
			case VRT_ODBCDATE:
			{
				$this->pattern = '/^[0-9]{4}\-[0-9]{1,2}\-[0-9]{1,2}$/';
				//$this->min_length = 8;
				$this->max_length = 10;
				break;
			} // end of VRT_ODBCDATE
			case VRT_ODBCDATETIME:
			{
				$this->pattern = '/^[0-9]{4}\-[0-9]{1,2}\-[0-9]{1,2} [0-9]{1,2}:[0-9]{1,2}:[0-9]{1,2}$/';
				$this->min_length = 14;
				$this->max_length = 19;
				break;
			} // end of VRT_ODBCDATETIME
			case VRT_TIME:
			{
				$this->pattern = '/^[0-9]{1,2}:[0-9]{1,2}:[0-9]{1,2} {0,1}([aApP][mM]){0,1}$/';
				$this->min_length = 5;
				$this->max_length = 11;
				break;
			} // end of VRT_TIME
			case VRT_DATETIME:
			{
				$this->pattern = '/^[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{4} [0-9]{1,2}:[0-9]{1,2}:[0-9]{1,2}$/';
				$this->min_length = 14;
				$this->max_length = 19;
				break;
			} // end of VRT_DATETIME
			case VRT_YEAR:
			{
				$this->min_length = 4;
				$this->max_length = 4;
				$this->pattern = '/^[0-9]{4}$/';
				if (is_numeric($add_info1))
					$this->min_numeric_value = $add_info1;
				if (is_numeric($add_info2))
					$this->max_numeric_value = $add_info2;
				break;
			} // end of VRT_YEAR
			case VRT_MONTH:
			{
				$this->min_length = 1;
				$this->max_length = 2;
				$this->pattern = '/^[0-9]{1,2}$/';
				$this->min_numeric_value = 1;
				$this->max_numeric_value = 12;
				break;
			} // end of VRT_MONTH
			case VRT_DAY:
			{
				$this->min_length = 1;
				$this->max_length = 2;
				$this->pattern = '/^[0-9]{1,2}$/';
				$this->min_numeric_value = 1;
				$this->max_numeric_value = 31;
				break;
			} // end of VRT_DAY
			case VRT_HOUR:
			{
				$this->min_length = 1;
				$this->max_length = 2;
				$this->pattern = '/^[0-9]{1,2}$/';
				$this->min_numeric_value = 1;
				$this->max_numeric_value = 24;
				break;
			} // end of VRT_HOUR
			case VRT_MINUTE:
			{
				$this->min_length = 1;
				$this->max_length = 2;
				$this->pattern = '/^[0-9]{1,2}$/';
				$this->min_numeric_value = 1;
				$this->max_numeric_value = 60;
				break;
			} // end of VRT_MINUTE
			case VRT_SECOND:
			{
				$this->min_length = 1;
				$this->max_length = 2;
				$this->pattern = '/^[0-9]{1,2}$/';
				$this->min_numeric_value = 1;
				$this->max_numeric_value = 60;
				break;
			} // end of VRT_SECOND
			case VRT_MSECOND:
			{
				$this->min_length = 1;
				$this->max_length = 4;
				$this->pattern = '/^[0-9]${1,4}/';
				$this->min_numeric_value = 0;
				$this->max_numeric_value = 9999;
				break;
			} // end of VRT_MSECOND
		}
		
	}
	
	function validate_input(&$errors, &$infos) {
		global $app;
		if ($this->checked) return $this->last_check;

		global $input_fields;
		if (!in_array($this->name, $input_fields)) {
			if (!$this->required)
				if (!isset($app->tv[$this->name]))
					$app->tv[$this->name] = $this->default_value;
			return true;
		}

		$this->checked = true;
		$this->last_check = true;
		$value = $this->default_value;
		if(preg_match('/^registry_path_value_#[0-9]+___[0-9a-z_-]+$/iu', $this->display_name)) $this->display_name = preg_replace('/^registry_path_value_#[0-9]+___/iu', '', $this->display_name);
		$this->display_name = $GLOBALS['app']->Localizer->get_string("validator_field_{$this->display_name}");
		
		if ( ($this->type == VRT_IMAGE_FILE) || ($this->type == VRT_CUSTOM_FILE) ) {
			$value = '';
			$app->tv[$this->name.'_file_content'] = '';
			$app->tv[$this->name.'_file_type'] = '';
			if (isset($_FILES[$this->name])) {
				if ( (intval($_FILES[$this->name]['size']) > 0) && (intval($_FILES[$this->name]['error']) == 0) ) {
					$fh = @fopen($_FILES[$this->name]['tmp_name'], 'rb');
					if ($fh) {
						$value = $_FILES[$this->name]['name'];
						$app->tv[$this->name.'_file_type'] = $_FILES[$this->name]['type'];
						$app->tv[$this->name.'_file_content'] = fread($fh, filesize($_FILES[$this->name]['tmp_name']));
						fclose($fh);
					}
				}
				if ( (intval($_FILES[$this->name]['error']) != 0) && (intval($_FILES[$this->name]['error']) != 4) )
				{
					$errors[] = (!$this->unique_error_mess) ? $GLOBALS['app']->Localizer->get_gstring('_validator_message', 'validator_cannot_read_file', $this->display_name) : $GLOBALS['app']->Localizer->get_gstring('_validator_message', 'validator_cannot_read_file_'.$this->name, $this->display_name);
					$this->last_check = false;
					return false;
				}
			}
		}
		else
			$value = InPostGet($this->name, $this->default_value);

		if (is_array($value))
			foreach ($value as $k => $v)
				$value[$k] = trim($value[$k]);
		else
		{
			$value = trim($value);
			if ( (strlen($this->default_value)>0) && (strlen($value) == 0) )
				$value = $this->default_value;
		}

		if ( ($this->type == VRT_STATE) || ($this->type == VRT_PROVINCE) )
		{
			$dict_mod = &$GLOBALS['app']->get_module('Dictionaries');
			//$value = $dict_mod->get_state_by_id($value);
		}
		if ($this->type == VRT_COUNTRY)
		{
			$dict_mod = &$GLOBALS['app']->get_module('Dictionaries');
			//$value = $dict_mod->get_country_by_id($value);
		}
		$app->tv[$this->name] = $value;

		if ( (!$this->required) && ($value == '') )
		{
			$this->last_check = true;
			return true;
		}

		if ( (!is_array($value)) && (strlen($value)==0) )
		{
			$errors[] = (!$this->unique_error_mess) ? $GLOBALS['app']->Localizer->get_gstring('_validator_message', 'validator_empty_string', $this->display_name) : $GLOBALS['app']->Localizer->get_gstring('_validator_message', 'validator_empty_string_'.$this->name, $this->display_name);
			$this->last_check = false;
			return false;
		}

		if ($this->min_length > 0)
			if (mb_strlen($value, 'UTF-8') < $this->min_length)
			{
				$errors[] = (!$this->unique_error_mess) ? $GLOBALS['app']->Localizer->get_gstring('_validator_message', 'validator_min_length', $this->display_name, $this->min_length) : $GLOBALS['app']->Localizer->get_gstring('_validator_message', 'validator_min_length_'.$this->name, $this->display_name, $this->min_length);
				$this->last_check = false;
				return false;
			}

		if ($this->max_length > 0)
			if (mb_strlen($value, 'UTF-8') > $this->max_length)
			{
				$errors[] = (!$this->unique_error_mess) ? $GLOBALS['app']->Localizer->get_gstring('_validator_message', 'validator_max_length', $this->display_name, $this->max_length) : $GLOBALS['app']->Localizer->get_gstring('_validator_message', 'validator_max_length_'.$this->name, $this->display_name, $this->max_length);
				$this->last_check = false;
				return false;
			}

		if (strlen($this->min_numeric_value) != 0)
		{
			$tmp = ((!is_array($value))?(array($value)):($value) );
			foreach ($tmp as $vlv)
				if (doubleval($vlv) < doubleval($this->min_numeric_value))
				{
					$errors[] = (!$this->unique_error_mess) ? $GLOBALS['app']->Localizer->get_gstring('_validator_message', 'validator_min_value', $this->display_name, $this->min_numeric_value) : $GLOBALS['app']->Localizer->get_gstring('_validator_message', 'validator_min_value_'.$this->name, $this->display_name, $this->min_numeric_value);
					$this->last_check = false;
					return false;
				}
		}

		if (strlen($this->max_numeric_value))
		{
			$tmp = ((!is_array($value))?(array($value)):($value) );
			foreach ($tmp as $vlv)
				if (doubleval($vlv) > doubleval($this->max_numeric_value))
				{
					$errors[] = (!$this->unique_error_mess) ? $GLOBALS['app']->Localizer->get_gstring('_validator_message', 'validator_max_value', $this->display_name, $this->max_numeric_value) : $GLOBALS['app']->Localizer->get_gstring('_validator_message', 'validator_max_value_'.$this->name, $this->display_name, $this->max_numeric_value);
					$this->last_check = false;
					return false;
				}
		}

		if (count($this->enumeration))
		{
			$tmp = ((!is_array($value))?(array($value)):($value) );
			foreach ($tmp as $vlv)
				if (!in_array($vlv, $this->enumeration))
				{
					$errors[] = (!$this->unique_error_mess) ? $GLOBALS['app']->Localizer->get_gstring('_validator_message', 'validator_invalid_enumeration_value', $this->display_name, $this->max_numeric_value) : $GLOBALS['app']->Localizer->get_gstring('_validator_message', 'validator_invalid_enumeration_value_'.$this->name, $this->display_name);
					$this->last_check = false;
					return false;
				}
		}
		if ($this->type == VRT_KEYENUMERATION)
		{
			$tmp = ((!is_array($value))?(array($value)):($value) );
			foreach ($tmp as $vlv)
				if (!isset($this->add_info1[$vlv]))
				{
					$errors[] = (!$this->unique_error_mess) ? $GLOBALS['app']->Localizer->get_gstring('_validator_message', 'validator_invalid_enumeration_value', $this->display_name, $this->max_numeric_value) : $GLOBALS['app']->Localizer->get_gstring('_validator_message', 'validator_invalid_enumeration_value_'.$this->name, $this->display_name);
					$this->last_check = false;
					return false;
				}
		}

		if (strlen($this->pattern))
		{
			$tmp = ((!is_array($value))?(array($value)):($value) );
			foreach ($tmp as $vlv)
				if (!preg_match($this->pattern, $vlv))
				{
					$errors[] = (!$this->unique_error_mess) ? $GLOBALS['app']->Localizer->get_gstring('_validator_message', 'validator_valid_value', $this->display_name) : $GLOBALS['app']->Localizer->get_gstring('_validator_message', 'validator_valid_value_'.$this->name, $this->display_name);
					$this->last_check = false;
					return false;
				}
		}

		if ( ($this->type == VRT_CUSTOM_FILE) || ($this->type == VRT_IMAGE_FILE) )
		{
			if ($app->tv[$this->name.'_file_content'] == '')
			{
				$errors[] = (!$this->unique_error_mess) ? $GLOBALS['app']->Localizer->get_gstring('_validator_message', 'validator_cannot_read_file', $this->display_name) : $GLOBALS['app']->Localizer->get_gstring('_validator_message', 'validator_cannot_read_file_'.$this->name, $this->display_name);
				$this->last_check = false;
				return false;
			}
		}

		if ( ($this->type == VRT_CUSTOM_FILE) && (!is_null($this->add_info1)) )
		{
			if (!in_array($_FILES[$this->name]['type'], $this->add_info1))
			{
				$errors[] = (!$this->unique_error_mess) ? $GLOBALS['app']->Localizer->get_gstring('_validator_message', 'validator_invalid_file', $this->display_name, join(', ', $this->add_info1)) : $GLOBALS['app']->Localizer->get_gstring('_validator_message', 'validator_invalid_file_'.$this->name, $this->display_name, join(', ', $this->add_info1));
				$this->last_check = false;
				return false;
			}
		}
		
		

		if ($this->type == VRT_IMAGE_FILE)
		{
			$image_types = array('image/x-xbitmap', 'image/bmp', 'image/gif', 'image/jpeg', 'image/pjpeg', 'image/png', 'image/x-png', 'image/tiff', 'image/x-icon');
			if (!in_array($_FILES[$this->name]['type'], $image_types))
			{
				$errors[] = (!$this->unique_error_mess) ? $GLOBALS['app']->Localizer->get_gstring('_validator_message', 'validator_invalid_file', $this->display_name, join(', ', $image_types)) : $GLOBALS['app']->Localizer->get_gstring('_validator_message', 'validator_invalid_file_'.$this->name, $this->display_name, join(', ', $image_types));
				$this->last_check = false;
				return false;
			}
		}
		
		if ($this->type == VRT_UNIQUE)
		{
			//add_info1 - table
			//add_info2 - field
			//add_info3 - id (on update)
			if (is_null($this->add_info3)) {
				$rs = $GLOBALS['app']->DataBase->select_sql($this->add_info1, array($this->add_info2 => $value));
				if ($rs !== false && !$rs->eof()) {
					$errors[] = (!$this->unique_error_mess) ? $GLOBALS['app']->Localizer->get_gstring('_validator_message', 'validator_not_unique', $this->display_name) : $GLOBALS['app']->Localizer->get_gstring('_validator_message', 'validator_not_unique_'.$this->name, $this->display_name);
					$this->last_check = false;
					return false;
				}
			}
			else {
	        	$rs = $GLOBALS['app']->DataBase->select_custom_sql('
	            select *
	            from %prefix%'.$this->add_info1.'
	            where
	            ('.$this->add_info2.' = \''.mysql_escape_string($value).'\') and id <> '.$this->add_info3.'');
				if (($rs !== false)&&(!$rs->eof())) {
					$errors[] = (!$this->unique_error_mess) ? $GLOBALS['app']->Localizer->get_gstring('_validator_message', 'validator_not_unique', $this->display_name) : $GLOBALS['app']->Localizer->get_gstring('_validator_message', 'validator_not_unique_'.$this->name, $this->display_name);
					$this->last_check = false;
					return false;
				}
			}
		}
		if ($this->type == VRT_NOT_IN_TABLE)
		{
			//add_info1 - table
			//add_info2 - field
			//add_info3 - id, can be null
			if (is_null($this->add_info3)) {
	        	$rs = $GLOBALS['app']->DataBase->select_custom_sql('
	            select *
	            from %prefix%'.$this->add_info1.'
	            where
	            ('.$this->add_info2.' = \''.mysql_escape_string($value).'\')');
			}
			else {
				$rs = $GLOBALS['app']->DataBase->select_custom_sql('
	            select *
	            from %prefix%'.$this->add_info1.'
	            where
	            ('.$this->add_info2.' = \''.mysql_escape_string($value).'\') AND (id <> '.$this->add_info3.')');
			}
			if (($rs !== false)&&(!$rs->eof())) {
				
				$errors[] = (!$this->unique_error_mess) ? $GLOBALS['app']->Localizer->get_gstring('_validator_message', 'validator_in_table', $this->display_name) : $GLOBALS['app']->Localizer->get_gstring('_validator_message', 'validator_in_table_'.$this->name, $this->display_name);
				$this->last_check = false;
				return false;
			}
		}
		
		if ($this->type == VRT_OLD_PASSWORD) {
			//add_info1 - table
			//add_info2 - id
        	$rs = $GLOBALS['app']->DataBase->select_custom_sql('
            select *
            from %prefix%'.$this->add_info1.'
            where
            (id = '.$this->add_info2.' and password = \''.md5($value).'\')');
			if (($rs !== false)&&($rs->eof())) {
				$errors[] = (!$this->unique_error_mess) ? $GLOBALS['app']->Localizer->get_gstring('_validator_message', 'validator_old_password_invalid', $this->display_name) : $GLOBALS['app']->Localizer->get_gstring('_validator_message', 'validator_old_password_invalid_'.$this->name, $this->display_name);
				$this->last_check = false;
				return false;
			}
		}

		if ($this->type == VRT_CALLBACK)
			if (!call_user_func($this->add_info1, $value))
			{
				$errors[] = (!$this->unique_error_mess) ? $GLOBALS['app']->Localizer->get_gstring('_validator_message', 'validator_invalid_function', $this->display_name, $this->add_info1) : $GLOBALS['app']->Localizer->get_gstring('_validator_message', 'validator_invalid_function_'.$this->name, $this->display_name, $this->add_info1);
				$this->last_check = false;
				return false;
			}

		if ($this->type == VRT_CC)
		{
			require_once(BASE_CLASSES_PATH . 'components/credit_card.php');
			if (!CCreditCard::is_valid_cc($value))
			{
				$errors[] = (!$this->unique_error_mess) ? $GLOBALS['app']->Localizer->get_gstring('_validator_message', 'validator_valid_value', $this->display_name) : $GLOBALS['app']->Localizer->get_gstring('_validator_message', 'validator_valid_value_'.$this->name, $this->display_name);
				$this->last_check = false;
				return false;
			}
			else
			{
				$value = CCreditCard::get_normalize_cc($value);
				$app->tv[$this->name] = $value;
			}
		}

		if ( ($this->type == VRT_TIME) || ($this->type == VRT_DATE) || ($this->type == VRT_DATETIME) || ($this->type == VRT_ODBCDATE) || ($this->type == VRT_ODBCDATETIME) )
		{
			$matches = array();
			$year = 0;
			$month = 0;
			$day = 0;
			$hour = 0;
			$minute = 0;
			$second = 0;
			if ($this->type == VRT_TIME)
			{
				preg_match('/^([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2}) {0,1}([aApP][mM]){0,1}$/', $value, $matches);
				$year = intval(date('Y'), 10);
				$month = 1;
				$day = 1;
				$hour = intval($matches[1]);
				$minute = intval($matches[2]);
				$second = intval($matches[3]);
				if (isset($matches[4]))
					if (strcasecmp($matches[4], 'pm') == 0)
						$hour += 12;
			}
			if ($this->type == VRT_DATE)
			{
				preg_match('/^([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{4})$/', $value, $matches);
				$year = intval($matches[3]);
				$month = intval($matches[1]);
				$day = intval($matches[2]);
				$value = str_pad(strval($year), 4, '0', STR_PAD_LEFT) . '-' . str_pad(strval($month), 2, '0', STR_PAD_LEFT) . '-' . str_pad(strval($day), 2, '0', STR_PAD_LEFT);
			}
			if ($this->type == VRT_DATETIME)
			{
				preg_match('/^([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{4}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})$/', $value, $matches);
				$year = intval($matches[3]);
				$month = intval($matches[1]);
				$day = intval($matches[2]);
				$hour = intval($matches[4]);
				$minute = intval($matches[5]);
				$second = intval($matches[6]);
				$value = str_pad(strval($year), 4, '0', STR_PAD_LEFT) . '-' . str_pad(strval($month), 2, '0', STR_PAD_LEFT) . '-' . str_pad(strval($day), 2, '0', STR_PAD_LEFT) . '-' . str_pad(strval($hour), 2, '0', STR_PAD_LEFT) . '-' . str_pad(strval($minute), 2, '0', STR_PAD_LEFT) . '-' . str_pad(strval($second), 2, '0', STR_PAD_LEFT);
			}
			if ($this->type == VRT_ODBCDATE)
			{
				preg_match('/^([0-9]{4})\-([0-9]{1,2})\-([0-9]{1,2})$/', $value, $matches);
				$year = intval($matches[1]);
				$month = intval($matches[2]);
				$day = intval($matches[3]);
				$value = str_pad(strval($year), 4, '0', STR_PAD_LEFT) . '-' . str_pad(strval($month), 2, '0', STR_PAD_LEFT) . '-' . str_pad(strval($day), 2, '0', STR_PAD_LEFT);
			}
			if ($this->type == VRT_ODBCDATETIME)
			{
				preg_match('/^([0-9]{4})\-([0-9]{1,2})\-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})$/', $value, $matches);
				$year = intval($matches[1]);
				$month = intval($matches[2]);
				$day = intval($matches[3]);
				$hour = intval($matches[4]);
				$minute = intval($matches[5]);
				$second = intval($matches[6]);
				$value = str_pad(strval($year), 4, '0', STR_PAD_LEFT) . '-' . str_pad(strval($month), 2, '0', STR_PAD_LEFT) . '-' . str_pad(strval($day), 2, '0', STR_PAD_LEFT) . '-' . str_pad(strval($hour), 2, '0', STR_PAD_LEFT) . '-' . str_pad(strval($minute), 2, '0', STR_PAD_LEFT) . '-' . str_pad(strval($second), 2, '0', STR_PAD_LEFT);
			}

			if ($year < 1900)
			{
				$errors[] = (!$this->unique_error_mess) ? $GLOBALS['app']->Localizer->get_gstring('_validator_message', 'validator_invalid_year', $this->display_name) : $GLOBALS['app']->Localizer->get_gstring('_validator_message', 'validator_invalid_year_'.$this->name, $this->display_name);
				$this->last_check = false;
				return false;
			}

			if ( ($month<1) || ($month>12) )
			{
				$errors[] = (!$this->unique_error_mess) ? $GLOBALS['app']->Localizer->get_gstring('_validator_message', 'validator_invalid_date', $this->display_name) : $GLOBALS['app']->Localizer->get_gstring('_validator_message', 'validator_invalid_date_'.$this->name, $this->display_name);
				$this->last_check = false;
				return false;
			}

			$months = array(1=>31,2=>28,3=>31,4=>30,5=>31,6=>30,7=>31,8=>31,9=>30,10=>31,11=>30,12=>31);
			if (is_leap_year($year)) $months[2] = 29;
			if ( ($day<1) || ($day>$months[$month]) )
			{
				$errors[] = (!$this->unique_error_mess) ? $GLOBALS['app']->Localizer->get_gstring('_validator_message', 'validator_invalid_date', $this->display_name) : $GLOBALS['app']->Localizer->get_gstring('_validator_message', 'validator_invalid_date_'.$this->name, $this->display_name);
				$this->last_check = false;
				return false;
			}

			if ( ($hour<0) || ($hour>23) )
			{
				$errors[] = (!$this->unique_error_mess) ? $GLOBALS['app']->Localizer->get_gstring('_validator_message', 'validator_invalid_time', $this->display_name) : $GLOBALS['app']->Localizer->get_gstring('_validator_message', 'validator_invalid_time_'.$this->name, $this->display_name);
				$this->last_check = false;
				return false;
			}
			if ( ($minute<0) || ($minute>59) )
			{
				$errors[] = (!$this->unique_error_mess) ? $GLOBALS['app']->Localizer->get_gstring('_validator_message', 'validator_invalid_time', $this->display_name) : $GLOBALS['app']->Localizer->get_gstring('_validator_message', 'validator_invalid_time_'.$this->name, $this->display_name);
				$this->last_check = false;
				return false;
			}
			if ( ($second<0) || ($second>59) )
			{
				$errors[] = (!$this->unique_error_mess) ? $GLOBALS['app']->Localizer->get_gstring('_validator_message', 'validator_invalid_time', $this->display_name) : $GLOBALS['app']->Localizer->get_gstring('_validator_message', 'validator_invalid_time_'.$this->name, $this->display_name);
				$this->last_check = false;
				return false;
			}
		}
		
		if ($this->type == VRT_IN_TABLE)
		{
			//add_info1 - table
			//add_info2 - field
			//add_info3 - id, can be null
			if (is_null($this->add_info3)) {
	        	$rs = $GLOBALS['app']->DataBase->select_custom_sql('
	            select *
	            from %prefix%'.$this->add_info1.'
	            where
	            ('.$this->add_info2.' = \''.mysql_escape_string($value).'\')');
			}
			else {
				$rs = $GLOBALS['app']->DataBase->select_custom_sql('
	            select *
	            from %prefix%'.$this->add_info1.'
	            where
	            ('.$this->add_info2.' = \''.mysql_escape_string($value).'\') AND (id <> '.$this->add_info3.')');
			}
			if (($rs == false) || ($rs->eof())) {
				$errors[] = (!$this->unique_error_mess) ? $GLOBALS['app']->Localizer->get_gstring('_validator_message', 'validator_not_in_table', $this->display_name) : $GLOBALS['app']->Localizer->get_gstring('_validator_message', 'validator_not_in_table_'.$this->name, $this->display_name);
				$this->last_check = false;
				return false;
			}
		}
				
		if ($this->last_check)
			$infos[] = $this->name.' is valid';
		
			
		if(preg_match('/<script/ui', $value))
			$value = js_escape_string(str_replace('<script', '&lt;script', str_replace('</script>', '&lt;/script&gt;', $value)));

		$app->tv[$this->name] = $value;
		return $this->last_check;
	}

};
?>