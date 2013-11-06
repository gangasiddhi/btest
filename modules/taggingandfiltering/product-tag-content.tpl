<script type="text/javascript">
//<![CDATA[
	var baseDir = '{$path}';
//]]>
</script>
<script src="{$path}assets/productTags.js" type="text/javascript"></script>
<div class="margin-form"></div>
<label>{l s='Tag Name' mod='taggingandfiltering'}</label>
<div class="margin-form">
    <input type="text" size="33" id="tag_name" name="name" value="" /> <sup>*</sup>
</div>
<label>{l s='Language' mod='taggingandfiltering'}</label>
<div class="margin-form">
    <select name="id_lang" id="id_lang">
        <option value="">-</option>
            {foreach from=$languages item=language}
                <option value="{$language.id_lang}" {if $language.id_lang == $defaultLanguage} selected="selected"{/if}>{$language.name}</option>
            {/foreach}
    </select> <sup>*</sup>
</div>
<div class="clear"></div>
<div class="margin-form">
    <span id= "AddNewTag" class="button">{l s='Add' mod='taggingandfiltering'}</span>
</div>
<div class="clear">&nbsp;</div>
<h3 style="margin: 25px 0pt 0pt;">{l s='Product Tags' mod='taggingandfiltering'}</h3>
<table>
    <tr>
        <td>
            <p>{l s='Available Tags' mod='taggingandfiltering'}</p>
            <select multiple id="selectProductTags" style="width:300px;height:160px;">
            {if $tags}
                {foreach from=$tags item=tag}
                    <option value="{$tag.id_tag}">{$tag.name}</option>
                {/foreach}
            {/if}
            </select><br /><br />                                
            <a href="#" id="addTag" style="text-align:center;display:block;border:1px solid #aaa;text-decoration:none;background-color:#fafafa;color:#123456;margin:2px;padding:2px">
                 {l s='Attach' mod='taggingandfiltering'}&nbsp;&gt;&gt;
            </a>
        </td>
        <td style="padding-left:20px;">
            <p>{l s='Attached Tags for this product' mod='taggingandfiltering'}</p>
            <select multiple id="selectedProductTags"  name="tags[]" style="width:300px;height:160px;">
            {if $each_product_tags }
                {foreach from=$each_product_tags key=key item=tag}
                    <option value="{$key}">{$tag}</option>
                {/foreach}
            {/if}
            </select><br /><br />
            <a href="#" id="removeTag" style="text-align:center;display:block;border:1px solid #aaa;text-decoration:none;background-color:#fafafa;color:#123456;margin:2px;padding:2px">
                 &lt;&lt;&nbsp;{l s='Remove' mod='taggingandfiltering'}
            </a>
        </td>
    </tr>
</table>
<div class="clear">&nbsp;</div>
<div class="margin-form">
    <input type="submit" value="{l s='Save' mod='taggingandfiltering'}" name="submitAddProductTag" class="button" />
</div>
<div class="small"><sup>*</sup>{l s='Required field' mod='taggingandfiltering'}</div>
