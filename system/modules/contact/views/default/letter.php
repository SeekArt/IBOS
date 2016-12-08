
<!-- private css -->
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/contactList.css?<?php echo VERHASH; ?>">
<div class="mc clearfix">
    <!-- Sidebar -->    
    <?php echo $this->getSidebar(); ?>
    <div class="mcr cl-mcr">
        <div class="page-list">
            <div class="page-list-header cl-list-header" id="cl_list_header">
                <div class="clearfix cl-funbar" id="cl_funbar">
                    <div class="search pull-left span8" id="name_search">
                        <input type="text" placeholder="<?php echo $lang['Enter the name check']; ?>" id="search_area">
                        <a href="javascript:;">search</a>
                        <input type="hidden" name="type" id="normal_search">
                    </div>
                    <div class="pull-right mr">
                        <button type="button" class="btn btn-default mrm" id="export_user">
                            <span class="o-pc-export"></span>
                            导出本页
                        </button>
                        <button type="button" class="btn btn-default" id="print_user">
                            <span class="o-pc-print"></span>
                            打印本页
                        </button>
                    </div>
                </div>
            </div>
            <div class="page-list-mainer" id="mainer">
                <div class="cl-rolling-sidebar" id="cl_rolling_sidebar">
                    <div class="personal-info" id="personal_info" style="width:520px; height:100%;"></div>
                </div>
                <div class="posr ovh clearfix">
                    <div class="exist-data">
                        <div class="contact-table" id="user_datalist"></div>
                        <div class="contact-table" id="user_searchlist"></div>
                    </div>
                    <div class="inexist-data">
                        <div class="no-data-tip"></div>
                    </div>
                </div>
            </div>
            <form id="export_form" method="post">
                <input type="hidden" name="uids" value="">
            </form>
        </div>
    </div>
</div>
<script src="<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo $assetUrl; ?>/js/contact.js?<?php echo VERHASH; ?>"></script>
