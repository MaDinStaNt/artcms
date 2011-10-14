<? CForm::begin($_table); ?>
	<div class="note">
		<span class="note_title">Warning!</span> Fields marked with <strong>bold</strong> are obligatory
	</div>	

	<? if($id): ?>
		<h2>Image: <? echo $title; ?></h2>
	<? else: ?>
		<h2>New Image</h2>
	<? endif; ?>
	
	<div class="col">
		<div class="row">
			<label for="title"><strong><? CTemplate::loc_string('title'); ?>:</strong></label>
			<? CTemplate::input('text', 'title', 'title', 'inp'); ?>
		</div>
		<div class="row">
			<label for="system_key"><strong><? CTemplate::loc_string('system_key'); ?>:</strong></label>
			<? if($id): ?>
				<? CTemplate::input('text', 'system_key', 'system_key', 'inp', true); ?>
			<? else: ?>
				<? CTemplate::input('text', 'system_key', 'system_key', 'inp'); ?>
			<? endif; ?>
		</div>
		<div class="row">
			<label for="path"><strong><? CTemplate::loc_string('path'); ?>:</strong></label>
			<? if($id): ?>
				<? CTemplate::input('text', 'path', 'path', 'inp', true); ?>
			<? else: ?>
				<? CTemplate::input('text', 'path', 'path', 'inp'); ?>
			<? endif; ?>
			<br /><i>e.g. pub/user/{user_id}/avatar/</i>
		</div>
		<div class="buttons">
			<div class="inpwrapper"><? CTemplate::submit('save', 'save', CTemplate::get_loc_string('btn_save'), 'butt'); ?></div>
			<div class="inpwrapper"><? CTemplate::reset('reset', 'reset', CTemplate::get_loc_string('btn_reset'), 'butt'); ?></div>
			<div class="inpwrapper"><? CTemplate::button('close', 'close', CTemplate::get_loc_string('btn_close'), 'butt'); ?></div>
		</div>
	</div>

<? if($id): ?>
	<? CControl::process('AdminFilters', 'image_sizes'); ?>
	<div class="top_buttons">
		<? if($remove_btn_show): ?>
		<div class="inpwrapper"><? CTemplate::button('del_sel_obj_top', 'delete_selected_objects', CTemplate::get_loc_string('delete_selected'), 'butt'); ?></div>
		<? endif; ?>
		<div class="inpwrapper"><? CTemplate::button('add_top', 'add_image_size', CTemplate::get_loc_string('add'), 'butt'); ?></div>
	</div>
	
	<? CControl::process('DBNavigator', 'image_size'); ?>
	
	<div class="bottom_buttons">
		<? if($remove_btn_show): ?>
		<div class="inpwrapper"><? CTemplate::button('del_sel_obj_bot', 'delete_selected_objects', CTemplate::get_loc_string('delete_selected'), 'butt'); ?></div>
		<? endif; ?>
		<div class="inpwrapper"><? CTemplate::button('add_bot', 'add_image_size', CTemplate::get_loc_string('add'), 'butt'); ?></div>
	</div>
<? endif; ?>
<? CForm::end(); ?>
