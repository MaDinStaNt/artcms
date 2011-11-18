<? CForm::begin($_table); ?>
<? CControl::process('AdminFilters', 'user'); ?>
<div class="top_buttons">
	<? if($remove_btn_show): ?>
	<div class="inpwrapper"><? CTemplate::button('del_sel_obj_top', 'delete_selected_objects', CTemplate::get_loc_string('delete_selected'), 'butt'); ?></div>
	<? endif; ?>
	<div class="inpwrapper"><? CTemplate::button('add_top', 'add', CTemplate::get_loc_string('add'), 'butt'); ?></div>
</div>

<? CControl::process('DBNavigator', 'users'); ?>

<div class="bottom_buttons">
	<? if($remove_btn_show): ?>
	<div class="inpwrapper"><? CTemplate::button('del_sel_obj_bot', 'delete_selected_objects', CTemplate::get_loc_string('delete_selected'), 'butt'); ?></div>
	<? endif; ?>
	<div class="inpwrapper"><? CTemplate::button('add_bot', 'add', CTemplate::get_loc_string('add'), 'butt'); ?></div>
</div>

<? CControl::process('DBNavigator', 'pos'); ?>

<? CForm::end(); ?>