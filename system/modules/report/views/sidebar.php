<?php

use application\core\utils\Env;
use application\core\utils\Module;
use application\modules\statistics\core\StatConst;
use application\modules\statistics\utils\StatCommon;
use application\modules\report\core\ReportType as ICReportType;

?>
<div class="aside" id="aside">
    <div class="sbb sbbl sbbf">
        <ul class="nav nav-strip nav-stacked">
            <li class="active">
                <a href="<?php echo $this->createUrl('default/index'); ?>">
                    <i class="o-rp-personal"></i>
                    <?php echo $lang['Personal']; ?>
                </a>
                <div class="rp-cycle">
                    <div class="rp-cycle-header">
                        <a href="javascript:;" class="o-setup pull-right" id="rp_type_setup"></a>
                        <strong><?php echo $lang['Report type']; ?></strong>
                    </div>
                    <ul class="aside-list" id="rp_type_aside_list">
                        <?php foreach ($reportTypes as $reportType): ?>
                            <?php $typeid = Env::getRequest('typeid'); ?>
                            <li <?php if ($reportType['typeid'] == $typeid): ?>class="active"<?php endif; ?>
                                data-id="<?php echo $reportType['typeid'] ?>">
                                <a href="<?php echo $this->createUrl('default/index', array('typeid' => $reportType['typeid'])); ?>">
                                    <i>&gt;</i> <?php echo $reportType['typename']; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </li>

            <li>
                <a href="<?php echo $this->createUrl('review/index'); ?>">
                    <?php if ($this->getUnreviews() != ''): ?>
                        <span class="badge pull-right"><?php echo $this->getUnreviews(); ?></span>
                    <?php endif; ?>
                    <i class="o-rp-appraise"></i>
                    <?php echo $lang['Reveiw']; ?>
                </a>
            </li>

            <?php if (Module::getIsEnabled('statistics') && isset($statModule['report'])): ?>
                <?php echo $this->widget(StatCommon::getWidgetName('report', StatConst::SIDEBAR_WIDGET), array('hasSub' => $this->checkIsHasSub()), true); ?>
            <?php endif; ?>
        </ul>
    </div>
</div>
<!-- 汇报类型设置 -->
<div id="d_report_type" style="width: 520px; display:none;">
    <form id="d_report_type_form">
        <table class="table table-operate mbz" id="rp_type_table">
            <thead>
            <tr>
                <th width="40"><?php echo $lang['Num']; ?></th>
                <th width="180"><?php echo $lang['Report type']; ?></th>
                <th width="220"><?php echo $lang['Report Interval']; ?></th>
                <th width="80"><?php echo $lang['Operation']; ?></th>
            </tr>
            </thead>
            <tbody id="rp_type_tbody">
            <?php foreach ($reportTypes as $index => $reportType): ?>
                <?php if ($reportType['issystype']): ?>
                    <tr>
                        <td><?php echo $reportType['sort']; ?></td>
                        <td><?php echo $reportType['typename']; ?></td>
                        <td><?php echo ICReportType::handleShowInterval($reportType['intervaltype']); ?></td>
                        <td></td>
                    </tr>
                <?php else: ?>
                    <tr data-id="<?php echo $reportType['typeid']; ?>">
                        <td data-name="sort"
                            data-value="<?php echo $reportType['sort']; ?>"><?php echo $reportType['sort']; ?></td>
                        <td data-name="typename"
                            data-value="<?php echo $reportType['typename']; ?>"><?php echo $reportType['typename']; ?></td>
                        <td data-name="intervaltype" data-value="<?php echo $reportType['intervaltype']; ?>">
                            <input type="hidden" data-name="intervals"
                                   data-value="<?php echo $reportType['intervals']; ?>"/>
                            <?php if ($reportType['intervaltype'] == 5): echo $reportType['intervals'] . $lang['Day']; ?>
                            <?php else: echo ICReportType::handleShowInterval($reportType['intervaltype']); ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="javascript:;" data-click="editType" class="anchor"
                               data-id="<?php echo $reportType['typeid']; ?>"><?php echo $lang['Edit']; ?></a>
                            <a href="javascript:;" data-click="removeType" class="anchor mlm"
                               data-id="<?php echo $reportType['typeid']; ?>"><?php echo $lang['Delete']; ?></a>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
            <tr>
                <td>
                    <input type="text" class="input-small" name="sort" id="rp_type_sort">
                </td>
                <td>
                    <input type="text" class="input-small" name="typename" id="rp_type_name">
                </td>
                <td class="clear-fix">
                    <select class="input-small span5" name="intervaltype" style="float:left">
                        <option value="0"><?php echo $lang['Week']; ?></option>
                        <option value="1"><?php echo $lang['Month']; ?></option>
                        <option value="2"><?php echo $lang['Season']; ?></option>
                        <option value="3"><?php echo $lang['Half of a year']; ?></option>
                        <option value="4"><?php echo $lang['Year']; ?></option>
                        <option value="5"><?php echo $lang['Other']; ?></option>
                    </select>
                    <div class="input-group" style="display:none; float:right;width:50%">
                        <input type="text" class="input-small" name="intervals">
                        <span class="input-group-addon input-small"><?php echo $lang['Day']; ?></span>
                    </div>
                </td>
                <td>
                    <a href="javascript:;" class="cbtn o-plus"></a>
                </td>
            </tr>
            </tfoot>
        </table>
    </form>
