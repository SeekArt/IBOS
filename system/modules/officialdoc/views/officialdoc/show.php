<?php

use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\user\model\User;

?>
<!-- load css -->
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/officialdoc.css">
<link rel="stylesheet" href="<?php echo STATICURL; ?>/css/emotion.css?<?php echo VERHASH; ?>">
<!-- load css end-->

<!-- Mainer -->
<div class="mc clearfix">
    <!-- Sidebar -->
    <?php echo $this->getSidebar($this->catId); ?>
    <!-- Sidebar -->

    <!-- Mainer right -->
    <div class="mcr">
        <form action="" class="form-horizontal">
            <div class="ct ctview ctview-art">
                <!-- 文章 -->
                <div class="art">
                    <div class="art-container">
                        <a href="javascript:"
                           <?php if ((!empty($signInfo) && $signInfo['issign'] == 1) || $data['status'] == 2): ?>onclick="window.location.href = document.referrer;"
                           <?php else: ?>id="art_close"<?php endif; ?> title="<?php echo $lang['Close']; ?>"
                           class="art-close"></a>
                        <h1 class="art-title ellipsis"><?php echo $data['subject']; ?></h1>
                        <!-- 套红 -->
                        <div class="mb art-content text-break" id="art_content">
                            <div class="officialdoc-content">
                                <?php echo $data['content']; ?>
                            </div>
                        </div>
                        <!--附件显示 start -->
                        <?php if (isset($attach)): ?>
                            <div class="fill noprint">
                                <h3 class="ctbt">
                                    <i class="o-paperclip"></i>
                                    <strong>附件</strong>（<?php echo count($attach); ?>个）
                                </h3>
                                <ul class="attl">
                                    <?php foreach ($attach as $fileInfo): ?>
                                        <li>
                                            <i class="atti">
                                                <img src="<?php echo $fileInfo['iconsmall']; ?>"
                                                     alt="<?php echo $fileInfo['filename']; ?>">
                                            </i>
                                            <div class="attc">
                                                <div class="mbm">
                                                    <?php echo $fileInfo['filename']; ?>
                                                    <span class="tcm">(<?php echo $fileInfo['filesize']; ?>)</span>
                                                </div>
                                                <span class="fss">
                                                    <a href="<?php echo $fileInfo['downurl']; ?>" class="anchor">下载</a>&nbsp;&nbsp;
                                                    <?php if (isset($fileInfo['officereadurl'])): ?>
                                                        <a href="javascript:;" data-action="viewOfficeFile"
                                                           data-param='{"href": "<?php echo $fileInfo['officereadurl']; ?>"}'
                                                           title="<?php echo $lang['View']; ?>">
                                                            <?php echo $lang['View']; ?>
                                                        </a>
                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        <!--附件显示 end -->
                        <div class="clearfix fill-zn art-funbar">
                            <div class="pull-right">
                                <?php if ($data['status'] == 2): ?>
                                    <div class="approval-flow">
                                        <span class="flow-name xwb"><?php echo $data['approvalName']; ?></span>
                                        <i class="o-art-description" data-toggle="tooltip"
                                           data-original-title="审批规则"></i>
                                        <div class="dib mls">
                                            <ul class="list-inline flow-ul">
                                                <?php for ($i = 1; $i <= $data['approval']['level']; $i++): ?>
                                                    <li>
                                                        <?php if ($i > 1): ?>
                                                            <span
                                                                class="<?php if ($data['stepNum'] >= $i): ?>o-allow-line<?php else: ?>o-noallow-line<?php endif; ?>"></span>
                                                        <?php endif; ?>
                                                        <span data-toggle="tooltip"
                                                              data-original-title="审核人:<?php echo $data['approval'][$i]['approvaler']; ?>"
                                                              class="<?php if ($data['stepNum'] >= $i): ?>o-allow-circle<?php else: ?>o-noallow-circle<?php endif; ?>"><?php echo $i; ?></span>
                                                    </li>
                                                <?php endfor; ?>
                                            </ul>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="pull-left">
                                <?php if ($data['version'] == $this->getNewestVerByDocid($data['docid'])): ?>
                                    <?php if (empty($signInfo) || $signInfo['issign'] == 0): ?>
                                        <div class="sign-area">
                                            <?php if ($data['status'] == 1) : ?>
                                                <?php if ($needSign): ?>
                                                    <button type="button" class="btn btn-large btn-danger sign-btn"
                                                            data-action="signDoc" id="sign_btn">
                                                        <i class="o-art-immediately-sign"></i>
                                                        <span class="dib fsl"><?php echo $lang['Now sign']; ?></span>
                                                    </button>
                                                    <a href="javascript:;" class="anchor ilsep"
                                                       data-action="signNextTime"><?php echo $lang['Next reminder']; ?></a>
                                                <?php endif; ?>
                                            <?php elseif (isset($isApprovaler) && $isApprovaler): ?>
                                                <button type="button" class="btn btn-large btn-primary"
                                                        data-action="approvalDoc">审核通过
                                                </button>
                                                <button type="button" class="btn btn-large" data-action="rollbackDoc">
                                                    退回
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <div>
                                            <button type="button" class="btn btn-large" disabled="disabled">
                                                <i class="o-art-handel-sign"></i>
                                                <span
                                                    class="dib fsl"><?php echo $lang['You have to sign this document']; ?></span>
                                            </button>
                                            <span
                                                class="dib mls"><?php echo $lang['Sign time']; ?><?php echo date('Y年m月d日 H:i', $signInfo['signtime']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="art-halving-line"></div>
                    <div class="art-desc mb noprint">
                        <ul class="art-desc-list">
                            <li>
                                <strong><?php echo $lang['News']; ?></strong>
                                <div class="art-desc-body">
                                    <?php echo User::model()->fetchRealnameByUid($data['author']); ?>
                                    <span class="ilsep"><?php echo $lang['Posted on']; ?></span>
                                    <?php echo $data['addtime']; ?>
                                    <span class="ilsep">|</span>
                                    <?php echo $lang['Approver']; ?>：<?php echo $data['approver']; ?>
                                    <span class="ilsep">|</span>
                                    <?php if (!empty($data['uptime'])): ?>
                                        <?php echo $lang['Update on']; ?><?php echo $data['uptime']; ?>
                                        <span class="ilsep">|</span>
                                    <?php endif; ?>
                                    <?php echo $lang['Version']; ?>：<?php echo $data['showVersion']; ?>
                                    <span class="ilsep">|</span>
                                    <?php echo $lang['Category']; ?>：<?php echo $data['categoryName']; ?>
                                </div>
                            </li>
                            <li>
                                <strong><?php echo $lang['Scope']; ?></strong>
                                <div class="art-desc-body">
                                    <?php if (!empty($data['departmentNames'])): ?>
                                        <i class="os-department"></i><?php echo $data['departmentNames']; ?>&nbsp;
                                    <?php endif; ?>
                                    <?php if (!empty($data['positionNames'])): ?>
                                        <i class="os-position"></i><?php echo $data['positionNames']; ?>&nbsp;
                                    <?php endif; ?>
                                    <?php if (!empty($data['uidNames'])): ?>
                                        <i class="os-user"></i><?php echo $data['uidNames']; ?>
                                    <?php endif; ?>
                                    <?php if (!empty($data['roleNames'])): ?>
                                        <i class="os-role"></i><?php echo $data['roleNames']; ?>
                                    <?php endif; ?>
                                </div>
                            </li>
                            <li>
                                <strong><?php echo $lang['Cc']; ?></strong>
                                <div class="art-desc-body">
                                    <?php if (!empty($data['ccDepartmentNames'])): ?>
                                        <i class="os-department"></i><?php echo $data['ccDepartmentNames']; ?>&nbsp;
                                    <?php endif; ?>
                                    <?php if (!empty($data['ccPositionNames'])): ?>
                                        <i class="os-position"></i><?php echo $data['ccPositionNames']; ?>&nbsp;
                                    <?php endif; ?>
                                    <?php if (!empty($data['ccUidNames'])): ?>
                                        <i class="os-user"></i><?php echo $data['ccUidNames']; ?>
                                    <?php endif; ?>
                                    <?php if (!empty($data['ccRoleNames'])): ?>
                                        <i class="os-role"></i><?php echo $data['ccRoleNames']; ?>
                                    <?php endif; ?>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <?php if ($data['status'] != 2): ?>
                        <!-- 查看非历史版本才加载显示tab -->
                        <?php if ($data['version'] == $this->getNewestVerByDocid($data['docid'])): ?>
                            <div class="noprint">
                                <ul class="nav nav-skid embeded art-related-nav" id="art_related_nav">
                                    <?php if ($dashboardConfig['doccommentenable'] && $data['commentstatus']): ?>
                                        <li class="active">
                                            <a href="#comment" data-toggle="tab">
                                                <i class="o-art-comment"></i>
                                                <?php echo $lang['Comment']; ?>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    <li>
                                        <a href="#isread" id="isread_tab" data-toggle="tab">
                                            <i class="o-art-isread"></i>
                                            <?php echo $lang['Sign isread']; ?>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#issign" id="sign_tab" data-toggle="tab">
                                            <i class="o-art-issign"></i>
                                            <?php echo $lang['Sign staff']; ?>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#isnosign" id="no_sign_tab" data-toggle="tab">
                                            <i class="o-art-isnosign"></i>
                                            <?php echo $lang['Unsign staff']; ?>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#version" id="version_tab" data-toggle="tab">
                                            <i class="o-art-version"></i>
                                            <?php echo $lang['History version']; ?>
                                        </a>
                                    </li>
                                </ul>
                                <div class="tab-content">
                                    <!-- 评论 -->
                                    <?php if ($dashboardConfig['doccommentenable'] && $data['commentstatus']): ?>
                                        <div id="comment" class="comment fill-zn tab-pane active">
                                            <?php
                                            $sourceUrl = Ibos::app()->urlManager->createUrl('officialdoc/officialdoc/show', array('docid' => $data['docid']));
                                            $this->widget('application\modules\officialdoc\core\OfficialdocComment', array(
                                                'module' => 'officialdoc',
                                                'table' => 'officialdoc',
                                                'attributes' => array(
                                                    'rowid' => $data['docid'],
                                                    'moduleuid' => Ibos::app()->user->uid,
                                                    'touid' => $data['author'],
                                                    'module_rowid' => $data['docid'],
                                                    'module_table' => 'officialdoc',
                                                    'url' => $sourceUrl,
                                                    'detail' => Ibos::lang('Comment my doc', '', array('{url}' => $sourceUrl, '{title}' => StringUtil::cutStr($data['subject'], 50)))
                                                )));
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                    <!-- 查阅情况 -->
                                    <div id="isread" class="tab-pane">
                                        <div class="sign-info-title fill-nn">
                                            共<span class="fsl num">0</span> 人已查阅
                                        </div>
                                        <div>
                                            <h5 class="doc-reader-dep">已查阅人员</h5>
                                            <ul class="doc-reader-list clearfix"></ul>
                                        </div>
                                    </div>
                                    <!-- 签收人情况 -->
                                    <div id="issign" class="tab-pane">

                                    </div>
                                    <!-- 未签收情况 -->
                                    <div id="isnosign" class="tab-pane">

                                    </div>
                                    <!-- 历史版本 -->
                                    <div id="version" class="tab-pane"></div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            <input type="hidden" name="docid" id="docid" value="<?php echo $data['docid']; ?>">
            <input type="hidden" name="relatedid" id="relatedid" value="<?php echo $data['docid']; ?>">
        </form>
    </div>
</div>

<!--退回-->
<div id="rollback_reason" style="display:none;">
    <form action="javascript:;" method="post" id="rollback_form">
        <textarea rows="8" cols="60" name="reason" id="rollback_textarea" placeholder="退回理由...."></textarea>
    </form>
</div>
<!-- Template: 历史版本表格 -->
<script type="text/template" id="tpl_version_table">
    <ul class="version-list">
        <% if(versions && versions.length) { %>
        <% for(var i = 0; i < versions.length; i++){ %>
        <li>
            <div class="clearfix">
                <div class="pull-left">
                    <a class="o-version-nub"
                       href="<%= Ibos.app.url('officialdoc/officialdoc/show', { docid : versions[i].docid, version: versions[i].version }) %>"
                       target="_blank">
                        <%= versions[i].showVersion %>
                    </a>
                </div>
                <div class="pull-left mls">
                    <p class="fss xcm"><%= versions[i].editor %>：<%= versions[i].reason %>。</p>
                    <p class="fss tcm"><%= versions[i].uptime %></p>
                </div>
            </div>
        </li>
        <% } %>
        <% } %>
    </ul>
</script>
<script>
    Ibos.app.setPageParam({
        "docId": "<?php echo $data['docid']; ?>",
        "docTitle": "<?php echo $data['subject']; ?>",
        // 后台是否启用评论
        "commentEnable": <?php echo $dashboardConfig['doccommentenable']; ?>,
        // 此通知是否允许被评论
        "commentStatus": <?php echo $data['commentstatus'] ?>,
        "readers": '<?php echo $data['readers']; ?>'
    });
</script>
<script src='<?php echo STATICURL; ?>/js/src/emotion.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/app/ibos.treeCategory.js?<?php echo VERHASH; ?>'></script>
<script src="<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo $assetUrl; ?>/js/officialdoc.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo $assetUrl; ?>/js/doc_officialdoc_show.js?<?php echo VERHASH; ?>"></script>
