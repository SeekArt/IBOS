<?php

/**
 * 翻页语言包
 *
 * @package application.language.zh_cn
 * @version $Id$
 * @author Ring <Ring@ibos.com.cn>
 */
return array(
    // 生成翻页样式之前要加入的文本
    'page_header' => ' ',
    // 生成翻页样式之后要加入的文本
    'page_footer' => '<div class="pagination-controll">
					<div class="input-group">
						<input name="page" id="jump_page" type="text" value="currentPage" class="input-small" 
							onkeydown="if(event.keyCode==13){window.location=\'currentUrl&page=\'+this.value;}">
						<span class="input-group-btn input-small" >
							<button type="button" class="btn btn-small" 
							onclick="window.location=\'currentUrl&page=\'+document.getElementById(\'jump_page\').value;">
								<i class="glyphicon-share-alt"></i>
							</button>
						</span>
					</div>
					<span>/itemCount页</span>
				</div>',
    // 上一页按钮文本
    'prevpage_label' => '<i class="glyphicon-chevron-left"></i>',
    // 下一页按钮文本
    'nextpage_label' => '<i class="glyphicon-chevron-right"></i>',
    // 首页按钮文本
    'firstpage_label' => '首页',
    // 首页按钮class样式名
    'firstpage_cssclass' => '',
    // 末页按钮文本
    'lastpage_label' => '末页',
    // 末页按钮class样式名
    'lastpage_cssclass' => '',
    // 隐藏按钮的css样式名
    'hiddenpage_cssclass' => 'disabled',
    // 已选择按钮样式
    'selected_cssclass' => 'active',
    // 翻页数字按钮li父节点的html属性
    'htmlOptions_id' => 'clink_page',
    'htmlOptions_class' => 'pagination',
    // 上一页和下一页按钮的父节点的html属性 
    'pervNextHtmlOption_id' => 'perv_next',
    'pervNextHtmlOption_class' => 'pager btn-group',
);
