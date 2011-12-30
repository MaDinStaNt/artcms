<? CForm::begin('registry', 'POST', '', 'multipart/form-data'); ?>
<? CTemplate::input('hidden', 'registry_language_id', 'registry_language_id'); ?>
<script type="text/javascript">
$(function(){
	$('.marker').click(function(){
		if($(this).hasClass('expand'))
		{
			$(this).removeClass('expand');
			$(this).parent('li.expand').removeClass('expand').children('ul.branch').hide();
		}
		else
		{
			$(this).addClass('expand');
			$(this).parent('li').addClass('expand').children('ul.branch').show();
		}
	});
	$('.branch li .marker, .branch li a').hover(function(){$(this).parent('li').children('a.edit, a.delete').css('display', 'inline-block')}, function(){$(this).parent('li').children('a.edit, a.delete').hide()});
	$('.branch li:last-child').addClass('last');
	$('.branch .active').parents('li').each(function(i){
		$(this).addClass('expand');
		$(this).children('.marker').addClass('expand');
		$(this).children('ul.branch').show();
	});
	if($(window).width() > 1024)
		$('.registry_path').css('border-left', '1px solid #B8B8B8');
	else
		$('.registry_path').css('border-top', '1px solid #B8B8B8');
});
function delete_rp(id)
{
	call('Inputs', 'delete_rp', [id]).listen(function(res){
		if(res.response == false)
		{
			alert(res.errors);
			return false;
		}
		document.location = '';
	});
}
</script>
<div class="col left_block" style="margin-right:35px;">
	<div class="tree">
		<h4><? echo $SITE_NAME; ?></h4>
		<div class="home">&nbsp;</div>
		<? echo $tree; ?>
	</div>
</div>
<? if($path_id): ?>
<div class="col registry_path left_block">
	<? CSimpleArrayOutput::create_html('reg_info', '<div class="info"><ul>'); ?>
	<? CSimpleArrayOutput::create_html('reg_errors', '<div class="errors"><ul>'); ?>
	<? foreach ($values_arr as $value): ?>
			<? if(intval($value['type']) == KEY_TYPE_IMAGE && strlen(${$value['path']}) > 0): ?>
				<div class="row">
					<div style="display:inline-block;width:103px;">&nbsp;</div>
					<img src="<? echo $HTTP; ?><? echo $registry_path; ?><? echo ${$value['path']}; ?>" width="200" title="<? echo $value['title']; ?>" />
					<div>&nbsp;</div>
					<div style="display:inline-block;width:105px;">&nbsp;</div>
					<? CTemplate::input('checkbox', "delete_{$value['path']}", "delete_{$value['path']}", 'chk'); ?>
					<label for="delete_<? echo $value['path']; ?>" class="widthauto"> - <? CTemplate::loc_string('delete_image'); ?></label>
				</div>
			<? endif; ?>
			<? if(intval($value['type']) == KEY_TYPE_FILE && strlen(${$value['path']}) > 0): ?>
				<div class="row">
					<div style="display:inline-block;width:103px;">&nbsp;</div>
					<a href="<? echo $HTTP; ?><? echo $registry_path; ?><? echo ${$value['path']}; ?>" title="<? echo $value['title']; ?>"><? CTemplate::loc_string('download'); ?></a>
					<div>&nbsp;</div>
					<div style="display:inline-block;width:105px;">&nbsp;</div>
					<? CTemplate::input('checkbox', "delete_{$value['path']}", "delete_{$value['path']}", 'chk'); ?>
					<label for="delete_<? echo $value['path']; ?>" class="widthauto"> - <? CTemplate::loc_string('delete_image'); ?></label>
				</div>
			<? endif; ?>
			<div class="row">
				<label for="<? echo $value['path']; ?>"><? if($value['required']): ?><strong><? endif; ?><? echo $value['title']; ?>:<? if($value['required']): ?></strong><? endif; ?></label>
				<? switch ($value['type']): 
					case KEY_TYPE_CHECKBOX: ?>
						<? CTemplate::input('checkbox', $value['path'], $value['path'], 'chk'); ?>
					<? break; ?>
					<? case KEY_TYPE_TEXT: ?>
							<? CTemplate::input('text', $value['path'], $value['path'], 'inp'); ?>
					<? break; ?>
					<? case KEY_TYPE_DATE: ?>
						<? CTemplate::input('text', $value['path'], $value['path'], 'inp inpDate'); ?>
						<script type="text/javascript">
						$(function(){
							$( "input[name='<? echo $value['path']; ?>']" ).datepicker("setDate", "<? echo ${$value['path']}; ?>");
						});
						</script>
					<? break; ?>
					<? case KEY_TYPE_FILE: ?>
						<? CTemplate::input('file', $value['path'], $value['path'], 'inp'); ?>
					<? break; ?>
						<? case KEY_TYPE_IMAGE: ?>
						<? CTemplate::input('file', $value['path'], $value['path'], 'inp'); ?>
					<? break; ?>
					<? case KEY_TYPE_TEXTAREA: ?>
						<? CTemplate::textarea($value['path'], $value['path'], 'inp'); ?>
					<? break; ?>
					<? case KEY_TYPE_HTML: ?>
							<? CTemplate::textarea($value['path'], $value['path'], 'inp', true); ?>
					<? break; ?>
				<? endswitch; ?>
			</div>
	<? endforeach; ?>
	<? if(!$butt_hide): ?>
		<div class="bottom_buttons">
			<div class="inpwrapper"><? CTemplate::button('save_path', 'save_path', CTemplate::get_loc_string('btn_save'), 'butt'); ?></div>
			<div class="inpwrapper"><? CTemplate::reset('reset_path', 'reset_path', CTemplate::get_loc_string('btn_reset'), 'butt'); ?></div>
		</div>
	<? endif; ?>
</div>
<? endif; ?>
<div class="clear">&nbsp;</div>
<? if($is_debug_mode): ?>
	<div class="bottom_buttons">
		<div class="inpwrapper"><? CTemplate::button('add_path', 'add_path', CTemplate::get_loc_string('add'), 'butt'); ?></div>
	</div>
<? endif; ?>
<? CForm::end(); ?>