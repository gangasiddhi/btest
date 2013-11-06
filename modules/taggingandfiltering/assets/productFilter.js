/*
 * This is code for the filter products based on the products attributes (shoeSize, colors etc ) selected the by customer.
 */
var filterTagProducts = {
    _options : {
        shoeSizes : new Array(),
        tags : new Array()
    },

    _initted : false,

    _init:function(){
         //filterTagProducts.uncheckThePreviouslySelectedCheckbox();
         filterTagProducts._getShoeSizesTagsToFilter();
         filterTagProducts._filterProducts();
    },
    uncheckThePreviouslySelectedCheckbox:function(){
        $('.shoe-size-checkbox').each(function(){
                $(this).removeAttr('checked');
        });

       $('.shoe-size-checkbox').each(function(){
            if(!$(this).hasClass('checkbox-selected')){
                $(this).removeAttr('checked');
            }else{
                shoeId = $(this).prop('value');
                $('#'+shoeId).addClass('selected');
                document.getElementById(shoeId).checked = true;
            }
        });

        $('.shoe-size-checkbox').each(function(){
            if($(this).hasClass('checkbox-selected')){
                $(this).removeClass('checkbox-selected');
            }
        });
    },
    _previousSelectedOptions:function(){
        filterTagProducts._options.shoeSizes = new Array();

        if(getCookie('shoeSize')){

            var shoeSizes = getCookie('shoeSize').split(',');
            for(var i=0 ; i < shoeSizes.length ; i++){
                 filterTagProducts._options.shoeSizes[i]= shoeSizes[i];
            }
             filterTagProducts._filterProducts();
        }
        $('.shoe-size-checkbox').removeAttr('checked');
        $('.multipleClick').removeAttr('checked');
        $('.singleClick').removeAttr('checked');
    },
    /*To get the shoe sizes selected by the customer*/
    _getShoeSizesTagsToFilter : function(){
                                var i = 0;
                                var allShoeProducts = false;
                                filterTagProducts._options.shoeSizes = new Array();
                                filterTagProducts._options.tags = new Array();

                                $('.multipleClick').each(function() {
                                    if($(this).prop('checked').valueOf() === true){
                                        filterTagProducts._options.tags[i] = $(this).val();
                                        i++;
                                    }
                                });

                                $('.singleClick').each(function() {
                                    if($(this).prop('checked').valueOf() === true) {
                                        filterTagProducts._options.tags[i] = $(this).val();
                                        i++;
                                    }
                                });

                                i = 0;
                                $('.shoe-size-checkbox').each(function() {
                                    if($(this).prop('checked').valueOf() === true){
                                        if($(this).val() != 'all') {
                                            filterTagProducts._options.shoeSizes[i] = $(this).val();
                                            //setCookie('shoeSize', $(this).val());
                                        }
                                        else
                                        {
                                            allShoeProducts = true;
                                           // setCookie('shoeSize', '');
                                        }
                                    }
                                    if(allShoeProducts)
                                    {
                                        if(filterTagProducts._options.tags.length > 0)
                                        {
                                            if($(this).val() != 'all') {
                                                filterTagProducts._options.shoeSizes[i] = $(this).val();
                                                i++;
                                            }
                                        }
                                     }
                                });
        },
        /*Filter the products based on the shoe size and colors*/
       _filterProducts:function(){
           //dont run on first load
           if (!filterTagProducts._initted) {
               filterTagProducts._initted = true;
               return false;
           }



                                        var url = baseDir+'modules/taggingandfiltering/sizeTagsFilter.php';
                                        var data = {categoryId : categoryId,
                                                    shoeSizes :  filterTagProducts._options.shoeSizes,
                                                    tags : filterTagProducts._options.tags
                                                    };
                                        var jsfile = new Array(baseDir+'themes/butigo/js/category.js' );

                                        $.ajax({
                                                type : 'GET',
                                                url : url,
                                                async : true,
                                                cache : false,
                                                dataType : 'html',
                                                data : data,
                                                beforeSend: function(){
                                                   $('.checkFilter').each(function(){
                                                        $(this).prop('disabled','true');
                                                    });
                                                    $('#product_list').html("<p style='color:#fff;'>loading</p>");
                                                    $('#product_list').addClass("loading");
                                                },
                                                success : function(res) {
                                                    $('#product_list').html(res);

                                                    for(var i = 0; i < jsfile.length ; i++){
                                                        $.ajax({
                                                                type: "GET",
                                                                url: jsfile[i],
                                                                dataType: "script"
                                                        });
                                                    }
                                                },
                                                complete:function(){
                                                    $('#product_list').removeClass("loading");
                                                    $('.checkFilter').each(function(){
                                                        $(this).removeAttr('disabled');
                                                    });
                                                }
                                        });

        },

    /*This is only for Testing printing at the console*/
    _printForTesting : function(){
                        for(var k = 0; k <  filterTagProducts._options.shoeSizes.length ; k++){
                            console.log( filterTagProducts._options.shoeSizes[k]);
                        }
    }
}

$(document).ready(function() {
     $('.multipleClick').removeAttr('checked');
     $('.singleClick').removeAttr('checked');
     $('.checkFilter').click(function(){
         filterTagProducts._init();
     });
	 $('.show-more-tags').click(function(){
		 $(this).addClass('hidden');
		 var filter = $(this).attr('filterTag');
		 $('.'+filter).each(function(){
			 if($(this).hasClass('hidden')){
				 $(this).removeClass('hidden');
			 }
		 });
	 });
     filterTagProducts._init();
});
