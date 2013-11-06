<?php
//  Author: iPlussoft, Brunei. http://www.iplussoft.com
//  ###do not edit beyond this line###

$importerinfo = array();

require("config.php");

$lib = stripslashes(@$_GET['lib']);
$req = stripslashes(@$_GET['req']);
$response = null;
$iname = null;

foreach ($importerinfo[1] as $name => $path) {
    if ($lib == $path[1]) {
        $iname = $name;
        break;
    }
}

if (! $iname) {
    $addons = '';

    foreach ($importerinfo[1] as $name => $path) {
        if (@file_exists($path[1])) {
            $addons .= '<a onclick="get_response_ref(\'ajax_ct.php?lib=' . urlencode($path[1]) . '&req=1\'); return false;" href="#"><img
                alt="' . htmlspecialchars($name) . '" title="'.htmlspecialchars($name) . '" width="89" height="39" src="' . $path[0] . '.png" /></a>';
        }
    }

    $response = '<h1>Lütfen kullandığınız email<br/>servisini seçiniz.</h1><div id="addons">'.$addons.'</div>';
} else {
    require($importerinfo[1][$iname][1]);

    $response = '<h1>Bilgilerinizi girip bir sonraki<br/>adıma geçiniz</h1>
        <form onsubmit="javascript: document.getElementById(\'status\').innerHTML = \'<img align=absmiddle src=ajax-loader2.gif />\';" method="POST" enctype="multipart/form-data" action="ajax_import.php?lib='.urlencode($importerinfo[1][$iname][1]).'">
            <table border="0" cellpadding="2" width="100%" cellspacing="2" style="margin:20px 0">
                <tr>
                    <td width="240" style="font:normal 12px/14px arial,helvetica,sans-serif; text-align:right">
                        <label for="username">Email Adresi:</label>
                    </td>
                    <td>
                        <input type="text" id="username" name="username" style="width:160px; border:1px solid #999; padding:2px" value="">
                    </td>
                </tr>
                <tr>
                    <td width="240" style="font:normal 12px/14px arial,helvetica,sans-serif; text-align:right">
                        <label for="password">Şifre:</label>
                    </td>
                    <td><input type="password" id="password" name="password" style="width:160px; border:1px solid #999; padding:2px" value=""></td>
                </tr>
                <tr>
                    <td width="240"></td>
                    <td><input type="submit" id="submitButton" onmouseover="javascript:className = \' ajax_hover submit\';" onmouseout="javascript:className = \'submit button\';" class="submit button" name="Import" value="DAVET GÖNDERME ADIMINA GEÇ"/>&nbsp;&nbsp;<span id="status"></span></td>
                </tr>
                <tr>
                    <td width="240"></td>
                    <td><span style="font:normal 10px/12px arial,helvetica,sans-serif">Butigo bilgi güvenliğine önem vermektedir.<br/>Sunucularımızda hiç bir bilgi saklanmamaktadır.</span></td>
                </tr>
            </table>
        </form>';
}

?>

<html>
    <head>
        <style type="text/css">
            h1 {
                background: #F2018a;
                font: normal 20px/24px Helvetica, Arial, Verdana, sans-serif;
                border-bottom: 1px solid #01BFF7;
                margin: 0;
                padding: 15px 5px;
                text-align: center;
                color:#fff
            }

            img {
                border: 0
            }

            #addons {
                float: left;
                margin: 5px 123px
            }

            #addons a {
                display: block;
                float: left;
                margin: 5px
            }

            #ajax {
                float: left;
                width: 100%
            }

            input.submit {
                cursor: pointer;
                display: inline-block;
                font: bold 13px/100% Arial, Helvetica, sans-serif;
                outline: medium none;
                text-align: center;
                text-decoration: none;
                float: left;
                height: 32px;
                width: auto;
                padding: 0 1em 0 1em;
                border: none;
            }

            .ajax_hover {
                background-color: #21c7f7;
                color: #fff;
                background: -webkit-gradient(linear, left top, left bottom, from(#21c7f7), to(#21c7f7));
                background: -moz-linear-gradient(top,  #21c7f7,  #21c7f7);
                filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#21c7f7', endColorstr='#21c7f7');
            }

            input.button{
                color: #fff;
                background: -webkit-gradient(linear, left top, left bottom, from(#5ddbff), to(#21c7f7));
                background: -moz-linear-gradient(top,  #5ddbff,  #21c7f7);
                filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#5ddbff', endColorstr='#21c7f7');
            }

            input.button:hover, .ajax_hover{
                background-color: #21c7f7;
            }

            input.button:active {
                color: #fff;
                background: -webkit-gradient(linear, left top, left bottom, from(#5ddbff), to(#5ddbff));
                background: -moz-linear-gradient(top,  #5ddbff,  #5ddbff);
                filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#5ddbff', endColorstr='#5ddbff');
            }
        </style>
        <script type="text/javascript">
            var req = <?php echo ((! empty($req)) ? 1 : 0) ?>;

            function handle_response_ref(doc, obj, dv) {
                var data = doc.getElementById(obj);
                document.getElementById(dv).innerHTML = data.innerHTML;
            }

            function get_response_ref(url) {
                document.getElementById('ajax').innerHTML = '<div align="center">Yükleniyor...<br/><img src="ajax-loader.gif"></div>';
                document.getElementById('loader').innerHTML = '<iframe src="' + url + '" style="width: 1px; height: 1px;"></iframe>';
            }
        </script>
    </head>
    <body onload="if (req && (typeof parent.handle_response_ref == 'function')) { parent.handle_response_ref(document, 'responsedata', 'ajax'); }"
        marginwidth="0" marginheight="0" leftmargin="0" topmargin="0" bgcolor="#FFFFFF">
        <div id="responsedata"><?php echo $response;?></div>
        <div id="ajax"></div>
        <div id="loader" name="loader" style="display: none; width: 1px; height: 1px;">&nbsp;</div>
    </body>
</html>
