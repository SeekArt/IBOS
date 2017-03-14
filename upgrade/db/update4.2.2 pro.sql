DROP TABLE IF EXISTS `{{app}}`;
DROP TABLE IF EXISTS `{{app_category}}`;
DROP TABLE IF EXISTS `{{app_personal}}`;

DELETE FROM `{{nav}}` WHERE `module` = 'app';
DELETE FROM `{{menu_common}}` WHERE `module` = 'app';
DELETE FROM `{{node}}` WHERE `module` = 'app';
DELETE FROM `{{node_related}}` WHERE `module` = 'app';
DELETE FROM `{{auth_item}}` WHERE `name` LIKE 'app%';
DELETE FROM `{{auth_item_child}}` WHERE `child` LIKE 'app%';