<!-- private css -->
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/recruit.css?<?php echo VERHASH; ?>">
<link rel="stylesheet" href="<?php echo $statAssetUrl; ?>/css/statistics.css?<?php echo VERHASH; ?>">
<body class="ibbody">
<div class="ibcontainer">
    <div class="wrap" id="mainer">
        <div class="mc clearfix">
            <!-- Sidebar -->
            <?php echo $this->getSidebar(); ?>
            <div class="mcr">
                <div class="page-list">
                    <div class="page-list-header">
                        <?php echo $this->widget($widgets['header'], array('type' => $type, 'timestr' => $timestr), true); ?>
                    </div>
                    <div class="page-list-mainer">
                        <div>
                            <?php echo $this->widget($widgets['summary'], array('type' => $type, 'timestr' => $timestr), true); ?>
                            <?php echo $this->widget($widgets['count'], array('type' => $type, 'timestr' => $timestr), true); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src='<?php echo $assetUrl; ?>/js/recruit.js?<?php echo VERHASH; ?>'></script>
</body>