
<div class="ct">
    <div class="clearfix">
        <h1 class="mt"><?php echo $lang['Notify setup']; ?></h1>
    </div>
    <div>
        <form action="<?php echo $this->createUrl( 'notify/setup' ); ?>" method="post" id="sms_search_form">
            <!-- 短信发送管理 start -->
            <div class="ctb">
                <h2 class="st"><?php echo $lang['Notify setup']; ?></h2>
                <div class="page-list">
                    <div class="page-list-mainer">
                        <form method="post" action="<?php echo $this->createUrl( 'notify/setup' ); ?>" class="form-horizontal">
                            <table class="table table-striped" id="sms_manage_table">
                                <thead>
                                    <tr>
                                        <th width="120"><?php echo $lang['Remind desc']; ?></th>
                                        <th width="120"><?php echo $lang['Module name']; ?></th>
                                        <th width="120"><?php echo $lang['Email remind']; ?></th>
                                        <th width="120"><?php echo $lang['Sms remind']; ?></th>
                                        <th width="120"><?php echo $lang['Sys remind']; ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ( $nodeList as $id => $node ): ?>
                                        <tr>
											<td><?php echo $node['nodeinfo']; ?></td>
                                            <td><?php echo $node['moduleName']; ?></td>                                            
                                            <td>
                                                <input type="checkbox" data-toggle="switch" name="sendemail[<?php echo $node['id']; ?>]" value='1' <?php if ( $node['sendemail'] ): ?>checked<?php endif; ?> />
                                            </td>
                                            <td>
                                                <input type="checkbox" data-toggle="switch" name="sendsms[<?php echo $node['id']; ?>]" value='1' <?php if ( $node['sendsms'] ): ?>checked<?php endif; ?> />
                                            </td>
                                            <td>
                                                <input type="checkbox" data-toggle="switch" name="sendmessage[<?php echo $node['id']; ?>]" value='1' <?php if ( $node['sendmessage'] ): ?>checked<?php endif; ?> />
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="5">
                                            <div>
                                                <input type="hidden" name="formhash" value="<?php echo FORMHASH; ?>">
                                                <input type="submit" value="保存" class="btn btn-large btn-primary btn-great">
                                            </div>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </form>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
