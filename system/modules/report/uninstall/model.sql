DROP TABLE IF EXISTS `{{report}}`;
DROP TABLE IF EXISTS `{{report_record}}`;
DROP TABLE IF EXISTS `{{report_type}}`;
DROP TABLE IF EXISTS `{{report_statistics}}`;
DROP TABLE IF EXISTS `{{calendar_rep_record}}`;

DELETE FROM `{{setting}}` WHERE `skey` = 'reportconfig';
DELETE FROM `{{nav}}` WHERE `module` = 'report';
DELETE FROM `{{menu}}` WHERE `m` = 'report';
DELETE FROM `{{notify_node}}` WHERE `node` = 'report_message';
DELETE FROM `{{notify_message}}` WHERE `module` = 'report';
DELETE FROM `{{credit_rule}}` WHERE `action` = 'addreport';
DELETE FROM `{{node}}` WHERE `module` = 'report';
DELETE FROM `{{node_related}}` WHERE `module` = 'report';
DELETE FROM `{{auth_item}}` WHERE `name` LIKE 'report%';
DELETE FROM `{{auth_item_child}}` WHERE `child` LIKE 'report%';
DELETE FROM `{{menu_common}}` WHERE `module` = 'report';