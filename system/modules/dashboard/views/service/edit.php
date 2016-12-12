<div class="ct">
    <div id="cm_content">
        <div>
            <form action="" method='post' class="form-horizontal">
                <!-- 企业QQ设置 start -->
                <div class="ctb">
                    <div class="ctbw">
                        <div>
                            <div class="control-group">
                                <label class="control-label">App ID</label>
                                <div class="controls">
                                    <input type="text" name='appid' value="<?php echo $setting['appid']; ?>"/>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">SECRET</label>
                                <div class="controls">
                                    <input type="text" name='secret' value="<?php echo $setting['secret']; ?>"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label"></label>
                    <div class="controls">
                        <button class="btn btn-primary btn-large btn-submit" type="submit">开通云服务</button>
                    </div>
                </div>
                <input type='hidden' name='formhash' value='<?php echo FORMHASH; ?>'/>
            </form>
        </div>
    </div>
</div>