<div class="ct">
    <div class="clearfix">
        <h1 class="mt"><?php echo $lang['Contact']; ?></h1>
    </div>
    <form id="contact_hide_form" action="<?php echo $this->createUrl('api/addhidemobile') ?>" method="post" class="form-horizontal">
        <div class="ctb">
            <h2 class="st"><?php echo $lang['Hide mobile']; ?></h2>
            <div class="ctbw">
                <div class="control-group">
                    <label class="control-label">选择人员</label>
                    <div class="controls">
                        <input type="text" name="publishscope" id="publish_scope">
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label"></label>
                    <div class="controls">
                        <button type="submit" name="updateSubmit"
                            class="btn btn-primary btn-large btn-submit"><?php echo $lang['Submit']; ?></button>
                    </div>
                </div>
            </div>
        </div>
            
    </form>
</div>
<script>
    var $publishscope = $("#publish_scope");
    
    $.post(Ibos.app.url("contact/api/hiddenuidarr"), null, function(res){
        if( res.isSuccess ){
            $publishscope.val(res.data.users.join(","));
            $publishscope.userSelect({
                type: "user",
                data: Ibos.data.get('user')
            });
        }else{
            Ui.tip(res.msg, 'danger');
        }
    }, 'json');
    $("#contact_hide_form").submit(function(){
        $.post(Ibos.app.url("contact/api/addhidemobile"), {
            publishscope: $publishscope.val()
        },function(res){
            if( res.isSuccess ){
                Ui.tip( res.msg );
                location.reload();
            }else{
                Ui.tip(res.msg, 'danger');
            }
        }, "json");
        return false;
    });
</script>
