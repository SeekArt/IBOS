(function(root, factory) {
    if (typeof define === 'function' && define.amd) {
        define(function() {
            root.Vote = factory(root);
        });
    } else if (typeof exports !== 'undefined') {
        factory(root);
    } else {
        root.Vote = factory(root);
    }
})(this, function() {
    var VoteList = {
        voteid: 0
    };

    /**
     * 添加题目
     * [Vote description]
     */
    var Vote = function() {
        this.$list = $("#vote_list");
        this.init();
    };
    Vote.prototype.init = function() {
        this.Vote = new Ibos.OrderList(this.$list, "vote_tpl");

        this.bind();
    };
    Vote.prototype.bind = function() {
        var _this = this;
        this.$list
            .on("click", ".vote-close", function() {
                _this.Vote.removeItem($(this).attr("data-id"));
            })
            .on("update", function(evt, data) {
                var type = data.checked ? 'image' : 'text';
                VoteList[data.id].type = type;
                VoteList[data.id].itemid = 0;
                VoteList[data.id].VoteProject.clear();
                for (var i = 0; i < 3; i++) {
                    VoteList[data.id].add();
                }
            })
            .on("change", "[data-toggle='switch']", function() {
                var $this = $(this);
                _this.changeType($this);
            });

        this.$list.on("list.add", function(evt, res) {
            $(_this).trigger("list.add", res.data);
        });
    };
    Vote.prototype.changeType = function($this) {
        var id = $this.attr("data-id"),
            checked = $this.prop("checked");
        $(".vote-item-" + id).val(checked ? "2" : "1");
        this.$list.trigger("update", {
            checked: checked,
            id: id
        });
    };
    Vote.prototype.add = function(data) {
        data = $.extend({
            type: "text",
            voteid: VoteList.voteid,
            subject: "",
            maxselectnum: ""
        }, data);

        this.Vote.addItem(data);
        VoteList.voteid++;
        $('[data-toggle="switch"]').iSwitch();
    };

    /**
     * 添加项目
     * [VoteProject description]
     * @param {[type]} data [description]
     */
    var VoteProject = function(data) {
        this.id = data.id;
        this.type = data.type;
        this.$list = $("#vote_project_" + data.id);
        this.voteid = VoteList.voteid;
        this.itemid = 0;
        this.init();
    };
    VoteProject.prototype.init = function() {
        this.VoteProject = new Ibos.OrderList(this.$list, "vote_project_tpl");

        this.bind();
        return this;
    };
    VoteProject.prototype.bind = function() {
        var _this = this;
        this.$list
            .on("click", "[data-item-remove]", function() {
                _this.VoteProject.removeItem($.attr(this, "data-item-remove"));
            })

        .on("click", "[data-item-add]", function() {
                _this.add();
            })
            .on("list.add", function(evt, d) {
                if (_this.type == "image") {
                    _this.picVote(d);
                }
                // refreshPicMaxSelect();
            });
        return this;
    };
    VoteProject.prototype.picVote = function(d) {
        var picUploadSettings = $.extend({
            file_post_name: 'Filedata',
            post_params: {
                module: 'vote'
            },
            button_width: "80",
            button_height: "60",
            custom_settings: {
                success: function(file, data) {
                    $(this.movieElement).siblings("[data-picpath]").val(data.url);
                }
            }
        }, Ibos.app.g("voteUploadSettings"));
        var settings = $.extend({
            button_placeholder_id: "vote_pic_upload_" + d.data.id
        }, picUploadSettings);
        Ibos.imgUpload(settings);

        return this;
    };
    VoteProject.prototype.add = function(data) {
        if (data && data.voteid) {
            this.voteid = data.voteid;
        }
        data = $.extend({
            type: this.type,
            voteid: this.voteid,
            itemid: this.itemid++,
            content: "",
            picpath: ""
        }, data);
        this.VoteProject.addItem(data);
        return this;
    };
    VoteProject.prototype.newadd = function() {
        for (var i = 0; i < 3; i++) {
            this.add();
        }
        return this;
    };

    return {
        Vote: Vote,
        VoteProject: VoteProject,
        VoteList: VoteList
    };
});



$(function() {

    var vote = new Vote.Vote();
    $(vote).on("list.add", function(evt, data) {
        if (data.itemid === undefined) {
            Vote.VoteList[data.id] = new Vote.VoteProject(data);
            if (data.subject == "") {
                Vote.VoteList[data.id].newadd();
            }
        }
    });
    $("#vote_add").on("click", function() {
        vote.add();
    });
    var voteEditData = Ibos.app.g("voteEditData");
    if (voteEditData.length) {
        for (var i = 0, topic; i < voteEditData.length; i++) {
            topic = voteEditData[i];
            vote.add({
                subject: topic.subject,
                maxselectnum: topic.maxselectnum,
                type: topic.type == 1 ? "text" : "image",
                voteid: topic.topicid
            });
        }
        for (var i = voteEditData.length - 1, topic; i >= 0; i--) {
            topic = voteEditData[i];
            for (var j = 0, item; j < topic.items.length; j++) {
                item = topic.items[j];
                Vote.VoteList[i].add({
                    voteid: item.topicid,
                    content: item.content,
                    picpath: item.picpath
                });
            }
        }
        Vote.VoteList.voteid = +voteEditData[ voteEditData.length - 1 ].topicid + 1;
    }else{
        vote.add();
    }

    // 截止时间
    $('#vot_deadline_date').datepicker({
        startDate: new Date(),
        format: "yyyy-mm-dd hh:ii",
        pickTime: true,
        pickSeconds: false
    }).on("hide", function() {
        $(this).find("input").trigger("blur");
    });

    if( $.formValidator ){
        $.getScript(Ibos.app.getAssetUrl("vote") + "/js/lang/zh-cn.js", function(){
            $("#vot_deadline_date input").formValidator({
                relativeID: "endtime",
                onFocus: Ibos.l("VOTE.SELECT_ENDTIME")
            }).regexValidator({
                regExp: "notempty",
                dataType: "enum",
                onError: Ibos.l("VOTE.ENDTIME_EMPTY")
            });
        });
    
    }
});