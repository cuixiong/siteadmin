CREATE TABLE `currency_config` (
  `id` smallint NOT NULL AUTO_INCREMENT COMMENT '货币设置表的主键',
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '货币代码，跟支付方式表关联',
  `is_first` tinyint(1) DEFAULT '0' COMMENT '是否为主货币',
  `exchange_rate` decimal(10,2) DEFAULT '1.00' COMMENT '相对与主货币的汇率',
  `tax_rate` decimal(10,2) DEFAULT '0.00' COMMENT '税率、消费税',
  `is_show` tinyint(1) DEFAULT '0' COMMENT '是否在列表、详情返回',
  `sort` smallint DEFAULT '100' COMMENT '排序:整数,数值越小,排序越靠前。',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态:1代表有效或显示,0代表无效或隐藏。',
  `created_by` int DEFAULT NULL,
  `created_at` int DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `updated_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;