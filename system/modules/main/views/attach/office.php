<?php
use application\core\utils\Ibos;

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="<?php echo CHARSET; ?>">
    <title><?php echo ($param['op'] == 'edit' ? $lang['Online edit'] : $lang['Online read']) . '--' . $fileName; ?></title>
    <link href="<?php echo STATICURL; ?>/css/base.css?<?php echo VERHASH; ?>" type="text/css" rel="stylesheet"/>
    <link href="<?php echo STATICURL; ?>/css/common.css?<?php echo VERHASH; ?>" type="text/css" rel="stylesheet"/>
    <link rel="stylesheet" href="<?php echo STATICURL; ?>/js/lib/artDialog/skins/ibos.css?<?php echo VERHASH; ?>"/>
    <link href="<?php echo $assetUrl; ?>/css/doc_view.css?<?php echo VERHASH; ?>" type="text/css" rel="stylesheet"/>
</head>
<body onload="loaddoc();">
<div id="office_viewer">
    <form action="<?php echo Ibos::app()->createUrl('main/attach/office'); ?>" id="office_form"
          enctype="multipart/form-data" method="post">
        <table width="100%" height="100%" cellspacing="1" cellpadding="3">
            <tbody>
            <tr width="100%">
                <td width="100" valign="top">
                    <div class="fun-sidebar">
                        <?php if ($param['op'] == 'edit'): ?>
                            <div class="mb">
                                <div class="mb ovh posr">
                                    <span
                                        class="fun-title"><?php echo $lang['File operations']; //文件操作                          ?></span>
                                    <span class="fun-divider"></span>
                                </div>
                                <button type="button" data-action="save" data-param='{"flag":0}'
                                        class="btn btn-primary db fun-btn mbs"><?php echo $lang['Save the file']; //保存文件                          ?></button>
                                <button type="button" data-action="chgLayout"
                                        class="btn fun-btn db mbs"><?php echo $lang['Page setup']; //页面设置                          ?></button>
                                <button type="button" data-action="print"
                                        class="btn fun-small-btn mbs"><?php echo $lang['Print']; //打印                          ?></button>
                                <button type="button" data-action="export"
                                        class="btn fun-small-btn mbs"><?php echo $lang['Export pdf']; //导出pdf                          ?></button>
                                <button type="button" data-action="showLog"
                                        class="btn fun-small-btn mbs disabled"><?php echo $lang['Operation log']; //操作日志 @banyan 暂时没有做这个功能   ?></button>
                            </div>
                        <?php endif; ?>
                        <?php if ($typeId != '5'): //如果文档类型不为PPT ?>
                            <?php if ($param['op'] == 'edit' && $typeId != '4'): ?>
                                <div class="mb">
                                    <div class="mb ovh posr">
                                        <span
                                            class="fun-title"><?php echo $lang['File edit']; //文件编辑                          ?></span>
                                        <span class="fun-divider"></span>
                                    </div>
                                    <div class="clearfix">
                                        <div class="pull-right">
                                            <label class="checkbox checkbox-inline">
                                                <input data-action="setMarkModify" checked type="checkbox" class="ml">
                                            </label>
                                        </div>
                                        <div class="pull-left choose-tip">
                                            <span><?php echo $lang['Keep track']; // 保留痕迹              ?></span>
                                        </div>
                                    </div>
                                    <div class="clearfix">
                                        <div class="pull-right">
                                            <label class="checkbox checkbox-inline">
                                                <input data-action="showRevisions" checked type="checkbox" class="ml">
                                            </label>
                                        </div>
                                        <div class="pull-left choose-tip">
                                            <span><?php echo $lang['According trace']; // 显示痕迹                         ?></span>
                                        </div>
                                    </div>
                                    <button type="button" data-action="selectWord"
                                            class="btn fun-btn db mbs"><?php echo $lang['File set']; //插入套红                         ?></button>
                                    <button type="button" data-action="addPictureFromLocal"
                                            class="btn fun-btn db"><?php echo $lang['Insert picture']; //插入图片                         ?></button>
                                </div>
                            <?php endif; ?>
                            <div>
                                <div class="mb ovh posr">
                                    <span
                                        class="fun-title"><?php echo $lang['Digital certification']; // 电子认证                        ?></span>
                                    <span class="fun-divider"></span>
                                </div>
                                <button data-action="checkSign" data-param='{"key":"<?php echo $param['aid']; ?>"}'
                                        type="button"
                                        class="btn fun-large-btn db mbs"><?php echo $lang['Verify the signature and seal']; // 验证签名及印章                        ?></button>
                                <?php if ($param['op'] == 'edit'): ?>
                                    <button type="button" data-action="fullHandSign"
                                            data-param='{"key":"<?php echo $param['aid']; ?>"}'
                                            class="btn fun-normal-btn db mbs"><?php echo $lang['Full-screen handwriting signature']; // 全屏手写签名                        ?></button>
                                    <button type="button" data-action="fullHandDraw"
                                            class="btn fun-normal-btn db mbs"><?php echo $lang['Full screen manual drawing']; // 全屏手工绘图                        ?></button>
                                    <button type="button" data-action="handSign"
                                            data-param='{"key":"<?php echo $param['aid']; ?>"}'
                                            class="btn fun-normal-btn db mbs"><?php echo $lang['Insert the handwritten signature']; // 插入手写签名                        ?></button>
                                    <button type="button" data-action="handDraw"
                                            class="btn fun-normal-btn db mbs"><?php echo $lang['Insert the manual drawing']; // 插入手工绘图                       ?></button>
                                    <button type="button" data-action="addSignFromLocal"
                                            data-param='{"key":"<?php echo $param['aid']; ?>"}'
                                            class="btn fun-normal-btn db mbs"><?php echo $lang['Add local electronic seal']; // 本地电子盖章                       ?></button>
                                    <!--<button type="button" data-action="addSignFromServer" data-param='{"key":"<?php echo $param['aid']; ?>"}' class="btn fun-large-btn db mbs"><?php echo $lang['Add server electronic seal']; // 服务器电子盖章                       ?></button>-->
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </td>
                <td width="100%" valign="top">
                    <!-- 控件放置容器 -->
                    <div class="view-area" style="z-index: 2;">
                        <object width="100%" height="100%" id="OCX" classid="clsid:C9BC4DFF-4248-4a3c-8A49-63A7D317F404"
                                codebase="<?php echo STATICURL; ?>/office/OfficeControl.cab#version=5,0,2,9">
                            <?php if ($param['op'] == 'edit'): ?>
                                <param name="IsNoCopy" value="0">
                                <param name="FileSave" value="1">
                                <param name="FileSaveAs" value="1">
                            <?php else: ?>
                                <param name="IsNoCopy" value="1">
                                <param name="FileSave" value="0">
                                <param name="FileSaveAs" value="0">
                                <param name="FileProperties" value="0">
                                <param name="Menubar" value="0">
                                <param name="ShowCommandBar" value="0">
                            <?php endif; ?>
                            <param name="FullScreenMode" value="1">
                            <param name="BorderStyle" value="1">
                            <param name="BorderColor" value="14402205">
                            <param name="TitlebarColor" value="14402205">
                            <param name="TitlebarTextColor" value="0">
                            <param name="Caption" value="<?php echo $fileName; ?>">
                            <param name="IsShowToolMenu" value="-1">
                            <param name="IsHiddenOpenURL" value="0">
                            <param name="IsUseUTF8URL" value="-1">
                            <param name="MakerCaption" value="深圳市博思协创网络科技有限公司">
                            <param name="MakerKey" value="E240360396592737B74C104BDC507551A9982622">
                            <param name="ProductCaption" value="<?php echo $licence['ProductCaption']; ?>">
                            <param name="ProductKey" value="<?php echo $licence['ProductKey']; ?>">
                            <div class="ocx-error-tip">
                                <div class="dib">
                                    <img src="<?php echo $assetUrl; ?>/image/lightbulbman.png"/>
                                </div>
                                <div class="dib mll">
                                    <p class="fsl xcm mbs"><?php echo $lang['Ocx error']; ?></p>
                                    <p class="mb xal xcg"><?php echo $lang['Ocx error desc'] ?></p>
                                </div>
                            </div>
                        </object>
                        <div align="center" id="ocxlog" style="display: none;"></div>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
        <input type="file" name="Filedata" style="display:none;">
        <input type="hidden" name="formhash" value="<?php echo FORMHASH; ?>"/>
    </form>
