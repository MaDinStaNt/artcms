<ul class="paginator">
	<? if($lower_total_numb): ?>
		<? foreach ($pages as $page): ?>
			<? if($page == $curr_page): ?>
				<li class="act"><span><? echo $page; ?></span></li>
			<? else: ?>
				<li><a href="<? echo $link; ?><? echo $page; ?>" title="<? CTemplate::loc_string('go_to'); ?> <? echo $page; ?> <? CTemplate::loc_string('page'); ?>"><? echo $page; ?></a></li>
			<? endif; ?>
		<? endforeach; ?>
	<? else: ?>
	
		<script type="text/javascript">
		$(function(){
			values = [
				<? foreach ($pages as $page): ?>
					<? echo $page; ?>,
				<? endforeach; ?>
			];
			$('#<? echo $object_id; ?>').Paginator( "<? echo $link; ?>", values, "...","<? CTemplate::loc_string('pages_not_found'); ?>");
			<? foreach ($pages as $page): ?>
				<? if($page == $curr_page): ?>
					var old_val = "<? echo $page; ?>";
					$('#<? echo $object_id; ?>_val').val("<? echo $page; ?>");
				<? endif; ?>
			<? endforeach; ?>
		});
		</script>
		<? for($page = 1; $page <= $cnt_left; $page++): ?>
			<? if ($page == $curr_page): ?>
				<li class="act"><span><? echo $page; ?></span></li>
			<? else: ?>
				<li><a href="<? echo $link; ?><? echo $page; ?>" title="<? CTemplate::loc_string('go_to'); ?> <? echo $page; ?> <? CTemplate::loc_string('page'); ?>"><? echo $page; ?></a></li>
			<? endif; ?>
		<? endfor; ?>
		
		<li id="<? echo $object_id; ?>" class="select-cont">
			<div class="custom-select-cont">
				<div class="custom-select">
					<ul>
						<li class="selected"><span class="val">&nbsp;</span><? CTemplate::input('text', "{$object_id}_val", "{$object_id}_val", 'inpHide'); ?></li>
					</ul>
					<div class="exp-butt">&nbsp;</div>
				</div>
				<div class="custom-option">
					<ul>
						
					</ul>
				</div>
			</div>
			<select name="paginator_<? echo $object_id; ?>" id="paginator_<? echo $object_id; ?>" class="customsel">
				<option value="">...</option>
				<? foreach ($pages as $page): ?>
					<? if($page == $curr_page): ?>
						<option value="<? echo $page; ?>" selected="selected"><? echo $page; ?></option>
					<? else: ?>
						<option value="<? echo $page; ?>" onclick="gotoURL('<? echo $link; ?><? echo $page; ?>');"><? echo $page; ?></option>
					<? endif; ?>
				<? endforeach; ?>
			</select>
		</li>
		
		<? for($page = $right_start; $page <= $cnt_pages; $page++): ?>
			<? if ($page == $curr_page): ?>
				<li class="act"><span><? echo $page; ?></span></li>
			<? else: ?>
				<li><a href="<? echo $link; ?><? echo $page; ?>" title="<? CTemplate::loc_string('go_to'); ?> <? echo $page; ?> <? CTemplate::loc_string('page'); ?>"><? echo $page; ?></a></li>
			<? endif; ?>
		<? endfor; ?>
	<? endif; ?>
</ul>