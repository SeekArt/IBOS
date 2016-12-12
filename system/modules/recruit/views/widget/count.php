<div class="statistics-box">
    <span class="fsl log-title">招聘过程</span>
    <div class="statistics-area">
        <div id="tendency_pic" class="statistics-content">
        </div>
    </div>
</div>
<div class="statistics-box clearfix">
    <div class="half-statistics-box">
        <span class="fsl log-title">性别比例</span>
        <div class="half-statistics-area">
            <div id="sex_ratio" class="half-statistics-content">
            </div>
        </div>
    </div>
    <div class="half-statistics-box">
        <span class="fsl log-title">年龄结构</span>
        <div class="half-statistics-area">
            <div id="age_structure" class="half-statistics-content">
            </div>
        </div>
    </div>
</div>
<div class="statistics-box clearfix">
    <div class="half-statistics-box">
        <span class="fsl log-title">学历分布</span>
        <div class="half-statistics-area">
            <div id="education_distribute" class="half-statistics-content">
            </div>
        </div>
    </div>
    <div class="half-statistics-box">
        <span class="fsl log-title">工作年限</span>
        <div class="half-statistics-area">
            <div id="entry_time" class="half-statistics-content">
            </div>
        </div>
    </div>
</div>
<script src='<?php echo STATICURL; ?>/js/lib/echarts/echarts.min.js?<?php echo VERHASH; ?>'></script>
<script>
    Ibos.app.s({
        tendency: {
            xAxis: [<?php foreach ( $talentFlow->getXaxis() as $date ): ?>'<?php echo $date; ?>',<?php endforeach; ?>],
            series: [
                <?php foreach ($talentFlow->getSeries() as $series): ?>
                {
                    name: '<?php echo $series['name']; ?>',
                    type: 'line',
                    symbol: 'circle',
                    itemStyle: {
                        normal: {
                            lineStyle: {// 系列级个性化折线样式
                                width: 2,
                            }
                        },
                        emphasis: {
                            lineStyle: {// 系列级个性化折线样式
                                width: 4,
                            }
                        }
                    },
                    data: [<?php echo implode(',', $series['list']); ?>],
                },
                <?php endforeach; ?>
            ]
        },
        sex: [
            <?php foreach ($sexRatio->getSeries() as $series): ?>
            {
                value: <?php echo $series['count']; ?>,
                name: "<?php echo $series['sex']; ?>"
            },
            <?php endforeach; ?>
        ],
        age: [
            <?php foreach($age->getSeries() as $series): ?>
            {value: <?php echo $series['count']; ?>, name: "<?php echo $series['name']; ?>"},
            <?php endforeach; ?>
        ],
        education: [
            <?php foreach($degree->getSeries() as $series): ?>
            {value: <?php echo $series['count']; ?>, name: "<?php echo $series['name']; ?>"},
            <?php endforeach; ?>
        ],
        entry: [
            <?php foreach($workYears->getSeries() as $series): ?>
            {value: <?php echo $series['count']; ?>, name: "<?php echo $series['name']; ?>"},
            <?php endforeach; ?>
        ]
    });
</script>
<script src="<?php echo $this->getController()->getAssetUrl(); ?>/js/recruit_stats_index.js"></script>
