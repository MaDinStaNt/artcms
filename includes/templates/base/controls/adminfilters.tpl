<? if(!empty($filter)): ?>
	<div class="filter">
		<div class="wrapper">
			<? foreach ($filter as $field => $data): ?>
				<div class="cont">
					<? if($data['type'] == FILTER_DATE): ?>
						<label for="<? echo $field; ?>_from" class="label date"><? echo $data['title']; ?>:</label>
						<? CTemplate::input('text', $field.'_from', 'adminfilter_'.$field.'_from', 'inp inpDate'); ?>
						<label for="<? echo $field; ?>_to" class="label sm"><? CTemplate::loc_string("to"); ?> :</label>
						<? CTemplate::input('text', $field.'_to', 'adminfilter_'.$field.'_to', 'inp inpDate'); ?>
					<? else: ?>
						<label for="<? echo $field; ?>" class="label"><? echo $data['title']; ?>:</label> 
						<? if ($data['type'] == FILTER_TEXT): ?>
							<? CTemplate::input('text', 'adminfilter_'.$field, 'adminfilter_'.$field, 'inp'); ?>
						<? elseif($data['type'] == FILTER_SELECT): ?>
							<? CTemplate::input('select', 'adminfilter_'.$field, 'adminfilter_'.$field, 'sel'); ?>
						<? elseif($data['type'] == FILTER_ENUM): ?>
							<? CTemplate::input('admfilter-radio', 'adminfilter_'.$field, 'adminfilter_'.$field); ?>		
						<? endif; ?>
					<? endif; ?>
				</div>
			<? endforeach; ?>
		</div>
		<div class="clear">&nbsp;</div>
		<div class="buttons">
			<div class="finpwrapper"><? CTemplate::button("{$object_id}_clear", "clear", CTemplate::get_loc_string('clear'), 'fbutt'); ?></div>
			<div class="finpwrapper"><? CTemplate::button("{$object_id}_filter", "filter", CTemplate::get_loc_string('filter'), 'fbutt'); ?></div>
		</div>
	</div>
<? endif; ?>