{if isset($product)}
<script type="text/javascript">
	var rating_rpc = '{$rating_rpc}';
</script>
{*<script type="text/javascript" src="{$content_dir}modules/productrating/rating/js/behavior.js"></script>
<script type="text/javascript" src="{$content_dir}modules/productrating/rating/js/rating.js"></script>
<link rel="stylesheet" type="text/css" href="{$content_dir}modules/productrating/rating/css/rating.css" />*}

<style type="text/css">
	.unit-rating, .unit-rating li a:hover, .unit-rating li.current-rating {ldelim}
	background-image:url('{$media_server}{$modules_dir}productrating/rating/stars/{$star}');
	{rdelim}
	
	.ratingblock {ldelim}
	{if $bgcolor}
	background-color: #{$bgcolor};
	{/if}
	{if $bdcolor}
	border: 1px #{$bdcolor} solid;
	{/if}
	{rdelim}
</style>
{/if}