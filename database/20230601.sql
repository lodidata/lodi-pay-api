#
#--#修改日志remak字段为text
#

ALTER TABLE admin_log MODIFY COLUMN `remark` text COMMENT '操作详情';


