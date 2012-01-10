<?php
$formname = '';
$_it_fs = array();
class CTemplate {
	
	function get_loc_string($str){
		return $GLOBALS['app']->Localizer->get_string($str);
	}
	
	function loc_string($str){
		echo $GLOBALS['app']->Localizer->get_string($str);
	}

	function button($id, $name, $value, $class = '', $target = '', $param1 = '')
	{
		global $formname;
		if(strlen($formname) > 0) echo "<input type=\"button\" id=\"{$id}\" name=\"{$name}\" value=\"{$value}\" onclick=\"{$formname}_submit('{$name}', '{$target}', '{$param1}', 0);\" class=\"{$class}\" />";
		else system_die('opening form tag not found');
	}
	
	function reset($id, $name, $value, $class = '', $target = '', $param1 = '')
	{
		global $formname;
		if(strlen($formname) > 0) echo "<input type=\"reset\" id=\"{$id}\" name=\"{$name}\" value=\"{$value}\" class=\"{$class}\" />";
		else system_die('opening form tag not found');
	}
	
	function submit($id, $name, $value, $class = '', $target = '', $param1 = '')
	{
		global $formname;
		if(strlen($formname) > 0) echo "<input type=\"submit\" id=\"{$id}\" name=\"{$name}\" value=\"{$value}\" class=\"{$class}\" />";
		else system_die('opening form tag not found');
	}
	
	function input($type, $id, $name, $class = '', $readonly = false, $disabled = false, $checked = false)
	{
		global $app, $_it_fs, $formname;
		$tv = $app->tv;
		$tv[$name] = str_replace('"', '&quot;', str_replace('«', '&laquo;', str_replace('»', '&raquo;', $tv[$name])));
		switch ($type)
		{
			case 'text':
				if(XHTML)
					echo '<input type="text" id="'.$id.'" name="'.$name.'"'.((strlen($class)>0) ? ' class="'.$class.'"' : null).' value="'.$tv[$name].'"'.(($readonly) ? ' readonly="readonly"' : null).' '.(($disabled) ? ' disabled="disabled"' : null).' />';
				else
					echo '<input type="text" id="'.$id.'" name="'.$name.'"'.((strlen($class)>0) ? ' class="'.$class.'"' : null).' value="'.$tv[$name].'"'.(($readonly) ? ' readonly' : null).' '.(($disabled) ? ' disabled' : null).'>';
				break;
			case 'hidden':
				if(XHTML)
					echo '<input type="hidden" id="'.$id.'" name="'.$name.'"'.((strlen($class)>0) ? ' class="'.$class.'"' : null).' value="'.$tv[$name].'"'.(($readonly) ? ' readonly="readonly"' : null).' '.(($disabled) ? ' disabled="disabled"' : null).' />';
				else
					echo '<input type="hidden" id="'.$id.'" name="'.$name.'"'.((strlen($class)>0) ? ' class="'.$class.'"' : null).' value="'.$tv[$name].'"'.(($readonly) ? ' readonly' : null).' '.(($disabled) ? ' disabled' : null).'>';
				break;
			case 'password':
				if(XHTML)
					echo '<input type="password" id="'.$id.'" name="'.$name.'"'.((strlen($class)>0) ? ' class="'.$class.'"' : null).' value=""'.(($readonly) ? ' readonly="readonly"' : null).' '.(($disabled) ? ' disabled="disabled"' : null).' />';
				else
					echo '<input type="password" id="'.$id.'" name="'.$name.'"'.((strlen($class)>0) ? ' class="'.$class.'"' : null).' value=""'.(($readonly) ? ' readonly' : null).' '.(($disabled) ? ' disabled' : null).' />';
				break;
			case 'checkbox':
				if(XHTML)
					echo '<input type="checkbox" id="'.$id.'" name="'.$name.'"'.((strlen($class)>0) ? ' class="'.$class.'"' : null).' value="1"'.(($readonly) ? ' readonly="readonly"' : null).' '.(($disabled) ? ' disabled="disabled"' : null).''.((intval($tv[$name]) > 0) ? 'checked="checked"' : null).' />';
				else
					echo '<input type="checkbox" id="'.$id.'" name="'.$name.'"'.((strlen($class)>0) ? ' class="'.$class.'"' : null).' value="1"'.(($readonly) ? ' readonly' : null).' '.(($disabled) ? ' disabled' : null).''.((intval($tv[$name]) > 0 || $checked) ? 'checked' : null).' />';
				break;
			case 'select':
				if(!isset($tv["{$name}:select"]) || !is_array($tv["{$name}:select"])) system_die('Invalid data for select: use CInput::set_select_data()', 'CTemplate::input');
				if(XHTML)
					echo '<select id="'.$id.'" name="'.$name.'"'.((strlen($class)>0) ? ' class="'.$class.'"' : null).(($readonly) ? ' readonly="readonly"' : null).' '.(($disabled) ? ' disabled="disabled"' : null).'>';
				else 
					echo '<select id="'.$id.'" name="'.$name.'"'.((strlen($class)>0) ? ' class="'.$class.'"' : null).(($readonly) ? ' readonly' : null).' '.(($disabled) ? ' disabled' : null).'>';
					
				foreach ($tv["{$name}:select"] as $id => $text)
					if($tv[$name] == $id) 
						echo '<option value="'.$id.'"'.((XHTML) ? 'selected="selected"' : 'selected').'>'.$text.'</option>';
					else 
						echo '<option value="'.$id.'">'.$text.'</option>';
				echo '</select>';
				break;
			case 'file':
				if(XHTML)
					echo '<input type="file" id="'.$id.'" name="'.$name.'"'.((strlen($class)>0) ? ' class="'.$class.'"' : null).' value="'.$tv[$name].'"'.(($readonly) ? ' readonly="readonly"' : null).' '.(($disabled) ? ' disabled="disabled"' : null).' />';
				else	
					echo '<input type="file" id="'.$id.'" name="'.$name.'"'.((strlen($class)>0) ? ' class="'.$class.'"' : null).' value="'.$tv[$name].'"'.(($readonly) ? ' readonly' : null).' '.(($disabled) ? ' disabled' : null).' />';
			break;
		}
		
		if(!isset($_it_fs[$formname])) $_it_fs[$formname] = array();
		$_it_fs[$formname][] = $name;
		
	}
	
