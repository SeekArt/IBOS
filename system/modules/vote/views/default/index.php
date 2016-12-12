<?php

use application\core\utils\Ibos;
use application\modules\vote\utils\VoteRoleUtil;

?>
<!-- load css -->
<link rel="stylesheet"
      href="<?php echo STATICURL; ?>/js/lib/dataTable/css/jquery.dataTables_ibos.min.css?<?php echo VERHASH; ?>">
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/vote.css?<?php echo VERHASH; ?>">
<!-- load css end-->
<!-- Mainer -->
<div class="mc clearfix" id="main">
    <!-- Sidebar -->
    <div class="aside" id="vote_nav">
        <div class="sbbf sbbf">
            <?php if (VoteRoleUtil::canPublish()) : ?>
                <div class="fill-ss">
                    <a href="<?php echo Ibos::app()->createUrl('vote/form/show'); ?>" class="btn btn-warning btn-block">
                        <i class="o-new"></i> 发起调查
                    </a>
                </div>
            <?php endif; ?>
            <ul class="nav nav-strip nav-stacked">
                <li <?php if ($type == 1): ?>class="active"<?php endif; ?>>
                    <a href="<?php echo Ibos::app()->createUrl('vote/default/index'); ?>">
                        <i class="o-vote-vote"></i>
                        <span class="mls">调查投票</span>
                    </a>
                </li>
                <?php if (VoteRoleUtil::canPublish()): ?>
                    <li <?php if ($type == 4): ?>class="active"<?php endif; ?>>
                        <a href="<?php echo Ibos::app()->createUrl('vote/default/index', array('type' => '4')); ?>">
                            <i class="o-vote-my"></i>
                            <span class="mls">我发起的</span>
                        </a>
                    </li>
                <?php endif; ?>
                <?php if (VoteRoleUtil::canManage()): ?>
                    <li <?php if ($type == 7): ?>class="active"<?php endif; ?>>
                        <a href="<?php echo Ibos::app()->createUrl('vote/default/index', array('type' => '7')); ?>">
                            <i class="o-vote-manage"></i>
                            <span class="mls">管理投票</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    <!-- Sidebar end -->
    <div class="mcr">
        <div class="mc-header">
            <!-- Mainer nav -->
            <?php if ($type == 1): ?>
                <ul class="mnv nl clearfix" id="index_tab">
                    <li class="active">
                        <a data-type="1" data-toggle="tab">
                            <i class="o-vote-has"></i> 未参与
                            <span class="bubble" id="unread_num"></span>
                        </a>
                    </li>
                    <li>
                        <a data-type="2" data-toggle="tab">
                            <i class="o-vote-no"></i> 已参与
                        </a>
                    </li>
                    <li>
                        <a data-type="3" data-toggle="tab">
                            <i class="o-vote-all"></i> 全部
                        </a>
                    </li>
                </ul>
            <?php endif; ?>
            <?php if ($type == 4): ?>
                <ul class="mnv nl clearfix" id="my_tab">
                    <li class="active">
                        <a data-type="4" data-toggle="tab">
                            <i class="o-vote-ing"></i> 进行中
                        </a>
                    </li>
                    <li>
                        <a data-type="5" data-toggle="tab">
                            <i class="o-vote-finish"></i> 已结束
                            <span class="bubble"></span>
                        </a>
                    </li>
                    <li>
                        <a data-type="6" data-toggle="tab">
                            <i class="o-vote-all"></i> 全部
                        </a>
                    </li>
                </ul>
            <?php endif; ?>
            <?php if ($type == 7): ?>
                <ul class="mnv nl clearfix" id="manage_tab">
                    <li class="active">
                        <a data-type="7" data-toggle="tab">
                            <i class="o-vote-ing"></i> 进行中
                        </a>
                    </li>
                    <li>
                        <a data-type="8" data-toggle="tab">
                            <i class="o-vote-finish"></i> 已结束
                        </a>
                    </li>
                    <li>
                        <a data-type="9" data-toggle="tab">
                            <i class="o-vote-all"></i> 全部
                        </a>
                    </li>
                </ul>
            <?php endif; ?>
        </div>
        <div class="page-list">
            <div class="page-list-header">
                <?php if ($type == 4 || $type == 7): ?>
                    <div class="btn-toolbar pull-left">
                        <button class="btn btn-default pull-left" data-action="removeVotes">删除</button>
                    </div>
                <?php endif; ?>
                <form action="javascript:;" method="post">
                    <div class="search search-config pull-right span3">
                        <input type="text" placeholder="输入标题查询" name="keyword" id="mn_search" nofocus value="">
                        <a href="javascript:;">search</a>
                        <input type="hidden" name="type" value="normal_search">
                    </div>
                </form>
            </div>
            <div class="page-list-mainer">
                <?php if ($type == 1): ?>
                    <div id="index_wrap">
                        <table class="table table-hover vote-table" id="index_table">
                            <thead>
                            <tr>
                                <th width="20">
                                    <label class="checkbox">
                                        <input type="checkbox" data-name="vote[]">
                                    </label>
                                </th>
                                <th width="32">
                                    <i class="i-lt o-art-list"></i>
                                </th>
                                <th width="230">标题</th>
                                <th width="100">发布人</th>
                                <th width="100">截止时间</th>
                                <th width="100">状态</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                <?php endif; ?>
                <?php if ($type == 4 || $type == 7): ?>
                    <div id="my_wrap">
                        <table class="table table-hover vote-table" id="my_table">
                            <thead>
                            <tr>
                                <th width="20">
                                    <label class="checkbox">
                                        <input type="checkbox" data-name="vote[]">
                                    </label>
                                </th>
                                <th width="32">
                                    <i class="i-lt o-art-list"></i>
                                </th>
                                <th width="230">标题</th>
                                <th width="80">已投票</th>
                                <th width="80">截止时间</th>
                                <th width="100">状态</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script type="text/template" id="search_tpl">
        <form id="mn_search_advance_form" class="form-horizontal form-compact" style="width: 520px;">
            <div class="control-group">
                <label class="control-label xal">标题</label>
                <div class="controls">
                    <input type="text" name="search[subject]" id="subject">
                </div>
            </div>
            <div class="control-group">
                <label class="control-label xal">发布人</label>
                <div class="controls">
                    <input type="text" name="search[sponsor]" id="publishScope">
                </div>
            </div>
            <div class="control-group">
                <label class="control-label xal">截止时间</label>
                <div class="controls">
                    <div class="row">
                        <div class="span5">
                            <div class="datepicker" id="vot_start_date">
                                <input type="text" readonly name="search[starttime]" class="datepicker-input" value="">
                                <a href="javascript:;" class="datepicker-btn"></a>
                            </div>
                        </div>
                        <div class="span2 lhf text-center">至</div>
                        <div class="span5">
                            <div class="datepicker" id="vot_end_date">
                                <input type="text" readonly name="search[endtime]" class="datepicker-input" value="">
                                <a href="javascript:;" class="datepicker-btn"></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <input type="hidden" name="search[type]" value="advanced_search">
        </form>
    </script>
    <script type="text/template" id="adjust_dialog_tpl">
        <form class="form-horizontal form-adjust" id="adjust_info_form">
            <div class="adjust-info-top clearfix">
                <div class="pull-left">
                    <div class="avatar-wrap">
                        <img src="<%= avatar %>">
                    </div>
                </div>
                <div class="pull-left mls" style="width: 300px;">
                    <p class="info-title">
                        <span class="xwb"><%= sponsor %></span>
                        <span>主持</span>
                        <span><%= endtimestr %></span>
                    </p>
                    <p class="xwb fsl ellipsis">
                        <%= subject %>
                    </p>
                </div>
            </div>
            <div class="adjust-info-body fill-nn">
                <div class="control-group">
                    <label class="control-label">
                        <span>截止时间</span>
                    </label>
                    <div class="controls">
                        <div class="time-select-wrap">
                            <div class="datepicker" id="end_time">
                                <a href="javascript:;" class="datepicker-btn"></a>
                                <input type="text" name="vote[endtime]" readonly
                                       class="datepicker-input adjust-info-input" placeholder="实际结束时间" id="time"
                                       readonly="readonly" data-node-type="condition"/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <input type="hidden" name="vote[voteid]" value="<%= id %>"/>
        </form>
    </script>
    <script src="<?php echo STATICURL; ?>/js/lib/dataTable/js/jquery.dataTables.js?<?php echo VERHASH; ?>"></script>
    <script src="<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>"></script>
    <script src="<?php echo $assetUrl; ?>/js/vote_default_index.js?<?php echo VERHASH; ?>"></script>
