<?php ?>
<div class="ct">
    <div class="clearfix">
        <h1 class="mt">部门管理</h1>
    </div>
    <div>
        <!-- 部门信息 start -->
        <div class="ctb">
            <h2 class="st">编辑部门</h2>
            <div class="">
                <div class="btn-group mb">
                    <a href="javascript:;" class="btn active">部门设置</a>
                    <a href="<?php echo $this->createUrl('department/edit', array('op' => 'member', 'id' => $id)); ?>"
                       class="btn">部门成员管理</a>
                </div>
                <form action="<?php echo $this->createUrl('department/edit', array('updatesubmit' => 1)); ?>"
                      method="post" class="department-info-form form-horizontal" id="add_dept_form">
                    <div class="control-group">
                        <label class="control-label">上级部门</label>
                        <div class="controls">
                            <input type="text" name="pid" value="<?php echo $deptid;?>" id="dept_pid"/>
                            <!-- <select name="pid" id="dept_pid">
                                <option value="0"><?php //echo $lang['Top department']; ?></option>
                                <?php //echo $tree; ?>
                            </select> -->
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">部门名称</label>
                        <div class="controls">
                            <input type="text" name="deptname" placeholder="请输入部门名称" id="dept_name"
                                   value="<?php echo $department['deptname']; ?>"/>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">部门主管</label>
                        <div class="controls">
                            <input type="text" name="manager" id="dep_manager"
                                   value="<?php echo $department['manager']; ?>"/>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">上级主管领导</label>
                        <div class="controls">
                            <input type="text" name="leader" id="superior_manager"
                                   value="<?php echo $department['leader']; ?>"/>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">上级分管领导</label>
                        <div class="controls">
                            <input type="text" name="subleader" id="superior_branched_manager"
                                   value="<?php echo $department['subleader']; ?>"/>
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="" class="control-label">作为分支机构</label>
                        <div class="controls">
                            <input type="checkbox" data-toggle="switch" value="1" name="isbranch"
                                   <?php if ($department['isbranch']): ?>checked<?php endif; ?> />
                            <span class="mls vam">部门名称加粗</span>
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="" class="control-label">部门电话</label>
                        <div class="controls">
                            <input type="text" name="tel" placeholder="请输入部门电话"
                                   value="<?php echo $department['tel']; ?>">
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="" class="control-label">部门传真</label>
                        <div class="controls">
                            <input type="text" name="fax" placeholder="请输入部门传真"
                                   value="<?php echo $department['fax']; ?>">
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="" class="control-label">部门地址</label>
                        <div class="controls">
                            <input type="text" name="addr" placeholder="请输入部门地址"
                                   value="<?php echo $department['addr']; ?>">
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="" class="control-label">部门职能</label>
                        <div class="controls">
                            <textarea name="func" rows="5"
                                      placeholder="请填写部门的职责和能力"><?php echo $department['func']; ?></textarea>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label"></label>
                        <div class="controls">
                            <button type="submit"
                                    class="btn btn-primary btn-large btn-submit"><?php echo $lang['Submit']; ?></button>
                        </div>
                    </div>
                    <input type="hidden" name="deptid" value="<?php echo $department['deptid'] ?>">
                </form>
            </div>
        </div>
    </div>
</div>
<script src='<?php echo STATICURL; ?>/js/lib/formValidator/formValidator.packaged.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/org_department_add.js?<?php echo VERHASH; ?>'></script>