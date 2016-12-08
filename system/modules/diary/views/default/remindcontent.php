<div style="width:493px; margin:0 auto;padding-bottom:15px;border-bottom:1px dashed #d7d4d4">
    <span style="width:330px; font-size:14px; font-weight:bold; color:#1180c6;"><?php echo $realname; ?>
        　<?php echo $date; ?>　工作日志</span>
</div>
<?php if (!empty($originalPlan)): ?>
    <p style="font-weight:bold;font-size:14px; color:#50545f;"><?php echo $lang['Original plan']; ?></p>
    <?php foreach ($originalPlan as $key => $orgplan): ?>
        <p style="font-size:14px; color:#50545f;"><?php echo $key + 1; ?>.<?php echo $orgplan; ?></p>
    <?php endforeach; ?>
    <br>
<?php endif; ?>
<?php if (!empty($planOutside)): ?>
    <p style="font-weight:bold;font-size:14px; color:#50545f;"><?php echo $lang['Unplanned']; ?></p>
    <?php foreach ($planOutside as $key => $outplan): ?>
        <p style="font-size:14px; color:#50545f;"><?php echo $key + 1; ?>.<?php echo $outplan['content']; ?></p>
    <?php endforeach; ?>
    <br>
<?php endif; ?>
<p style="font-weight:bold;font-size:14px; color:#50545f;"><?php echo $lang['Summary']; ?></p>
<p style="font-size:14px; color:#50545f;"><?php echo $content; ?></p>
<br>
<p style="font-weight:bold;font-size:14px; color:#50545f;"><?php echo $lang['Plan']; ?><span
        style="font-size:12px;">(<?php echo $plantime; ?>)</span></p>
<?php foreach ($plan as $key => $p): ?>
    <p style="font-size:14px; color:#50545f;"><?php echo $key + 1; ?>.<?php echo $p['content']; ?></p>
<?php endforeach; ?>