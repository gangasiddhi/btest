function shoes_more_initCallback(carousel)
{
    // Disable autoscrolling if the user clicks the prev or next button.
    carousel.buttonNext.bind('click', function() {
        carousel.startAuto(0);
    });
    carousel.buttonPrev.bind('click', function() {
        carousel.startAuto(0);
    });
    // Pause autoscrolling if the user moves with the cursor over the clip.
    carousel.clip.hover(function() {
        carousel.stopAuto();
    }, function() {
        carousel.startAuto();
    });
	//$("#mycarousel li").css("display","block");
}
jQuery(document).ready(function() {
    jQuery('#shoes-more').jcarousel({
        auto: 3,
        wrap: 'last',
        initCallback: shoes_more_initCallback
    });
});