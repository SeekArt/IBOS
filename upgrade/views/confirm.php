<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>SQL执行确认</title>
    <style type="text/css">
        body, div, form, label, p, ul, ol, li { margin: 0; padding: 0; }
        .mc { position: relative; font-family: '微软雅黑'; padding: 20px 25px; }
        .xcbu { color: #3497db; }
        .xcn { color: #82939E; }
        .mb { margin-bottom: 20px; }
        .scroll::-webkit-scrollbar { width: 10px; height: 10px; }
        .scroll::-webkit-scrollbar-track { border-radius: 5px; background-color: #B2C0D1; }
        /* Handle */
        .scroll::-webkit-scrollbar-thumb { border-radius: 5px; border: 2px solid #B2C0D1; background-color: #f5f5f5; }
        .scroll::-webkit-scrollbar-thumb:hover { background-color: #fff; }
        .scroll::-webkit-scrollbar-button { width: 0; height: 0; background-color: transparent; }

        .upgrade-title { margin-bottom: 20px; font-size: 20px; }
        .upgrade-detail { margin-bottom: 40px; }
        .upgrade-content { position: relative; height: 340px; overflow: auto; font-size: 14px; line-height: 20px; }
    </style>
</head>
<body>
    <div class="mc">
        <p class="upgrade-title xcbu">代码升级成功</p>
        <p class="upgrade-detail xcn">
            原先版本&nbsp;
            <span class="xcbu"><?php echo $fromVersion; ?></span>
            &nbsp;升级到&nbsp;
            <span class="xcbu"><?php echo end($crossVersion); ?></span>
        </p>
        <p class="mb">确认要执行的SQL语句</p>
        <div class="upgrade-content xcn scroll">
            <pre><?php echo $confirmContent; ?></pre>
        </div>
        <input type="hidden" name="fromVersion" value="<?php echo $fromVersion; ?>">
        <input type="hidden" name="toVersion" value="<?php echo end($crossVersion); ?>">
    </div>
</body>
</html>