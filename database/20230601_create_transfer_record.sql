/*
 Navicat Premium Data Transfer

 Source Server         : 52.74.208.242
 Source Server Type    : MySQL
 Source Server Version : 50726
 Source Host           : 52.74.208.242:3308
 Source Schema         : lodi_pay

 Target Server Type    : MySQL
 Target Server Version : 50726
 File Encoding         : 65001

 Date: 26/05/2023 18:39:12
*/

SET NAMES utf8mb4;
SET
    FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for transfer_record
-- ----------------------------
DROP TABLE IF EXISTS `transfer_record`;
CREATE TABLE `transfer_record`
(
    `id`                 bigint(20) UNSIGNED                                           NOT NULL AUTO_INCREMENT,
    `order_sn`           varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci  NULL     DEFAULT NULL COMMENT '订单号',
    `pay_inner_order_sn` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci  NOT NULL DEFAULT '' COMMENT '平台内部订单号(提现)',
    `pay_config_id`      int(11) UNSIGNED                                              NULL     DEFAULT NULL,
    `bank_card_name`     varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL     DEFAULT NULL COMMENT '收款人姓名',
    `bank`               varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci  NULL     DEFAULT NULL COMMENT '银行代码',
    `bank_card_account`  varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL     DEFAULT NULL COMMENT '收款人账号 ',
    `amount`             decimal(16, 2) UNSIGNED                                       NOT NULL DEFAULT 0.00 COMMENT '转账金额',
    `received_amount`    decimal(16, 2) UNSIGNED                                       NOT NULL DEFAULT 0.00 COMMENT '实际到账金额',
    `merchant_id`        bigint(20)                                                    NULL     DEFAULT NULL COMMENT '商户id',
    `status`             tinyint(2)                                                    NOT NULL COMMENT '1=待处理，2=转账成功，0=转账失败',
    `remark`             varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL     DEFAULT NULL COMMENT '备注',
    `created_at`         timestamp(0)                                                  NOT NULL DEFAULT CURRENT_TIMESTAMP(0),
    `updated_at`         timestamp(0)                                                  NOT NULL DEFAULT CURRENT_TIMESTAMP(0) ON UPDATE CURRENT_TIMESTAMP(0),
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 11
  CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_general_ci COMMENT = '转账记录表'
  ROW_FORMAT = Dynamic;

SET
    FOREIGN_KEY_CHECKS = 1;

ALTER TABLE `admin`
    MODIFY COLUMN `last_login_time` timestamp(0) NOT NULL DEFAULT CURRENT_TIMESTAMP(0) COMMENT '上次登录时间' AFTER `last_login_ip`;

ALTER TABLE `orders_collection_trial`
    ADD COLUMN `pay_status` tinyint(2) UNSIGNED NULL COMMENT '代付状态:0=失败 1=成功' AFTER `remark`;
