<!-- load css -->
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/email.css?<?php echo VERHASH; ?>">
<!-- Mainer -->
<div class="mc clearfix">
    <!-- Sidebar -->
    <?php echo $this->getSidebar(); ?>
    <!-- Mainer right -->
    <div class="mcr">
        <div class="page-list">
            <div class="page-list-header">
                <div class="btn-toolbar pull-left">
                    <div class="btn-group">
                        <a href="javascript:;" class="btn btn-narrow ">
                            <label class="checkbox">
                                <input type="checkbox" data-name="email">
                            </label>
                        </a>
                        <a href="javascript:;" class="btn dropdown-toggle" data-toggle="dropdown">
                            <i class="caret"></i>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="javascript:;"
                                   data-click="selectInvert"><?php echo $lang['Reverse selected']; ?></a></li>
                        </ul>
                    </div>
                    <div class="btn-group">
                        <a href="<?php echo $this->createUrl('web/add'); ?>"
                           class="btn btn-primary"><?php echo $lang['Add web mail']; ?></a>
                    </div>
                    <div class="btn-group">
                        <button type="button" class="btn" data-click="deleteWebMailBox"
                                data-param="{&quot;url&quot;: &quot;<?php echo $this->createUrl('web/del'); ?>&quot;}"><?php echo $lang['Delete']; ?></button>
                    </div>
                </div>
            </div>
            <div class="page-list-mainer">
                <table class="table table-striped table-hover">
                    <thead>
                    <tr>
                        <th width="10%"></th>
                        <th width="30%"><?php echo $lang['Web mail account']; ?></th>
                        <th width="10%"><?php echo $lang['Web mail nickname']; ?></th>
                        <th width="20%" colspan="2"><?php echo $lang['Operation']; ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($list as $data): ?>
                        <tr id="list_tr_<?php echo $data['webid']; ?>">
                            <td width="10%">
                                <label class="checkbox">
                                    <input type="checkbox" name="email" value="<?php echo $data['webid']; ?>">
                                </label>
                            </td>
                            <td width="30%">
                                <a href="<?php echo $this->createUrl('list/index', array('op' => 'folder', 'fid' => $data['fid'])); ?>"
                                   class="art-list-title">
                                    <?php echo $data['address']; ?>
                                </a>
                            </td>
                            <td width="30%">
                                <span class="art-list-title"><?php echo $data['nickname']; ?></span>
                            </td>
                            <td width="10%">
                                <a href="<?php echo $this->createUrl('web/edit', array('id' => $data['webid'])); ?>"
                                   title="<?php echo $lang['Setup']; ?>"><?php echo $lang['Setup']; ?></a>
                            </td>
                            <td width="20%">
                                <a href="javascript:;"
                                   class="mal-default-webmail <?php if ($data['isdefault']): ?>active<?php endif; ?>"
                                   title="<?php if ($data['isdefault']): ?><?php echo $lang['Default']; ?><?php else: ?><?php echo $lang['Set default']; ?><?php endif; ?>"
                                   data-click="setDefaultWebMailBox" data-isDefault="<?php echo $data['isdefault']; ?>"
                                   data-param="{
									   &quot;id&quot;: &quot;<?php echo $data['webid']; ?>&quot;,&quot;url&quot;: &quot;<?php echo $this->createUrl('web/edit', array('op' => 'setDefault')); ?>&quot;}"><?php if ($data['isdefault']): ?><?php echo $lang['Default']; ?><?php else: ?><?php echo $lang['Set default']; ?><?php endif; ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="page-list-footer">
                <div class="pull-right">
                    <?php $this->widget('application\core\widgets\Page', array('pages' => $pages)); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script src='<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/email.js'></script>

