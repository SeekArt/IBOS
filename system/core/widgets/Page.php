<?php

/**
 * 翻页组件类，继承CLinkPager
 *
 * @package application.core.widgets
 * @version $Id$
 * @author Ring <Ring@ibos.com.cn>
 */

namespace application\core\widgets;

use application\core\utils\Ibos;
use CHtml;
use CLinkPager;

class Page extends CLinkPager
{

    public $maxButtonCount = 5;

    /**
     * 当前页面Url
     * @var string
     */
    public $currentUrl;

    /**
     * 当前页数
     * @var integer
     */
    private $currentPage = 1;

    /**
     * 总页数
     * @var integer
     */
    private $itemCount = 0;

    /**
     * 上一页和下一页按钮的父节点的html属性
     * @var string
     * @access private
     */
    private $pervNextHtmlOption = array();

    /**
     * 链接的类型 默认为空，值可以为ajax
     * @var string
     * @author gzwwb <gzwwb@ibos.com.cn>
     */
    public $type = '';

    /**
     * ajax提交的地址或者函数,值示例:getAjaxPage(参数1，参数2...)；，JS函数
     * @var type
     * @author gzwwb <gzwwb@ibos.com.cn>
     */
    public $ajaxUrl = '';

    /**
     * Initializes the pager by setting some default property values.
     */
    public function init()
    {
        // 当前页面Url
        $this->setCurrentUrl($this->currentUrl);
        // 当前页数
        $this->currentPage = parent::getCurrentPage(false) + 1;
        // 总页数
        $this->itemCount = parent::getPageCount();
        // 生成翻页样式之前要加入的文本
        $this->header = Ibos::lang('page_header', 'page');
        // 翻页数字按钮li父节点的html属性
        $this->htmlOptions['id'] = Ibos::lang('htmlOptions_id', 'page');
        $this->htmlOptions['class'] = Ibos::lang('htmlOptions_class', 'page');
        // 上一页和下一页按钮的父节点的html属性 
        $this->pervNextHtmlOption['id'] = Ibos::lang('pervNextHtmlOption_id', 'page');
        $this->pervNextHtmlOption['class'] = Ibos::lang('pervNextHtmlOption_class', 'page');
        // 生成翻页样式之后要加入的文本
        $this->setFooter();
        // 上一页按钮文本
        $this->prevPageLabel = Ibos::lang('prevpage_label', 'page');
        // 下一页按钮文本
        $this->nextPageLabel = Ibos::lang('nextpage_label', 'page');
        // 首页按钮文本
        $this->firstPageLabel = Ibos::lang('firstpage_label', 'page');
        // 首页按钮class样式名
        $this->firstPageCssClass = Ibos::lang('firstpage_cssclass', 'page');
        // 末页按钮文本
        $this->lastPageLabel = Ibos::lang('lastpage_label', 'page');
        // 末页按钮class样式名
        $this->lastPageCssClass = Ibos::lang('lastpage_cssclass', 'page');
        // 隐藏按钮的css样式名
        $this->hiddenPageCssClass = Ibos::lang('hiddenpage_cssclass', 'page');
        // 已选择按钮样式
        $this->selectedPageCssClass = Ibos::lang('selected_cssclass', 'page');
        // 翻页css样式文件 等于false 表示不发布
        $this->cssFile = false;
        // 上一页链接class类样式值
        $this->previousPageCssClass = 'btn btn-small';
        // 下一页链接class类样式值
        $this->nextPageCssClass = 'btn btn-small';
    }

