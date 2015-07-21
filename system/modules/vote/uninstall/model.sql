DROP TABLE IF EXISTS `{{vote}}`;
DROP TABLE IF EXISTS `{{vote_item}}`;
DROP TABLE IF EXISTS `{{vote_item_count}}`;

DELETE FROM `{{setting}}` WHERE `skey` = 'votethumbenable';
DELETE FROM `{{setting}}` WHERE `skey` = 'votethumbwh';
DELETE FROM `{{menu}}` WHERE `m` = 'vote';