var butigoMagazine = {};

butigoMagazine.init = function ()  {
    var initted = false;
    var slider;

    var $currentPage = $(".magazineFooter .pager span em");
    var totalItemCount = $(".magazineContainer").data("totalItemCount");

    var initSlide = function (index) {
        $currentPage.html(index + 1);
        if (initted) { //do not initalize bxSlider more than once
            slider.goToSlide(index);
            return false;
        }

        initted = true;

        slider = $(".bxSliderItem").bxSlider({
            auto: false,
            pause: 5000,
            mode: "fade",
            pager: false,
            nextText: '',
            prevText: '',
            startSlide: index,
            onSliderLoad: function () {
                $(".container figure img").css("visibility", "visible");
            },
            onSlideAfter: function (x, oldIndex, index) {
                $currentPage.html(index + 1);
            }
        });
    };

    /* go to slide on thumbnail click */
    $(".thumbnails img").click(function () {
        var $this = $(this);
        var index = $this.data("index");
        $(".slideContent2").slideDown(function () {
            initSlide(index -1);
        });

        $(".slideContent1").slideUp();
    });


	$(".archiveThumbnails img").click(function () {
		var $this = $(this);
		window.location.href = baseDir+'magazine.php?mag='+$this.data("index");
	});


    $(".magazineFooter a.galleryView").click(function (e) {
		e.preventDefault();

		/* To find the url parameter */
		function getUrlVars()
		{
			var vars = [], delimiter;
			var currentpath = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
			for(var i = 0; i < currentpath.length; i++)
			{
				delimiter = currentpath[i].split('=');
				vars.push(delimiter[0]);
				vars[delimiter[0]] = delimiter[1];
			}
			return vars;
		}
		var parameter = getUrlVars()["view"];

		if(parameter == 'archive'){
			window.location.href = baseDir+'magazine.php';
		}
		else{
			$(".slideContent1").slideDown();
			$(".slideContent2").slideUp();
		}
    });

	$(".magazineFooter a.archiveView").click(function () {
        window.location.href = baseDir+'magazine.php?view=archive';
    });



    $(".magazineFooter a.next").click(function (e) {
        e.preventDefault();

        var currentPage = parseInt($currentPage.html(), 10);

        if ($(".slideContent2").is(":not(:visible)")) {
            $(".slideContent2").slideDown(function () {
                initSlide(0);
            });
            $(".slideContent1").slideUp();
        } else {
            slider.goToNextSlide();
        }
    });


    $(".magazineFooter a.prev").click(function (e) {
        e.preventDefault();
        var currentPage = parseInt($currentPage.html(), 10);

        if ($(".slideContent2").is(":not(:visible)")) {
            $(".slideContent2").slideDown(function () {
                initSlide(totalItemCount - 1);
            });
            $(".slideContent1").slideUp();
        } else {
            slider.goToPrevSlide();
        }
    });


};

$(function () {
    butigoMagazine.init();
});
