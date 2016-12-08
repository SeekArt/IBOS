<html>
<head>
    <title>选择模版</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <style>
        .menulines {
        }
    </style>
    <SCRIPT>
        var menu_enter = "";

        function borderize_on(e) {
            color = "#708DDF";
            source3 = event.srcElement

            if (source3.className == "menulines" && source3 != menu_enter)
                source3.style.backgroundColor = color;
        }

        function borderize_on1(e) {
            for (i = 0; i < document.all.length; i++) {
                document.all(i).style.borderColor = "";
                document.all(i).style.backgroundColor = "";
                document.all(i).style.color = "";
                document.all(i).style.fontWeight = "";
            }

            color = "#003FBF";
            source3 = event.srcElement

            if (source3.className == "menulines") {
                source3.style.borderColor = "black";
                source3.style.backgroundColor = color;
                source3.style.color = "white";
                source3.style.fontWeight = "bold";
            }

            menu_enter = source3;
        }

        function borderize_off(e) {
            source4 = event.srcElement

            if (source4.className == "menulines" && source4 != menu_enter) {
                source4.style.backgroundColor = "";
                source4.style.borderColor = "";
            }
        }
    </SCRIPT>
    <script Language="JavaScript">
        var parent_window = window.opener;
        function click_model(ID) {
            parent_window.officeOcx.addDocHeader(ID);
            window.close();
        }
    </script>
</head>
<body class="bodycolor" topmargin="5">
<?php
$I = 0;
if ($handle = opendir('../')) {
    while (false !== ($file = readdir($handle))) {
        if (strtolower(substr($file, -4)) == ".dot") {
            $MODEL_ARRAY[$I++] = substr($file, 0, -4);
        }
    }
    closedir($handle);
}
if (sizeof($MODEL_ARRAY) > 0) {
    sort($MODEL_ARRAY);
    reset($MODEL_ARRAY);
}
?>
<table class="TableBlock" width="100%" onMouseover="borderize_on(event)" onMouseout="borderize_off(event)"
       onclick="borderize_on1(event)">
    <tr class="TableHeader">
        <td align="center"><b>选择模版</b></td>
    </tr>
    <?php for ($I = 0; $I < sizeof($MODEL_ARRAY); $I++): ?>
        <tr class="TableData">
            <td class="menulines" align="center"
                onclick=javascript:click_model('<?php echo urlencode($MODEL_ARRAY[$I]); ?>') style="cursor:hand">
                <?php echo iconv('gbk', 'utf-8', $MODEL_ARRAY[$I]); ?>
            </td>
        </tr>
    <?php endfor; ?>
</table>
<?php if ($I == 0): echo "没有定义模版"; ?><?php endif; ?>
</body>
</html>

