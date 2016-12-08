<link rel="stylesheet" href="static/css/code.css">
<div class="fill-ss code-wrap">
    <div class="tdcode-wrap mbs posr">
        <div class="tdcode-pic-wrap">
            <img src="{wxcode}" width="270px" height="270px" alt="二维码" title="二维码">
        </div>
    </div>
    <div class="banner" id="banner">
        <div class="bnrBom"></div>
        <div class="hd">
            <div class="posr">
                <div class="liOn"></div>
                <ul class="clearfix"></ul>
            </div>
        </div>
        <div class="bd">
            <span class="prev"></span>
            <span class="next"></span>
            <ul class="fixed banner-cal">
                <li style="background:url(static/image/page/bnr_1.png) center bottom no-repeat;">
                </li>
                <li style="background:url(static/image/page/bnr_2.png) center bottom no-repeat;">
                </li>
                <li style="background:url(static/image/page/bnr_3.png) center bottom no-repeat;">
                </li>
                <li style="background:url(static/image/page/bnr_4.png) center bottom no-repeat;">
                </li>
                <li style="background:url(static/image/page/bnr_5.png) center bottom no-repeat;">
                </li>
            </ul>
        </div>
    </div>
</div>
<script src='static/js/src/jquery.SuperSlide2.11.js?'></script>
<script>
    (function () {
        $("#banner").slide({
            titCell: ".hd ul",
            mainCell: ".bd ul",
            effect: "fold",
            easing: "easeInOutExpo",
            autoPage: "<li></li>",
            autoPlay: true,
            interTime: 3000,
            delayTime: 1000,
            startFun: function (i) {
                $(".banner .liOn").animate({
                    "left": 18 * i + 2,
                    "top": -0.5
                });
            }
        });
    })();
</script>