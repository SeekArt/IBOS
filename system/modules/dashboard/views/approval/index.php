<link href="<?php echo $this->getAssetUrl(); ?>/css/approval.css" type="text/css" rel="stylesheet"/>
<div class="ct sp-ct">
    <div class="clearfix">
        <h1 class="mt">审批流程</h1>
    </div>
    <div>
        <form id="process_setting_form" action="" method="post" class="form-horizontal process-setting-form">
            <div class="ctb ps-type-title">
                <h2 class="st">审批流程</h2>
            </div>
            <div>
                <ul class="process-list clearfix">
                    <?php foreach ($approvals as $approval): ?>
                        <li data-id="<?php echo $approval['id']; ?>">
                            <div class="fill-nn approval-box">
                                <div class="clearfix mbs apploval-flow-title">
                                    <div class="pull-left fsl xcn"><?php echo $approval['name']; ?></div>
                                    <!-- <div class="pull-right"><i class="level-<?php echo $approval['level']; ?>"></i></div> -->
                                    <div class="pull-right">
                                        <a href="<?php echo $this->createUrl('approval/edit', array('id' => $approval['id'])); ?>"
                                           title="编辑" target="_self" class="o-edit"></a>
                                        <a href="javascript:;" title="删除" class="o-trash"></a>
                                    </div>
                                </div>
                                <div class="process-step">
                                    <div class="process-step-list">
                                        <div class="xwb mbs">审批流程</div>
                                        <div class="step-list mb">
                                            <?php foreach ($approval['levels'] as $level => $levelInfo): ?>
                                                <div class="step-content">
                                                    <div class="step-icon">
                                                        <i class="<?php echo $levelInfo['levelClass']; ?>"></i>
                                                    </div>
                                                    <div class="related-person"
                                                         title="<?php echo $levelInfo['title']; ?>">
                                                        <?php echo !empty($levelInfo['show']) ? $levelInfo['show'] : '未设置审核人'; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="escape-person mb">
                                            <div class="step-icon">
                                                <i class="<?php echo $approval['free']['levelClass']; ?>"></i>
                                            </div>
                                            <div class="related-person"
                                                 title="<?php echo $approval['free']['title']; ?>">
                                                <?php echo !empty($approval['free']['show']) ? $approval['free']['show'] : '未设置免审核人'; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="approve-description">
                                        <span class="tcm">审核描述</span>
                                        <p title="<?php echo $approval['desc']; ?>"
                                           class="description-content"><?php echo !empty($approval['desc']) ? $approval['desc'] : '暂无信息'; ?></p>
                                    </div>
                                </div>
                                <!-- <div class="clearfix ps-funbar">
									<div class="pull-right">
										<a href="<?php echo $this->createUrl('approval/edit', array('id' => $approval['id'])); ?>" title="编辑" target="_self" class="o-edit"></a>
										<a href="javascript:;" title="删除" class="o-trash"></a>
									</div>
								</div> -->
                            </div>
                        </li>
                    <?php endforeach; ?>
                    <li class="item-add">
                        <div class="fill-nn approval-box">
                            <a href="<?php echo $this->createUrl('approval/add'); ?>" class="process-item-add">
                                <i class="process-add-icon"></i>
                                <p class="">新建审批流程</p>
                            </a>
                        </div>
                    </li>
                </ul>
            </div>
        </form>
    </div>
</div>
<script src="<?php echo $this->getAssetUrl(); ?>/js/db_approval.js"></script>
