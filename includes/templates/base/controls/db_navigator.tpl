<div class="nav">
	<? if($navigator_is_empty): ?>
		<div class="heading">
			<span class="ttl"><? echo $title; ?></span> <span class="stt">(<? CTemplate::loc_string('nav_empty_message'); ?>)</span>
			<div class="clear">&nbsp;</div>
		</div>
	<? else: ?>
		<div class="heading">
			<span class="ttl"><? echo $title; ?></span> <span class="stt">(<? echo $total_items; ?>  <? CTemplate::loc_string('total'); ?>, <? CTemplate::loc_string('displaying'); ?> <? echo $row_numb_before; ?><? if($size > 1): ?> <? CTemplate::loc_string('to'); ?> <? echo $row_numb_end; ?><? endif; ?>)</span>
			<? CControl::process('Paginator', $object_id.'_top'); ?>
			<div class="clear">&nbsp;</div>
		</div>
							
		<div class="nav_body">
			<?if (!$navigator_is_empty): ?>
			<script type="text/javascript">
			$(function(){
				$('.nav_t_r:odd').addClass('nav_t_r2');
				$('.nav_t_r:last-child').addClass('last');
				$('.nav_t_r').hover(
					function(){
						$(this).addClass('nav_t_r_a hand');
					},
					function(){
						$(this).removeClass('nav_t_r_a hand');
					}
				);
				$(".check").click(function(){
					obj = $('.checked[name="<? echo $_table; ?>_res"]');
					if($(this).attr('name') == '<? echo $_table; ?>_chAll'){
						if($(this).is(':checked'))
						{
							new_val = '';
							$('.check[name="<? echo $_table; ?>_ch"]').each(function(i){
								if(new_val == '') new_val = $(this).val();
								else new_val += ','+$(this).val();
								$(this).attr('checked', true);
							});
							obj.val(new_val);
						}
						else
						{
							$('.check[name="<? echo $_table; ?>_ch"]').each(function(i){
								$(this).attr('checked', false);
							});
							obj.val('[]');
						}
					}
					else{
						old_val = obj.val();
						val = $(this).val();
						if($(this).is(":checked")){
							if($('.check[name="<? echo $_table; ?>_ch"]').not(':checked').length == 0) $('.check[name="<? echo $_table; ?>_chAll"]').attr('checked', true);
							if(old_val === '[]'){
								obj.val(val);
								return true;
							}
							obj.val(old_val +','+ val);
							return true;
						}
						else{
							$('.check[name="<? echo $_table; ?>_chAll"]').attr('checked', false);
							if(old_val !== '[]'){
								new_val = old_val.split(",");
								
								for(i = 0; i < new_val.length;i++){
									if(new_val[i] == val){
										new_val.splice(i, 1);
									}
								}
								new_val = new_val.join(",");
								if(new_val == ''){
									new_val = '[]';
								}
								obj.val(new_val);
							}
							return true;
						}
					}
				});
			});
			</script>
			<table class="nav_t maxw" cellpadding="0" cellspacing="0" border="0">
			<tbody><tr class="nav_t_ttl">
				<? foreach ($headers as $field => $data): ?>
					<? if($field == 'id'): ?>
						<td class="fld-check minw t_cl1_ttl left" width="10">
							<input type="checkbox" class="check" name="<? echo $_table; ?>_chAll" />
							<input type="hidden" class="checked" name="<? echo $_table; ?>_res" value="[]" />
						</td>
					<? else: ?>
						<td width="<? echo $data['width']; ?>" <? if(!$data['wrap']): ?>nowrap="nowrap"<? endif; ?> align="<? echo $data['align']; ?>" valign="<? echo $data['valign']; ?>" class="t_cl1_ttl <? if($data['sort_act']): ?>sort_<? echo $data['sort_act']; ?><?endif;?>">
							<? if($data['is_sort']): ?>
								<a href="<? echo $data['sort_link']; ?>" title="<? CTemplate::loc_string('click_for_sort'); ?>"><? echo $data['title']; ?></a>
							<? else: ?>
								<span class="nav_header"><? echo $data['title']; ?></span>
							<? endif; ?>
						</td>
					<? endif; ?>
				<? endforeach; ?>
				</tr>
				<? foreach ($rows as $row): ?>
					<tr class="nav_t_r nav_t_r1 nav_row">
					<? foreach ($headers as $field => $data): ?>
						<? if($field == 'id'): ?>
						<td class="fld-check left">
							<input type="checkbox" class="check" name="<? echo $_table; ?>_ch" value="<? echo $row[$field]; ?>" />
						</td>
						<? else: ?>
							<? if($data['clickable'] && $is_checkable): ?>
								<td valign="<? echo $data['valign']; ?>" align="<? echo $data['align']; ?>" class="hand nav_row" onclick="gotoURL('<? echo $data['clckaction']; ?><? echo $row['id']; ?>');" <? if(!$data['wrap']): ?>nowrap="nowrap"<? endif; ?>>
							<? else: ?>
								<td valign="<? echo $data['valign']; ?>" align="<? echo $data['align']; ?>" class="hand nav_row" nowrap="<? echo $data['wrap']; ?>">
							<? endif;?>
							<? if($data['mail']): ?>
								<a href="mailto:<? echo $row[$field]; ?>" title="<? CTemplate::loc_string('click_for_send_message'); ?>"><? echo $row[$field]; ?></a>
							<? else: ?>
								<? echo $row[$field]; ?>
							<? endif; ?> 
							</td>
						<? endif; ?>
					<? endforeach; ?>
					</tr>
				<? endforeach; ?>
			</tbody></table>
			<? endif; ?>
		</div>
		<div class="foot">
			<? CControl::process('Paginator', $object_id.'_bottom'); ?>
		</div>
	<? endif; ?>
</div>
