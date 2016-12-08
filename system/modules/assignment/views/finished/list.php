<?php
use application\core\utils\DateTime;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\modules\main\utils\Main;

?>
<link rel="stylesheet"
      href="<?php echo STATICURL; ?>/js/lib/daterangepicker/daterangepicker-ibos.css?<?php echo VERHASH; ?>">
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/assignment.css?<?php echo VERHASH; ?>">
<div class="mc clearfix">
    <!-- Sidebar start -->
    <?php echo $this->getSidebar(); ?>
    <!-- Siderbar end -->
    <div class="mcr">
        <div class="page-list">
            <div class="page-list-header">
                <div class="pull-left am-finished-count">
                    <?php echo $lang['Completed']; ?>
                    <strong><?php echo $count; ?></strong> <?php echo $lang['Number of tasks']; ?>
                </div>
                <form action="<?php echo $this->createUrl('finished/index', array('param' => 'search')); ?>"
                      method="post" class="am-search-bar">
                    <div class="datepicker pull-left">
                        <input type="text" class="datepicker-input" name="daterange" id="am_daterange_input"
                               value="<?php if (Env::getRequest('param') == 'search') {
                                   echo Main::getCookie('daterange');
                               }; ?>">
                        <a href="javascript:;" class="datepicker-btn" id="am_daterange_btn"></a>
                    </div>

                    <div class="search pull-right">
                        <input type="text" placeholder="<?php echo $lang['Enter the user or task name']; ?>"
                               name="keyword" id="mn_search" nofocus
                               value="<?php if (Env::getRequest('param') == 'search') {
                                   echo Main::getCookie('keyword');
                               }; ?>">
                        <a href="javascript:;">search</a>
                        <input type="hidden" name="search" value="1">
                    </div>
                </form>
            </div>
            <div class="page-list-mainer">
                <?php foreach ($datas as $finishDate => $assignments): ?>
                    <div>
                        <div class="fill-sn">
                            <div class="mini-date">
                                <strong><?php echo date('d', $finishDate); ?></strong>
                                <div class="mini-date-body">
                                    <p><?php echo $lang['Weekday']; ?><?php echo DateTime::getWeekDay($finishDate); ?></p>
                                    <p><?php echo date('Y-m', $finishDate); ?> </p>
                                </div>
                            </div>
                        </div>
                        <table class="table table-hover" data-node-type="taskTable">
                            <tbody>
                            <?php foreach ($assignments as $k => $assignment): ?>

                                <tr data-id="<?php echo $assignment['assignmentid']; ?>">
                                    <td width="22">
                                        <?php if ($assignment['designeeuid'] == Ibos::app()->user->uid || $assignment['chargeuid'] == Ibos::app()->user->uid): ?>
                                            <a href="javascript:;" class="am-checkbox am-checkbox-ret"
                                               data-id="<?php echo $assignment['assignmentid']; ?>"
                                               title="<?php echo $lang['Unfinish'] ?>"></a>
                                        <?php endif; ?>
                                    </td>
                                    <td width="36">
											<span class="avatar-circle avatar-circle-small">
<!--												<img src="-->
                                                <?php //echo $assignment['charge']['avatar_small']; ?><!--">-->
												<img src="<?php echo $assignment['designee']['avatar_small']; ?>">
											</span>
                                    </td>
                                    <td>
                                        <a class="xcm" target="_blank"
                                           href="<?php echo $this->createUrl('default/show', array('assignmentId' => $assignment['assignmentid'])) ?>"><?php echo $assignment['subject']; ?></a>
                                        <div class="fss">
                                            <?php if ($assignment['designeeuid'] == Ibos::app()->user->uid): ?>
                                                <?php echo $lang['Arrange to']; ?> <span
                                                    class="ilsep"><?php echo $assignment['designee']['realname']; ?></span>
                                            <?php else: ?>
                                                <?php echo $lang['The originator']; ?> <span
                                                    class="ilsep"><?php echo $assignment['designee']['realname']; ?></span>
                                            <?php endif; ?>
                                            <?php echo $assignment['st'] ?> -- <?php echo $assignment['et'] ?>
                                        </div>
                                    </td>
                                    <td width="80">
                                        <?php if (isset($assignment['stampPath'])): ?>
                                            &nbsp;&nbsp;<img width="60" height="24"
                                                             src="<?php echo $assignment['stampPath']; ?>"/>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                        <div class="am-date-sep"></div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="page-list-footer">
                <div class="pull-right">
                    <?php $this->widget('application\core\widgets\Page', array('pages' => $pages)); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo STATICURL; ?>/js/lib/moment.min.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo STATICURL; ?>/js/lib/daterangepicker/daterangepicker.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo $assetUrl; ?>/js/assignment.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo $assetUrl; ?>/js/assignment_finished_list.js?<?php echo VERHASH; ?>"></script> 
