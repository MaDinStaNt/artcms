<? CForm::begin($_table); ?>
	<div class="note">
		<span class="note_title"><? CTemplate::loc_string('warning'); ?>!</span> <? CTemplate::loc_string('fields_marked_with'); ?> <strong><? CTemplate::loc_string('bold'); ?></strong> <? CTemplate::loc_string('has_been_required'); ?>
	</div>

	<? if($id): ?>
		<h2><? CTemplate::loc_string('image_size'); ?>: <? echo $image_width; ?>x<? echo $image_height; ?></h2>
	<? else: ?>
		<h2><? CTemplate::loc_string('new_image_size'); ?></h2>
	<? endif; ?>
	
	<div class="col">
		<div class="row">
			<label for="name"><strong><? CTemplate::loc_string('system_key'); ?>:</strong></label>
			<? if($id): ?>
				<? CTemplate::input('text', 'system_key', 'system_key', 'inp', true); ?>
			<? else: ?>
				<? CTemplate::input('text', 'system_key', 'system_key', 'inp'); ?>
			<? endif; ?>
		</div>
		<div class="row">
			<label for="image_width"><? CTemplate::loc_string('image_width'); ?>:</label>
			<? CTemplate::input('text', 'image_width', 'image_width', 'inp inp1'); ?>
		</div>
		<div class="row">
			<label for="image_height"><? CTemplate::loc_string('image_height'); ?>:</label>
			<? CTemplate::input('text', 'image_height', 'image_height', 'inp inp1'); ?>
		</div>
		<div class="row">
			<label for="thumbnail_method"><? CTemplate::loc_string('thumbnail_method'); ?>:</label>
			<? CTemplate::input('select', 'thumbnail_method', 'thumbnail_method', 'sel'); ?>
		</div>
		<div class="buttons">
			<div class="inpwrapper"><? CTemplate::submit('save', 'save', CTemplate::get_loc_string('btn_save'), 'butt'); ?></div>
			<div class="inpwrapper"><? CTemplate::reset('reset', 'reset', CTemplate::get_loc_string('btn_reset'), 'butt'); ?></div>
			<div class="inpwrapper"><? CTemplate::button('close', 'close', CTemplate::get_loc_string('btn_close'), 'butt'); ?></div>
		</div>
	</div>
<? CForm::end(); ?>

