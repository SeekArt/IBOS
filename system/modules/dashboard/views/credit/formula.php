<div class="ct">
    <div class="clearfix">
        <h1 class="mt"><?php echo $lang['Integral set']; ?></h1>
        <ul class="mn">
            <li>
                <a href="<?php echo $this->createUrl('credit/setup'); ?>"><?php echo $lang['Expand credit']; ?></a>
            </li>
            <li>
                <span><?php echo $lang['Credit formula']; ?></span>
            </li>
            <li>
                <a href="<?php echo $this->createUrl('credit/rule'); ?>"><?php echo $lang['Credit rule']; ?></a>
            </li>
        </ul>
    </div>
    <form action="<?php echo $this->createUrl('credit/formula'); ?>" method="post" class="form-horizontal">
        <div>
            <!-- 总积分计算公式 start -->
            <div class="ctb">
                <h2 class="st"><?php echo $lang['Total credit formula']; ?></h2>
                <div>
                    <div class="ptc radius" id="calculator">
                        <div class="ptcs radius" data-component="display" id="calculator_screen">
                            <?php echo $creditFormulaExp; ?>
                        </div>
                        <input type="hidden" name="creditsFormula" value="<?php echo $creditsFormula; ?>"
                               id="calculator_input">
                        <input type="hidden" name="creditsFormulaExp" value="" id="calculator_expression">
                        <div class="clearfix">
                            <div class="pull-left" data-component="panel">
                                <ul id="credit_type" class="ptctl clearfix">
                                    <?php foreach ($data as $val): ?>
                                        <li><a href="javascript:;" data-id="<?php echo $val['cid']; ?>"
                                               data-type="entry"
                                               data-value="extcredits<?php echo $val['cid']; ?>"><?php echo $val['name']; ?></a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <div class="pull-right" data-component="keyboard">
                                <table class="ptckl">
                                    <tr>
                                        <td colspan="2"><a href="javascript:;" class="ptckl-col2 ptckl-back"
                                                           data-type="action" title="后退" data-value="back"></a></td>
                                        <td><a href="javascript:;" class="ptckl-clear" data-type="action"
                                               data-value="clear" title="清空"></a></td>
                                        <td><a href="javascript:;" class="ptckl-operator" data-type="operator"
                                               data-value="divide">÷</a></td>
                                    </tr>
                                    <tr>
                                        <td><a href="javascript:;" data-type="number" data-value="7">7</a></td>
                                        <td><a href="javascript:;" data-type="number" data-value="8">8</a></td>
                                        <td><a href="javascript:;" data-type="number" data-value="9">9</a></td>
                                        <td><a href="javascript:;" class="ptckl-operator" data-type="operator"
                                               data-value="multiply">×</a></td>
                                    </tr>
                                    <tr>
                                        <td><a href="javascript:;" data-type="number" data-value="4">4</a></td>
                                        <td><a href="javascript:;" data-type="number" data-value="5">5</a></td>
                                        <td><a href="javascript:;" data-type="number" data-value="6">6</a></td>
                                        <td><a href="javascript:;" class="ptckl-operator" data-type="operator"
                                               data-value="minus">-</a></td>
                                    </tr>
                                    <tr>
                                        <td><a href="javascript:;" data-type="number" data-value="1">1</a></td>
                                        <td><a href="javascript:;" data-type="number" data-value="2">2</a></td>
                                        <td><a href="javascript:;" data-type="number" data-value="3">3</a></td>
                                        <td rowspan="2"><a href="javascript:;" class="ptckl-operator ptckl-row2"
                                                           data-type="operator" data-value="plus">+</a></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"><a href="javascript:;" class="ptckl-col2" data-type="number"
                                                           data-value="0">0</a></td>
                                        <td><a href="javascript:;" class="ptckl-operator" data-type="bracket"
                                               data-value="(">()</a></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div>
            <button class="btn btn-primary btn-large btn-submit" name="creditSetupSubmit" value="1"
                    type="submit"><?php echo $lang['Submit'] ?></button>
        </div>
    </form>
</div>
<script src="<?php echo $assetUrl; ?>/js/db_credit_fomula.js"></script>