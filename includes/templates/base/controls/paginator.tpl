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
			//alert($('#<? echo $object_id; ?> ul li').length);
			if($('#<? echo $object_id; ?> .custom-select ul li').length == 0) $('#<? echo $object_id; ?> .custom-select ul').html('<li class="selected"><span class="val">&nbsp;</span>...</li>');
			$('#<? echo $object_id; ?> .custom-select').click(function(){
				$('#<? echo $object_id; ?> .custom-option').toggle();
			});
			
			$('#<? echo $object_id; ?> .custom-option li').hover(function(){$(this).addClass('act')}, function(){$(this).removeClass('act')});
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
						<? foreach ($pages as $page): ?>
							<? if($page == $curr_page): ?>
								<li class="selected"><span class="val"><? echo $page; ?></span><? echo $page; ?></li>
							<? endif; ?>
						<? endforeach; ?>
					</ul>
					<div class="exp-butt">&nbsp;</div>
				</div>
				<div class="custom-option">
					<ul>
						<? foreach ($pages as $page): ?>
							<li onclick="gotoURL('<? echo $link; ?><? echo $page; ?>');"><span class="val"><? echo $page; ?></span><? echo $page; ?></li>
						<? endforeach; ?>
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