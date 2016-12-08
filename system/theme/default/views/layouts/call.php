<?php

use application\core\utils\Ibos;

?>

<div id="fun_call_dialog">
    <div class="fun-call-box clearfix">
        <div class="pull-left fun-box-left posr">
            <div class="fun-call-form" id="fun_call_form">
                <div class="fun-box-header clearfix">
                    <span class="box-header-title pull-left">打电话</span>
                    <div class="header-nav-block pull-left">
                        <ul class="nav nav-skid fun-header-nav" id="fun_header_nav">
                            <li class="active" data-type="unidirec">
                                <a href="javascript:;">单向呼叫</a>
                            </li>
                            <li data-type="bidirec">
                                <a href="javascript:;">双向呼叫</a>
                            </li>
                            <li data-type="meeting">
                                <a href="javascript:;">语音会议</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <form action="#" method="post" id="call_content_form">
                    <div class="fun-box-body" id="fun_box_body">
                        <div data-type="unidirec" class="call-content-wrap call-type-unidirec">
                            <div class="call-type-tip mb">
                                <i class="o-type-headsets"></i>
                                <span class="dib xwb mls">耳麦</span>
                                <i class="o-call-point"></i>
                                <span class="dib xwb mls">手机</span>
                                <i class="o-type-phone mls"></i>
                            </div>
                            <div class="number-input-block mb">
                                <div class="clearfix mbm">
                                    <div class="pull-left">
                                        <span>对方电话号码</span>
                                    </div>
                                    <div class="pull-right">
                                        <a href="javascript:;" class="active" id="dial_opt_toggle" data-toggle="dial">
                                            <i class="o-tip-down"></i>
                                            <span>拨号盘</span>
                                        </a>
                                    </div>
                                </div>
                                <div class="clearfix">
                                    <div class="pull-left other-select-block">
                                        <input type="text" data-init="select" id="other_phone_select"
                                               placeholder="您可以输入号码或者选人"/>
                                    </div>
                                    <div class="pull-right">
                                        <a href="javascript:;" class="btn select-person-btn" id="select_other_btn">
                                            <i class="o-select-person-tip"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="phone-dial-block">
                                <div class="phone-dial-content" data-content="dial">
                                    <input type="hidden" id="other_phone_number" data-input="hideVal"/>
                                    <ul class="phone-dial-list" id="phone_number_list" data-target="other_phone_select">
                                        <li>
                                            <a href="javascript:;" class="btn" data-value="1">1</a>
                                        </li>
                                        <li>
                                            <a href="javascript:;" class="btn" data-value="2">2</a>
                                        </li>
                                        <li>
                                            <a href="javascript:;" class="btn" data-value="3">3</a>
                                        </li>
                                        <li>
                                            <a href="javascript:;" class="btn" data-value="4">4</a>
                                        </li>
                                        <li>
                                            <a href="javascript:;" class="btn" data-value="5">5</a>
                                        </li>
                                        <li>
                                            <a href="javascript:;" class="btn" data-value="6">6</a>
                                        </li>
                                        <li>
                                            <a href="javascript:;" class="btn" data-value="7">7</a>
                                        </li>
                                        <li>
                                            <a href="javascript:;" class="btn" data-value="8">8</a>
                                        </li>
                                        <li>
                                            <a href="javascript:;" class="btn" data-value="9">9</a>
                                        </li>
                                        <li class="dial-toggle-btn">
                                            <a href="javascript:;" class="btn dial-up-btn" data-action="dialToggle">
                                                <i class="o-tip-up"></i>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="javascript:;" class="btn" data-value="0">0</a>
                                        </li>
                                        <li class="del-number-btn">
                                            <a href="javascript:;" class="btn del-number-btn" data-action="delPhoneNum">
                                                <i class="o-del-number-tip"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div>
                                <a href="javascript:;" data-type="unidirec" class="btn btn-success phone-call-btn"
                                   data-action="call">呼叫</a>
                            </div>
                        </div>
                        <div data-type="bidirec" class="call-content-wrap call-type-bidirec" style="display:none;">
                            <div class="call-type-tip mb">
                                <i class="o-type-phone"></i>
                                <span class="dib xwb mls">手机</span>
                                <i class="o-call-point"></i>
                                <span class="dib xwb mls">手机</span>
                                <i class="o-type-phone mls"></i>
                            </div>
                            <div class="number-input-block mb">
                                <input type="hidden" id="my_phone_select"
                                       value="<?php echo 'u_' . Ibos::app()->user->uid; ?>"/>
                            </div>
                            <div class="number-input-block mb">
                                <div class="clearfix mbm">
                                    <div class="pull-left">
                                        <span>对方电话号码</span>
                                    </div>
                                    <div class="pull-right">
                                        <a href="javascript:;" data-toggle="dial">
                                            <i class="o-tip-down"></i>
                                            <span>拨号盘</span>
                                        </a>
                                    </div>
                                </div>
                                <div class="clearfix">
                                    <div class="pull-left other-select-block" data-id="others_phone_select">
                                        <input type="text" id="others_phone_select" placeholder="您可以输入号码或者选人"/>
                                    </div>
                                    <div class="pull-right">
                                        <a href="javascript:;" class="btn select-person-btn" id="select_others_btn">
                                            <i class="o-select-person-tip"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="phone-dial-block">
                                <div class="phone-dial-content" data-content="dial">
                                    <input type="hidden" id="target_phone_number"/>
                                    <ul class="phone-dial-list" id="target_number_list">
                                        <li>
                                            <a href="javascript:;" class="btn" data-value="1">1</a>
                                        </li>
                                        <li>
                                            <a href="javascript:;" class="btn" data-value="2">2</a>
                                        </li>
                                        <li>
                                            <a href="javascript:;" class="btn" data-value="3">3</a>
                                        </li>
                                        <li>
                                            <a href="javascript:;" class="btn" data-value="4">4</a>
                                        </li>
                                        <li>
                                            <a href="javascript:;" class="btn" data-value="5">5</a>
                                        </li>
                                        <li>
                                            <a href="javascript:;" class="btn" data-value="6">6</a>
                                        </li>
                                        <li>
                                            <a href="javascript:;" class="btn" data-value="7">7</a>
                                        </li>
                                        <li>
                                            <a href="javascript:;" class="btn" data-value="8">8</a>
                                        </li>
                                        <li>
                                            <a href="javascript:;" class="btn" data-value="9">9</a>
                                        </li>
                                        <li class="dial-toggle-btn">
                                            <a href="javascript:;" class="btn dial-up-btn" data-action="dialToggle">
                                                <i class="o-tip-up"></i>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="javascript:;" class="btn" data-value="0">0</a>
                                        </li>
                                        <li class="del-number-btn">
                                            <a href="javascript:;" class="btn del-number-btn" id="del_number_btn">
                                                <i class="o-del-number-tip"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div>
                                <a href="javascript:;" data-action="call" data-type="bidirec"
                                   class="btn btn-success phone-call-btn">呼叫</a>
                            </div>
                        </div>
                        <div data-type="meeting" class="call-content-wrap call-type-meeting" style="display:none;">
                            <div class="call-type-tip mb">
                                <i class="o-type-chair"></i>
                                <span class="dib xwb mls">主持</span>
                                <i class="o-call-point"></i>
                                <span class="dib xwb mls">多人</span>
                                <i class="o-type-more mls"></i>
                            </div>
                            <div class="number-input-block mb">
                                <div class="clearfix mbm">
                                    <div class="pull-left">
                                        <span>内部参会人员</span>
                                    </div>
                                </div>
                                <div class="clearfix">
                                    <div class="pull-left other-select-block">
                                        <input type="text" id="meeting_phone_select" placeholder="通过同事录入号码"/>
                                    </div>
                                </div>
                            </div>
                            <div class="number-input-block mb">
                                <div class="clearfix mbm">
                                    <div class="pull-left">
                                        <span>外部参会人员</span>
                                    </div>
                                    <div class="pull-right">
                                        <!-- <a href="javascript:;" data-toggle="dial">
                                            <i class="o-tip-down"></i>
                                            <span>拨号盘</span>
                                        </a> -->
                                    </div>
                                </div>
                                <div class="clearfix">
                                    <div class="pull-left other-select-block">
                                        <input type="text" id="outside_meeting_phone" placeholder="您可以输入号码,按分号隔开多个"/>
                                    </div>
                                </div>
                            </div>
                            <div class="phone-dial-block">
                                <div class="phone-dial-content" data-content="dial" style="display:none;">
                                    <input type="hidden" id="outside_phone_number"/>
                                    <ul class="phone-dial-list">
                                        <li>
                                            <a href="javascript:;" class="btn">1</a>
                                        </li>
                                        <li>
                                            <a href="javascript:;" class="btn">2</a>
                                        </li>
                                        <li>
                                            <a href="javascript:;" class="btn">3</a>
                                        </li>
                                        <li>
                                            <a href="javascript:;" class="btn">4</a>
                                        </li>
                                        <li>
                                            <a href="javascript:;" class="btn">5</a>
                                        </li>
                                        <li>
                                            <a href="javascript:;" class="btn">6</a>
                                        </li>
                                        <li>
                                            <a href="javascript:;" class="btn">7</a>
                                        </li>
                                        <li>
                                            <a href="javascript:;" class="btn">8</a>
                                        </li>
                                        <li>
                                            <a href="javascript:;" class="btn">9</a>
                                        </li>
                                        <li class="dial-toggle-btn">
                                            <a href="javascript:;" class="btn dial-up-btn" data-action="dialToggle">
                                                <i class="o-tip-up"></i>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="javascript:;" class="btn">0</a>
                                        </li>
                                        <li class="del-number-btn">
                                            <a href="javascript:;" class="btn del-number-btn" data-action="delPhoneNum">
                                                <i class="o-del-number-tip"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div>
                                <a href="javascript:;" data-action="call" data-type="meeting"
                                   class="btn btn-success phone-call-btn">呼叫</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="pull-right fun-box-right">
            <div class="box-right-header">
                <span class="tcm fsl">拨号记录</span>
            </div>
            <div class="box-right-body">
                <div class="fill-nn low-show-block scroll" id="log_show_block">
                </div>
            </div>
        </div>
    </div>
</div>

<div id="member_select_box"></div>
<div id="member_others_box"></div>
<script type="text/javascript">
    var emptyAvatar = "static.php?type=avatar&uid=0&size=middle&engine=<?php echo ENGINE; ?>";
    Ibos.statics.loads([
        Ibos.app.getStaticUrl("/js/src/call.js"),
        Ibos.app.getStaticUrl("/js/src/call_dialog.js")
    ]);
</script>
