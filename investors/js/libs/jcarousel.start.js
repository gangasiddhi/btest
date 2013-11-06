function how_carousel_initCallback(carousel) {
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
  	jQuery('#how-slide').jcarousel({
        initCallback: how_carousel_initCallback
    });
});