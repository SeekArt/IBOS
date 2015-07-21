/**
 * 工作流表单设计器，编辑器插件扩展
 */

(function(){
    // 扩展语言包
    // @Todo: 编辑器默认的语言包模板机制并不适用于当前控件复杂度，而且效率比较低
    // 考虑是否不使用语言包或使用其他机制
    var timerCount = setInterval(function(){
        if(UE.I18N['zh-cn']) {
            UE.utils.extend(UE.I18N['zh-cn'], {
                "fc": {
                    "noNameTip": "请输入控件名称",
                    "noTextTip": "请输入文本",
                    "noOptionTip": "请输入选项",
                    "addError": "控件添加失败，请重试或联系管理员解决",
                },
                "iclabel": {
                    "static": {
                        "lang_control_text": "标签文本",
                        "lang_control_style": "控件样式",
                        "lang_control_width": "宽",
                        "lang_control_fontsize": "文字大小",
                        "lang_control_fontstyle": "文字样式",
                        "control_label_align": {
                            options: ["左对齐", "居中", "右对齐"]
                        }
                    }
                },
                "ictext": {
                    "static": {
                        "lang_control_title": "控件名称",
                        "lang_control_style": "控件样式",
                        "lang_control_value": "默认值",
                        "lang_control_width": "宽",
                        "lang_control_visibility": "可见性",
                        "lang_control_hide": "隐藏"
                    }
                },
                "ictextarea": {
                    "static": {
                        "lang_control_title": "控件名称",
                        "lang_control_style": "控件样式",
                        "lang_control_value": "默认值",
                        "lang_control_width": "宽",
                        "lang_control_row": "行",
                        "lang_enable_editor": "富文本形式(启用文本编辑器)"
                    }
                },
                "icselect": {
                    "static": {
                        "lang_control_title": "控件名称",
                        "lang_control_style": "控件样式",
                        "lang_control_value": "默认值",
                        "lang_control_width": "宽",
                        "lang_control_row": "行",
                        "lang_add_options": "批量增加选项(每行一个)"
                    }
                },
                "icradio": {
                    "static": {
                        "lang_control_title": "控件名称",
                        "lang_control_value": "默认值",
                        "lang_add_options": "批量增加选项(每行一个)"
                    }
                },
                "iccheckbox": {
                    "static": {
                        "lang_control_title": "控件名称",
                        "lang_control_checked": "默认选中"
                    }
                },
                "icuser": {
                    "static": {
                        "lang_control_title": "控件名称",
                        "lang_control_style": "控件样式",
                        "lang_control_width": "宽",
                        "lang_user_type": "选择类型",
                        "control_user_type": {
                            options: ["选择人员", "选择部门", "选择岗位"],
                        },
                        "lang_single_select": "单选"
                    }
                },
                "icdate": {
                    "static": {
                        "lang_control_title": "控件名称",
                        "lang_control_style": "控件样式",
                        "lang_control_width": "宽",
                        "lang_date_format": "日历显示格式",
                        "control_date_format": {
                            options: ["年-月-日 时:分:秒", "年-月-日 时:分", "年-月-日 时", "年-月-日", "年-月", "年"]
                        }
                    }
                },
                "icauto": {
                    "static": {
                        "lang_control_title": "控件名称",
                        "lang_control_style": "控件样式",
                        "lang_control_width": "宽",
                        "lang_control_visibility": "可见性",
                        "lang_control_hide": "隐藏",
                        "lang_auto_field": "类型",
                        "control_auto_field": {
                            options: [
                                "当前日期，形如 1999-01-01",
                                "当前日期，形如 2009年1月1日",
                                "当前日期，形如 2009年",
                                "当前年份，形如 2009",
                                "当前日期，形如 2009年1月",
                                "当前日期，形如 1月1日",
                                "当前时间",
                                "当前日期+时间",
                                "当前星期中的第几天，形如 星期一",
                                "当前用户id",
                                "当前用户姓名",
                                "当前用户部门(长名称)",
                                "当前用户部门(短名称)",
                                "当前用户岗位",
                                "当前用户辅助岗位",
                                "当前用户姓名+日期",
                                "当前用户姓名+日期+时间",
                                "表单名称",
                                "工作名称/文号",
                                "流程开始日期",
                                "流程开始日期+时间",
                                "流水号",
                                "文号计数器",
                                "经办人ip地址",
                                "部门主管(本部门)",
                                "部门主管(上级部门)",
                                "部门主管(一级部门)",
                                "来自sql查询语句",
                                "部门列表",
                                "人员列表",
                                "角色列表",
                                "流程设置所有经办人列表",
                                "本步骤设置经办人列表",
                                "部门主管(本部门)",
                                "部门主管(上级部门)",
                                "部门主管(一级部门)",
                                "来自sql查询语句的列表"
                            ]
                        },
                        "auto_field_input": {
                            label: "单行输入框"
                        },
                        "auto_field_select": {
                            label: "下拉菜单"
                        }
                    }
                },
                "iccalc": {
                    "static": {
                        "lang_control_title": "控件名称",
                        "lang_control_style": "控件样式",
                        "lang_control_width": "宽",
                        "lang_calc_prec": "计算精度",
                        "lang_calc_prec_desc": "默认保留小数点后4位",
                        "lang_calc_expression": "计算公式"
                    }
                },
                "iclistview": {
                    "static": {
                        "lang_control_title": "控件名称"
                    },
                    "noColumnTip": "请输入至少一列数据",
                    "text": "单行输入框",
                    "textarea": "多行输入框",
                    "select": "下拉菜单",
                    "radio": "单选框",
                    "checkbox": "复选框",
                    "date": "日期",
                    "calc": "计算公式"
                },
                "icimgupload": {
                    "static": {
                        "lang_control_title": "控件名称",
                        "lang_control_style": "控件样式",
                        "lang_control_width": "宽",
                        "lang_control_height": "高"
                    }
                },
                "icprogressbar": {
                    "static": {
                        "lang_control_title": "控件名称",
                        "lang_control_style": "控件样式",
                        "lang_control_width": "宽",
                        "lang_control_step": "跨度",
                        "lang_success_tip": "表示完成、成功",
                        "lang_warning_tip": "表示进行中，比较重要的事项",
                        "lang_primary_tip": "表示信息、进度"
                    }
                },
                "icsign": {
                    "static": {
                        "lang_title": "控件名称",
                        "lang_type": "控件类型",
                        "lang_stamp": "盖章",
                        "lang_write": "手写",
                        "lang_color": "手写颜色",
                        "lang_lock_tag": "验证锁定标签(每行一个)"
                    },
                },
                "icqrcode": {
                    "static": {
                        "lang_text": "内容",
                        "lang_tel": "电话",
                        "lang_sms": "短信",
                        "lang_mecard": "名片",
                        "lang_wifi": "WIFI",
                        "lang_tip": "推荐140字以内，手机摄像头更容易辨识。",
                        "lang_qrcode_size": "二维码尺寸",
                        "lang_large": "大",
                        "lang_medium": "中",
                        "lang_small": "小",
                        "lang_create_preview": "输入内容后将自动为您生成二维码预览",
                        "qrcode_value": {
                            placeholder: "输入网址、邮箱、文本等您想生成的内容",
                        }
                    },
                    "noValueTip": "请输入要生成二维码的内容"
                },
                "jsext": {
                    "static": {
                        "lang_code_edit": "编辑Javascript代码(不需要带<script>标签)"
                    }
                },
                "cssext": {
                    "static": {
                        "lang_code_edit": "编辑css样式(不需要带<style>标签)"
                    }
                },
                "macro": {}
            });
            clearInterval(timerCount);
        }
    }, 100);

    UE.plugins['formcontrols'] = function(){
        var me = this;

        var ctrls = {
            'iclabel': { title: "标签控件" },
            'ictext': { title: "单行输入框" },
            'ictextarea': { title: "多行输入框" },
            'icselect': { title: "下拉选框" },
            'icradio': { title: "单选框" },
            'iccheckbox': { title: "复选框" },
            'icuser': { title: "部门人员控件" },
            'icdate': { title: "日历控件" },
            'icauto': { title: "宏控件" },
            'iccalc': { title: "计算控件" },
            'iclistview': { title: "列表控件" },
            'icimgupload': { title: "图片上传控件" },
            'icprogressbar': { title: "进度条控件" },
            'icsign': { title: "签章控件" },
            'icqrcode': { title: "二维码控件" },
        };

        for(var id in ctrls) {
            if(!ctrls.hasOwnProperty(id)) {
                return false;
            }

            UE.commands[id] = {
                // 传入elem参数时，为编辑操作，否则为新建操作
                execCommand: function(command, elem){
                    var id = command,
                        title = ctrls[id].title,
                        dialogId = id + "Dialog",
                        dialog = me.ui._dialogs[dialogId];

                    // 关闭其他对话框, 
                    for(var i in me.ui._dialogs) {
                        // 当前即将打开的对话框不用处理
                        if(i !== dialogId) {
                            // 对话框须已在页面上初始化，否则会出错
                            if(me.ui._dialogs[i] && me.ui._dialogs[i].close && document.getElementById(me.ui._dialogs[i].id)) {
                                // 关闭显示状态的窗口
                                (!me.ui._dialogs[i].isHidden()) &&
                                me.ui._dialogs[i].close();
                            }
                        }
                    }
                    
                    // editing 属性指向编辑中的节点
                    // 由于关闭其他对话框时，会将editing属性删除，所以editing指向需要放在关闭操作后
                    UE.plugins['formcontrols'].editing = elem;

                    // 未初始化对话框时，初始化
                    if(!dialog) {
                        dialog = new UE.ui.Dialog({
                            // @Debug: 随机数用于开发阶段，实际使用是不需要的
                            iframeUrl: me.ui.mapUrl('~/dialogs/' + id + '/' + id + '.html?=' + Math.random()),
                            editor: me,
                            className: 'edui-for-' + id,
                            title: title,
                            buttons: [{
                                    className: 'edui-okbutton',
                                    label: '确认',
                                    onclick: function() {
                                        dialog.close(true);
                                    }
                                }, {
                                    className: 'edui-cancelbutton',
                                    label: '取消',
                                    onclick: function() {
                                        dialog.close(false);
                                    }
                                }, {
                                    className: "edui-deletebutton",
                                    label: "删除",
                                    onclick: function(){
                                        if(window.confirm("确定删除该控件吗？")) {
                                            UE.plugins['formcontrols'].editing &&
                                            UE.dom.domUtils.remove(UE.plugins['formcontrols'].editing, false);
                                            dialog.close(false);
                                        }
                                    }
                                }]
                        });
                        dialog.render();
                        // 存入缓存， 避免重复初始化
                        me.ui._dialogs[dialogId] = dialog;

                        // 关闭窗口删除editing指向, 结束编辑状态
                        dialog.addListener("close", function(){
                            delete UE.plugins['formcontrols'].editing;
                        })
                        // 去掉模态层
                        dialog.removeListener("show", dialog.__allListeners["show"][0]);
                    }
                    // 编辑状态时显示“删除”按钮，“新建”时不显示
                    dialog.buttons[2].getStateDom().style.display = elem ? "" : "none";

                    dialog.open();
                    // 隐藏模态层
                    // dialog.modalMask.hide();
                }
            };
        }

        /* 绑定触发操作菜单的事件 */
        me.addListener('click', function(t, evt) {
            evt = evt || window.event;
            var elem = evt.target || evt.srcElement;
            // 触发目标必须为ic节点
            if (elem.nodeName.toLowerCase() !== "ic") {
                elem = UE.dom.domUtils.findParentByTagName(elem, "ic");
                if (!elem) {
                    return false;
                }
            }
            // 编辑时，将目标节点作为参数传入
            me.execCommand('ic' + elem.getAttribute("data-type"), elem);
            // 这个位置可以阻止lable的点击事件
            if(evt.preventDefault){
                evt.preventDefault();
            } else {
                evt.returnValue = false
            };
        });

        me.ready(function(){
            // 禁止从视图上编辑控件
            // @Todo: radio跟checkbox目前无法阻止，需要另找方法
            UE.dom.domUtils.on(me.document.body, "mousedown", function(evt){
                var elem = evt.target || evt.srcElement;
                // 阻止ic标签下的控件默认事件
                if(/label|input|select|textarea/.test(elem.nodeName.toLowerCase())) {
                    if(UE.dom.domUtils.findParentByTagName(elem, "ic")){
                        if(evt.preventDefault){
                            evt.preventDefault();
                        } else {
                            evt.returnValue = false
                        };
                    }
                }
            })
        })
    }

    var formToolbarPlugins = {
        'jsext': { title: "js脚本扩展" },
        'cssext': { title: "css样式扩展" },
        'macro': { title: "宏标记"}
    }

    for(var pg in formToolbarPlugins) {
        if(formToolbarPlugins.hasOwnProperty(pg)){
            UE.plugins[pg] = (function(pg){
                return function(){
                    var me = this;
                    me.commands[pg] = {
                        execCommand: function(command){
                            var id = command,
                                title = formToolbarPlugins[id].title,
                                dialogId = id + "Dialog",
                                dialog = me.ui._dialogs[dialogId];

                            if(!dialog){
                                dialog = new UE.ui.Dialog({
                                    // @Debug: 随机数用于开发阶段，实际使用是不需要的
                                    iframeUrl: me.ui.mapUrl('~/dialogs/' + id + '/' + id + '.html?=' + Math.random()),
                                    editor: me,
                                    className: 'edui-for-' + id,
                                    title: title,
                                    buttons: [{
                                            className: 'edui-okbutton',
                                            label: '确认',
                                            onclick: function() {
                                                dialog.close(true);
                                            }
                                        }, {
                                            className: 'edui-cancelbutton',
                                            label: '取消',
                                            onclick: function() {
                                                dialog.close(false);
                                            }
                                        }]
                                });
                                dialog.render();
                                // 存入缓存， 避免重复初始化
                                me.ui._dialogs[dialogId] = dialog;
                            }
                            dialog.open();
                        }
                    }
                }
            })(pg)
        }
    }

})();