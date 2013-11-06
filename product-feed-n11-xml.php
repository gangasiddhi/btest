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

    $products_sql = '
        SELECT p.`id_product`, p.`price`, p.`id_tax_rules_group`, p.`ean13`, p.`quantity`, p.`id_category_default`, pa.`id_product_attribute`, pl.`name`, pl.`description`, pl.`link_rewrite`, i.`id_image`, cp.`id_category` AS category_id, cl.`name` AS category_name, sp.`id_specific_price`, sp.`reduction`, sp.`reduction_type`, sp.`strike_out`
        FROM `'._DB_PREFIX_.'product` p
        LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON (p.`id_product` = pa.`id_product` AND pa.`default_on` = 1)
        LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = 4)
        LEFT JOIN `'._DB_PREFIX_.'category_product` cp ON (cp.`id_product` = p.`id_product` AND cp.`id_category` = p.`id_category_default`)
        LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (cl.`id_category` = cp.`id_category` AND cl.`id_lang` = 4)
        LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = p.`id_product`)
        LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = 4)
        LEFT JOIN `'._DB_PREFIX_.'specific_price` sp ON (sp.`id_product` = p.`id_product` AND sp.`id_group` = 1)
        WHERE p.`active` = 1
        ORDER BY p.`id_product` ASC ';

    $products = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($products_sql);

    $products_attributes_sql = 'SELECT p.`id_product`, pa.`id_product_attribute`, pa.`quantity`, a.`id_attribute`, a.`id_attribute_group`, al.`name` AS attribute_name, agl.`name` AS attribute_group_name, ag.`is_color_group`
        FROM `'._DB_PREFIX_.'product` p
        LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON (p.`id_product` = pa.`id_product`)
        LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac ON pac.`id_product_attribute` = pa.`id_product_attribute`
        LEFT JOIN `'._DB_PREFIX_.'attribute` a ON a.`id_attribute` = pac.`id_attribute`
        LEFT JOIN `'._DB_PREFIX_.'attribute_group` ag ON ag.`id_attribute_group` = a.`id_attribute_group`
        LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON (a.`id_attribute` = al.`id_attribute` AND al.`id_lang` = 4)
        LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl ON (ag.`id_attribute_group` = agl.`id_attribute_group` AND agl.`id_lang` = 4)
        WHERE p.`active` = 1';
    $products_attributes = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($products_attributes_sql);

    //echo $products_attributes_sql;exit;
    //echo $products_sql;
    //echo "<pre>";print_r($products);echo "</pre>";
    //echo "<br/>";exit;

    $old_product_id = 0;
    foreach($products as $product) {
        if($old_product_id == $product['id_product']) {
            $image_count++;
            if($image_count > 4)
                continue;
            $all_products[$product['id_product']]['images'][] =  $link->getImageLink($product['link_rewrite'], $product['id_product'].'-'.$product['id_image'], 'large');
        } else {
            $all_products[$product['id_product']]['product_name'] =  $product['name'];

            $productPrice = Product::getPriceStatic((int)$product['id_product'], true, $product['id_product_attribute'] , 6);
            $all_products[$product['id_product']]['product_price'] =  $productPrice;
            $originalPrice = Product::getPriceWithoutReductStatic((int)$product['id_product'], False, (int)$product['id_product'],6);
            if ((int)$originalPrice > (int)$productPrice){
                $all_products[$product['id_product']]['product_price_original'] = $originalPrice;
            }
            $all_products[$product['id_product']]['description'] =   str_replace(array("\n", "\r"), '', trim(strip_tags($product['description'])));
            //$category = Category::getLinkRewrite((int)$product['id_category_default'], (int)($cookie->id_lang));
            //$all_products[$product['id_product']]['link'] = $link->getProductLink((int)$product['id_product'], NULL, $product['link_rewrite'], $category, $product['ean13']).'?from_gad=1';
            $all_products[$product['id_product']]['images'][] =  $link->getImageLink($product['link_rewrite'], $product['id_product'].'-'.$product['id_image'], 'large');

            $all_products[$product['id_product']]['category'] =  $product['category_name'];
            $all_products[$product['id_product']]['stock'] = $product['quantity'];
            $all_products[$product['id_product']]['sp_reduction'] = $product['reduction'];
            $all_products[$product['id_product']]['sp_reduction_type'] = $product['reduction_type'];

            $old_product_id = $product['id_product'];
            $image_count = 1;
        }
    }

    foreach($products_attributes as $attributes) {
        if($attributes['is_color_group'] == 1)
            $all_products[$attributes['id_product']]['color_attributes'][$attributes['id_attribute']] = $attributes['attribute_name'];
        else
            $all_products[$attributes['id_product']]['size_attributes'][$attributes['id_attribute']] = array($attributes['attribute_name'], $attributes['quantity']);
    }

    // If color of product is not exist on bu_attribute get colors from bu_feature table
    foreach($products as $product) {
        $product_colors = &$all_products[$product['id_product']]['color_attributes'];
        if ($product_colors)
            continue;

        $product_attributes = Product::getFrontFeaturesStatic(Configuration::get('PS_LANG_DEFAULT'), $product['id_product']);
        foreach ($product_attributes as $atribute) {
            if ($atribute['name'] == 'Renk') {
                $product_colors[] = $atribute['value'];
            }
        }
    }

   //echo "<pre>";print_r($all_products);echo "<pre>";
    //exit;

    $product_feed_xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?> \n";
    $product_feed_xml .= "<Urunler>\n";
    foreach($all_products AS $prod_id => $product) {

        if(isset($product['color_attributes'])) {
            foreach($product['color_attributes'] AS $prod_attribute) {
                $canta_color = array(Tools::xmlentities($prod_attribute).", ");
            }
        }
        $product_feed_xml .= "\t<Urun>\n";
        $product_feed_xml .= "\t\t<Marka>[BUTIGO] ".(isset($product['size_attributes']) ? "Kadın Ayakkabı" : "Kadın Çanta" )." ".$canta_color[0]. " # ".Tools::xmlentities($prod_id)."</Marka>\n";

        $product_feed_xml .= "\t\t<KategoriAdi>".Tools::xmlentities($product['category'])."</KategoriAdi>\n";
        $product_feed_xml .= "\t\t<UrunID>".Tools::xmlentities($prod_id)."</UrunID>\n";
        $product_feed_xml .= "\t\t<UrunAdi>".Tools::xmlentities($product['product_name'])."</UrunAdi>\n";
        foreach($product['images'] AS $key => $image){
            if($key == 0)
                $image_name = "A";
            if($key == 1)
                $image_name = "B";
            if($key == 2)
                $image_name = "C";
            if($key == 3)
                $image_name = "D";

             $product_feed_xml .= "\t\t<resim".$image_name.">".Tools::xmlentities($image)."</resim".$image_name.">\n";
        }
        if (isset($product['product_price_original'])){
            $product_feed_xml .= "\t\t<IndirimTutari>".Tools::xmlentities(Tools::ps_round($product['product_price'], 2))."</IndirimTutari>\n";
            $product_feed_xml .= "\t\t<ListeFiyat>".Tools::xmlentities(Tools::ps_round($product['product_price_original'], 2))."</ListeFiyat>\n";
            $product_feed_xml .= "\t\t<IndirimTuru>1</IndirimTuru>\n";
        } else {
            if($product['sp_reduction_type'] == 'percentage') {
                $product_feed_xml .= "\t\t<IndirimTutari>".Tools::xmlentities(Tools::ps_round($product['sp_reduction'] * 100, 2))."</IndirimTutari>\n";
                $product_feed_xml .= "\t\t<ListeFiyat>".Tools::xmlentities(Tools::ps_round($product['product_price'], 2))."</ListeFiyat>\n";
                $product_feed_xml .= "\t\t<IndirimTuru>2</IndirimTuru>\n";
            }
            elseif($product['sp_reduction_type'] == 'amount') {
                $product_feed_xml .= "\t\t<IndirimTutari>".Tools::xmlentities(Tools::ps_round($product['sp_reduction'], 2))."</IndirimTutari>\n";
                $product_feed_xml .= "\t\t<ListeFiyat>".Tools::xmlentities(Tools::ps_round($product['product_price'], 2))."</ListeFiyat>\n";
                $product_feed_xml .= "\t\t<IndirimTuru>1</IndirimTuru>\n";
            }
            else {
                $product_feed_xml .= "\t\t<ListeFiyat>".Tools::xmlentities(Tools::ps_round($product['product_price'], 2))."</ListeFiyat>\n";
            }
        }
        $product_feed_xml .= "\t\t<Kur>TL</Kur>\n";
        $product_feed_xml .= "\t\t<KDV>8</KDV>\n";

        if(isset($product['color_attributes'])) {
            $product_feed_xml .= "\t\t<Renk>";
            foreach($product['color_attributes'] AS $prod_attribute) {
                $product_feed_xml .= Tools::xmlentities($prod_attribute).", ";
            }
            $product_feed_xml = rtrim($product_feed_xml, ", ");
            $product_feed_xml .= "</Renk>\n";
        }

        $product_feed_xml .= "\t\t<Aciklama>\n".Tools::xmlentities($product['description'])."\n\t\t</Aciklama>\n";
        $product_feed_xml .= "\t\t<VaryantGrupID></VaryantGrupID>\n";

        $product_feed_xml .= "\t\t<Stoklar>\n";
        if(isset($product['size_attributes'])) {
            foreach($product['size_attributes'] AS $prod_size_attribute) {
                $product_feed_xml .= "\t\t\t<Stok>\n";
                $product_feed_xml .= "\t\t\t\t<isim>Beden</isim>\n";
                $product_feed_xml .= "\t\t\t\t<deger>".Tools::xmlentities($prod_size_attribute[0])."</deger>\n";
                $product_feed_xml .= "\t\t\t\t<barcode>AYNI</barcode>\n";
                $product_feed_xml .= "\t\t\t\t<miktar>".Tools::xmlentities($prod_size_attribute[1])."</miktar>\n";
                $product_feed_xml .= "\t\t\t</Stok>\n";
            }
        } else {
            $product_feed_xml .= "\t\t\t<Stok>\n";
            $product_feed_xml .= "\t\t\t\t<isim>Renk</isim>\n";
            $product_feed_xml .= "\t\t\t\t<deger></deger>\n";
            $product_feed_xml .= "\t\t\t\t<barcode>AYNI</barcode>\n";
            $product_feed_xml .= "\t\t\t\t<miktar>".Tools::xmlentities($product['stock'])."</miktar>\n";
            $product_feed_xml .= "\t\t\t</Stok>\n";
        }
        $product_feed_xml .= "\t\t</Stoklar>\n";

        $product_feed_xml .= "\t</Urun>\n";
    }
    $product_feed_xml .= "</Urunler>\n";

    $fileLog = fopen(_PS_LOG_DIR_.'product-feed-n11-log.txt','a');
    fwrite($fileLog , "START\n");
    if(!$fileLog)
        die("Cannot open the file butigo/log/product-feed-n11-log.txt");
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

    if(!$file = fopen(_PS_DOWNLOAD_DIR_.'product-feed/current-n11.xml','w'))
    {
        fwrite($fileLog , "Cannot open the file butigo/download/product-feed/current-n11.xml ".date('Y-m-d H:i:s')."\n");
        die("Cannot open the file butigo/download/product-feed/current-n11.xml");
    }
    if(fwrite($file , $product_feed_xml))
    {
        fclose($file);
        fwrite($fileLog , "Product Feed XML written ".date('Y-m-d H:i:s')."\n");
        echo 'You can open created xml file : <a href="'.__PS_BASE_URI__.'download/product-feed/current-n11.xml">current-n11.xml</a>';
        echo "<br/>";
        echo "Find the file current-n11.xml in path butigo/download/product-feed/";
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
