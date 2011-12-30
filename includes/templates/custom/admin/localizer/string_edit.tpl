<? CForm::begin($_table, 'POST', '', 'multipart/form-data'); ?>
	<div class="note">
		<span class="note_title"><? CTemplate::loc_string('warning'); ?>!</span> <? CTemplate::loc_string('fields_marked_with'); ?> <strong><? CTemplate::loc_string('bold'); ?></strong> <? CTemplate::loc_string('has_been_required'); ?>
	</div>		

	<? if($id): ?>
		<h2><? CTemplate::loc_string('localizer_string'); ?>: <? echo $name; ?></h2>
	<? else: ?>
		<h2><? CTemplate::loc_string('new_localizer_string'); ?></h2>
	<? endif; ?>
	
	<div class="col">
		<div class="row">
			<label for="language_id"><strong><? CTemplate::loc_string('language_id'); ?>:</strong></label>
			<? CTemplate::input('select', 'language_id', 'language_id', 'sel'); ?>
		</div>
		<div class="row">
			<label for="name"><strong><? CTemplate::loc_string('name'); ?>:</strong></label>
			<? CTemplate::input('text', 'name', 'name', 'inp'); ?>
		</div>
		<div class="row">
			<label for="value"><? CTemplate::loc_string('value'); ?>:</label>
			<? CTemplate::input('text', 'value', 'value', 'inp'); ?>
		</div>
		<div class="buttons">
			<div class="inpwrapper"><? CTemplate::submit('save', 'save', CTemplate::get_loc_string('btn_save'), 'butt'); ?></div>
			<div class="inpwrapper"><? CTemplate::reset('reset', 'reset', CTemplate::get_loc_string('btn_reset'), 'butt'); ?></div>
			<div class="inpwrapper"><? CTemplate::button('close', 'close', CTemplate::get_loc_string('btn_close'), 'butt'); ?></div>
		</div>
	</div>
<? CForm::end(); ?>
