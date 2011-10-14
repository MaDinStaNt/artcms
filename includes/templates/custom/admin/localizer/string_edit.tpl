<? CForm::begin($_table); ?>
	<div class="note">
		<span class="note_title">Warning!</span> Fields marked with <strong>bold</strong> are obligatory
	</div>	

	<? if($id): ?>
		<h2>Localizer String: <? echo $name; ?></h2>
	<? else: ?>
		<h2>New Localizer String</h2>
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
