function hiw_carousel_initCallback(carousel) {
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
}
jQuery(document).ready(function() {
    jQuery('#celebrity_slide, #home-featured-slide-logged-out, #home-featured-slide-logged, #stylist_slide, #cloth_slide').jcarousel({
        auto: 10,
        initCallback: hiw_carousel_initCallback,
        scroll: 1,
        wrap: 'last'
    });

    jQuery('#accessories-slide-show').jcarousel({
        initCallback: hiw_carousel_initCallback
    });
});
