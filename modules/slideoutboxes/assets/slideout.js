$(document).ready(function() {
	//slide-out-boxes
	$(function() {
		// the list of boxes
		var $list = $('#slide_boxes_list ul');
		// number of related boxes
		var elems_cnt = $list.children().length;
		// show the first set of boxes.
		// 200 is the initial left margin for the list elements
		load(340);

		function load(initial) {
			$list.find('li').hide().andSelf().find('div#slide-left').css('margin-left',-initial+'px');
			var loaded	= 0;
			//show 2 random boxes from all the ones in the list.
			//Make sure not to repeat
			while(loaded < 2){
				var r 		= Math.floor(Math.random()*elems_cnt);
				var $elem	= $list.find('li:nth-child('+ (r+1) +')');
				if($elem.is(':visible'))
					continue;
				else
					$elem.show();
				++loaded;
			}
			//animate them
			var d = 200;
			$list.find('li:visible div#slide-left').each(function() {
				$(this).stop().animate({
					'marginLeft':'40px'
				},d += 100);
			});
		}

		//hovering over the list elements makes them slide out
		$list.find('li:visible').on('mouseenter', function() {
			$(this).find('div#slide-left').stop().animate({
				'marginLeft':'238px'
			},200);
		}).on('mouseleave',function () {
			$(this).find('div#slide-left').stop().animate({
				'marginLeft':'40px'
			},200);
		});
	});
});
