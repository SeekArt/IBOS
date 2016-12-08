/*!
* artDialog 5.0.2
* Date: 2012-11-11
* https://github.com/aui/artDialog
* (c) 2009-2012 TangBin, http://www.planeArt.cn
*
* This is licensed under the GNU LGPL, version 2.1 or later.
* For details, see: http://creativecommons.org/licenses/LGPL/2.1/
*/

;(function ($, window, undefined) {

// artDialog 只支持 xhtml 1.0 或者以上的 DOCTYPE 声明
if (document.compatMode === 'BackCompat') {
    throw new Error('artDialog: Document types require more than xhtml1.0');
};

var _singleton,
    _count = 0,
    _root = $(document.getElementsByTagName('html')[0]),
    _expando = 'artDialog' + + new Date,
    _isIE6 = window.VBArray && !window.XMLHttpRequest,
    _isMobile = 'createTouch' in document && !('onmousemove' in document)
        || /(iPhone|iPad|iPod)/i.test(navigator.userAgent),
    _isFixed = !_isIE6 && !_isMobile;

    
var artDialog = function (config, ok, cancel) {

    config = config || {};
    
    if (typeof config === 'string' || config.nodeType === 1) {
    
        config = {content: config, fixed: !_isMobile};
    };
    
    
    var api, defaults = artDialog.defaults;
    var elem = config.follow = this.nodeType === 1 && this || config.follow;
        
    
    // 合并默认配置
    for (var i in defaults) {
        if (config[i] === undefined) {
            config[i] = defaults[i];
        };
    };

    
    config.id = elem && elem[_expando + 'follow'] || config.id || _expando + _count;
    api = artDialog.list[config.id];
    
    
    
    if (api) {
        if (elem) {
            api.follow(elem)
        };
        api.zIndex().focus();
        return api;
    };
    
    
    
    // 目前主流移动设备对fixed支持不好
    if (!_isFixed) {
        config.fixed = false;
    };
    
    // !$.isArray(config.button)
    if (!config.button || !config.button.push) {
        config.button = [];
    };
      
    
    // 取消按钮
    if (cancel !== undefined) {
        config.cancel = cancel;
    };
    
    if (config.cancel) {
        config.button.push({
            id: 'cancel',
            value: config.cancelVal,
            callback: config.cancel
        });
    };  
    
    // 确定按钮
    if (ok !== undefined) {
        config.ok = ok;
    };
    
    if (config.ok) {
        config.button.push({
            id: 'ok',
            value: config.okVal,
            callback: config.ok,
            focus: true
        });
    };

    
    // 更新 zIndex 全局配置
    artDialog.defaults.zIndex = config.zIndex;
    
    _count ++;

    return artDialog.list[config.id] = _singleton ?
        _singleton.constructor(config) : new artDialog.fn.constructor(config);
};

artDialog.version = '5.0.2';

artDialog.fn = artDialog.prototype = {
    
    /** @inner */
    constructor: function (config) {
        var DOM;
        
        this.closed = false;
        this.config = config;
        this.DOM = DOM = this.DOM || this._getDom();
        
        config.skin && DOM.wrap.addClass(config.skin);
        
        DOM.wrap.css('position', config.fixed ? 'fixed' : 'absolute');
        DOM.close[config.cancel === false ? 'hide' : 'show']();
        DOM.content.css('padding', config.padding);
        
        this.button.apply(this, config.button);
        
        this.title(config.title)
        .content(config.content)
        .size(config.width, config.height)
        .time(config.time);
        
        this._reset();
        
        this.zIndex();
        config.lock && this.lock();
        
        this._addEvent();
        this[config.visible ? 'visible' : 'hidden']().focus();
        
        _singleton = null;
        
        config.init && config.init.call(this);
        
        return this;
    },
    
    
    /**
    * 设置内容
    * @param    {String, HTMLElement, Object}   内容 (可选)
    */
    content: function (message) {
    
        var prev, next, parent, display,
            that = this,
            $content = this.DOM.content,
            content = $content[0];
        
        
        if (this._elemBack) {
            this._elemBack();
            delete this._elemBack;
        };
        
        
        if (typeof message === 'string') {
        
            $content.html(message);
        } else
        
        if (message && message.nodeType === 1) {
        
            // 让传入的元素在对话框关闭后可以返回到原来的地方
            display = message.style.display;
            prev = message.previousSibling;
            next = message.nextSibling;
            parent = message.parentNode;
            
            this._elemBack = function () {
                if (prev && prev.parentNode) {
                    prev.parentNode.insertBefore(message, prev.nextSibling);
                } else if (next && next.parentNode) {
                    next.parentNode.insertBefore(message, next);
                } else if (parent) {
                    parent.appendChild(message);
                };
                message.style.display = display;
                that._elemBack = null;
            };
            
            $content.html('');
            content.appendChild(message);
            $(message).show();
            
        };
        
        this._reset();
        
        return this;
    },
    
    
    /**
    * 设置标题
    * @param    {String, Boolean}   标题内容. 为 false 则隐藏标题栏
    */
    title: function (content) {
    
        var DOM = this.DOM,
            outer = DOM.outer,
            $title = DOM.title,
            className = 'd-state-noTitle';
        
        if (content === false || content == undefined) {
            $title.hide().html('');
            outer.addClass(className);
        } else {
            $title.show().html(content);
            outer.removeClass(className);
        };
        
        return this;
    },

    // px与%单位转换成数值 (百分比单位按照最大值换算)
    // 其他的单位返回原值
    _toNumber: function (thisValue, maxValue) {
        if (!thisValue && thisValue !== 0 || typeof thisValue === 'number') {
            return thisValue;
        };
        
        var last = thisValue.length - 1;
        if (thisValue.lastIndexOf('px') === last) {
            thisValue = parseInt(thisValue);
        } else if (thisValue.lastIndexOf('%') === last) {
            thisValue = parseInt(maxValue * thisValue.split('%')[0] / 100);
        };
        
        return thisValue;
    },
    
    /**
     * 位置(相对于可视区域)
     * @param   {Number, String}
     * @param   {Number, String}
     */
    position: function (left, top) {
    
        var DOM = this.DOM,
            wrap = DOM.wrap[0],
            $window = DOM.window,
            $document = DOM.document,
            fixed = this.config.fixed,
            dl = fixed ? 0 : $document.scrollLeft(),
            dt = fixed ? 0 : $document.scrollTop(),
            ww = $window.width(),
            wh = $window.height(),
            ow = wrap.offsetWidth,
            oh = wrap.offsetHeight,
            style = wrap.style;

        if( left || left == 0 ){
            left = this._toNumber(left, ww - ow);

            if (typeof left === 'number') {
                left = fixed ? (left += dl) : left + dl;
                style.left = Math.max(left, dl) + 'px';
            } else if (typeof left === 'string') {
                style.left = left;
            }

        }

        if( top || top == 0 ){
            top = this._toNumber(top, wh - oh);

            if (typeof top === 'number') {
                top = fixed ? (top += dt) : top + dt;
                style.top = Math.max(top, dt) + 'px';
            } else if (typeof top === 'string') {
                style.top = top;
            }
        }

        if( left == undefined && top == undefined ){
            left = (ww - ow) / 2 + dl;
            top =  (wh - oh) * 382 / 1000 + dt;// 黄金比例

            style.left = Math.max(parseInt(left), dl) + 'px';
            style.top = Math.max(parseInt(top), dt) + 'px';
        }

        if (this._follow) {
            this._follow.removeAttribute(_expando + 'follow');
            this._follow = null;
        }
        
        return this;
    },
    
    
    /**
    *   尺寸
    *   @param  {Number, String}    宽度
    *   @param  {Number, String}    高度
    */
    size: function (width, height) {
    
        var style = this.DOM.main[0].style;
        
        if (typeof width === 'number') {
            width = width + 'px';
        };
        
        if (typeof height === 'number') {
            height = height + 'px';
        };
            
        style.width = width;
        style.height = height;
        
        return this;
    },
    
    
    /**
    * 跟随元素
    * @param    {HTMLElement}
    */
    follow: function (elem) {
    
        var $elem = $(elem),
            config = this.config;
        
        
        // 隐藏元素不可用
        if (!elem || !elem.offsetWidth && !elem.offsetHeight) {
        
            return this.position(this._left, this._top);
        };
        
        var fixed = config.fixed,
            expando = _expando + 'follow',
            DOM = this.DOM,
            $window = DOM.window,
            $document = DOM.document,
            
            winWidth = $window.width(),
            winHeight = $window.height(),
            docLeft =  $document.scrollLeft(),
            docTop = $document.scrollTop(),
            offset = $elem.offset(),
            
            width = elem.offsetWidth,
            height = elem.offsetHeight,
            left = fixed ? offset.left - docLeft : offset.left,
            top = fixed ? offset.top - docTop : offset.top,
            
            wrap = this.DOM.wrap[0],
            style = wrap.style,
            wrapWidth = wrap.offsetWidth,
            wrapHeight = wrap.offsetHeight,
            setLeft = left - (wrapWidth - width) / 2,
            setTop = top + height,
            
            dl = fixed ? 0 : docLeft,
            dt = fixed ? 0 : docTop;
            
            
        setLeft = setLeft < dl ? left :
        (setLeft + wrapWidth > winWidth) && (left - wrapWidth > dl)
        ? left - wrapWidth + width
        : setLeft;

        
        setTop = (setTop + wrapHeight > winHeight + dt)
        && (top - wrapHeight > dt)
        ? top - wrapHeight
        : setTop;
        
        
        style.left = parseInt(setLeft) + 'px';
        style.top = parseInt(setTop) + 'px';
        
        
        this._follow && this._follow.removeAttribute(expando);
        this._follow = elem;
        elem[expando] = config.id;
        
        return this;
    },
    
    
    /**
    * 自定义按钮
    * @example
        button({
            value: 'login',
            callback: function () {},
            disabled: false,
            focus: true
        }, .., ..)
    */
    button: function () {
    
        var DOM = this.DOM,
            $buttons = DOM.buttons,
            elem = $buttons[0],
            strongButton = 'btn-primary',
            listeners = this._listeners = this._listeners || {},
            ags = [].slice.call(arguments);
            
        var i = 0, val, value, id, isNewButton, button;
        
        for (; i < ags.length; i ++) {
            
            val = ags[i];
            
            value = val.value;
            id = val.id || value;
            isNewButton = !listeners[id];
            button = !isNewButton ? listeners[id].elem : document.createElement('input');
            
            button.type = 'button';
            button.className = 'btn';
                    
            if (!listeners[id]) {
                listeners[id] = {};
            };
            
            if (value) {
                button.value = value;
            };
            
            if (val.width) {
                button.style.width = val.width;
            };
            
            if (val.callback) {
                listeners[id].callback = val.callback;
            };
            
            if (val.focus) {
                this._focus && this._focus.removeClass(strongButton);
                this._focus = $(button).addClass(strongButton);
                this.focus();
            };
            
            button[_expando + 'callback'] = id;
            button.disabled = !!val.disabled;
            

            if (isNewButton) {
                listeners[id].elem = button;
                elem.appendChild(button);
            };
        };
        
        $buttons[0].style.display = ags.length ? '' : 'none';
        
        return this;
    },
    
    
    /** 显示对话框 */
    visible: function () {
        //this.DOM.wrap.show();
        this.DOM.wrap.css('visibility', 'visible');
        this.DOM.outer.addClass('d-state-visible');
        
        if (this._isLock) {
            this._lockMask.show();
        };
        
        return this;
    },
    
    
    /** 隐藏对话框 */
    hidden: function () {
        //this.DOM.wrap.hide();
        this.DOM.wrap.css('visibility', 'hidden');
        this.DOM.outer.removeClass('d-state-visible');
        
        if (this._isLock) {
            this._lockMask.hide();
        };
        
        return this;
    },
    
    
    /** 关闭对话框 */
    close: function () {
    
        if (this.closed) {
            return this;
        };
    
        var DOM = this.DOM,
            $wrap = DOM.wrap,
            list = artDialog.list,
            close = this.config.close;
        
        if (close && close.call(this) === false) {
            return this;
        };
        
        
        if (artDialog.focus === this) {
            artDialog.focus = null;
        };
        
        
        if (this._follow) {
            this._follow.removeAttribute(_expando + 'follow');
        }
        
        
        if (this._elemBack) {
            this._elemBack();
        };
        
        
        
        this.time();
        this.unlock();
        this._removeEvent();
        delete list[this.config.id];

        
        if (_singleton) {
        
            $wrap.remove();
        
        // 使用单例模式
        } else {
        
            _singleton = this;
            
            DOM.title.html('');
            DOM.content.html('');
            DOM.buttons.html('');
            
            $wrap[0].className = $wrap[0].style.cssText = '';
            DOM.outer[0].className = 'd-outer';
            
            $wrap.css({
                left: 0,
                top: 0,
                position: _isFixed ? 'fixed' : 'absolute'
            });
            
            for (var i in this) {
                if (this.hasOwnProperty(i) && i !== 'DOM') {
                    delete this[i];
                };
            };
            
            this.hidden();
            
        };
        
        this.closed = true;
        return this;
    },
    
    
    /**
    * 定时关闭
    * @param    {Number}    单位毫秒, 无参数则停止计时器
    */
    time: function (time) {
    
        var that = this,
            timer = this._timer;
            
        timer && clearTimeout(timer);
        
        if (time) {
            this._timer = setTimeout(function(){
                that._click('cancel');
            }, 1000 * time);
        };
        
        
        return this;
    },
    
    /** @inner 设置焦点 */
    focus: function () {

        if (this.config.focus) {
            //setTimeout(function () {
                try {
                    var elem = this._focus && this._focus[0] || this.DOM.close[0];
                    elem && elem.focus();
                // IE对不可见元素设置焦点会报错
                } catch (e) {};
            //}, 0);
        };
        
        return this;
    },
    
    
    /** 置顶对话框 */
    zIndex: function () {
    
        var DOM = this.DOM,
            top = artDialog.focus,
            index = artDialog.defaults.zIndex ++;
        
        // 设置叠加高度
        DOM.wrap.css('zIndex', index);
        this._lockMask && this._lockMask.css('zIndex', index - 1);
        
        // 设置最高层的样式
        top && top.DOM.outer.removeClass('d-state-focus');
        artDialog.focus = this;
        DOM.outer.addClass('d-state-focus');
        
        return this;
    },
    
    
    /** 设置屏锁 */
    lock: function () {
    
        if (this._isLock) {
            return this;
        };
        
        var that = this,
            config = this.config,
            DOM = this.DOM,
            div = document.createElement('div'),
            $div = $(div),
            index = artDialog.defaults.zIndex - 1;
        
        this.zIndex();
        DOM.outer.addClass('d-state-lock');
            
        $div.css({
            zIndex: index,
            position: 'fixed',
            left: 0,
            top: 0,
            width: '100%',
            height: '100%',
            overflow: 'hidden',
            background: "rgba(0,0,0,.1)",
            filter: "progid:DXImageTransform.Microsoft.gradient(startColorstr=#19000000,endColorstr=#19000000)"
        }).addClass('d-mask');
        
        if (!_isFixed) {
            $div.css({
                position: 'absolute',
                width: $(window).width() + 'px',
                height: $(document).height() + 'px'
            });
        };
        
            
        $div.bind('dblclick', function () {
            that._click('cancel');
        });
        
        document.body.appendChild(div);
        
        this._lockMask = $div;
        this._isLock = true;
        
        return this;
    },
    
    
    /** 解开屏锁 */
    unlock: function () {

        if (!this._isLock) {
            return this;
        };
        
        this._lockMask.unbind();
        this._lockMask.hide();
        this._lockMask.remove();
        
        this.DOM.outer.removeClass('d-state-lock');
        this._isLock = false;

        return this;
    },
    
    
    // 获取元素
    _getDom: function () {
    
        var body = document.body;
        
        if (!body) {
            throw new Error('artDialog: "documents.body" not ready');
        };
        
        var wrap = document.createElement('div');
            
        wrap.style.cssText = 'position:absolute;left:0;top:0';
        wrap.innerHTML = artDialog._templates;
        body.insertBefore(wrap, body.firstChild);
        
        var name,
            i = 0,
            dom = {},
            els = wrap.getElementsByTagName('*'),
            elsLen = els.length;
            
        for (; i < elsLen; i ++) {
            name = els[i].className.split('d-')[1];
            if (name) {
                dom[name] = $(els[i]);
            };
        };
        
        dom.window = $(window);
        dom.document = $(document);
        dom.wrap = $(wrap);
        
        return dom;
    },
    
    
    // 按钮回调函数触发
    _click: function (id) {
    
        var fn = this._listeners[id] && this._listeners[id].callback;
            
        return typeof fn !== 'function' || fn.call(this) !== false ?
            this.close() : this;
    },
    
    
    // 重置位置
    _reset: function () {
        var elem = this.config.follow || this._follow;
        elem ? this.follow(elem) : this.position();
    },
    
    
    // 事件代理
    _addEvent: function () {
    
        var that = this,
            DOM = this.DOM;
        
        
        // 监听点击
        DOM.wrap
        .bind('click', function (event) {
        
            var target = event.target, callbackID;
            
            // IE BUG
            if (target.disabled) {
                return false;
            };
            
            if (target === DOM.close[0]) {
                that._click('cancel');
                return false;
            } else {
                callbackID = target[_expando + 'callback'];
                callbackID && that._click(callbackID);
            };
            
        })
        .bind('mousedown', function () {
            that.zIndex();
        });
        
    },
    
    
    // 卸载事件代理
    _removeEvent: function () {
        this.DOM.wrap.unbind();
    }
    
};

artDialog.fn.constructor.prototype = artDialog.fn;



$.fn.dialog = $.fn.artDialog = function () {
    var config = arguments;
    this[this.live ? 'live' : 'bind']('click', function () {
        artDialog.apply(this, config);
        return false;
    });
    return this;
};



/** 最顶层的对话框API */
artDialog.focus = null;



/**
* 根据 ID 获取某对话框 API
* @param    {String}    对话框 ID
* @return   {Object}    对话框 API (实例)
*/
artDialog.get = function (id) {
    return id === undefined
    ? artDialog.list
    : artDialog.list[id];
};

artDialog.list = {};



// 全局快捷键
$(document).bind('keydown', function (event) {
    var target = event.target,
        nodeName = target.nodeName,
        rinput = /^input|textarea$/i,
        api = artDialog.focus,
        keyCode = event.keyCode;

    if (!api || !api.config.esc || rinput.test(nodeName) && target.type !== 'button') {
        return;
    };
    
    // ESC
    keyCode === 27 && api._click('cancel');
});



// 浏览器窗口改变后重置对话框位置
$(window).bind('resize', function () {
    var dialogs = artDialog.list;
    for (var id in dialogs) {
        dialogs[id]._reset();
    };
});



// XHTML 模板
// 使用 uglifyjs 压缩能够预先处理"+"号合并字符串
// @see http://marijnhaverbeke.nl/uglifyjs
artDialog._templates = 
'<div class="d-outer">'
+   '<table class="d-border">'
+       '<tbody>'
+           '<tr>'
+               '<td class="d-nw"></td>'
+               '<td class="d-n"></td>'
+               '<td class="d-ne"></td>'
+           '</tr>'
+           '<tr>'
+               '<td class="d-w"></td>'
+               '<td class="d-c">'
+                   '<div class="d-inner">'
+                   '<table class="d-dialog">'
+                       '<tbody>'
+                           '<tr>'
+                               '<td class="d-header">'
+                                   '<div class="d-titleBar">'
+                                       '<div class="d-title"></div>'
+                                       '<a class="d-close" href="javascript:/*artDialog*/;">'
+                                           '\xd7'
+                                       '</a>'
+                                   '</div>'
+                               '</td>'
+                           '</tr>'
+                           '<tr>'
+                               '<td class="d-main">'
+                                   '<div class="d-content"></div>'
+                               '</td>'
+                           '</tr>'
+                           '<tr>'
+                               '<td class="d-footer">'
+                                   '<div class="d-buttons"></div>'
+                               '</td>'
+                           '</tr>'
+                       '</tbody>'
+                   '</table>'
+                   '</div>'
+               '</td>'
+               '<td class="d-e"></td>'
+           '</tr>'
+           '<tr>'
+               '<td class="d-sw"></td>'
+               '<td class="d-s"></td>'
+               '<td class="d-se"></td>'
+           '</tr>'
+       '</tbody>'
+   '</table>'
+'</div>';



/**
 * 默认配置
 */
artDialog.defaults = {

    // 消息内容
    content: '<div class="d-loading"><span>loading..</span></div>',
    
    // 标题
    // title: 'message',
    
    // 自定义按钮
    button: null,
    
    // 确定按钮回调函数
    ok: null,
    
    // 取消按钮回调函数
    cancel: null,
    
    // 对话框初始化后执行的函数
    init: null,
    
    // 对话框关闭前执行的函数
    close: null,
    
    // 确定按钮文本
    okVal: '\u786E\u5B9A',
    
    // 取消按钮文本
    cancelVal: '\u53D6\u6D88',
    
    // 内容宽度
    width: 'auto',
    
    // 内容高度
    height: 'auto',
    
    // 内容与边界填充距离
    padding: '20px 25px',
    
    // 皮肤名(多皮肤共存预留接口)
    skin: null,
    
    // 自动关闭时间
    time: null,
    
    // 是否支持Esc键关闭
    esc: true,
    
    // 是否支持对话框按钮自动聚焦
    focus: true,
    
    // 初始化后是否显示对话框
    visible: true,
    
    // 让对话框跟随某元素
    follow: null,
    
    // 是否锁屏
    lock: false,
    
    // 是否固定定位
    fixed: false,
    
    // 对话框叠加高度值(重要：此值不能超过浏览器最大限制)
    zIndex: 5000
    
};

this.artDialog = $.dialog = $.artDialog = artDialog;
}(this.art || this.jQuery, this));




