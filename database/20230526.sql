#
#--#针对商户，单用户同时处在充值中的订单数=<N，大于N时拒绝匹配
#

alter table merchant
    add COLUMN `recharge_waiting_limit` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '充值中的订单限制数(超过则限制匹配)';