	function textarea($id, $name, $class = '', $is_htmlarea = false, $cols = 150, $rows = 7, $readonly = false, $disabled = false)
	{
		global $app, $_it_fs, $formname;
		$tv = &$app->tv;
		if(!$is_htmlarea)
			if(XHTML)
				echo '<textarea id="'.$id.'" name="'.$name.'" cols="'.$cols.'" rows="'.$rows.'"'.((strlen($class) > 0) ? ' class="'.$class.'"' : null).(($readonly) ? ' readonly="readonly"' : null).' '.(($disabled) ? ' disabled="disabled"' : null).'>'.$tv[$name].'</textarea>';
			else 
				echo '<textarea id="'.$id.'" name="'.$name.'" cols="'.$cols.'" rows="'.$rows.'"'.((strlen($class) > 0) ? ' class="'.$class.'"' : null).(($readonly) ? ' readonly' : null).' '.(($disabled) ? ' disabled' : null).'>'.$tv[$name].'</textarea>';
		else 
		{
			$out = '<div class="html-cont">
		            <!-- TinyMCE -->
					<script type="text/javascript" src="'.$tv['HTTP'].'js/tiny_mce/tiny_mce.js"></script>
					<script type="text/javascript">
						tinyMCE.init({
							// General options
							mode : "specific_textareas",
		                    editor_selector : "mceEditor",
							theme : "advanced",
							plugins : "autolink,lists,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,wordcount,advlist,autosave",
							// Theme options
							theme_advanced_buttons1 : "bold,italic,underline,strikethrough,separator,sub,sup,separator,justifyleft,justifycenter,justifyright,justifyfull,separator,formatselect,fontselect,fontsizeselect",
		                    theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,separator,undo,redo,separator,search,replace,separator,bullist,numlist,separator,outdent,indent,blockquote,separator,link,unlink,anchor,charmap,hr",
		                    theme_advanced_buttons3 : "forecolor,backcolor,|,insertdate,inserttime,|,removeformat,cleanup,|,preview,print,fullscreen,|,code",
							theme_advanced_toolbar_location : "top",
							theme_advanced_toolbar_align : "left",
							theme_advanced_statusbar_location : "bottom",
							theme_advanced_resizing : true,
							
							// Example content CSS (should be your site CSS)
							content_css : "'.$tv['CSS'].'weditor.css",
							
						});
					</script>
					<!-- /TinyMCE -->';
			$out .= '<textarea mce_editable="true" class="mceEditor" id="'.$id.'" name="'.$name.'">'.htmlspecialchars(strval($tv[$name])).'</textarea></div>';
			echo $out;
		}
		if(!isset($_it_fs[$formname])) $_it_fs[$formname] = array();
		$_it_fs[$formname][] = $name;
	}
	
	function parse_string($str, $tv = null)
	{
		if(is_null($tv))
			$tv = $app->tv;
			
		foreach ($tv as $var => $value)
			$str = str_replace("<%={$var}%>", strval($value), $str);
			
		return $str;
	}

}

