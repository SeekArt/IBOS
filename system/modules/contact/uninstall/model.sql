DROP TABLE IF EXISTS `{{contact}}`;
DROP TABLE IF EXISTS `{{contact_hide}}`;

DELETE FROM `{{node}}` WHERE `module` = 'contact';
DELETE FROM `{{node_related}}` WHERE `module` = 'contact';
DELETE FROM `{{auth_item}}` WHERE `name` LIKE 'contact%';
DELETE FROM `{{auth_item_child}}` WHERE `child` LIKE 'contact%';
DELETE FROM `{{menu_common}}` WHERE `module` = 'contact';
DELETE FROM `{{nav}}` WHERE `module` = 'contact';
DELETE FROM `{{menu}}` WHERE `m` = 'contact';