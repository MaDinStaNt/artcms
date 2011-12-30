<? CForm::begin($_table); ?>
	<div class="note">
		<span class="note_title"><? CTemplate::loc_string('warning'); ?>!</span> <? CTemplate::loc_string('fields_marked_with'); ?> <strong><? CTemplate::loc_string('bold'); ?></strong> <? CTemplate::loc_string('has_been_required'); ?>
	</div>	

	<? if($id): ?>
		<h2><? CTemplate::loc_string('user_role'); ?>: <? echo $title; ?></h2>
	<? else: ?>
		<h2><? CTemplate::loc_string('new_user_role'); ?></h2>
	<? endif; ?>
	
	<div class="col">
		<div class="row">
			<label for="id"><strong><? CTemplate::loc_string('id'); ?>:</strong></label>
			<? CTemplate::input('text', 'id', 'id', 'inp'); ?>
		</div>
		<div class="row">
			<label for="title"><strong><? CTemplate::loc_string('title'); ?>:</strong></label>
			<? CTemplate::input('text', 'title', 'title', 'inp'); ?>
		</div>
		<div class="row">
			<label for="description"><? CTemplate::loc_string('description'); ?>:</label>
			<? CTemplate::input('text', 'description', 'description', 'inp'); ?>
		</div>
		<div class="buttons">
			<div class="inpwrapper"><? CTemplate::submit('save', 'save', CTemplate::get_loc_string('btn_save'), 'butt'); ?></div>
			<div class="inpwrapper"><? CTemplate::reset('reset', 'reset', CTemplate::get_loc_string('btn_reset'), 'butt'); ?></div>
			<div class="inpwrapper"><? CTemplate::button('close', 'close', CTemplate::get_loc_string('btn_close'), 'butt'); ?></div>
		</div>
	</div>

<? CForm::end(); ?>
