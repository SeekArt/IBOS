<!-- 共享人员设置 -->
<div style="width: 420px">
    <input type="text" id="fc_share_to" value="<?php echo $shares ?>"/>
    <div id="fc_share_to_box"></div>
</div>
<script>
    $("#fc_share_to").userSelect({
        data: Ibos.data.get(),
        clearable: false
    });
</script>