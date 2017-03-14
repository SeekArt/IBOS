<?php

/**
 * 升级模块默认语言包
 * @author banyan
 */
return array(
    'warnning' => '警告',
    'warning content' => '<p>升级有危险，请先备份数据库，以防万一。</p>
<pre>
1. 可以通过后台->数据库进行备份。
2. 可以通过phpMyadmin进行备份。
2. 使用mysql命令行的工具。
   $> mysqldump -u <span>username</span> -p <span class="text-danger">dbname</span> > <span>filename</span>
   要将上面红色的部分分别替换成对应的用户名和IBOS系统的数据库名。
   比如： mysqldump -u root -p ibosdb >ibosdb.bak
</pre>',
    'upgrade module' => '系统升级',
    'select upgrade version' => '选择版本',
    'from version' => '原来的版本',
    'select version tip' => '务必选择正确的版本，否则会造成数据丢失。',
    'to version' => '升级到',
    'upgrade' => '升级',
    'confirm tip' => '确认要执行的SQL语句',
    'sure execute' => '确认执行',
    'result' => '升级结果',
    'success' => '升级成功',
    'tohome' => '回到首页',
    'permission denied' => '您没有权限访问该页面',
    'none database update' => '无数据库更新',
    'db upgrade error' => '数据库升级失败。请点击文档，查看 <a href="http://doc.ibos.com.cn/article/detail/id/341" target="_blank">更新数据库失败如何处理？</a>'
);
