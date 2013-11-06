<?php
require_once(dirname(__FILE__).'/config/config.inc.php');
require_once(dirname(__FILE__).'/init.php');

$cron_user = Tools::getValue('crnu');
$cron_pass = Tools::getValue('crnp');
if(!$cron_user)
	$cron_user = $argv[1];
if(!$cron_pass)
	$cron_pass = $argv[2];
$cron_pass = Tools::encrypt($cron_pass);

if($cron_user == _CRON_USER_ && $cron_pass == _CRON_PASSWD_)
{
    global $link;

    function getImagesx($prod_id, $id_lang,$ipa = 0)
	{
		return Db::getInstance()->ExecuteS('
		SELECT i.`cover`, i.`id_image`, il.`legend`, i.`position`
		FROM `'._DB_PREFIX_.'image` i
		LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)($id_lang).')
		WHERE i.`id_product` = '.(int)($prod_id).'
		ORDER BY `position`');
	}

    function getSubCategoriesx($cat_id, $sub_cat_id, $id_lang, $active = true)
	{
	 	if (!Validate::isBool($active))
	 		die(Tools::displayError());

		$groups = FrontController::getCurrentCustomerGroups();
		$sqlGroups = (count($groups) ? 'IN ('.implode(',', $groups).')' : '= 1');

		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT c.*, cl.id_lang, cl.name, cl.description, cl.link_rewrite, cl.meta_title, cl.meta_keywords, cl.meta_description
		FROM `'._DB_PREFIX_.'category` c
		LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (c.`id_category` = cl.`id_category` AND `id_lang` = '.(int)($id_lang).')
		LEFT JOIN `'._DB_PREFIX_.'category_group` cg ON (cg.`id_category` = c.`id_category`)
		WHERE c.`id_parent` = '.(int)($cat_id).' AND c.id_category='.(int)($sub_cat_id).'
		'.($active ? 'AND `active` = 1' : '').'
		GROUP BY c.`id_category`
		ORDER BY c.`position` ASC');

		foreach ($result AS &$row)
		{
			$row['id_image'] = (file_exists(_PS_CAT_IMG_DIR_.$row['id_category'].'.jpg')) ? (int)($row['id_category']) : Language::getIsoById($id_lang).'-default';
			$row['legend'] = 'no picture';

            $categ = new Category((int)$row['id_category'], (int)$id_lang);
            $row['nbproducts'] =$categ->getProducts(4, 1, 10000000, 'position', NULL, false, true, false, 1, true, true);
		}
		return $result;
	}

    function getAttributesGroupsx($prod_id)
	{
		return Db::getInstance()->ExecuteS('
		SELECT pa.`reference`
		FROM `'._DB_PREFIX_.'product_attribute` pa
		WHERE pa.`id_product` = '.(int)($prod_id).' AND pa.`active` = 1');
	}
    
    /*Get Product Types*/
	function getProductTypes($id_prod_type)
	{
		$product_types = DB::getInstance()->getValue('
			 SELECT type_name FROM `'._DB_PREFIX_.'product_type` WHERE `id_product_type`='.$id_prod_type);

		return 	$product_types;
	}


    /*query for fetching the main category name*/
    $category_sql = 'SELECT c.id_category as category_id,cl.name as category_name,c.level_depth,c.id_parent as parent_id FROM '._DB_PREFIX_.'category as c
          INNER JOIN '._DB_PREFIX_.'category_lang as cl ON cl.id_category = c.id_category WHERE c.id_parent=1 AND cl.id_lang = 4 AND c.active=1 AND c.id_category in(162,164,171)';

    //(122,131,137)
     $category_result= Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($category_sql);

     foreach($category_result as $category) {
        /*query for fetching the subcategory name*/
        $subcategory_sql = 'SELECT c.id_category as subcategory_id,cl.name as subcategory_name,c.level_depth,c.id_parent FROM '._DB_PREFIX_.'category c
            INNER JOIN '._DB_PREFIX_.'category_lang cl ON cl.id_category = c.id_category WHERE c.id_parent='.$category['category_id'].' AND cl.id_lang = 4 AND c.id_category not in(153,154) AND c.active=1';
        //123,124

        $subcategory_result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($subcategory_sql);
        $id_lang = Configuration::get('PS_LANG_DEFAULT');
        if(count($subcategory_result) !=0){
			/*Foreach loop for main category products starts here*/
			$parent_cat_product_object=new Category($category['category_id']);
            $all_category_products = $parent_cat_product_object->getProducts(4, 1, 10000000, 'position', NULL, false, true, false, 1, true, true);

            if($_GET['flag']==1){
				echo'category_id='.$category['category_id'];
				echo'parent_category product<br>';
                echo "<pre>";print_r($all_category_products);echo "</pre>";
            }
            /*Building the array of all the products of parent category*/
            $category_old_product_id = 0;
            foreach($all_category_products as $product) {
                $image_array=getImagesx($product['id_product'],4);

                foreach ($image_array as $image_id_info){
                    if($category_old_product_id == $product['id_product']) {
                        $image_count++;
                        if($image_count > 4)
                            continue;
                        $all_products[$product['id_product']]['images'][] =  $link->getImageLink($product['link_rewrite'], $product['id_product'].'-'.$image_id_info['id_image'], 'large');
                    }else {
                        $all_products[$product['id_product']]['parent_category_name'] =  $category['category_name'];/*Parent category*/
                        $all_products[$product['id_product']]['sub_category_name'] = '';/*Subcategory Name*/
                        $all_products[$product['id_product']]['product_name'] =  $product['name'];
                        $category_name = Category::getLinkRewrite((int)$product['id_category_default'],(int) $id_lang);
						$all_products[$product['id_product']]['product_url'] = $link->getProductLink((int)$product['id_product'], NULL, $product['link_rewrite'], $category_name, $product['ean13'], 4);
                        $productPrice = Product::getPriceStatic((int)$product['id_product'], true, $product['id_product_attribute'] , 6);
						$originalPrice = Product::getPriceWithoutReductStatic((int)$product['id_product'], False, (int)$product['id_product'],6);
						
                        $all_products[$product['id_product']]['product_price'] =  $productPrice;
                        if ((int)$originalPrice > (int)$productPrice){
                            $all_products[$product['id_product']]['product_price_original'] = $originalPrice;
                        }
                        $all_products[$product['id_product']]['description'] =   str_replace(array("\n", "\r"), '', trim(strip_tags($product['description'])));
                        $all_products[$product['id_product']]['images'][] =  $link->getImageLink($product['link_rewrite'], $product['id_product'].'-'.$image_id_info['id_image'], 'large');

                        $all_products[$product['id_product']]['category'] =  $product['name'];
                        $all_products[$product['id_product']]['stock'] = $product['quantity'];
                        $all_products[$product['id_product']]['sp_reduction'] = $product['specific_prices']['reduction'];
                        $all_products[$product['id_product']]['sp_reduction_type'] = $product['specific_prices']['reduction_type'];
                        $all_products[$product['id_product']]['out_of_stock'] =  $product['out_of_stock'];
                        $all_products[$product['id_product']]['available_for_order'] =  $product['available_for_order'];
                        $all_products[$product['id_product']]['condition'] =  $product['condition'];
                        
                        $product_types = getProductTypes($product['id_product_type']);
                        $all_products[$product['id_product']]['product_type'] =  $product_types;

                        $product_colors_array=$product['product_colors'];
                                if(count($product_colors_array)!=0){
                            foreach($product_colors_array as $attributes ){
                                if($attributes['is_color_group'] == 1)
                                $all_products[$product['id_product']]['color_attributes'][$attributes['id_attribute']] = $attributes['attribute_name'];
                            }
                        }

                        if(count($product['shoe_sizes'])!=0){
                            foreach ($product['shoe_sizes'] as $shoeattributes){
                                $all_products[$product['id_product']]['size_attributes'][$shoeattributes['id_attribute']] = array($shoeattributes['attribute_name'], $shoeattributes['product_qty']);
                            }
                        }

                        $category_old_product_id = $product['id_product'];
                        $image_count = 1;
                    }
                }
            }
			/*Foreach loop for main category products starts here*/

            foreach($subcategory_result as $subcategory) {
                    $sub_cat_id=$subcategory['subcategory_id'];
                    $category_products=getSubCategoriesx($category['category_id'],$sub_cat_id,4,true);
                    $all_featured_products=$category_products[0]['nbproducts'];
                    if($_GET['flag']==1){
                        echo "Subcategory_products_Array=<pre>";print_r($category_products);echo "</pre>";
                    }
                    $old_product_id = 0;
                    foreach($all_featured_products as $product) {
                        /*Getting all images of a product*/
                        $image_array=getImagesx($product['id_product'],4);
                        $product_references_array=getAttributesGroupsx($product['id_product']);

                        if($_GET['flag']==1){
                            echo "product references=<pre>";print_r($product_references_array);echo "</pre>";
                            echo "image_array<pre>";print_r($image_array);echo "</pre>";
                        }

                        foreach ($image_array as $image_id_info){

                            if($old_product_id == $product['id_product']) {
                                $image_count++;
                                if($image_count > 4)
                                    continue;
                                $all_products[$product['id_product']]['images'][] =  $link->getImageLink($product['link_rewrite'],$product['id_product'].'-'.$image_id_info['id_image'], 'large');
                            }else {
                                $all_products[$product['id_product']]['parent_category_name'] =  $category['category_name'];/*Parent category*/
                                $all_products[$product['id_product']]['sub_category_name'] =  $category_products[0]['name'];/*Subcategory Name*/
                                $all_products[$product['id_product']]['product_name'] =  $product['name'];
								$category_name = Category::getLinkRewrite((int)$product['id_category_default'],(int) $id_lang);
                                $all_products[$product['id_product']]['product_url'] = $link->getProductLink((int)$product['id_product'], NULL, $product['link_rewrite'], $category_name, $product['ean13'], 4);
                                $productPrice = Product::getPriceStatic((int)$product['id_product'], true, $product['id_product_attribute'] , 6);
								$originalPrice = Product::getPriceWithoutReductStatic((int)$product['id_product'], False, (int)$product['id_product'],6);
                                $all_products[$product['id_product']]['product_price'] =  $productPrice;
                                if ((int)$originalPrice > (int)$productPrice){
                                    $all_products[$product['id_product']]['product_price_original'] = $originalPrice;
                                }
                                
                                $all_products[$product['id_product']]['description'] =   str_replace(array("\n", "\r"), '', trim(strip_tags($product['description'])));
                                $all_products[$product['id_product']]['images'][] =  $link->getImageLink($product['link_rewrite'], $product['id_product'].'-'.$image_id_info['id_image'], 'large');

                                $all_products[$product['id_product']]['category'] =  $product['name'];
                                $all_products[$product['id_product']]['stock'] = $product['quantity'];
                                $all_products[$product['id_product']]['sp_reduction'] = $product['specific_prices']['reduction'];
                                $all_products[$product['id_product']]['sp_reduction_type'] = $product['specific_prices']['reduction_type'];
                                $all_products[$product['id_product']]['product_reference'] =  $product_references_array;
                                $all_products[$product['id_product']]['out_of_stock'] =  $product['out_of_stock'];
                                $all_products[$product['id_product']]['available_for_order'] =  $product['available_for_order'];
                                $all_products[$product['id_product']]['condition'] =  $product['condition'];
                                $product_types = getProductTypes($product['id_product_type']);
                                $all_products[$product['id_product']]['product_type'] =  $product_types;
                                
                                $product_colors_array=$product['product_colors'];
                                if(count($product_colors_array)!=0){
                                    foreach($product_colors_array as $attributes ){
                                        if($attributes['is_color_group'] == 1)
                                        $all_products[$product['id_product']]['color_attributes'][$attributes['id_attribute']] = $attributes['attribute_name'];
                                    }
                                }

                                if(count($product['shoe_sizes'])!=0){
                                    foreach ($product['shoe_sizes'] as $shoeattributes){
                                        $all_products[$product['id_product']]['size_attributes'][$shoeattributes['id_attribute']] = array($shoeattributes['attribute_name'], $shoeattributes['product_qty']);
                                    }
                                }

                                $old_product_id = $product['id_product'];
                                $image_count = 1;
                            }
                        }

                    }

                    // If color of product is not exist on bu_attribute get colors from bu_feature table
                    foreach($all_featured_products as $product) {
                        $product_colors = &$all_products[$product['id_product']]['color_attributes'];
                        if ($product_colors)
                            continue;

                        $product_attributes = Product::getFrontFeaturesStatic(4, $product['id_product']);
                        foreach ($product_attributes as $atribute) {
                            if ($atribute['name'] == 'Renk') {
                                $product_colors[] = $atribute['value'];
                            }
                        }
                    }
            }
        }else{
			$parent_cat_product_object=new Category($category['category_id']);
            $all_category_products = $parent_cat_product_object->getProducts(4, 1, 10000000, 'position', NULL, false, true, false, 1, true, true);

            if($_GET['flag']==1){
				echo'category_id='.$category['category_id'];
				echo'parent_category product<br>';
                echo "<pre>";print_r($all_category_products);echo "</pre>";
            }
            /*Building the array of all the products of parent category*/
            $old_product_id = 0;
            foreach($all_category_products as $product) {
                $image_array=getImagesx($product['id_product'],4);

                foreach ($image_array as $image_id_info){
                    if($old_product_id == $product['id_product']) {
                        $image_count++;
                        if($image_count > 4)
                            continue;
                        $all_products[$product['id_product']]['images'][] =  $link->getImageLink($product['link_rewrite'], $product['id_product'].'-'.$image_id_info['id_image'], 'large');
                    }else {
                        $all_products[$product['id_product']]['parent_category_name'] =  $category['category_name'];/*Parent category*/
                        $all_products[$product['id_product']]['sub_category_name'] = '';/*Subcategory Name*/
                        $all_products[$product['id_product']]['product_name'] =  $product['name'];
                        $category_name = Category::getLinkRewrite((int)$product['id_category_default'],(int) $id_lang);
						$all_products[$product['id_product']]['product_url'] = $link->getProductLink((int)$product['id_product'], NULL, $product['link_rewrite'], $category_name, $product['ean13'], 4);
                        $productPrice = Product::getPriceStatic((int)$product['id_product'], true, $product['id_product_attribute'] , 6);
						$originalPrice = Product::getPriceWithoutReductStatic((int)$product['id_product'], False, (int)$product['id_product'],6);
                        
                        $all_products[$product['id_product']]['product_price'] =  $productPrice;
                        if ((int)$originalPrice > (int)$productPrice){
                            $all_products[$product['id_product']]['product_price_original'] = $originalPrice;
                        }
                        $all_products[$product['id_product']]['description'] =   str_replace(array("\n", "\r"), '', trim(strip_tags($product['description'])));
                        $all_products[$product['id_product']]['images'][] =  $link->getImageLink($product['link_rewrite'], $product['id_product'].'-'.$image_id_info['id_image'], 'large');

                        $all_products[$product['id_product']]['category'] =  $product['name'];
                        $all_products[$product['id_product']]['stock'] = $product['quantity'];
                        $all_products[$product['id_product']]['sp_reduction'] = $product['specific_prices']['reduction'];
                        $all_products[$product['id_product']]['sp_reduction_type'] = $product['specific_prices']['reduction_type'];
                        $all_products[$product['id_product']]['out_of_stock'] =  $product['out_of_stock'];
                        $all_products[$product['id_product']]['available_for_order'] =  $product['available_for_order'];
                        $all_products[$product['id_product']]['condition'] =  $product['condition'];
                        $product_types = getProductTypes($product['id_product_type']);
                        $all_products[$product['id_product']]['product_type'] =  $product_types;
                        
                        $product_colors_array=$product['product_colors'];
                                if(count($product_colors_array)!=0){
                            foreach($product_colors_array as $attributes ){
                                if($attributes['is_color_group'] == 1)
                                $all_products[$product['id_product']]['color_attributes'][$attributes['id_attribute']] = $attributes['attribute_name'];
                            }
                        }

                        if(count($product['shoe_sizes'])!=0){
                            foreach ($product['shoe_sizes'] as $shoeattributes){
                                $all_products[$product['id_product']]['size_attributes'][$shoeattributes['id_attribute']] = array($shoeattributes['attribute_name'], $shoeattributes['product_qty']);
                            }
                        }

                        $old_product_id = $product['id_product'];
                        $image_count = 1;
                    }
                }
            }

            // If color of product is not exist on bu_attribute get colors from bu_feature table
            foreach($all_category_products as $product) {
                $product_colors = &$all_products[$product['id_product']]['color_attributes'];
                if ($product_colors)
                    continue;

                $product_attributes = Product::getFrontFeaturesStatic(4, $product['id_product']);
                foreach ($product_attributes as $atribute) {
                    if ($atribute['name'] == 'Renk') {
                        $product_colors[] = $atribute['value'];
                    }
                }
            }

        }

    }
    //exit;
    $product_feed_xml = "<?xml version=\"1.0\"?> \n";
    $product_feed_xml .= "<rss version=\"2.0\" xmlns:g=\"http://base.google.com/ns/1.0\">\n";    
    $product_feed_xml .= "<channel>
                            <title>Butigo</title>\n
                            <link rel=\"self\" href=\"http://www.butigo.com\"/>
                            <updated>".date('Y-m-d H:i:s')."</updated>\n
                            <description><![CDATA[Bayan ayakkabı markası Butigo'nun resmi internet sitesi Butigo.com'un hesabı]]></description>
                        ";
    
    
    foreach($all_products AS $prod_id => $product) {
        $product_feed_xml .= "<item>\n";
        
        if(isset($product['color_attributes'])) {
            foreach($product['color_attributes'] AS $prod_attribute) {
                $canta_color = array(Tools::xmlentities($prod_attribute).", ");
            }
        }
        $product_url=$product['product_url'];
        $product_feed_xml .= "<g:id>".Tools::xmlentities($prod_id)."</g:id>\n";
        $product_feed_xml .= "<title>".Tools::xmlentities($product['product_name'])."</title>\n";
        $product_feed_xml .= "<link>".Tools::xmlentities($product_url)."</link>\n";
        $product_feed_xml .= "<description><![CDATA[".Tools::xmlentities($product['description'])."]]></description>\n";
        
        if($product['sub_category_name'] !=''){
            $category_separator='&#47;';
            
        }else{
            $category_separator='';
            
        }
        $product_feed_xml .= "<g:google_product_category>".Tools::xmlentities($product['parent_category_name'])." ".$category_separator." ".Tools::xmlentities($product['sub_category_name'])."</g:google_product_category>\n";
        $product_feed_xml = rtrim($product_feed_xml, "&#47; ");
        
        $product_feed_xml .= "<g:product_type>".Tools::xmlentities($product['product_type'])."</g:product_type>\n";      
        $product_feed_xml .= "<g:condition>".$product['condition']."</g:condition>\n";
        
         if($product['product_price_original'] > $product['product_price']){
            $google_product_price=Tools::xmlentities(Tools::ps_round($product['product_price_original'], 2));
        }else{
            $google_product_price=Tools::xmlentities(Tools::ps_round($product['product_price'], 2));
        }
        $product_feed_xml .= "<g:price> ".$google_product_price." TL</g:price>\n";
        $product_feed_xml .= "<g:brand>Butigo</g:brand>\n";
        
         /*Check for out of stock, showing status, available for order & apply following attribute accordingly*/
        
        if(isset($product['stock']) && $product['stock'] > 0){
            $availability='in stock';
        }else {
            $availability='out of stock';
        }
        
        $product_feed_xml .= "<g:availability>".$availability."</g:availability>\n";        
        
        foreach(array_unique($product['images']) AS $key => $image){
            if($key == 0){
                $image_attribute = "<g:image_link>";
                $image_attribute_end_tag = "</g:image_link>";
            }
            if($key == 1){
                $image_attribute = "<g:additional_image_link>";
                $image_attribute_end_tag = "</g:additional_image_link>";
            }
            if($key == 2){
                $image_attribute = "<g:additional_image_link>";
                $image_attribute_end_tag = "</g:additional_image_link>";
            }
            if($key == 3){
                $image_attribute = "<g:additional_image_link>";
                $image_attribute_end_tag = "</g:additional_image_link>";
            }

             $product_feed_xml .= $image_attribute.Tools::xmlentities($image).$image_attribute_end_tag."\n";
        }
        
        /*$product_feed_xml.="<g:shipping>
                                <g:country>Turkey</g:country>
                                <g:service>Standard</g:service>
                                <g:price>".$google_product_price." TL</g:price>
                            </g:shipping>\n";*/
        
       
        if(isset($product['color_attributes'])) {
            $product_feed_xml .= "<g:color>";
            foreach($product['color_attributes'] AS $prod_attribute) {
                $product_feed_xml .= Tools::xmlentities($prod_attribute)."&#47; ";
            }
            $product_feed_xml = rtrim($product_feed_xml, "&#47; ");
            $product_feed_xml .= "</g:color>\n";
        }
                 
        if(isset($product['size_attributes'])) {
            $product_feed_xml .= "<g:size>";
            foreach($product['size_attributes'] AS $prod_size_attribute) {
                $product_feed_xml .= Tools::xmlentities($prod_size_attribute[0])."&#47; ";
            }
          $product_feed_xml = rtrim($product_feed_xml, "&#47; ");
          $product_feed_xml .= "</g:size>\n";
          
        }
        $product_feed_xml .= "</item>\n";
    }
    $product_feed_xml .= "</channel></rss>";

    $fileLog = fopen(_PS_LOG_DIR_.'google-merchant-product-feed-log.txt','a');
    fwrite($fileLog , "START\n");
    if(!$fileLog)
        die("Cannot open the file butigo/log/google-merchant-product-feed-log.txt");
    fwrite($fileLog , "Total Products = ".sizeof($all_products)."\n");

    if(is_dir(_PS_DOWNLOAD_DIR_."product-feed"))
    {
        fwrite($fileLog , "Folder butigo/download/product-feed exists\n");
        echo "Folder butigo/download/product-feed exists";echo "<br/>";
    }
    elseif(mkdir(_PS_DOWNLOAD_DIR_."product-feed", 0777))
    {
        fwrite($fileLog , "Created folder butigo/download/product-feed\n");
        echo "Created folder butigo/download/product-feed";
        echo "<br/>";
    }
    else
    {
        fwrite($fileLog , "Could not create folder butigo/download/product-feed ".date('Y-m-d H:i:s')."\n");
        die("Could not create folder butigo/download/product-feed");
        echo "Could not create folder butigo/download/product-feed";
        echo "<br/>";
    }

    if(!$file = fopen(_PS_DOWNLOAD_DIR_.'product-feed/prod-feed-gmerchant.xml','w'))
    {
        fwrite($fileLog , "Cannot open the file butigo/download/product-feed/prod-feed-gmerchant.xml ".date('Y-m-d H:i:s')."\n");
        die("Cannot open the file butigo/download/product-feed/prod-feed-gmerchant.xml");
    }
    if(fwrite($file , $product_feed_xml))
    {
        fclose($file);
        fwrite($fileLog , "Product Feed XML written ".date('Y-m-d H:i:s')."\n");
        echo 'You can open created xml file : <a href="'.__PS_BASE_URI__.'download/product-feed/prod-feed-gmerchant.xml">prod-feed-gmerchant.xml</a>';
        echo "<br/>";
        echo "Find the file prod-feed-gmerchant.xml in path butigo/download/product-feed/";
        echo "<br/>";
    }
    else
    {
        fwrite($fileLog , "Could not write the XML {else part}\n");
        echo 'unable to write the XML {else part}.';
    }
    fwrite($fileLog , "END\n");
    fclose( $fileLog);
}
else
{
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");

	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");

	header("Location: ../");
	exit;
}
?>
