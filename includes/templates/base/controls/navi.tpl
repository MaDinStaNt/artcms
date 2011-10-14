<? if(!$navi_is_empty): ?>
	<script type="text/javascript">
	$(function(){
		$("#left_nav .section:first").addClass('top');
		$("#left_nav .section, #left_nav .subsection, #left_nav .branch li").hover(function(){$(this).addClass('hover')}, function(){$(this).removeClass('hover')});
		$("#left_nav .section .pm").click(function(){
			if($(this).parent('div.section').hasClass('expand'))
				$(this).parent('div.section').removeClass('expand')
			else
				$(this).parent('div.section').addClass('expand')
				
			return false;
		});
	});

	</script>
	<div class="left_block">
		<? foreach ($navi as $value): ?>
			<? if($value['is_act']): ?>
				<div class="section expand act">
			<? else: ?>
				<div class="section">
			<? endif; ?>
				<a href="#" class="pm">&nbsp;</a>
				<a href="<? echo $value['link']; ?>" class="title"><span class="h">&nbsp;</span><span class="b"><? echo $value['name']; ?></span><span class="f">&nbsp;</span></a>
			
				<? if(!empty($value['children'])): ?>
					<ul class="tree">
						<li class="first">&nbsp;</li>
						<? foreach ($value['children'] as $s_child): ?>
							<li class="subsection <? if($s_child['is_act']): ?> act <? endif; ?>"><a href="<? echo $s_child['link']; ?>" class="owner"><? echo $s_child['name']; ?></a>
								<? if(!empty($s_child['children'])): ?>
									<ul class="branch">
										<? foreach ($s_child['children'] as $gs_child): ?>
											<li class="<? if($gs_child['is_act']): ?> act <? endif; ?>"><a href="<? echo $gs_child['link']; ?>"><? echo $gs_child['name']; ?></a></li>
										<? endforeach; ?>
									</ul>
								<? endif; ?>
							</li>
						<? endforeach; ?>
						<li class="f">&nbsp;</li>
					</ul>
				<? endif; ?>
			</div>
		<? endforeach; ?>
	</div>
<? else: ?>

<? endif; ?>