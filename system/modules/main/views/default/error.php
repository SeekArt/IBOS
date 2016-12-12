<style type="text/css">
    <!--
    #container {
        width: 1024px;
    }

    a:link {
        font: 9pt/11pt verdana, arial, sans-serif;
        color: red;
    }

    a:visited {
        font: 9pt/11pt verdana, arial, sans-serif;
        color: #4e4e4e;
    }

    h1 {
        color: #FF0000;
        font: 18pt "Verdana";
        margin-bottom: 0.5em;
    }

    .info {
        background: none repeat scroll 0 0 #F3F3F3;
        border: 0px solid #aaaaaa;
        border-radius: 10px 10px 10px 10px;
        color: #000000;
        font-size: 11pt;
        line-height: 160%;
        margin-bottom: 1em;
        padding: 1em;
    }

    -->
</style>
<div id="container">
    <h1>IBOS System Error</h1>
    <? if (isset($error)): ?>
        <div class='info'><?php echo $error ?></div>
    <? endif; ?>
</div>