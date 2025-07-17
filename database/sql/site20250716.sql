ALTER TABLE `invoices`
    ADD COLUMN `department` varchar(255) NULL COMMENT '部门' AFTER `company_address`,
    ADD COLUMN `email` varchar(255) NULL COMMENT '邮箱' AFTER `company_address`;
