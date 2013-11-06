<?php
    //this script only workes when logged param sent
    //we can exit if this logged param not set
    if (!isset($_GET['logged']) || !$_GET['logged']) exit; 

    require(dirname(__FILE__).'/config/config.inc.php');
    require_once(dirname(__FILE__) . '/init.php');
    
    global $cookie,$smarty;
    
    $id_lang = Tools::getvalue('id_lang');
    $logged = Tools::getValue('logged');
    $prod_id=Tools::getValue('prod_id');
    if (Module::isInstalled('sailthru')) {
        if($logged){
            $customer = new Customer(intval($cookie->id_customer));
            $customerInterestsProductList = Module::hookExec('sailthruCustomerInterests');
            $customerInterestsProductListArray = explode(',', $customerInterestsProductList);
            unset($customerInterestsProductListArray[array_search($prod_id, $customerInterestsProductListArray)]);
            $customerInterestsProductList = implode(',',$customerInterestsProductListArray);
            $customerInterestProducts = Category::getCustomerInterestProducts(intval($id_lang),$customerInterestsProductList, 1, 10, 'id_product');
            $customerInterestProducts = $customer->disappearDiscountedProducts($customerInterestProducts);
            $prodsmall=Image::getSize('prodsmall');
            $prodsmall_width=($prodsmall['width']-65);
            $prodsmall_height=($prodsmall['height']-65);
            
            if(count($customerInterestProducts) >=4){                
                if(isset($customerInterestProducts) && count($customerInterestProducts) >4){
                    $list_recommend_products .='<a id="user-interest-btn-scroll-left" title="Previous" href="javascript:{}">Previous</a>';
                 }
                 $width=(136*count($customerInterestProducts));
                 $list_recommend_products .='<div id="user-interest-thumbs-list">
					<ul id="user-interest-thumbs-list-frame" style="width:'.$width.'px">';
                 if (isset($customerInterestProducts)){
                     foreach ($customerInterestProducts as  $key=> $userInterestProduct) {
                        if(count($userInterestProduct['quantity']) > 0){
                          $list_recommend_products .='<li>
									<a href="'.$userInterestProduct['link'].'" title="'.$userInterestProduct['legend'].'">
									<img  src="'.$link->getImageLink($userInterestProduct['link_rewrite'], $userInterestProduct['id_image'], "prodsmall").'" alt="'.$userInterestProduct['legend'].'" height="'.$prodsmall_height.'" width="'.$prodsmall_width.'" />
									</a>
									<a href='.$userInterestProduct['link'].'" title="'.$userInterestProduct['legend'].'">
										<p class="user-interest-name">'.substr($userInterestProduct['name'], 0,22).'</p>
									</a>
									<p class="user-interest-price">'.$userInterestProduct['price'].' TL</p>
								</li>';      
                        }
                     }
                     
                 }
                 $list_recommend_products .='</ul></div>';
                 if(isset($customerInterestProducts) && count($customerInterestProducts) >4){
                     $list_recommend_products .='<a id="user-interest-btn-scroll-right" title="Next" href="javascript:{}">Next</a>';
                 }
             
            }else{
                $list_recommend_products="";
            }
            
            echo $list_recommend_products;           
            
        }
    }
?>