/* 更新记录

1.  follow 不再支持 String 类型
2.  button 参数只支持 Array 类型
3.  button name 成员改成 value
4.  button 增加 id 成员
5.  okVal 参数更名为 okVal, 默认值由 '确定' 改为 'ok'
6.  cancelVal 参数更名为 cancelVal, 默认值由 '取消' 改为 'cancel'
6.  close 参数更名为 close
7.  init 参数更名为 init
8.  title 参数默认值由 '消息' 改为 'message'
9.  time 参数与方法参数单位由秒改为毫秒
10. hide 参数方法更名为 hidden
11. 内部为皮肤增加动态样式 d-state-visible 类
12. 给遮罩增添样式 d-mask 类
13. background 参数被取消, 由 CSS 文件定义
14. opacity 参数被取消, 由 CSS 文件定义
15. 取消拖动特性，改由插件支持
16. 取消 left 与 top 参数
17. 取消对 ie6 提供 fixed 支持，自动转换为 absolute
18. 取消对 ie6 提供 alpha png 支持
19. 取消对 ie6 提供 select 标签遮盖支持
20. 增加 focus 参数
21. 取消 position 方法
22. 取消对 <script type="text/dialog"></script> 的支持
23. 取消对 iframe 的支持
24. title 方法不支持空参数
25. content 方法不支持空参数
26. button 方法的参数不支持数组类型
27. 判断 DOCTYPE, 对 xhtml1.0 以下的页面报告错误
28. 修复 IE8 动态等新内容时没有撑开对话框高度，特意为 ie8 取消 .d-content { display:inline-block }
29. show 参数与方法更名为 visible
30. 修正重复调用 close 方法出现的错误
31. 修正设定了follow后再使用content()方法导致其居中的问题
32. 修复居中可能导致左边框显示不出的问题

*/



