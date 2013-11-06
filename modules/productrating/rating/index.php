<?php require('_drawrating.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<title>Multiple Ajax Star Rating Bars</title>

<script type="text/javascript" language="javascript" src="js/behavior.js"></script>
<script type="text/javascript" language="javascript" src="js/rating.js"></script>

<link rel="stylesheet" type="text/css" href="css/default.css" />
<link rel="stylesheet" type="text/css" href="css/rating.css" />
</head>

<body>

<div id="container">
<h1>Unobtrusive AJAX Star Rating Bar</h1>

<?php echo rating_bar('id21',''); ?>
<?php echo rating_bar('id22',''); ?>


<?php echo rating_bar('id1',''); ?>
<?php echo rating_bar('2id',5); ?>
<?php echo rating_bar('3xx',6); ?>
<?php echo rating_bar('4test',8); ?>
<?php echo rating_bar('5560'); ?>
<?php echo rating_bar('id1','','static'); ?>
<?php echo rating_bar('66334',''); ?>
<?php echo rating_bar('63334',''); ?>
</div>

</body>
</html>