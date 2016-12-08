<!-- load css -->
<link rel="stylesheet" href="<?php echo STATICURL; ?>/css/base.css?<?php echo VERHASH; ?>">
<form action="<?php echo $this->createUrl('im/syncoa'); ?>" enctype="multipart/form-data" id="import_form" method="post"
      class="prs pls">
    <div><input type="file" id='rtx_init_file' name="xml" class="mts"></div>
    <div><input type="text" name="pwd" class="mbs mts" placeholder="用户初始化密码"></div>
    <div>
        <button type="submit" class="btn mb">开始导入RTX</button>
    </div>
    <input type="hidden" name="formhash" value="<?php echo FORMHASH; ?>"/>
</form>