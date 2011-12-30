<? CForm::begin($_table); ?>
	<div class="note">
		<span class="note_title"><? CTemplate::loc_string('warning'); ?>!</span> <? CTemplate::loc_string('fields_marked_with'); ?> <strong><? CTemplate::loc_string('bold'); ?></strong> <? CTemplate::loc_string('has_been_required'); ?>
	</div>		

	<? if($id): ?>
		<h2><? CTemplate::loc_string('language'); ?>: <? echo $title; ?></h2>
	<? else: ?>
		<h2><? CTemplate::loc_string('new_language'); ?></h2>
	<? endif; ?>
	
	<div class="col">
		<div class="row">
			<label for="title"><strong><? CTemplate::loc_string('title'); ?>:</strong></label>
			<? CTemplate::input('text', 'title', 'title', 'inp'); ?>
		</div>
		<div class="row">
			<label for="abbreviation"><strong><? CTemplate::loc_string('abbreviation'); ?>:</strong></label>
			<? CTemplate::input('text', 'abbreviation', 'abbreviation', 'inp inp1'); ?>
		</div>
		<div class="buttons">
			<div class="inpwrapper"><? CTemplate::submit('save', 'save', CTemplate::get_loc_string('btn_save'), 'butt'); ?></div>
			<div class="inpwrapper"><? CTemplate::reset('reset', 'reset', CTemplate::get_loc_string('btn_reset'), 'butt'); ?></div>
			<div class="inpwrapper"><? CTemplate::button('close', 'close', CTemplate::get_loc_string('btn_close'), 'butt'); ?></div>
		</div>
	</div>
<? CForm::end(); ?>
