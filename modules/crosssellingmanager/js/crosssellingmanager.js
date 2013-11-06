$(document).ready(function() {	
	$("#cross-selling-manager-carousel").carouFredSel({
		auto : {
			play: false
		},
		scroll : {
			easing : "easeOutSine",
			duration: 1000
		},
		items: {
			visible: 4,
			height: 250
		},
		height: 260,
		direction: "left",
		prev : {
			button  : "#carnav-prev"
		},
		next : {
			button  : "#carnav-next"
		},
		pagination  : "#carnav-pag"
	});	
});