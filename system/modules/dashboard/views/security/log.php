<?php use application\core\utils\StringUtil; ?>
<link rel="stylesheet"
      href="<?php echo STATICURL; ?>/js/lib/dataTable/css/jquery.dataTables_ibos.min.css?<?php echo VERHASH; ?>">
<div class="ct">
    <div class="clearfix">
        <h1 class="mt"><?php echo $lang['Security setting']; ?></h1>
        <!-- @Todo: PHP -->
        <ul class="mn">
            <li>
                <a href="<?php echo $this->createUrl('security/setup'); ?>"><?php echo $lang['Account security setup']; ?></a>
            </li>
            <li>
                <span><?php echo $lang['Run log']; ?></span>
            </li>
            <li>
                <a href="<?php echo $this->createUrl('security/ip'); ?>"><?php echo $lang['Disabled ip']; ?></a>
            </li>
        </ul>
    </div>
    <div>
        <form action="" class="form-horizontal">
            <!-- 运行记录 start -->
            <div class="ctb">
                <h2 class="st"><?php echo $lang['Run log']; ?></h2>
                <div class="alert trick-tip clearfix admincp-tip">
                    <div class="trick-tip-title">
                        <strong><?php echo $lang['Skills prompt']; ?></strong>
                    </div>
                    <p class="trick-tip-content"><?php echo $lang['Security log tip']; ?></p>
                </div>
                <div class="btn-group control-group" data-toggle="buttons-radio" id="record">
                    <a href="javascript:;" data-action="tableTab" data-param='{ "type": "admincp" }'
                       class="btn active"><?php echo $lang['Log admincp']; ?></a>
                    <a href="javascript:;" data-action="tableTab" data-param='{ "type": "illegal" }'
                       class="btn"><?php echo $lang['Log password mistake']; ?></a>
                    <a href="javascript:;" data-action="tableTab" data-param='{ "type": "login" }'
                       class="btn"><?php echo $lang['Log login record']; ?></a>
                </div>
                <div class="page-list">
                    <div class="page-list-header">
                        <div class="row" id="admincp_search">
                            <div class="span9">
                                <select style="width:150px;" id="time_scope">
                                    <option value=""><?php echo $lang['Select year archive']; ?></option>
                                    <?php foreach ($archive as $table): ?>
                                        <option value="<?php echo $table; ?>"><?php echo $table; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="datepicker span2 dib" id="date_start">
                                    <a href="javascript:;" class="datepicker-btn"></a>
                                    <input type="text" class="datepicker-input" name="starttime" id="start_time">
                                </div>
                                <?php echo $lang['To']; ?>
                                <div class="datepicker span2 dib" id="date_end">
                                    <a href="javascript:;" class="datepicker-btn"></a>
                                    <input type="text" class="datepicker-input" name="endtime" id="end_time">
                                </div>
                                <a href="javascript:void(0);" id="query_act"
                                   class="btn"><?php echo $lang['Security log search']; ?></a>
                            </div>
                            <div class="span3 pull-right">
                                <select id="actions">
                                    <option value="" selected=""><?php echo $lang['All of it']; ?></option>
                                    <?php foreach ($actions as $key => $value) : ?>
                                        <?php
                                        if ($key == 'default') {
                                            continue;
                                        }
                                        $label = $lang["Action {$key}"];
                                        ?>
                                        <optgroup label="<?php echo $label; ?>">
                                            <?php foreach ($value as $action => $desc) : ?>
                                                <?php $val = $key . '.' . $action; ?>
                                                <option value="<?php echo $val; ?>"><?php echo $desc; ?></option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="page-list-mainer">
                        <!-- 后台访问 -->
                        <div class="table-admincp">
                            <table class="table table-striped table-condensed" id="table_admincp">
                                <thead>
                                <tr>
                                    <th width="60"><?php echo $lang['Operator']; ?></th>
                                    <th width="80"><?php echo $lang['Ip address']; ?></th>
                                    <th width="120"><?php echo $lang['Time']; ?></th>
                                    <th width="120"><?php echo $lang['Action']; ?></th>
                                    <th><?php echo $lang['Other']; ?></th>
                                </tr>
                                </thead>
                            </table>
                        </div>
                        <div class="table-login" style="display:none;">
                            <table class="table table-striped table-condensed" id="table_login">
                                <thead>
                                <tr>
                                    <th><?php echo $lang['Time']; ?></th>
                                    <th><?php echo $lang['Ip address']; ?></th>
                                    <th><?php echo $lang['Try username']; ?></th>
                                    <th><?php echo $lang['Try password']; ?></th>
                                    <th><?php echo $lang['Login method']; ?></th>
                                </tr>
                                </thead>
                            </table>
                        </div>
                        <div class="table-illegal" style="display:none;">
                            <table class="table table-striped table-condensed" id="table_illegal">
                                <thead>
                                <tr>
                                    <th><?php echo $lang['Time']; ?></th>
                                    <th><?php echo $lang['Ip address']; ?></th>
                                    <th><?php echo $lang['Try username']; ?></th>
                                    <th><?php echo $lang['Try password']; ?></th>
                                </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
    Ibos.app.s({level: "<?php //echo $con["level"]; ?>"})
</script>
<script src="<?php echo STATICURL; ?>/js/lib/dataTable/js/jquery.dataTables.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo $assetUrl; ?>/js/db_security.js"></script>