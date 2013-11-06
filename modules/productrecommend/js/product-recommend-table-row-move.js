$(document).ready(function () {
	/*To move the table row up and down*/
	$('.move').click(function() {                                                               
		var direction = $(this).attr('direction'),
		$original = $(this).closest("tr"),
		$target = direction === "up" ? $original.prev() : $original.next();

		if ( $target.length && direction === "up" ) {
			$original.insertBefore($target);
		}else if( $target.length ) {
			$original.insertAfter($target);
		}
		
		var i = 1;
		var newProductList = '';
		$('.recommend-product-value').each(function(){
			var recommendValue = $(this).val().split('_');
			var recommendValueString = recommendValue[0]+'_'+recommendValue[1]+'_'+recommendValue[2]+'_'+i;
			newProductList += recommendValueString+'|';
			if(recommendValueString){
				$(this).val(recommendValueString);
				i++;
			}				
		});
		
		productRecommendCompleteArray['productIdList'] = newProductList;
		
		/*arrangeTheTableArrows();	*/	
	});
});