</div>
<script src="<?php echo STATICURL; ?>/js/src/core.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo STATICURL; ?>/js/src/base.js?<?php echo VERHASH; ?>"></script>
<script src='<?php echo STATICURL; ?>/js/lib/artDialog/artDialog.min.js?<?php echo VERHASH; ?>'></script>
<script src="<?php echo STATICURL; ?>/js/src/common.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo $assetUrl; ?>/js/main_attach_office.js?<?php echo VERHASH; ?>"></script>
<script>
    function loaddoc() {
        var settings = {
            obj: document.getElementById('OCX'),
            logobj: document.getElementById('ocxlog'),
            op: '<?php echo $param['op']; ?>',
            attachUrl: '<?php echo $fileUrl; ?>',
            actionUrl: $('#office_form').attr('action'),
            pathUrl: '<?php echo $officePath; ?>',
            attachName: '<?php echo $fileName; ?>',
            fileName: '<?php echo $fileName; ?>',
            user: '<?php echo Ibos::app()->user->realname; ?>',
            staticurl: '<?php echo STATICURL; ?>/office/'
        };

        window.officeOcx = new OCX(settings);

        $(window).on("unload", function () {
            officeOcx.settings.obj.setAttribute('IsNoCopy', false);
            if (officeOcx.settings.op == 'edit') {
                if (officeOcx.settings.docOpen) {
                    Ui.confirm('是否保存对 ' + officeOcx.settings.fileName + '的修改？', function () {
                        officeOcx.saveDoc(0);
                    });
                }
            }
        });
    }
    $(document).ready(function () {
        var height = $(window).height() - 9;
        $('.view-area').css({height: height});
    });
</script>
</body>
</html>
