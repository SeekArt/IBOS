<?php
use application\core\utils\Ibos;

?>

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
<script type="text/ibos-template" id="compare_list">
    <div>
        <h3><?php echo $lang['Upgrade diff show']; ?></h3>
        <div class="alert alert-error">
            <h4><?php echo $lang['Diff']; ?></h4>
            <ul class="list-inline"><%=diffList%></ul>
        </div>
        <div class="alert alert-info">
            <h4><?php echo $lang['Normal']; ?></h4>
            <ul class="list-inline"><%=normalList%></ul>
        </div>
        <div class="alert alert-success">
            <h4><?php echo $lang['New add']; ?></h4>
            <ul class="list-inline"><%=newList%></ul>
        </div>
        <blockquote>
            <p><?php echo $lang['Upgrade download file']; ?><code>/data/update/IBOS <%=version%>
                    Release[<%=release%>]</code></p>
        </blockquote>
        <blockquote>
            <p><?php echo $lang['Upgrade backup file']; ?><code>/data/back/IBOS <?php echo VERSION; ?>
                    Release[<?php echo VERSION_DATE; ?>]</code><?php echo $lang['Upgrade backup file2']; ?></p>
        </blockquote>
        <p>
            <button type="button" data-target="<%=actionUrl%>"
                    data-loading-text="<?php echo $lang['Action processing']; ?>" autocomplete="off"
                    data-act="processStep" class="btn <%=actionClass%> btn-large"><%=actionDesc%>
            </button>
        </p>
    </div>
</script>
<script type="text/ibos-template" id="update_confirm">
    <div>
        <div class="alert alert-error>"><%=msg%></div>
        <button type="button" data-target="<%=retryUrl%>" data-loading-text="<?php echo $lang['Action processing']; ?>"
                autocomplete="off" data-act="processStep" class="btn btn-large"></button>
        <button type="button" data-target="<%=ftpUrl%>" data-loading-text="<?php echo $lang['Action processing']; ?>"
                autocomplete="off" data-act="processStep" class="btn btn-large mls"></button>
    </div>
</script>
<script type="text/ibos-template" id="ftp_setup">
    <form id="sys_ftp_form" method="post" class="form-horizontal">
        <div class="control-group">
            <label class="control-label"><?php echo $lang['Enabled ssl']; ?></label>
            <div class="controls">
                <input type="checkbox" name="ftp[ssl]" value="1" data-toggle="switch" class="visi-hidden"/>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label"><?php echo $lang['Ftp host']; ?></label>
            <div class="controls">
                <input type="text" name="ftp[host]"/>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label"><?php echo $lang['Ftp port']; ?></label>
            <div class="controls">
                <input type="text" name="ftp[port]" value="25"/>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label"><?php echo $lang['Ftp user']; ?></label>
            <div class="controls">
                <input type="text" name="ftp[username]"/>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label"><?php echo $lang['Ftp pass']; ?></label>
            <div class="controls">
                <input type="text" name="ftp[password]"/>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label"><?php echo $lang['Ftp pasv']; ?></label>
            <div class="controls">
                <input type="checkbox" name="ftp[pasv]" value="1" data-toggle="switch" class="visi-hidden"/>
            </div>
        </div>
    </form>
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

<script src="<?php echo $assetUrl; ?>/js/lang/zh-cn.js"></script>
<script src="<?php echo $assetUrl; ?>/js/db_upgrade.js"></script>
