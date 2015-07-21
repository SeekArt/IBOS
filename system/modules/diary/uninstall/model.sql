DROP TABLE IF EXISTS `{{diary}}`;
DROP TABLE IF EXISTS `{{diary_record}}`;
DROP TABLE IF EXISTS `{{diary_share}}`;
DROP TABLE IF EXISTS `{{diary_attention}}`;
DROP TABLE IF EXISTS `{{diary_statistics}}`;
DROP TABLE IF EXISTS `{{calendar_record}}`;

DELETE FROM `{{setting}}` WHERE `skey` = 'diaryconfig';
DELETE FROM `{{nav}}` WHERE `module` = 'diary';
DELETE FROM `{{menu}}` WHERE `m` = 'diary';
DELETE FROM `{{notify_node}}` WHERE `node` = 'diary_message';
DELETE FROM `{{notify_message}}` WHERE `module` = 'diary';
DELETE FROM `{{credit_rule}}` WHERE `action` = 'adddiary';
DELETE FROM `{{node}}` WHERE `module` = 'diary';
DELETE FROM `{{node_related}}` WHERE `module` = 'diary';
DELETE FROM `{{auth_item}}` WHERE `name` LIKE 'diary%';
DELETE FROM `{{auth_item_child}}` WHERE `child` LIKE 'diary%';
DELETE FROM `{{menu_common}}` WHERE `module` = 'diary';
