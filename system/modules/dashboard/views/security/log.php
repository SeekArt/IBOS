 <?php  use application\core\utils\String; ?> 
<div class="ct">
    <div class="clearfix">
        <h1 class="mt"><?php echo $lang['Security setting']; ?></h1>
        <!-- @Todo: PHP -->
        <ul class="mn">
            <li>
                <a href="<?php echo $this->createUrl( 'security/setup' ); ?>"><?php echo $lang['Account security setup']; ?></a>
            </li>
            <li>
                <span><?php echo $lang['Run log']; ?></span>
            </li>
            <li>
                <a href="<?php echo $this->createUrl( 'security/ip' ); ?>"><?php echo $lang['Disabled ip']; ?></a>
            </li>
        </ul>
    </div>
    <div>
        <form action="" class="form-horizontal">
            <!-- 运行记录 start -->
            <div class="ctb">
                <h2 class="st"><?php echo $lang['Run log']; ?></h2>
                <?php if ( $level == 'admincp' ): ?>
                    <div class="alert trick-tip clearfix">
                        <div class="trick-tip-title">
                            <strong><?php echo $lang['Skills prompt']; ?></strong>
                        </div>
                        <p class="trick-tip-content"><?php echo $lang['Security log tip']; ?></p>
                    </div>
                <?php endif; ?>
                <div class="btn-group control-group" data-toggle="buttons-radio" id="record">
                    <a href="<?php echo $this->createUrl( 'security/log', array( 'level' => 'admincp' ) ); ?>" class="btn <?php if ( $level == 'admincp' ): ?>active<?php endif; ?>"><?php echo $lang['Log admincp']; ?></a>
                    <a href="<?php echo $this->createUrl( 'security/log', array( 'level' => 'illegal' ) ); ?>" class="btn <?php if ( $level == 'illegal' ): ?>active<?php endif; ?>"><?php echo $lang['Log password mistake']; ?></a>
                    <a href="<?php echo $this->createUrl( 'security/log', array( 'level' => 'login' ) ); ?>" class="btn <?php if ( $level == 'login' ): ?>active<?php endif; ?>"><?php echo $lang['Log login record']; ?></a>
                </div>
                <div class="page-list">
                    <div class="page-list-header">
                        <div class="row">	
                            <?php if ( $level == 'admincp' ): ?>
                                <div class="span9">
                                    <select style="width:150px;" id="time_scope">
                                        <option value=""><?php echo $lang['Select year archive']; ?></option>
                                        <?php foreach ( $archive as $table ): ?>
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
                                    <a href="javascript:void(0);" id="query_act" class="btn"><?php echo $lang['Security log search']; ?></a>
                                </div>
                                <div class="span3 pull-right">
                                    <select id="actions">
                                        <option <?php if ( $filterAct == '' ): ?>selected<?php endif; ?> value=""><?php echo $lang['All of it']; ?></option>
                                        <?php foreach ( $actions as $key => $value ) : ?>
                                            <?php
                                            if ( $key == 'default' ) {
                                                continue;
                                            }
                                            $label = $lang["Action {$key}"];
                                            ?>
                                            <optgroup label="<?php echo $label; ?>">
                                                <?php foreach ( $value as $action => $desc ) : ?>
                                                    <?php $val = $key . '.' . $action; ?>
                                                    <option <?php if ( $filterAct == $val ): ?>selected<?php endif; ?> value="<?php echo $val; ?>"><?php echo $desc; ?></option>
                                                <?php endforeach; ?>
                                            </optgroup>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="page-list-mainer">
                        <!-- 后台访问 -->
                        <table class="table table-striped table-condensed">
                            <thead>
                                <?php switch ( $level ) :case 'admincp': ?>
                                        <tr>
                                            <th width="60"><?php echo $lang['Operator']; ?></th>
                                            <th width="80"><?php echo $lang['Ip address']; ?></th>
                                            <th width="120"><?php echo $lang['Time']; ?></th>
                                            <th width="120"><?php echo $lang['Action']; ?></th>
                                            <th><?php echo $lang['Other']; ?></th>
                                        </tr>
                                        <?php
                                        break;
                                    case 'login':
                                        ?>
                                        <tr>
                                            <th><?php echo $lang['Time']; ?></th>
                                            <th><?php echo $lang['Ip address']; ?></th>
                                            <th><?php echo $lang['Try username']; ?></th>
                                            <th><?php echo $lang['Try password']; ?></th>
                                            <th><?php echo $lang['Login method']; ?></th>
                                        </tr>
                                        <?php
                                        break;
                                    case 'illegal':
                                        ?>
                                        <tr>
                                            <th><?php echo $lang['Time']; ?></th>
                                            <th><?php echo $lang['Ip address']; ?></th>
                                            <th><?php echo $lang['Try username']; ?></th>
                                            <th><?php echo $lang['Try password']; ?></th>
                                        </tr>
                                <?php endswitch; ?>
                            </thead>
                            <tbody>
                                <?php switch ( $level ) :case 'admincp': ?>
                                        <?php foreach ( $log as $key => $value ) : ?>
                                            <?php
                                            $row = json_decode( $value['message'], true );
                                            preg_match( "/r=(.[^;]*)/i", $row['param'], $operationInfo );
                                            if ( isset( $operationInfo[1] ) ) {
                                                $route = explode( '/', $operationInfo[1] );
                                                $actionDesc = @$actions[strtolower( $route[1] )][strtolower( $route[2] )];
                                            }
                                            ?>
                                            <tr>
                                                <td>
                                                    <?php echo $row['user']; ?>
                                                </td>
                                                <td><?php echo $row['ip']; ?></td>
                                                <td><?php echo date( 'y-n-j H:i', $value['logtime'] ); ?></td>
                                                <td><?php echo $actionDesc; ?></td>
                                                <td><span title="<?php echo $row['param']; ?>"><?php echo String::cutStr( $row['param'], 100 ); ?></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php
                                        break;
                                    case 'illegal':
                                        ?>
                                        <?php foreach ( $log as $key => $value ) : ?>
                                            <?php $row = json_decode( $value['message'], true ); ?>
                                            <tr>
                                                <td><?php echo date( 'y-n-j H:i', $value['logtime'] ); ?></td>
                                                <td><?php echo $row['ip']; ?></td>
                                                <td><?php echo $row['user']; ?></td>
                                                <td><?php echo $row['password']; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php
                                        break;
                                    case 'login':
                                        ?>
                                        <?php foreach ( $log as $key => $value ) : ?>
                                            <?php $row = json_decode( $value['message'], true ); ?>
                                            <tr>
                                                <td><?php echo date( 'y-n-j H:i', $value['logtime'] ); ?></td>
                                                <td><?php echo $row['ip']; ?></td>
                                                <td><?php echo $row['user']; ?></td>
                                                <td><?php echo $row['password']; ?></td>
                                                <td><?php echo $row['terminal']; ?></td>
                                            </tr>
        <?php endforeach; ?>
                        <?php endswitch; ?>
                            </tbody>
                        </table>	
                    </div>
                    <div class="page-list-footer">
<?php $this->widget( 'application\core\widgets\Page', array( 'pages' => $pages ) ); ?>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
Ibos.app.s({ level : "<?php echo $con["level"]; ?>"})
</script>
<script src="<?php echo $assetUrl; ?>/js/db_security.js"></script>