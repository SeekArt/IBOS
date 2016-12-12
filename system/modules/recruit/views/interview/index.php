<!-- load css -->
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/recruit.css?<?php echo VERHASH; ?>">
<!-- load css end-->

<!-- Mainer -->
<div class="wrap">
    <div class="mc clearfix">
        <!-- Sidebar -->
        <?php echo $sidebar; ?>
        <!-- Sidebar end -->

        <!-- Mainer right -->
        <div class="mcr">
            <!-- Mainer nav -->
            <div class="page-list">
                <div class="page-list-header">
                    <div class="btn-toolbar pull-left">
                        <button class="btn btn-primary pull-left"
                                data-action="addInterview"><?php echo $lang['Add']; ?></button>
                        <div class="btn-group" id="art_more" style="display:block;">
                            <button class="btn dropdown-toggle" data-toggle="dropdown">
                                <?php echo $lang['More operation']; ?>
                                <i class="caret"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a href="javascript:;" data-action="deleteInterviews">
                                        <?php echo $lang['Delete']; ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:;" data-action="exportInterview">
                                        <?php echo $lang['Export']; ?>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <form action="<?php echo $this->createUrl('interview/search'); ?>" method="post">
                        <div class="search search-config pull-right span3">
                            <input type="text" placeholder="Search" id="mn_search" name="keyword" nofocus>
                            <a href="javascript:;">search</a>
                            <input type="hidden" name="type" value="normal_search">
                        </div>
                    </form>
                </div>
                <div class="page-list-mainer">
                    <table class="table table-striped table-hover">
                        <thead>
                        <tr>
                            <th width="20">
                                <label class="checkbox">
                                    <input type="checkbox" name="" data-name="interview[]" id="all_select">
                                </label>
                            </th>
                            <th width="100">
                                <?php echo $lang['Name']; ?>
                            </th>
                            <th width="100"><?php echo $lang['Interview time']; ?></th>
                            <th width="70"><?php echo $lang['Interview people']; ?></th>
                            <th width="70"><?php echo $lang['Interview types']; ?></th>
                            <th><?php echo $lang['Interview process']; ?></th>
                            <th width="60"><?php echo $lang['Operation']; ?></th>
                        </tr>
                        </thead>
                        <tbody id="interview_tbody">
                        <?php foreach ($resumeInterviewList as $resumeInterview) { ?>
                            <tr>
                                <td>
                                    <label class="checkbox">
                                        <input type="checkbox" name="interview[]"
                                               value="<?php echo $resumeInterview['interviewid']; ?>">
                                    </label>
                                </td>
                                <td>
                                    <a href="<?php echo $this->createUrl('resume/show', array('resumeid' => $resumeInterview['resumeid'])); ?>"><?php echo $resumeInterview['realname']; ?></a>
                                </td>
                                <td>
                                    <?php echo $resumeInterview['interviewtime']; ?>
                                </td>
                                <td>
                                    <?php echo $resumeInterview['interviewer']; ?>
                                </td>
                                <td>
                                    <?php echo $resumeInterview['type']; ?>
                                </td>
                                <td>
                                    <?php echo $resumeInterview['process']; ?>
                                </td>
                                <td>
                                    <a href="javascript:" data-action="editInterview"
                                       data-id="<?php echo $resumeInterview['interviewid']; ?>"
                                       title="<?php echo $lang['Modify']; ?>" class="cbtn o-edit"></a>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                    <div
                        class="no-data-tip" <?php if (count($resumeInterviewList) > 0): ?> style="display:none" <?php endif; ?>
                        id="no_interview_tip"></div>
                </div>
                <div class="page-list-footer">
                    <div class="pull-right">
                        <?php $this->widget('application\core\widgets\Page', array('pages' => $pagination)); ?>
                    </div>
                </div>
            </div>
            <!-- Mainer content -->
        </div>
    </div>
</div>
<!-- 高级搜索 -->
<div id="mn_search_advance" style="width: 400px; display:none;">
    <form id="mn_search_advance_form" action="<?php echo $this->createUrl('interview/search'); ?>" method="post">
        <div class="form-horizontal form-compact">
            <div class="control-group">
                <label for="" class="control-label"><?php echo $lang['Name']; ?></label>
                <div class="controls">
                    <input type="text" id="realname" name="search[realname]">
                </div>
            </div>
            <div class="control-group">
                <label for="" class="control-label"><?php echo $lang['Interview time']; ?></label>
                <div class="controls">
                    <select name="search[interviewtime]" id="interviewtime">
                        <option value="-1"><?php echo $lang['Please select']; ?></option>
                        <option value="7"><?php echo $lang['Within a week']; ?></option>
                        <option value="15"><?php echo $lang['Within two weeks']; ?></option>
                        <option value="31"><?php echo $lang['within a month']; ?></option>
                    </select>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label"><?php echo $lang['Interview people']; ?></label>
                <div class="controls">
                    <input type="text" name="search[interviewer]" data-toggle="userSelect" id="user_interview_search"
                           value="">
                </div>
            </div>
            <div class="control-group">
                <label for="" class="control-label"><?php echo $lang['Interview types']; ?></label>
                <div class="controls">
                    <select name="search[type]" id="type">
                        <option value="-1"><?php echo $lang['Please select']; ?></option>
                        <option value="<?php echo $lang['First test']; ?>"><?php echo $lang['First test']; ?></option>
                        <option value="<?php echo $lang['Audition']; ?>"><?php echo $lang['Audition']; ?></option>
                        <option value="<?php echo $lang['Retest']; ?>"><?php echo $lang['Retest']; ?></option>
                    </select>
                </div>
            </div>
        </div>
        <input type="hidden" name="type" value="advanced_search">
    </form>
</div>

<!--增加/编辑面试信息-->
<div id="interview_dialog" style="width: 500px; display:none;">
    <form id="interview_dialog_form" method="get">
        <!-- @Todo: 此处是否应该改为选择框 -->
        <div class="form-horizontal form-compact">
            <div class="control-group" id="r_fullname">
                <label class="control-label"><?php echo $lang['Full name']; ?></label>
                <div class="controls span6">
                    <select name="detailid" id="detailid">
                        <?php foreach ($resumes as $resume): ?>
                            <option
                                value="<?php echo $resume['detailid']; ?>"><?php echo $resume['realname']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label"><?php echo $lang['Interview methods']; ?></label>
                <div class="controls span6">
                    <select name="method" id="method">
                        <option value="<?php echo $lang['Telephone']; ?>"><?php echo $lang['Telephone']; ?></option>
                        <option value="<?php echo $lang['Letters']; ?>"><?php echo $lang['Letters']; ?></option>
                        <option value="<?php echo $lang['Mail']; ?>"><?php echo $lang['Mail']; ?></option>
                        <option value="<?php echo $lang['Visit']; ?>"><?php echo $lang['Visit']; ?></option>
                    </select>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label"><?php echo $lang['Type']; ?></label>
                <div class="controls span6">
                    <select name="type" id="type">
                        <option value="<?php echo $lang['First test']; ?>"><?php echo $lang['First test']; ?></option>
                        <option value="<?php echo $lang['Audition']; ?>"><?php echo $lang['Audition']; ?></option>
                        <option value="<?php echo $lang['Retest']; ?>"><?php echo $lang['Retest']; ?></option>
                    </select>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label"><?php echo $lang['Interview time']; ?></label>
                <div class="controls span6">
                    <div class="datepicker" id="interview_time">
                        <a href="javascript:;" class="datepicker-btn"></a>
                        <input type="text" class="datepicker-input" name="interviewtime"
                               value="<?php echo date('Y-m-d', time()); ?>">
                    </div>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label"><?php echo $lang['Interview people']; ?></label>
                <div class="controls span6">
                    <input type="text" name="interviewer" data-toggle="userSelect" id="user_interview" value="">
                </div>
            </div>
            <div class="control-group">
                <label class="control-label"><?php echo $lang['Interview process']; ?></label>
                <div class="controls">
                    <textarea name="process" id="process" rows="4" cols="20"></textarea>
                </div>
            </div>
        </div>
        <input type="hidden" name="interviewid" id="interviewid"/>
    </form>
</div>

<script src='<?php echo STATICURL; ?>/js/lib/formValidator/formValidator.packaged.js?<?php echo VERHASH; ?>'></script>
<!-- 插入面试信息模板 -->
<script type="text/ibos-template" id="interview_template">
    <tr>
        <td>
            <label class="checkbox">
                <input type="checkbox" value="<%=interviewid%>" name="interview[]">
            </label>
        </td>
        <td>
            <a href="/?r=recruit/resume/show&resumeid=<%=resumeid%>"><%=fullname%></a>
        </td>
        <td>
            <%=interviewtime%>
        </td>
        <td>
            <%=interviewer%>
        </td>
        <td>
            <%=type%>
        </td>
        <td>
            <%=process%>
        </td>
        <td>
            <a href="javascript:" data-action="editInterview" data-id="<%=interviewid%>"
               title="<?php echo $lang['Update']; ?>" class="cbtn o-edit"></a>
        </td>
    </tr>
</script>

<script src='<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/recruit.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/recruit_interview_index.js?<?php echo VERHASH; ?>'></script>
