<div id="dialog_allowed_user">
    <div class="wb-userlist bdbs scroll">
        <table class="table table-crowd table-striped mbz">
            <tbody>
            <?php echo $this->renderPartial('digglistmore', array('list' => $list, 'followstates' => $followstates)); ?>
            </tbody>
        </table>
    </div>
    <a href="javascript:;" class="wb-userlist-more" data-action="loadMoreDiggUser"
       data-param='{"feedid": <?php echo $feedid; ?>, "offset": 5}'>
        <i class="cbtn o-more"></i>
        查看更多
    </a>
</div>