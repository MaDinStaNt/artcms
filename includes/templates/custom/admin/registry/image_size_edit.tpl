<? CForm::begin($_table); ?>
	<div class="note">
		<span class="note_title">Warning!</span> Fields marked with <strong>bold</strong> are obligatory
	</div>	

	<? if($id): ?>
		<h2>Image Size: <? echo $system_key; ?></h2>
	<? else: ?>
		<h2>New Image Size</h2>
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

