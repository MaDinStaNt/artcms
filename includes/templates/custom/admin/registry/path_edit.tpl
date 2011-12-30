<? CForm::begin('registry_path'); ?>
	<div class="note">
		<span class="note_title"><? CTemplate::loc_string('warning'); ?>!</span> <? CTemplate::loc_string('fields_marked_with'); ?> <strong><? CTemplate::loc_string('bold'); ?></strong> <? CTemplate::loc_string('has_been_required'); ?>
	</div>	

	<? if($id): ?>
		<h2><? CTemplate::loc_string('path'); ?>: <? echo $title; ?></h2>
	<? else: ?>
		<h2><? CTemplate::loc_string('new_path'); ?></h2>
	<? endif; ?>
	
	<script type="text/javascript" language="javascript" src="<? echo $JS; ?>jquery/jquery.cookie.js"></script>
	<script type="text/javascript">
	$(function(){
		$('#tabs').tabs({
			cookie: { expires: 30 }
		});
	});
	</script>
	
	<div id="tabs" class="tabs">
		<ul>
			<li><a href="#tabs-1"><? CTemplate::loc_string('path'); ?></a></li>
			<? if($id): ?>
				<li><a href="#tabs-2"><? CTemplate::loc_string('path_fields'); ?></a></li>
			<? endif; ?>
		</ul>
		<div id="tabs-1">
			<div class="col">
				<div class="row">
					<label for="language_id"><strong><? CTemplate::loc_string('language_id'); ?>:</strong></label>
					<? CTemplate::input('select', 'language_id', 'language_id', 'sel'); ?>
				</div>
				<div class="row">
					<label for="parent_id"><? CTemplate::loc_string('parent_id'); ?>:</label>
					<? CTemplate::input('select', 'parent_id', 'parent_id', 'sel'); ?>
				</div>
				<div class="row">
					<label for="path"><strong><? CTemplate::loc_string('path'); ?>:</strong></label>
					<? CTemplate::input('text', 'path', 'path', 'inp'); ?>
				</div>
				<div class="row">
					<label for="title"><strong><? CTemplate::loc_string('title'); ?>:</strong></label>
					<? CTemplate::input('text', 'title', 'title', 'inp'); ?>
				</div>
				<div class="buttons">
					<div class="inpwrapper"><? CTemplate::submit('save', 'save', CTemplate::get_loc_string('btn_save'), 'butt'); ?></div>
					<div class="inpwrapper"><? CTemplate::reset('reset', 'reset', CTemplate::get_loc_string('btn_reset'), 'butt'); ?></div>
					<div class="inpwrapper"><? CTemplate::button('close', 'close', CTemplate::get_loc_string('btn_close'), 'butt'); ?></div>
				</div>
			</div>
		</div>
		<? if($id): ?>
			<div id="tabs-2">
				<? CControl::process('AdminFilters', 'registry_value'); ?>
				<div class="top_buttons">
					<? if($remove_btn_show): ?>
					<div class="inpwrapper"><? CTemplate::button('del_sel_obj_top', 'delete_selected_objects', CTemplate::get_loc_string('delete_selected'), 'butt'); ?></div>
					<? endif; ?>
					<div class="inpwrapper"><? CTemplate::button('add_top', 'add_registry_value', CTemplate::get_loc_string('add'), 'butt'); ?></div>
				</div>
				
				<? CControl::process('DBNavigator', 'registry_value'); ?>
				
				<div class="bottom_buttons">
					<? if($remove_btn_show): ?>
					<div class="inpwrapper"><? CTemplate::button('del_sel_obj_bot', 'delete_selected_objects', CTemplate::get_loc_string('delete_selected'), 'butt'); ?></div>
					<? endif; ?>
					<div class="inpwrapper"><? CTemplate::button('add_bot', 'add_registry_value', CTemplate::get_loc_string('add'), 'butt'); ?></div>
				</div>
			</div>
		<? endif; ?>
	</div>
	
<? CForm::end(); ?>

