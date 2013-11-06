{assign var='current_step' value='payment_error'}
{include file=$tpl_dir./order-steps.tpl}
<div class="shopping-cart">
<div class="errorContainer">
	<img src="{$img_dir}cart/shopping problem.gif" alt="{l s='Payment error message' mod='pgg'}"/>
	{*<div class= "credit_error">
		<h4 style="margin-bottom: 15px">{l s='İşlem banka tarafından onaylanmadı.' mod='pgg'}</h4>
		<p>{l s='Bu mesajı, alışverişinizde kullandığınız kart ile alakalı olarak, bankadan olumsuz geri dönüş aldığımız için görüyorsunuz. Bu nedenden dolayı işleminizi gerçekleştiremedik. Bu kredi kartınızdan herhangi bir ücret çekilmemiş olduğu anlamına da geliyor.' mod='pgg'}</p>
		<p>{l s='Dilerseniz işlemi farklı bir kart kullanarak deneyebilirsiniz. Eğer aklınıza takılan bir şey olursa bize destek@butigo.com adresinden ve (216) 418 26 26 no’lu telefondan ulaşabilirsiniz.' mod='pgg'}</p>
		<p>{l s='İşlemi başka bir kartla tamamlamak için lütfen' mod='pgg'}&nbsp;
			<a href="{$base_dir_ssl}order.php?step=3" title="{l s='buraya tıklayınız' mod='pgg'}"><strong>{l s='buraya tıklayınız' mod='pgg'}</strong></a>.
		</p>
		<p>{l s='Teşekkürler,' mod='pgg'}<br/>{l s='Butigo Müşteri Mutluluk Ekibi' mod='pgg'}</p>
	</div>*}
	{*<div style="border: 1px dashed red; background-color:#FFFFFF;" class="alert error">
		<img src="./images/error.gif" /><b> Hata mesajı: ({$smarty.post.mdstatus})  {$smarty.post.mderrormessage}</b>
	</div>
    <br />
	<p>{$error}</p>*}
	{*<p class="cart_navigation">
		<a href="{$base_dir_ssl}order.php?step=3" class="buttons button_continue" title="{l s='Back to payment' mod='pgg'}">
			<span>{l s='Back to payment' mod='pgg'}</span>
		</a>
	</p>*}
	<a href="{$link->getPageLink('order.php',true)}?step=3{if $back}&amp;back={$back}{/if}" class="exclusive" title="{l s='Back To Payment' mod='pgg'}">
		{*<img src="{$img_dir}buttons/payment_error.jpg" alt="{l s='Back To Payment' mod='pgg'}" style="margin: 20px 0pt 0pt 15px;"/>*}
	</a>
</div>
{include file="$tpl_dir./cart_bottom_footer.tpl"}
</div>