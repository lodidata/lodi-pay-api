alter table admin_log drop table;
alter table admin_log drop pid;
alter table admin_log drop row_id;


alter table admin_log
    add COLUMN `ip` varchar(20) NOT NULL DEFAULT '0.0.0.0' COMMENT '操作ip',
   add COLUMN `path` varchar(30) NOT NULL default '' COMMENT '请求路径',
   add COLUMN `uname2` varchar(30) NOT NULL DEFAULT '' COMMENT '被操作要用户名称',
   add COLUMN `module` varchar(30) NOT NULL DEFAULT '' COMMENT '模块名称',
   add COLUMN `module_child` varchar(30) NOT NULL DEFAULT '' COMMENT '子模块名称',
   add COLUMN `fun_name` varchar(30) NOT NULL DEFAULT '' COMMENT '功能模块',
   add COLUMN `uid2` int(10) NOT NULL DEFAULT 0 COMMENT '被操作要用户id',
   add COLUMN `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '操作详情';