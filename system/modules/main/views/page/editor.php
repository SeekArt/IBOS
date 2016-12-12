<div class="page-edit">
    <div class="edit-area bds radius" id="edit_area">
        <script id="editor" name="content" type="text/plain"><?php echo $page['content']; ?></script>
    </div>
    <div class="fixed-footer">
        <div class="footer-bg"></div>
        <div class="footer-content clearfix">
            <div class="pull-left">
                <button type="button" class="btn" data-action="close">关闭</button>
            </div>
            <div class="pull-right">
                <div class="btn-group dropup">
                    <button type="button" class="btn dropdown-toggle toggle-all-btn" data-toggle="dropdown">
                        选择模版
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu" id="moduel-type" role="menu">
                        <?php foreach ($tpls as $tpl => $name): ?>
                            <li <?php if ($page['template'] == $tpl): ?>class="active"<?php endif; ?>><a
                                    href="javascript:;" data-type="<?php echo $tpl; ?>"
                                    data-value="<?php echo $tpl; ?>"><?php echo $name; ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <input type="hidden" id="pageid" value="<?php echo $page['id']; ?>">
                <button type="button" class="btn" data-action="preview">预览内容</button>
                <button type="button" class="btn btn-primary" data-action="save">确认保存</button>
            </div>
        </div>
    </div>
</div>