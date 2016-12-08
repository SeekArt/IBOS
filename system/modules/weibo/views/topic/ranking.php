<!-- private css -->
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/wbpublic.css?<?php echo VERHASH; ?>"/>
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/wbstyle.css?<?php echo VERHASH; ?>"/>
<div class="wrap">
    <div class="wb-topic clearfix">
        <!-- 右侧栏 -->
        <div class="wbc-right pull-right">
            <!-- 我的话题 -->
            <div class="mpanel mb">
                <div class="wb-tpc-user bdbs">
                    <div class="wbc-box">
                        <div class="clearfix">
                            <div class="pull-left wbb-pr10">
                                <a href="personal.html" class="wb-pub-opic">
                                    <img src="<?php echo $assetUrl; ?>/image/defaultAva.gif" alt=""></a>
                            </div>
                            <div class="pull-left wbb-pt10 wb-nf-info"><strong>小胖</strong>
                                <p>博思协创 打酱油份子</p>
                            </div>
                        </div>
                    </div>
                    <div class="wb-mytopic">
                        <p class="tits">
                            我的话题
                            <a href="#">(6)</a>
                        </p>
                        <ul class="wb-mytopic-list">
                            <li>
                                <a href="?r=weibo/topic/detail&k=IBOS">
                                    <span>123</span>
                                    #IBOS#设计创新讨论 <i class="o-wbi-mic"></i>
                                </a>
                            </li>
                            <li>
                                <a href="?r=weibo/topic/detail&k=IBOS">
                                    <span>123</span>
                                    #带着微博去旅行# <i class="o-wbi-mic"></i>
                                </a>
                            </li>
                            <li>
                                <a href="?r=weibo/topic/detail&k=IBOS">
                                    <span>123</span>
                                    #2013年会#
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    <span>123</span>
                                    #IBOS2.0发布#
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    <span>123</span>
                                    #测试什么的最讨厌了#
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="wb-tpc-etr rdb">
                    <div class="input-group mbs">
                        <span class="input-group-addon">#</span>
                        <input type="text" class="form-control" placeholder="输入话题申请主持人">
                        <span class="input-group-addon">#</span>
                    </div>
                    <button type="button" class="btn btn-warning btn-block">
                        <i class="o-wbi-wmic"></i>
                        申请主持人
                    </button>
                </div>
            </div>

            <!-- 最新话题 -->
            <div class="mpanel">
                <div class="wbc-box wbc-rt bdbs"><strong>最新话题</strong>
                </div>
                <div class="wb-new-friend">
                    <ul class="">
                        <li>
                            <div class="clearfix">
                                <!--同事信息-->
                                <div class="pull-left clearfix">
                                    <div class="pull-left wbb-pr10">
                                        <a href="#" class="wb-pub-opic">
                                            <img src="<?php echo $assetUrl; ?>/image/defaultAva.gif" alt="">
                                            <i class="o-wbi-cmic"></i>
                                        </a>
                                    </div>
                                    <div class="pull-right wbb-pt10 wb-nf-info">
                                        <strong>小胖</strong>
                                        <p>博思协创 打酱油份子</p>
                                    </div>
                                </div>
                            </div>

                            <div class="wb-nf-notes">
                                <a href="#" class="xcbu">#打酱油啊打酱油#</a>
                                <div class="wb-nf-arr"><i></i></div>
                            </div>
                        </li>
                        <li>
                            <div class="clearfix">
                                <!--同事信息-->
                                <div class="pull-left clearfix">
                                    <div class="pull-left wbb-pr10">
                                        <a href="#" class="wb-pub-opic">
                                            <img src="<?php echo $assetUrl; ?>/image/defaultAva.gif" alt="">
                                            <i class="o-wbi-cmic"></i>
                                        </a>
                                    </div>
                                    <div class="pull-right wbb-pt10 wb-nf-info">
                                        <strong>por</strong>
                                        <p>博思协创 打酱油份子</p>
                                    </div>
                                </div>
                            </div>

                            <div class="wb-nf-notes">
                                <a href="#" class="xcbu">#带着微博去旅行#</a>
                                <div class="wb-nf-arr"><i></i></div>
                            </div>
                        </li>
                        <li>
                            <div class="clearfix">
                                <!--同事信息-->
                                <div class="pull-left clearfix">
                                    <div class="pull-left wbb-pr10">
                                        <a href="#" class="wb-pub-opic">
                                            <img src="<?php echo $assetUrl; ?>/image/test/userAva1.jpg" alt="">
                                            <i class="o-wbi-cmic"></i>
                                        </a>
                                    </div>
                                    <div class="pull-right wbb-pt10 wb-nf-info">
                                        <strong>por</strong>
                                        <p>博思协创 打酱油份子</p>
                                    </div>
                                </div>
                            </div>
                            <div class="wb-nf-notes">
                                <a href="#" class="xcbu">#随便写的#</a>
                                <div class="wb-nf-arr"><i></i></div>
                            </div>

                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- 主内容区 -->
        <div class="wbc-left pull-left">
            <div class="wb-tpc-join mpanel">
                <div class="tit">热门话题榜</div>
                <div class="wb-topic-rank">
                    <table class="table table-striped table-hover table-condensed bdbs">
                        <thead>
                        <tr>
                            <th width="40">排名</th>
                            <th>话题</th>
                            <th width="160">主持人</th>
                            <th width="60">微博讨论</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>
                                <span class="wbi-rank-one">1</span>
                            </td>
                            <td><a href="?r=weibo/topic/detail&k=IBOS">#成长路上#</a></td>
                            <td>
									<span href="#" class="wb-tpcr-pic">
										<img src="<?php echo $assetUrl; ?>/image/test/userAva3.jpg" alt=""></span>
                                <span>the Bird</span>
                            </td>
                            <td>
                                <div class="xar xwb fss xcr">423</div>
                            </td>
                        </tr>
                        <tr>
                            <td><a href=""></a>
                                <span class="wbi-rank-two">2</span>
                            </td>
                            <td><a href="?r=weibo/topic/detail&k=IBOS">#安妮海瑟薇#</a></td>
                            <td>
									<span href="#" class="wb-tpcr-pic">
										<img src="<?php echo $assetUrl; ?>/image/test/userAva2.jpg" alt=""></span>
                                <span>the Bird</span>
                            </td>
                            <td>
                                <div class="xar xwb fss xcr">400</div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="wbi-rank-thr">3</span>
                            </td>
                            <td><a href="?r=weibo/topic/detail&k=IBOS">#天猫双11来了#</a></td>
                            <td>
									<span href="#" class="wb-tpcr-pic">
										<img src="<?php echo $assetUrl; ?>/image/test/userAva1.jpg" alt=""></span>
                                <span>the Bird</span>
                            </td>
                            <td>
                                <div class="xar xwb fss xcr">399</div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="wbi-rank-other">4</span>
                            </td>
                            <td><a href="?r=weibo/topic/detail&k=IBOS">#天猫双11来了#</a></td>
                            <td>
									<span href="#" class="wb-tpcr-pic">
										<img src="<?php echo $assetUrl; ?>/image/test/userAva2.jpg" alt=""></span>
                                <span>the Bird</span>
                            </td>
                            <td>
                                <div class="xar xwb fss">392</div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="wbi-rank-other">5</span>
                            </td>
                            <td><a href="?r=weibo/topic/detail&k=IBOS">#天猫双11来了#</a></td>
                            <td>
									<span href="#" class="wb-tpcr-pic">
										<img src="<?php echo $assetUrl; ?>/image/test/userAva3.jpg" alt=""></span>
                                <span>the Bird</span>
                            </td>
                            <td>
                                <div class="xar xwb fss">391</div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="wbi-rank-other">6</span>
                            </td>
                            <td><a href="?r=weibo/topic/detail&k=IBOS">#天猫双11来了#</a></td>
                            <td>
									<span href="#" class="wb-tpcr-pic">
										<img src="<?php echo $assetUrl; ?>/image/test/userAva4.jpg" alt=""></span>
                                <span>the Bird</span>
                            </td>
                            <td>
                                <div class="xar xwb fss">390</div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="wbi-rank-other">7</span>
                            </td>
                            <td><a href="?r=weibo/topic/detail&k=IBOS">#天猫双11来了#</a></td>
                            <td>
									<span href="#" class="wb-tpcr-pic">
										<img src="<?php echo $assetUrl; ?>/image/test/userAva5.jpg" alt=""></span>
                                <span>the Bird</span>
                            </td>
                            <td>
                                <div class="xar fss xwb">380</div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="wbi-rank-other">8</span>
                            </td>
                            <td><a href="?r=weibo/topic/detail&k=IBOS">#天猫双11来了#</a></td>
                            <td>
									<span href="#" class="wb-tpcr-pic">
										<img src="<?php echo $assetUrl; ?>/image/test/userAva6.jpg" alt=""></span>
                                <span>the Bird</span>
                            </td>
                            <td>
                                <div class="xar fss xwb">370</div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="wbi-rank-other">9</span>
                            </td>
                            <td><a href="?r=weibo/topic/detail&k=IBOS">#天猫双11来了#</a></td>
                            <td>
									<span href="#" class="wb-tpcr-pic">
										<img src="<?php echo $assetUrl; ?>/image/test/userAva6.jpg" alt=""></span>
                                <span>the Bird</span>
                            </td>
                            <td>
                                <div class="xar fss xwb">360</div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="wbi-rank-other">10</span>
                            </td>
                            <td><a href="?r=weibo/topic/detail&k=IBOS">#天猫双11来了#</a></td>
                            <td>
									<span href="#" class="wb-tpcr-pic">
										<img src="<?php echo $assetUrl; ?>/image/test/userAva6.jpg" alt=""></span>
                                <span>the Bird</span>
                            </td>
                            <td>
                                <div class="xar xwb fss">350</div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="wbi-rank-other">11</span>
                            </td>
                            <td><a href="?r=weibo/topic/detail&k=IBOS">#天猫双11来了#</a></td>
                            <td>
									<span href="#" class="wb-tpcr-pic">
										<img src="<?php echo $assetUrl; ?>/image/test/userAva6.jpg" alt=""></span>
                                <span>the Bird</span>
                            </td>
                            <td>
                                <div class="xar xwb fss">345</div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="wbi-rank-other">12</span>
                            </td>
                            <td><a href="?r=weibo/topic/detail&k=IBOS">#天猫双11来了#</a></td>
                            <td>
									<span href="#" class="wb-tpcr-pic">
										<img src="<?php echo $assetUrl; ?>/image/test/userAva6.jpg" alt=""></span>
                                <span>the Bird</span>
                            </td>
                            <td>
                                <div class="xar xwb fss">234</div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="wbi-rank-other">13</span>
                            </td>
                            <td><a href="?r=weibo/topic/detail&k=IBOS">#天猫双11来了#</a></td>
                            <td>
									<span href="#" class="wb-tpcr-pic">
										<img src="<?php echo $assetUrl; ?>/image/test/userAva6.jpg" alt=""></span>
                                <span>the Bird</span>
                            </td>
                            <td>
                                <div class="xar xwb fss">123</div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="wbi-rank-other">14</span>
                            </td>
                            <td><a href="?r=weibo/topic/detail&k=IBOS">#天猫双11来了#</a></td>
                            <td>
									<span href="#" class="wb-tpcr-pic">
										<img src="<?php echo $assetUrl; ?>/image/test/userAva6.jpg" alt=""></span>
                                <span>the Bird</span>
                            </td>
                            <td>
                                <div class="xar xwb fss">100</div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="wbi-rank-other">15</span>
                            </td>
                            <td><a href="?r=weibo/topic/detail&k=IBOS">#天猫双11来了#</a></td>
                            <td>
									<span href="#" class="wb-tpcr-pic">
										<img src="<?php echo $assetUrl; ?>/image/test/userAva6.jpg" alt=""></span>
                                <span>the Bird</span>
                            </td>
                            <td>
                                <div class="xar xwb fss">79</div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="wbi-rank-other">16</span>
                            </td>
                            <td><a href="?r=weibo/topic/detail&k=IBOS">#天猫双11来了#</a></td>
                            <td>
									<span href="#" class="wb-tpcr-pic">
										<img src="<?php echo $assetUrl; ?>/image/test/userAva6.jpg" alt=""></span>
                                <span>the Bird</span>
                            </td>
                            <td>
                                <div class="xar xwb fss">48</div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="wbi-rank-other">17</span>
                            </td>
                            <td><a href="?r=weibo/topic/detail&k=IBOS">#天猫双11来了#</a></td>
                            <td>
									<span href="#" class="wb-tpcr-pic">
										<img src="<?php echo $assetUrl; ?>/image/test/userAva6.jpg" alt=""></span>
                                <span>the Bird</span>
                            </td>
                            <td>
                                <div class="xar xwb fss">14</div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="wbi-rank-other">18</span>
                            </td>
                            <td><a href="?r=weibo/topic/detail&k=IBOS">#天猫双11来了#</a></td>
                            <td>
									<span href="#" class="wb-tpcr-pic">
										<img src="<?php echo $assetUrl; ?>/image/test/userAva6.jpg" alt=""></span>
                                <span>the Bird</span>
                            </td>
                            <td>
                                <div class="xar xwb fss">3</div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="wbi-rank-other">19</span>
                            </td>
                            <td><a href="?r=weibo/topic/detail&k=IBOS">#天猫双11来了#</a></td>
                            <td>
									<span href="#" class="wb-tpcr-pic">
										<img src="<?php echo $assetUrl; ?>/image/test/userAva5.jpg" alt=""></span>
                                <span>the Bird</span>
                            </td>
                            <td>
                                <div class="xar xwb fss">2</div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="wbi-rank-other">20</span>
                            </td>
                            <td><a href="?r=weibo/topic/detail&k=IBOS">#天猫双11来了#</a></td>
                            <td>
									<span href="#" class="wb-tpcr-pic">
										<img src="<?php echo $assetUrl; ?>/image/test/userAva6.jpg" alt=""></span>
                                <span>the Bird</span>
                            </td>
                            <td>
                                <div class="xar xwb fss">1</div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <div class="mbox-layer">
                        <div class="xac fill-nn">
                            <a href="#" class="link-more">
                                <i class="cbtn o-more"></i>
                                <span class="ilsep">查看30天内记录</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="dialog_topic_edit" style="width: 480px;">
    <h4 class="xwb">#三星字库门#</h4>
    <div class="fss tcm mbs">配图可以让话题更吸引人，文字介绍可以让话题更容易理解</div>
    <div class="wb-topic-edit-box">
        <span class="charcount">还可以输入 <strong>140</strong> 字</span>
        <textarea name="" id="" rows="1"></textarea>
        <!--上传图片-->
        <div class="wb-upload-img mb" data-node-type="picBox">
            <!-- Flash占位 -->
            <div class="wb-upload-btn">
                <span id="wb_imgupload"></span>
            </div>
            <!-- 上传引导 -->
            <div class="wb-upload-holder">
                <div class="wb-upload-pic">
                    <i class="pic-holder"></i>
                    <p><strong>上传一张图片</strong></p>
                </div>
            </div>
            <!-- 成功提示 -->
            <div class="wb-upload-success-tip">
                <i class="cbtn o-ok active"></i>
                上传成功
            </div>
            <input type="hidden" name="picid" data-node-type="picId">
        </div>
        <div class="pull-right">
            <label class="checkbox checkbox-inline">
                <input type="checkbox" checked>
                同时转发到微博
            </label>
            <button class="btn btn-primary btn-static mls">发布</button>
        </div>
    </div>
</div>
<script>
    Ui.dialog({
        id: 'd_topic_edit',
        title: "新增话题",
        content: document.getElementById('dialog_topic_edit')
    })

</script>