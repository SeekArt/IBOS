
<div class="ct">
    <div class="clearfix">
        <h1 class="mt"><?php echo $lang['Online upgrade']; ?></h1>
    </div>
    <div>
        <div class="ctb">
            <h2 class="st"><?php echo $lang['Online upgrade']; ?></h2>
            <div id="upgrade_info">

            </div>
        </div>
    </div>
</div>
<script type="text/ibos-template" id="progress_bar">
    <div style="width:300px;">
        <div id="progress_bar" class="progress progress-striped active" title="Progress-bar">
            <div class="progress-bar" style="width: 100%;"></div>
        </div>
    </div>
</script>
<script type="text/ibos-template" id="list_table">
    <div>
        <table class="table table-striped">
            <thead>
            <tr>
                <th><?php echo $lang['Upgrade list']; ?></th>
                <th><?php echo $lang['Operation']; ?></th>
            </tr>
            </thead>
            <%=list%>
        </table>
    </div>
</script>
<script type="text/ibos-template" id="list_tr">
    <tr>
        <td>
            <p> <%=desc%> <span class="label label-warning">NEW！</span></p>
        </td>
        <td>
            <%=op%>
        </td>
    </tr>
</script>
<script type="text/ibos-template" id="info">
    <div>
        <blockquote>
            <p><%=msg%></p>
        </blockquote>
    </div>
</script>
<script type="text/ibos-template" id="pre_update_list">
    <div>
        <h3><?php echo $lang['Upgrade preupdatelist']; ?></h3>
        <%=list%>
        <p></p>
        <p>
            <button type="button" data-target="<%=actionUrl%>" data-loading-text="<?php echo $lang['Downloading']; ?>"
                    autocomplete="off" data-act="processStep"
                    class="btn btn-primary btn-large"><?php echo $lang['Upgrade download']; ?></button>
        </p>
    </div>
</script>
<script type="text/ibos-template" id="upgrade_complete">
    <div>
        <div class="alert alert-success">
            <%=msg%>
            <br/>
            <p><?php echo $lang['Upgrade complete recommand']; ?></p>
            <button type="button" onclick="window.location.href = '<?php echo $this->createUrl('update/index'); ?>';"
                    class="btn btn-primary btn-large"><?php echo $lang['Update cache']; ?></button>
        </div>
    </div>
</script>

<script src="<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?= FORMHASH ?>"></script>
<script src="<?php echo $assetUrl; ?>/js/db_upgrade.js?<?= FORMHASH ?>"></script>
