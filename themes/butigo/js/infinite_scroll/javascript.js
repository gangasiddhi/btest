(function($) {

	$.fn.scrollPagination = function(options) {
		var settings = {
			nop     : 10, // The number of posts per scroll to be loaded
			offset  : 10, // Initial offset, begins at 0 in this case
			error   : 'No More Posts!', // When the user reaches the end this is the message that is
			                            // displayed. You can change this if you want.
			delay   : 1500, // When you scroll down the posts will load after a delayed amount of time.
			               // This is mainly for usability concerns. You can alter this as you see fit
			scroll  : true, // The main bit, if set to false posts will not load as the user scrolls.
			               // but will still load if the user clicks.
			head_foot : 1, //this is mainly used to because not to repeat the header and footer of the page.
			path : options
		}

		// Extend the options so they work with the plugin
		if(options) {
			$.extend(settings, options);
		}

		// For each so that we keep chainability.
		return this.each(function() {
			// Some variables

			$this = $(this);
			$settings = settings;
			var offset = $settings.offset;
			var head_foot = $settings.head_foot;
			var busy = false; // Checks if the scroll action is happening
			                  // so we don't run it multiple times

			// Custom messages based on settings
			if($settings.scroll == true){
				$initmessage = '';
			}
			else{
				$initmessage = '';
			}

			// Append custom messages and extra UI
			$this.append('<div class="content"></div><div class="loading" style=margin-left:450px></div>');

			function getData() {
				// Post data to ajax.php
				var filepath = baseDir+$settings.path;
				$.post(filepath, {

					action        : 'scrollpagination',
				    number        : $settings.nop,
				    offset        : offset,
					head_foot     : head_foot,

				}, function(data) {
					// Change loading bar content (it may have been altered)
					$this.find('.loading').html($initmessage);

					// If there is no data returned, there are no more posts to be shown. Show error
					if($.trim(data).length == 0 ) {

					}
					else {

						// Offset increases
					    offset = offset+$settings.nop;
						head_foot = 1;

						// Append the data to the content div
					   	$this.find('.content').append(data);

						// No longer busy!
						busy = false;
					}

				});

			}

			getData(); // Run function initially

			// If scrolling is enabled
			if($settings.scroll == true) {
				// .. and the user is scrolling
				$(window).scroll(function() {

					// Check the user is at the bottom of the element
					if($(window).scrollTop() + $(window).height() > $this.height() && !busy) {

						// Now we are working, so busy is true
						busy = true;
						// Tell the user we're loading posts
						$this.find('.loading').html('<img src="'+baseDir+'img/infinite_scroll/infiniteloading.gif" alt="loading"/>');

						// Run the function to fetch the data inside a delay
						// This is useful if you have content in a footer you
						// want the user to see.
						setTimeout(function() {

							getData();

						}, $settings.delay);

					}
					$this.find('.showroom_sel_cntnr').lazyloader('a.product_image img');
				});
			}

			// Also content can be loaded by clicking the loading bar/
			$this.find('.loading').click(function() {

				if(busy == false) {
					busy = true;
					getData();
				}

			});

		});
	}

})(jQuery);