/*!
* artDialog 5 plugins
* Date: 2012-03-16
* https://github.com/aui/artDialog
* (c) 2009-2012 TangBin, http://www.planeArt.cn
*
* This is licensed under the GNU LGPL, version 2.1 or later.
* For details, see: http://creativecommons.org/licenses/LGPL/2.1/
*/

;(function ($) {
var _isIE6 = window.VBArray && !window.XMLHttpRequest;
/** 获取 artDialog 可跨级调用的最高层的 window 对象 */
var _top = artDialog.top = function () {
    var top = window,
    test = function (name) {
        try {
            var doc = window[name].document;    // 跨域|无权限
            doc.getElementsByTagName;           // chrome 本地安全限制
        } catch (e) {
            return false;
        };
        
        return window[name].artDialog
        // 框架集无法显示第三方元素
        && doc.getElementsByTagName('frameset').length === 0;
    };
    
    if (test('top')) {
        top = window.top;
    } else if (test('parent')) {
        top = window.parent;
    };
    
    return top;
}();
artDialog.parent = _top; // 兼容v4.1之前版本，未来版本将删除此
/**
 * 弹窗 (iframe)
 * @param   {String}    地址
 * @param   {Object}    配置参数. 这里传入的回调函数接收的第1个参数为iframe内部window对象
 * @param   {Boolean}   是否允许缓存. 默认true
 */
$.open = $.dialog.open = function (url, options, cache) {
    options = options || {};
    
    var api, DOM,
        $content, $main, iframe, $iframe, $idoc, iwin, ibody,
        top = artDialog.top,
        initCss = 'position:absolute;left:-9999em;top:-9999em;border:none 0;background:transparent',
        loadCss = 'width:100%;height:100%;border:none 0';
        
    if (cache === false) {
        var ts = + new Date,
            ret = url.replace(/([?&])_=[^&]*/, "$1_=" + ts );
        url = ret + ((ret === url) ? (/\?/.test(url) ? "&" : "?") + "_=" + ts : "");
    };
        
    var load = function () {
        var iWidth, iHeight,
            loading = DOM.content.find('.d-loading'),
            aConfig = api.config;
            
        $content.addClass('d-state-full');
        
        loading && loading.hide();
        
        try {
            iwin = iframe.contentWindow;
            $idoc = $(iwin.document);
            ibody = iwin.document.body;
        } catch (e) {// 跨域
            iframe.style.cssText = loadCss;
            
            aConfig.follow
            ? api.follow(aConfig.follow)
            : api.position(aConfig.left, aConfig.top);
            
            options.init && options.init.call(api, iwin, top);
            options.init = null;
            return;
        };
        
        // 获取iframe内部尺寸
        iWidth = aConfig.width === 'auto'
        ? $idoc.width() + (_isIE6 ? 0 : parseInt($(ibody).css('marginLeft')))
        : aConfig.width;
        
        iHeight = aConfig.height === 'auto'
        ? $idoc.height()
        : aConfig.height;
        
        // 适应iframe尺寸
        setTimeout(function () {
            iframe.style.cssText = loadCss;
        }, 0);// setTimeout: 防止IE6~7对话框样式渲染异常
        api.size(iWidth, iHeight);
        
        // 调整对话框位置
        aConfig.follow
        ? api.follow(aConfig.follow)
        : api.position(aConfig.left, aConfig.top);
        
        options.init && options.init.call(api, iwin, top);
        options.init = null;
    };
        
    var config = {
        init: function () {
            api = this;
            DOM = api.DOM;
            $main = DOM.main;
            $content = DOM.content;
            
            iframe = api.iframe = top.document.createElement('iframe');
            iframe.src = url;
            iframe.name = 'Open' + api.config.id;
            iframe.id = 'Open' + api.config.id;
            iframe.style.cssText = initCss;
            iframe.setAttribute('frameborder', 0, 0);
            iframe.setAttribute('allowTransparency', true);
            
            $iframe = $(iframe);
            api.DOM.content.append(iframe);
            iwin = iframe.contentWindow;
            
            try {
                iwin.name = iframe.name;
                artDialog.data(iframe.name + _open, api);
                artDialog.data(iframe.name + _opener, window);
            } catch (e) {};
            
            $iframe.bind('load', load);
        },
        close: function () {
            $iframe.css('display', 'none').unbind('load', load);
            
            if (options.close && options.close.call(this, iframe.contentWindow, top) === false) {
                return false;
            };
            $content.removeClass('aui_state_full');
            
            // 重要！需要重置iframe地址，否则下次出现的对话框在IE6、7无法聚焦input
            // IE删除iframe后，iframe仍然会留在内存中出现上述问题，置换src是最容易解决的方法
            $iframe[0].src = 'about:blank';
            $iframe.remove();
            
            try {
                artDialog.removeData(iframe.name + _open);
                artDialog.removeData(iframe.name + _opener);
            } catch (e) {};
        }
    };
    
    // 回调函数第一个参数指向iframe内部window对象
    if (typeof options.ok === 'function') config.ok = function () {
        return options.ok.call(api, iframe.contentWindow, top);
    };
    if (typeof options.cancel === 'function') config.cancel = function () {
        return options.cancel.call(api, iframe.contentWindow, top);
    };
    
    delete options.content;

    for (var i in options) {
        if (config[i] === undefined) config[i] = options[i];
    };
    
    return $.dialog(config);
};

/**
 * Ajax填充内容
 * @param   {String}            地址
 * @param   {Object}            配置参数
 * @param   {Boolean}           是否允许缓存. 默认true
 */
$.load = $.dialog.load = function(url, options, cache){
    cache = cache || false;
    var opt = options || {};
        
    var config = {
        init: function(here){
            var api = this,
                aConfig = api.config;
            
            $.ajax({
                url: url,
                success: function (content) {
                    if( content && content.isSucess == false ){
                        api._listeners.ok = null;
                        api.DOM.content.html(content.msg);
                        return
                    }
                    api.content(content);
                    opt.init && opt.init.call(api, here);       
                },
                cache: cache
            });
            
        }
    };
    
    delete options.content;
    
    for (var i in opt) {
        if (config[i] === undefined) config[i] = opt[i];
    };
    
    return $.dialog(config);
};

/**
 * 警告
 * @param   {String, HTMLElement}   消息内容
 * @param   {Function}              (可选) 回调函数
 */
$.alert = $.dialog.alert = function (content, callback) {
    return $.dialog({
        id: 'Alert',
        init: function(){
            var api = this;
            api.DOM.dialog.addClass("d-dialog-alert");
        },
        width: 300,
        height: 80,
        padding: "10px 10px 10px 80px",
        fixed: true,
        lock: true,
        content: content,
        ok: true,
        close: function() {
            var api = this;
            callback && callback();
            api.DOM.dialog.removeClass("d-dialog-alert");
        }
    });
};


/**
 * 确认选择
 * @param   {String, HTMLElement}   消息内容
 * @param   {Function}              确定按钮回调函数
 * @param   {Function}              取消按钮回调函数
 */
$.confirm = $.dialog.confirm = function (content, ok, cancel) {
    return $.dialog({
        id: 'Confirm',
        fixed: true,
        lock: true,
        content: content,
        width: 300,
        height: 80,
        padding: "10px 10px 10px 80px",
        init: function(){
            var api = this;
            api.DOM.dialog.addClass("d-dialog-confirm");
        },
        ok: function (here) {
            return ok.call(this, here);
        },
        cancel: function (here) {
            return cancel && cancel.call(this, here);
        },
        close: function(){
            var api = this;
            api.DOM.dialog.removeClass("d-dialog-confirm");
        }
    });
};


/**
 * 输入框
 * @param   {String, HTMLElement}   消息内容
 * @param   {Function}              确定按钮回调函数。函数第一个参数接收用户录入的数据
 * @param   {String}                输入框默认文本
 */
$.prompt = $.dialog.prompt = function (content, ok, defaultValue) {
    defaultValue = defaultValue || '';
    var input;
    
    return $.dialog({
        id: 'Prompt',
        fixed: true,
        lock: true,
        content: [
            '<div style="margin-bottom:5px;font-size:12px">',
                content,
            '</div>',
            '<div>',
                '<input type="text" class="d-input-text" value="',
                    defaultValue,
                '" style="width:18em;" />',
            '</div>'
            ].join(''),
        init: function () {
            input = this.DOM.content.find('.d-input-text')[0];
            input.select();
            input.focus();
			this.DOM.dialog.addClass("d-dialog-prompt");
        },
        ok: function () {
            return ok && ok.call(this, input.value);
        },
        cancel: function () {
            var api = this;
            api.DOM.dialog.removeClass("d-dialog-prompt");
        }
    });
};


$.tips = $.dialog.tips = function(content, time){
    return $.dialog({
        id: 'Tips',
        title: false,
        cancel: false,
        fixed: true,
        lock: false
    })
    .content('<div style="padding: 0 1em;">' + content + '</div>')
    .time(time || 1.5);

};

/** 抖动效果 */
$.dialog.prototype.shake = (function () {

    var fx = function (ontween, onend, duration) {
        var startTime = + new Date;
        var timer = setInterval(function () {
            var runTime = + new Date - startTime;
            var pre = runTime / duration;
                
            if (pre >= 1) {
                clearInterval(timer);
                onend(pre);
            } else {
                ontween(pre);
            };
        }, 13);
    };
    
    var animate = function (elem, distance, duration) {
        var quantity = arguments[3];

        if (quantity === undefined) {
            quantity = 6;
            duration = duration / quantity;
        };
        
        var style = elem.style;
        var from = parseInt(style.marginLeft) || 0;
        
        fx(function (pre) {
            elem.style.marginLeft = from + (distance - from) * pre + 'px';
        }, function () {
            if (quantity !== 0) {
                animate(
                    elem,
                    quantity === 1 ? 0 : (distance / quantity - distance) * 1.3,
                    duration,
                    -- quantity
                );
            };
        }, duration);
    };
    
    return function () {
        animate(this.DOM.wrap[0], 40, 600);
        return this;
    };
})(this.jQuery);


// 拖拽支持
var DragEvent = function () {
    var that = this,
        proxy = function (name) {
            var fn = that[name];
            that[name] = function () {
                return fn.apply(that, arguments);
            };
        };
        
    proxy('start');
    proxy('over');
    proxy('end');
};


DragEvent.prototype = {

    // 开始拖拽
    // onstart: function () {},
    start: function (event) {
        $(document)
        .bind('mousemove', this.over)
        .bind('mouseup', this.end);
            
        this._sClientX = event.clientX;
        this._sClientY = event.clientY;
        this.onstart(event.clientX, event.clientY);

        return false;
    },
    
    // 正在拖拽
    // onover: function () {},
    over: function (event) {        
        this._mClientX = event.clientX;
        this._mClientY = event.clientY;
        this.onover(
            event.clientX - this._sClientX,
            event.clientY - this._sClientY
        );
        
        return false;
    },
    
    // 结束拖拽
    // onend: function () {},
    end: function (event) {
        $(document)
        .unbind('mousemove', this.over)
        .unbind('mouseup', this.end);
        
        this.onend(event.clientX, event.clientY);
        return false;
    }
    
};

var $window = $(window),
    $document = $(document),
    html = document.documentElement,
    isIE6 = !('minWidth' in html.style),
    isLosecapture = !isIE6 && 'onlosecapture' in html,
    isSetCapture = 'setCapture' in html,
    dragstart = function () {
        return false
    };
    
var dragInit = function (event) {
    
    var dragEvent = new DragEvent,
        api = artDialog.focus,
        DOM = api.DOM,
        $wrap = DOM.wrap,
        $title = DOM.title,
        $main = DOM.main,
        wrap = $wrap[0],
        title = $title[0],
        main = $main[0],
        wrapStyle = wrap.style,
        mainStyle = main.style;
        
        
    var isResize = event.target === DOM.se[0] ? true : false;
    var isFixed = wrap.style.position === 'fixed',
        minX = isFixed ? 0 : $document.scrollLeft(),
        minY = isFixed ? 0 : $document.scrollTop(),
        maxX = $window.width() - wrap.offsetWidth + minX,
        maxY = $window.height() - wrap.offsetHeight + minY;
    
    
    var startWidth, startHeight, startLeft, startTop;
    
    
    // 对话框准备拖动
    dragEvent.onstart = function (x, y) {
    
        if (isResize) {
            startWidth = main.offsetWidth;
            startHeight = main.offsetHeight;
        } else {
            startLeft = wrap.offsetLeft;
            startTop = wrap.offsetTop;
        };
        
        $document.bind('dblclick', dragEvent.end)
        .bind('dragstart', dragstart);
            
        if (isLosecapture) {
            $title.bind('losecapture', dragEvent.end)
        } else {
            $window.bind('blur', dragEvent.end)
        };
            
        isSetCapture && title.setCapture();
        
        $wrap.addClass('d-state-drag');
        api.focus();
    };
    
    // 对话框拖动进行中
    dragEvent.onover = function (x, y) {
    
        if (isResize) {
            var width = x + startWidth,
                height = y + startHeight;
            
            wrapStyle.width = 'auto';
            mainStyle.width = Math.max(0, width) + 'px';
            wrapStyle.width = wrap.offsetWidth + 'px';
            
            mainStyle.height = Math.max(0, height) + 'px';
            
        } else {
            var left = Math.max(minX, Math.min(maxX, x + startLeft)),
                top = Math.max(minY, Math.min(maxY, y + startTop));

            wrapStyle.left = left  + 'px';
            wrapStyle.top = top + 'px';
        };
        
        
    };
    
    // 对话框拖动结束
    dragEvent.onend = function (x, y) {
    
        $document.unbind('dblclick', dragEvent.end)
        .unbind('dragstart', dragstart);
        
        if (isLosecapture) {
            $title.unbind('losecapture', dragEvent.end);
        } else {
            $window.unbind('blur', dragEvent.end)
        };
        
        isSetCapture && title.releaseCapture();
        
        $wrap.removeClass('d-state-drag');
    };
    
    
    dragEvent.start(event);
    
};


// 代理 mousedown 事件触发对话框拖动
$(document).bind('mousedown', function (event) {
    var api = artDialog.focus;
    if (!api) return;

    var target = event.target,
        config = api.config,
        DOM = api.DOM;
    
    if (config.drag !== false && target === DOM.title[0]
    || config.resize !== false && target === DOM.se[0]) {
        dragInit(event);
        
        // 防止firefox与chrome滚屏
        return false;
    };
});


}(this.art || this.jQuery));

