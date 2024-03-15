ALTER TABLE `financial_statements`
DROP COLUMN `payment_point`,
DROP COLUMN `recharge_point`,
ADD COLUMN `finance_date` date NOT NULL COMMENT '统计日期';