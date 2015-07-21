DROP TABLE IF EXISTS `{{resume}}`;
DROP TABLE IF EXISTS `{{resume_bgchecks}}`;
DROP TABLE IF EXISTS `{{resume_contact}}`;
DROP TABLE IF EXISTS `{{resume_detail}}`;
DROP TABLE IF EXISTS `{{resume_interview}}`;
DROP TABLE IF EXISTS `{{resume_statistics}}`;

DELETE FROM `{{setting}}` WHERE `skey` = 'recruitconfig';
DELETE FROM `{{nav}}` WHERE `module` = 'recruit';
DELETE FROM `{{menu}}` WHERE `m` = 'recruit';
DELETE FROM `{{credit_rule}}` WHERE `action` = 'addresume';
DELETE FROM `{{node}}` WHERE `module` = 'recruit';
DELETE FROM `{{node_related}}` WHERE `module` = 'recruit';
DELETE FROM `{{auth_item}}` WHERE `name` LIKE 'recruit%';
DELETE FROM `{{auth_item_child}}` WHERE `child` LIKE 'recruit%';
DELETE FROM `{{cron}}` WHERE `module` = 'recruit';
DELETE FROM `{{menu_common}}` WHERE `module` = 'recruit';