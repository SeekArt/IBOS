<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>浏览器升级提示</title>
    <style>
        body {
            background: #fff;
            font-family: 'Microsoft Yahei';
            font-size: 18px;
            color: #82939E;
            font-weight: 500;
        }

        body, ul, li, p, a {
            margin: 0;
            padding: 0;
        }

        .clearfix {
            *zoom: 1;
        }

        .clearfix:before, .clearfix:after {
            content: "";
            display: block;
        }

        .clearfix:after {
            clear: both;
        }

        .container {
            position: relative;
            width: 630px;
            margin: 100px auto 0;
        }

        .container i {
            display: block;
            background-image: url(data/login/ie_guide.png);
        }

        .o-guide-logo {
            width: 545px;
            height: 328px;
            background-position: 0 0;
            margin: 0 auto;
        }

        .o-guide-tips {
            width: 626px;
            height: 50px;
            background-position: 0 -328px;
            margin: 30px auto;
        }

        .o-browser-ff,
        .o-browser-op,
        .o-browser-ch,
        .o-browser-sa {
            width: 42px;
            height: 42px;
            margin: 6px 16px 4px;
        }

        .o-browser-ff {
            background-position: 0 -380px;
        }

        .o-browser-op {
            background-position: -46px -380px;
        }

        .o-browser-ch {
            background-position: -91px -380px;
        }

        .o-browser-sa {
            background-position: -140px -380px;
        }

        .o-guide-list1,
        .o-guide-list2,
        .o-guide-list3,
        .o-guide-list4 {
            width: 20px;
            height: 20px;
            float: left;
            margin-right: 5px;
            position: relative;
            top: 3px;
        }

        .o-guide-list1 {
            background-position: -210px -379px;
        }

        .o-guide-list2 {
            background-position: -231px -379px;
        }

        .o-guide-list3 {
            background-position: -252px -379px;
        }

        .o-guide-list4 {
            background-position: -273px -379px;
        }

        .container li {
            list-style: none;
        }

        .container a {
            color: #3397DB;
            text-align: center;
        }

        .mb {
            margin-bottom: 20px;
        }

        .guide-box .above-tips {
            color: #B2C0D1;
            margin-left: 5px;
        }

        .browser-list ul {
            width: 100%;
            height: 75px;
            padding: 15px 0 0;
        }

        .browser-list li {
            float: left;
            width: 76px;
            height: 76px;
            margin: 0 35px;
        }

        .browser-list li a {
            display: block;
            width: 74px;
            height: 74px;
            border: 1px solid #F7F7F7;
            border-radius: 3px;
        }

        .browser-box {
            text-decoration: none;
            font-size: 14px;
        }

        .browser-box p {
            color: #82939E;
        }

        .browser-list li a:hover {
            border: 1px solid #77D8EE;
        }

        .browser-list li a:hover p {
            color: #77D8EE;
        }
    </style>
    <script type="text/javascript">
        (function () {
            var ua = navigator.userAgent.toLowerCase(),
                sys = ua.match(/msie ([\d.]+)/);
            if (!window.ActiveXObject && !sys || (sys && parseFloat(sys[1]) >= 8)) {
                window.location.href = location.origin + location.pathname;
            }
        })();
    </script>
</head>
<body>
<div class="container">
    <div class="img-box">
        <i class="o-guide-logo"></i>
        <i class="o-guide-tips"></i>
    </div>
    <div class="guide-box">
        <p class="mb">为了更好的使用系统，您可以选择：</p>
        <ul>
            <li class="mb">
                <i class="o-guide-list1"></i>
                <span>升级浏览器<small class="above-tips">(&nbsp;已经是IE8及以上浏览器？<a
                            href="http://doc.ibos.com.cn/article/detail/id/315" target="_blank">请点击这里</a>&nbsp;)
                    </small></span>
            </li>
            <li class="mb">
                <div>
                    <i class="o-guide-list2"></i>
                    <span>安装强大、好用的标准浏览器</span>
                </div>
                <div class="browser-list clearfix">
                    <ul>
                        <li>
                            <a class="browser-box" href="http://www.firefox.com.cn/download/" target="_blank">
                                <i class="o-browser-ff"></i>
                                <p>Firefox</p>
                            </a>
                        </li>
                        <li>
                            <a class="browser-box" href="http://www.opera.com/zh-cn" target="_blank">
                                <i class="o-browser-op"></i>
                                <p>Opera</p>
                            </a>
                        </li>
                        <li>
                            <a class="browser-box" href="http://www.google.cn/chrome/browser/desktop/index.html"
                               target="_blank">
                                <i class="o-browser-ch"></i>
                                <p>Chrome</p>
                            </a>
                        </li>
                        <li>
                            <a class="browser-box" href="http://www.apple.com/cn/safari/" target="_blank">
                                <i class="o-browser-sa"></i>
                                <p>Safari</p>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="mb">
                <i class="o-guide-list3"></i>
                <span>如果您还是习惯使用当前的浏览器，请安装<a href="http://www.ibos.com.cn/file/99" target="_blank">Chrome框架</a></span>
            </li>
            <li class="mb">
                <i class="o-guide-list4"></i>
                <span><a href="/" target="_self">返回首页</a></span>
            </li>
        </ul>
    </div>
</div>
</body>
</html>
