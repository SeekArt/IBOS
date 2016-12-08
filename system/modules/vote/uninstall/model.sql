DROP TABLE IF EXISTS `{{vote}}`;
DROP TABLE IF EXISTS `{{vote_item}}`;
DROP TABLE IF EXISTS `{{vote_item_count}}`;
DROP TABLE IF EXISTS `{{vote_topic}}`;

DELETE FROM `{{setting}}` WHERE `skey` = 'votethumbenable';
DELETE FROM `{{setting}}` WHERE `skey` = 'votethumbwh';
DELETE FROM `{{menu}}` WHERE `m` = 'vote';
DELETE FROM `{{nav}}` WHERE `module` = 'vote';
DELETE FROM `{{reader}}` where `module` = 'vote';
DELETE FROM `{{notify_node}}` WHERE `module` = 'vote';
DELETE FROM `{{notify_message}}` WHERE `module` = 'vote';