class CForm{
	
	function begin($id, $method = 'POST', $action = '', $enctype = '', $class = '', $ajaxValidator = false){
		global $formname, $app;
		if(strlen($formname) > 0) system_die('opening form tag can not be used because the previous form is not closed');
		else 
		{
			$formname = $id;
			echo "<script language=\"JavaScript\" type=\"text/javascript\"><!--
				function {$id}_submit(act,trg,prm,v) {
					var f=document.forms.{$id};
					f.target=trg;
					f.elements.param1.value = prm;
					f.elements.param2.value = act;
					
					if ((act != '') && (!v)) {
						{$id}noa=1;
						f.submit();
					} else {
						{$id}oa=1;
						f.submit();
					}
				}
				//-->
				</script>";
			echo "<form id=\"{$id}\" method=\"{$method}\" action=\"{$action}\"".((strlen($enctype) > 0) ? " enctype=\"{$enctype}\"" : null)." class=\"".((strlen($class) > 0) ? "{$class}" : null).(($ajaxValidator === true) ? " ajax_validation" : null)."\">";
			echo "<input type=\"hidden\" name=\"formname\" value=\"{$id}\" />";
			echo "<input type=\"hidden\" name=\"param2\" value=\"\" /><input type=\"hidden\" name=\"param1\" value=\"\" />";
			if($ajaxValidator === true && $page_class = get_class($app->CurrentPage))
				echo "<input type=\"hidden\" id=\"page_class\" name=\"page_class\" value=\"{$page_class}\" />"; 
			
		}
	}
	
	function end()
	{
		global $formname, $_it_fs;
		if(strlen($formname) > 0)
		{
			echo '<input type="hidden" name="_it_fs" value="'. base64_encode(join(',', $_it_fs[$formname])) .'" />';
			echo '</form>';
			$formname = '';
		}
		else system_die('closing tag can not be used because of lack of opening <form>');	
	}
	
	function is_submit($name, $action = null, $check_action = true) {
		$r = (strcasecmp(InPostGet('formname'), $name)==0);
		if ($check_action)
			if (is_null($action)) return ( $r && (0==strlen(InPostGet('param2'))) );
			else return ( $r && (strcasecmp(InPostGet('param2'), $action)==0) );
		else return $r;
	}

	function get_param() {
		return InPostGet('param1');
	}
}

class CInput{
	
	function set_select_data($name, $arr, $id='id', $text='title', &$tv = null)
	{
		global $app;
		if(is_null($tv)) $tv = &$app->tv;
		
		$tv[$name.':select'] = array();
		
		if(is_array($arr)) foreach ($arr as $key => $val) $tv[$name.':select'][$key] = $val;
		elseif (strcasecmp(get_class($arr), 'CRecordSet') == 0)
		{
			if($arr !== false && !$arr->eof())
				while (!$arr->eof())
				{
					$tv[$name.':select'][$arr->get_field($id)] = $arr->get_field($text);
					$arr->next();
				}
			else 
				$tv[$name.':select'][''] = $GLOBALS['app']->Localizer->get_string('internal_error');
		}
	}
}

class CTemplateVar{
	
	protected $value;
	
	function CTemplateVar(&$val)
	{
		if(is_object($val))
			system_die('Invalid input $val (string or array)', 'CTemplateVar');
		
		if(is_array($val))
			foreach ($val as $k => $v)
				$val[$k] = new CTemplateVar($val[$k]);
				
		$this->value = $val;
	}
	
	function escape($print = true)
	{
		if(!is_bool($print))
			system_die('Invalid input $print (bool)', 'CTemplateVar -> escape()');
			
		if($print === true)
			echo htmlspecialchars($this->value);
		else 
			return htmlspecialchars($this->value);
	}
	function js_escape($print = true)
	{
		if(!is_bool($print))
			system_die('Invalid input $print (bool)', 'CTemplateVar -> js_escape()');
			
		if($print === true)
			echo stripcslashes(js_escape_string($this->value));
		else 
			return stripcslashes(js_escape_string($this->value));
	}
	function html_escape($print = true, $allowable_tags = '<p><strong><b><font><h1><h2><h3><h4><h5><h6>')
	{
		if(!is_bool($print))
			system_die('Invalid input $print (bool)', 'CTemplateVar -> html_escape()');
		if(!is_string($allowable_tags))
			system_die('Invalid input $allowable_tags (string)', 'CTemplateVar -> html_escape()');
			
		if($print === true)
			echo strip_tags($this->value, $allowable_tags);
		else 
			return strip_tags($this->value, $allowable_tags);
	}
	function _print()
	{
		echo $this->value;
	}
	function get_value()
	{
		return $this->value; 
	}
}
?>