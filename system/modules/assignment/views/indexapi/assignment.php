<?php

use application\core\utils\IBOS;
use application\core\utils\StringUtil;
use application\modules\assignment\utils\Assignment as AssignmentUtil;
?>
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/assignment.css?<?php echo VERHASH; ?>">
<!--我负责的-->
<?php if ( $tab == 'charge' ): ?>
    <?php if ( !empty( $chargeData ) ): ?>
        <table class="table table-underline">
            <?php foreach ( $chargeData as $charge ): ?>
                <tr>
                    <td width="40">
                        <span class="avatar-circle avatar-circle-small">
                            <img class="mbm" src="<?php echo $charge['designee']['avatar_small']; ?>" alt="">
                        </span>
                    </td>
                    <td>
                        <a href="<?php echo IBOS::app()->urlManager->createUrl( 'assignment/default/show', array( 'assignmentId' => $charge['assignmentid'] ) ); ?>" class="xcm">
                            <?php echo StringUtil::cutStr( $charge['subject'], 40 ); ?>
                        </a>
                        <div class="fss">
                            <?php echo $charge['designee']['realname']; ?>
                            <?php echo $charge['st']; ?> -- <?php echo $charge['et']; ?>
                            <?php if ( TIMESTAMP > $charge['endtime'] ): ?>
                                <i class="om-am-warning mls" title="<?php echo $lang['Expired']; ?>"></i>
                            <?php elseif ( $charge['remindtime'] > 0 ): ?>
                                <i class="om-am-clock mls" title="<?php echo $lang['Has been set to remind']; ?>"></i>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td width="60">
                        <span class="pull-right am-tag am-tag-<?php echo AssignmentUtil::getCssClassByStatus( $charge['status'] ) ?>">
                            <?php if ( $charge['status'] == 0 ): ?>
                                <?php echo $lang['Unreaded']; ?>
                            <?php elseif ( $charge['status'] == 1 ): ?>
                                <?php echo $lang['Ongoing']; ?>
                            <?php elseif ( $charge['status'] == 4 ): ?>
                                <?php echo $lang['Has been cancelled']; ?>
                            <?php endif; ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <div class="mbox-base">
            <div class="fill-hn xac">
                <a href="<?php echo IBOS::app()->urlManager->createUrl( 'assignment/unfinished/index' ); ?>" class="link-more">
                    <i class="cbtn o-more"></i>
                    <span class="ilsep"><?php echo $lang['Show more assignment']; ?></span>
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="am-charge-empty"></div>
    <?php endif; ?>
    <!--我指派的-->
<?php elseif ( $tab == 'designee' ): ?>
    <?php if ( !empty( $designeeData ) ): ?>
        <table class="table table-underline">
            <?php foreach ( $designeeData as $designee ): ?>
                <tr>
                    <td width="40">
                        <span class="avatar-circle avatar-circle-small">
                            <img class="mbm" src="<?php echo $designee['charge']['avatar_small']; ?>" alt="">
                        </span>
                    </td>
                    <td>
                        <a href="<?php echo IBOS::app()->urlManager->createUrl( 'assignment/default/show', array( 'assignmentId' => $designee['assignmentid'] ) ); ?>" class="xcm">
                            <?php echo StringUtil::cutStr( $designee['subject'], 40 ); ?>
                        </a>
                        <div class="fss">
                            <?php echo $designee['charge']['realname']; ?>
                            <?php echo $designee['st']; ?> -- <?php echo $designee['et']; ?>
                            <?php if ( TIMESTAMP > $designee['endtime'] ): ?>
                                <i class="om-am-warning mls" title="<?php echo $lang['Expired']; ?>"></i>
                            <?php elseif ( $designee['remindtime'] > 0 ): ?>
                                <i class="om-am-clock mls" title="<?php echo $lang['Has been set to remind']; ?>"></i>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td width="60">
                        <span class="pull-right am-tag am-tag-<?php echo AssignmentUtil::getCssClassByStatus( $designee['status'] ) ?>">
                            <?php if ( $designee['status'] == 0 ): ?>
                                <?php echo $lang['Unreaded']; ?>
                            <?php elseif ( $designee['status'] == 1 ): ?>
                                <?php echo $lang['Ongoing']; ?>
                            <?php elseif ( $designee['status'] == 4 ): ?>
                                <?php echo $lang['Has been cancelled']; ?>
                            <?php endif; ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <div class="mbox-base">
            <div class="fill-hn xac">
                <a href="<?php echo IBOS::app()->urlManager->createUrl( 'assignment/unfinished/index' ); ?>" class="link-more">
                    <i class="cbtn o-more"></i>
                    <span class="ilsep"><?php echo $lang['Show more assignment']; ?></span>
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="am-designee-empty"></div>
    <?php endif; ?>
<?php endif; ?>
