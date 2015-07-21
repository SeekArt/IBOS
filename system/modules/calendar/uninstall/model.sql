DROP TABLE IF EXISTS `{{calendars}}`;
DROP TABLE IF EXISTS `{{tasks}}`;
DROP TABLE IF EXISTS `{{calendar_record}}`;
DROP TABLE IF EXISTS `{{calendar_rep_record}}`;
DROP TABLE IF EXISTS `{{calendar_setup}}`;
DELETE FROM `{{setting}}` WHERE `skey` = 'calendaraddschedule';
DELETE FROM `{{setting}}` WHERE `skey` = 'calendareditschedule';
DELETE FROM `{{setting}}` WHERE `skey` = 'calendarworkingtime';
DELETE FROM `{{setting}}` WHERE `skey` = 'calendaredittask';
DELETE FROM `{{nav}}` WHERE `module` = 'calendar';
DELETE FROM `{{menu}}` WHERE `m` = 'calendar';

DELETE FROM `{{auth_item}}` WHERE `name` LIKE 'calendar%';
DELETE FROM `{{auth_item_child}}` WHERE `child` LIKE 'calendar%';
DELETE FROM `{{node}}` WHERE `module` = 'calendar';
DELETE FROM `{{node_related}}` WHERE `module` = 'calendar';
DELETE FROM `{{notify_node}}` WHERE `node` = 'calendar_message';
DELETE FROM `{{cron}}` WHERE `module` = 'calendar';
DELETE FROM `{{menu_common}}` WHERE `module` = 'calendar';

