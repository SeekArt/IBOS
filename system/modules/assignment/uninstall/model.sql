DROP TABLE IF EXISTS `{{assignment}}`;
DROP TABLE IF EXISTS `{{assignment_apply}}`;
DROP TABLE IF EXISTS `{{assignment_remind}}`;
DROP TABLE IF EXISTS `{{assignment_log}}`;

DELETE FROM `{{nav}}` WHERE `module` = 'assignment';
DELETE FROM `{{notify_node}}` WHERE `module` = 'assignment';
DELETE FROM `{{notify_message}}` WHERE `module` = 'assignment';
DELETE FROM `{{node}}` WHERE `module` = 'assignment';
DELETE FROM `{{node_related}}` WHERE `module` = 'assignment';
DELETE FROM `{{auth_item}}` WHERE `name` LIKE 'assignment%';
DELETE FROM `{{auth_item_child}}` WHERE `child` LIKE 'assignment%';
DELETE FROM `{{menu_common}}` WHERE `module` = 'assignment';
DELETE FROM `{{credit_rule}}` WHERE `action` = 'finishassignment';
