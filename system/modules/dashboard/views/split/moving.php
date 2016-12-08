<div class="ct">
    <!-- 运行记录 start -->
    <div>
        <form id="submit_form" action="<?php echo $url; ?>" method="post" class="form-horizontal">
            <div class="ctb">
                <h2 class="st">移动中</h2>
                <p><?php echo $message ?></p>
                <p><span id="wait" class='badge badge-info' r>3</span></p>
            </div>
        </form>
    </div>
</div>
<script>
    (function () {
        var wait = document.getElementById('wait'),
            interval = setInterval(function () {
                var time = --wait.innerHTML;
                if (time === 0) {
                    $('#submit_form').submit();
                    clearInterval(interval);
                }
            }, 1000);
    })();
</script>