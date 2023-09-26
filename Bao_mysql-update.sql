-- 2023年9月25日11:50:32 BAO
ALTER TABLE `laravel-sass`.`users` 
MODIFY COLUMN `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '账号状态：0禁用，1正常 默认正常' AFTER `password`,
MODIFY COLUMN `position_id` int(11) NOT NULL COMMENT '职位ID' AFTER `role_id`,
ADD COLUMN `verify_status` tinyint(2) NOT NULL COMMENT '账号审核状态：0待审核，1未通过，2已通过 默认待审核' AFTER `position_id`;

ALTER TABLE `laravel-sass`.`users` 
MODIFY COLUMN `verify_status` tinyint(2) NOT NULL DEFAULT 0 COMMENT '账号审核状态：0待审核，1未通过，2已通过 默认待审核' AFTER `position_id`;