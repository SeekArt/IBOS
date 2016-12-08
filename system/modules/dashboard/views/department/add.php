<?php

?>
<div class="ct">
    <div class="clearfix">
        <h1 class="mt">部门人员管理</h1>
    </div>
    <div>
        <!-- 部门信息 start -->
        <div class="ctb">
            <h2 class="st">新增部门</h2>
            <div class="">
                <form action="<?php echo $this->createUrl('department/add', array('addsubmit' => 1)); ?>" method="post"
                      class="department-info-form form-horizontal" id="add_dept_form">
                    <div class="control-group">
                        <label class="control-label">上级部门</label>
                        <div class="controls">
                            <select name="pid">
                                <option value="0" selected><?php echo $lang['Top department']; ?></option>
                                <?php echo $tree; ?>
                            </select>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">部门名称</label>
                        <div class="controls">
                            <input type="text" name="deptname" placeholder="请输入部门名称" id="dept_name"/>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">部门主管</label>
                        <div class="controls">
                            <input type="text" name="manager" id="dep_manager"/>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">上级主管领导</label>
                        <div class="controls">
                            <input type="text" name="leader" id="superior_manager"/>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">上级分管领导</label>
                        <div class="controls">
                            <input type="text" name="subleader" id="superior_branched_manager"/>
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="" class="control-label">作为分支机构</label>
                        <div class="controls">
                            <input type="checkbox" data-toggle="switch" value="1" name="isbranch"/>
                            <span class="mls vam">部门名称加粗</span>
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="" class="control-label">部门电话</label>
                        <div class="controls">
                            <input type="text" name="tel" placeholder="请输入部门电话">
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="" class="control-label">部门传真</label>
                        <div class="controls">
                            <input type="text" name="fax" placeholder="请输入部门传真">
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="" class="control-label">部门地址</label>
                        <div class="controls">
                            <input type="text" name="addr" placeholder="请输入部门地址">
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="" class="control-label">部门职能</label>
                        <div class="controls">
                            <textarea name="func" rows="5" placeholder="请填写部门的职责和能力"></textarea>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label"></label>
                        <div class="controls">
                            <button type="submit"
                                    class="btn btn-primary btn-large btn-submit"><?php echo $lang['Submit']; ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script src='<?php echo STATICURL; ?>/js/lib/formValidator/formValidator.packaged.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/org_department_add.js?<?php echo VERHASH; ?>'></script>