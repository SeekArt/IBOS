
<div class="aside" id="aside">
    <div class="sbb sbbl sbbf">
        <ul class="nav nav-strip nav-stacked">
            <li class="active">
                <a href="<?php echo $this->createUrl('default/index'); ?>">
                    <i class="o-company-cl"></i>
                    <?php echo $lang['Company contact']; ?>
                </a>
                <div>
                <table class="org-dept-table">
                    <tbody>
                    <tr data-id='0' data-pid='0' id="corp_unit">
                        <td>
                            <a href='javascript:;'
                               class='org-dep-name'><i
                                    class='os-company'></i> <?php echo isset($unit['fullname']) ? $unit['fullname'] : ''; ?>
                            </a>
                        </td>
                    </tr>
                    </tbody>
                </table>
                    <div class="ztree-wrap">
                        <ul id="utree" class="ztree org-utree"></ul>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</div>