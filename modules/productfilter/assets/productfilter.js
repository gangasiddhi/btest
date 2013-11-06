/*
 * This is code for the filter products based on the products attributes (shoeSize, colors etc ) selected the by customer.
 */
var filterProducts = {
    _options : {
        shoeSizes : new Array(),
        colors : new Array()
    },

    _init:function(){
        filterProducts.uncheckThePreviouslySelectedCheckbox();
        filterProducts._getShoeSizesToFilter();
       /* filterProducts._getColorsToFilter();*/
        filterProducts.filterProductsByShoesizesOrColors();
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
        filterProducts._options.shoeSizes = new Array();
        filterProducts._options.colors = new Array();

        if(getCookie('shoeSize') || getCookie('color')){

            var shoeSizes = getCookie('shoeSize').split(',');
            for(var i=0 ; i < shoeSizes.length ; i++){
                filterProducts._options.shoeSizes[i]= shoeSizes[i];
            }

           /* var color = getCookie('color').split(',');
            for(var j=0 ; j < color.length ; j++){
                filterProducts._options.colors[j]= color[j];
            }
            */
            filterProducts.filterProductsByShoesizesOrColors();
        }
    },
    /*To get the shoe sizes selected by the customer*/
    _getShoeSizesToFilter : function(){
                                var i = 0;
                                filterProducts._options.shoeSizes = new Array();
                                $('.shoe-size-checkbox').each(function(){
                                    if($(this).prop('checked').valueOf() === true){
                                        if($(this).val() != 'all') {
                                            filterProducts._options.shoeSizes[i] = $(this).val();
                                            i++;
                                        }
                                    }
                                    setCookie('shoeSize',filterProducts._options.shoeSizes);
                                });
        },

     /*To get the colors selected by the customer*/
     _getColorsToFilter : function(){
                            var j = 0;
                            filterProducts._options.colors = new Array();
                            $('.color-checkbox').each(function(){
                                if($(this).prop('checked').valueOf() === true){
                                    filterProducts._options.colors[j] = $(this).prop('value').valueOf();
                                    j++;
                                }
                                setCookie('color',filterProducts._options.colors);
                            });
        },

     /*Filter the products based on the shoe size and colors*/
     filterProductsByShoesizesOrColors:function(){
                                        var url = baseDir+'modules/productfilter/filter.php';
                                        var data = {categoryId : categoryId,
                                                    shoeSizes : filterProducts._options.shoeSizes,
                                                    colors : filterProducts._options.colors};
                                        var jsfile = new Array(/*baseDir+'js/jquery/jquery.lazyloader.js',
                                                               baseDir+'themes/butigo/js/quick-view.js',*/
                                                               baseDir+'themes/butigo/js/category.js' );

                                        $.ajax({
                                                type : 'GET',
                                                url : url,
                                                async : true,
                                                cache : false,
                                                dataType : 'html',
                                                data : data,
                                                beforeSend: function(){
                                                   $('.shoe-size-checkbox').each(function(){
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
                                                    $('.shoe-size-checkbox').each(function(){
                                                        $(this).removeAttr('disabled');
                                                    });
                                                }
                                        });

        },

    /*This is only for Testing printing at the console*/
    _printForTesting : function(){
                        for(var k = 0; k < filterProducts._options.shoeSizes.length ; k++){
                            console.log(filterProducts._options.shoeSizes[k]);
                        }
                        for(var k = 0; k < filterProducts._options.colors.length ; k++){
                            console.log(filterProducts._options.colors[k]);
                        }
    }
}


$(document).ready(function() {

    /*DONT DELETE THIS CODE*/
   /*$('#filter-option-shoesize').click(function(){
       if($('.shoe-size-list').hasClass('hidden')){
           $('.shoe-size-list').removeClass('hidden');
       }else{
           $('.shoe-size-list').addClass('hidden');
       }
   });
   $('#filter-option-color').click(function(){
       if($('.color-list').hasClass('hidden')){
           $('.color-list').removeClass('hidden');
       }else{
           $('.color-list').addClass('hidden');
       }
   });*/

    $('.shoe-size-checkbox, .color-checkbox').click(function(){
        filterProducts._init();
    });
});


$(window).ready(function(){
    filterProducts._previousSelectedOptions();
});

