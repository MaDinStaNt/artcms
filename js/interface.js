function gotoURL(url){
	if (!url) url = "/";
	if (window.event){
		var src = window.event.srcElement;
		if((src.tagName != 'A') && ((src.tagName != 'IMG') || (src.parentElement.tagName != 'A'))){
			if (window.event.shiftKey) window.open(url);
			else document.location = url;
		}
	} else document.location = url;
}
$.fn.swapPosition = function(table, id_field, pos_field, type) {
	if(!table) return false;
    var id_field = id_field || "id";
    var pos_field = pos_field || "position";
    var type = type || "fx";
    return this.queue(type, function() {
		var object_id = $(this).attr('id');
		var body = $('#'+ object_id +' .sortable_body');
		var form = $('#'+ object_id +' .sortable_form');
		var select = $('#'+ object_id + ' .sortable_select');
		var swapbutt = $('#'+ object_id + ' a.swappos');
		var swapinp = $('#'+ object_id + ' input.sortable_inp');
		var inp_start = $('#'+ object_id + ' input.pos_start');
		var inp_end = $('#'+ object_id + ' input.pos_end');
		var butt_filter = $('#'+ object_id + ' input.pos_filter');
		var butt_clear = $('#'+ object_id + ' input.pos_clear');
		var have_sort = false;
		var sort_field = false;
		var sort_value = false;
		var position_list = false;
		var max_position = 0;
		var max_index = 0;
		var field_cond = false;
		
		if(body.height() > 510)
			body.addClass('scroll');
			
		//check and bind select
		if(select.length > 0)
		{
			have_sort = true;
			position_list = body.children('div.row['+ select.attr('name') +'='+ select.val() +']');
			field_cond = select.attr('name');
			select.change(function(){
				body.children('div.row').removeClass('showed filtered');
				position_list = body.children('div.row['+ $(this).attr('name') +'='+ $(this).val() +']');
				max_position = position_list.length;
				max_index = max_position - 1;
				if(max_position < 2){
					$(inp_start).parent('div.field').hide();
					$(inp_end).parent('div.field').hide();
					$(butt_filter).parents('div.field').hide();
				}
				else{
					$(inp_start).parent('div.field').show();
					$(inp_end).parent('div.field').show();
					$(butt_filter).parents('div.field').show();
				}
				inp_start.val(1);
				inp_end.val(max_position);
				position_list.addClass('showed filtered').eq(0).addClass('first');
				$(position_list).eq(max_index).addClass('last');
				butt_filter.click();
			});
		}
		else
			position_list = body.children('div.row');
		
		//bind buttons action
		butt_filter.click(function(){
			var pos_list = body.children('div.row.filtered')
			var pos_start = inp_start.val();
			var pos_end = inp_end.val();
			if(pos_start < 1 || pos_start > pos_end){ pos_start = 1; inp_start.val(1); }
			if(pos_end > max_position || pos_end < 1){ pos_end = max_position; inp_end.val(pos_list.length); }
			
			pos_list.removeClass('showed');
			for(i = pos_start; i <= pos_end; i++)
				pos_list.eq(i - 1).addClass('showed');
			
			var showed_pos = body.children('div.row.showed');
			showed_pos.removeClass('first').eq(0).addClass('first');
			showed_pos.removeClass('last').eq(showed_pos.length - 1).addClass('last');
			if(showed_pos.length >= 15)
				body.addClass('scroll');
			else
				body.removeClass('scroll');
			
			return false;
		});
		butt_clear.click(function(){
			var pos_list = body.children('div.row.filtered')
			inp_start.val(1);
			inp_end.val(pos_list.length);
			butt_filter.click();
			return false;
		});
		swapbutt.live('click', function(){
			var showed_pos = body.children('div.row.showed');
			var pos_list = body.children('div.row.filtered');
			var row = $(this).parents('div.row.showed');
			var index = row.index('#'+ object_id +' div.filtered');
			var pos = row.attr('position');
			var pos_inp = parseInt(row.find('div.control .inp').val());
			var swap_row = pos_list.eq(pos_inp - 1);
			var parametres = [table, row.attr(id_field), swap_row.attr(pos_field), id_field, pos_field, field_cond, row.attr(field_cond)];
			
			if((index + 1) == pos && ( $(this).hasClass('interchange') || pos == pos_inp ))
			{
				if($(this).hasClass('top') && pos > 1)
				{
					swap_row = showed_pos.eq(index - 1);
					swap_row.before( row );
				}
				else if($(this).hasClass('bottom') && pos < max_position)
				{
					swap_row = showed_pos.eq(index + 1);
					swap_row.after( row );
				}
				else if($(this).hasClass('interchange'))
				{
					swap_row.after( row );
					
					if(pos_inp < pos){
						body.children('div.row.filtered:eq('+ index +')').after( swap_row );
					}
					else{
						body.children('div.row.filtered:eq('+ index +')').before( swap_row );
					}
				}
				else
				{
					call('Inputs', 'rebuild_positions', [table, id_field, pos_field, field_cond]);
					pos_list = body.children('div.row.filtered');
					pos_list.each(function(){
						var position = $(this).index('#'+ object_id +' div.filtered') + 1;
						$(this).attr('position', position);
						$(this).find('div.control input.inp').val(position);
					});
					butt_filter.click();
					return false;
				}
				
				parametres[2] = swap_row.attr(id_field);
				call('Inputs', 'swap_positions', parametres);
			}
			else if( pos_inp >= 1 && pos_inp <= max_position && (index + 1) == pos )
			{
				if( pos > pos_inp )
				{
					swap_row.before( row );
				}
				else
				{
					swap_row.after( row );
				}
				
				call('Inputs', 'move_into_pos', parametres);
			}
			else
			{
				pos_list = body.children('div.row.filtered');
				pos_list.each(function(){
					var position = $(this).index('#'+ object_id +' div.filtered') + 1;
					$(this).attr('position', position);
					$(this).find('div.control input.inp').val(position);
				});
				butt_filter.click();
				call('Inputs', 'rebuild_positions', [table, id_field, pos_field, field_cond]);
				return false;
			}
			
			
			pos_list = body.children('div.row.filtered');
			pos_list.each(function(){
				var position = $(this).index('#'+ object_id +' div.filtered') + 1;
				$(this).attr('position', position);
				$(this).find('div.control input.inp').val(position);
			});
			butt_filter.click();
			return false;
		});
		
		max_position = position_list.length;
		max_index = max_position - 1;
		inp_start.val(1);
		inp_end.val(max_position);
		
		position_list.addClass('showed filtered').eq(0).addClass('first');
		$(position_list).eq(max_index).addClass('last');
		
		if(max_position < 2){
			$(inp_start).parent('div.field').hide();
			$(inp_end).parent('div.field').hide();
			$(butt_filter).parents('div.field').hide();
		}
		else if(max_position > 5)
		{
			inp_end.val(5);
			butt_filter.click();
		}
		
		//bind sortable
		body.sortable({
			items: "div.row:not(.empty)",
			update: function(event, ui) { 
				var pos_list = body.children('div.row.filtered');
				pos_list.each(function(){
					var position = $(this).index('#'+ object_id +' div.filtered') + 1;
					$(this).attr('position', position);
					$(this).find('div.control input.inp').val(position);
				});
				call('Inputs', 'move_into_pos', [table, ui.item.attr(id_field), (ui.item.index('#'+ object_id +' div.filtered') + 1), id_field, pos_field, field_cond, ui.item.attr(field_cond)]);
				butt_filter.click();
			}
		});

		//bind key press on "Enter"
		swapinp.live('keyup', function(event){
			if(event.keyCode == 13)
			{
				var showed_pos = body.children('div.row.showed');
				var pos_list = body.children('div.row.filtered');
				var row = $(this).parents('div.row.showed');
				var index = row.index('#'+ object_id +' div.filtered');
				var pos = row.attr('position');
				var pos_inp = $(this).val();
				var swap_row = pos_list.eq(pos_inp - 1);
				if( pos !== pos_inp && pos_inp >= 1 && pos_inp <= max_position )
				{
					if( pos > pos_inp )
						swap_row.before( row );
					else
						swap_row.after( row );
					call('Inputs', 'move_into_pos', [table, row.attr(id_field), swap_row.attr(pos_field), id_field, pos_field, field_cond, row.attr(field_cond)]);
				}
				else
				{
					call('Inputs', 'rebuild_positions', [table, id_field, pos_field, field_cond]);
					$(this).val(pos);
					return false;
				}
				pos_list = body.children('div.row.filtered')
				pos_list.each(function(){
					var position = $(this).index('#'+ object_id +' div.filtered') + 1;
					$(this).attr('position', position);
					$(this).find('div.control input.inp').val(position);
				});
				butt_filter.click();
			}
			else
				event.preventDefault();
		});
		
    	$(this).dequeue();
    });
};