</div>
<!-- 汇报类型新增一行模板 -->
<script type="text/template" id="rp_type_tpl">
    <tr data-id="<%= typeid %>">
        <td data-name="sort" data-value="<%= sort %>"><%= sort %></td>
        <td data-name="typename" data-value="<%= typename %>"><%= typename %></td>
        <td data-name="intervaltype" data-value="<%= intervaltype %>"><%= intervalTypeName %></td>
        <input type="hidden" data-name="intervals" data-value="<%= intervals %>"/>
        <td>
            <a href="javascript:;" data-click="editType" class="anchor"
               data-id="<%= typeid %>"><?php echo $lang['Edit']; ?></a>
            <a href="javascript:;" data-click="removeType" class="anchor mlm"
               data-id="<%= typeid %>"><?php echo $lang['Delete']; ?></a>
        </td>
    </tr>
</script>

<!-- 汇报类型修改模板 -->
<script type="text/template" id="rp_type_edit_tpl">
    <tr data-id="<%= typeid %>">
        <td>
            <input type="text" class="input-small" name="sort" value="<%= sort %>"/>
        </td>
        <td>
            <input type="text" class="input-small" name="typename" value="<%= typename %>"/>
        </td>
        <td class="clearfix">
            <select name="intervaltype" class="input-small span5" style="float:left">
                <option value="0"
                <% if(intervaltype == 0) { %>selected<% } %>><?php echo $lang['Week']; ?></option>
                <option value="1"
                <% if(intervaltype == 1) { %>selected<% } %>><?php echo $lang['Month']; ?></option>
                <option value="2"
                <% if(intervaltype == 2) { %>selected<% } %>><?php echo $lang['Season']; ?></option>
                <option value="3"
                <% if(intervaltype == 3) { %>selected<% } %>><?php echo $lang['Half of a year']; ?></option>
                <option value="4"
                <% if(intervaltype == 4) { %>selected<% } %>><?php echo $lang['Year']; ?></option>
                <option value="5"
                <% if(intervaltype == 5) { %>selected<% } %>><?php echo $lang['Other']; ?></option>
            </select>
            <div class="input-group" style="
				<% if(intervaltype != 5) { %>
					display:none;
				<% } %>
				float:right; width: 50%;">
                <input type="text" class="input-small" name="intervals" value="<%= intervals %>">
                <span class="input-group-addon input-small">天</span>
            </div>
        </td>
        <td>
            <a href="javascript:;" data-click="saveType" class="anchor" data-id="<%= typeid %>"><%= Ibos.l("SAVE")
                %></a>
            <a href="javascript:;" data-click="cancelEdit" class="anchor mlm" data-id="<%= typeid %>"><%=
                Ibos.l("CANCEL") %></a>
        </td>
    </tr>
</script>

<!-- 汇报类型新增一行到侧边栏模板 -->
<script type="text/template" id="rp_type_sidebar_tpl">
    <li data-id="<%=typeid%>">
        <a href="<%=url%>">
            <i>&gt;</i> <%=typename%>
        </a>
    </li>
</script>