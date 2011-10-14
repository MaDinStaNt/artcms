<?
class CSimpleArrayOutput
{
	function create_html($array, $block_begin = '<div><ul>', $item_begin = '<li>', $item_end = '</li>', $block_end = '</ul></div>', $is_html = false)
	{
		global $app;
		$tv = &$app->tv;
		if(is_array($tv[$array]))
		{
			echo $block_begin;
			foreach ($tv[$array] as $v) echo $item_begin.$v.$item_end;
			echo $block_end;
		}
		elseif (strval($tv[$array])) echo $block_begin.$item_begin.$tv[$array].$item_end.$block_end;
		elseif(isset($tv[$array])) system_die('Invalid input $array: link on array or string', 'SimpleArrayOutput->create_html');
	}
}

?>