    /**
     * Creates the page buttons.
     * @return array a list of page buttons (in HTML code).
     */
    protected function createPageButtons()
    {
        if (($pageCount = $this->getPageCount()) <= 1)
            return array();

        list($beginPage, $endPage) = $this->getPageRange();
        // currentPage is calculated in getPageRange()
        $currentPage = $this->getCurrentPage(false);
        $buttons = array();

        // first page
        $buttons[] = $this->createPageButton($this->firstPageLabel, 0, $this->firstPageCssClass, $currentPage <= 0, false);

        // internal pages
        for ($i = $beginPage; $i <= $endPage; ++$i)
            $buttons[] = $this->createPageButton($i + 1, $i, $this->internalPageCssClass, false, $i == $currentPage);

        // last page
        $buttons[] = $this->createPageButton($this->lastPageLabel, $pageCount - 1, $this->lastPageCssClass, $currentPage >= $pageCount - 1, false);

        // prev page
        if (($page = $currentPage - 1) < 0)
            $page = 0;
        $buttons[] = $this->createPervNextButton($this->prevPageLabel, $page, $this->previousPageCssClass, $currentPage <= 0, false);

        // next page
        if (($page = $currentPage + 1) >= $pageCount - 1)
            $page = $pageCount - 1;
        $buttons[] = $this->createPervNextButton($this->nextPageLabel, $page, $this->nextPageCssClass, $currentPage >= $pageCount - 1, false);


        return $buttons;
    }

    /**
     * Creates a page button for prevbutton and nextbutton .
     * @param string $label the text label for the button
     * @param integer $page the page number
     * @param string $class the CSS class for the page button.
     * @param boolean $hidden whether this page button is visible
     * @param boolean $selected whether this page button is selected
     * @return string the generated button
     */
    private function createPervNextButton($label, $page, $class, $hidden, $selected)
    {
        if ($hidden || $selected) {
            $class .= ' ' . ($hidden ? $this->hiddenPageCssClass : $this->selectedPageCssClass);
        }
        return CHtml::link($label, $this->createPageUrl($page), array('class' => $class));
    }

    /**
     * Executes the widget.
     * This overrides the parent implementation by displaying the generated page buttons.
     */
    public function run()
    {
        $this->registerClientScript();
        $buttons = $this->createPageButtons();
        if (empty($buttons)) {
            return;
        } else {
            $count = count($buttons);
            $prev = $buttons[$count - 2];
            unset($buttons[$count - 2]);
            $next = $buttons[$count - 1];
            unset($buttons[$count - 1]);
            $pervNextArray = array($prev, $next);
        }
        echo $this->header;
        echo CHtml::tag('ul', $this->htmlOptions, implode("\n", $buttons));
        echo $this->footer;
        echo CHtml::tag('div', $this->pervNextHtmlOption, implode("\n", $pervNextArray));
    }

    /**
     * 设置当前页url
     * @param string $value 默认为null
     */
    public function setCurrentUrl($value = null)
    {
        if (is_null($value)) {
            $currentUrl = (string)Ibos::app()->getRequest()->getUrl();
            if (strpos($currentUrl, '?page=') !== false) {
                $splitArray = explode('?page=', $currentUrl);
                $this->currentUrl = $splitArray[0];
            } elseif (strpos($currentUrl, '&page=') !== false) {
                $splitArray = explode('&page=', $currentUrl);
                $this->currentUrl = $splitArray[0];
            } else {
                $this->currentUrl = $currentUrl;
            }
        } else {
            $this->currentUrl = $value;
        }
    }

    /**
     * 设置翻页样式之后要加入的文本
     * @param string $value 默认为null
     */
    public function setFooter($value = null)
    {
        if (is_null($value)) {
            $this->footer = Ibos::lang('page_footer', 'page', array(
                'currentUrl' => $this->currentUrl,
                'currentPage' => $this->currentPage,
                'itemCount' => $this->itemCount,
            ));
        } else {
            $this->footer = $value;
        }
    }

    /**
     * 重写createPageUrl方法，实现url自定义，增加对ajax的处理
     * @param integer $page
     * @return string
     * @author gzwwb <gzwwb@ibos.com.cn>
     */
    protected function createPageUrl($page)
    {
        if (empty($this->type)) {
            return $this->getPages()->createPageUrl($this->getController(), $page);
        } else if ($this->type == 'ajax') {
            return substr($this->ajaxUrl, 0, strpos($this->ajaxUrl, ')')) . ',' . $page . ');';
        }
    }

}
