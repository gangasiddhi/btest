function isScrolledIntoView() {
    var docViewTop = $(window).scrollTop();
    var docViewBottom = docViewTop + $(window).height();
    var elemTop = $("#sh_hidden").offset().top;
    var elemBottom = elemTop + $("sh_hidden").height();

    return ((elemBottom >= docViewTop) && (elemTop <= docViewBottom));
}

$(document).ready(function() {
	$(window).scroll(function() {
		var docViewTop = $(window).scrollTop();
		var docViewBottom = docViewTop + $(window).height();
		var elemTop = $("#sh_hidden").offset().top;
		var elemBottom = elemTop + $("#more_shoes_products").height();
		if (elemBottom <= docViewTop) {
			$("#more_shoes_products").css({height:"240px",width:"1008px"});
			$("#more_shoes_products").show();
		}
	});
});
