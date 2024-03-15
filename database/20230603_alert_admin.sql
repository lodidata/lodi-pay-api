ALTER TABLE `admin`
DROP
COLUMN `merchant_id`,
ADD COLUMN `merchant_id` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '所属商户' AFTER `last_login_time`;