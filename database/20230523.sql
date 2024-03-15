

#新增钱包自动转账到代付功能加字段


alter table merchant_collection_balance ADD COLUMN
    `is_auto` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否启用自动转入代付0-否，1-是';

alter table merchant_collection_balance add COLUMN
    `limit_amount` decimal(10,2) unsigned NOT NULL default '0.00' COMMENT '设定金额(启用自动转入代付时的设置)';
