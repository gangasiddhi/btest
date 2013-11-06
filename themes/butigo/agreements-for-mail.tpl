<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{$lang_iso}">
    <head>
        <link media="all" type="text/css" rel="stylesheet" href="{$css_dir}agreements.css" />
    </head>
    <body>
        {if $id_cms == 20}
            <h1 style="margin: 23px">Ön Satış Sözleşmesi</h1>
            {$pre_sales_agreement_content}
        {/if}

        {if $id_cms == 21}
            <h1 style="margin: 23px">Mesafeli Satış Sözleşmesi</h1>
            {$non_member_sales_agreement_content}
        {/if}

        {if $id_cms == 22}
            <h1 style="margin: 23px">Üye Mesafeli Satış Sözleşmesi</h1>
            {$member_sales_agreement_content}
        {/if}
    </body>
</html
