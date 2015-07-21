<form action="<?php echo $this->createUrl( 'im/syncoa' ); ?>" enctype="multipart/form-data" id="import_form" method="post">
    <div><input type="file" id='rtx_init_file' name="xml"></div>
    <div><input type="text" name="pwd" placeholder="用户初始化密码"></div>
    <div><button type="submit" class="btn">开始导入RTX</button></div>
    <input type="hidden" name="formhash" value="<?php echo FORMHASH; ?>" />
</form>