<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>缓存数据更新</title>
    <!-- JS全局变量-->
    <script>
        <?php include PATH_ROOT . '/data/jsconfig.php'; ?>
    </script>
    <!-- 核心库类 -->
    <script src='<?php echo STATICURL; ?>/js/src/core.js?<?php echo VERHASH; ?>'></script>
    <script src='<?php echo STATICURL; ?>/js/src/common.js?<?php echo VERHASH; ?>'></script>
</head>
<body>
<script>
    var updatetype = ["data", "static", "module"],
        url = Ibos.app.url("main/default/update"),
        index = 0,
        body = document.getElementsByTagName("body")[0];

    sync(updatetype[index], 0);

    function sync(op, offset) {
        $.post(url, {
            op: op,
            offset: offset
        }, function (res) {
            var data = res.data;
            if (res.isSuccess) {
                body.innerHTML = res.msg;

                if (data.process == "end") {
                    index += 1;
                }

                updatetype[index] ? sync(updatetype[index], data.offset) : (function () {
                    U.setCookie((G.uid || 0) + "_update_lock", "");
                    body.innerHTML = "全部更新完成";
                })();
            }
        }, "json");
    }
</script>
</body>
</html>

