<ul class="paginator dbnavsize">
	<li><? CTemplate::loc_string('items_on_page'); ?>:</li>
	<? foreach ($sizes as $s): ?>
		 <? if($s == $size_act): ?>
			<li class="act"><span><? echo $s; ?></span></li>
		<? else: ?>
			<li><a href="<? echo $link; ?><? echo $s; ?>" title="<? CTemplate::loc_string('dbnav_size_show'); ?> <? echo $s; ?> <? CTemplate::loc_string('dbnav_size_items'); ?>"><? echo $s; ?></a></li>
		<? endif; ?>
	<? endforeach; ?>
</ul>