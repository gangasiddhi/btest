<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor. 
 */

include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/../../init.php');
include_once(dirname(__FILE__) . '/taggingandfiltering.php');
$categoryId = Tools::getvalue('categoryId');
$tagFilterList = '';
$sizeFilterList = '';
$tagIds = Tools::getValue('tags');
$shoeSizes = Tools::getValue('shoeSizes');
    
if(!empty($shoeSizes)){
    $shoeSizes = array_unique($shoeSizes);
    foreach($shoeSizes as $shoeSize){
        $sizeFilterList .= $shoeSize.',';
    }
    $sizeFilterList = rtrim($sizeFilterList, ',');
}

if(!empty($tagIds)) {
    $tagIds = array_unique($tagIds);
    foreach($tagIds as $tagId)
    {
        $tagFilterList  .= $tagId.',';
    }
    $tagFilterList = rtrim($tagFilterList, ',');
}

$productFilter = new TaggingAndFiltering();
$filterProducts = $productFilter->displaySizeTagFilterProducts($categoryId, $tagFilterList, $sizeFilterList);

echo $filterProducts;
?>
