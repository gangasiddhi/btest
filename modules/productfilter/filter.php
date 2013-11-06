<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor. 
 */

include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/../../init.php');
include_once(dirname(__FILE__) . '/productfilter.php');

$categoryId = Tools::getvalue('categoryId');
$shoeSizes = Tools::getValue('shoeSizes');
$colors = Tools::getValue('colors');

$filterList = '';

if(!empty($shoeSizes)){
    $shoeSizes = array_unique($shoeSizes);
    foreach($shoeSizes as $shoeSize){
        $filterList .= $shoeSize.',';
    }
}

if(!empty($colors)){
    $colors = array_unique($colors);
    foreach($colors as $color){
        $filterList .= '"'.$color.'",';
    }
}

$filterList = rtrim($filterList, ',');

$productFilter = new productfilter();

$filterProducts = $productFilter->displayFilterProducts($categoryId,$filterList);

echo $filterProducts;

?>
