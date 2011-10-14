<? CForm::begin($_table); ?>
	<div class="note">
		<span class="note_title">Warning!</span> Fields marked with <strong>bold</strong> are obligatory
	</div>	

	<? if($id): ?>
		<h2>Path: <? echo $path_title; ?> -> Value: <? echo $title; ?></h2>
	<? else: ?>
		<h2>Path: <? echo $path_title; ?> -> New Value</h2>
	<? endif; ?>
	
	<div class="col left_block">
		<div class="row">
			<label for="path_id"><strong><? CTemplate::loc_string('path_id'); ?>:</strong></label>
			<? CTemplate::input('select', 'path_id', 'path_id', 'sel'); ?>
		</div>
		<div class="row">
			<label for="type"><strong><? CTemplate::loc_string('type'); ?>:</strong></label>
			<? CTemplate::input('select', 'type', 'type', 'sel'); ?>
		</div>
		<div class="row">
			<label for="path"><strong><? CTemplate::loc_string('path'); ?>:</strong></label>
			<? CTemplate::input('text', 'path', 'path', 'inp'); ?>
		</div>
		<div class="row">
			<label for="title"><strong><? CTemplate::loc_string('title'); ?>:</strong></label>
			<? CTemplate::input('text', 'title', 'title', 'inp'); ?>
		</div>
		<div class="row">
			<label for="required"><? CTemplate::loc_string('required'); ?>:</label>
			<? CTemplate::input('checkbox', 'required', 'required', 'chk'); ?>
		</div>
		<div class="row">
			<label for="value"><? CTemplate::loc_string('value'); ?>:</label>
			<? CTemplate::textarea('value', 'value', 'inp'); ?>
		</div>
		<div class="buttons">
			<div class="inpwrapper"><? CTemplate::submit('save', 'save', CTemplate::get_loc_string('btn_save'), 'butt'); ?></div>
			<div class="inpwrapper"><? CTemplate::reset('reset', 'reset', CTemplate::get_loc_string('btn_reset'), 'butt'); ?></div>
			<div class="inpwrapper"><? CTemplate::button('close', 'close', CTemplate::get_loc_string('btn_close'), 'butt'); ?></div>
		</div>
	</div>
	<div class="col">
		<div class="row">
			<label for="valid_type"><? CTemplate::loc_string('valid_type'); ?>:</label>
			<? CTemplate::input('select', 'valid_type', 'valid_type', 'sel'); ?>
		</div>
		<div class="row">
			<label for="unique_error_mess"><? CTemplate::loc_string('unique_error_mess'); ?>:</label>
			<? CTemplate::input('checkbox', 'unique_error_mess', 'unique_error_mess', 'chk'); ?>
		</div>
		<div class="row">
			<label for="valid_add_info1"><? CTemplate::loc_string('valid_add_info1'); ?>:</label>
			<? CTemplate::textarea('valid_add_info1', 'valid_add_info1', 'inp'); ?>
		</div>
		<div class="row">
			<label for="image_width"><? CTemplate::loc_string('valid_add_info2'); ?>:</label>
			<? CTemplate::textarea('valid_add_info2', 'valid_add_info2', 'inp'); ?>
		</div>
		<div class="row">
			<label for="valid_add_info3"><? CTemplate::loc_string('valid_add_info3'); ?>:</label>
			<? CTemplate::textarea('valid_add_info3', 'valid_add_info3', 'inp'); ?>
		</div>
	</div>
	<div class="clear">&nbsp;</div>

<? CForm::end(); ?>

