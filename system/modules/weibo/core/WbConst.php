<?php

namespace application\modules\weibo\core;

class WbConst
{

    const ALL_VIEW_SCOPE = 0;  // 全公司可见范围标识
    const SELF_VIEW_SCOPE = 1;  // 仅自己可见范围标识
    const SELFDEPT_VIEW_SCOPE = 2;  // 所在部门可见范围标识
    const CUSTOM_VIEW_SCOPE = 3;  // 自定义查看范围的标识
    const DEF_LIST_FEED_NUMS = 25;  // 默认每页的微博数
    const ALBUM_DISPLAY_WIDTH = 180;  // 相册展示固定宽度
    const ALBUM_DISPLAY_HEIGHT = 180;  //  固定高度
    const WEIBO_DISPLAY_HEIGHT = 325;  // 固定高度
    const WEIBO_DISPLAY_WIDTH = 560;  // 微博展示固定宽度
    const MAX_VIEW_FEED_NUMS = 10000; // 设定可查看的微博总数，可以提高大数据量下的查询效率

}
