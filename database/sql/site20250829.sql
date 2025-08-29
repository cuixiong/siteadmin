ALTER TABLE `team_members`
    ADD COLUMN `working_time` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '营业/工作时间' AFTER `language`,
    ADD COLUMN `time_zone` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '工作时区' AFTER `working_time`;

