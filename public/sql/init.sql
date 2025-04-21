/*
 Navicat Premium Data Transfer

 Source Server         : 线上-平台总控
 Source Server Type    : MySQL
 Source Server Version : 80034
 Source Host           : rm-wz9e0v8lb5i105l51ho.mysql.rds.aliyuncs.com:3306
 Source Schema         : globalinforesearchcn

 Target Server Type    : MySQL
 Target Server Version : 80034
 File Encoding         : 65001

 Date: 26/09/2024 14:17:08
*/
-- navicat 导出的纯结构sql文件操作：
-- DROP TABLE IF EXISTS.*\n 替换 空白 (正则匹配)
-- CREATE TABLE ` 替换 CREATE TABLE IF NOT EXISTS `

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;


-- ----------------------------
-- Table structure for applyfors
-- ----------------------------
CREATE TABLE IF NOT EXISTS `applyfors`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '申请姓名',
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '邮箱',
  `company` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '公司',
  `country` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '国家',
  `channel` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '来源',
  `message_id` tinyint(1) NOT NULL COMMENT '留言分类',
  `product_id` int NOT NULL COMMENT '报告ID',
  `category_id` int NOT NULL COMMENT '分类ID',
  `status` tinyint(1) NULL DEFAULT 0 COMMENT '状态',
  `sort` int NULL DEFAULT NULL COMMENT '排序',
  `created_at` int NULL DEFAULT 0 COMMENT '创建时间',
  `created_by` mediumint NULL DEFAULT 0 COMMENT '创建者',
  `updated_by` mediumint NULL DEFAULT 0 COMMENT '更新者',
  `updated_at` int NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for authorities
-- ----------------------------
CREATE TABLE IF NOT EXISTS `authorities`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '名称',
  `body` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '内容',
  `link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '链接地址',
  `class_id` int NULL DEFAULT 0 COMMENT '行业分类',
  `keyword` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '关键词',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '描述',
  `big_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '高清图',
  `thumbnail` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '缩略图',
  `status` tinyint(1) NULL DEFAULT 0 COMMENT '状态',
  `sort` int NULL DEFAULT 0 COMMENT '排序',
  `category_id` int NULL DEFAULT 0 COMMENT '权威分类ID',
  `created_by` mediumint NULL DEFAULT 0 COMMENT '创建者',
  `created_at` int NULL DEFAULT 0 COMMENT '创建时间',
  `updated_by` mediumint NULL DEFAULT 0 COMMENT '更新者',
  `updated_at` int NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 113 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '权威引用' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for citys
-- ----------------------------
CREATE TABLE IF NOT EXISTS `citys`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '主键自增',
  `country_id` int NULL DEFAULT NULL,
  `pid` int NULL DEFAULT NULL COMMENT '父类id',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '城市的名字',
  `type` int NULL DEFAULT NULL COMMENT '城市的类型，0是国，1是省，2是市，3是区',
  `status` tinyint(1) NULL DEFAULT 1 COMMENT '状态',
  `sort` smallint NULL DEFAULT 100,
  `updated_at` int NULL DEFAULT NULL COMMENT '更新时间',
  `created_at` int NOT NULL COMMENT '创建时间',
  `updated_by` mediumint NULL DEFAULT NULL COMMENT '更新者',
  `created_by` mediumint NOT NULL COMMENT '创建者',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3409 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for comments
-- ----------------------------
CREATE TABLE IF NOT EXISTS `comments`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '标题',
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '图片',
  `company` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '公司名称',
  `post` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '评论者的职务',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '评论内容',
  `comment_at` int NULL DEFAULT NULL COMMENT '评论的时间',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态',
  `sort` int NULL DEFAULT 0 COMMENT '排序',
  `created_by` int NULL DEFAULT 0 COMMENT '创建者',
  `created_at` int NULL DEFAULT 0 COMMENT '创建时间',
  `updated_by` int NULL DEFAULT 0 COMMENT '更新者',
  `updated_at` int NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 50 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for contact_us
-- ----------------------------
CREATE TABLE IF NOT EXISTS `contact_us`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `category_id` smallint NULL DEFAULT 0 COMMENT '留言分类',
  `product_id` int NULL DEFAULT 0 COMMENT '可能存在的报告id',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '名称',
  `phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '电话',
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '邮箱',
  `company` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '公司',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '备注、反馈内容',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态',
  `view_status` tinyint(1) NOT NULL DEFAULT 0,
  `country_id` int NULL DEFAULT 0 COMMENT '国家地区ID',
  `province_id` int NULL DEFAULT NULL COMMENT '所属省份ID',
  `city_id` int NULL DEFAULT 0 COMMENT '城市ID',
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '地址',
  `buy_time` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '购买时间',
  `channel` smallint NULL DEFAULT 0 COMMENT '提交来源',
  `language_version` smallint NULL DEFAULT 0 COMMENT '申请的语言版本',
  `sort` int NULL DEFAULT 0 COMMENT '排序',
  `created_by` mediumint NULL DEFAULT 0 COMMENT '创建者',
  `created_at` int NULL DEFAULT 0 COMMENT '创建时间',
  `updated_by` mediumint NULL DEFAULT 0 COMMENT '更新者',
  `updated_at` int NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2092 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for countrys
-- ----------------------------
CREATE TABLE IF NOT EXISTS `countrys`  (
  `id` smallint NOT NULL AUTO_INCREMENT COMMENT '国家/地区表的自增编号',
  `name` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '名称',
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '详情',
  `acronym` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '简写',
  `code` smallint NULL DEFAULT NULL COMMENT '代码',
  `status` tinyint(1) NULL DEFAULT 1 COMMENT '状态:1代表有效,0代表无效。',
  `sort` smallint NULL DEFAULT 100,
  `updated_at` int NULL DEFAULT NULL COMMENT '更新时间',
  `created_at` int NOT NULL COMMENT '创建时间',
  `updated_by` mediumint NULL DEFAULT NULL COMMENT '更新者',
  `created_by` mediumint NOT NULL COMMENT '创建者',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `country`(`name`(20)) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 247 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for coupon_users
-- ----------------------------
CREATE TABLE IF NOT EXISTS `coupon_users`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '用户-优惠券对应关系表的主键',
  `user_id` int NOT NULL COMMENT '用户ID',
  `coupon_id` int NOT NULL COMMENT '优惠券ID',
  `is_used` tinyint(1) NULL DEFAULT 1 COMMENT '是否已使用：0否，1是',
  `use_time` int NULL DEFAULT NULL COMMENT '使用时间',
  `order_id` int NOT NULL DEFAULT 0 COMMENT '订单id',
  `created_by` mediumint NULL DEFAULT 0 COMMENT '数据创建者ID',
  `created_at` int NULL DEFAULT 0 COMMENT '数据创建时（时间戳）',
  `updated_by` mediumint NULL DEFAULT 0 COMMENT '数据修改者ID',
  `updated_at` int NULL DEFAULT 0 COMMENT '数据修改时（时间戳）',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 85 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for coupons
-- ----------------------------
CREATE TABLE IF NOT EXISTS `coupons`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '优惠券表的主键',
  `code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '优惠券码',
  `type` tinyint(1) NOT NULL COMMENT '优惠券类型：1表示折扣券 2表示现金券',
  `value` decimal(9, 2) NOT NULL COMMENT '数值：如果是折扣券，就是打多少折；如果是现金券，就是减去多少钱',
  `user_ids` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '领取到该优惠券的用户id',
  `time_begin` int NOT NULL COMMENT '起始有效期（时间戳）',
  `time_end` int NOT NULL COMMENT '结束有效期（时间戳）',
  `sort` int NULL DEFAULT 100 COMMENT '数据排序',
  `status` tinyint(1) NULL DEFAULT NULL COMMENT '数据状态',
  `created_by` mediumint NOT NULL COMMENT '数据创建者ID',
  `created_at` int NULL DEFAULT NULL COMMENT '数据创建时（时间戳）',
  `updated_by` mediumint NULL DEFAULT NULL COMMENT '数据修改者ID',
  `updated_at` int NULL DEFAULT NULL COMMENT '数据修改时（时间戳）',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 29 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for dictionaries
-- ----------------------------
CREATE TABLE IF NOT EXISTS `dictionaries`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '名称',
  `code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '字典编码',
  `status` tinyint NOT NULL DEFAULT 0 COMMENT '状态：0禁用，1正常',
  `remark` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '备注',
  `sort` int NULL DEFAULT 0 COMMENT '排序',
  `updated_at` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  `updated_by` mediumint NULL DEFAULT 0 COMMENT '更新者',
  `created_by` mediumint NOT NULL DEFAULT 0 COMMENT '创建者',
  `created_at` int NOT NULL DEFAULT 0 COMMENT '更新者',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `code`(`code`) USING BTREE COMMENT '唯一索引'
) ENGINE = InnoDB AUTO_INCREMENT = 66 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '字典管理' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for dictionary_values
-- ----------------------------
CREATE TABLE IF NOT EXISTS `dictionary_values`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `parent_id` int NOT NULL DEFAULT 0 COMMENT '父级ID',
  `code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '父级编码',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '名称',
  `english_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '英文名称',
  `value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '内容',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态：0禁用，1正常',
  `sort` int NOT NULL DEFAULT 0 COMMENT '排序',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '备注',
  `created_by` mediumint NOT NULL DEFAULT 0 COMMENT '创建者',
  `created_at` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `updated_by` mediumint NULL DEFAULT 0 COMMENT '更新者',
  `updated_at` int NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `parent_id`(`parent_id`) USING BTREE COMMENT '父级索引',
  INDEX `code`(`code`) USING BTREE COMMENT '编码索引',
  INDEX `status`(`status`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 238 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '字典管理的值' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for email_logs
-- ----------------------------
CREATE TABLE IF NOT EXISTS `email_logs`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `status` tinyint(1) NOT NULL COMMENT '状态：0失败，1成功',
  `send_email_id` mediumint NOT NULL COMMENT '发邮场景ID',
  `emails` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '接受邮箱',
  `email_scenes` mediumint NOT NULL DEFAULT 0 COMMENT '邮箱场景',
  `data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '场景数据',
  `sort` int NULL DEFAULT 0 COMMENT '排序',
  `updated_at` int NULL DEFAULT 0 COMMENT '更新时间',
  `created_at` int NULL DEFAULT 0 COMMENT '创建时间',
  `created_by` int NULL DEFAULT 0 COMMENT '创建者',
  `updated_by` int NULL DEFAULT 0 COMMENT '更新者',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 73 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for email_scenes
-- ----------------------------
CREATE TABLE IF NOT EXISTS `email_scenes`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '场景名称',
  `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '邮箱标题',
  `body` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '邮箱内容',
  `email_sender_id` mediumint NOT NULL DEFAULT 0 COMMENT '发送邮件的邮箱ID',
  `email_recipient` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '邮箱收件人：多个邮箱用逗号分开',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态：0禁用，1正常',
  `sort` int NULL DEFAULT 0 COMMENT '排序',
  `action` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '测试邮箱发送方法名',
  `alternate_email_id` mediumint NOT NULL DEFAULT 0 COMMENT '发送邮件的备用邮箱ID',
  `updated_by` mediumint NULL DEFAULT 0 COMMENT '更新者',
  `updated_at` int NULL DEFAULT 0 COMMENT '更新时间',
  `created_by` mediumint NOT NULL DEFAULT 0 COMMENT '创建者',
  `created_at` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `action`(`action`) USING BTREE COMMENT '唯一索引'
) ENGINE = InnoDB AUTO_INCREMENT = 19 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '发邮场景' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for emails
-- ----------------------------
CREATE TABLE IF NOT EXISTS `emails`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '邮箱名字',
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '邮箱账号',
  `host` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'SMTP主机地址',
  `port` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'SMTP主机端口',
  `encryption` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'SMTP加密类型',
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '邮箱授权码',
  `status` tinyint(1) NULL DEFAULT 1 COMMENT '状态',
  `sort` int NULL DEFAULT 0 COMMENT '排序',
  `updated_by` mediumint NULL DEFAULT 0 COMMENT '更新者',
  `updated_at` int NULL DEFAULT 0 COMMENT '更新时间',
  `created_by` mediumint NOT NULL DEFAULT 0 COMMENT '创建者',
  `created_at` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 19 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for failed_jobs
-- ----------------------------
CREATE TABLE IF NOT EXISTS `failed_jobs`  (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `uuid` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `exception` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 16 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for histories
-- ----------------------------
CREATE TABLE IF NOT EXISTS `histories`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `year` varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '年份',
  `body` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '发展事件',
  `sort` int NULL DEFAULT 0 COMMENT '排序',
  `status` tinyint(1) NULL DEFAULT 0 COMMENT '状态',
  `created_by` mediumint NULL DEFAULT 0 COMMENT '创建者',
  `created_at` int NULL DEFAULT 0 COMMENT '创建时间',
  `updated_by` mediumint NULL DEFAULT 0 COMMENT '更新者',
  `updated_at` int NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 11 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '发展历程' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for information
-- ----------------------------
CREATE TABLE IF NOT EXISTS `information`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '新闻表的自增编号',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '标题（seo标题）',
  `type` tinyint(1) NOT NULL DEFAULT 0 COMMENT '资讯类型',
  `category_id` tinyint(1) NULL DEFAULT NULL COMMENT '行业分类ID',
  `keywords` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'seo关键词(用来关联相关新闻)',
  `tags` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '标签',
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '自定义链接',
  `thumb` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '封面图',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '简述/seo描述',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '内容',
  `sort` smallint NULL DEFAULT NULL COMMENT '排序',
  `show_home` tinyint(1) NULL DEFAULT 0 COMMENT '是否在首页显示',
  `status` tinyint(1) NULL DEFAULT 1 COMMENT '状态',
  `created_by` smallint NOT NULL COMMENT '创建者',
  `created_at` int NOT NULL COMMENT '创建时间',
  `updated_by` smallint NULL DEFAULT NULL COMMENT '修改者',
  `updated_at` int NULL DEFAULT NULL COMMENT '修改时间',
  `hits` int NULL DEFAULT 0 COMMENT '虚拟点击',
  `real_hits` int NULL DEFAULT 0 COMMENT '真实点击',
  `upload_at` int NULL DEFAULT NULL COMMENT '发布时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `sort`(`sort`) USING BTREE,
  INDEX `upload_at`(`upload_at`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for invoices
-- ----------------------------
CREATE TABLE IF NOT EXISTS `invoices`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '发票表的自增编号',
  `company_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '公司名称',
  `company_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '公司地址（注册地址）',
  `tax_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '纳税人识别码',
  `invoice_type` tinyint(1) NULL DEFAULT 1 COMMENT '发票类型:1代表普通发票,2代表专用发票,默认是1。',
  `price` decimal(8, 2) NOT NULL DEFAULT 0.00 COMMENT '发票金额',
  `user_id` int NULL DEFAULT NULL COMMENT '用户编号',
  `order_id` int NOT NULL COMMENT '订单编号',
  `title` varchar(2048) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '发票抬头（改为：已购买的若干份报告名称）',
  `contact_person` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '联系人（国内发票用不到本字段）',
  `contact_detail` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '内容（国内发票用不到本字段）',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态状态',
  `apply_status` tinyint(1) NULL DEFAULT 1 COMMENT '开票状态:0代表未开,1代表开票中,2代表已开',
  `phone` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '注册电话',
  `bank_name` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '开户银行',
  `bank_account` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '银行账户',
  `created_at` int NOT NULL COMMENT '发票生成时间（开票日期）',
  `updated_at` int NULL DEFAULT NULL COMMENT '修改时间',
  `created_by` mediumint NULL DEFAULT NULL COMMENT '创建者',
  `updated_by` mediumint NULL DEFAULT NULL COMMENT '更新者',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE,
  INDEX `order_id`(`order_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 19 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for ip_ban_log
-- ----------------------------
CREATE TABLE IF NOT EXISTS `ip_ban_log`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `ip` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'ip',
  `ip_addr` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ip归属地',
  `route` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '路由',
  `sort` int NULL DEFAULT 0 COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态',
  `created_by` mediumint NOT NULL DEFAULT 0 COMMENT '创建者',
  `created_at` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `updated_by` mediumint NOT NULL DEFAULT 0 COMMENT '更新者',
  `updated_at` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 9 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for language_websites
-- ----------------------------
CREATE TABLE IF NOT EXISTS `language_websites`  (
  `id` smallint NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '名称',
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '跳转链接',
  `sort` smallint NULL DEFAULT NULL,
  `status` smallint NULL DEFAULT NULL,
  `created_by` mediumint NULL DEFAULT NULL,
  `created_at` int NULL DEFAULT NULL,
  `updated_by` mediumint NULL DEFAULT NULL,
  `updated_at` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for languages
-- ----------------------------
CREATE TABLE IF NOT EXISTS `languages`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '语言',
  `code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `sort` tinyint NOT NULL DEFAULT 100 COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态',
  `created_by` int NOT NULL,
  `created_at` int NOT NULL,
  `updated_by` int NULL DEFAULT NULL,
  `updated_at` int NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 34 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for links
-- ----------------------------
CREATE TABLE IF NOT EXISTS `links`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '名称',
  `logo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT 'logo',
  `link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '链接',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态',
  `sort` int NULL DEFAULT 100 COMMENT '排序',
  `created_by` mediumint NULL DEFAULT 0 COMMENT '创建者',
  `created_at` int NULL DEFAULT 0 COMMENT '创建时间',
  `updated_by` mediumint NULL DEFAULT 0 COMMENT '更新者',
  `updated_at` int NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 37 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for menus
-- ----------------------------
CREATE TABLE IF NOT EXISTS `menus`  (
  `id` smallint NOT NULL AUTO_INCREMENT COMMENT '前端菜单的自增编号',
  `parent_id` smallint NULL DEFAULT 0 COMMENT '上级',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '菜单名称',
  `is_single` tinyint(1) NULL DEFAULT 0 COMMENT '是否为单页:1代表是,0代表否。',
  `type` tinyint(1) NULL DEFAULT 0 COMMENT '类型:1代表顶部导航菜单,2代表底部导航菜单,3代表顶部兼底部导航菜单。',
  `banner_pc` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '页面背景图',
  `banner_mobile` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '移动端页面背景图',
  `banner_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '覆盖在页面背景图上的文字',
  `banner_short_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '页面背景图短标题',
  `link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '链接',
  `sort` int NULL DEFAULT 0 COMMENT '排序',
  `status` tinyint(1) NULL DEFAULT 1 COMMENT '状态:1代表有效,0代表无效。',
  `seo_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '页面的SEO标题',
  `seo_keyword` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '页面的SEO关键词',
  `seo_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '页面的SEO描述',
  `prompt` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '提示语',
  `created_by` int NULL DEFAULT 0 COMMENT '数据创建者',
  `created_at` int NULL DEFAULT 0 COMMENT '数据创建时',
  `updated_by` int NULL DEFAULT 0 COMMENT '数据修改者',
  `updated_at` int NULL DEFAULT 0 COMMENT '数据修改时',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `type`(`type`) USING BTREE COMMENT '类型索引',
  INDEX `status`(`status`) USING BTREE COMMENT '状态索引'
) ENGINE = InnoDB AUTO_INCREMENT = 53 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for message_categories
-- ----------------------------
CREATE TABLE IF NOT EXISTS `message_categories`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '名称',
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `style` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '后台留言列表样式',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态',
  `sort` int NOT NULL DEFAULT 0 COMMENT '排序',
  `created_by` mediumint NULL DEFAULT 0 COMMENT '创建者',
  `created_at` int NULL DEFAULT 0 COMMENT '创建时间',
  `updated_by` mediumint NULL DEFAULT 0 COMMENT '更新者',
  `updated_at` int NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for message_language_versions
-- ----------------------------
CREATE TABLE IF NOT EXISTS `message_language_versions`  (
  `id` tinyint(1) NOT NULL AUTO_INCREMENT COMMENT '提交留言选择样本是什么语言',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` tinyint(1) NULL DEFAULT 1,
  `sort` int NULL DEFAULT 0 COMMENT '排序',
  `created_by` mediumint NULL DEFAULT 0 COMMENT '创建者',
  `created_at` int NULL DEFAULT 0 COMMENT '创建时间',
  `updated_by` mediumint NULL DEFAULT 0 COMMENT '更新者',
  `updated_at` int NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for migrations
-- ----------------------------
CREATE TABLE IF NOT EXISTS `migrations`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for news
-- ----------------------------
CREATE TABLE IF NOT EXISTS `news`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '新闻表的自增编号',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '标题（seo标题）',
  `short_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '短标题（副标题）',
  `category_id` smallint NULL DEFAULT 0 COMMENT '所属分类',
  `type` tinyint(1) NOT NULL DEFAULT 0 COMMENT '新闻类型',
  `keywords` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'seo关键词(用来关联相关新闻)',
  `tags` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '标签',
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '自定义链接',
  `thumb` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '缩略图',
  `sort` int NULL DEFAULT 100 COMMENT '排序',
  `show_home` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否在首页显示:1代表是,0代表否,默认是0。',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '内容',
  `created_by` smallint NOT NULL COMMENT '上传者（就是后台登录者）',
  `created_at` int NOT NULL COMMENT '上传时间',
  `updated_by` smallint NULL DEFAULT NULL COMMENT '修改者（就是后台登录者）',
  `updated_at` int NULL DEFAULT NULL COMMENT '修改时间',
  `description` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'seo描述',
  `hits` int NULL DEFAULT NULL COMMENT '浏览量（设置3位数的随机数）',
  `upload_at` int NULL DEFAULT NULL COMMENT '发布时间',
  `real_hits` int NULL DEFAULT 0 COMMENT '真实点击',
  `author` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '作者',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2704 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for news_category
-- ----------------------------
CREATE TABLE IF NOT EXISTS `news_category`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `sort` smallint NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态',
  `seo_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `seo_keyword` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `seo_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `created_by` smallint NOT NULL COMMENT '创建者',
  `created_at` int NOT NULL COMMENT '创建时间',
  `updated_by` smallint NULL DEFAULT NULL COMMENT '修改者',
  `updated_at` int NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 44 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for offices
-- ----------------------------
CREATE TABLE IF NOT EXISTS `offices`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '城市',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '简称',
  `language_alias` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '语言别名',
  `region` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '区域',
  `area` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '地区',
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '图片',
  `national_flag` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '国旗',
  `phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '电话',
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '地址',
  `post` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '邮编',
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '邮箱',
  `website` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '网址',
  `working_language` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '工作语言',
  `working_language_status` tinyint(1) NULL DEFAULT 1 COMMENT '工作语言状态1:显示,0关闭',
  `working_time` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '工作时间',
  `working_time_status` tinyint(1) NULL DEFAULT 1 COMMENT '工作时间状态1:显示,0关闭',
  `time_zone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '时区',
  `time_zone_status` tinyint(1) NULL DEFAULT 1 COMMENT '时区状态1:显示,0关闭',
  `status` tinyint(1) NULL DEFAULT 0 COMMENT '状态',
  `sort` int NULL DEFAULT 0 COMMENT '排序',
  `created_by` mediumint NULL DEFAULT 0 COMMENT '创建者',
  `created_at` int NULL DEFAULT 0 COMMENT '创建时间',
  `updated_by` mediumint NULL DEFAULT 0 COMMENT '更新者',
  `updated_at` int NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '办公室列表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for operation_log_2024
-- ----------------------------
CREATE TABLE IF NOT EXISTS `operation_log_2024`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '日志类型',
  `category` tinyint NOT NULL COMMENT '日志分类',
  `route` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '请求路由',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '日志标题',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '日志内容',
  `site` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '站点',
  `sort` int NULL DEFAULT 0 COMMENT '排序',
  `module` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '模块',
  `created_by` mediumint NOT NULL DEFAULT 0 COMMENT '创建者',
  `updated_by` mediumint NULL DEFAULT 0 COMMENT '更新者',
  `created_at` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `updated_at` int NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 315 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for operation_logs
-- ----------------------------
CREATE TABLE IF NOT EXISTS `operation_logs`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '日志类型',
  `category` tinyint NOT NULL COMMENT '日志分类',
  `route` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '请求路由',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '日志标题',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '日志内容',
  `site` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '站点',
  `sort` int NULL DEFAULT 0 COMMENT '排序',
  `module` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '模块',
  `created_by` mediumint NOT NULL DEFAULT 0 COMMENT '创建者',
  `updated_by` mediumint NULL DEFAULT 0 COMMENT '更新者',
  `created_at` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `updated_at` int NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for order_goods
-- ----------------------------
CREATE TABLE IF NOT EXISTS `order_goods`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '订单商品表的自增编号（主键）',
  `order_id` int NOT NULL COMMENT '订单编号（不是订单号）,对应订单表（order）的主键',
  `goods_id` int NOT NULL COMMENT '产品（即报告）编号,对应product表的主键',
  `goods_number` tinyint NOT NULL COMMENT '某一种商品的数量',
  `goods_original_price` decimal(11, 2) NOT NULL COMMENT '商品原价',
  `goods_present_price` decimal(11, 2) NULL DEFAULT NULL COMMENT '商品现价',
  `price_edition` int NOT NULL COMMENT '价格版本',
  `created_at` int NOT NULL COMMENT '创建时间',
  `updated_at` int NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 647 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for order_status
-- ----------------------------
CREATE TABLE IF NOT EXISTS `order_status`  (
  `id` tinyint(1) NOT NULL AUTO_INCREMENT COMMENT '订单状态表的主键',
  `name` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `order` tinyint(1) NULL DEFAULT NULL,
  `status` tinyint(1) NULL DEFAULT NULL,
  `creater` int NULL DEFAULT NULL,
  `created_at` int NULL DEFAULT NULL,
  `updater` int NULL DEFAULT NULL,
  `updated_at` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for orders
-- ----------------------------
CREATE TABLE IF NOT EXISTS `orders`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '订单表的自增编号（主键）',
  `order_number` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '订单号:订单生成的年月日时分秒加上两位数的序号，例如:2020070209510601,总共16位数',
  `out_order_num` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '外部订单号',
  `user_id` int NOT NULL DEFAULT 0 COMMENT '购物者编号（用户ID）,对应user表的主键',
  `is_pay` tinyint(1) NOT NULL DEFAULT 1 COMMENT '订单状态;1:未付款,2:已付款,3:支付失败',
  `pay_time` int NULL DEFAULT NULL COMMENT '付款时间',
  `pay_type` tinyint(1) NULL DEFAULT NULL COMMENT '支付方式',
  `pay_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '支付方式code',
  `order_amount` decimal(8, 2) NOT NULL COMMENT '订单金额',
  `actually_paid` decimal(8, 2) NULL DEFAULT NULL COMMENT '实付金额',
  `pay_coin_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'RMB' COMMENT '实际支付货币类型',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态',
  `view_status` tinyint(1) NOT NULL DEFAULT 0,
  `is_delete` tinyint(1) NULL DEFAULT 0 COMMENT '是否被前台用户删除:0代表否,1代表是。',
  `created_at` int NOT NULL DEFAULT 0 COMMENT '下单时间',
  `updated_at` int NULL DEFAULT NULL COMMENT '修改时间',
  `username` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `channel_id` tinyint NULL DEFAULT NULL,
  `company` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `country_id` smallint NULL DEFAULT 0,
  `province_id` int NULL DEFAULT 0,
  `city_id` int NULL DEFAULT 0,
  `post_id` tinyint(1) NULL DEFAULT 0 COMMENT '送货方式ID',
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '详细的送货地址',
  `coupon_id` int NULL DEFAULT NULL COMMENT '优惠券ID:对应coupon表的主键',
  `coupon_amount` decimal(12, 2) NULL DEFAULT 0.00 COMMENT '优惠金额',
  `wechat_type` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '微信支付时调用的场景',
  `is_mobile_pay` tinyint(1) NULL DEFAULT 0 COMMENT '是否为移动端支付：0代表否，1代表是。',
  `updated_by` mediumint NULL DEFAULT NULL COMMENT '更新者',
  `created_by` mediumint NOT NULL DEFAULT 0 COMMENT '创建者',
  `rate` decimal(8, 3) NULL DEFAULT NULL COMMENT '付款时记录汇率',
  `ip` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `ip_region` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `remarks` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '订单备注',
  `exchange_rate` decimal(10, 3) NULL DEFAULT 1.000 COMMENT '付款时记录汇率',
  `exchange_amount` decimal(12, 2) NOT NULL DEFAULT 0.00 COMMENT '汇率换算后金额',
  `tax_rate` decimal(8, 2) NOT NULL DEFAULT 0.00 COMMENT '税率',
  `tax_amount` decimal(12, 2) NOT NULL DEFAULT 0.00 COMMENT '税率金额',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 529 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for oss_file
-- ----------------------------
CREATE TABLE IF NOT EXISTS `oss_file`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '文件路径',
  `oss_path` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT 'oss文件全路径',
  `file_fullpath` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL COMMENT '文件全路径',
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '文件名称',
  `file_size` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '文件大小',
  `file_suffix` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '文件后缀',
  `updated_at` int NULL DEFAULT 0 COMMENT '更新时间',
  `created_at` int NULL DEFAULT 0 COMMENT '创建时间',
  `created_by` int NULL DEFAULT 0 COMMENT '创建者',
  `updated_by` int NULL DEFAULT 0 COMMENT '更新者',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for pages
-- ----------------------------
CREATE TABLE IF NOT EXISTS `pages`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `page_id` int NOT NULL COMMENT '页面ID',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '内容',
  `sort` int NULL DEFAULT 0 COMMENT '排序',
  `status` tinyint(1) NULL DEFAULT NULL COMMENT '状态',
  `created_by` mediumint NULL DEFAULT NULL COMMENT '创建者',
  `created_at` int NULL DEFAULT NULL COMMENT '创建时间',
  `updated_by` mediumint NULL DEFAULT NULL COMMENT '更新者',
  `updated_at` int NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 38 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for partners
-- ----------------------------
CREATE TABLE IF NOT EXISTS `partners`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '名称',
  `logo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Logo',
  `sort` int NULL DEFAULT 0 COMMENT '排序',
  `status` tinyint(1) NULL DEFAULT 0 COMMENT '状态',
  `created_by` mediumint NULL DEFAULT 0 COMMENT '创建者',
  `created_at` int NULL DEFAULT 0 COMMENT '创建时间',
  `updated_by` mediumint NULL DEFAULT 0 COMMENT '更新者',
  `updated_at` int NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 25 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '合作伙伴' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for partners2
-- ----------------------------
CREATE TABLE IF NOT EXISTS `partners2`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '名称',
  `logo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Logo',
  `sort` int NULL DEFAULT 0 COMMENT '排序',
  `status` tinyint(1) NULL DEFAULT 0 COMMENT '状态',
  `created_by` mediumint NULL DEFAULT 0 COMMENT '创建者',
  `created_at` int NULL DEFAULT 0 COMMENT '创建时间',
  `updated_by` mediumint NULL DEFAULT 0 COMMENT '更新者',
  `updated_at` int NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 26 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '合作伙伴' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for payments_copy1
-- ----------------------------
CREATE TABLE IF NOT EXISTS `payments_copy1`  (
  `id` smallint NOT NULL AUTO_INCREMENT COMMENT '后台支付方式设置表的主键',
  `name` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '支付方式名称',
  `code` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '代码（重要！）',
  `img` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '支付方式图标',
  `notice` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '详细介绍',
  `order` smallint NULL DEFAULT NULL COMMENT '排序:整数,数值越小,排序越靠前。',
  `status` tinyint(1) NULL DEFAULT 1 COMMENT '状态:1代表有效或显示,2代表无效或隐藏。',
  `creater` int NULL DEFAULT NULL,
  `created_at` int NULL DEFAULT NULL,
  `updater` int NULL DEFAULT NULL,
  `updated_at` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for pays
-- ----------------------------
CREATE TABLE IF NOT EXISTS `pays`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '名称',
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '支付图片',
  `info_login` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT 'info logo',
  `info_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT 'info key',
  `return_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '同步回调地址',
  `notify_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '异步回调地址',
  `sign` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '回调签名',
  `status` tinyint(1) NOT NULL COMMENT '状态',
  `sort` int NULL DEFAULT 0 COMMENT '排序',
  `content` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '内容',
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '代码（重要！）',
  `created_by` mediumint NULL DEFAULT 0 COMMENT '创建者',
  `created_at` int NULL DEFAULT 0 COMMENT '创建时间',
  `updated_by` mediumint NULL DEFAULT 0 COMMENT '更新者',
  `updated_at` int NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 11 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for plate_values
-- ----------------------------
CREATE TABLE IF NOT EXISTS `plate_values`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `parent_id` int NULL DEFAULT NULL COMMENT '父级ID',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '标题',
  `short_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '短标题',
  `link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '链接',
  `alias` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '别名',
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '图片',
  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '图标',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '内容',
  `sort` int NULL DEFAULT 0 COMMENT '排序',
  `status` tinyint NULL DEFAULT 0 COMMENT '状态',
  `created_by` mediumint NULL DEFAULT 0 COMMENT '创建者',
  `created_at` int NULL DEFAULT 0 COMMENT '创建时间',
  `updated_by` mediumint NULL DEFAULT 0 COMMENT '更新者',
  `updated_at` int NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 29 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for plates
-- ----------------------------
CREATE TABLE IF NOT EXISTS `plates`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '名称',
  `alias` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '别名',
  `page_id` tinyint NOT NULL COMMENT '页面ID',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '标题',
  `short_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '短标题',
  `pc_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'PC图片',
  `mb_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '手机端图片',
  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `content` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '内容',
  `status` tinyint NULL DEFAULT 0 COMMENT '状态',
  `sort` int NULL DEFAULT 0 COMMENT '排序',
  `created_by` mediumint NOT NULL DEFAULT 0 COMMENT '创建者',
  `created_at` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `updated_by` mediumint NULL DEFAULT 0 COMMENT '更新者',
  `updated_at` int NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 12 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for price_edition_values
-- ----------------------------
CREATE TABLE IF NOT EXISTS `price_edition_values`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '价格版本项名称',
  `edition_id` int NOT NULL COMMENT '所属价格版本组ID',
  `language_id` int NOT NULL COMMENT '语言ID',
  `rules` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '规则',
  `notice` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '提示/备注/版本项说明',
  `is_logistics` tinyint(1) NULL DEFAULT 0 COMMENT '是否使用物流：1-启用，0-禁用；默认为0',
  `sort` tinyint NOT NULL DEFAULT 100 COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态：1-启用，0-禁用；默认为1',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态：1-正常，0-已删除；默认为1',
  `created_by` mediumint NOT NULL COMMENT '创建者',
  `created_at` int NOT NULL COMMENT '创建时间',
  `updated_by` mediumint NULL DEFAULT NULL COMMENT '修改者',
  `updated_at` int NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `status`(`status`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 184 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for price_editions
-- ----------------------------
CREATE TABLE IF NOT EXISTS `price_editions`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `publisher_id` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '出版商ID',
  `sort` tinyint NOT NULL DEFAULT 100 COMMENT '排序；默认为100；越小值越优先显示',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态：1-启用，0-禁用；默认为1',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态：1-正常，0-已删除；默认为1',
  `created_by` mediumint NOT NULL COMMENT '创建者',
  `created_at` int NOT NULL COMMENT '创建时间',
  `updated_by` mediumint NULL DEFAULT NULL COMMENT '修改者',
  `updated_at` int NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 55 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for problems
-- ----------------------------
CREATE TABLE IF NOT EXISTS `problems`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `problem` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '问题',
  `reply` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '答复',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态',
  `sort` int NULL DEFAULT 0 COMMENT '排序',
  `created_by` mediumint NULL DEFAULT 0 COMMENT '创建者',
  `created_at` int NULL DEFAULT 0 COMMENT '创建时间',
  `updated_by` mediumint NULL DEFAULT 0 COMMENT '更新者',
  `updated_at` int NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 13 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '常见问题' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for product_category
-- ----------------------------
CREATE TABLE IF NOT EXISTS `product_category`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '产品分类表的自增编号',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '名称',
  `pid` int NOT NULL DEFAULT 0 COMMENT '父级id',
  `link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '自定义链接',
  `thumb` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '缩略图',
  `home_thumb` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '首页缩略图（仅作用于显示在首页的报告）',
  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '图标',
  `icon_hover` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '图标经过',
  `sort` int NULL DEFAULT 100 COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态:1代表有效,0代表无效。',
  `is_hot` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否热门, 0默认普通,  1:热门',
  `is_recommend` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否精品, 0默认普通,  1:精品',
  `show_home` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否显示:1代表显示,0代表折叠。',
  `discount` smallint UNSIGNED NULL DEFAULT 100 COMMENT '折扣率：两位整数,例如85代表85折85%。',
  `discount_amount` decimal(8, 2) UNSIGNED NULL DEFAULT 0.00 COMMENT '折扣金额：大于等于0',
  `discount_type` tinyint(1) NULL DEFAULT 1 COMMENT '折扣类型: 1：折扣率；2：折扣金额',
  `discount_time_begin` int NULL DEFAULT NULL COMMENT '折扣有效期的开始时间',
  `discount_time_end` int NULL DEFAULT NULL COMMENT '折扣有效期的结束时间',
  `seo_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `seo_keyword` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `seo_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '申请样本、定制报告发送时需根据产品分类多发一份至此邮箱',
  `keyword_suffix` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `product_tag` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `updated_at` int NULL DEFAULT NULL COMMENT '更新时间',
  `created_at` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `updated_by` mediumint NULL DEFAULT NULL COMMENT '更新者',
  `created_by` mediumint NOT NULL DEFAULT 0 COMMENT '创建者',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `name`(`name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 18 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for product_description
-- ----------------------------
CREATE TABLE IF NOT EXISTS `product_description`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL COMMENT '产品表的编号',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '描述',
  `table_of_content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '目录',
  `tables_and_figures` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '图表',
  `definition` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '定义',
  `overview` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '概述',
  `description_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '描述(英)',
  `table_of_content_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '目录(英)',
  `tables_and_figures_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '图表(英)',
  `companies_mentioned` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '提及的公司',
  `updated_at` int NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `yii1_product_detailed_product_id_IDX`(`product_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for product_excel_field
-- ----------------------------
CREATE TABLE IF NOT EXISTS `product_excel_field`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '标题文本',
  `field` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '字段',
  `sort` smallint NULL DEFAULT 100 COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态:1代表有效,0代表无效。',
  `created_by` mediumint NOT NULL COMMENT '创建者',
  `updated_by` mediumint NULL DEFAULT NULL COMMENT '更新者',
  `created_at` int NOT NULL COMMENT '创建时间',
  `updated_at` int NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 45 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for product_export_log
-- ----------------------------
CREATE TABLE IF NOT EXISTS `product_export_log`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增主键ID',
  `file` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '最终文件名称/路径',
  `count` smallint NULL DEFAULT 0 COMMENT '导出总条数',
  `success_count` smallint NULL DEFAULT 0 COMMENT '导出成功条数',
  `error_count` smallint NULL DEFAULT 0 COMMENT '导出失败条数',
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '详情',
  `created_at` int NOT NULL COMMENT '创建时间',
  `created_by` mediumint NOT NULL COMMENT '创建者',
  `updated_at` int NOT NULL COMMENT '更新时间',
  `state` tinyint(1) NOT NULL DEFAULT 0 COMMENT '记录任务状态',
  `sort` smallint NOT NULL DEFAULT 100 COMMENT '排序',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for product_routine
-- ----------------------------
CREATE TABLE IF NOT EXISTS `product_routine`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '产品表的自增编号',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '名称',
  `english_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '英文名称',
  `thumb` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '缩略图',
  `publisher_id` smallint NULL DEFAULT NULL COMMENT '出版商id',
  `category_id` smallint NOT NULL COMMENT '所属分类',
  `country_id` smallint NULL DEFAULT NULL COMMENT '所属区域',
  `price` decimal(8, 2) NOT NULL COMMENT '基础价格',
  `keywords` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '关键词',
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '自定义链接',
  `published_date` int NOT NULL COMMENT '出版日期',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态:1代表有效,0代表无效。',
  `author` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '作者',
  `show_home` tinyint(1) NULL DEFAULT 0 COMMENT '是否在首页显示:1代表是,0代表否,默认是0。',
  `have_sample` tinyint(1) NULL DEFAULT 0 COMMENT '有否对应的样本文件(pdf):1代表有,0代表无。',
  `discount` smallint NULL DEFAULT 100 COMMENT '折扣数值:两位整数,例如85代表85折85%。',
  `discount_amount` decimal(8, 2) NULL DEFAULT 0.00,
  `discount_type` tinyint(1) NULL DEFAULT 1 COMMENT '折扣类型: 1：折扣率；2：折扣金额',
  `discount_time_begin` int NULL DEFAULT NULL COMMENT '折扣有效期的开始时间',
  `discount_time_end` int NULL DEFAULT NULL COMMENT '折扣有效期的结束时间',
  `pages` smallint NULL DEFAULT 6 COMMENT '页数',
  `tables` smallint NULL DEFAULT 6 COMMENT '表格数Number of Tables and Figures',
  `hits` int NULL DEFAULT 0 COMMENT '浏览量（设置3位数的随机数）',
  `downloads` int NULL DEFAULT NULL COMMENT '下载数（设置3位数的随机数。仅限于下载PDF）',
  `show_hot` tinyint(1) NULL DEFAULT 0 COMMENT '是否属于精品并在首页显示:1代表是,0代表否,默认是0。',
  `show_recommend` tinyint(1) NULL DEFAULT 0 COMMENT '是否推荐在首页显示:1代表是,0代表否,默认是0。',
  `sort` int NULL DEFAULT 100 COMMENT '综合排序:整数,值越大，排序越靠前。',
  `created_at` int NULL DEFAULT NULL COMMENT '数据被创建的时间',
  `updated_at` int NULL DEFAULT NULL COMMENT '数据被修改的时间',
  `created_by` mediumint NULL DEFAULT NULL COMMENT '创建者',
  `updated_by` mediumint NULL DEFAULT NULL COMMENT '更新者',
  `classification` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '产品类型/分类',
  `application` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '产品应用领域',
  `cagr` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '6年复合年均增长率',
  `last_scale` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '去年规模',
  `current_scale` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '当前规模',
  `future_scale` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '未来规模',
  `year` smallint NULL DEFAULT 0 COMMENT '数据年份',
  `third_sync_id` int NULL DEFAULT NULL COMMENT '第三方同步id',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `status`(`status`) USING BTREE,
  INDEX `category_id`(`category_id`) USING BTREE,
  INDEX `show_recommend`(`show_recommend`) USING BTREE,
  INDEX `show_hot`(`show_hot`) USING BTREE,
  INDEX `keywords`(`keywords`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1929329 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for product_upload_log
-- ----------------------------
CREATE TABLE IF NOT EXISTS `product_upload_log`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增主键ID',
  `file` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '文件名称',
  `count` int NULL DEFAULT 0 COMMENT '上传条数',
  `insert_count` int NULL DEFAULT 0 COMMENT '成功条数',
  `update_count` int NULL DEFAULT 0 COMMENT '更新条数',
  `error_count` int NULL DEFAULT 0 COMMENT '错误条数',
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '详情',
  `created_at` int NOT NULL COMMENT '创建时间',
  `created_by` mediumint NOT NULL COMMENT '创建者',
  `updated_at` int NOT NULL COMMENT '更新时间',
  `updated_by` mediumint NOT NULL DEFAULT 0,
  `state` tinyint(1) NOT NULL DEFAULT 0 COMMENT '记录任务状态',
  `sort` smallint NOT NULL DEFAULT 100 COMMENT '排序',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for qualifications
-- ----------------------------
CREATE TABLE IF NOT EXISTS `qualifications`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '名称',
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '图片',
  `thumbnail` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '缩略图',
  `sort` int NULL DEFAULT NULL COMMENT '排序',
  `status` tinyint NOT NULL COMMENT '状态',
  `created_by` mediumint NULL DEFAULT 0 COMMENT '创建者',
  `created_at` int NULL DEFAULT 0 COMMENT '创建时间',
  `updated_by` mediumint NULL DEFAULT 0 COMMENT '更新者',
  `updated_at` int NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 12 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '资质认证' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for quote_categorys
-- ----------------------------
CREATE TABLE IF NOT EXISTS `quote_categorys`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '名称',
  `sort` int NULL DEFAULT NULL COMMENT '排序',
  `status` tinyint(1) NULL DEFAULT 0 COMMENT '状态',
  `created_by` mediumint NULL DEFAULT 0 COMMENT '创建者',
  `updated_by` mediumint NULL DEFAULT 0 COMMENT '更新者',
  `created_at` int NULL DEFAULT 0 COMMENT '创建时间',
  `updated_at` int NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '权威引用分类' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for regions
-- ----------------------------
CREATE TABLE IF NOT EXISTS `regions`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '地区名称',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '地区状态',
  `sort` smallint NULL DEFAULT 100 COMMENT '排序；默认为100；越小值越优先显示',
  `created_at` int NOT NULL COMMENT '创建时间',
  `created_by` mediumint NOT NULL COMMENT '创建者',
  `updated_at` int NULL DEFAULT 0 COMMENT '更新时间',
  `updated_by` mediumint NULL DEFAULT 0 COMMENT '更新者',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 58 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for search_ranks
-- ----------------------------
CREATE TABLE IF NOT EXISTS `search_ranks`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '搜索词',
  `hits` int UNSIGNED NULL DEFAULT 0 COMMENT '搜索次数',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态',
  `sort` tinyint(1) NOT NULL DEFAULT 100 COMMENT '排序；默认为100；越小值越优先显示',
  `created_at` int NOT NULL COMMENT '创建时间',
  `created_by` mediumint NOT NULL COMMENT '创建者',
  `updated_at` int NULL DEFAULT 0 COMMENT '更新时间',
  `updated_by` mediumint NULL DEFAULT 0 COMMENT '更新者',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 19 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sensitive_words
-- ----------------------------
CREATE TABLE IF NOT EXISTS `sensitive_words`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `word` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `sort` int NOT NULL DEFAULT 0 COMMENT '排序',
  `updated_at` int NULL DEFAULT NULL COMMENT '更新时间',
  `created_at` int NOT NULL COMMENT '创建时间',
  `updated_by` int NULL DEFAULT NULL COMMENT '更新者',
  `created_by` int NOT NULL COMMENT '创建者',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `word`(`word`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 34 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_bin ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for shop_carts
-- ----------------------------
CREATE TABLE IF NOT EXISTS `shop_carts`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '购物车表的自增编号',
  `user_id` int NULL DEFAULT NULL COMMENT '购物者id',
  `goods_id` int NOT NULL COMMENT '产品编号',
  `number` tinyint NOT NULL COMMENT '商品数量',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态,1代表有效，0代表无效',
  `created_at` int NOT NULL COMMENT '创建时间',
  `updated_at` int NULL DEFAULT NULL COMMENT '更新时间',
  `price_edition` mediumint NOT NULL COMMENT '价格版本',
  `created_by` mediumint NULL DEFAULT NULL COMMENT '数据创建者ID',
  `updated_by` mediumint NULL DEFAULT NULL COMMENT '数据修改者ID',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 20 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sync_field
-- ----------------------------
CREATE TABLE IF NOT EXISTS `sync_field`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '对方网站的字段',
  `as_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '我方网站的字段',
  `type` tinyint NULL DEFAULT NULL COMMENT '字段类型，int、string、date等',
  `order` int NULL DEFAULT 100 COMMENT '排序',
  `description` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '字段描述/字段中文名',
  `status` tinyint(1) NULL DEFAULT 1,
  `is_required` tinyint(1) NULL DEFAULT 0 COMMENT '是否必填',
  `sort` int NOT NULL DEFAULT 100 COMMENT '排序',
  `table` tinyint(1) NULL DEFAULT 0 COMMENT '属于表',
  `created_at` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `created_by` int NOT NULL DEFAULT 0 COMMENT '创建者',
  `updated_at` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  `updated_by` int NOT NULL DEFAULT 0 COMMENT '更新者',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 31 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sync_log
-- ----------------------------
CREATE TABLE IF NOT EXISTS `sync_log`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `count` int NULL DEFAULT 0 COMMENT '总数',
  `ingore_count` int NULL DEFAULT 0 COMMENT '忽略总数',
  `insert_count` int NULL DEFAULT 0 COMMENT '新增数量',
  `update_count` int NULL DEFAULT 0 COMMENT '更新数量',
  `error_count` int NULL DEFAULT 0 COMMENT '错误数量',
  `created_at` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `updated_at` int NOT NULL DEFAULT 0 COMMENT '最后修改时间(完成时间)',
  `ingore_detail` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `update_detail` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `insert_detail` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 106 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sync_publisher
-- ----------------------------
CREATE TABLE IF NOT EXISTS `sync_publisher`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `publisher_id` int NULL DEFAULT 0 COMMENT '出版商id',
  `site_publisher_code` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '站点端出版商code',
  `third_publisher_code` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '第三方出版商code',
  `created_at` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `created_by` int NOT NULL DEFAULT 0 COMMENT '创建者',
  `updated_at` int NOT NULL DEFAULT 0 COMMENT '修改时间',
  `updated_by` int NOT NULL DEFAULT 0 COMMENT '修改者',
  `sort` int NOT NULL DEFAULT 100 COMMENT '排序',
  `status` int NOT NULL DEFAULT 1 COMMENT '状态',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for system_values
-- ----------------------------
CREATE TABLE IF NOT EXISTS `system_values`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `parent_id` int NOT NULL COMMENT '父级ID',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '名称',
  `english_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '英文名称',
  `alias` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '组别名',
  `key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '键名',
  `value` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '内容',
  `type` tinyint NOT NULL DEFAULT 1 COMMENT '类型：1文本 2开关 3下拉框 4图片 5文件',
  `status` tinyint(1) NULL DEFAULT 1 COMMENT '状态：0禁用 1正常',
  `switch` tinyint(1) NULL DEFAULT 1 COMMENT '开关：0隐藏 1显示',
  `hidden` tinyint(1) NULL DEFAULT 1 COMMENT '列表显示：0隐藏 1显示',
  `sort` int NULL DEFAULT 0 COMMENT '排序',
  `created_at` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `created_by` mediumint NOT NULL DEFAULT 0 COMMENT '创建者',
  `updated_at` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  `updated_by` mediumint NOT NULL DEFAULT 0 COMMENT '更新者',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `parent_id`(`parent_id`) USING BTREE COMMENT '父级普通索引'
) ENGINE = InnoDB AUTO_INCREMENT = 98 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '系统配置子级值' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for systems
-- ----------------------------
CREATE TABLE IF NOT EXISTS `systems`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Tab名称',
  `english_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '英文名称',
  `alias` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '别名',
  `sort` int NULL DEFAULT 0 COMMENT '排序',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态 0禁用 1正常',
  `created_by` mediumint NOT NULL DEFAULT 0 COMMENT '创建者',
  `updated_by` mediumint NULL DEFAULT 0 COMMENT '更新者',
  `created_at` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `updated_at` int NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 46 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '系统配置Tab切页' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for team_members
-- ----------------------------
CREATE TABLE IF NOT EXISTS `team_members`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '名称',
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '图片',
  `position` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '职位',
  `status` tinyint NOT NULL DEFAULT 0 COMMENT '状态',
  `sort` int NULL DEFAULT 0 COMMENT '排序',
  `describe` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '个人描述',
  `area` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '研究领域',
  `experience` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '项目经历',
  `custom` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '合作客户',
  `industry_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '研究行业',
  `is_analyst` tinyint(1) NULL DEFAULT 0 COMMENT '是否分析师',
  `created_by` mediumint NULL DEFAULT 0 COMMENT '创建者',
  `created_at` int NULL DEFAULT 0 COMMENT '创建时间',
  `updated_by` mediumint NULL DEFAULT 0 COMMENT '更新者',
  `updated_at` int NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 16 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for template
-- ----------------------------
CREATE TABLE IF NOT EXISTS `template`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '模板昵称',
  `type` tinyint(1) NULL DEFAULT 0 COMMENT '模版类型',
  `btn_color` int NOT NULL DEFAULT 0 COMMENT '按钮颜色',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '模板内容',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态',
  `sort` smallint NOT NULL DEFAULT 0 COMMENT '排序',
  `created_by` smallint NOT NULL COMMENT '创建者',
  `created_at` int NOT NULL COMMENT '创建时间',
  `updated_by` smallint NULL DEFAULT NULL COMMENT '修改者',
  `updated_at` int NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 27 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for template_cate_mapping
-- ----------------------------
CREATE TABLE IF NOT EXISTS `template_cate_mapping`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `cate_id` int NOT NULL COMMENT '模版分类id',
  `temp_id` int NOT NULL COMMENT '模板id',
  `created_by` smallint NOT NULL DEFAULT 0 COMMENT '创建者',
  `created_at` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `updated_by` smallint NULL DEFAULT NULL COMMENT '修改者',
  `updated_at` int NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 42 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for template_category
-- ----------------------------
CREATE TABLE IF NOT EXISTS `template_category`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '分类昵称',
  `match_words` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '匹配词',
  `sort` smallint NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态',
  `created_by` smallint NOT NULL COMMENT '创建者',
  `created_at` int NOT NULL COMMENT '创建时间',
  `updated_by` smallint NULL DEFAULT NULL COMMENT '修改者',
  `updated_at` int NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 14 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for url_filter_edition
-- ----------------------------
CREATE TABLE IF NOT EXISTS `url_filter_edition`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '过滤字符',
  `sort` tinyint NOT NULL DEFAULT 100 COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态：1-启用，0-禁用；默认为1',
  `created_by` int NOT NULL COMMENT '创建者',
  `created_at` int NOT NULL COMMENT '创建时间',
  `updated_by` int NULL DEFAULT NULL COMMENT '修改者',
  `updated_at` int NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 35 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for user_address
-- ----------------------------
CREATE TABLE IF NOT EXISTS `user_address`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '用户收货地址表的主键',
  `user_id` int NOT NULL COMMENT '用户编号',
  `sort` tinyint NOT NULL DEFAULT 0 COMMENT '排序',
  `is_default` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1为默认地址,每个用户只能有一个默认地址。',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态1显示',
  `consignee` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '收货人',
  `contact_number` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '联系电话',
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '邮箱地址',
  `country_id` smallint NULL DEFAULT NULL COMMENT '国家ID',
  `province_id` smallint NULL DEFAULT NULL COMMENT '省份ID',
  `city_id` smallint NULL DEFAULT NULL COMMENT '城市ID',
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '详细地址',
  `created_at` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `created_by` int NOT NULL DEFAULT 0 COMMENT '创建者',
  `updated_at` int NOT NULL DEFAULT 0 COMMENT '修改时间',
  `updated_by` int NOT NULL DEFAULT 0 COMMENT '修改者',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 41 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for users
-- ----------------------------
CREATE TABLE IF NOT EXISTS `users`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '名称',
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '登陆名',
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '邮箱',
  `phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '电话号码',
  `city_id` int NULL DEFAULT 0 COMMENT '地区ID',
  `province_id` int NULL DEFAULT 0 COMMENT '城市ID',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态',
  `company` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '公司',
  `login_time` int NULL DEFAULT NULL COMMENT '登陆时间',
  `check_email` tinyint(1) NULL DEFAULT 0 COMMENT '邮箱验证',
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '哈希密码',
  `sort` int NULL DEFAULT NULL COMMENT '排序',
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '地址',
  `updated_at` int NULL DEFAULT 0 COMMENT '更新时间',
  `updated_by` mediumint NULL DEFAULT 0 COMMENT '更新者',
  `created_at` int NULL DEFAULT 0 COMMENT '创建时间',
  `created_by` mediumint NULL DEFAULT 0 COMMENT '创建者',
  `token` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `country_id` smallint NULL DEFAULT NULL COMMENT '所属地区ID',
  `area_id` smallint NOT NULL DEFAULT 0 COMMENT '国家id',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `email`(`email`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 193 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for videos
-- ----------------------------
CREATE TABLE IF NOT EXISTS `videos`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '标题',
  `content` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '视频',
  `status` int NULL DEFAULT 0 COMMENT '状态',
  `sort` int NULL DEFAULT 0 COMMENT '排序',
  `created_by` int NULL DEFAULT 0 COMMENT '创建者',
  `created_at` int NULL DEFAULT 0 COMMENT '创建时间',
  `updated_by` int NULL DEFAULT 0 COMMENT '更新者',
  `updated_at` int NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '视频列表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for view_product_export_log
-- ----------------------------
CREATE TABLE IF NOT EXISTS `view_product_export_log`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增主键ID',
  `file` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '最终文件名称/路径',
  `count` smallint NULL DEFAULT 0 COMMENT '导出总条数',
  `success_count` smallint NULL DEFAULT 0 COMMENT '导出成功条数',
  `error_count` smallint NULL DEFAULT 0 COMMENT '导出失败条数',
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '详情',
  `created_at` int NOT NULL COMMENT '创建时间',
  `created_by` mediumint NOT NULL COMMENT '创建者',
  `updated_at` int NOT NULL COMMENT '更新时间',
  `state` tinyint(1) NOT NULL DEFAULT 0 COMMENT '记录任务状态',
  `sort` smallint NOT NULL DEFAULT 100 COMMENT '排序',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for view_products_log
-- ----------------------------
CREATE TABLE IF NOT EXISTS `view_products_log`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '用户-优惠券对应关系表的主键',
  `user_id` int NOT NULL DEFAULT 0 COMMENT '用户ID',
  `product_id` int NOT NULL COMMENT '报告ID',
  `ip` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ip地址',
  `ip_addr` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ip所在地',
  `product_name` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '报告昵称',
  `keyword` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '关键词',
  `view_cnt` int NOT NULL DEFAULT 0 COMMENT '浏览次数',
  `status` smallint NOT NULL DEFAULT 1 COMMENT '状态 1:正常',
  `sort` smallint NULL DEFAULT 100 COMMENT '排序',
  `view_date_str` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '浏览时间(年月日)',
  `created_by` mediumint NULL DEFAULT 0 COMMENT '数据创建者ID',
  `created_at` int NULL DEFAULT 0 COMMENT '数据创建时（时间戳）',
  `updated_by` mediumint NULL DEFAULT 0 COMMENT '数据修改者ID',
  `updated_at` int NULL DEFAULT 0 COMMENT '数据修改时（时间戳）',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 404781 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;


-- ----------------------------
-- Records of citys
-- ----------------------------
INSERT INTO `citys` VALUES (1, 44, 0, '中国', 0, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2, 44, 1, '北京', 1, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3, 44, 1, '安徽', 1, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (4, 44, 1, '福建', 1, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (5, 44, 1, '甘肃', 1, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (6, 44, 1, '广东', 1, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (7, 44, 1, '广西', 1, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (8, 44, 1, '贵州', 1, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (9, 44, 1, '海南', 1, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (10, 44, 1, '河北', 1, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (11, 44, 1, '河南', 1, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (12, 44, 1, '黑龙江', 1, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (13, 44, 1, '湖北', 1, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (14, 44, 1, '湖南', 1, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (15, 44, 1, '吉林', 1, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (16, 44, 1, '江苏', 1, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (17, 44, 1, '江西', 1, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (18, 44, 1, '辽宁', 1, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (19, 44, 1, '内蒙古', 1, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (20, 44, 1, '宁夏', 1, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (21, 44, 1, '青海', 1, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (22, 44, 1, '山东', 1, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (23, 44, 1, '山西', 1, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (24, 44, 1, '陕西', 1, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (25, 44, 1, '上海', 1, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (26, 44, 1, '四川', 1, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (27, 44, 1, '天津', 1, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (28, 44, 1, '西藏', 1, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (29, 44, 1, '新疆', 1, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (30, 44, 1, '云南', 1, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (31, 44, 1, '浙江', 1, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (32, 44, 1, '重庆', 1, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (33, 44, 1, '香港', 1, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (34, 44, 1, '澳门', 1, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (35, 44, 1, '台湾', 1, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (36, 44, 3, '安庆', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (37, 44, 3, '蚌埠', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (38, 44, 3, '巢湖', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (39, 44, 3, '池州', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (40, 44, 3, '滁州', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (41, 44, 3, '阜阳', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (42, 44, 3, '淮北', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (43, 44, 3, '淮南', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (44, 44, 3, '黄山', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (45, 44, 3, '六安', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (46, 44, 3, '马鞍山', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (47, 44, 3, '宿州', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (48, 44, 3, '铜陵', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (49, 44, 3, '芜湖', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (50, 44, 3, '宣城', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (51, 44, 3, '亳州', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (52, 44, 2, '市辖区', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (53, 44, 4, '福州', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (54, 44, 4, '龙岩', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (55, 44, 4, '南平', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (56, 44, 4, '宁德', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (57, 44, 4, '莆田', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (58, 44, 4, '泉州', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (59, 44, 4, '三明', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (60, 44, 4, '厦门', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (61, 44, 4, '漳州', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (62, 44, 5, '兰州', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (63, 44, 5, '白银', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (64, 44, 5, '定西', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (65, 44, 5, '甘南', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (66, 44, 5, '嘉峪关', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (67, 44, 5, '金昌', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (68, 44, 5, '酒泉', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (69, 44, 5, '临夏', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (70, 44, 5, '陇南', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (71, 44, 5, '平凉', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (72, 44, 5, '庆阳', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (73, 44, 5, '天水', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (74, 44, 5, '武威', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (75, 44, 5, '张掖', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (76, 44, 6, '广州', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (77, 44, 6, '深圳', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (78, 44, 6, '潮州', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (79, 44, 6, '东莞', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (80, 44, 6, '佛山', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (81, 44, 6, '河源', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (82, 44, 6, '惠州', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (83, 44, 6, '江门', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (84, 44, 6, '揭阳', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (85, 44, 6, '茂名', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (86, 44, 6, '梅州', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (87, 44, 6, '清远', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (88, 44, 6, '汕头', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (89, 44, 6, '汕尾', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (90, 44, 6, '韶关', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (91, 44, 6, '阳江', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (92, 44, 6, '云浮', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (93, 44, 6, '湛江', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (94, 44, 6, '肇庆', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (95, 44, 6, '中山', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (96, 44, 6, '珠海', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (97, 44, 7, '南宁', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (98, 44, 7, '桂林', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (99, 44, 7, '百色', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (100, 44, 7, '北海', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (101, 44, 7, '崇左', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (102, 44, 7, '防城港', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (103, 44, 7, '贵港', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (104, 44, 7, '河池', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (105, 44, 7, '贺州', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (106, 44, 7, '来宾', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (107, 44, 7, '柳州', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (108, 44, 7, '钦州', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (109, 44, 7, '梧州', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (110, 44, 7, '玉林', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (111, 44, 8, '贵阳', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (112, 44, 8, '安顺', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (113, 44, 8, '毕节', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (114, 44, 8, '六盘水', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (115, 44, 8, '黔东南', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (116, 44, 8, '黔南', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (117, 44, 8, '黔西南', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (118, 44, 8, '铜仁', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (119, 44, 8, '遵义', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (120, 44, 9, '海口', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (121, 44, 9, '三亚', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (122, 44, 9, '白沙', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (123, 44, 9, '保亭', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (124, 44, 9, '昌江', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (125, 44, 9, '澄迈县', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (126, 44, 9, '定安县', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (127, 44, 9, '东方', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (128, 44, 9, '乐东', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (129, 44, 9, '临高县', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (130, 44, 9, '陵水', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (131, 44, 9, '琼海', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (132, 44, 9, '琼中', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (133, 44, 9, '屯昌县', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (134, 44, 9, '万宁', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (135, 44, 9, '文昌', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (136, 44, 9, '五指山', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (137, 44, 9, '儋州', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (138, 44, 10, '石家庄', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (139, 44, 10, '保定', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (140, 44, 10, '沧州', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (141, 44, 10, '承德', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (142, 44, 10, '邯郸', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (143, 44, 10, '衡水', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (144, 44, 10, '廊坊', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (145, 44, 10, '秦皇岛', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (146, 44, 10, '唐山', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (147, 44, 10, '邢台', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (148, 44, 10, '张家口', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (149, 44, 11, '郑州', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (150, 44, 11, '洛阳', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (151, 44, 11, '开封', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (152, 44, 11, '安阳', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (153, 44, 11, '鹤壁', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (154, 44, 11, '济源', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (155, 44, 11, '焦作', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (156, 44, 11, '南阳', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (157, 44, 11, '平顶山', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (158, 44, 11, '三门峡', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (159, 44, 11, '商丘', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (160, 44, 11, '新乡', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (161, 44, 11, '信阳', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (162, 44, 11, '许昌', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (163, 44, 11, '周口', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (164, 44, 11, '驻马店', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (165, 44, 11, '漯河', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (166, 44, 11, '濮阳', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (167, 44, 12, '哈尔滨', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (168, 44, 12, '大庆', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (169, 44, 12, '大兴安岭', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (170, 44, 12, '鹤岗', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (171, 44, 12, '黑河', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (172, 44, 12, '鸡西', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (173, 44, 12, '佳木斯', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (174, 44, 12, '牡丹江', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (175, 44, 12, '七台河', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (176, 44, 12, '齐齐哈尔', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (177, 44, 12, '双鸭山', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (178, 44, 12, '绥化', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (179, 44, 12, '伊春', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (180, 44, 13, '武汉', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (181, 44, 13, '仙桃', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (182, 44, 13, '鄂州', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (183, 44, 13, '黄冈', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (184, 44, 13, '黄石', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (185, 44, 13, '荆门', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (186, 44, 13, '荆州', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (187, 44, 13, '潜江', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (188, 44, 13, '神农架林区', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (189, 44, 13, '十堰', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (190, 44, 13, '随州', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (191, 44, 13, '天门', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (192, 44, 13, '咸宁', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (193, 44, 13, '襄樊', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (194, 44, 13, '孝感', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (195, 44, 13, '宜昌', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (196, 44, 13, '恩施', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (197, 44, 14, '长沙', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (198, 44, 14, '张家界', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (199, 44, 14, '常德', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (200, 44, 14, '郴州', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (201, 44, 14, '衡阳', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (202, 44, 14, '怀化', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (203, 44, 14, '娄底', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (204, 44, 14, '邵阳', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (205, 44, 14, '湘潭', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (206, 44, 14, '湘西', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (207, 44, 14, '益阳', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (208, 44, 14, '永州', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (209, 44, 14, '岳阳', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (210, 44, 14, '株洲', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (211, 44, 15, '长春', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (212, 44, 15, '吉林', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (213, 44, 15, '白城', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (214, 44, 15, '白山', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (215, 44, 15, '辽源', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (216, 44, 15, '四平', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (217, 44, 15, '松原', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (218, 44, 15, '通化', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (219, 44, 15, '延边', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (220, 44, 16, '南京', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (221, 44, 16, '苏州', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (222, 44, 16, '无锡', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (223, 44, 16, '常州', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (224, 44, 16, '淮安', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (225, 44, 16, '连云港', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (226, 44, 16, '南通', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (227, 44, 16, '宿迁', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (228, 44, 16, '泰州', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (229, 44, 16, '徐州', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (230, 44, 16, '盐城', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (231, 44, 16, '扬州', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (232, 44, 16, '镇江', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (233, 44, 17, '南昌', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (234, 44, 17, '抚州', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (235, 44, 17, '赣州', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (236, 44, 17, '吉安', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (237, 44, 17, '景德镇', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (238, 44, 17, '九江', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (239, 44, 17, '萍乡', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (240, 44, 17, '上饶', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (241, 44, 17, '新余', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (242, 44, 17, '宜春', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (243, 44, 17, '鹰潭', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (244, 44, 18, '沈阳', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (245, 44, 18, '大连', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (246, 44, 18, '鞍山', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (247, 44, 18, '本溪', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (248, 44, 18, '朝阳', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (249, 44, 18, '丹东', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (250, 44, 18, '抚顺', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (251, 44, 18, '阜新', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (252, 44, 18, '葫芦岛', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (253, 44, 18, '锦州', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (254, 44, 18, '辽阳', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (255, 44, 18, '盘锦', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (256, 44, 18, '铁岭', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (257, 44, 18, '营口', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (258, 44, 19, '呼和浩特', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (259, 44, 19, '阿拉善盟', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (260, 44, 19, '巴彦淖尔盟', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (261, 44, 19, '包头', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (262, 44, 19, '赤峰', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (263, 44, 19, '鄂尔多斯', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (264, 44, 19, '呼伦贝尔', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (265, 44, 19, '通辽', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (266, 44, 19, '乌海', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (267, 44, 19, '乌兰察布市', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (268, 44, 19, '锡林郭勒盟', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (269, 44, 19, '兴安盟', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (270, 44, 20, '银川', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (271, 44, 20, '固原', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (272, 44, 20, '石嘴山', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (273, 44, 20, '吴忠', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (274, 44, 20, '中卫', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (275, 44, 21, '西宁', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (276, 44, 21, '果洛', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (277, 44, 21, '海北', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (278, 44, 21, '海东', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (279, 44, 21, '海南', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (280, 44, 21, '海西', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (281, 44, 21, '黄南', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (282, 44, 21, '玉树', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (283, 44, 22, '济南', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (284, 44, 22, '青岛', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (285, 44, 22, '滨州', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (286, 44, 22, '德州', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (287, 44, 22, '东营', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (288, 44, 22, '菏泽', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (289, 44, 22, '济宁', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (290, 44, 22, '莱芜', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (291, 44, 22, '聊城', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (292, 44, 22, '临沂', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (293, 44, 22, '日照', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (294, 44, 22, '泰安', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (295, 44, 22, '威海', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (296, 44, 22, '潍坊', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (297, 44, 22, '烟台', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (298, 44, 22, '枣庄', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (299, 44, 22, '淄博', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (300, 44, 23, '太原', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (301, 44, 23, '长治', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (302, 44, 23, '大同', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (303, 44, 23, '晋城', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (304, 44, 23, '晋中', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (305, 44, 23, '临汾', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (306, 44, 23, '吕梁', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (307, 44, 23, '朔州', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (308, 44, 23, '忻州', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (309, 44, 23, '阳泉', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (310, 44, 23, '运城', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (311, 44, 24, '西安', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (312, 44, 24, '安康', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (313, 44, 24, '宝鸡', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (314, 44, 24, '汉中', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (315, 44, 24, '商洛', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (316, 44, 24, '铜川', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (317, 44, 24, '渭南', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (318, 44, 24, '咸阳', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (319, 44, 24, '延安', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (320, 44, 24, '榆林', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (321, 44, 25, '上海', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (322, 44, 26, '成都', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (323, 44, 26, '绵阳', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (324, 44, 26, '阿坝', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (325, 44, 26, '巴中', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (326, 44, 26, '达州', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (327, 44, 26, '德阳', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (328, 44, 26, '甘孜', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (329, 44, 26, '广安', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (330, 44, 26, '广元', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (331, 44, 26, '乐山', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (332, 44, 26, '凉山', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (333, 44, 26, '眉山', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (334, 44, 26, '南充', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (335, 44, 26, '内江', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (336, 44, 26, '攀枝花', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (337, 44, 26, '遂宁', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (338, 44, 26, '雅安', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (339, 44, 26, '宜宾', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (340, 44, 26, '资阳', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (341, 44, 26, '自贡', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (342, 44, 26, '泸州', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (343, 44, 27, '天津', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (344, 44, 28, '拉萨', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (345, 44, 28, '阿里', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (346, 44, 28, '昌都', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (347, 44, 28, '林芝', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (348, 44, 28, '那曲', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (349, 44, 28, '日喀则', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (350, 44, 28, '山南', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (351, 44, 29, '乌鲁木齐', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (352, 44, 29, '阿克苏', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (353, 44, 29, '阿拉尔', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (354, 44, 29, '巴音郭楞', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (355, 44, 29, '博尔塔拉', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (356, 44, 29, '昌吉', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (357, 44, 29, '哈密', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (358, 44, 29, '和田', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (359, 44, 29, '喀什', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (360, 44, 29, '克拉玛依', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (361, 44, 29, '克孜勒苏', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (362, 44, 29, '石河子', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (363, 44, 29, '图木舒克', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (364, 44, 29, '吐鲁番', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (365, 44, 29, '五家渠', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (366, 44, 29, '伊犁', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (367, 44, 30, '昆明', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (368, 44, 30, '怒江', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (369, 44, 30, '普洱', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (370, 44, 30, '丽江', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (371, 44, 30, '保山', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (372, 44, 30, '楚雄', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (373, 44, 30, '大理', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (374, 44, 30, '德宏', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (375, 44, 30, '迪庆', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (376, 44, 30, '红河', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (377, 44, 30, '临沧', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (378, 44, 30, '曲靖', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (379, 44, 30, '文山', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (380, 44, 30, '西双版纳', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (381, 44, 30, '玉溪', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (382, 44, 30, '昭通', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (383, 44, 31, '杭州', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (384, 44, 31, '湖州', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (385, 44, 31, '嘉兴', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (386, 44, 31, '金华', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (387, 44, 31, '丽水', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (388, 44, 31, '宁波', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (389, 44, 31, '绍兴', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (390, 44, 31, '台州', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (391, 44, 31, '温州', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (392, 44, 31, '舟山', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (393, 44, 31, '衢州', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (394, 44, 32, '重庆', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (395, 44, 33, '香港', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (396, 44, 34, '澳门', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (397, 44, 35, '台湾', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (398, 44, 36, '迎江区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (399, 44, 36, '大观区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (400, 44, 36, '宜秀区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (401, 44, 36, '桐城市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (402, 44, 36, '怀宁县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (403, 44, 36, '枞阳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (404, 44, 36, '潜山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (405, 44, 36, '太湖县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (406, 44, 36, '宿松县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (407, 44, 36, '望江县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (408, 44, 36, '岳西县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (409, 44, 37, '中市区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (410, 44, 37, '东市区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (411, 44, 37, '西市区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (412, 44, 37, '郊区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (413, 44, 37, '怀远县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (414, 44, 37, '五河县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (415, 44, 37, '固镇县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (416, 44, 38, '居巢区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (417, 44, 38, '庐江县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (418, 44, 38, '无为县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (419, 44, 38, '含山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (420, 44, 38, '和县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (421, 44, 39, '贵池区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (422, 44, 39, '东至县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (423, 44, 39, '石台县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (424, 44, 39, '青阳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (425, 44, 40, '琅琊区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (426, 44, 40, '南谯区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (427, 44, 40, '天长市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (428, 44, 40, '明光市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (429, 44, 40, '来安县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (430, 44, 40, '全椒县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (431, 44, 40, '定远县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (432, 44, 40, '凤阳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (433, 44, 41, '蚌山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (434, 44, 41, '龙子湖区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (435, 44, 41, '禹会区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (436, 44, 41, '淮上区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (437, 44, 41, '颍州区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (438, 44, 41, '颍东区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (439, 44, 41, '颍泉区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (440, 44, 41, '界首市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (441, 44, 41, '临泉县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (442, 44, 41, '太和县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (443, 44, 41, '阜南县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (444, 44, 41, '颖上县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (445, 44, 42, '相山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (446, 44, 42, '杜集区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (447, 44, 42, '烈山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (448, 44, 42, '濉溪县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (449, 44, 43, '田家庵区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (450, 44, 43, '大通区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (451, 44, 43, '谢家集区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (452, 44, 43, '八公山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (453, 44, 43, '潘集区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (454, 44, 43, '凤台县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (455, 44, 44, '屯溪区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (456, 44, 44, '黄山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (457, 44, 44, '徽州区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (458, 44, 44, '歙县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (459, 44, 44, '休宁县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (460, 44, 44, '黟县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (461, 44, 44, '祁门县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (462, 44, 45, '金安区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (463, 44, 45, '裕安区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (464, 44, 45, '寿县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (465, 44, 45, '霍邱县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (466, 44, 45, '舒城县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (467, 44, 45, '金寨县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (468, 44, 45, '霍山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (469, 44, 46, '雨山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (470, 44, 46, '花山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (471, 44, 46, '金家庄区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (472, 44, 46, '当涂县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (473, 44, 47, '埇桥区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (474, 44, 47, '砀山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (475, 44, 47, '萧县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (476, 44, 47, '灵璧县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (477, 44, 47, '泗县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (478, 44, 48, '铜官山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (479, 44, 48, '狮子山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (480, 44, 48, '郊区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (481, 44, 48, '铜陵县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (482, 44, 49, '镜湖区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (483, 44, 49, '弋江区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (484, 44, 49, '鸠江区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (485, 44, 49, '三山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (486, 44, 49, '芜湖县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (487, 44, 49, '繁昌县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (488, 44, 49, '南陵县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (489, 44, 50, '宣州区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (490, 44, 50, '宁国市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (491, 44, 50, '郎溪县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (492, 44, 50, '广德县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (493, 44, 50, '泾县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (494, 44, 50, '绩溪县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (495, 44, 50, '旌德县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (496, 44, 51, '涡阳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (497, 44, 51, '蒙城县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (498, 44, 51, '利辛县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (499, 44, 51, '谯城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (500, 44, 52, '东城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (501, 44, 52, '西城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (502, 44, 52, '海淀区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (503, 44, 52, '朝阳区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (504, 44, 52, '崇文区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (505, 44, 52, '宣武区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (506, 44, 52, '丰台区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (507, 44, 52, '石景山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (508, 44, 52, '房山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (509, 44, 52, '门头沟区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (510, 44, 52, '通州区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (511, 44, 52, '顺义区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (512, 44, 52, '昌平区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (513, 44, 52, '怀柔区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (514, 44, 52, '平谷区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (515, 44, 52, '大兴区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (516, 44, 52, '密云县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (517, 44, 52, '延庆县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (518, 44, 53, '鼓楼区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (519, 44, 53, '台江区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (520, 44, 53, '仓山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (521, 44, 53, '马尾区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (522, 44, 53, '晋安区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (523, 44, 53, '福清市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (524, 44, 53, '长乐市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (525, 44, 53, '闽侯县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (526, 44, 53, '连江县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (527, 44, 53, '罗源县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (528, 44, 53, '闽清县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (529, 44, 53, '永泰县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (530, 44, 53, '平潭县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (531, 44, 54, '新罗区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (532, 44, 54, '漳平市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (533, 44, 54, '长汀县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (534, 44, 54, '永定县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (535, 44, 54, '上杭县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (536, 44, 54, '武平县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (537, 44, 54, '连城县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (538, 44, 55, '延平区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (539, 44, 55, '邵武市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (540, 44, 55, '武夷山市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (541, 44, 55, '建瓯市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (542, 44, 55, '建阳市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (543, 44, 55, '顺昌县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (544, 44, 55, '浦城县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (545, 44, 55, '光泽县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (546, 44, 55, '松溪县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (547, 44, 55, '政和县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (548, 44, 56, '蕉城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (549, 44, 56, '福安市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (550, 44, 56, '福鼎市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (551, 44, 56, '霞浦县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (552, 44, 56, '古田县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (553, 44, 56, '屏南县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (554, 44, 56, '寿宁县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (555, 44, 56, '周宁县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (556, 44, 56, '柘荣县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (557, 44, 57, '城厢区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (558, 44, 57, '涵江区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (559, 44, 57, '荔城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (560, 44, 57, '秀屿区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (561, 44, 57, '仙游县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (562, 44, 58, '鲤城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (563, 44, 58, '丰泽区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (564, 44, 58, '洛江区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (565, 44, 58, '清濛开发区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (566, 44, 58, '泉港区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (567, 44, 58, '石狮市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (568, 44, 58, '晋江市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (569, 44, 58, '南安市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (570, 44, 58, '惠安县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (571, 44, 58, '安溪县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (572, 44, 58, '永春县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (573, 44, 58, '德化县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (574, 44, 58, '金门县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (575, 44, 59, '梅列区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (576, 44, 59, '三元区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (577, 44, 59, '永安市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (578, 44, 59, '明溪县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (579, 44, 59, '清流县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (580, 44, 59, '宁化县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (581, 44, 59, '大田县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (582, 44, 59, '尤溪县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (583, 44, 59, '沙县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (584, 44, 59, '将乐县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (585, 44, 59, '泰宁县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (586, 44, 59, '建宁县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (587, 44, 60, '思明区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (588, 44, 60, '海沧区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (589, 44, 60, '湖里区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (590, 44, 60, '集美区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (591, 44, 60, '同安区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (592, 44, 60, '翔安区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (593, 44, 61, '芗城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (594, 44, 61, '龙文区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (595, 44, 61, '龙海市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (596, 44, 61, '云霄县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (597, 44, 61, '漳浦县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (598, 44, 61, '诏安县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (599, 44, 61, '长泰县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (600, 44, 61, '东山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (601, 44, 61, '南靖县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (602, 44, 61, '平和县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (603, 44, 61, '华安县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (604, 44, 62, '皋兰县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (605, 44, 62, '城关区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (606, 44, 62, '七里河区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (607, 44, 62, '西固区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (608, 44, 62, '安宁区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (609, 44, 62, '红古区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (610, 44, 62, '永登县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (611, 44, 62, '榆中县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (612, 44, 63, '白银区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (613, 44, 63, '平川区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (614, 44, 63, '会宁县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (615, 44, 63, '景泰县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (616, 44, 63, '靖远县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (617, 44, 64, '临洮县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (618, 44, 64, '陇西县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (619, 44, 64, '通渭县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (620, 44, 64, '渭源县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (621, 44, 64, '漳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (622, 44, 64, '岷县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (623, 44, 64, '安定区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (624, 44, 64, '安定区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (625, 44, 65, '合作市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (626, 44, 65, '临潭县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (627, 44, 65, '卓尼县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (628, 44, 65, '舟曲县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (629, 44, 65, '迭部县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (630, 44, 65, '玛曲县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (631, 44, 65, '碌曲县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (632, 44, 65, '夏河县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (633, 44, 66, '嘉峪关市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (634, 44, 67, '金川区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (635, 44, 67, '永昌县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (636, 44, 68, '肃州区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (637, 44, 68, '玉门市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (638, 44, 68, '敦煌市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (639, 44, 68, '金塔县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (640, 44, 68, '瓜州县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (641, 44, 68, '肃北', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (642, 44, 68, '阿克塞', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (643, 44, 69, '临夏市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (644, 44, 69, '临夏县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (645, 44, 69, '康乐县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (646, 44, 69, '永靖县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (647, 44, 69, '广河县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (648, 44, 69, '和政县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (649, 44, 69, '东乡族自治县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (650, 44, 69, '积石山', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (651, 44, 70, '成县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (652, 44, 70, '徽县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (653, 44, 70, '康县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (654, 44, 70, '礼县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (655, 44, 70, '两当县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (656, 44, 70, '文县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (657, 44, 70, '西和县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (658, 44, 70, '宕昌县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (659, 44, 70, '武都区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (660, 44, 71, '崇信县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (661, 44, 71, '华亭县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (662, 44, 71, '静宁县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (663, 44, 71, '灵台县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (664, 44, 71, '崆峒区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (665, 44, 71, '庄浪县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (666, 44, 71, '泾川县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (667, 44, 72, '合水县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (668, 44, 72, '华池县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (669, 44, 72, '环县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (670, 44, 72, '宁县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (671, 44, 72, '庆城县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (672, 44, 72, '西峰区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (673, 44, 72, '镇原县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (674, 44, 72, '正宁县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (675, 44, 73, '甘谷县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (676, 44, 73, '秦安县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (677, 44, 73, '清水县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (678, 44, 73, '秦州区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (679, 44, 73, '麦积区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (680, 44, 73, '武山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (681, 44, 73, '张家川', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (682, 44, 74, '古浪县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (683, 44, 74, '民勤县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (684, 44, 74, '天祝', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (685, 44, 74, '凉州区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (686, 44, 75, '高台县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (687, 44, 75, '临泽县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (688, 44, 75, '民乐县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (689, 44, 75, '山丹县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (690, 44, 75, '肃南', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (691, 44, 75, '甘州区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (692, 44, 76, '从化市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (693, 44, 76, '天河区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (694, 44, 76, '东山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (695, 44, 76, '白云区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (696, 44, 76, '海珠区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (697, 44, 76, '荔湾区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (698, 44, 76, '越秀区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (699, 44, 76, '黄埔区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (700, 44, 76, '番禺区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (701, 44, 76, '花都区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (702, 44, 76, '增城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (703, 44, 76, '从化区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (704, 44, 76, '市郊', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (705, 44, 77, '福田区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (706, 44, 77, '罗湖区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (707, 44, 77, '南山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (708, 44, 77, '宝安区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (709, 44, 77, '龙岗区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (710, 44, 77, '盐田区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (711, 44, 78, '湘桥区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (712, 44, 78, '潮安县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (713, 44, 78, '饶平县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (714, 44, 79, '南城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (715, 44, 79, '东城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (716, 44, 79, '万江区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (717, 44, 79, '莞城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (718, 44, 79, '石龙镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (719, 44, 79, '虎门镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (720, 44, 79, '麻涌镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (721, 44, 79, '道滘镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (722, 44, 79, '石碣镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (723, 44, 79, '沙田镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (724, 44, 79, '望牛墩镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (725, 44, 79, '洪梅镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (726, 44, 79, '茶山镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (727, 44, 79, '寮步镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (728, 44, 79, '大岭山镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (729, 44, 79, '大朗镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (730, 44, 79, '黄江镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (731, 44, 79, '樟木头', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (732, 44, 79, '凤岗镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (733, 44, 79, '塘厦镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (734, 44, 79, '谢岗镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (735, 44, 79, '厚街镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (736, 44, 79, '清溪镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (737, 44, 79, '常平镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (738, 44, 79, '桥头镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (739, 44, 79, '横沥镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (740, 44, 79, '东坑镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (741, 44, 79, '企石镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (742, 44, 79, '石排镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (743, 44, 79, '长安镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (744, 44, 79, '中堂镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (745, 44, 79, '高埗镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (746, 44, 80, '禅城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (747, 44, 80, '南海区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (748, 44, 80, '顺德区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (749, 44, 80, '三水区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (750, 44, 80, '高明区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (751, 44, 81, '东源县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (752, 44, 81, '和平县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (753, 44, 81, '源城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (754, 44, 81, '连平县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (755, 44, 81, '龙川县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (756, 44, 81, '紫金县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (757, 44, 82, '惠阳区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (758, 44, 82, '惠城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (759, 44, 82, '大亚湾', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (760, 44, 82, '博罗县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (761, 44, 82, '惠东县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (762, 44, 82, '龙门县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (763, 44, 83, '江海区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (764, 44, 83, '蓬江区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (765, 44, 83, '新会区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (766, 44, 83, '台山市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (767, 44, 83, '开平市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (768, 44, 83, '鹤山市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (769, 44, 83, '恩平市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (770, 44, 84, '榕城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (771, 44, 84, '普宁市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (772, 44, 84, '揭东县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (773, 44, 84, '揭西县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (774, 44, 84, '惠来县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (775, 44, 85, '茂南区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (776, 44, 85, '茂港区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (777, 44, 85, '高州市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (778, 44, 85, '化州市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (779, 44, 85, '信宜市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (780, 44, 85, '电白县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (781, 44, 86, '梅县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (782, 44, 86, '梅江区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (783, 44, 86, '兴宁市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (784, 44, 86, '大埔县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (785, 44, 86, '丰顺县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (786, 44, 86, '五华县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (787, 44, 86, '平远县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (788, 44, 86, '蕉岭县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (789, 44, 87, '清城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (790, 44, 87, '英德市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (791, 44, 87, '连州市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (792, 44, 87, '佛冈县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (793, 44, 87, '阳山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (794, 44, 87, '清新县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (795, 44, 87, '连山', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (796, 44, 87, '连南', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (797, 44, 88, '南澳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (798, 44, 88, '潮阳区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (799, 44, 88, '澄海区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (800, 44, 88, '龙湖区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (801, 44, 88, '金平区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (802, 44, 88, '濠江区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (803, 44, 88, '潮南区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (804, 44, 89, '城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (805, 44, 89, '陆丰市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (806, 44, 89, '海丰县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (807, 44, 89, '陆河县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (808, 44, 90, '曲江县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (809, 44, 90, '浈江区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (810, 44, 90, '武江区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (811, 44, 90, '曲江区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (812, 44, 90, '乐昌市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (813, 44, 90, '南雄市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (814, 44, 90, '始兴县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (815, 44, 90, '仁化县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (816, 44, 90, '翁源县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (817, 44, 90, '新丰县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (818, 44, 90, '乳源', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (819, 44, 91, '江城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (820, 44, 91, '阳春市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (821, 44, 91, '阳西县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (822, 44, 91, '阳东县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (823, 44, 92, '云城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (824, 44, 92, '罗定市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (825, 44, 92, '新兴县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (826, 44, 92, '郁南县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (827, 44, 92, '云安县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (828, 44, 93, '赤坎区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (829, 44, 93, '霞山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (830, 44, 93, '坡头区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (831, 44, 93, '麻章区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (832, 44, 93, '廉江市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (833, 44, 93, '雷州市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (834, 44, 93, '吴川市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (835, 44, 93, '遂溪县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (836, 44, 93, '徐闻县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (837, 44, 94, '肇庆市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (838, 44, 94, '高要市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (839, 44, 94, '四会市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (840, 44, 94, '广宁县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (841, 44, 94, '怀集县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (842, 44, 94, '封开县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (843, 44, 94, '德庆县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (844, 44, 95, '石岐街道', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (845, 44, 95, '东区街道', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (846, 44, 95, '西区街道', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (847, 44, 95, '环城街道', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (848, 44, 95, '中山港街道', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (849, 44, 95, '五桂山街道', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (850, 44, 96, '香洲区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (851, 44, 96, '斗门区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (852, 44, 96, '金湾区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (853, 44, 97, '邕宁区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (854, 44, 97, '青秀区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (855, 44, 97, '兴宁区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (856, 44, 97, '良庆区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (857, 44, 97, '西乡塘区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (858, 44, 97, '江南区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (859, 44, 97, '武鸣县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (860, 44, 97, '隆安县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (861, 44, 97, '马山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (862, 44, 97, '上林县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (863, 44, 97, '宾阳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (864, 44, 97, '横县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (865, 44, 98, '秀峰区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (866, 44, 98, '叠彩区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (867, 44, 98, '象山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (868, 44, 98, '七星区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (869, 44, 98, '雁山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (870, 44, 98, '阳朔县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (871, 44, 98, '临桂县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (872, 44, 98, '灵川县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (873, 44, 98, '全州县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (874, 44, 98, '平乐县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (875, 44, 98, '兴安县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (876, 44, 98, '灌阳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (877, 44, 98, '荔浦县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (878, 44, 98, '资源县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (879, 44, 98, '永福县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (880, 44, 98, '龙胜', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (881, 44, 98, '恭城', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (882, 44, 99, '右江区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (883, 44, 99, '凌云县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (884, 44, 99, '平果县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (885, 44, 99, '西林县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (886, 44, 99, '乐业县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (887, 44, 99, '德保县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (888, 44, 99, '田林县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (889, 44, 99, '田阳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (890, 44, 99, '靖西县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (891, 44, 99, '田东县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (892, 44, 99, '那坡县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (893, 44, 99, '隆林', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (894, 44, 100, '海城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (895, 44, 100, '银海区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (896, 44, 100, '铁山港区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (897, 44, 100, '合浦县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (898, 44, 101, '江州区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (899, 44, 101, '凭祥市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (900, 44, 101, '宁明县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (901, 44, 101, '扶绥县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (902, 44, 101, '龙州县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (903, 44, 101, '大新县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (904, 44, 101, '天等县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (905, 44, 102, '港口区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (906, 44, 102, '防城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (907, 44, 102, '东兴市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (908, 44, 102, '上思县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (909, 44, 103, '港北区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (910, 44, 103, '港南区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (911, 44, 103, '覃塘区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (912, 44, 103, '桂平市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (913, 44, 103, '平南县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (914, 44, 104, '金城江区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (915, 44, 104, '宜州市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (916, 44, 104, '天峨县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (917, 44, 104, '凤山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (918, 44, 104, '南丹县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (919, 44, 104, '东兰县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (920, 44, 104, '都安', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (921, 44, 104, '罗城', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (922, 44, 104, '巴马', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (923, 44, 104, '环江', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (924, 44, 104, '大化', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (925, 44, 105, '八步区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (926, 44, 105, '钟山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (927, 44, 105, '昭平县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (928, 44, 105, '富川', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (929, 44, 106, '兴宾区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (930, 44, 106, '合山市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (931, 44, 106, '象州县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (932, 44, 106, '武宣县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (933, 44, 106, '忻城县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (934, 44, 106, '金秀', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (935, 44, 107, '城中区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (936, 44, 107, '鱼峰区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (937, 44, 107, '柳北区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (938, 44, 107, '柳南区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (939, 44, 107, '柳江县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (940, 44, 107, '柳城县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (941, 44, 107, '鹿寨县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (942, 44, 107, '融安县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (943, 44, 107, '融水', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (944, 44, 107, '三江', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (945, 44, 108, '钦南区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (946, 44, 108, '钦北区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (947, 44, 108, '灵山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (948, 44, 108, '浦北县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (949, 44, 109, '万秀区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (950, 44, 109, '蝶山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (951, 44, 109, '长洲区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (952, 44, 109, '岑溪市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (953, 44, 109, '苍梧县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (954, 44, 109, '藤县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (955, 44, 109, '蒙山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (956, 44, 110, '玉州区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (957, 44, 110, '北流市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (958, 44, 110, '容县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (959, 44, 110, '陆川县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (960, 44, 110, '博白县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (961, 44, 110, '兴业县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (962, 44, 111, '南明区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (963, 44, 111, '云岩区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (964, 44, 111, '花溪区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (965, 44, 111, '乌当区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (966, 44, 111, '白云区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (967, 44, 111, '小河区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (968, 44, 111, '金阳新区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (969, 44, 111, '新天园区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (970, 44, 111, '清镇市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (971, 44, 111, '开阳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (972, 44, 111, '修文县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (973, 44, 111, '息烽县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (974, 44, 112, '西秀区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (975, 44, 112, '关岭', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (976, 44, 112, '镇宁', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (977, 44, 112, '紫云', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (978, 44, 112, '平坝县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (979, 44, 112, '普定县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (980, 44, 113, '毕节市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (981, 44, 113, '大方县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (982, 44, 113, '黔西县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (983, 44, 113, '金沙县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (984, 44, 113, '织金县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (985, 44, 113, '纳雍县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (986, 44, 113, '赫章县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (987, 44, 113, '威宁', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (988, 44, 114, '钟山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (989, 44, 114, '六枝特区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (990, 44, 114, '水城县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (991, 44, 114, '盘县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (992, 44, 115, '凯里市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (993, 44, 115, '黄平县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (994, 44, 115, '施秉县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (995, 44, 115, '三穗县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (996, 44, 115, '镇远县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (997, 44, 115, '岑巩县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (998, 44, 115, '天柱县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (999, 44, 115, '锦屏县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1000, 44, 115, '剑河县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1001, 44, 115, '台江县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1002, 44, 115, '黎平县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1003, 44, 115, '榕江县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1004, 44, 115, '从江县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1005, 44, 115, '雷山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1006, 44, 115, '麻江县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1007, 44, 115, '丹寨县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1008, 44, 116, '都匀市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1009, 44, 116, '福泉市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1010, 44, 116, '荔波县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1011, 44, 116, '贵定县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1012, 44, 116, '瓮安县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1013, 44, 116, '独山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1014, 44, 116, '平塘县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1015, 44, 116, '罗甸县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1016, 44, 116, '长顺县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1017, 44, 116, '龙里县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1018, 44, 116, '惠水县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1019, 44, 116, '三都', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1020, 44, 117, '兴义市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1021, 44, 117, '兴仁县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1022, 44, 117, '普安县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1023, 44, 117, '晴隆县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1024, 44, 117, '贞丰县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1025, 44, 117, '望谟县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1026, 44, 117, '册亨县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1027, 44, 117, '安龙县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1028, 44, 118, '铜仁市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1029, 44, 118, '江口县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1030, 44, 118, '石阡县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1031, 44, 118, '思南县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1032, 44, 118, '德江县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1033, 44, 118, '玉屏', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1034, 44, 118, '印江', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1035, 44, 118, '沿河', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1036, 44, 118, '松桃', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1037, 44, 118, '万山特区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1038, 44, 119, '红花岗区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1039, 44, 119, '务川县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1040, 44, 119, '道真县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1041, 44, 119, '汇川区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1042, 44, 119, '赤水市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1043, 44, 119, '仁怀市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1044, 44, 119, '遵义县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1045, 44, 119, '桐梓县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1046, 44, 119, '绥阳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1047, 44, 119, '正安县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1048, 44, 119, '凤冈县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1049, 44, 119, '湄潭县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1050, 44, 119, '余庆县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1051, 44, 119, '习水县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1052, 44, 119, '道真', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1053, 44, 119, '务川', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1054, 44, 120, '秀英区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1055, 44, 120, '龙华区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1056, 44, 120, '琼山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1057, 44, 120, '美兰区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1058, 44, 137, '市区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1059, 44, 137, '洋浦开发区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1060, 44, 137, '那大镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1061, 44, 137, '王五镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1062, 44, 137, '雅星镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1063, 44, 137, '大成镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1064, 44, 137, '中和镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1065, 44, 137, '峨蔓镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1066, 44, 137, '南丰镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1067, 44, 137, '白马井镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1068, 44, 137, '兰洋镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1069, 44, 137, '和庆镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1070, 44, 137, '海头镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1071, 44, 137, '排浦镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1072, 44, 137, '东成镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1073, 44, 137, '光村镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1074, 44, 137, '木棠镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1075, 44, 137, '新州镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1076, 44, 137, '三都镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1077, 44, 137, '其他', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1078, 44, 138, '长安区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1079, 44, 138, '桥东区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1080, 44, 138, '桥西区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1081, 44, 138, '新华区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1082, 44, 138, '裕华区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1083, 44, 138, '井陉矿区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1084, 44, 138, '高新区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1085, 44, 138, '辛集市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1086, 44, 138, '藁城市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1087, 44, 138, '晋州市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1088, 44, 138, '新乐市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1089, 44, 138, '鹿泉市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1090, 44, 138, '井陉县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1091, 44, 138, '正定县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1092, 44, 138, '栾城县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1093, 44, 138, '行唐县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1094, 44, 138, '灵寿县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1095, 44, 138, '高邑县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1096, 44, 138, '深泽县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1097, 44, 138, '赞皇县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1098, 44, 138, '无极县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1099, 44, 138, '平山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1100, 44, 138, '元氏县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1101, 44, 138, '赵县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1102, 44, 139, '新市区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1103, 44, 139, '南市区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1104, 44, 139, '北市区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1105, 44, 139, '涿州市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1106, 44, 139, '定州市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1107, 44, 139, '安国市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1108, 44, 139, '高碑店市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1109, 44, 139, '满城县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1110, 44, 139, '清苑县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1111, 44, 139, '涞水县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1112, 44, 139, '阜平县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1113, 44, 139, '徐水县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1114, 44, 139, '定兴县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1115, 44, 139, '唐县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1116, 44, 139, '高阳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1117, 44, 139, '容城县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1118, 44, 139, '涞源县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1119, 44, 139, '望都县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1120, 44, 139, '安新县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1121, 44, 139, '易县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1122, 44, 139, '曲阳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1123, 44, 139, '蠡县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1124, 44, 139, '顺平县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1125, 44, 139, '博野县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1126, 44, 139, '雄县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1127, 44, 140, '运河区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1128, 44, 140, '新华区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1129, 44, 140, '泊头市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1130, 44, 140, '任丘市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1131, 44, 140, '黄骅市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1132, 44, 140, '河间市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1133, 44, 140, '沧县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1134, 44, 140, '青县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1135, 44, 140, '东光县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1136, 44, 140, '海兴县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1137, 44, 140, '盐山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1138, 44, 140, '肃宁县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1139, 44, 140, '南皮县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1140, 44, 140, '吴桥县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1141, 44, 140, '献县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1142, 44, 140, '孟村', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1143, 44, 141, '双桥区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1144, 44, 141, '双滦区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1145, 44, 141, '鹰手营子矿区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1146, 44, 141, '承德县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1147, 44, 141, '兴隆县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1148, 44, 141, '平泉县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1149, 44, 141, '滦平县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1150, 44, 141, '隆化县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1151, 44, 141, '丰宁', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1152, 44, 141, '宽城', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1153, 44, 141, '围场', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1154, 44, 142, '从台区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1155, 44, 142, '复兴区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1156, 44, 142, '邯山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1157, 44, 142, '峰峰矿区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1158, 44, 142, '武安市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1159, 44, 142, '邯郸县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1160, 44, 142, '临漳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1161, 44, 142, '成安县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1162, 44, 142, '大名县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1163, 44, 142, '涉县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1164, 44, 142, '磁县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1165, 44, 142, '肥乡县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1166, 44, 142, '永年县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1167, 44, 142, '邱县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1168, 44, 142, '鸡泽县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1169, 44, 142, '广平县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1170, 44, 142, '馆陶县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1171, 44, 142, '魏县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1172, 44, 142, '曲周县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1173, 44, 143, '桃城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1174, 44, 143, '冀州市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1175, 44, 143, '深州市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1176, 44, 143, '枣强县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1177, 44, 143, '武邑县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1178, 44, 143, '武强县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1179, 44, 143, '饶阳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1180, 44, 143, '安平县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1181, 44, 143, '故城县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1182, 44, 143, '景县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1183, 44, 143, '阜城县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1184, 44, 144, '安次区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1185, 44, 144, '广阳区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1186, 44, 144, '霸州市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1187, 44, 144, '三河市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1188, 44, 144, '固安县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1189, 44, 144, '永清县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1190, 44, 144, '香河县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1191, 44, 144, '大城县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1192, 44, 144, '文安县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1193, 44, 144, '大厂', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1194, 44, 145, '海港区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1195, 44, 145, '山海关区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1196, 44, 145, '北戴河区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1197, 44, 145, '昌黎县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1198, 44, 145, '抚宁县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1199, 44, 145, '卢龙县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1200, 44, 145, '青龙', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1201, 44, 146, '路北区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1202, 44, 146, '路南区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1203, 44, 146, '古冶区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1204, 44, 146, '开平区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1205, 44, 146, '丰南区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1206, 44, 146, '丰润区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1207, 44, 146, '遵化市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1208, 44, 146, '迁安市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1209, 44, 146, '滦县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1210, 44, 146, '滦南县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1211, 44, 146, '乐亭县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1212, 44, 146, '迁西县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1213, 44, 146, '玉田县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1214, 44, 146, '唐海县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1215, 44, 147, '桥东区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1216, 44, 147, '桥西区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1217, 44, 147, '南宫市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1218, 44, 147, '沙河市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1219, 44, 147, '邢台县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1220, 44, 147, '临城县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1221, 44, 147, '内丘县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1222, 44, 147, '柏乡县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1223, 44, 147, '隆尧县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1224, 44, 147, '任县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1225, 44, 147, '南和县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1226, 44, 147, '宁晋县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1227, 44, 147, '巨鹿县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1228, 44, 147, '新河县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1229, 44, 147, '广宗县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1230, 44, 147, '平乡县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1231, 44, 147, '威县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1232, 44, 147, '清河县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1233, 44, 147, '临西县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1234, 44, 148, '桥西区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1235, 44, 148, '桥东区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1236, 44, 148, '宣化区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1237, 44, 148, '下花园区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1238, 44, 148, '宣化县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1239, 44, 148, '张北县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1240, 44, 148, '康保县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1241, 44, 148, '沽源县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1242, 44, 148, '尚义县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1243, 44, 148, '蔚县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1244, 44, 148, '阳原县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1245, 44, 148, '怀安县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1246, 44, 148, '万全县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1247, 44, 148, '怀来县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1248, 44, 148, '涿鹿县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1249, 44, 148, '赤城县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1250, 44, 148, '崇礼县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1251, 44, 149, '金水区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1252, 44, 149, '邙山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1253, 44, 149, '二七区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1254, 44, 149, '管城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1255, 44, 149, '中原区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1256, 44, 149, '上街区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1257, 44, 149, '惠济区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1258, 44, 149, '郑东新区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1259, 44, 149, '经济技术开发区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1260, 44, 149, '高新开发区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1261, 44, 149, '出口加工区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1262, 44, 149, '巩义市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1263, 44, 149, '荥阳市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1264, 44, 149, '新密市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1265, 44, 149, '新郑市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1266, 44, 149, '登封市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1267, 44, 149, '中牟县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1268, 44, 150, '西工区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1269, 44, 150, '老城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1270, 44, 150, '涧西区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1271, 44, 150, '瀍河回族区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1272, 44, 150, '洛龙区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1273, 44, 150, '吉利区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1274, 44, 150, '偃师市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1275, 44, 150, '孟津县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1276, 44, 150, '新安县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1277, 44, 150, '栾川县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1278, 44, 150, '嵩县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1279, 44, 150, '汝阳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1280, 44, 150, '宜阳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1281, 44, 150, '洛宁县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1282, 44, 150, '伊川县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1283, 44, 151, '鼓楼区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1284, 44, 151, '龙亭区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1285, 44, 151, '顺河回族区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1286, 44, 151, '金明区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1287, 44, 151, '禹王台区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1288, 44, 151, '杞县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1289, 44, 151, '通许县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1290, 44, 151, '尉氏县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1291, 44, 151, '开封县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1292, 44, 151, '兰考县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1293, 44, 152, '北关区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1294, 44, 152, '文峰区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1295, 44, 152, '殷都区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1296, 44, 152, '龙安区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1297, 44, 152, '林州市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1298, 44, 152, '安阳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1299, 44, 152, '汤阴县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1300, 44, 152, '滑县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1301, 44, 152, '内黄县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1302, 44, 153, '淇滨区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1303, 44, 153, '山城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1304, 44, 153, '鹤山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1305, 44, 153, '浚县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1306, 44, 153, '淇县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1307, 44, 154, '济源市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1308, 44, 155, '解放区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1309, 44, 155, '中站区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1310, 44, 155, '马村区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1311, 44, 155, '山阳区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1312, 44, 155, '沁阳市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1313, 44, 155, '孟州市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1314, 44, 155, '修武县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1315, 44, 155, '博爱县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1316, 44, 155, '武陟县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1317, 44, 155, '温县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1318, 44, 156, '卧龙区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1319, 44, 156, '宛城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1320, 44, 156, '邓州市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1321, 44, 156, '南召县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1322, 44, 156, '方城县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1323, 44, 156, '西峡县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1324, 44, 156, '镇平县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1325, 44, 156, '内乡县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1326, 44, 156, '淅川县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1327, 44, 156, '社旗县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1328, 44, 156, '唐河县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1329, 44, 156, '新野县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1330, 44, 156, '桐柏县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1331, 44, 157, '新华区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1332, 44, 157, '卫东区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1333, 44, 157, '湛河区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1334, 44, 157, '石龙区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1335, 44, 157, '舞钢市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1336, 44, 157, '汝州市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1337, 44, 157, '宝丰县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1338, 44, 157, '叶县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1339, 44, 157, '鲁山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1340, 44, 157, '郏县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1341, 44, 158, '湖滨区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1342, 44, 158, '义马市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1343, 44, 158, '灵宝市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1344, 44, 158, '渑池县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1345, 44, 158, '陕县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1346, 44, 158, '卢氏县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1347, 44, 159, '梁园区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1348, 44, 159, '睢阳区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1349, 44, 159, '永城市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1350, 44, 159, '民权县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1351, 44, 159, '睢县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1352, 44, 159, '宁陵县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1353, 44, 159, '虞城县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1354, 44, 159, '柘城县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1355, 44, 159, '夏邑县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1356, 44, 160, '卫滨区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1357, 44, 160, '红旗区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1358, 44, 160, '凤泉区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1359, 44, 160, '牧野区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1360, 44, 160, '卫辉市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1361, 44, 160, '辉县市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1362, 44, 160, '新乡县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1363, 44, 160, '获嘉县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1364, 44, 160, '原阳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1365, 44, 160, '延津县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1366, 44, 160, '封丘县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1367, 44, 160, '长垣县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1368, 44, 161, '浉河区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1369, 44, 161, '平桥区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1370, 44, 161, '罗山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1371, 44, 161, '光山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1372, 44, 161, '新县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1373, 44, 161, '商城县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1374, 44, 161, '固始县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1375, 44, 161, '潢川县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1376, 44, 161, '淮滨县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1377, 44, 161, '息县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1378, 44, 162, '魏都区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1379, 44, 162, '禹州市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1380, 44, 162, '长葛市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1381, 44, 162, '许昌县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1382, 44, 162, '鄢陵县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1383, 44, 162, '襄城县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1384, 44, 163, '川汇区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1385, 44, 163, '项城市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1386, 44, 163, '扶沟县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1387, 44, 163, '西华县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1388, 44, 163, '商水县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1389, 44, 163, '沈丘县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1390, 44, 163, '郸城县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1391, 44, 163, '淮阳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1392, 44, 163, '太康县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1393, 44, 163, '鹿邑县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1394, 44, 164, '驿城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1395, 44, 164, '西平县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1396, 44, 164, '上蔡县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1397, 44, 164, '平舆县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1398, 44, 164, '正阳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1399, 44, 164, '确山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1400, 44, 164, '泌阳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1401, 44, 164, '汝南县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1402, 44, 164, '遂平县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1403, 44, 164, '新蔡县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1404, 44, 165, '郾城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1405, 44, 165, '源汇区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1406, 44, 165, '召陵区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1407, 44, 165, '舞阳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1408, 44, 165, '临颍县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1409, 44, 166, '华龙区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1410, 44, 166, '清丰县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1411, 44, 166, '南乐县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1412, 44, 166, '范县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1413, 44, 166, '台前县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1414, 44, 166, '濮阳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1415, 44, 167, '道里区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1416, 44, 167, '南岗区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1417, 44, 167, '动力区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1418, 44, 167, '平房区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1419, 44, 167, '香坊区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1420, 44, 167, '太平区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1421, 44, 167, '道外区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1422, 44, 167, '阿城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1423, 44, 167, '呼兰区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1424, 44, 167, '松北区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1425, 44, 167, '尚志市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1426, 44, 167, '双城市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1427, 44, 167, '五常市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1428, 44, 167, '方正县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1429, 44, 167, '宾县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1430, 44, 167, '依兰县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1431, 44, 167, '巴彦县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1432, 44, 167, '通河县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1433, 44, 167, '木兰县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1434, 44, 167, '延寿县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1435, 44, 168, '萨尔图区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1436, 44, 168, '红岗区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1437, 44, 168, '龙凤区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1438, 44, 168, '让胡路区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1439, 44, 168, '大同区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1440, 44, 168, '肇州县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1441, 44, 168, '肇源县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1442, 44, 168, '林甸县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1443, 44, 168, '杜尔伯特', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1444, 44, 169, '呼玛县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1445, 44, 169, '漠河县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1446, 44, 169, '塔河县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1447, 44, 170, '兴山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1448, 44, 170, '工农区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1449, 44, 170, '南山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1450, 44, 170, '兴安区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1451, 44, 170, '向阳区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1452, 44, 170, '东山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1453, 44, 170, '萝北县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1454, 44, 170, '绥滨县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1455, 44, 171, '爱辉区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1456, 44, 171, '五大连池市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1457, 44, 171, '北安市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1458, 44, 171, '嫩江县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1459, 44, 171, '逊克县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1460, 44, 171, '孙吴县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1461, 44, 172, '鸡冠区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1462, 44, 172, '恒山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1463, 44, 172, '城子河区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1464, 44, 172, '滴道区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1465, 44, 172, '梨树区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1466, 44, 172, '虎林市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1467, 44, 172, '密山市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1468, 44, 172, '鸡东县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1469, 44, 173, '前进区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1470, 44, 173, '郊区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1471, 44, 173, '向阳区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1472, 44, 173, '东风区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1473, 44, 173, '同江市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1474, 44, 173, '富锦市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1475, 44, 173, '桦南县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1476, 44, 173, '桦川县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1477, 44, 173, '汤原县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1478, 44, 173, '抚远县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1479, 44, 174, '爱民区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1480, 44, 174, '东安区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1481, 44, 174, '阳明区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1482, 44, 174, '西安区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1483, 44, 174, '绥芬河市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1484, 44, 174, '海林市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1485, 44, 174, '宁安市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1486, 44, 174, '穆棱市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1487, 44, 174, '东宁县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1488, 44, 174, '林口县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1489, 44, 175, '桃山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1490, 44, 175, '新兴区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1491, 44, 175, '茄子河区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1492, 44, 175, '勃利县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1493, 44, 176, '龙沙区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1494, 44, 176, '昂昂溪区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1495, 44, 176, '铁峰区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1496, 44, 176, '建华区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1497, 44, 176, '富拉尔基区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1498, 44, 176, '碾子山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1499, 44, 176, '梅里斯达斡尔区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1500, 44, 176, '讷河市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1501, 44, 176, '龙江县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1502, 44, 176, '依安县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1503, 44, 176, '泰来县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1504, 44, 176, '甘南县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1505, 44, 176, '富裕县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1506, 44, 176, '克山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1507, 44, 176, '克东县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1508, 44, 176, '拜泉县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1509, 44, 177, '尖山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1510, 44, 177, '岭东区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1511, 44, 177, '四方台区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1512, 44, 177, '宝山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1513, 44, 177, '集贤县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1514, 44, 177, '友谊县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1515, 44, 177, '宝清县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1516, 44, 177, '饶河县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1517, 44, 178, '北林区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1518, 44, 178, '安达市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1519, 44, 178, '肇东市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1520, 44, 178, '海伦市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1521, 44, 178, '望奎县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1522, 44, 178, '兰西县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1523, 44, 178, '青冈县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1524, 44, 178, '庆安县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1525, 44, 178, '明水县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1526, 44, 178, '绥棱县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1527, 44, 179, '伊春区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1528, 44, 179, '带岭区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1529, 44, 179, '南岔区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1530, 44, 179, '金山屯区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1531, 44, 179, '西林区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1532, 44, 179, '美溪区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1533, 44, 179, '乌马河区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1534, 44, 179, '翠峦区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1535, 44, 179, '友好区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1536, 44, 179, '上甘岭区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1537, 44, 179, '五营区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1538, 44, 179, '红星区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1539, 44, 179, '新青区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1540, 44, 179, '汤旺河区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1541, 44, 179, '乌伊岭区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1542, 44, 179, '铁力市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1543, 44, 179, '嘉荫县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1544, 44, 180, '江岸区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1545, 44, 180, '武昌区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1546, 44, 180, '江汉区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1547, 44, 180, '硚口区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1548, 44, 180, '汉阳区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1549, 44, 180, '青山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1550, 44, 180, '洪山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1551, 44, 180, '东西湖区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1552, 44, 180, '汉南区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1553, 44, 180, '蔡甸区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1554, 44, 180, '江夏区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1555, 44, 180, '黄陂区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1556, 44, 180, '新洲区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1557, 44, 180, '经济开发区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1558, 44, 181, '仙桃市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1559, 44, 182, '鄂城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1560, 44, 182, '华容区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1561, 44, 182, '梁子湖区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1562, 44, 183, '黄州区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1563, 44, 183, '麻城市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1564, 44, 183, '武穴市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1565, 44, 183, '团风县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1566, 44, 183, '红安县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1567, 44, 183, '罗田县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1568, 44, 183, '英山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1569, 44, 183, '浠水县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1570, 44, 183, '蕲春县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1571, 44, 183, '黄梅县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1572, 44, 184, '黄石港区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1573, 44, 184, '西塞山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1574, 44, 184, '下陆区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1575, 44, 184, '铁山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1576, 44, 184, '大冶市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1577, 44, 184, '阳新县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1578, 44, 185, '东宝区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1579, 44, 185, '掇刀区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1580, 44, 185, '钟祥市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1581, 44, 185, '京山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1582, 44, 185, '沙洋县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1583, 44, 186, '沙市区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1584, 44, 186, '荆州区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1585, 44, 186, '石首市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1586, 44, 186, '洪湖市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1587, 44, 186, '松滋市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1588, 44, 186, '公安县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1589, 44, 186, '监利县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1590, 44, 186, '江陵县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1591, 44, 187, '潜江市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1592, 44, 188, '神农架林区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1593, 44, 189, '张湾区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1594, 44, 189, '茅箭区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1595, 44, 189, '丹江口市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1596, 44, 189, '郧县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1597, 44, 189, '郧西县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1598, 44, 189, '竹山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1599, 44, 189, '竹溪县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1600, 44, 189, '房县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1601, 44, 190, '曾都区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1602, 44, 190, '广水市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1603, 44, 191, '天门市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1604, 44, 192, '咸安区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1605, 44, 192, '赤壁市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1606, 44, 192, '嘉鱼县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1607, 44, 192, '通城县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1608, 44, 192, '崇阳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1609, 44, 192, '通山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1610, 44, 193, '襄城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1611, 44, 193, '樊城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1612, 44, 193, '襄阳区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1613, 44, 193, '老河口市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1614, 44, 193, '枣阳市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1615, 44, 193, '宜城市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1616, 44, 193, '南漳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1617, 44, 193, '谷城县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1618, 44, 193, '保康县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1619, 44, 194, '孝南区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1620, 44, 194, '应城市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1621, 44, 194, '安陆市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1622, 44, 194, '汉川市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1623, 44, 194, '孝昌县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1624, 44, 194, '大悟县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1625, 44, 194, '云梦县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1626, 44, 195, '长阳', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1627, 44, 195, '五峰', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1628, 44, 195, '西陵区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1629, 44, 195, '伍家岗区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1630, 44, 195, '点军区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1631, 44, 195, '猇亭区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1632, 44, 195, '夷陵区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1633, 44, 195, '宜都市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1634, 44, 195, '当阳市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1635, 44, 195, '枝江市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1636, 44, 195, '远安县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1637, 44, 195, '兴山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1638, 44, 195, '秭归县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1639, 44, 196, '恩施市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1640, 44, 196, '利川市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1641, 44, 196, '建始县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1642, 44, 196, '巴东县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1643, 44, 196, '宣恩县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1644, 44, 196, '咸丰县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1645, 44, 196, '来凤县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1646, 44, 196, '鹤峰县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1647, 44, 197, '岳麓区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1648, 44, 197, '芙蓉区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1649, 44, 197, '天心区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1650, 44, 197, '开福区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1651, 44, 197, '雨花区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1652, 44, 197, '开发区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1653, 44, 197, '浏阳市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1654, 44, 197, '长沙县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1655, 44, 197, '望城县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1656, 44, 197, '宁乡县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1657, 44, 198, '永定区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1658, 44, 198, '武陵源区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1659, 44, 198, '慈利县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1660, 44, 198, '桑植县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1661, 44, 199, '武陵区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1662, 44, 199, '鼎城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1663, 44, 199, '津市市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1664, 44, 199, '安乡县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1665, 44, 199, '汉寿县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1666, 44, 199, '澧县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1667, 44, 199, '临澧县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1668, 44, 199, '桃源县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1669, 44, 199, '石门县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1670, 44, 200, '北湖区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1671, 44, 200, '苏仙区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1672, 44, 200, '资兴市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1673, 44, 200, '桂阳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1674, 44, 200, '宜章县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1675, 44, 200, '永兴县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1676, 44, 200, '嘉禾县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1677, 44, 200, '临武县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1678, 44, 200, '汝城县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1679, 44, 200, '桂东县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1680, 44, 200, '安仁县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1681, 44, 201, '雁峰区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1682, 44, 201, '珠晖区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1683, 44, 201, '石鼓区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1684, 44, 201, '蒸湘区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1685, 44, 201, '南岳区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1686, 44, 201, '耒阳市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1687, 44, 201, '常宁市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1688, 44, 201, '衡阳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1689, 44, 201, '衡南县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1690, 44, 201, '衡山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1691, 44, 201, '衡东县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1692, 44, 201, '祁东县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1693, 44, 202, '鹤城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1694, 44, 202, '靖州', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1695, 44, 202, '麻阳', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1696, 44, 202, '通道', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1697, 44, 202, '新晃', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1698, 44, 202, '芷江', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1699, 44, 202, '沅陵县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1700, 44, 202, '辰溪县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1701, 44, 202, '溆浦县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1702, 44, 202, '中方县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1703, 44, 202, '会同县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1704, 44, 202, '洪江市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1705, 44, 203, '娄星区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1706, 44, 203, '冷水江市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1707, 44, 203, '涟源市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1708, 44, 203, '双峰县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1709, 44, 203, '新化县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1710, 44, 204, '城步', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1711, 44, 204, '双清区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1712, 44, 204, '大祥区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1713, 44, 204, '北塔区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1714, 44, 204, '武冈市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1715, 44, 204, '邵东县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1716, 44, 204, '新邵县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1717, 44, 204, '邵阳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1718, 44, 204, '隆回县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1719, 44, 204, '洞口县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1720, 44, 204, '绥宁县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1721, 44, 204, '新宁县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1722, 44, 205, '岳塘区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1723, 44, 205, '雨湖区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1724, 44, 205, '湘乡市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1725, 44, 205, '韶山市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1726, 44, 205, '湘潭县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1727, 44, 206, '吉首市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1728, 44, 206, '泸溪县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1729, 44, 206, '凤凰县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1730, 44, 206, '花垣县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1731, 44, 206, '保靖县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1732, 44, 206, '古丈县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1733, 44, 206, '永顺县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1734, 44, 206, '龙山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1735, 44, 207, '赫山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1736, 44, 207, '资阳区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1737, 44, 207, '沅江市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1738, 44, 207, '南县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1739, 44, 207, '桃江县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1740, 44, 207, '安化县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1741, 44, 208, '江华', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1742, 44, 208, '冷水滩区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1743, 44, 208, '零陵区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1744, 44, 208, '祁阳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1745, 44, 208, '东安县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1746, 44, 208, '双牌县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1747, 44, 208, '道县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1748, 44, 208, '江永县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1749, 44, 208, '宁远县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1750, 44, 208, '蓝山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1751, 44, 208, '新田县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1752, 44, 209, '岳阳楼区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1753, 44, 209, '君山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1754, 44, 209, '云溪区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1755, 44, 209, '汨罗市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1756, 44, 209, '临湘市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1757, 44, 209, '岳阳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1758, 44, 209, '华容县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1759, 44, 209, '湘阴县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1760, 44, 209, '平江县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1761, 44, 210, '天元区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1762, 44, 210, '荷塘区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1763, 44, 210, '芦淞区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1764, 44, 210, '石峰区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1765, 44, 210, '醴陵市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1766, 44, 210, '株洲县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1767, 44, 210, '攸县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1768, 44, 210, '茶陵县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1769, 44, 210, '炎陵县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1770, 44, 211, '朝阳区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1771, 44, 211, '宽城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1772, 44, 211, '二道区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1773, 44, 211, '南关区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1774, 44, 211, '绿园区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1775, 44, 211, '双阳区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1776, 44, 211, '净月潭开发区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1777, 44, 211, '高新技术开发区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1778, 44, 211, '经济技术开发区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1779, 44, 211, '汽车产业开发区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1780, 44, 211, '德惠市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1781, 44, 211, '九台市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1782, 44, 211, '榆树市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1783, 44, 211, '农安县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1784, 44, 212, '船营区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1785, 44, 212, '昌邑区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1786, 44, 212, '龙潭区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1787, 44, 212, '丰满区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1788, 44, 212, '蛟河市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1789, 44, 212, '桦甸市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1790, 44, 212, '舒兰市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1791, 44, 212, '磐石市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1792, 44, 212, '永吉县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1793, 44, 213, '洮北区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1794, 44, 213, '洮南市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1795, 44, 213, '大安市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1796, 44, 213, '镇赉县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1797, 44, 213, '通榆县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1798, 44, 214, '江源区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1799, 44, 214, '八道江区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1800, 44, 214, '长白', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1801, 44, 214, '临江市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1802, 44, 214, '抚松县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1803, 44, 214, '靖宇县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1804, 44, 215, '龙山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1805, 44, 215, '西安区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1806, 44, 215, '东丰县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1807, 44, 215, '东辽县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1808, 44, 216, '铁西区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1809, 44, 216, '铁东区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1810, 44, 216, '伊通', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1811, 44, 216, '公主岭市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1812, 44, 216, '双辽市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1813, 44, 216, '梨树县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1814, 44, 217, '前郭尔罗斯', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1815, 44, 217, '宁江区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1816, 44, 217, '长岭县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1817, 44, 217, '乾安县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1818, 44, 217, '扶余县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1819, 44, 218, '东昌区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1820, 44, 218, '二道江区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1821, 44, 218, '梅河口市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1822, 44, 218, '集安市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1823, 44, 218, '通化县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1824, 44, 218, '辉南县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1825, 44, 218, '柳河县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1826, 44, 219, '延吉市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1827, 44, 219, '图们市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1828, 44, 219, '敦化市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1829, 44, 219, '珲春市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1830, 44, 219, '龙井市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1831, 44, 219, '和龙市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1832, 44, 219, '安图县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1833, 44, 219, '汪清县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1834, 44, 220, '玄武区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1835, 44, 220, '鼓楼区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1836, 44, 220, '白下区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1837, 44, 220, '建邺区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1838, 44, 220, '秦淮区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1839, 44, 220, '雨花台区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1840, 44, 220, '下关区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1841, 44, 220, '栖霞区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1842, 44, 220, '浦口区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1843, 44, 220, '江宁区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1844, 44, 220, '六合区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1845, 44, 220, '溧水县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1846, 44, 220, '高淳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1847, 44, 221, '沧浪区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1848, 44, 221, '金阊区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1849, 44, 221, '平江区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1850, 44, 221, '虎丘区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1851, 44, 221, '吴中区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1852, 44, 221, '相城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1853, 44, 221, '园区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1854, 44, 221, '新区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1855, 44, 221, '常熟市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1856, 44, 221, '张家港市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1857, 44, 221, '玉山镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1858, 44, 221, '巴城镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1859, 44, 221, '周市镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1860, 44, 221, '陆家镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1861, 44, 221, '花桥镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1862, 44, 221, '淀山湖镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1863, 44, 221, '张浦镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1864, 44, 221, '周庄镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1865, 44, 221, '千灯镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1866, 44, 221, '锦溪镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1867, 44, 221, '开发区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1868, 44, 221, '吴江市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1869, 44, 221, '太仓市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1870, 44, 222, '崇安区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1871, 44, 222, '北塘区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1872, 44, 222, '南长区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1873, 44, 222, '锡山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1874, 44, 222, '惠山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1875, 44, 222, '滨湖区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1876, 44, 222, '新区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1877, 44, 222, '江阴市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1878, 44, 222, '宜兴市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1879, 44, 223, '天宁区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1880, 44, 223, '钟楼区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1881, 44, 223, '戚墅堰区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1882, 44, 223, '郊区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1883, 44, 223, '新北区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1884, 44, 223, '武进区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1885, 44, 223, '溧阳市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1886, 44, 223, '金坛市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1887, 44, 224, '清河区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1888, 44, 224, '清浦区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1889, 44, 224, '楚州区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1890, 44, 224, '淮阴区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1891, 44, 224, '涟水县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1892, 44, 224, '洪泽县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1893, 44, 224, '盱眙县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1894, 44, 224, '金湖县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1895, 44, 225, '新浦区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1896, 44, 225, '连云区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1897, 44, 225, '海州区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1898, 44, 225, '赣榆县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1899, 44, 225, '东海县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1900, 44, 225, '灌云县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1901, 44, 225, '灌南县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1902, 44, 226, '崇川区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1903, 44, 226, '港闸区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1904, 44, 226, '经济开发区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1905, 44, 226, '启东市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1906, 44, 226, '如皋市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1907, 44, 226, '通州市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1908, 44, 226, '海门市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1909, 44, 226, '海安县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1910, 44, 226, '如东县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1911, 44, 227, '宿城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1912, 44, 227, '宿豫区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1913, 44, 227, '宿豫县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1914, 44, 227, '沭阳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1915, 44, 227, '泗阳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1916, 44, 227, '泗洪县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1917, 44, 228, '海陵区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1918, 44, 228, '高港区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1919, 44, 228, '兴化市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1920, 44, 228, '靖江市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1921, 44, 228, '泰兴市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1922, 44, 228, '姜堰市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1923, 44, 229, '云龙区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1924, 44, 229, '鼓楼区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1925, 44, 229, '九里区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1926, 44, 229, '贾汪区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1927, 44, 229, '泉山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1928, 44, 229, '新沂市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1929, 44, 229, '邳州市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1930, 44, 229, '丰县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1931, 44, 229, '沛县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1932, 44, 229, '铜山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1933, 44, 229, '睢宁县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1934, 44, 230, '城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1935, 44, 230, '亭湖区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1936, 44, 230, '盐都区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1937, 44, 230, '盐都县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1938, 44, 230, '东台市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1939, 44, 230, '大丰市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1940, 44, 230, '响水县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1941, 44, 230, '滨海县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1942, 44, 230, '阜宁县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1943, 44, 230, '射阳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1944, 44, 230, '建湖县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1945, 44, 231, '广陵区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1946, 44, 231, '维扬区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1947, 44, 231, '邗江区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1948, 44, 231, '仪征市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1949, 44, 231, '高邮市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1950, 44, 231, '江都市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1951, 44, 231, '宝应县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1952, 44, 232, '京口区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1953, 44, 232, '润州区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1954, 44, 232, '丹徒区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1955, 44, 232, '丹阳市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1956, 44, 232, '扬中市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1957, 44, 232, '句容市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1958, 44, 233, '东湖区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1959, 44, 233, '西湖区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1960, 44, 233, '青云谱区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1961, 44, 233, '湾里区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1962, 44, 233, '青山湖区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1963, 44, 233, '红谷滩新区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1964, 44, 233, '昌北区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1965, 44, 233, '高新区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1966, 44, 233, '南昌县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1967, 44, 233, '新建县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1968, 44, 233, '安义县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1969, 44, 233, '进贤县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1970, 44, 234, '临川区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1971, 44, 234, '南城县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1972, 44, 234, '黎川县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1973, 44, 234, '南丰县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1974, 44, 234, '崇仁县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1975, 44, 234, '乐安县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1976, 44, 234, '宜黄县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1977, 44, 234, '金溪县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1978, 44, 234, '资溪县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1979, 44, 234, '东乡县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1980, 44, 234, '广昌县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1981, 44, 235, '章贡区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1982, 44, 235, '于都县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1983, 44, 235, '瑞金市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1984, 44, 235, '南康市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1985, 44, 235, '赣县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1986, 44, 235, '信丰县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1987, 44, 235, '大余县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1988, 44, 235, '上犹县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1989, 44, 235, '崇义县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1990, 44, 235, '安远县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1991, 44, 235, '龙南县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1992, 44, 235, '定南县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1993, 44, 235, '全南县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1994, 44, 235, '宁都县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1995, 44, 235, '兴国县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1996, 44, 235, '会昌县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1997, 44, 235, '寻乌县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1998, 44, 235, '石城县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (1999, 44, 236, '安福县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2000, 44, 236, '吉州区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2001, 44, 236, '青原区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2002, 44, 236, '井冈山市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2003, 44, 236, '吉安县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2004, 44, 236, '吉水县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2005, 44, 236, '峡江县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2006, 44, 236, '新干县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2007, 44, 236, '永丰县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2008, 44, 236, '泰和县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2009, 44, 236, '遂川县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2010, 44, 236, '万安县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2011, 44, 236, '永新县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2012, 44, 237, '珠山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2013, 44, 237, '昌江区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2014, 44, 237, '乐平市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2015, 44, 237, '浮梁县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2016, 44, 238, '浔阳区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2017, 44, 238, '庐山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2018, 44, 238, '瑞昌市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2019, 44, 238, '九江县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2020, 44, 238, '武宁县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2021, 44, 238, '修水县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2022, 44, 238, '永修县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2023, 44, 238, '德安县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2024, 44, 238, '星子县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2025, 44, 238, '都昌县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2026, 44, 238, '湖口县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2027, 44, 238, '彭泽县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2028, 44, 239, '安源区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2029, 44, 239, '湘东区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2030, 44, 239, '莲花县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2031, 44, 239, '芦溪县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2032, 44, 239, '上栗县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2033, 44, 240, '信州区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2034, 44, 240, '德兴市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2035, 44, 240, '上饶县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2036, 44, 240, '广丰县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2037, 44, 240, '玉山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2038, 44, 240, '铅山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2039, 44, 240, '横峰县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2040, 44, 240, '弋阳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2041, 44, 240, '余干县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2042, 44, 240, '波阳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2043, 44, 240, '万年县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2044, 44, 240, '婺源县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2045, 44, 241, '渝水区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2046, 44, 241, '分宜县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2047, 44, 242, '袁州区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2048, 44, 242, '丰城市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2049, 44, 242, '樟树市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2050, 44, 242, '高安市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2051, 44, 242, '奉新县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2052, 44, 242, '万载县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2053, 44, 242, '上高县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2054, 44, 242, '宜丰县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2055, 44, 242, '靖安县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2056, 44, 242, '铜鼓县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2057, 44, 243, '月湖区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2058, 44, 243, '贵溪市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2059, 44, 243, '余江县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2060, 44, 244, '沈河区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2061, 44, 244, '皇姑区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2062, 44, 244, '和平区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2063, 44, 244, '大东区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2064, 44, 244, '铁西区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2065, 44, 244, '苏家屯区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2066, 44, 244, '东陵区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2067, 44, 244, '沈北新区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2068, 44, 244, '于洪区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2069, 44, 244, '浑南新区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2070, 44, 244, '新民市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2071, 44, 244, '辽中县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2072, 44, 244, '康平县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2073, 44, 244, '法库县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2074, 44, 245, '西岗区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2075, 44, 245, '中山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2076, 44, 245, '沙河口区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2077, 44, 245, '甘井子区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2078, 44, 245, '旅顺口区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2079, 44, 245, '金州区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2080, 44, 245, '开发区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2081, 44, 245, '瓦房店市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2082, 44, 245, '普兰店市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2083, 44, 245, '庄河市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2084, 44, 245, '长海县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2085, 44, 246, '铁东区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2086, 44, 246, '铁西区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2087, 44, 246, '立山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2088, 44, 246, '千山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2089, 44, 246, '岫岩', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2090, 44, 246, '海城市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2091, 44, 246, '台安县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2092, 44, 247, '本溪', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2093, 44, 247, '平山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2094, 44, 247, '明山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2095, 44, 247, '溪湖区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2096, 44, 247, '南芬区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2097, 44, 247, '桓仁', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2098, 44, 248, '双塔区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2099, 44, 248, '龙城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2100, 44, 248, '喀喇沁左翼蒙古族自治县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2101, 44, 248, '北票市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2102, 44, 248, '凌源市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2103, 44, 248, '朝阳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2104, 44, 248, '建平县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2105, 44, 249, '振兴区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2106, 44, 249, '元宝区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2107, 44, 249, '振安区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2108, 44, 249, '宽甸', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2109, 44, 249, '东港市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2110, 44, 249, '凤城市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2111, 44, 250, '顺城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2112, 44, 250, '新抚区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2113, 44, 250, '东洲区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2114, 44, 250, '望花区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2115, 44, 250, '清原', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2116, 44, 250, '新宾', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2117, 44, 250, '抚顺县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2118, 44, 251, '阜新', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2119, 44, 251, '海州区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2120, 44, 251, '新邱区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2121, 44, 251, '太平区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2122, 44, 251, '清河门区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2123, 44, 251, '细河区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2124, 44, 251, '彰武县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2125, 44, 252, '龙港区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2126, 44, 252, '南票区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2127, 44, 252, '连山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2128, 44, 252, '兴城市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2129, 44, 252, '绥中县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2130, 44, 252, '建昌县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2131, 44, 253, '太和区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2132, 44, 253, '古塔区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2133, 44, 253, '凌河区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2134, 44, 253, '凌海市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2135, 44, 253, '北镇市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2136, 44, 253, '黑山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2137, 44, 253, '义县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2138, 44, 254, '白塔区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2139, 44, 254, '文圣区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2140, 44, 254, '宏伟区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2141, 44, 254, '太子河区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2142, 44, 254, '弓长岭区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2143, 44, 254, '灯塔市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2144, 44, 254, '辽阳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2145, 44, 255, '双台子区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2146, 44, 255, '兴隆台区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2147, 44, 255, '大洼县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2148, 44, 255, '盘山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2149, 44, 256, '银州区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2150, 44, 256, '清河区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2151, 44, 256, '调兵山市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2152, 44, 256, '开原市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2153, 44, 256, '铁岭县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2154, 44, 256, '西丰县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2155, 44, 256, '昌图县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2156, 44, 257, '站前区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2157, 44, 257, '西市区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2158, 44, 257, '鲅鱼圈区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2159, 44, 257, '老边区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2160, 44, 257, '盖州市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2161, 44, 257, '大石桥市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2162, 44, 258, '回民区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2163, 44, 258, '玉泉区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2164, 44, 258, '新城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2165, 44, 258, '赛罕区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2166, 44, 258, '清水河县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2167, 44, 258, '土默特左旗', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2168, 44, 258, '托克托县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2169, 44, 258, '和林格尔县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2170, 44, 258, '武川县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2171, 44, 259, '阿拉善左旗', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2172, 44, 259, '阿拉善右旗', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2173, 44, 259, '额济纳旗', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2174, 44, 260, '临河区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2175, 44, 260, '五原县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2176, 44, 260, '磴口县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2177, 44, 260, '乌拉特前旗', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2178, 44, 260, '乌拉特中旗', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2179, 44, 260, '乌拉特后旗', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2180, 44, 260, '杭锦后旗', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2181, 44, 261, '昆都仑区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2182, 44, 261, '青山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2183, 44, 261, '东河区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2184, 44, 261, '九原区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2185, 44, 261, '石拐区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2186, 44, 261, '白云矿区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2187, 44, 261, '土默特右旗', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2188, 44, 261, '固阳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2189, 44, 261, '达尔罕茂明安联合旗', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2190, 44, 262, '红山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2191, 44, 262, '元宝山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2192, 44, 262, '松山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2193, 44, 262, '阿鲁科尔沁旗', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2194, 44, 262, '巴林左旗', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2195, 44, 262, '巴林右旗', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2196, 44, 262, '林西县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2197, 44, 262, '克什克腾旗', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2198, 44, 262, '翁牛特旗', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2199, 44, 262, '喀喇沁旗', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2200, 44, 262, '宁城县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2201, 44, 262, '敖汉旗', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2202, 44, 263, '东胜区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2203, 44, 263, '达拉特旗', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2204, 44, 263, '准格尔旗', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2205, 44, 263, '鄂托克前旗', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2206, 44, 263, '鄂托克旗', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2207, 44, 263, '杭锦旗', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2208, 44, 263, '乌审旗', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2209, 44, 263, '伊金霍洛旗', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2210, 44, 264, '海拉尔区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2211, 44, 264, '莫力达瓦', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2212, 44, 264, '满洲里市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2213, 44, 264, '牙克石市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2214, 44, 264, '扎兰屯市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2215, 44, 264, '额尔古纳市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2216, 44, 264, '根河市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2217, 44, 264, '阿荣旗', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2218, 44, 264, '鄂伦春自治旗', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2219, 44, 264, '鄂温克族自治旗', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2220, 44, 264, '陈巴尔虎旗', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2221, 44, 264, '新巴尔虎左旗', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2222, 44, 264, '新巴尔虎右旗', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2223, 44, 265, '科尔沁区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2224, 44, 265, '霍林郭勒市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2225, 44, 265, '科尔沁左翼中旗', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2226, 44, 265, '科尔沁左翼后旗', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2227, 44, 265, '开鲁县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2228, 44, 265, '库伦旗', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2229, 44, 265, '奈曼旗', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2230, 44, 265, '扎鲁特旗', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2231, 44, 266, '海勃湾区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2232, 44, 266, '乌达区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2233, 44, 266, '海南区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2234, 44, 267, '化德县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2235, 44, 267, '集宁区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2236, 44, 267, '丰镇市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2237, 44, 267, '卓资县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2238, 44, 267, '商都县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2239, 44, 267, '兴和县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2240, 44, 267, '凉城县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2241, 44, 267, '察哈尔右翼前旗', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2242, 44, 267, '察哈尔右翼中旗', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2243, 44, 267, '察哈尔右翼后旗', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2244, 44, 267, '四子王旗', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2245, 44, 268, '二连浩特市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2246, 44, 268, '锡林浩特市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2247, 44, 268, '阿巴嘎旗', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2248, 44, 268, '苏尼特左旗', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2249, 44, 268, '苏尼特右旗', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2250, 44, 268, '东乌珠穆沁旗', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2251, 44, 268, '西乌珠穆沁旗', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2252, 44, 268, '太仆寺旗', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2253, 44, 268, '镶黄旗', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2254, 44, 268, '正镶白旗', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2255, 44, 268, '正蓝旗', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2256, 44, 268, '多伦县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2257, 44, 269, '乌兰浩特市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2258, 44, 269, '阿尔山市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2259, 44, 269, '科尔沁右翼前旗', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2260, 44, 269, '科尔沁右翼中旗', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2261, 44, 269, '扎赉特旗', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2262, 44, 269, '突泉县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2263, 44, 270, '西夏区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2264, 44, 270, '金凤区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2265, 44, 270, '兴庆区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2266, 44, 270, '灵武市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2267, 44, 270, '永宁县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2268, 44, 270, '贺兰县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2269, 44, 271, '原州区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2270, 44, 271, '海原县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2271, 44, 271, '西吉县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2272, 44, 271, '隆德县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2273, 44, 271, '泾源县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2274, 44, 271, '彭阳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2275, 44, 272, '惠农县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2276, 44, 272, '大武口区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2277, 44, 272, '惠农区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2278, 44, 272, '陶乐县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2279, 44, 272, '平罗县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2280, 44, 273, '利通区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2281, 44, 273, '中卫县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2282, 44, 273, '青铜峡市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2283, 44, 273, '中宁县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2284, 44, 273, '盐池县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2285, 44, 273, '同心县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2286, 44, 274, '沙坡头区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2287, 44, 274, '海原县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2288, 44, 274, '中宁县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2289, 44, 275, '城中区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2290, 44, 275, '城东区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2291, 44, 275, '城西区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2292, 44, 275, '城北区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2293, 44, 275, '湟中县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2294, 44, 275, '湟源县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2295, 44, 275, '大通', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2296, 44, 276, '玛沁县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2297, 44, 276, '班玛县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2298, 44, 276, '甘德县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2299, 44, 276, '达日县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2300, 44, 276, '久治县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2301, 44, 276, '玛多县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2302, 44, 277, '海晏县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2303, 44, 277, '祁连县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2304, 44, 277, '刚察县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2305, 44, 277, '门源', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2306, 44, 278, '平安县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2307, 44, 278, '乐都县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2308, 44, 278, '民和', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2309, 44, 278, '互助', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2310, 44, 278, '化隆', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2311, 44, 278, '循化', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2312, 44, 279, '共和县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2313, 44, 279, '同德县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2314, 44, 279, '贵德县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2315, 44, 279, '兴海县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2316, 44, 279, '贵南县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2317, 44, 280, '德令哈市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2318, 44, 280, '格尔木市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2319, 44, 280, '乌兰县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2320, 44, 280, '都兰县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2321, 44, 280, '天峻县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2322, 44, 281, '同仁县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2323, 44, 281, '尖扎县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2324, 44, 281, '泽库县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2325, 44, 281, '河南蒙古族自治县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2326, 44, 282, '玉树县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2327, 44, 282, '杂多县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2328, 44, 282, '称多县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2329, 44, 282, '治多县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2330, 44, 282, '囊谦县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2331, 44, 282, '曲麻莱县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2332, 44, 283, '市中区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2333, 44, 283, '历下区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2334, 44, 283, '天桥区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2335, 44, 283, '槐荫区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2336, 44, 283, '历城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2337, 44, 283, '长清区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2338, 44, 283, '章丘市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2339, 44, 283, '平阴县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2340, 44, 283, '济阳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2341, 44, 283, '商河县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2342, 44, 284, '市南区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2343, 44, 284, '市北区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2344, 44, 284, '城阳区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2345, 44, 284, '四方区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2346, 44, 284, '李沧区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2347, 44, 284, '黄岛区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2348, 44, 284, '崂山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2349, 44, 284, '胶州市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2350, 44, 284, '即墨市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2351, 44, 284, '平度市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2352, 44, 284, '胶南市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2353, 44, 284, '莱西市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2354, 44, 285, '滨城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2355, 44, 285, '惠民县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2356, 44, 285, '阳信县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2357, 44, 285, '无棣县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2358, 44, 285, '沾化县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2359, 44, 285, '博兴县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2360, 44, 285, '邹平县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2361, 44, 286, '德城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2362, 44, 286, '陵县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2363, 44, 286, '乐陵市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2364, 44, 286, '禹城市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2365, 44, 286, '宁津县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2366, 44, 286, '庆云县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2367, 44, 286, '临邑县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2368, 44, 286, '齐河县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2369, 44, 286, '平原县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2370, 44, 286, '夏津县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2371, 44, 286, '武城县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2372, 44, 287, '东营区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2373, 44, 287, '河口区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2374, 44, 287, '垦利县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2375, 44, 287, '利津县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2376, 44, 287, '广饶县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2377, 44, 288, '牡丹区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2378, 44, 288, '曹县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2379, 44, 288, '单县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2380, 44, 288, '成武县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2381, 44, 288, '巨野县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2382, 44, 288, '郓城县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2383, 44, 288, '鄄城县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2384, 44, 288, '定陶县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2385, 44, 288, '东明县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2386, 44, 289, '市中区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2387, 44, 289, '任城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2388, 44, 289, '曲阜市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2389, 44, 289, '兖州市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2390, 44, 289, '邹城市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2391, 44, 289, '微山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2392, 44, 289, '鱼台县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2393, 44, 289, '金乡县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2394, 44, 289, '嘉祥县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2395, 44, 289, '汶上县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2396, 44, 289, '泗水县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2397, 44, 289, '梁山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2398, 44, 290, '莱城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2399, 44, 290, '钢城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2400, 44, 291, '东昌府区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2401, 44, 291, '临清市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2402, 44, 291, '阳谷县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2403, 44, 291, '莘县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2404, 44, 291, '茌平县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2405, 44, 291, '东阿县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2406, 44, 291, '冠县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2407, 44, 291, '高唐县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2408, 44, 292, '兰山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2409, 44, 292, '罗庄区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2410, 44, 292, '河东区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2411, 44, 292, '沂南县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2412, 44, 292, '郯城县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2413, 44, 292, '沂水县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2414, 44, 292, '苍山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2415, 44, 292, '费县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2416, 44, 292, '平邑县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2417, 44, 292, '莒南县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2418, 44, 292, '蒙阴县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2419, 44, 292, '临沭县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2420, 44, 293, '东港区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2421, 44, 293, '岚山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2422, 44, 293, '五莲县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2423, 44, 293, '莒县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2424, 44, 294, '泰山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2425, 44, 294, '岱岳区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2426, 44, 294, '新泰市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2427, 44, 294, '肥城市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2428, 44, 294, '宁阳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2429, 44, 294, '东平县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2430, 44, 295, '荣成市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2431, 44, 295, '乳山市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2432, 44, 295, '环翠区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2433, 44, 295, '文登市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2434, 44, 296, '潍城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2435, 44, 296, '寒亭区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2436, 44, 296, '坊子区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2437, 44, 296, '奎文区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2438, 44, 296, '青州市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2439, 44, 296, '诸城市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2440, 44, 296, '寿光市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2441, 44, 296, '安丘市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2442, 44, 296, '高密市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2443, 44, 296, '昌邑市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2444, 44, 296, '临朐县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2445, 44, 296, '昌乐县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2446, 44, 297, '芝罘区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2447, 44, 297, '福山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2448, 44, 297, '牟平区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2449, 44, 297, '莱山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2450, 44, 297, '开发区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2451, 44, 297, '龙口市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2452, 44, 297, '莱阳市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2453, 44, 297, '莱州市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2454, 44, 297, '蓬莱市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2455, 44, 297, '招远市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2456, 44, 297, '栖霞市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2457, 44, 297, '海阳市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2458, 44, 297, '长岛县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2459, 44, 298, '市中区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2460, 44, 298, '山亭区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2461, 44, 298, '峄城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2462, 44, 298, '台儿庄区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2463, 44, 298, '薛城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2464, 44, 298, '滕州市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2465, 44, 299, '张店区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2466, 44, 299, '临淄区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2467, 44, 299, '淄川区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2468, 44, 299, '博山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2469, 44, 299, '周村区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2470, 44, 299, '桓台县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2471, 44, 299, '高青县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2472, 44, 299, '沂源县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2473, 44, 300, '杏花岭区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2474, 44, 300, '小店区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2475, 44, 300, '迎泽区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2476, 44, 300, '尖草坪区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2477, 44, 300, '万柏林区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2478, 44, 300, '晋源区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2479, 44, 300, '高新开发区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2480, 44, 300, '民营经济开发区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2481, 44, 300, '经济技术开发区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2482, 44, 300, '清徐县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2483, 44, 300, '阳曲县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2484, 44, 300, '娄烦县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2485, 44, 300, '古交市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2486, 44, 301, '城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2487, 44, 301, '郊区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2488, 44, 301, '沁县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2489, 44, 301, '潞城市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2490, 44, 301, '长治县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2491, 44, 301, '襄垣县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2492, 44, 301, '屯留县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2493, 44, 301, '平顺县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2494, 44, 301, '黎城县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2495, 44, 301, '壶关县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2496, 44, 301, '长子县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2497, 44, 301, '武乡县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2498, 44, 301, '沁源县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2499, 44, 302, '城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2500, 44, 302, '矿区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2501, 44, 302, '南郊区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2502, 44, 302, '新荣区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2503, 44, 302, '阳高县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2504, 44, 302, '天镇县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2505, 44, 302, '广灵县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2506, 44, 302, '灵丘县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2507, 44, 302, '浑源县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2508, 44, 302, '左云县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2509, 44, 302, '大同县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2510, 44, 303, '城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2511, 44, 303, '高平市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2512, 44, 303, '沁水县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2513, 44, 303, '阳城县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2514, 44, 303, '陵川县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2515, 44, 303, '泽州县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2516, 44, 304, '榆次区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2517, 44, 304, '介休市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2518, 44, 304, '榆社县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2519, 44, 304, '左权县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2520, 44, 304, '和顺县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2521, 44, 304, '昔阳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2522, 44, 304, '寿阳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2523, 44, 304, '太谷县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2524, 44, 304, '祁县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2525, 44, 304, '平遥县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2526, 44, 304, '灵石县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2527, 44, 305, '尧都区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2528, 44, 305, '侯马市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2529, 44, 305, '霍州市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2530, 44, 305, '曲沃县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2531, 44, 305, '翼城县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2532, 44, 305, '襄汾县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2533, 44, 305, '洪洞县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2534, 44, 305, '吉县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2535, 44, 305, '安泽县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2536, 44, 305, '浮山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2537, 44, 305, '古县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2538, 44, 305, '乡宁县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2539, 44, 305, '大宁县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2540, 44, 305, '隰县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2541, 44, 305, '永和县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2542, 44, 305, '蒲县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2543, 44, 305, '汾西县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2544, 44, 306, '离石市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2545, 44, 306, '离石区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2546, 44, 306, '孝义市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2547, 44, 306, '汾阳市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2548, 44, 306, '文水县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2549, 44, 306, '交城县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2550, 44, 306, '兴县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2551, 44, 306, '临县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2552, 44, 306, '柳林县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2553, 44, 306, '石楼县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2554, 44, 306, '岚县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2555, 44, 306, '方山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2556, 44, 306, '中阳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2557, 44, 306, '交口县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2558, 44, 307, '朔城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2559, 44, 307, '平鲁区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2560, 44, 307, '山阴县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2561, 44, 307, '应县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2562, 44, 307, '右玉县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2563, 44, 307, '怀仁县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2564, 44, 308, '忻府区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2565, 44, 308, '原平市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2566, 44, 308, '定襄县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2567, 44, 308, '五台县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2568, 44, 308, '代县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2569, 44, 308, '繁峙县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2570, 44, 308, '宁武县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2571, 44, 308, '静乐县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2572, 44, 308, '神池县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2573, 44, 308, '五寨县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2574, 44, 308, '岢岚县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2575, 44, 308, '河曲县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2576, 44, 308, '保德县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2577, 44, 308, '偏关县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2578, 44, 309, '城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2579, 44, 309, '矿区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2580, 44, 309, '郊区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2581, 44, 309, '平定县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2582, 44, 309, '盂县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2583, 44, 310, '盐湖区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2584, 44, 310, '永济市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2585, 44, 310, '河津市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2586, 44, 310, '临猗县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2587, 44, 310, '万荣县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2588, 44, 310, '闻喜县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2589, 44, 310, '稷山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2590, 44, 310, '新绛县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2591, 44, 310, '绛县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2592, 44, 310, '垣曲县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2593, 44, 310, '夏县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2594, 44, 310, '平陆县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2595, 44, 310, '芮城县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2596, 44, 311, '莲湖区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2597, 44, 311, '新城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2598, 44, 311, '碑林区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2599, 44, 311, '雁塔区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2600, 44, 311, '灞桥区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2601, 44, 311, '未央区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2602, 44, 311, '阎良区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2603, 44, 311, '临潼区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2604, 44, 311, '长安区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2605, 44, 311, '蓝田县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2606, 44, 311, '周至县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2607, 44, 311, '户县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2608, 44, 311, '高陵县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2609, 44, 312, '汉滨区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2610, 44, 312, '汉阴县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2611, 44, 312, '石泉县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2612, 44, 312, '宁陕县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2613, 44, 312, '紫阳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2614, 44, 312, '岚皋县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2615, 44, 312, '平利县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2616, 44, 312, '镇坪县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2617, 44, 312, '旬阳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2618, 44, 312, '白河县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2619, 44, 313, '陈仓区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2620, 44, 313, '渭滨区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2621, 44, 313, '金台区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2622, 44, 313, '凤翔县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2623, 44, 313, '岐山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2624, 44, 313, '扶风县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2625, 44, 313, '眉县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2626, 44, 313, '陇县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2627, 44, 313, '千阳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2628, 44, 313, '麟游县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2629, 44, 313, '凤县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2630, 44, 313, '太白县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2631, 44, 314, '汉台区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2632, 44, 314, '南郑县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2633, 44, 314, '城固县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2634, 44, 314, '洋县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2635, 44, 314, '西乡县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2636, 44, 314, '勉县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2637, 44, 314, '宁强县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2638, 44, 314, '略阳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2639, 44, 314, '镇巴县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2640, 44, 314, '留坝县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2641, 44, 314, '佛坪县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2642, 44, 315, '商州区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2643, 44, 315, '洛南县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2644, 44, 315, '丹凤县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2645, 44, 315, '商南县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2646, 44, 315, '山阳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2647, 44, 315, '镇安县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2648, 44, 315, '柞水县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2649, 44, 316, '耀州区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2650, 44, 316, '王益区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2651, 44, 316, '印台区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2652, 44, 316, '宜君县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2653, 44, 317, '临渭区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2654, 44, 317, '韩城市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2655, 44, 317, '华阴市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2656, 44, 317, '华县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2657, 44, 317, '潼关县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2658, 44, 317, '大荔县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2659, 44, 317, '合阳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2660, 44, 317, '澄城县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2661, 44, 317, '蒲城县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2662, 44, 317, '白水县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2663, 44, 317, '富平县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2664, 44, 318, '秦都区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2665, 44, 318, '渭城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2666, 44, 318, '杨陵区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2667, 44, 318, '兴平市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2668, 44, 318, '三原县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2669, 44, 318, '泾阳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2670, 44, 318, '乾县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2671, 44, 318, '礼泉县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2672, 44, 318, '永寿县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2673, 44, 318, '彬县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2674, 44, 318, '长武县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2675, 44, 318, '旬邑县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2676, 44, 318, '淳化县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2677, 44, 318, '武功县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2678, 44, 319, '吴起县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2679, 44, 319, '宝塔区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2680, 44, 319, '延长县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2681, 44, 319, '延川县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2682, 44, 319, '子长县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2683, 44, 319, '安塞县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2684, 44, 319, '志丹县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2685, 44, 319, '甘泉县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2686, 44, 319, '富县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2687, 44, 319, '洛川县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2688, 44, 319, '宜川县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2689, 44, 319, '黄龙县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2690, 44, 319, '黄陵县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2691, 44, 320, '榆阳区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2692, 44, 320, '神木县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2693, 44, 320, '府谷县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2694, 44, 320, '横山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2695, 44, 320, '靖边县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2696, 44, 320, '定边县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2697, 44, 320, '绥德县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2698, 44, 320, '米脂县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2699, 44, 320, '佳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2700, 44, 320, '吴堡县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2701, 44, 320, '清涧县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2702, 44, 320, '子洲县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2703, 44, 321, '长宁区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2704, 44, 321, '闸北区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2705, 44, 321, '闵行区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2706, 44, 321, '徐汇区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2707, 44, 321, '浦东新区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2708, 44, 321, '杨浦区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2709, 44, 321, '普陀区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2710, 44, 321, '静安区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2711, 44, 321, '卢湾区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2712, 44, 321, '虹口区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2713, 44, 321, '黄浦区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2714, 44, 321, '南汇区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2715, 44, 321, '松江区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2716, 44, 321, '嘉定区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2717, 44, 321, '宝山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2718, 44, 321, '青浦区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2719, 44, 321, '金山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2720, 44, 321, '奉贤区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2721, 44, 321, '崇明县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2722, 44, 322, '青羊区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2723, 44, 322, '锦江区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2724, 44, 322, '金牛区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2725, 44, 322, '武侯区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2726, 44, 322, '成华区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2727, 44, 322, '龙泉驿区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2728, 44, 322, '青白江区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2729, 44, 322, '新都区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2730, 44, 322, '温江区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2731, 44, 322, '高新区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2732, 44, 322, '高新西区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2733, 44, 322, '都江堰市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2734, 44, 322, '彭州市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2735, 44, 322, '邛崃市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2736, 44, 322, '崇州市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2737, 44, 322, '金堂县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2738, 44, 322, '双流县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2739, 44, 322, '郫县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2740, 44, 322, '大邑县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2741, 44, 322, '蒲江县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2742, 44, 322, '新津县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2743, 44, 322, '都江堰市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2744, 44, 322, '彭州市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2745, 44, 322, '邛崃市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2746, 44, 322, '崇州市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2747, 44, 322, '金堂县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2748, 44, 322, '双流县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2749, 44, 322, '郫县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2750, 44, 322, '大邑县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2751, 44, 322, '蒲江县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2752, 44, 322, '新津县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2753, 44, 323, '涪城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2754, 44, 323, '游仙区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2755, 44, 323, '江油市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2756, 44, 323, '盐亭县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2757, 44, 323, '三台县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2758, 44, 323, '平武县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2759, 44, 323, '安县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2760, 44, 323, '梓潼县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2761, 44, 323, '北川县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2762, 44, 324, '马尔康县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2763, 44, 324, '汶川县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2764, 44, 324, '理县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2765, 44, 324, '茂县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2766, 44, 324, '松潘县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2767, 44, 324, '九寨沟县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2768, 44, 324, '金川县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2769, 44, 324, '小金县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2770, 44, 324, '黑水县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2771, 44, 324, '壤塘县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2772, 44, 324, '阿坝县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2773, 44, 324, '若尔盖县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2774, 44, 324, '红原县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2775, 44, 325, '巴州区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2776, 44, 325, '通江县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2777, 44, 325, '南江县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2778, 44, 325, '平昌县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2779, 44, 326, '通川区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2780, 44, 326, '万源市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2781, 44, 326, '达县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2782, 44, 326, '宣汉县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2783, 44, 326, '开江县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2784, 44, 326, '大竹县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2785, 44, 326, '渠县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2786, 44, 327, '旌阳区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2787, 44, 327, '广汉市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2788, 44, 327, '什邡市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2789, 44, 327, '绵竹市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2790, 44, 327, '罗江县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2791, 44, 327, '中江县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2792, 44, 328, '康定县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2793, 44, 328, '丹巴县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2794, 44, 328, '泸定县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2795, 44, 328, '炉霍县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2796, 44, 328, '九龙县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2797, 44, 328, '甘孜县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2798, 44, 328, '雅江县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2799, 44, 328, '新龙县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2800, 44, 328, '道孚县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2801, 44, 328, '白玉县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2802, 44, 328, '理塘县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2803, 44, 328, '德格县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2804, 44, 328, '乡城县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2805, 44, 328, '石渠县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2806, 44, 328, '稻城县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2807, 44, 328, '色达县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2808, 44, 328, '巴塘县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2809, 44, 328, '得荣县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2810, 44, 329, '广安区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2811, 44, 329, '华蓥市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2812, 44, 329, '岳池县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2813, 44, 329, '武胜县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2814, 44, 329, '邻水县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2815, 44, 330, '利州区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2816, 44, 330, '元坝区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2817, 44, 330, '朝天区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2818, 44, 330, '旺苍县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2819, 44, 330, '青川县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2820, 44, 330, '剑阁县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2821, 44, 330, '苍溪县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2822, 44, 331, '峨眉山市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2823, 44, 331, '乐山市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2824, 44, 331, '犍为县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2825, 44, 331, '井研县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2826, 44, 331, '夹江县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2827, 44, 331, '沐川县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2828, 44, 331, '峨边', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2829, 44, 331, '马边', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2830, 44, 332, '西昌市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2831, 44, 332, '盐源县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2832, 44, 332, '德昌县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2833, 44, 332, '会理县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2834, 44, 332, '会东县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2835, 44, 332, '宁南县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2836, 44, 332, '普格县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2837, 44, 332, '布拖县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2838, 44, 332, '金阳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2839, 44, 332, '昭觉县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2840, 44, 332, '喜德县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2841, 44, 332, '冕宁县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2842, 44, 332, '越西县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2843, 44, 332, '甘洛县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2844, 44, 332, '美姑县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2845, 44, 332, '雷波县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2846, 44, 332, '木里', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2847, 44, 333, '东坡区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2848, 44, 333, '仁寿县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2849, 44, 333, '彭山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2850, 44, 333, '洪雅县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2851, 44, 333, '丹棱县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2852, 44, 333, '青神县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2853, 44, 334, '阆中市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2854, 44, 334, '南部县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2855, 44, 334, '营山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2856, 44, 334, '蓬安县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2857, 44, 334, '仪陇县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2858, 44, 334, '顺庆区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2859, 44, 334, '高坪区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2860, 44, 334, '嘉陵区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2861, 44, 334, '西充县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2862, 44, 335, '市中区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2863, 44, 335, '东兴区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2864, 44, 335, '威远县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2865, 44, 335, '资中县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2866, 44, 335, '隆昌县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2867, 44, 336, '东  区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2868, 44, 336, '西  区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2869, 44, 336, '仁和区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2870, 44, 336, '米易县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2871, 44, 336, '盐边县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2872, 44, 337, '船山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2873, 44, 337, '安居区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2874, 44, 337, '蓬溪县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2875, 44, 337, '射洪县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2876, 44, 337, '大英县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2877, 44, 338, '雨城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2878, 44, 338, '名山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2879, 44, 338, '荥经县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2880, 44, 338, '汉源县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2881, 44, 338, '石棉县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2882, 44, 338, '天全县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2883, 44, 338, '芦山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2884, 44, 338, '宝兴县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2885, 44, 339, '翠屏区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2886, 44, 339, '宜宾县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2887, 44, 339, '南溪县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2888, 44, 339, '江安县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2889, 44, 339, '长宁县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2890, 44, 339, '高县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2891, 44, 339, '珙县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2892, 44, 339, '筠连县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2893, 44, 339, '兴文县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2894, 44, 339, '屏山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2895, 44, 340, '雁江区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2896, 44, 340, '简阳市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2897, 44, 340, '安岳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2898, 44, 340, '乐至县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2899, 44, 341, '大安区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2900, 44, 341, '自流井区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2901, 44, 341, '贡井区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2902, 44, 341, '沿滩区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2903, 44, 341, '荣县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2904, 44, 341, '富顺县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2905, 44, 342, '江阳区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2906, 44, 342, '纳溪区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2907, 44, 342, '龙马潭区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2908, 44, 342, '泸县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2909, 44, 342, '合江县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2910, 44, 342, '叙永县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2911, 44, 342, '古蔺县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2912, 44, 343, '和平区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2913, 44, 343, '河西区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2914, 44, 343, '南开区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2915, 44, 343, '河北区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2916, 44, 343, '河东区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2917, 44, 343, '红桥区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2918, 44, 343, '东丽区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2919, 44, 343, '津南区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2920, 44, 343, '西青区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2921, 44, 343, '北辰区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2922, 44, 343, '塘沽区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2923, 44, 343, '汉沽区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2924, 44, 343, '大港区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2925, 44, 343, '武清区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2926, 44, 343, '宝坻区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2927, 44, 343, '经济开发区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2928, 44, 343, '宁河县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2929, 44, 343, '静海县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2930, 44, 343, '蓟县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2931, 44, 344, '城关区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2932, 44, 344, '林周县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2933, 44, 344, '当雄县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2934, 44, 344, '尼木县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2935, 44, 344, '曲水县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2936, 44, 344, '堆龙德庆县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2937, 44, 344, '达孜县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2938, 44, 344, '墨竹工卡县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2939, 44, 345, '噶尔县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2940, 44, 345, '普兰县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2941, 44, 345, '札达县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2942, 44, 345, '日土县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2943, 44, 345, '革吉县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2944, 44, 345, '改则县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2945, 44, 345, '措勤县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2946, 44, 346, '昌都县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2947, 44, 346, '江达县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2948, 44, 346, '贡觉县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2949, 44, 346, '类乌齐县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2950, 44, 346, '丁青县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2951, 44, 346, '察雅县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2952, 44, 346, '八宿县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2953, 44, 346, '左贡县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2954, 44, 346, '芒康县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2955, 44, 346, '洛隆县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2956, 44, 346, '边坝县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2957, 44, 347, '林芝县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2958, 44, 347, '工布江达县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2959, 44, 347, '米林县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2960, 44, 347, '墨脱县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2961, 44, 347, '波密县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2962, 44, 347, '察隅县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2963, 44, 347, '朗县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2964, 44, 348, '那曲县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2965, 44, 348, '嘉黎县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2966, 44, 348, '比如县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2967, 44, 348, '聂荣县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2968, 44, 348, '安多县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2969, 44, 348, '申扎县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2970, 44, 348, '索县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2971, 44, 348, '班戈县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2972, 44, 348, '巴青县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2973, 44, 348, '尼玛县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2974, 44, 349, '日喀则市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2975, 44, 349, '南木林县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2976, 44, 349, '江孜县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2977, 44, 349, '定日县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2978, 44, 349, '萨迦县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2979, 44, 349, '拉孜县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2980, 44, 349, '昂仁县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2981, 44, 349, '谢通门县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2982, 44, 349, '白朗县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2983, 44, 349, '仁布县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2984, 44, 349, '康马县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2985, 44, 349, '定结县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2986, 44, 349, '仲巴县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2987, 44, 349, '亚东县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2988, 44, 349, '吉隆县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2989, 44, 349, '聂拉木县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2990, 44, 349, '萨嘎县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2991, 44, 349, '岗巴县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2992, 44, 350, '乃东县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2993, 44, 350, '扎囊县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2994, 44, 350, '贡嘎县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2995, 44, 350, '桑日县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2996, 44, 350, '琼结县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2997, 44, 350, '曲松县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2998, 44, 350, '措美县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (2999, 44, 350, '洛扎县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3000, 44, 350, '加查县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3001, 44, 350, '隆子县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3002, 44, 350, '错那县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3003, 44, 350, '浪卡子县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3004, 44, 351, '天山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3005, 44, 351, '沙依巴克区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3006, 44, 351, '新市区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3007, 44, 351, '水磨沟区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3008, 44, 351, '头屯河区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3009, 44, 351, '达坂城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3010, 44, 351, '米东区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3011, 44, 351, '乌鲁木齐县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3012, 44, 352, '阿克苏市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3013, 44, 352, '温宿县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3014, 44, 352, '库车县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3015, 44, 352, '沙雅县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3016, 44, 352, '新和县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3017, 44, 352, '拜城县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3018, 44, 352, '乌什县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3019, 44, 352, '阿瓦提县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3020, 44, 352, '柯坪县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3021, 44, 353, '阿拉尔市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3022, 44, 354, '库尔勒市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3023, 44, 354, '轮台县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3024, 44, 354, '尉犁县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3025, 44, 354, '若羌县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3026, 44, 354, '且末县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3027, 44, 354, '焉耆', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3028, 44, 354, '和静县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3029, 44, 354, '和硕县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3030, 44, 354, '博湖县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3031, 44, 355, '博乐市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3032, 44, 355, '精河县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3033, 44, 355, '温泉县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3034, 44, 356, '呼图壁县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3035, 44, 356, '米泉市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3036, 44, 356, '昌吉市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3037, 44, 356, '阜康市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3038, 44, 356, '玛纳斯县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3039, 44, 356, '奇台县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3040, 44, 356, '吉木萨尔县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3041, 44, 356, '木垒', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3042, 44, 357, '哈密市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3043, 44, 357, '伊吾县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3044, 44, 357, '巴里坤', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3045, 44, 358, '和田市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3046, 44, 358, '和田县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3047, 44, 358, '墨玉县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3048, 44, 358, '皮山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3049, 44, 358, '洛浦县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3050, 44, 358, '策勒县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3051, 44, 358, '于田县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3052, 44, 358, '民丰县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3053, 44, 359, '喀什市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3054, 44, 359, '疏附县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3055, 44, 359, '疏勒县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3056, 44, 359, '英吉沙县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3057, 44, 359, '泽普县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3058, 44, 359, '莎车县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3059, 44, 359, '叶城县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3060, 44, 359, '麦盖提县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3061, 44, 359, '岳普湖县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3062, 44, 359, '伽师县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3063, 44, 359, '巴楚县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3064, 44, 359, '塔什库尔干', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3065, 44, 360, '克拉玛依市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3066, 44, 361, '阿图什市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3067, 44, 361, '阿克陶县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3068, 44, 361, '阿合奇县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3069, 44, 361, '乌恰县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3070, 44, 362, '石河子市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3071, 44, 363, '图木舒克市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3072, 44, 364, '吐鲁番市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3073, 44, 364, '鄯善县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3074, 44, 364, '托克逊县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3075, 44, 365, '五家渠市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3076, 44, 366, '阿勒泰市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3077, 44, 366, '布克赛尔', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3078, 44, 366, '伊宁市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3079, 44, 366, '布尔津县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3080, 44, 366, '奎屯市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3081, 44, 366, '乌苏市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3082, 44, 366, '额敏县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3083, 44, 366, '富蕴县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3084, 44, 366, '伊宁县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3085, 44, 366, '福海县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3086, 44, 366, '霍城县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3087, 44, 366, '沙湾县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3088, 44, 366, '巩留县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3089, 44, 366, '哈巴河县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3090, 44, 366, '托里县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3091, 44, 366, '青河县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3092, 44, 366, '新源县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3093, 44, 366, '裕民县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3094, 44, 366, '和布克赛尔', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3095, 44, 366, '吉木乃县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3096, 44, 366, '昭苏县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3097, 44, 366, '特克斯县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3098, 44, 366, '尼勒克县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3099, 44, 366, '察布查尔', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3100, 44, 367, '盘龙区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3101, 44, 367, '五华区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3102, 44, 367, '官渡区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3103, 44, 367, '西山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3104, 44, 367, '东川区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3105, 44, 367, '安宁市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3106, 44, 367, '呈贡县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3107, 44, 367, '晋宁县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3108, 44, 367, '富民县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3109, 44, 367, '宜良县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3110, 44, 367, '嵩明县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3111, 44, 367, '石林县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3112, 44, 367, '禄劝', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3113, 44, 367, '寻甸', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3114, 44, 368, '兰坪', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3115, 44, 368, '泸水县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3116, 44, 368, '福贡县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3117, 44, 368, '贡山', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3118, 44, 369, '宁洱', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3119, 44, 369, '思茅区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3120, 44, 369, '墨江', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3121, 44, 369, '景东', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3122, 44, 369, '景谷', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3123, 44, 369, '镇沅', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3124, 44, 369, '江城', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3125, 44, 369, '孟连', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3126, 44, 369, '澜沧', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3127, 44, 369, '西盟', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3128, 44, 370, '古城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3129, 44, 370, '宁蒗', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3130, 44, 370, '玉龙', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3131, 44, 370, '永胜县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3132, 44, 370, '华坪县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3133, 44, 371, '隆阳区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3134, 44, 371, '施甸县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3135, 44, 371, '腾冲县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3136, 44, 371, '龙陵县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3137, 44, 371, '昌宁县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3138, 44, 372, '楚雄市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3139, 44, 372, '双柏县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3140, 44, 372, '牟定县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3141, 44, 372, '南华县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3142, 44, 372, '姚安县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3143, 44, 372, '大姚县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3144, 44, 372, '永仁县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3145, 44, 372, '元谋县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3146, 44, 372, '武定县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3147, 44, 372, '禄丰县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3148, 44, 373, '大理市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3149, 44, 373, '祥云县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3150, 44, 373, '宾川县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3151, 44, 373, '弥渡县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3152, 44, 373, '永平县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3153, 44, 373, '云龙县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3154, 44, 373, '洱源县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3155, 44, 373, '剑川县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3156, 44, 373, '鹤庆县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3157, 44, 373, '漾濞', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3158, 44, 373, '南涧', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3159, 44, 373, '巍山', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3160, 44, 374, '潞西市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3161, 44, 374, '瑞丽市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3162, 44, 374, '梁河县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3163, 44, 374, '盈江县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3164, 44, 374, '陇川县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3165, 44, 375, '香格里拉县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3166, 44, 375, '德钦县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3167, 44, 375, '维西', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3168, 44, 376, '泸西县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3169, 44, 376, '蒙自县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3170, 44, 376, '个旧市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3171, 44, 376, '开远市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3172, 44, 376, '绿春县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3173, 44, 376, '建水县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3174, 44, 376, '石屏县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3175, 44, 376, '弥勒县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3176, 44, 376, '元阳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3177, 44, 376, '红河县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3178, 44, 376, '金平', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3179, 44, 376, '河口', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3180, 44, 376, '屏边', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3181, 44, 377, '临翔区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3182, 44, 377, '凤庆县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3183, 44, 377, '云县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3184, 44, 377, '永德县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3185, 44, 377, '镇康县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3186, 44, 377, '双江', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3187, 44, 377, '耿马', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3188, 44, 377, '沧源', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3189, 44, 378, '麒麟区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3190, 44, 378, '宣威市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3191, 44, 378, '马龙县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3192, 44, 378, '陆良县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3193, 44, 378, '师宗县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3194, 44, 378, '罗平县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3195, 44, 378, '富源县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3196, 44, 378, '会泽县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3197, 44, 378, '沾益县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3198, 44, 379, '文山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3199, 44, 379, '砚山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3200, 44, 379, '西畴县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3201, 44, 379, '麻栗坡县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3202, 44, 379, '马关县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3203, 44, 379, '丘北县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3204, 44, 379, '广南县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3205, 44, 379, '富宁县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3206, 44, 380, '景洪市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3207, 44, 380, '勐海县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3208, 44, 380, '勐腊县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3209, 44, 381, '红塔区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3210, 44, 381, '江川县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3211, 44, 381, '澄江县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3212, 44, 381, '通海县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3213, 44, 381, '华宁县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3214, 44, 381, '易门县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3215, 44, 381, '峨山', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3216, 44, 381, '新平', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3217, 44, 381, '元江', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3218, 44, 382, '昭阳区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3219, 44, 382, '鲁甸县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3220, 44, 382, '巧家县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3221, 44, 382, '盐津县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3222, 44, 382, '大关县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3223, 44, 382, '永善县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3224, 44, 382, '绥江县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3225, 44, 382, '镇雄县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3226, 44, 382, '彝良县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3227, 44, 382, '威信县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3228, 44, 382, '水富县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3229, 44, 383, '西湖区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3230, 44, 383, '上城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3231, 44, 383, '下城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3232, 44, 383, '拱墅区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3233, 44, 383, '滨江区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3234, 44, 383, '江干区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3235, 44, 383, '萧山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3236, 44, 383, '余杭区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3237, 44, 383, '市郊', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3238, 44, 383, '建德市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3239, 44, 383, '富阳市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3240, 44, 383, '临安市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3241, 44, 383, '桐庐县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3242, 44, 383, '淳安县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3243, 44, 384, '吴兴区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3244, 44, 384, '南浔区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3245, 44, 384, '德清县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3246, 44, 384, '长兴县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3247, 44, 384, '安吉县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3248, 44, 385, '南湖区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3249, 44, 385, '秀洲区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3250, 44, 385, '海宁市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3251, 44, 385, '嘉善县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3252, 44, 385, '平湖市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3253, 44, 385, '桐乡市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3254, 44, 385, '海盐县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3255, 44, 386, '婺城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3256, 44, 386, '金东区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3257, 44, 386, '兰溪市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3258, 44, 386, '市区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3259, 44, 386, '佛堂镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3260, 44, 386, '上溪镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3261, 44, 386, '义亭镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3262, 44, 386, '大陈镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3263, 44, 386, '苏溪镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3264, 44, 386, '赤岸镇', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3265, 44, 386, '东阳市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3266, 44, 386, '永康市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3267, 44, 386, '武义县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3268, 44, 386, '浦江县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3269, 44, 386, '磐安县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3270, 44, 387, '莲都区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3271, 44, 387, '龙泉市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3272, 44, 387, '青田县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3273, 44, 387, '缙云县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3274, 44, 387, '遂昌县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3275, 44, 387, '松阳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3276, 44, 387, '云和县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3277, 44, 387, '庆元县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3278, 44, 387, '景宁', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3279, 44, 388, '海曙区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3280, 44, 388, '江东区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3281, 44, 388, '江北区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3282, 44, 388, '镇海区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3283, 44, 388, '北仑区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3284, 44, 388, '鄞州区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3285, 44, 388, '余姚市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3286, 44, 388, '慈溪市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3287, 44, 388, '奉化市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3288, 44, 388, '象山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3289, 44, 388, '宁海县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3290, 44, 389, '越城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3291, 44, 389, '上虞市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3292, 44, 389, '嵊州市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3293, 44, 389, '绍兴县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3294, 44, 389, '新昌县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3295, 44, 389, '诸暨市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3296, 44, 390, '椒江区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3297, 44, 390, '黄岩区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3298, 44, 390, '路桥区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3299, 44, 390, '温岭市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3300, 44, 390, '临海市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3301, 44, 390, '玉环县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3302, 44, 390, '三门县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3303, 44, 390, '天台县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3304, 44, 390, '仙居县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3305, 44, 391, '鹿城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3306, 44, 391, '龙湾区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3307, 44, 391, '瓯海区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3308, 44, 391, '瑞安市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3309, 44, 391, '乐清市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3310, 44, 391, '洞头县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3311, 44, 391, '永嘉县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3312, 44, 391, '平阳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3313, 44, 391, '苍南县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3314, 44, 391, '文成县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3315, 44, 391, '泰顺县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3316, 44, 392, '定海区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3317, 44, 392, '普陀区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3318, 44, 392, '岱山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3319, 44, 392, '嵊泗县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3320, 44, 393, '衢州市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3321, 44, 393, '江山市', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3322, 44, 393, '常山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3323, 44, 393, '开化县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3324, 44, 393, '龙游县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3325, 44, 394, '合川区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3326, 44, 394, '江津区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3327, 44, 394, '南川区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3328, 44, 394, '永川区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3329, 44, 394, '南岸区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3330, 44, 394, '渝北区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3331, 44, 394, '万盛区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3332, 44, 394, '大渡口区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3333, 44, 394, '万州区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3334, 44, 394, '北碚区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3335, 44, 394, '沙坪坝区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3336, 44, 394, '巴南区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3337, 44, 394, '涪陵区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3338, 44, 394, '江北区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3339, 44, 394, '九龙坡区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3340, 44, 394, '渝中区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3341, 44, 394, '黔江开发区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3342, 44, 394, '长寿区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3343, 44, 394, '双桥区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3344, 44, 394, '綦江县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3345, 44, 394, '潼南县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3346, 44, 394, '铜梁县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3347, 44, 394, '大足县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3348, 44, 394, '荣昌县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3349, 44, 394, '璧山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3350, 44, 394, '垫江县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3351, 44, 394, '武隆县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3352, 44, 394, '丰都县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3353, 44, 394, '城口县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3354, 44, 394, '梁平县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3355, 44, 394, '开县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3356, 44, 394, '巫溪县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3357, 44, 394, '巫山县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3358, 44, 394, '奉节县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3359, 44, 394, '云阳县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3360, 44, 394, '忠县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3361, 44, 394, '石柱', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3362, 44, 394, '彭水', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3363, 44, 394, '酉阳', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3364, 44, 394, '秀山', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3365, 44, 395, '沙田区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3366, 44, 395, '东区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3367, 44, 395, '观塘区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3368, 44, 395, '黄大仙区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3369, 44, 395, '九龙城区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3370, 44, 395, '屯门区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3371, 44, 395, '葵青区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3372, 44, 395, '元朗区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3373, 44, 395, '深水埗区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3374, 44, 395, '西贡区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3375, 44, 395, '大埔区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3376, 44, 395, '湾仔区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3377, 44, 395, '油尖旺区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3378, 44, 395, '北区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3379, 44, 395, '南区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3380, 44, 395, '荃湾区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3381, 44, 395, '中西区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3382, 44, 395, '离岛区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3383, 44, 396, '澳门', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3384, 44, 397, '台北', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3385, 44, 397, '高雄', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3386, 44, 397, '基隆', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3387, 44, 397, '台中', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3388, 44, 397, '台南', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3389, 44, 397, '新竹', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3390, 44, 397, '嘉义', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3391, 44, 397, '宜兰县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3392, 44, 397, '桃园县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3393, 44, 397, '苗栗县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3394, 44, 397, '彰化县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3395, 44, 397, '南投县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3396, 44, 397, '云林县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3397, 44, 397, '屏东县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3398, 44, 397, '台东县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3399, 44, 397, '花莲县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3400, 44, 397, '澎湖县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3401, 44, 3, '合肥', 2, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3402, 44, 3401, '庐阳区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3403, 44, 3401, '瑶海区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3404, 44, 3401, '蜀山区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3405, 44, 3401, '包河区', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3406, 44, 3401, '长丰县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3407, 44, 3401, '肥东县', 3, 1, 100, 1705462308, 1705462308, 25, 25);
INSERT INTO `citys` VALUES (3408, 44, 3401, '肥西县', 3, 1, 100, 1705462308, 1705462308, 25, 25);

-- ----------------------------
-- Records of countrys
-- ----------------------------
INSERT INTO `countrys` VALUES (1, 'Afghanistan', 'Afghanistan', 'AF', 93, 1, 100, 1720770776, 0, 167, 0);
INSERT INTO `countrys` VALUES (4, 'American Samoa', '美属萨摩亚', 'AS', 1684, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (5, 'Andorra', '安道​​尔', 'AD', 376, 0, 100, 1713498793, 0, 149, 0);
INSERT INTO `countrys` VALUES (6, 'Angola', '安哥拉', 'AO', 244, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (7, 'Anguilla', '安圭拉', 'AI', 1264, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (9, 'Antigua and Barbuda', '安提瓜和巴布达', 'AG', 1268, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (10, 'Argentina', '阿根廷', 'AR', 54, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (11, 'Armenia', '亚美尼亚', 'AM', 374, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (12, 'Aruba', '阿鲁巴岛', 'AW', 297, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (13, 'Australia', '澳大利亚', 'AU', 61, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (14, 'Austria', '奥地利', 'AT', 43, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (15, 'Azerbaijan', '阿塞拜疆', 'AZ', 994, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (16, 'Bahamas', '巴哈马', 'BS', 1242, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (17, 'Bahrain', '巴林', 'BH', 973, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (18, 'Bangladesh', '孟加拉国', 'BD', 880, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (19, 'Barbados', '巴巴多斯', 'BB', 1246, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (20, 'Belarus', '白俄罗斯', 'BY', 375, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (21, 'Belgium', '比利时', 'BE', 32, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (22, 'Belize', '伯利兹', 'BZ', 501, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (23, 'Benin', '贝宁', 'BJ', 229, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (24, 'Bermuda', '百慕大', 'BM', 1441, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (25, 'Bhutan', '不丹', 'BT', 975, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (26, 'Bolivia', '玻利维亚', 'BO', 591, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (27, 'Bosnia and Herzegovina', '波斯尼亚和黑塞哥维那', 'BA', 387, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (28, 'Botswana', '博茨瓦纳', 'BW', 267, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (29, 'Bouvet Island', '布维岛', 'BV', 0, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (30, 'Brazil', '巴西', 'BR', 55, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (31, 'British Indian Ocean Terr', '英属印度洋领地', 'IO', 246, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (32, 'Brunei Darussalam', '文莱达鲁萨兰国', 'BN', 673, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (33, 'Bulgaria', '保加利亚', 'BG', 359, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (34, 'Burkina Faso', '布基纳法索', 'BF', 226, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (35, 'Burundi', '布隆迪', 'BI', 257, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (36, 'Cambodia', '柬埔寨', 'KH', 855, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (37, 'Cameroon', '喀麦隆', 'CM', 237, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (38, 'Canada', '加拿大', 'CA', 1, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (39, 'Cape Verde', '佛得角', 'CV', 238, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (40, 'Cayman Islands', '开曼群岛', 'KY', 1345, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (41, 'Central African Republic', '中非共和国', 'CF', 236, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (42, 'Chad', '乍得', 'TD', 235, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (43, 'Chile', '智利', 'CL', 56, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (44, 'China', '中国', 'CN', 86, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (45, 'Christmas Island', '圣诞岛', 'CX', 61, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (46, 'Cocos Islands', '科科斯群岛', 'CC', 672, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (47, 'Colombia', '哥伦比亚', 'CO', 57, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (48, 'Comoros', '科摩罗', 'KM', 269, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (49, 'Congo', '刚果', 'CG', 242, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (50, 'Democratic Republic of th', '刚果民主共和国', 'CD', 243, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (51, 'Cook Islands', '库克群岛', 'CK', 682, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (52, 'Costa Rica', '哥斯达黎加', 'CR', 506, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (53, 'Cote D\'ivoire', '科特迪瓦', 'KT', 225, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (54, 'The Republic of Croatia', '克罗地亚共和国', 'HR', 385, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (55, 'Cuba', '古巴', 'CU', 53, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (56, 'Cyprus', '塞浦路斯', 'CY', 357, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (57, 'Czech Republic', '捷克共和国', 'CZ', 420, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (58, 'Denmark', '丹麦', 'DK', 45, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (59, 'Djibouti', '吉布提', 'DJ', 253, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (60, 'Dominica', '多明尼加', 'DO', 1767, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (62, 'East Timor', '东帝汶', 'TL', 670, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (63, 'Ecuador', '厄瓜多尔', 'EC', 593, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (64, 'Egypt', '埃及', 'EG', 20, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (65, 'El Salvador', '萨尔瓦多', 'SV', 503, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (66, 'Equatorial Guinea', '赤道几内亚', 'GQ', 240, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (67, 'Eritrea', '厄立特里亚', 'ER', 291, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (68, 'Estonia', '爱沙尼亚', 'EE', 372, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (69, 'Ethiopia', '埃塞俄比亚', 'ET', 251, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (70, 'Falkland Islands', '福克兰群岛', 'FK', 500, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (71, 'Faroe Islands', '法罗群岛', 'FO', 298, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (72, 'Fiji', '斐济', 'FJ', 679, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (73, 'Finland', '芬兰', 'FI', 358, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (74, 'France', '法国', 'FR', 33, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (75, 'French Guiana', '法属圭亚那', 'GF', 594, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (76, 'French Polynesia', '法属波利尼西亚', 'PF', 689, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (77, 'French Southern Territori', '法国南部领土', 'TF', 0, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (78, 'Gabon', '加蓬', 'GA', 241, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (79, 'Gambia', '冈比亚', 'GM', 220, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (80, 'Georgia', '格鲁吉亚', 'GE', 995, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (81, 'Germany', '德国', 'DE', 49, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (82, 'Ghana', '加纳', 'GH', 233, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (83, 'Gibraltar', '直布罗陀', 'GI', 350, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (84, 'Greece', '希腊', 'GR', 30, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (85, 'Greenland', '格陵兰', 'GL', 299, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (86, 'Grenada', '格林纳达', 'GD', 1473, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (87, 'Guadeloupe', '瓜德罗普岛', 'GP', 0, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (88, 'Guam', '关岛', 'GU', 1671, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (89, 'Guatemala', '危地马拉', 'GT', 502, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (90, 'Guinea', '几内亚', 'GN', 224, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (91, 'Guinea-Bissau', '几内亚比绍', 'GW', 245, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (92, 'Guyana', '圭亚那', 'GY', 592, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (93, 'Haiti', '海地', 'HT', 509, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (94, 'Heard Island and Mcdonald', '赫德岛和麦克唐纳群岛', 'HM', 0, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (96, 'Honduras', '洪都拉斯', 'HN', 504, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (97, 'Hong Kong, China', '中国香港', 'HK', 852, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (98, 'Hungary', '匈牙利', 'HU', 36, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (99, 'Iceland', '冰岛', 'IS', 354, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (100, 'India', '印度', 'IN', 91, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (101, 'Indonesia', '印度尼西亚', 'ID', 62, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (102, 'Iran', '伊朗', 'IR', 98, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (103, 'Iraq', '伊拉克', 'IQ', 964, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (104, 'Ireland', '爱尔兰', 'IE', 353, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (105, 'Israel', '以色列', 'IL', 972, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (106, 'Italy', '意大利', 'IT', 39, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (107, 'Jamaica', '牙买加', 'JM', 1876, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (108, 'Japan', '日本', 'JP', 81, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (109, 'Jordan', '约旦', 'JO', 962, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (110, 'Kazakhstan', '哈萨克斯坦', 'KZ', 7, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (111, 'Kenya', '肯尼亚', 'KE', 254, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (112, 'Kiribati', '基里巴斯', 'KI', 686, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (113, 'South Korea', '韩国', 'KR', 82, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (114, 'Democratic People\'s Repub', '朝鲜人民民主共和国', 'KP', 850, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (115, 'Kuwait', '科威特', 'KW', 965, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (116, 'Kyrgyzstan', '吉尔吉斯共和国', 'KG', 996, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (117, 'Laos', '老挝', 'LA', 856, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (118, 'Latvia', '拉脱维亚', 'LV', 371, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (119, 'Lebanon', '黎巴嫩', 'LB', 961, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (120, 'Lesotho', '莱索托', 'LS', 266, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (121, 'Liberia', '利比里亚', 'LR', 231, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (122, 'State of Libya', '利比亚', 'LY', 218, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (123, 'Liechtenstein', '列支敦士登', 'LI', 423, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (124, 'Lithuania', '立陶宛', 'LT', 370, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (125, 'Luxembourg', '卢森堡', 'LU', 352, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (126, 'Macau, China', '中国澳门', 'MO', 853, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (127, 'Macedonia', '马其顿', 'MK', 389, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (128, 'Madagascar', '马达加斯加', 'MG', 261, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (129, 'Malawi', '马拉维', 'MW', 265, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (130, 'Malaysia', '马来西亚', 'MY', 60, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (131, 'Maldives', '马尔代夫', 'MV', 960, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (132, 'Mali', '马里', 'ML', 223, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (133, 'Malta', '马耳他', 'MT', 356, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (134, 'Marshall Islands', '马绍尔群岛', 'MH', 692, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (135, 'Martinique', '马提尼克', 'MQ', 596, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (136, 'Mauritania', '毛里塔尼亚', 'MR', 222, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (137, 'Mauritius', '毛里求斯', 'MU', 230, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (138, 'Mayotte', '马约特', 'YT', 269, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (139, 'Mexico', '墨西哥', 'MX', 52, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (140, 'Micronesia', '密克罗尼西亚', 'FM', 691, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (141, 'Moldova', '摩尔多瓦', 'MD', 373, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (142, 'Monaco', '摩纳哥', 'MC', 377, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (143, 'Mongolia', '蒙古', 'MN', 976, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (144, 'Montserrat', '蒙特塞拉特', 'MS', 1664, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (145, 'Morocco', '摩洛哥', 'MA', 212, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (146, 'Mozambique', '莫桑比克', 'MZ', 258, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (147, 'Myanmar', '缅甸', 'MM', 95, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (148, 'Namibia', '纳米比亚', 'NA', 264, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (149, 'Nauru', '瑙鲁', 'NR', 674, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (150, 'Nepal', '尼泊尔', 'NP', 977, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (151, 'Netherlands', '荷兰', 'NL', 31, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (152, 'Saint Vincent and the Gre', '圣文森特和格林纳丁斯', 'VC', 784, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (153, 'New Caledonia', '新喀里多尼亚', 'NC', 687, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (154, 'New Zealand', '新西兰', 'NZ', 64, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (155, 'Nicaragua', '尼加拉瓜', 'NI', 505, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (156, 'Niger', '尼日尔', 'NE', 227, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (157, 'Nigeria', '尼日利亚', 'NG', 234, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (158, 'Niue', '纽埃', 'NU', 683, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (159, 'Norfolk Island', '诺福克岛', 'NF', 672, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (160, 'Northern Mariana Islands', '北马里亚纳群岛', 'MP', 1670, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (161, 'Norway', '挪威', 'NO', 47, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (162, 'Oman', '阿曼', 'OM', 968, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (163, 'Pakistan', '巴基斯坦', 'PK', 92, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (164, 'Palau', '帕劳', 'PW', 680, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (165, 'Palestine', '巴勒斯坦', 'PS', 970, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (166, 'Panama', '巴拿马', 'PA', 507, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (167, 'Papua New Guinea', '巴布亚新几内亚', 'PG', 675, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (168, 'Paraguay', '巴拉圭', 'PY', 595, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (169, 'Peru', '秘鲁', 'PE', 51, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (170, 'Philippines', '菲律宾', 'PH', 63, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (171, 'Pitcairn Islands', '皮特凯恩群岛', 'PN', 64, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (172, 'Poland', '波兰', 'PL', 48, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (173, 'Portugal', '葡萄牙', 'PT', 351, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (174, 'Puerto Rico', '波多黎各', 'PR', 1787, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (175, 'Qatar', '卡塔尔', 'QA', 974, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (176, 'Reunion', '留尼汪岛', 'RE', 262, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (177, 'Romania', '罗马尼亚', 'RO', 40, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (178, 'Russia', '俄国', 'RU', 7, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (179, 'Rwanda', '卢旺达', 'RW', 250, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (180, 'Saint Helena', '圣赫勒拿', 'SH', 247, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (181, 'Saint Kitts and Nevis', '圣基茨和尼维斯', 'KN', 1869, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (182, 'Saint Lucia', '圣卢西亚', 'LC', 1758, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (184, 'Saint Pierre and Miquelon', '圣皮埃尔和密克隆', 'PM', 508, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (185, 'San Marino', '圣马力诺', 'SM', 378, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (186, 'Sao Tome and Principe', '圣多美和普林西比', 'ST', 239, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (187, 'Saudi Arabia', '沙特阿拉伯', 'SA', 966, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (188, 'Senegal', '塞内加尔', 'SN', 221, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (189, 'Serbia', '塞尔维亚', 'RS', 381, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (190, 'Seychelles', '塞舌尔', 'SC', 248, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (191, 'Sierra Leone', '塞拉利昂', 'SL', 232, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (192, 'Singapore', '新加坡', 'SG', 65, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (193, 'Slovakia', '斯洛伐克', 'SK', 421, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (194, 'Slovenia', '斯洛文尼亚', 'SI', 386, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (195, 'Solomon Islands', '所罗门群岛', 'SB', 677, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (196, 'Somalia', '索马里', 'SO', 252, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (197, 'South Africa', '南非', 'ZA', 27, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (198, 'South Georgia and The Sou', '南乔治亚岛和南桑威奇群岛', 'GS', 0, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (199, 'Spain', '西班牙', 'ES', 34, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (200, 'Sri Lanka', '斯里兰卡', 'LK', 94, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (201, 'Sudan', '苏丹', 'SD', 249, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (202, 'Suriname', '苏里南', 'SR', 597, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (203, 'Svalbard and Jan Mayen', '斯瓦尔巴群岛和扬马延岛', 'SJ', 0, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (204, 'Swaziland', '斯威士兰', 'SZ', 268, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (205, 'Sweden', '瑞典', 'SE', 46, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (206, 'Switzerland', '瑞士', 'CH', 41, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (207, 'Syrian Arab Republic', '阿拉伯叙利亚共和国', 'SY', 963, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (208, 'TaiWan, China', '中国台湾', 'TW', 886, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (209, 'Tajikistan', '塔吉克斯坦', 'TJ', 992, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (210, 'Tanzania', '坦桑尼亚', 'TZ', 255, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (211, 'Thailand', '泰国', 'TH', 66, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (212, 'Togo', '多哥共和国', 'TG', 228, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (213, 'Tokelau', '托克劳', 'TK', 690, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (214, 'Tonga', '汤加', 'TO', 676, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (215, 'Trinidad and Tobago', '特立尼达和多巴哥', 'TT', 1868, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (216, 'Tunisia', '突尼斯', 'TN', 216, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (217, 'Turkey', '土耳其', 'TR', 90, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (218, 'Turkmenistan', '土库曼斯坦', 'TM', 993, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (219, 'Turks and Caicos Islands', '特克斯和凯科斯群岛', 'TC', 1649, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (220, 'Tuvalu', '图瓦卢', 'TV', 688, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (221, 'Uganda', '乌干达', 'UG', 256, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (222, 'Ukraine', '乌克兰', 'UA', 380, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (223, 'United Arab Emirates', '阿拉伯联合酋长国', 'AE', 971, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (224, 'United Kingdom', '英国', 'GB', 44, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (225, 'United States Minor Outly', '美国本土外小岛屿', 'UM', 0, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (226, 'United States', 'United States', 'US', 12, 0, 1, 1720777441, 0, 176, 0);
INSERT INTO `countrys` VALUES (227, 'Uruguay', '乌拉圭', 'UY', 598, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (228, 'US Virgin Islands', '美属维尔京群岛', 'VI', 1340, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (230, 'Uzbekistan', '乌兹别克斯坦', 'UZ', 998, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (231, 'Vanuatu', '瓦努阿图', 'VU', 678, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (232, 'Venezuela', '委内瑞拉', 'VE', 58, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (233, 'Socialist Republic of Vie', '越南社会主义共和国', 'VN', 84, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (234, 'Wallis and Futuna Islands', '瓦利斯和富图纳群岛', 'WF', 681, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (235, 'Western Sahara', '西撒哈拉', 'EH', 210, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (236, 'Western Samoa', '萨摩亚', 'WS', 685, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (237, 'Yemen', '也门', 'YE', 967, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (238, 'Vatican City State', '梵蒂冈城国', 'VA', 379, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (239, 'Zambia', '赞比亚', 'ZM', 260, 1, 100, NULL, 0, NULL, 0);
INSERT INTO `countrys` VALUES (240, 'Zimbabwe', '津巴布韦', 'ZW', 263, 1, 9999, 1706522331, 0, 73, 0);
INSERT INTO `countrys` VALUES (246, 'Albania', 'Albania', 'AL', 355, 1, 100, 1714025347, 1714025347, NULL, 149);
-- ----------------------------
-- Records of dictionaries
-- ----------------------------
INSERT INTO `dictionaries` VALUES (3, '性别', 'Gender', 1, '总控端：男-1 女-2 未知-3', 0, 1701832208, 40, 0, 0);
INSERT INTO `dictionaries` VALUES (13, '消息状态', 'Information_State', 1, '总控端：已读-1 未读-2', 0, 1701832256, 40, 19, 1699239534);
INSERT INTO `dictionaries` VALUES (14, '开关状态', 'Switch_State', 1, '总控端：启用-1 禁用-0', 0, 1701851453, 19, 40, 1699348754);
INSERT INTO `dictionaries` VALUES (15, '更新状态', 'Update_Status', 1, '总控端：开始-1 停止-2 结束-3', 0, 1701832402, 40, 73, 1699409418);
INSERT INTO `dictionaries` VALUES (16, '支付状态', 'Payment_Status', 1, '总控端：未支付-1 已支付-2 支付失败-3', 0, 1701833548, 1, 40, 1699410778);
INSERT INTO `dictionaries` VALUES (17, '数据范围', 'Data_Range', 0, NULL, 0, 1701141740, 1, 40, 1699412500);
INSERT INTO `dictionaries` VALUES (18, '路由分类', 'Route_Classification', 1, '总控端：总控端-1 站点端-2', 0, 1701832516, 40, 40, 1699412645);
INSERT INTO `dictionaries` VALUES (19, '登录注册提示', 'Login_Registration', 0, NULL, 0, 1701141729, 1, 40, 1699412725);
INSERT INTO `dictionaries` VALUES (20, '新增删除提示', 'Add_Delete', 0, NULL, 0, 1701141738, 1, 40, 1699413186);
INSERT INTO `dictionaries` VALUES (21, '是否管理员', 'Administrator', 1, '总控端：是-1 否-0', 0, 1701851427, 19, 40, 1699413544);
INSERT INTO `dictionaries` VALUES (22, '菜单类型', 'Menu_Type', 1, '总控端：外链-EXTLINK 目录-CATALOG 菜单-MENU 按钮-BUTTON', 0, 1701833500, 1, 40, 1699413663);
INSERT INTO `dictionaries` VALUES (23, '是否缓存', 'Cache', 1, '总控端：是-1 否-2', 0, 1701833855, 1, 40, 1699413912);
INSERT INTO `dictionaries` VALUES (24, '按钮名称', 'Button_Name', 0, NULL, 0, 1701141792, 1, 40, 1699414921);
INSERT INTO `dictionaries` VALUES (32, '平台字段类型', 'Platform_Type', 1, '总控端：文本-1 输入框-2 图片-3', 0, 1701833821, 1, 19, 1699943141);
INSERT INTO `dictionaries` VALUES (33, '是否送货', 'Logistics_State', 1, '总控端：否-0 是-1', 0, 1701833927, 1, 25, 1699945516);
INSERT INTO `dictionaries` VALUES (34, '发邮日志状态', 'EmailLog_Status', 1, '总控端：发送失败-0 发送成功-1', 0, 1701833912, 1, 1, 1700105367);
INSERT INTO `dictionaries` VALUES (35, '操作日志操作类型', 'OperationLog_Type', 1, '总控端：删除操作-delete 更新操作-update 新增操作-insert', 0, 1701833066, 1, 1, 1700121114);
INSERT INTO `dictionaries` VALUES (36, '是否显示', 'V_Show', 1, '总控端：隐藏-0 显示-1', 0, 1701833897, 1, 19, 1700463680);
INSERT INTO `dictionaries` VALUES (37, '操作日志的模块名称', 'OperationLogModule', 1, '总控端：权限管理-0 数据库列表-1 服务器列表-2 站点列表-3 用户列表-4 部门列表-5 角色列表-6', 0, 1701832890, 1, 1, 1700620774);
INSERT INTO `dictionaries` VALUES (38, '命令执行状态', 'Exec_State', 1, '总控端：成功-1 失败-2', 0, 1701832747, 1, 25, 1700624124);
INSERT INTO `dictionaries` VALUES (40, '网站显示状态：首页显示/热门/推荐等', 'Show_Home_State', 1, '站点端：显示-1 隐藏-0', 0, 1702015899, 25, 25, 1701746918);
INSERT INTO `dictionaries` VALUES (41, '折扣类型', 'Discount_Type', 1, '站点端：折扣率-1 折扣金额-2', 0, 1702015333, 25, 25, 1701825722);
INSERT INTO `dictionaries` VALUES (42, '是否有样本', 'Has_Sample', 1, '站点端：报告是否有样本： 是-1 否-0', 0, 1702017227, 25, 25, 1702015436);
INSERT INTO `dictionaries` VALUES (43, '定时任务时间类型', 'Timed_Task_Time_Type', 1, '总控端：定时任务的时间类型', 0, 1702547118, 0, 1, 1702547118);
INSERT INTO `dictionaries` VALUES (44, '定时任务任务分类', 'Timed_Task_Category', 1, '总控端：定时任务任务分类，admin后台端，index接口端', 0, 1702966764, 1, 1, 1702547418);
INSERT INTO `dictionaries` VALUES (45, '定时任务任务类型', 'Timed_Task_Type', 1, '总控端：任务定时任务任务类型，shell-命令行  http-URL请求', 0, 1702966627, 0, 1, 1702966627);
INSERT INTO `dictionaries` VALUES (46, '定时任务每周天数', 'Timed_Task_Week', 1, '总控端：定时任务每周天数', 0, 1702969781, 0, 1, 1702969781);
INSERT INTO `dictionaries` VALUES (47, '支付状态', 'Pay_State', 1, '站点端：未付款-1；已付款-2；支付失败-3；', 0, 1704703730, 25, 25, 1704703661);
INSERT INTO `dictionaries` VALUES (48, '导航菜单-类型', 'Navigation_Menu_Type', 1, '站点端：1-顶部，2-底部，3-顶部+底部', 0, 1704778948, 0, 1, 1704778948);
INSERT INTO `dictionaries` VALUES (49, '导航菜单-是否单页', 'Is_Single_Page', 1, '站点端：是否单页，1-是 0否', 0, 1704779095, 0, 1, 1704779095);
INSERT INTO `dictionaries` VALUES (50, '优惠券类型', 'Coupon_Type', 1, '站点端：打折券-1，现金券-2', 0, 1704794352, 0, 25, 1704794352);
INSERT INTO `dictionaries` VALUES (51, '优惠券生效状态', 'Coupon_State', 1, '站点端：生效-1，过期-0', 0, 1704852523, 25, 25, 1704852505);
INSERT INTO `dictionaries` VALUES (52, '开票状态', 'Invoice_State', 1, '站点端：已开票-2；申请中-1；', 0, 1715307531, 167, 25, 1705029000);
INSERT INTO `dictionaries` VALUES (53, '发票类型', 'Invoice_Type', 1, '站点端：普通发票-1；专用发票-2', 0, 1705036703, 25, 25, 1705036673);
INSERT INTO `dictionaries` VALUES (54, '获知渠道', 'Channel_Type', 1, '站点端：Google-1；Email marketing-2；Press Release-3;Enter directly-4；Other-5;', 0, 1705316709, 25, 25, 1705316462);
INSERT INTO `dictionaries` VALUES (55, '物流/配送方式', 'Post_Type', 1, '站点端：不需要物流-0；顺丰-1；ems-2;', 0, 1705317481, 0, 25, 1705317481);
INSERT INTO `dictionaries` VALUES (56, '省市区类型', 'City_Type', 1, '总控端：国家-0；省-1；市-2；区（县）-3', 0, 1705462506, 0, 25, 1705462506);
INSERT INTO `dictionaries` VALUES (57, '新闻类型', 'News_Type', 1, '站点端: 行业新闻-1；免费咨询-2；公司动态-3;', 0, 1710150292, 73, 25, 1705482466);
INSERT INTO `dictionaries` VALUES (58, '愿意购买时间', 'Buy_Time', 1, '站点端：购买时间，3天内、7天内、15天内、30天内', 0, 1711935952, 165, 1, 1705632503);
INSERT INTO `dictionaries` VALUES (59, '是否属于分析师', 'Is_Analyst', 1, '站点端：0 否、1是', 0, 1706253440, 0, 1, 1706253440);
INSERT INTO `dictionaries` VALUES (60, '快速搜索条件下拉', 'Quick_Search', 1, '站点端：对应的快速搜索的下拉框，key是字段名字，备注对应的是输入类型：1文本、2下拉框、3树形下拉框、4时间组件', 0, 1711010301, 1, 1, 1709277793);
INSERT INTO `dictionaries` VALUES (61, '权威引用分类', 'quote_cage', 1, '站点端：企业引用-1；券商引用-2；媒体引用-3', 0, 1713497786, 149, 19, 1711330821);
INSERT INTO `dictionaries` VALUES (62, '模版颜色', 'template_color', 1, '站点端：', 0, 1713497104, 25, 167, 1713495784);
INSERT INTO `dictionaries` VALUES (64, '自动同步报告数据', 'autoSyncData', 1, '自动同步(北京)报告数据', 0, 1718604310, 0, 167, 1718604310);

-- ----------------------------
-- Records of dictionary_values
-- ----------------------------
INSERT INTO `dictionary_values` VALUES (3, 3, 'Gender', '女', 'Girl', '2', 1, 2, NULL, 0, 0, 19, 1701832208);
INSERT INTO `dictionary_values` VALUES (4, 3, 'Gender', '未知', 'Unknown', '3', 1, 3, NULL, 0, 0, 79, 1701936265);
INSERT INTO `dictionary_values` VALUES (15, 15, 'Update_Status', '结束', 'End', '3', 1, 3, NULL, 40, 1699348819, 40, 1701832402);
INSERT INTO `dictionary_values` VALUES (17, 15, 'Update_Status', '开始', 'Start', '1', 1, 1, NULL, 73, 1699409443, 40, 1701832402);
INSERT INTO `dictionary_values` VALUES (20, 14, 'Switch_State', '启用', 'Enable', '1', 1, 1, NULL, 73, 1699409898, 1, 1701926777);
INSERT INTO `dictionary_values` VALUES (21, 16, 'Payment_Status', '未支付', 'Unpaid', '1', 1, 1, NULL, 40, 1699410801, 40, 1701833548);
INSERT INTO `dictionary_values` VALUES (22, 16, 'Payment_Status', '已支付', 'Paid', '2', 1, 2, NULL, 40, 1699410820, 79, 1701833548);
INSERT INTO `dictionary_values` VALUES (23, 16, 'Payment_Status', '支付失败', 'Failed', '3', 1, 3, NULL, 40, 1699410845, 79, 1701833548);
INSERT INTO `dictionary_values` VALUES (24, 13, 'Information_State', '未读', 'Unread', '2', 1, 2, NULL, 40, 1699411241, 79, 1701832256);
INSERT INTO `dictionary_values` VALUES (25, 13, 'Information_State', '已读', 'Read', '1', 1, 1, NULL, 40, 1699411252, 40, 1701832256);
INSERT INTO `dictionary_values` VALUES (26, 15, 'Update_Status', '停止', 'Stop', '2', 1, 2, NULL, 40, 1699411357, 40, 1701832402);
INSERT INTO `dictionary_values` VALUES (27, 17, 'Data_Range', '全部', 'All', '1', 1, 1, NULL, 40, 1699412512, 40, 1699417329);
INSERT INTO `dictionary_values` VALUES (28, 17, 'Data_Range', '本部门', 'This Department', '2', 1, 1, NULL, 40, 1699412555, 40, 1699417329);
INSERT INTO `dictionary_values` VALUES (31, 18, 'Route_Classification', '总控端', 'Master Control', '1', 1, 2, NULL, 40, 1699412672, 79, 1701832516);
INSERT INTO `dictionary_values` VALUES (32, 18, 'Route_Classification', '站点端', 'Site Side', '2', 1, 3, NULL, 40, 1699412694, 1, 1701832516);
INSERT INTO `dictionary_values` VALUES (33, 19, 'Login_Registration', '注册成功', 'Registration Success', '1', 1, 1, NULL, 40, 1699412744, 40, 1699417329);
INSERT INTO `dictionary_values` VALUES (34, 19, 'Login_Registration', '注册失败', 'Registration Failed', '2', 1, 1, NULL, 40, 1699412762, 40, 1699417329);
INSERT INTO `dictionary_values` VALUES (35, 19, 'Login_Registration', '邮箱已被注册', 'Email Has Been Registered', '3', 1, 1, NULL, 40, 1699412787, 40, 1699417329);
INSERT INTO `dictionary_values` VALUES (36, 36, 'V_Show', '显示', 'Display', '1', 1, 1, NULL, 40, 1699412818, 149, 1710148417);
INSERT INTO `dictionary_values` VALUES (37, 37, 'OperationLogModule', '权限管理', 'Rights Management', 'rule', 1, 0, NULL, 40, 1699412833, 149, 1710234580);
INSERT INTO `dictionary_values` VALUES (38, 19, 'Login_Registration', '账号未验证通过', 'Account Not Verified', '6', 1, 1, NULL, 40, 1699412862, 40, 1699417329);
INSERT INTO `dictionary_values` VALUES (39, 19, 'Login_Registration', '账号不存在', 'Account Does Not Exist', '7', 1, 1, NULL, 40, 1699412920, 40, 1699417329);
INSERT INTO `dictionary_values` VALUES (40, 20, 'Add_Delete', '新增成功', 'Added successfully', '1', 0, 1, NULL, 40, 1699413214, 149, 1710146956);
INSERT INTO `dictionary_values` VALUES (41, 20, 'Add_Delete', '新增失败', 'Failed to add', '2', 1, 1, '', 40, 1699413231, 0, 1699415717);
INSERT INTO `dictionary_values` VALUES (42, 42, 'Has_Sample', '是', 'yes', '1', 1, 2, NULL, 40, 1699413248, 25, 1702017265);
INSERT INTO `dictionary_values` VALUES (43, 20, 'Add_Delete', '删除失败', 'Failed to delete', '4', 1, 1, NULL, 40, 1699413272, 40, 1699417329);
INSERT INTO `dictionary_values` VALUES (44, 44, 'Timed_Task_Category', '后台端', 'Backend End', 'admin', 1, 1, NULL, 40, 1699413557, 1, 1702966764);
INSERT INTO `dictionary_values` VALUES (45, 45, 'Timed_Task_Type', '命令行', 'command line', 'shell', 1, 1, NULL, 40, 1699413571, 1, 1702966672);
INSERT INTO `dictionary_values` VALUES (46, 22, 'Menu_Type', '目录', 'Table Of Contents', 'CATALOG', 1, 1, NULL, 40, 1699413709, 19, 1701833500);
INSERT INTO `dictionary_values` VALUES (47, 22, 'Menu_Type', '菜单', 'Menu', 'MENU', 1, 2, NULL, 40, 1699413728, 1, 1701845805);
INSERT INTO `dictionary_values` VALUES (48, 48, 'Navigation_Menu_Type', '顶部', 'top', '1', 1, 1, NULL, 40, 1699413769, 1, 1704783107);
INSERT INTO `dictionary_values` VALUES (49, 49, 'Is_Single_Page', '是', 'yes', '1', 1, 1, NULL, 40, 1699413789, 1, 1704783155);
INSERT INTO `dictionary_values` VALUES (50, 23, 'Cache', '是', 'Yes', '1', 1, 1, NULL, 40, 1699413925, 1, 1701833855);
INSERT INTO `dictionary_values` VALUES (51, 23, 'Cache', '否', 'No', '2', 1, 1, NULL, 40, 1699413935, 1, 1701833855);
INSERT INTO `dictionary_values` VALUES (56, 24, 'Button_Name', '新增', 'Add', '1', 1, 1, NULL, 40, 1699415769, 1, 1700458691);
INSERT INTO `dictionary_values` VALUES (57, 24, 'Button_Name', '删除', 'Del', '2', 1, 2, NULL, 40, 1699415782, 1, 1700458691);
INSERT INTO `dictionary_values` VALUES (58, 24, 'Button_Name', '搜索', 'Search', '3', 1, 3, NULL, 40, 1699415801, 40, 1700458691);
INSERT INTO `dictionary_values` VALUES (59, 24, 'Button_Name', '重置', 'Reset', '4', 1, 4, NULL, 40, 1699415824, 40, 1700458691);
INSERT INTO `dictionary_values` VALUES (61, 24, 'Button_Name', '提交', 'Submit', '5', 1, 6, NULL, 40, 1699415935, 40, 1700458691);
INSERT INTO `dictionary_values` VALUES (62, 24, 'Button_Name', '取消', 'Cancel', '6', 1, 6, NULL, 40, 1699415957, 40, 1700458691);
INSERT INTO `dictionary_values` VALUES (63, 24, 'Button_Name', '升级', 'Upgrade', '7', 1, 7, '', 40, 1699415998, 0, 1700458691);
INSERT INTO `dictionary_values` VALUES (64, 24, 'Button_Name', '登录', 'Log', '8', 1, 8, NULL, 40, 1699416045, 1, 1700458691);
INSERT INTO `dictionary_values` VALUES (65, 24, 'Button_Name', '注册', 'Register', '9', 1, 9, NULL, 40, 1699416073, 1, 1700458691);
INSERT INTO `dictionary_values` VALUES (66, 24, 'Button_Name', '忘记密码', 'Forget The Password', '10', 1, 10, '', 40, 1699416141, 0, 1700458691);
INSERT INTO `dictionary_values` VALUES (67, 24, 'Button_Name', '修改', 'Revise', '11', 1, 3, '', 40, 1699416201, 0, 1700458691);
INSERT INTO `dictionary_values` VALUES (75, 24, 'Button_Name', '详情', 'Details', '12', 1, 12, '', 40, 1699425093, 0, 1700458691);
INSERT INTO `dictionary_values` VALUES (76, 24, 'Button_Name', '批量上传', 'Bulk Upload', '13', 1, 13, NULL, 40, 1699428077, 40, 1700458691);
INSERT INTO `dictionary_values` VALUES (77, 24, 'Button_Name', '导出Execl', 'Export Execl', '14', 1, 14, NULL, 40, 1699428094, 40, 1700458691);
INSERT INTO `dictionary_values` VALUES (78, 24, 'Button_Name', '导出TXT', 'Export TXT', '15', 1, 15, '', 40, 1699428211, 0, 1700458691);
INSERT INTO `dictionary_values` VALUES (79, 24, 'Button_Name', '批量修改', 'Batch Edit', '16', 1, 16, '', 40, 1699428247, 0, 1700458691);
INSERT INTO `dictionary_values` VALUES (80, 24, 'Button_Name', '表头顺序', 'HeaderOrder', '17', 1, 17, '', 40, 1699428291, 0, 1700458691);
INSERT INTO `dictionary_values` VALUES (81, 24, 'Button_Name', '批量删除', 'Batch Deletion', '18', 1, 18, '', 40, 1699428346, 0, 1700458691);
INSERT INTO `dictionary_values` VALUES (82, 24, 'Button_Name', '折扣', 'Discount', '19', 1, 19, '', 40, 1699428372, 0, 1700458691);
INSERT INTO `dictionary_values` VALUES (83, 24, 'Button_Name', '上传文件', 'Upload', '20', 1, 20, '', 40, 1699428440, 0, 1700458691);
INSERT INTO `dictionary_values` VALUES (84, 24, 'Button_Name', '示例', 'Example', '21', 1, 21, '', 40, 1699428480, 0, 1700458691);
INSERT INTO `dictionary_values` VALUES (85, 24, 'Button_Name', '复制', 'Copy', '22', 1, 22, '', 40, 1699428665, 0, 1700458691);
INSERT INTO `dictionary_values` VALUES (86, 24, 'Button_Name', '结束', 'End', '3', 1, 3, '', 40, 1699428838, 0, 1700458691);
INSERT INTO `dictionary_values` VALUES (97, 3, 'Gender', '男', 'Boy', '1', 1, 1, NULL, 1, 1699493621, 1, 1701832208);
INSERT INTO `dictionary_values` VALUES (100, 32, 'Platform_Type', '文本', 'Text', '1', 1, 1, '', 19, 1699943199, 0, 1701833821);
INSERT INTO `dictionary_values` VALUES (102, 32, 'Platform_Type', '输入框', 'Input', '2', 1, 4, NULL, 19, 1699943264, 149, 1710233474);
INSERT INTO `dictionary_values` VALUES (103, 32, 'Platform_Type', '图片', 'Pic', '3', 1, 2, NULL, 19, 1699943279, 19, 1701833821);
INSERT INTO `dictionary_values` VALUES (105, 33, 'Logistics_State', '是', 'Yes', '1', 1, 0, NULL, 25, 1699945540, 149, 1710233534);
INSERT INTO `dictionary_values` VALUES (106, 33, 'Logistics_State', '否', 'No', '0', 1, 1, NULL, 25, 1699945554, 149, 1710233517);
INSERT INTO `dictionary_values` VALUES (110, 34, 'EmailLog_Status', '发送成功', 'Successfully Sent', '1', 1, 0, NULL, 1, 1700105419, 149, 1710233877);
INSERT INTO `dictionary_values` VALUES (111, 34, 'EmailLog_Status', '发送失败', 'Fail In Send', '0', 1, 1, NULL, 1, 1700105433, 1, 1701833912);
INSERT INTO `dictionary_values` VALUES (113, 35, 'OperationLog_Type', '新增操作', 'Add Operation', 'insert', 1, 1, NULL, 1, 1700121140, 149, 1710233960);
INSERT INTO `dictionary_values` VALUES (114, 35, 'OperationLog_Type', '更新操作', 'Update Operation', 'update', 1, 1, NULL, 1, 1700121183, 149, 1710234452);
INSERT INTO `dictionary_values` VALUES (116, 36, 'V_Show', '隐藏', 'Hide', '0', 1, 2, NULL, 19, 1700463727, 73, 1710148527);
INSERT INTO `dictionary_values` VALUES (117, 37, 'OperationLogModule', '角色列表', 'Role List', 'role', 1, 6, NULL, 1, 1700620863, 1, 1701832890);
INSERT INTO `dictionary_values` VALUES (118, 37, 'OperationLogModule', '部门列表', 'Department List', 'department', 1, 5, NULL, 1, 1700620898, 1, 1701832890);
INSERT INTO `dictionary_values` VALUES (119, 37, 'OperationLogModule', '用户列表', 'User List', 'user', 1, 4, NULL, 1, 1700620937, 1, 1701832890);
INSERT INTO `dictionary_values` VALUES (120, 37, 'OperationLogModule', '站点列表', 'Site List', 'site', 1, 3, NULL, 1, 1700620968, 1, 1701832890);
INSERT INTO `dictionary_values` VALUES (121, 37, 'OperationLogModule', '服务器列表', 'Server List', 'server', 1, 2, NULL, 1, 1700621007, 1, 1701832890);
INSERT INTO `dictionary_values` VALUES (122, 37, 'OperationLogModule', '数据库列表', 'Database List', 'database', 1, 1, '', 1, 1700621043, 0, 1701832890);
INSERT INTO `dictionary_values` VALUES (123, 35, 'OperationLog_Type', '删除操作', 'Delete Operation', 'delete', 1, 1, NULL, 1, 1700623210, 149, 1710233941);
INSERT INTO `dictionary_values` VALUES (124, 38, 'Exec_State', '成功', 'Success', '1', 1, 1, NULL, 25, 1700624175, 73, 1710205614);
INSERT INTO `dictionary_values` VALUES (125, 38, 'Exec_State', '失败', 'Error', '2', 1, 2, NULL, 25, 1700624187, 73, 1710205613);
INSERT INTO `dictionary_values` VALUES (126, 40, 'Show_Home_State', '隐藏', 'hide', '0', 1, 1, NULL, 25, 1701746966, 25, 1702017213);
INSERT INTO `dictionary_values` VALUES (127, 40, 'Show_Home_State', '显示', 'show', '1', 1, 2, '', 25, 1701746979, 0, 1702015899);
INSERT INTO `dictionary_values` VALUES (128, 41, 'Discount_Type', '折扣率', 'discount', '1', 1, 1, '', 25, 1701825776, 0, 1702015333);
INSERT INTO `dictionary_values` VALUES (129, 41, 'Discount_Type', '折扣金额', 'discount_amount', '2', 1, 1, '', 25, 1701825799, 149, 1710235166);
INSERT INTO `dictionary_values` VALUES (130, 14, 'Switch_State', '禁用', 'Unable', '0', 1, 2, NULL, 1, 1701834211, 79, 1701851453);
INSERT INTO `dictionary_values` VALUES (131, 42, 'Has_Sample', '否', 'no', '0', 1, 1, '', 25, 1702017276, 0, 1702017276);
INSERT INTO `dictionary_values` VALUES (132, 43, 'Timed_Task_Time_Type', '每天', 'every day', 'every_day', 1, 1, '', 1, 1702547158, 0, 1702547158);
INSERT INTO `dictionary_values` VALUES (133, 43, 'Timed_Task_Time_Type', 'N天', 'N days', 'N_days', 1, 1, '', 1, 1702547177, 0, 1702547177);
INSERT INTO `dictionary_values` VALUES (134, 43, 'Timed_Task_Time_Type', '每小时', 'Every Hour', 'Every_hour', 1, 1, '', 1, 1702547212, 0, 1702547212);
INSERT INTO `dictionary_values` VALUES (135, 43, 'Timed_Task_Time_Type', 'N小时', 'N Hours', 'N_hours', 1, 1, '', 1, 1702547249, 0, 1702547249);
INSERT INTO `dictionary_values` VALUES (136, 43, 'Timed_Task_Time_Type', 'N分钟', 'N Minutes', 'N_minutes', 1, 1, '', 1, 1702547277, 0, 1702547277);
INSERT INTO `dictionary_values` VALUES (137, 43, 'Timed_Task_Time_Type', '每星期', 'Every Week', 'Every_week', 1, 1, '', 1, 1702547308, 149, 1710150575);
INSERT INTO `dictionary_values` VALUES (138, 43, 'Timed_Task_Time_Type', '每月', 'Monthly', 'monthly', 1, 1, '', 1, 1702547333, 149, 1710150575);
INSERT INTO `dictionary_values` VALUES (139, 44, 'Timed_Task_Category', '接口端', 'Interface End', 'index', 1, 1, '', 1, 1702547472, 0, 1702966764);
INSERT INTO `dictionary_values` VALUES (140, 21, 'Administrator', '是', 'Yes', '1', 1, 1, '', 19, 1702876843, 0, 1702876843);
INSERT INTO `dictionary_values` VALUES (141, 45, 'Timed_Task_Type', 'URL请求', 'URL request', 'http', 1, 1, '', 1, 1702966688, 149, 1710148826);
INSERT INTO `dictionary_values` VALUES (142, 46, 'Timed_Task_Week', '周一', 'Monday', '1', 1, 1, '', 1, 1702969821, 149, 1710150424);
INSERT INTO `dictionary_values` VALUES (143, 46, 'Timed_Task_Week', '周二', 'Tuesday', '2', 1, 1, '', 1, 1702969839, 149, 1710150425);
INSERT INTO `dictionary_values` VALUES (144, 46, 'Timed_Task_Week', '周三', 'Wednesday', '3', 1, 1, '', 1, 1702969856, 149, 1710150425);
INSERT INTO `dictionary_values` VALUES (145, 46, 'Timed_Task_Week', '周四', 'Thursday', '4', 1, 1, '', 1, 1702969872, 149, 1710150426);
INSERT INTO `dictionary_values` VALUES (146, 46, 'Timed_Task_Week', '周五', 'Friday', '5', 1, 1, '', 1, 1702969887, 149, 1710150427);
INSERT INTO `dictionary_values` VALUES (147, 46, 'Timed_Task_Week', '周六', 'Saturday', '6', 1, 1, '', 1, 1702969906, 149, 1710320835);
INSERT INTO `dictionary_values` VALUES (148, 46, 'Timed_Task_Week', '周日', 'Sunday', '7', 1, 1, NULL, 1, 1702969925, 149, 1711007319);
INSERT INTO `dictionary_values` VALUES (149, 21, 'Administrator', '否', 'No', '0', 1, 1, '', 19, 1703489164, 0, 1703489164);
INSERT INTO `dictionary_values` VALUES (150, 47, 'Pay_State', '未付款', 'Unpaid', '1', 1, 1, '', 25, 1704703772, 0, 1704703772);
INSERT INTO `dictionary_values` VALUES (151, 47, 'Pay_State', '付款成功', 'Payment Successful', '2', 1, 2, '', 25, 1704703831, 0, 1704703831);
INSERT INTO `dictionary_values` VALUES (152, 47, 'Pay_State', '付款失败', 'Payment failed', '3', 1, 3, '', 25, 1704703847, 0, 1704703847);
INSERT INTO `dictionary_values` VALUES (153, 48, 'Navigation_Menu_Type', '底部', 'bottom', '2', 1, 1, '', 1, 1704783123, 0, 1704783123);
INSERT INTO `dictionary_values` VALUES (154, 48, 'Navigation_Menu_Type', '顶部+底部', 'Top+bottom', '3', 1, 1, '', 1, 1704783142, 0, 1704783142);
INSERT INTO `dictionary_values` VALUES (155, 49, 'Is_Single_Page', '否', 'no', '0', 1, 1, '', 1, 1704783167, 0, 1704783167);
INSERT INTO `dictionary_values` VALUES (156, 50, 'Coupon_Type', '打折券', 'discount percent', '1', 1, 1, '', 25, 1704794475, 0, 1704794475);
INSERT INTO `dictionary_values` VALUES (157, 50, 'Coupon_Type', '现金券', 'discount cash', '2', 1, 2, NULL, 25, 1704794497, 25, 1704794507);
INSERT INTO `dictionary_values` VALUES (158, 22, 'Menu_Type', '按钮', 'Button', 'BUTTON', 1, 3, '', 19, 1704850174, 0, 1704850174);
INSERT INTO `dictionary_values` VALUES (159, 22, 'Menu_Type', '外链', 'Extlink', 'EXTLINK', 1, 4, '', 19, 1704850197, 0, 1704850197);
INSERT INTO `dictionary_values` VALUES (160, 51, 'Coupon_State', '生效', 'effective', '1', 1, 1, NULL, 25, 1704852582, 25, 1704854154);
INSERT INTO `dictionary_values` VALUES (161, 51, 'Coupon_State', '过期', 'ineffective', '0', 1, 2, NULL, 25, 1704852606, 25, 1704854158);
INSERT INTO `dictionary_values` VALUES (163, 52, 'Invoice_State', '已开票', 'Invoiced', '2', 1, 3, NULL, 25, 1705029980, 25, 1715307531);
INSERT INTO `dictionary_values` VALUES (164, 53, 'Invoice_Type', '普通发票', 'Ordinary invoice', '1', 1, 1, '', 25, 1705036792, 0, 1705036792);
INSERT INTO `dictionary_values` VALUES (165, 53, 'Invoice_Type', '专用发票', 'Special invoice', '2', 1, 2, '', 25, 1705036808, 0, 1705036808);
INSERT INTO `dictionary_values` VALUES (166, 52, 'Invoice_State', '申请中', 'Invoicing', '1', 1, 2, NULL, 25, 1705308617, 25, 1715307531);
INSERT INTO `dictionary_values` VALUES (167, 54, 'Channel_Type', 'Google', 'Google', '1', 1, 1, '', 25, 1705316759, 0, 1705316759);
INSERT INTO `dictionary_values` VALUES (168, 54, 'Channel_Type', 'Email marketing', 'Email marketing', '2', 1, 2, '', 25, 1705316770, 0, 1705316770);
INSERT INTO `dictionary_values` VALUES (169, 54, 'Channel_Type', 'Press Release', 'Press Release', '3', 1, 3, '', 25, 1705316777, 0, 1705316777);
INSERT INTO `dictionary_values` VALUES (170, 54, 'Channel_Type', 'Enter directly', 'Enter directly', '4', 1, 4, '', 25, 1705316786, 0, 1705316786);
INSERT INTO `dictionary_values` VALUES (171, 54, 'Channel_Type', 'Other', 'Other', '5', 1, 5, '', 25, 1705316796, 0, 1705316796);
INSERT INTO `dictionary_values` VALUES (172, 55, 'Post_Type', '不需要物流', 'None', '0', 1, 1, '', 25, 1705317508, 0, 1705317508);
INSERT INTO `dictionary_values` VALUES (173, 55, 'Post_Type', '顺丰', 'SF', '1', 1, 2, '', 25, 1705317523, 0, 1705317523);
INSERT INTO `dictionary_values` VALUES (174, 55, 'Post_Type', '邮政', 'EMS', '2', 1, 3, '', 25, 1705317536, 0, 1705317536);
INSERT INTO `dictionary_values` VALUES (175, 56, 'City_Type', '国家', 'country', '0', 1, 1, '', 25, 1705470707, 0, 1705470707);
INSERT INTO `dictionary_values` VALUES (176, 56, 'City_Type', '省', 'Province', '1', 1, 2, NULL, 25, 1705470726, 25, 1705470836);
INSERT INTO `dictionary_values` VALUES (177, 56, 'City_Type', '市', 'City', '2', 1, 3, NULL, 25, 1705470750, 25, 1705470755);
INSERT INTO `dictionary_values` VALUES (178, 56, 'City_Type', '区/县', 'county', '3', 1, 4, '', 25, 1705470790, 0, 1705470790);
INSERT INTO `dictionary_values` VALUES (179, 57, 'News_Type', '行业新闻', 'Industry news', '1', 1, 1, '', 25, 1705482829, 0, 1705482829);
INSERT INTO `dictionary_values` VALUES (180, 57, 'News_Type', '免费咨询', 'Free information', '2', 1, 2, '', 25, 1705482895, 0, 1705482895);
INSERT INTO `dictionary_values` VALUES (181, 57, 'News_Type', '公司动态', 'Company dynamics', '3', 1, 3, '', 25, 1705482912, 0, 1705482912);
INSERT INTO `dictionary_values` VALUES (182, 58, 'Buy_Time', '3天内', 'Within 3 days', '3', 1, 10, NULL, 1, 1705632619, 165, 1711935952);
INSERT INTO `dictionary_values` VALUES (183, 58, 'Buy_Time', '7天内', 'Within 7 days', '7', 1, 20, NULL, 1, 1705632639, 165, 1711935952);
INSERT INTO `dictionary_values` VALUES (184, 58, 'Buy_Time', '15天内', 'Within 15 days', '15', 1, 30, NULL, 1, 1705632672, 165, 1711935952);
INSERT INTO `dictionary_values` VALUES (185, 58, 'Buy_Time', '30天内', 'Within 30 days', '30', 1, 40, NULL, 1, 1705632709, 165, 1711935952);
INSERT INTO `dictionary_values` VALUES (186, 59, 'Is_Analyst', '是', 'yes', '1', 1, 1, '', 1, 1706253451, 0, 1706253451);
INSERT INTO `dictionary_values` VALUES (187, 59, 'Is_Analyst', '否', 'no', '0', 1, 1, '', 1, 1706253463, 0, 1706253463);
INSERT INTO `dictionary_values` VALUES (189, 60, 'Quick_Search', 'ID', 'ID', 'id', 1, 1, '1', 1, 1709278142, 165, 1715393461);
INSERT INTO `dictionary_values` VALUES (190, 60, 'Quick_Search', '报告', 'Report', 'name', 1, 0, '1', 1, 1709278171, 165, 1718789285);
INSERT INTO `dictionary_values` VALUES (191, 60, 'Quick_Search', '英文报告', 'English Report', 'english_name', 1, 0, '1', 1, 1709278202, 165, 1718789279);
INSERT INTO `dictionary_values` VALUES (192, 60, 'Quick_Search', '地区', 'Area', 'country_id', 1, 12, '2', 1, 1709278226, 149, 1715393778);
INSERT INTO `dictionary_values` VALUES (193, 60, 'Quick_Search', '分类', 'Classification', 'category_id', 1, 11, '3', 1, 1709278248, 149, 1715393773);
INSERT INTO `dictionary_values` VALUES (194, 60, 'Quick_Search', '金额', 'Price', 'price', 1, 10, '1', 1, 1709278280, 149, 1715393751);
INSERT INTO `dictionary_values` VALUES (195, 60, 'Quick_Search', '折扣率', 'Discount Rate', 'discount', 1, 9, '1', 1, 1709278301, 149, 1715393745);
INSERT INTO `dictionary_values` VALUES (196, 60, 'Quick_Search', '折扣金额', 'Discount Amount', 'discount_amount', 1, 8, '1', 1, 1709278328, 149, 1715393739);
INSERT INTO `dictionary_values` VALUES (197, 60, 'Quick_Search', '创建时间', 'Creation Time', 'created_at', 1, 7, '4', 1, 1709278371, 149, 1715393733);
INSERT INTO `dictionary_values` VALUES (198, 60, 'Quick_Search', '出版时间', 'Publication Time', 'published_date', 1, 6, '4', 1, 1709278391, 149, 1715393727);
INSERT INTO `dictionary_values` VALUES (199, 60, 'Quick_Search', '作者', 'Author', 'author', 1, 5, '1', 1, 1709278411, 149, 1715393721);
INSERT INTO `dictionary_values` VALUES (200, 60, 'Quick_Search', '是否热门', 'Is It Popular', 'show_hot', 1, 4, '2', 1, 1709278443, 149, 1715393715);
INSERT INTO `dictionary_values` VALUES (201, 60, 'Quick_Search', '是否精品', 'Show Recommend', 'show_recommend', 1, 3, '2', 1, 1709278468, 149, 1715393709);
INSERT INTO `dictionary_values` VALUES (202, 60, 'Quick_Search', '状态', 'Status', 'status', 1, 2, '2', 1, 1709278481, 149, 1715393701);
INSERT INTO `dictionary_values` VALUES (205, 61, 'quote_cage', '券商引用', '券商引用', '2', 1, 2, NULL, 19, 1711330867, 149, 1713497786);
INSERT INTO `dictionary_values` VALUES (206, 61, 'quote_cage', '企业引用', '企业引用', '1', 1, 1, NULL, 19, 1711330900, 149, 1713497786);
INSERT INTO `dictionary_values` VALUES (207, 61, 'quote_cage', '媒体引用', '媒体引用', '3', 1, 3, '', 149, 1711517122, 149, 1713497786);
INSERT INTO `dictionary_values` VALUES (215, 62, 'template_color', '白', 'white', 'default', 1, 1, NULL, 167, 1713495887, 165, 1713511075);
INSERT INTO `dictionary_values` VALUES (216, 62, 'template_color', '灰', 'grey', 'info', 1, 1, NULL, 167, 1713495953, 165, 1713510726);
INSERT INTO `dictionary_values` VALUES (219, 62, 'template_color', '绿', 'green', 'success', 1, 1, NULL, 165, 1713514765, 165, 1713514886);
INSERT INTO `dictionary_values` VALUES (222, 62, 'template_color', '黄', 'yellow', 'warning', 1, 1, '', 165, 1713514914, 0, 1713514914);
INSERT INTO `dictionary_values` VALUES (224, 62, 'template_color', '红', 'red', '5', 1, 1, '', 79, 1718270933, 0, 1718270933);
INSERT INTO `dictionary_values` VALUES (234, 64, 'autoSyncData', '开启', 'open', '1', 1, 1, '开启自动同步报告数据', 167, 1718607230, 0, 1718607230);
INSERT INTO `dictionary_values` VALUES (235, 64, 'autoSyncData', '关闭', 'close', '0', 1, 1, '关闭同步报告数据', 167, 1718607264, 0, 1718607264);

-- ----------------------------
-- Records of email_scenes
-- ----------------------------
INSERT INTO `email_scenes` VALUES (6, '注册验证邮箱', '注册验证邮箱', '<div style=\"display: flex; justify-content: center; padding-bottom: 60px; margin-left: 5px; margin-right: 5px;\">\n<div style=\"max-width: 760px; width: 100%; margin-top: 40px; background: #FFFFFF; box-shadow: 0px 0px 8px 0px #B2C8FF;\"><!-- 头部 -->\n<div>\n<div style=\"display: flex; justify-content: space-between; padding-left: 5%; padding-bottom: 30px;\">\n<div class=\"img_div1\" style=\"zoom: 0.9; max-width: 222px; width: 100%;\"><img class=\"img_logo\" style=\"max-width: 222px; width: 100%; max-height: 73px; height: 100%; margin-top: 20px;\" src=\"{{$backendUrl}}/site/gircn/emails/GIRLogo.webp\" alt=\"logo\"></div>\n<div class=\"img_left\" style=\"width: 100%; height: 88px; background: url(\'{{$backendUrl}}/site/gircn/emails/yeMei.webp\') no-repeat; background-size: 100% 100%; margin-left: 10%; margin-right: -1px;\">\n<p class=\"img_left_p1\" style=\"padding-top: 42px; font-size: 14px; font-weight: 400; color: #333333; text-align: right; padding-right: 12%;\">{{$dateTime}}</p>\n<p class=\"img_left_p2\" style=\"padding-top: 12px; font-size: 14px; font-weight: 400; color: #0d318c; text-align: right; padding-right: 12%;\"><a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$homeUrl}}\">首页</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$myAccountUrl}}\">我的账户</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$contactUsUrl}}\">联系我们</a></p>\n</div>\n</div>\n</div>\n<!-- 内容 -->\n<div>\n<div style=\"margin-left: 5%; margin-right: 5%;\">\n<p style=\"font-weight: 400; font-size: 14px; color: #333333; line-height: 25px; text-align: left; margin-bottom: 20px;\">尊敬的{{$userName}}，您好： <br><br>这是<a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$homeUrl}}\">{{$homePage}}</a>的自动发送邮件。 请不要回复这封邮件。 <br><br>感谢您选择了{{$siteName}}（<a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$homeUrl}}\">{{$homePage}}</a>）。<br>您的帐号为：{{$email}} ,电子邮件验证完毕后，您可以使用邮箱作为登录帐号。 <br><br>请点击下面的按钮进行邮箱验证，以便保证您的网站功能使用和账号安全。</p>\n<div style=\"margin-bottom: 20px; width: 200px; height: 40px; background: #0D318C; font-weight: 400; font-size: 14px; color: #ffffff; line-height: 40px; text-align: center;\"><a href=\"{{$verifyUrl}}\" target=\"_blank\" style=\"font-family: Microsoft YaHei; white-space: nowrap; text-decoration: none; color: #ffffff;\" rel=\"noopener\">立即验证</a></div>\n<p style=\"font-weight: 400; font-size: 14px; color: #333333; line-height: 24px; word-wrap: break-word; word-break: break-all;\">如果上面的链接不可用，请复制并粘贴以下网址到浏览器的地址栏并访问。<br><span style=\"color: #0d318c;\">{{$verifyUrl}}</span></p>\n<p style=\"font-size: 14px; font-weight: 500; color: #333333; margin-bottom: 30px; margin-top: 20px;\">如果您有任何疑问，请<a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$contactUsUrl}}\">联系我们</a>。 <br><br><br>至此,</p>\n<p style=\"font-size: 14px; font-weight: bold; color: #333333; line-height: 20px;\">{{$siteName}} 客服团队</p>\n<p style=\"font-size: 14px; font-weight: 400; color: #333333; line-height: 20px; margin-bottom: 40px;\">因为您是{{$siteName}}网站的注册会员，您会收到这封邮件。<br>想了解更多信息，请登录您的账号：<a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$homeUrl}}\">{{$homePage}}</a>并提交您的想法，或者使用联系我们的客服。</p>\n</div>\n</div>\n<!-- 底部 -->\n<div style=\"width: 100%; background: #F1F5FF; padding-top: 15px;\">\n<div class=\"bottom_div\" style=\"display: flex; flex-wrap: wrap; margin-left: 5%; margin-right: 5%;\">\n<div class=\"bottom_div_l\" style=\"width: 50%; padding-left: 4%; display: flex; flex-direction: column; justify-content: end;\">\n<p style=\"font-weight: 500; font-size: 14px; color: #0d318c; margin-bottom: 15px;\"><img src=\"{{$backendUrl}}/site/gircn/emails/phone.webp\" alt=\"phone\"> 电话: <a style=\"color: #0d318c; text-decoration: none;\" href=\"tel:{{$sitePhone}}\">{{$sitePhone}}</a></p>\n<p style=\"font-weight: 500; font-size: 14px; color: #0d318c; margin-bottom: 5px;\"><img src=\"{{$backendUrl}}/site/gircn/emails/email.webp\" alt=\"email\"> 邮箱: <a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$toSiteEmail}}\">{{$siteEmail}}</a></p>\n</div>\n<div class=\"bottom_div_r\" style=\"width: 50%; padding-left: 2%; margin-bottom: -15px;\">\n<div style=\"display: flex; justify-content: space-between;\"><img style=\"width: 200px; height: 66px; margin-top: 30px;\" src=\"{{$backendUrl}}/site/gircn/emails/logo2.webp\" alt=\"logo\"> <img style=\"width: 100px; height: 100px;\" src=\"{{$backendUrl}}/site/gircn/emails/erWeiMa.webp\" alt=\"erWeiMa\"></div>\n</div>\n</div>\n<div class=\"bottom_img1\" style=\"background: url(\'{{$backendUrl}}/site/gircn/emails/yeJiao.webp\') no-repeat; background-size: 100% 100%; width: 100%; height: 41px;\"></div>\n<div class=\"bottom_img2\" style=\"background: url(\'{{$backendUrl}}/site/gircn/emails/yeJiao.png\') no-repeat; background-size: 100% 100%; width: 100%; height: 41px;\"></div>\n</div>\n</div>\n</div>\n<style>\n    * {\n        list-style:none !important;    \n        padding: 0;\n        margin: 0;\n        box-sizing: border-box;\n        font-family: HarmonyOS Sans SC;\n        font-weight: 500;\n    }\n\n    @media screen and (max-width: 768px) {\n        /* 头部 */\n        .img_div1 {\n            max-width: 122px  !important;\n            max-height: 45px  !important;\n        }\n        .img_logo {\n            max-width: 122px  !important;\n            max-height: 45px  !important;\n        }\n        .img_left {\n            height: 60px !important;\n        }\n        .img_left_p1 {\n            padding-top: 25px !important;\n            font-size: 12px !important;\n            padding-right: 9% !important;\n        }\n        .img_left_p2 {\n            padding-top: 0px !important;\n            font-size: 12px !important;\n            padding-right: 9% !important;\n        }\n\n        /* 底部 */\n        .bottom_div {\n        }\n        .bottom_div_l {\n            width: 100% !important;\n        }\n        .bottom_div_r {\n            width: 100% !important;\n            margin-bottom: 5px !important;\n        }\n        .bottom_img1 {\n            display: none !important;\n        }\n    }\n    @media screen and (min-width: 769px) {\n        /* 底部 */\n        .bottom_img2 {\n            display: none !important;\n        }\n    }\n</style>', 2, '', 1, 0, 'register', 10, 220, 1726647150, 19, 1704958029);
INSERT INTO `email_scenes` VALUES (11, '注册成功', '注册成功', '<style>\n    * {\n        padding: 0;\n        margin: 0;\n        box-sizing: border-box;\n    }\n\n    a {\n        text-decoration: none;\n    }\n\n    ul {\n        list-style: none;\n    }\n\n    .box {\n        position: relative;\n        z-index: 99;\n        width: 90%;\n        max-width: 910px;\n        margin: 50px auto 0;\n        font-size: 14px;\n        font-family: \'Poppins\';\n        color: #4573B1;\n        box-sizing: border-box;\n        box-shadow: 0px 0px 10px 3px rgba(50, 51, 51, 0.25);\n    }\n\n    .box a {\n        color: #01508F;\n    }\n\n    p {\n        color: #333;\n    }\n\n    .header_wrap {\n        position: relative;\n    }\n\n    .header_wrap .logo_wrap {\n        display: block;\n    }\n\n    .nav {\n        position: absolute;\n        top: 36px;\n        right: 30px;\n        z-index: 33;\n        background-color: transparent;\n    }\n\n    .nav .time {\n        font-size: 14px;\n        font-family: Microsoft YaHei;\n        font-weight: 400;\n        color: #FFFFFF;\n        text-align: center;\n    }\n\n    .nav .nav_item {\n        display: flex;\n        align-items: center;\n    }\n\n    .nav .nav_item a {\n        display: inline-block;\n        margin-left: 5px;\n        margin-right: 5px;\n        font-size: 14px;\n        font-family: Microsoft YaHei;\n        font-weight: 400;\n        color: #FFFFFF;\n        text-decoration: underline;\n    }\n\n    .footer_wrap {\n        display: flex;\n        flex-wrap: wrap;\n    }\n\n    .footer_wrap .foot_log {\n        max-width: 258px;\n        padding: 8px 30px;\n        display: flex;\n        align-items: center;\n        background: #0044A5;\n    }\n\n    .footer_wrap .foot_contact {\n        flex: 1;\n        padding: 8px 0;\n        display: flex;\n        align-items: center;\n        background: #0051CE;\n    }\n\n    .footer_wrap .foot_contact .phone_,\n    .footer_wrap .foot_contact .email_ {\n        padding: 5px 30px;\n    }\n\n    .footer_wrap .foot_contact .phone_ {\n        border-right: 1px solid #fff;\n    }\n\n    .footer_wrap .foot_contact .phone_ a,\n    .footer_wrap .foot_contact .phone_ .tit span,\n    .footer_wrap .foot_contact .email_ a,\n    .footer_wrap .foot_contact .email_ .tit span {\n        font-size: 14px;\n        font-family: Microsoft YaHei;\n        font-weight: 400;\n        color: #FFFFFF;\n    }\n\n    .footer_wrap .foot_contact .phone_ a,\n    .footer_wrap .foot_contact .email_ a {\n        word-break: break-all;\n        word-wrap: break-word;\n    }\n\n    .footer_wrap .foot_contact .phone_ .tit img,\n    .footer_wrap .foot_contact .email_ .tit img {\n        vertical-align: middle;\n    }\n\n    @media screen and (min-width:768px) {\n\n        /* 头部 */\n        .logo1 {\n            display: block !important;\n        }\n\n        .nav {\n            top: 59% !important;\n            right: 7% !important;\n        }\n\n        /* 内容 */\n        .wrapper {\n            padding: 10px 25px !important;\n        }\n\n        .pading_left {\n            padding: 8px 10px 8px 25px !important;\n        }\n\n        /* // 底部 */\n        .footer_wrap {\n            position: relative !important;\n            height: 160px !important;\n            max-height: 160px !important;\n        }\n\n        .foot_img_1 {\n            display: block !important;\n            position: absolute !important;\n            top: 0 !important;\n            left: 0 !important;\n            width: 100% !important;\n            max-height: 160px !important;\n        }\n\n        .weixin {\n            position: absolute !important;\n            bottom: 5% !important;\n            left: 3% !important;\n        }\n\n        .weixin img {\n            width: 100% !important;\n            max-width: 76px !important;\n        }\n\n        .weixin p {\n            font-size: 14px !important;\n            font-family: HarmonyOS Sans SC !important;\n            font-weight: 400 !important;\n            color: #FFFFFF !important;\n            line-height: 24px !important;\n        }\n\n        .foot_left_box {\n            position: absolute !important;\n            top: 20% !important;\n            right: 3% !important;\n            width: 70% !important;\n            z-index: 2 !important;\n            display: flex !important;\n            align-items: center !important;\n            justify-content: space-between !important;\n        }\n\n        .foot_left_box img {\n            width: 100% !important;\n            max-width: 175px !important;\n        }\n\n        .icon_box {\n            display: flex !important;\n            align-items: center !important;\n        }\n\n        .icon_box img {\n            width: 14px !important;\n            height: 14px !important;\n            margin-right: 5px !important;\n        }\n\n        .icon_box p {\n            font-size: 14px !important;\n            font-family: HarmonyOS Sans SC !important;\n            font-weight: bold !important;\n            color: #025BA0 !important;\n            line-height: 43px !important;\n        }\n\n        .line_box {\n            display: block !important;\n            position: absolute !important;\n            top: 56% !important;\n            right: 3% !important;\n            width: 70% !important;\n            z-index: 2 !important;\n            height: 1px !important;\n            background: #B3C3D0 !important;\n        }\n\n        .foot_p {\n            position: absolute !important;\n            top: 65% !important;\n            right: 3% !important;\n            width: 55% !important;\n            z-index: 2;\n            font-size: 18px !important;\n            font-weight: bold !important;\n            color: #025ba0 !important;\n            text-align: right !important;\n            font-style: italic !important;\n        }\n    }\n</style>\n<div class=\"box_wrap\">\n<div class=\"box\" style=\"background-color: #fff;\"><header class=\"header_wrap\" style=\"position: relative; max-height: 80px; min-height: 75px; background-color: #025ba0;\"><a class=\"logo_wrap\" href=\"{{$homePage}}\"> <img class=\"logo1\" src=\"{{$backendUrl}}/site/mmgcn/emails/top.png\" alt=\"logo\" style=\"width: 100%; min-height: 80px; display: none;\"> </a> <a class=\"m_logo_wrap\" style=\"position: absolute; top: 28%; left: 3%;\" href=\"{{$homeUrl}}\"> <img class=\"logo\" style=\"max-width: 145px;\" src=\"{{$backendUrl}}/site/mmgcn/emails/logo.png\" alt=\"logo\"> </a>\n<div class=\"nav\" style=\"position: absolute; top: 50%; right: 2%; z-index: 33;\">\n<div class=\"nav_item\"><a class=\"ml10\" href=\"{{$homeUrl}}\">网站首页</a> | <a class=\"ml10\" href=\"{{$myAccountUrl}}\">我的账户</a> | <a class=\"ml10\" href=\"{{$contactUsUrl}}\">联系我们</a></div>\n</div>\n</header>\n<div class=\"wrapper\" style=\"padding: 10px 20px; margin: 25px 0;\">\n<div class=\"tip_box\" style=\"margin-bottom: 10px;\">\n<div class=\"tit\" style=\"margin-bottom: 10px; font-family: HarmonyOS Sans SC;\">尊敬的 {{$siteName}} 用户：</div>\n<p style=\"font-family: Microsoft YaHei; font-weight: 400; line-height: 24px; margin-top: 8px;\">您好！</p>\n<p style=\"font-family: Microsoft YaHei; font-weight: 400; line-height: 24px;\">感谢您选择了 {{$siteName}} （<a href=\"{{$homeUrl}}\" target=\"_blank\" rel=\"noopener\">{{$homePage}}</a>），您已经成功注册成为我们网站的会员，欢迎加入我们，我们将为您提供最贴心的服务，祝您购物愉快。您的帐号信息如下：</p>\n</div>\n<div style=\"font-family: Source Han Sans CN; font-weight: bold; height: 40px; background: #EBF4FB; line-height: 40px; padding-left: 20px; margin-bottom: 1px;\">帐号信息</div>\n<div class=\"Details\">\n<dl style=\"display: flex; border-bottom: 1px solid #fff; overflow: hidden; margin: 0px;\">\n<dt style=\"width: 35%; background: #EBF4FB; max-width: 150px; text-align: left; padding: 8px 0 8px 20px; border-right: 1px solid #fff; font-family: Microsoft YaHei; font-weight: 400;\">用户名</dt>\n<dd style=\"flex: 1; background: #F5FBFF; text-align: left; padding: 8px 10px 8px 5px; font-family: Microsoft YaHei; font-weight: 400;\" class=\"pading_left\">{{$userName}}</dd>\n</dl>\n<dl style=\"display: flex; border-bottom: 1px solid #fff; overflow: hidden; margin: 0px;\">\n<dt style=\"width: 35%; background: #EBF4FB; max-width: 150px; padding: 8px 0 8px 20px; border-right: 1px solid #fff; text-align: left; font-family: Microsoft YaHei; font-weight: 400;\">帐号</dt>\n<dd style=\"flex: 1; background: #F5FBFF; text-align: left; padding: 8px 10px 8px 5px; font-family: Microsoft YaHei; font-weight: 400;\" class=\"pading_left\">{{$email}}</dd>\n</dl>\n<dl style=\"display: flex; border-bottom: 1px solid #fff; overflow: hidden; margin: 0px;\">\n<dt style=\"width: 35%; background: #EBF4FB; max-width: 150px; padding: 8px 0 8px 20px; border-right: 1px solid #fff; text-align: left; font-family: Microsoft YaHei; font-weight: 400;\">地区</dt>\n<dd style=\"flex: 1; background: #F5FBFF; text-align: left; padding: 8px 10px 8px 5px; font-family: Microsoft YaHei; font-weight: 400;\" class=\"pading_left\">{{$area}}</dd>\n</dl>\n<dl style=\"display: flex; border-bottom: 1px solid #fff; overflow: hidden; margin: 0px;\">\n<dt style=\"width: 35%; background: #EBF4FB; max-width: 150px; padding: 8px 0 8px 20px; border-right: 1px solid #fff; text-align: left; font-family: Microsoft YaHei; font-weight: 400;\">电话</dt>\n<dd style=\"flex: 1; background: #F5FBFF; text-align: left; padding: 8px 10px 8px 5px; font-family: Microsoft YaHei; font-weight: 400;\" class=\"pading_left\">{{$phone}}</dd>\n</dl>\n<dl style=\"display: flex; border-bottom: 1px solid #fff; overflow: hidden; margin: 0px;\">\n<dt style=\"width: 35%; background: #EBF4FB; max-width: 150px; padding: 8px 0 8px 20px; border-right: 1px solid #fff; text-align: left; font-family: Microsoft YaHei; font-weight: 400;\">公司</dt>\n<dd style=\"flex: 1; background: #F5FBFF; text-align: left; padding: 8px 10px 8px 5px; font-family: Microsoft YaHei; font-weight: 400;\" class=\"pading_left\">{{$company}}</dd>\n</dl>\n</div>\n<br>\n<p>如果您有任何疑问或建议。请点击 <a href=\"{{$contactUsUrl}}\" style=\"font-family: Microsoft YaHei; font-weight: 400;\">联系我们</a>。</p>\n<p style=\"font-family: Microsoft YaHei; font-weight: 400;\">您之所以收到这封邮件，是因为您曾经注册成为 Market Monitor Global 的用户。本邮件由系统自动发出，请勿回复。</p>\n</div>\n<footer class=\"footer_wrap\" style=\"width: 100%; display: flex; justify-content: space-between; background-color: #b8cfe1; padding: 10px 15px;\"><img class=\"foot_img_1\" src=\"{{$backendUrl}}/site/mmgcn/emails/yeJiao.png\" alt=\"底部\" style=\"display: none;\">\n<div class=\"weixin\" style=\"display: flex; flex-direction: column; align-items: center; z-index: 2;\"><img src=\"{{$backendUrl}}/site/mmgcn/emails/erWeiMa.png\" alt=\"微信咨询\" style=\"width: 100%; max-width: 66px;\">\n<p style=\"font-family: HarmonyOS Sans SC; font-weight: 400; line-height: 24px;\">微信咨询</p>\n</div>\n<div class=\"foot_left_box\"><img src=\"{{$backendUrl}}/site/mmgcn/emails/logo2.png\" alt=\"首页\" style=\"width: 100%; max-width: 121px; margin-bottom: 3px;\">\n<div class=\"icon_box\" style=\"display: flex; align-items: center;\"><img src=\"{{$backendUrl}}/site/mmgcn/emails/phone.png\" alt=\"电话\" style=\"width: 14px; height: 14px; margin-right: 5px;\">\n<p style=\"font-family: HarmonyOS Sans SC; font-weight: bold; line-height: 25px;\">{{$sitePhone}}</p>\n</div>\n<div class=\"icon_box\" style=\"display: flex; align-items: center;\"><img src=\"{{$backendUrl}}/site/mmgcn/emails/email.png\" alt=\"电话\" style=\"width: 14px; height: 14px; margin-right: 5px;\">\n<p style=\"font-family: HarmonyOS Sans SC; font-weight: bold; line-height: 25px;\">{{$siteEmail}}</p>\n</div>\n</div>\n<div class=\"line_box\" style=\"display: none;\"></div>\n<p class=\"foot_p\" style=\"width: 100%; z-index: 2; font-weight: bold; text-align: right; font-style: italic;\">专注为企业提供资深报告 助力企业做出明确的商业决策</p>\n</footer></div>\n</div>', 2, 'hardenhst@qq.com', 1, 0, 'registerSuccess', 10, 220, 1727062479, 1, 1705385541);
INSERT INTO `email_scenes` VALUES (12, '会员重置密码', '会员重置密码', '<div style=\"display: flex; justify-content: center; padding-bottom: 60px; margin-left: 5px; margin-right: 5px;\">\n<div style=\"max-width: 760px; width: 100%; margin-top: 40px; background: #FFFFFF; box-shadow: 0px 0px 8px 0px #B2C8FF;\"><!-- 头部 -->\n<div>\n<div style=\"display: flex; justify-content: space-between; padding-left: 5%; padding-bottom: 30px;\">\n<div class=\"img_div1\" style=\"zoom: 0.9; max-width: 222px; width: 100%;\"><img class=\"img_logo\" style=\"max-width: 222px; width: 100%; max-height: 73px; height: 100%; margin-top: 20px;\" src=\"{{$backendUrl}}/site/gircn/emails/GIRLogo.webp\" alt=\"logo\"></div>\n<div class=\"img_left\" style=\"width: 100%; height: 88px; background: url(\'{{$backendUrl}}/site/gircn/emails/yeMei.webp\') no-repeat; background-size: 100% 100%; margin-left: 10%; margin-right: -1px;\">\n<p class=\"img_left_p1\" style=\"padding-top: 42px; font-size: 14px; font-weight: 400; color: #333333; text-align: right; padding-right: 12%;\">{{$dateTime}}</p>\n<p class=\"img_left_p2\" style=\"padding-top: 12px; font-size: 14px; font-weight: 400; color: #0d318c; text-align: right; padding-right: 12%;\"><a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$homeUrl}}\">首页</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$myAccountUrl}}\">我的账户</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$contactUsUrl}}\">联系我们</a></p>\n</div>\n</div>\n</div>\n<!-- 内容 -->\n<div>\n<div style=\"margin-left: 5%; margin-right: 5%;\">\n<p style=\"font-weight: 400; font-size: 14px; color: #333333; line-height: 25px; text-align: left; margin-bottom: 20px;\">尊敬的{{$userName}}，您好： <br><br>这是<a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$homeUrl}}\">{{$homePage}}</a>的自动发送邮件。 请不要回复这封邮件。 <br><br>感谢您选择了{{$siteName}}（<a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$homeUrl}}\">{{$homePage}}</a>）。 <br><br>您最近提出了重设用户密码的请求。要完成此过程，请点击下面的按钮进行验证，以便保证您的网站功能使用和账号安全。</p>\n<div style=\"margin-bottom: 20px; width: 200px; height: 40px; background: #0D318C; font-weight: 400; font-size: 14px; color: #ffffff; line-height: 40px; text-align: center;\"><a href=\"{{$verifyUrl}}\" target=\"_blank\" style=\"font-family: Microsoft YaHei; white-space: nowrap; text-decoration: none; color: #ffffff;\" rel=\"noopener\">重设密码</a></div>\n<p style=\"font-weight: 400; font-size: 14px; color: #333333; line-height: 24px; word-wrap: break-word; word-break: break-all;\">如果上面的链接不可用，请复制并粘贴以下网址到浏览器的地址栏并访问。<br><span style=\"color: #0d318c;\">{{$verifyUrl}}</span></p>\n<p style=\"font-size: 14px; font-weight: 500; color: #333333; margin-bottom: 30px; margin-top: 20px;\">如果您有任何疑问，请<a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$contactUsUrl}}\">联系我们</a>。 <br><br><br>至此,</p>\n<p style=\"font-size: 14px; font-weight: bold; color: #333333; line-height: 20px;\">{{$siteName}} 客服团队</p>\n<p style=\"font-size: 14px; font-weight: 400; color: #333333; line-height: 20px; margin-bottom: 40px;\">因为您是{{$siteName}}网站的注册会员，您会收到这封邮件。<br>想了解更多信息，请登录您的账号：<a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$homeUrl}}\">{{$homePage}}</a>并提交您的想法，或者使用联系我们的客服。</p>\n</div>\n</div>\n<!-- 底部 -->\n<div style=\"width: 100%; background: #F1F5FF; padding-top: 15px;\">\n<div class=\"bottom_div\" style=\"display: flex; flex-wrap: wrap; margin-left: 5%; margin-right: 5%;\">\n<div class=\"bottom_div_l\" style=\"width: 50%; padding-left: 4%; display: flex; flex-direction: column; justify-content: end;\">\n<p style=\"font-weight: 500; font-size: 14px; color: #0d318c; margin-bottom: 15px;\"><img src=\"{{$backendUrl}}/site/gircn/emails/phone.webp\" alt=\"phone\"> 电话: <a style=\"color: #0d318c; text-decoration: none;\" href=\"tel:{{$sitePhone}}\">{{$sitePhone}}</a></p>\n<p style=\"font-weight: 500; font-size: 14px; color: #0d318c; margin-bottom: 5px;\"><img src=\"{{$backendUrl}}/site/gircn/emails/email.webp\" alt=\"email\"> 邮箱: <a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$toSiteEmail}}\">{{$siteEmail}}</a></p>\n</div>\n<div class=\"bottom_div_r\" style=\"width: 50%; padding-left: 2%; margin-bottom: -15px;\">\n<div style=\"display: flex; justify-content: space-between;\"><img style=\"width: 200px; height: 66px; margin-top: 30px;\" src=\"{{$backendUrl}}/site/gircn/emails/logo2.webp\" alt=\"logo\"> <img style=\"width: 100px; height: 100px;\" src=\"{{$backendUrl}}/site/gircn/emails/erWeiMa.webp\" alt=\"erWeiMa\"></div>\n</div>\n</div>\n<div class=\"bottom_img1\" style=\"background: url(\'{{$backendUrl}}/site/gircn/emails/yeJiao.webp\') no-repeat; background-size: 100% 100%; width: 100%; height: 41px;\"></div>\n<div class=\"bottom_img2\" style=\"background: url(\'{{$backendUrl}}/site/gircn/emails/yeJiao.png\') no-repeat; background-size: 100% 100%; width: 100%; height: 41px;\"></div>\n</div>\n</div>\n</div>\n<style>\n    * {\n        list-style:none !important;    \n        padding: 0;\n        margin: 0;\n        box-sizing: border-box;\n        font-family: HarmonyOS Sans SC;\n        font-weight: 500;\n    }\n\n    @media screen and (max-width: 768px) {\n        /* 头部 */\n        .img_div1 {\n            max-width: 122px  !important;\n            max-height: 45px  !important;\n        }\n        .img_logo {\n            max-width: 122px  !important;\n            max-height: 45px  !important;\n        }\n        .img_left {\n            height: 60px !important;\n        }\n        .img_left_p1 {\n            padding-top: 25px !important;\n            font-size: 12px !important;\n            padding-right: 9% !important;\n        }\n        .img_left_p2 {\n            padding-top: 0px !important;\n            font-size: 12px !important;\n            padding-right: 9% !important;\n        }\n\n        /* 底部 */\n        .bottom_div {\n        }\n        .bottom_div_l {\n            width: 100% !important;\n        }\n        .bottom_div_r {\n            width: 100% !important;\n            margin-bottom: 5px !important;\n        }\n        .bottom_img1 {\n            display: none !important;\n        }\n    }\n    @media screen and (min-width: 769px) {\n        /* 底部 */\n        .bottom_img2 {\n            display: none !important;\n        }\n    }\n</style>', 2, '', 1, 0, 'password', 10, 220, 1726647145, 1, 1705385541);
INSERT INTO `email_scenes` VALUES (13, '联系我们', '联系我们', '<div style=\"display: flex; justify-content: center; padding-bottom: 60px; margin-left: 5px; margin-right: 5px;\">\n<div style=\"max-width: 760px; width: 100%; margin-top: 40px; background: #FFFFFF; box-shadow: 0px 0px 8px 0px #B2C8FF;\"><!-- 头部 -->\n<div>\n<div style=\"display: flex; justify-content: space-between; padding-left: 5%; padding-bottom: 30px;\">\n<div class=\"img_div1\" style=\"zoom: 0.9; max-width: 222px; width: 100%;\"><img class=\"img_logo\" style=\"max-width: 222px; width: 100%; max-height: 73px; height: 100%; margin-top: 20px;\" src=\"{{$backendUrl}}/site/gircn/emails/GIRLogo.webp\" alt=\"logo\"></div>\n<div class=\"img_left\" style=\"width: 100%; height: 88px; background: url(\'{{$backendUrl}}/site/gircn/emails/yeMei.webp\') no-repeat; background-size: 100% 100%; margin-left: 10%; margin-right: -1px;\">\n<p class=\"img_left_p1\" style=\"padding-top: 42px; font-size: 14px; font-weight: 400; color: #333333; text-align: right; padding-right: 12%;\">{{$dateTime}}</p>\n<p class=\"img_left_p2\" style=\"padding-top: 12px; font-size: 14px; font-weight: 400; color: #0d318c; text-align: right; padding-right: 12%;\"><a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$homeUrl}}\">首页</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$myAccountUrl}}\">我的账户</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$contactUsUrl}}\">联系我们</a></p>\n</div>\n</div>\n</div>\n<!-- 内容 -->\n<div>\n<div style=\"margin-left: 5%; margin-right: 5%;\">\n<p style=\"font-weight: 400; font-size: 14px; color: #333333; line-height: 25px; text-align: left; margin-bottom: 20px;\">尊敬的{{$userName}}，您好： <br><br>这是<a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$homeUrl}}\">{{$homePage}}</a>的自动发送邮件。 请不要回复这封邮件。 <br><br>感谢您选择了{{$siteName}}（<a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$homeUrl}}\">{{$homePage}}</a>）。<br>我们已经收到了您的联系我们需求。 <br><br>请查看以下您的需求信息:</p>\n<div style=\"width: 100%; height: 40px; background: #EEF3FF; border: 1px solid #B2BFE1; border-bottom: none; display: flex; align-items: center;\"><img src=\"{{$backendUrl}}/site/gircn/emails/biaoTi.webp\" alt=\"biaoTi\">\n<p style=\"margin-left: 8px; font-weight: bold; font-size: 16px; color: #0d318c;\">联系我们</p>\n</div>\n<ul style=\"padding: 0px; width: 100%; display: flex; flex-wrap: wrap; margin-bottom: 30px; border-left: 1px solid #B2BFE1; border-right: 1px solid #B2BFE1; border-bottom: 1px solid #B2BFE1;\">\n<li style=\"width: 36%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; border-right: 1px solid #B2BFE1; background: #EEF3FF; height: auto; line-height: 32px; font-size: 14px; font-weight: bold; color: #333;\">联系人</li>\n<li style=\"width: 64%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; background: #F7F9FF; height: auto; line-height: 32px; font-size: 14px; font-weight: 400; color: #333333;\">{{$userName}}</li>\n<li style=\"width: 36%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; border-right: 1px solid #B2BFE1; background: #EEF3FF; height: auto; line-height: 32px; font-size: 14px; font-weight: bold; color: #333;\">电子邮箱</li>\n<li style=\"width: 64%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; background: #F7F9FF; height: auto; line-height: 32px; font-size: 14px; font-weight: 400; color: #333333;\">{{$email}}</li>\n<li style=\"width: 36%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; border-right: 1px solid #B2BFE1; background: #EEF3FF; height: auto; line-height: 32px; font-size: 14px; font-weight: bold; color: #333;\">公司名称</li>\n<li style=\"width: 64%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; background: #F7F9FF; height: auto; line-height: 32px; font-size: 14px; font-weight: 400; color: #333333;\">{{$company}}</li>\n<li style=\"width: 36%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; border-right: 1px solid #B2BFE1; background: #EEF3FF; height: auto; line-height: 32px; font-size: 14px; font-weight: bold; color: #333;\">地区</li>\n<li style=\"width: 64%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; background: #F7F9FF; height: auto; line-height: 32px; font-size: 14px; font-weight: 400; color: #333333;\">{{$area}}</li>\n<li style=\"width: 36%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; border-right: 1px solid #B2BFE1; background: #EEF3FF; height: auto; line-height: 32px; font-size: 14px; font-weight: bold; color: #333;\">联系电话</li>\n<li style=\"width: 64%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; background: #F7F9FF; height: auto; line-height: 32px; font-size: 14px; font-weight: 400; color: #333333;\">{{$phone}}</li>\n<li>@if ($content!=\"\")</li>\n<li style=\"width: 36%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; border-right: 1px solid #B2BFE1; background: #EEF3FF; height: auto; line-height: 32px; font-size: 14px; font-weight: bold; color: #333;\">留言反馈</li>\n<li style=\"width: 64%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; background: #F7F9FF; height: auto; line-height: 32px; font-size: 14px; font-weight: 400; color: #333333;\">{{$content}}</li>\n<li>@endif</li>\n</ul>\n<p style=\"font-size: 14px; font-weight: 500; color: #333333; margin-bottom: 30px; margin-top: 20px;\">如果您有任何疑问，请<a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$contactUsUrl}}\">联系我们</a>。 <br><br><br>至此,</p>\n<p style=\"font-size: 14px; font-weight: bold; color: #333333; line-height: 20px;\">{{$siteName}} 客服团队</p>\n<p style=\"font-size: 14px; font-weight: 400; color: #333333; line-height: 20px; margin-bottom: 40px;\">因为您是{{$siteName}}网站的注册会员，您会收到这封邮件。<br>想了解更多信息，请登录您的账号：<a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$homeUrl}}\">{{$homePage}}</a>并提交您的想法，或者使用联系我们的客服。</p>\n</div>\n</div>\n<!-- 底部 -->\n<div style=\"width: 100%; background: #F1F5FF; padding-top: 15px;\">\n<div class=\"bottom_div\" style=\"display: flex; flex-wrap: wrap; margin-left: 5%; margin-right: 5%;\">\n<div class=\"bottom_div_l\" style=\"width: 50%; padding-left: 4%; display: flex; flex-direction: column; justify-content: end;\">\n<p style=\"font-weight: 500; font-size: 14px; color: #0d318c; margin-bottom: 15px;\"><img src=\"{{$backendUrl}}/site/gircn/emails/phone.webp\" alt=\"phone\"> 电话: <a style=\"color: #0d318c; text-decoration: none;\" href=\"tel:{{$sitePhone}}\">{{$sitePhone}}</a></p>\n<p style=\"font-weight: 500; font-size: 14px; color: #0d318c; margin-bottom: 5px;\"><img src=\"{{$backendUrl}}/site/gircn/emails/email.webp\" alt=\"email\"> 邮箱: <a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$toSiteEmail}}\">{{$siteEmail}}</a></p>\n</div>\n<div class=\"bottom_div_r\" style=\"width: 50%; padding-left: 2%; margin-bottom: -15px;\">\n<div style=\"display: flex; justify-content: space-between;\"><img style=\"width: 200px; height: 66px; margin-top: 30px;\" src=\"{{$backendUrl}}/site/gircn/emails/logo2.webp\" alt=\"logo\"> <img style=\"width: 100px; height: 100px;\" src=\"{{$backendUrl}}/site/gircn/emails/erWeiMa.webp\" alt=\"erWeiMa\"></div>\n</div>\n</div>\n<div class=\"bottom_img1\" style=\"background: url(\'{{$backendUrl}}/site/gircn/emails/yeJiao.webp\') no-repeat; background-size: 100% 100%; width: 100%; height: 41px;\"></div>\n<div class=\"bottom_img2\" style=\"background: url(\'{{$backendUrl}}/site/gircn/emails/yeJiao.png\') no-repeat; background-size: 100% 100%; width: 100%; height: 41px;\"></div>\n</div>\n</div>\n</div>\n<style>\n    * {\n        list-style:none !important;    \n        padding: 0;\n        margin: 0;\n        box-sizing: border-box;\n        font-family: HarmonyOS Sans SC;\n        font-weight: 500;\n    }\n\n    @media screen and (max-width: 768px) {\n        /* 头部 */\n        .img_div1 {\n            max-width: 122px  !important;\n            max-height: 45px  !important;\n        }\n        .img_logo {\n            max-width: 122px  !important;\n            max-height: 45px  !important;\n        }\n        .img_left {\n            height: 60px !important;\n        }\n        .img_left_p1 {\n            padding-top: 25px !important;\n            font-size: 12px !important;\n            padding-right: 9% !important;\n        }\n        .img_left_p2 {\n            padding-top: 0px !important;\n            font-size: 12px !important;\n            padding-right: 9% !important;\n        }\n\n        /* 底部 */\n        .bottom_div {\n        }\n        .bottom_div_l {\n            width: 100% !important;\n        }\n        .bottom_div_r {\n            width: 100% !important;\n            margin-bottom: 5px !important;\n        }\n        .bottom_img1 {\n            display: none !important;\n        }\n    }\n    @media screen and (min-width: 769px) {\n        /* 底部 */\n        .bottom_img2 {\n            display: none !important;\n        }\n    }\n</style>', 2, 'joe@globalinforesearch.com,sales@globalinforesearch.com,2504727233@qq.com,gupengke@qyresearch.com', 1, 0, 'contactUs', 10, 25, 1726725557, 1, 1710490505);
INSERT INTO `email_scenes` VALUES (14, '申请样本', '申请样本', '<div style=\"display: flex; justify-content: center; padding-bottom: 60px; margin-left: 5px; margin-right: 5px;\">\n<div style=\"max-width: 760px; width: 100%; margin-top: 40px; background: #FFFFFF; box-shadow: 0px 0px 8px 0px #B2C8FF;\"><!-- 头部 -->\n<div>\n<div style=\"display: flex; justify-content: space-between; padding-left: 5%; padding-bottom: 30px;\">\n<div class=\"img_div1\" style=\"zoom: 0.9; max-width: 222px; width: 100%;\"><img class=\"img_logo\" style=\"max-width: 222px; width: 100%; max-height: 73px; height: 100%; margin-top: 20px;\" src=\"{{$backendUrl}}/site/gircn/emails/GIRLogo.webp\" alt=\"logo\"></div>\n<div class=\"img_left\" style=\"width: 100%; height: 88px; background: url(\'{{$backendUrl}}/site/gircn/emails/yeMei.webp\') no-repeat; background-size: 100% 100%; margin-left: 10%; margin-right: -1px;\">\n<p class=\"img_left_p1\" style=\"padding-top: 42px; font-size: 14px; font-weight: 400; color: #333333; text-align: right; padding-right: 12%;\">{{$dateTime}}</p>\n<p class=\"img_left_p2\" style=\"padding-top: 12px; font-size: 14px; font-weight: 400; color: #0d318c; text-align: right; padding-right: 12%;\"><a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$homeUrl}}\">首页</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$myAccountUrl}}\">我的账户</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$contactUsUrl}}\">联系我们</a></p>\n</div>\n</div>\n</div>\n<!-- 内容 -->\n<div>\n<div style=\"margin-left: 5%; margin-right: 5%;\">\n<p style=\"font-weight: 400; font-size: 14px; color: #333333; line-height: 25px; text-align: left; margin-bottom: 20px;\">尊敬的{{$userName}}，您好： <br><br>这是<a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$homeUrl}}\">{{$homePage}}</a>的自动发送邮件。 请不要回复这封邮件。 <br><br>感谢您选择了{{$siteName}}（<a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$homeUrl}}\">{{$homePage}}</a>）。<br>我们已经收到了您的申请样本需求。 <br><br>请查看以下您的需求信息:</p>\n<div style=\"width: 100%; height: 40px; background: #EEF3FF; border: 1px solid #B2BFE1; border-bottom: none; display: flex; align-items: center;\"><img src=\"{{$backendUrl}}/site/gircn/emails/biaoTi.webp\" alt=\"biaoTi\">\n<p style=\"margin-left: 8px; font-weight: bold; font-size: 16px; color: #0d318c;\">申请样本</p>\n</div>\n<ul style=\"padding: 0px; width: 100%; display: flex; flex-wrap: wrap; margin-bottom: 30px; border-left: 1px solid #B2BFE1; border-right: 1px solid #B2BFE1; border-bottom: 1px solid #B2BFE1;\">\n<li>@if ($productsName!=\"\")</li>\n<li style=\"width: 36%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; border-right: 1px solid #B2BFE1; background: #EEF3FF; height: auto; line-height: 32px; font-size: 14px; font-weight: bold; color: #333;\">报告</li>\n<li style=\"width: 64%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; background: #F7F9FF; height: auto; line-height: 32px; font-size: 14px; font-weight: 400; color: #333333;\"><a class=\"ml10\" href=\"{{$link}}\">{{$productsName}}</a></li>\n<li>@endif</li>\n<li style=\"width: 36%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; border-right: 1px solid #B2BFE1; background: #EEF3FF; height: auto; line-height: 32px; font-size: 14px; font-weight: bold; color: #333;\">联系人</li>\n<li style=\"width: 64%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; background: #F7F9FF; height: auto; line-height: 32px; font-size: 14px; font-weight: 400; color: #333333;\">{{$userName}}</li>\n<li style=\"width: 36%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; border-right: 1px solid #B2BFE1; background: #EEF3FF; height: auto; line-height: 32px; font-size: 14px; font-weight: bold; color: #333;\">电子邮箱</li>\n<li style=\"width: 64%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; background: #F7F9FF; height: auto; line-height: 32px; font-size: 14px; font-weight: 400; color: #333333;\">{{$email}}</li>\n<li style=\"width: 36%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; border-right: 1px solid #B2BFE1; background: #EEF3FF; height: auto; line-height: 32px; font-size: 14px; font-weight: bold; color: #333;\">公司名称</li>\n<li style=\"width: 64%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; background: #F7F9FF; height: auto; line-height: 32px; font-size: 14px; font-weight: 400; color: #333333;\">{{$company}}</li>\n<li style=\"width: 36%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; border-right: 1px solid #B2BFE1; background: #EEF3FF; height: auto; line-height: 32px; font-size: 14px; font-weight: bold; color: #333;\">地区</li>\n<li style=\"width: 64%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; background: #F7F9FF; height: auto; line-height: 32px; font-size: 14px; font-weight: 400; color: #333333;\">{{$area}}</li>\n<li style=\"width: 36%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; border-right: 1px solid #B2BFE1; background: #EEF3FF; height: auto; line-height: 32px; font-size: 14px; font-weight: bold; color: #333;\">联系电话</li>\n<li style=\"width: 64%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; background: #F7F9FF; height: auto; line-height: 32px; font-size: 14px; font-weight: 400; color: #333333;\">{{$phone}}</li>\n<li style=\"width: 36%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; border-right: 1px solid #B2BFE1; background: #EEF3FF; height: auto; line-height: 32px; font-size: 14px; font-weight: bold; color: #333;\">计划购买时间</li>\n<li style=\"width: 64%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; background: #F7F9FF; height: auto; line-height: 32px; font-size: 14px; font-weight: 400; color: #333333;\">{{$buy_time}}</li>\n<li>@if ($content!=\"\")</li>\n<li style=\"width: 36%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; border-right: 1px solid #B2BFE1; background: #EEF3FF; height: auto; line-height: 32px; font-size: 14px; font-weight: bold; color: #333;\">内容补充</li>\n<li style=\"width: 64%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; background: #F7F9FF; height: auto; line-height: 32px; font-size: 14px; font-weight: 400; color: #333333;\">{{$content}}</li>\n<li>@endif</li>\n</ul>\n<p style=\"font-size: 14px; font-weight: 500; color: #333333; margin-bottom: 30px; margin-top: 20px;\">如果您有任何疑问，请<a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$contactUsUrl}}\">联系我们</a>。 <br><br><br>至此,</p>\n<p style=\"font-size: 14px; font-weight: bold; color: #333333; line-height: 20px;\">{{$siteName}} 客服团队</p>\n<p style=\"font-size: 14px; font-weight: 400; color: #333333; line-height: 20px; margin-bottom: 40px;\">因为您是{{$siteName}}网站的注册会员，您会收到这封邮件。<br>想了解更多信息，请登录您的账号：<a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$homeUrl}}\">{{$homePage}}</a>并提交您的想法，或者使用联系我们的客服。</p>\n</div>\n</div>\n<!-- 底部 -->\n<div style=\"width: 100%; background: #F1F5FF; padding-top: 15px;\">\n<div class=\"bottom_div\" style=\"display: flex; flex-wrap: wrap; margin-left: 5%; margin-right: 5%;\">\n<div class=\"bottom_div_l\" style=\"width: 50%; padding-left: 4%; display: flex; flex-direction: column; justify-content: end;\">\n<p style=\"font-weight: 500; font-size: 14px; color: #0d318c; margin-bottom: 15px;\"><img src=\"{{$backendUrl}}/site/gircn/emails/phone.webp\" alt=\"phone\"> 电话: <a style=\"color: #0d318c; text-decoration: none;\" href=\"tel:{{$sitePhone}}\">{{$sitePhone}}</a></p>\n<p style=\"font-weight: 500; font-size: 14px; color: #0d318c; margin-bottom: 5px;\"><img src=\"{{$backendUrl}}/site/gircn/emails/email.webp\" alt=\"email\"> 邮箱: <a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$toSiteEmail}}\">{{$siteEmail}}</a></p>\n</div>\n<div class=\"bottom_div_r\" style=\"width: 50%; padding-left: 2%; margin-bottom: -15px;\">\n<div style=\"display: flex; justify-content: space-between;\"><img style=\"width: 200px; height: 66px; margin-top: 30px;\" src=\"{{$backendUrl}}/site/gircn/emails/logo2.webp\" alt=\"logo\"> <img style=\"width: 100px; height: 100px;\" src=\"{{$backendUrl}}/site/gircn/emails/erWeiMa.webp\" alt=\"erWeiMa\"></div>\n</div>\n</div>\n<div class=\"bottom_img1\" style=\"background: url(\'{{$backendUrl}}/site/gircn/emails/yeJiao.webp\') no-repeat; background-size: 100% 100%; width: 100%; height: 41px;\"></div>\n<div class=\"bottom_img2\" style=\"background: url(\'{{$backendUrl}}/site/gircn/emails/yeJiao.png\') no-repeat; background-size: 100% 100%; width: 100%; height: 41px;\"></div>\n</div>\n</div>\n</div>\n<style>\n    * {\n        list-style:none !important;    \n        padding: 0;\n        margin: 0;\n        box-sizing: border-box;\n        font-family: HarmonyOS Sans SC ;\n        font-weight: 500;\n    }\n\n    @media screen and (max-width: 768px) {\n        /* 头部 */\n        .img_div1 {\n            max-width: 122px  !important;\n            max-height: 45px  !important;\n        }\n        .img_logo {\n            max-width: 122px  !important;\n            max-height: 45px  !important;\n        }\n        .img_left {\n            height: 60px !important;\n        }\n        .img_left_p1 {\n            padding-top: 25px !important;\n            font-size: 12px !important;\n            padding-right: 9% !important;\n        }\n        .img_left_p2 {\n            padding-top: 0px !important;\n            font-size: 12px !important;\n            padding-right: 9% !important;\n        }\n\n        /* 底部 */\n        .bottom_div {\n        }\n        .bottom_div_l {\n            width: 100% !important;\n        }\n        .bottom_div_r {\n            width: 100% !important;\n            margin-bottom: 5px !important;\n        }\n        .bottom_img1 {\n            display: none !important;\n        }\n    }\n    @media screen and (min-width: 769px) {\n        /* 底部 */\n        .bottom_img2 {\n            display: none !important;\n        }\n    }\n</style>', 2, 'joe@globalinforesearch.com,sales@globalinforesearch.com,2504727233@qq.com,gupengke@qyresearch.com', 1, 0, 'productSample', 10, 25, 1727251684, 1, 1710490604);
INSERT INTO `email_scenes` VALUES (16, '定制报告', '定制报告', '<div style=\"display: flex; justify-content: center; padding-bottom: 60px; margin-left: 5px; margin-right: 5px;\">\n<div style=\"max-width: 760px; width: 100%; margin-top: 40px; background: #FFFFFF; box-shadow: 0px 0px 8px 0px #B2C8FF;\"><!-- 头部 -->\n<div>\n<div style=\"display: flex; justify-content: space-between; padding-left: 5%; padding-bottom: 30px;\">\n<div class=\"img_div1\" style=\"zoom: 0.9; max-width: 222px; width: 100%;\"><img class=\"img_logo\" style=\"max-width: 222px; width: 100%; max-height: 73px; height: 100%; margin-top: 20px;\" src=\"{{$backendUrl}}/site/gircn/emails/GIRLogo.webp\" alt=\"logo\"></div>\n<div class=\"img_left\" style=\"width: 100%; height: 88px; background: url(\'{{$backendUrl}}/site/gircn/emails/yeMei.webp\') no-repeat; background-size: 100% 100%; margin-left: 10%; margin-right: -1px;\">\n<p class=\"img_left_p1\" style=\"padding-top: 42px; font-size: 14px; font-weight: 400; color: #333333; text-align: right; padding-right: 12%;\">{{$dateTime}}</p>\n<p class=\"img_left_p2\" style=\"padding-top: 12px; font-size: 14px; font-weight: 400; color: #0d318c; text-align: right; padding-right: 12%;\"><a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$homeUrl}}\">首页</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$myAccountUrl}}\">我的账户</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$contactUsUrl}}\">联系我们</a></p>\n</div>\n</div>\n</div>\n<!-- 内容 -->\n<div>\n<div style=\"margin-left: 5%; margin-right: 5%;\">\n<p style=\"font-weight: 400; font-size: 14px; color: #333333; line-height: 25px; text-align: left; margin-bottom: 20px;\">尊敬的{{$userName}}，您好： <br><br>这是<a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$homeUrl}}\">{{$homePage}}</a>的自动发送邮件。 请不要回复这封邮件。 <br><br>感谢您选择了{{$siteName}}（<a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$homeUrl}}\">{{$homePage}}</a>）。<br>我们已经收到了您的定制报告需求。 <br><br>请查看以下您的需求信息:</p>\n<div style=\"width: 100%; height: 40px; background: #EEF3FF; border: 1px solid #B2BFE1; border-bottom: none; display: flex; align-items: center;\"><img src=\"{{$backendUrl}}/site/gircn/emails/biaoTi.webp\" alt=\"biaoTi\">\n<p style=\"margin-left: 8px; font-weight: bold; font-size: 16px; color: #0d318c;\">定制报告</p>\n</div>\n<ul style=\"padding: 0px; width: 100%; display: flex; flex-wrap: wrap; margin-bottom: 30px; border-left: 1px solid #B2BFE1; border-right: 1px solid #B2BFE1; border-bottom: 1px solid #B2BFE1;\">\n<li>@if ($productsName!=\"\")</li>\n<li style=\"width: 36%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; border-right: 1px solid #B2BFE1; background: #EEF3FF; height: auto; line-height: 32px; font-size: 14px; font-weight: bold; color: #333;\">报告</li>\n<li style=\"width: 64%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; background: #F7F9FF; height: auto; line-height: 32px; font-size: 14px; font-weight: 400; color: #333333;\"><a class=\"ml10\" href=\"{{$link}}\">{{$productsName}}</a></li>\n<li>@endif</li>\n<li style=\"width: 36%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; border-right: 1px solid #B2BFE1; background: #EEF3FF; height: auto; line-height: 32px; font-size: 14px; font-weight: bold; color: #333;\">联系人</li>\n<li style=\"width: 64%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; background: #F7F9FF; height: auto; line-height: 32px; font-size: 14px; font-weight: 400; color: #333333;\">{{$userName}}</li>\n<li style=\"width: 36%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; border-right: 1px solid #B2BFE1; background: #EEF3FF; height: auto; line-height: 32px; font-size: 14px; font-weight: bold; color: #333;\">电子邮箱</li>\n<li style=\"width: 64%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; background: #F7F9FF; height: auto; line-height: 32px; font-size: 14px; font-weight: 400; color: #333333;\">{{$email}}</li>\n<li style=\"width: 36%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; border-right: 1px solid #B2BFE1; background: #EEF3FF; height: auto; line-height: 32px; font-size: 14px; font-weight: bold; color: #333;\">公司名称</li>\n<li style=\"width: 64%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; background: #F7F9FF; height: auto; line-height: 32px; font-size: 14px; font-weight: 400; color: #333333;\">{{$company}}</li>\n<li style=\"width: 36%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; border-right: 1px solid #B2BFE1; background: #EEF3FF; height: auto; line-height: 32px; font-size: 14px; font-weight: bold; color: #333;\">地区</li>\n<li style=\"width: 64%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; background: #F7F9FF; height: auto; line-height: 32px; font-size: 14px; font-weight: 400; color: #333333;\">{{$area}}</li>\n<li style=\"width: 36%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; border-right: 1px solid #B2BFE1; background: #EEF3FF; height: auto; line-height: 32px; font-size: 14px; font-weight: bold; color: #333;\">联系电话</li>\n<li style=\"width: 64%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; background: #F7F9FF; height: auto; line-height: 32px; font-size: 14px; font-weight: 400; color: #333333;\">{{$phone}}</li>\n<li>@if ($buy_time!=\"\" and $buy_time != 0)</li>\n<li style=\"width: 36%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; border-right: 1px solid #B2BFE1; background: #EEF3FF; height: auto; line-height: 32px; font-size: 14px; font-weight: bold; color: #333;\">计划购买时间</li>\n<li style=\"width: 64%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; background: #F7F9FF; height: auto; line-height: 32px; font-size: 14px; font-weight: 400; color: #333333;\">{{$buy_time}}</li>\n<li>@endif</li>\n<li>@if ($content!=\"\")</li>\n<li style=\"width: 36%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; border-right: 1px solid #B2BFE1; background: #EEF3FF; height: auto; line-height: 32px; font-size: 14px; font-weight: bold; color: #333;\">内容补充</li>\n<li style=\"width: 64%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; background: #F7F9FF; height: auto; line-height: 32px; font-size: 14px; font-weight: 400; color: #333333;\">{{$content}}</li>\n<li>@endif</li>\n</ul>\n<p style=\"font-size: 14px; font-weight: 500; color: #333333; margin-bottom: 30px; margin-top: 20px;\">如果您有任何疑问，请<a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$contactUsUrl}}\">联系我们</a>。 <br><br><br>至此,</p>\n<p style=\"font-size: 14px; font-weight: bold; color: #333333; line-height: 20px;\">{{$siteName}} 客服团队</p>\n<p style=\"font-size: 14px; font-weight: 400; color: #333333; line-height: 20px; margin-bottom: 40px;\">因为您是{{$siteName}}网站的注册会员，您会收到这封邮件。<br>想了解更多信息，请登录您的账号：<a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$homeUrl}}\">{{$homePage}}</a>并提交您的想法，或者使用联系我们的客服。</p>\n</div>\n</div>\n<!-- 底部 -->\n<div style=\"width: 100%; background: #F1F5FF; padding-top: 15px;\">\n<div class=\"bottom_div\" style=\"display: flex; flex-wrap: wrap; margin-left: 5%; margin-right: 5%;\">\n<div class=\"bottom_div_l\" style=\"width: 50%; padding-left: 4%; display: flex; flex-direction: column; justify-content: end;\">\n<p style=\"font-weight: 500; font-size: 14px; color: #0d318c; margin-bottom: 15px;\"><img src=\"{{$backendUrl}}/site/gircn/emails/phone.webp\" alt=\"phone\"> 电话: <a style=\"color: #0d318c; text-decoration: none;\" href=\"tel:{{$sitePhone}}\">{{$sitePhone}}</a></p>\n<p style=\"font-weight: 500; font-size: 14px; color: #0d318c; margin-bottom: 5px;\"><img src=\"{{$backendUrl}}/site/gircn/emails/email.webp\" alt=\"email\"> 邮箱: <a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$toSiteEmail}}\">{{$siteEmail}}</a></p>\n</div>\n<div class=\"bottom_div_r\" style=\"width: 50%; padding-left: 2%; margin-bottom: -15px;\">\n<div style=\"display: flex; justify-content: space-between;\"><img style=\"width: 200px; height: 66px; margin-top: 30px;\" src=\"{{$backendUrl}}/site/gircn/emails/logo2.webp\" alt=\"logo\"> <img style=\"width: 100px; height: 100px;\" src=\"{{$backendUrl}}/site/gircn/emails/erWeiMa.webp\" alt=\"erWeiMa\"></div>\n</div>\n</div>\n<div class=\"bottom_img1\" style=\"background: url(\'{{$backendUrl}}/site/gircn/emails/yeJiao.webp\') no-repeat; background-size: 100% 100%; width: 100%; height: 41px;\"></div>\n<div class=\"bottom_img2\" style=\"background: url(\'{{$backendUrl}}/site/gircn/emails/yeJiao.png\') no-repeat; background-size: 100% 100%; width: 100%; height: 41px;\"></div>\n</div>\n</div>\n</div>\n<style>\n    * {\n        list-style: none !important;\n        padding: 0;\n        margin: 0;\n        box-sizing: border-box;\n        font-family: HarmonyOS Sans SC;\n        font-weight: 500;\n    }\n\n    @media screen and (max-width: 768px) {\n\n        /* 头部 */\n        .img_div1 {\n            max-width: 122px !important;\n            max-height: 45px !important;\n        }\n\n        .img_logo {\n            max-width: 122px !important;\n            max-height: 45px !important;\n        }\n\n        .img_left {\n            height: 60px !important;\n        }\n\n        .img_left_p1 {\n            padding-top: 25px !important;\n            font-size: 12px !important;\n            padding-right: 9% !important;\n        }\n\n        .img_left_p2 {\n            padding-top: 0px !important;\n            font-size: 12px !important;\n            padding-right: 9% !important;\n        }\n\n        /* 底部 */\n        .bottom_div {}\n\n        .bottom_div_l {\n            width: 100% !important;\n        }\n\n        .bottom_div_r {\n            width: 100% !important;\n            margin-bottom: 5px !important;\n        }\n\n        .bottom_img1 {\n            display: none !important;\n        }\n    }\n\n    @media screen and (min-width: 769px) {\n\n        /* 底部 */\n        .bottom_img2 {\n            display: none !important;\n        }\n    }\n</style>', 2, 'joe@globalinforesearch.com,sales@globalinforesearch.com,gupengke@qyresearch.com', 1, 0, 'customized', 10, 25, 1726733856, 1, 1710490694);
INSERT INTO `email_scenes` VALUES (17, '下单付款成功', '下单付款成功', '<div style=\"display: flex; justify-content: center; padding-bottom: 60px; margin-left: 5px; margin-right: 5px;\">\n<div style=\"max-width: 760px; width: 100%; margin-top: 40px; background: #FFFFFF; box-shadow: 0px 0px 8px 0px #B2C8FF;\"><!-- 头部 -->\n<div>\n<div style=\"display: flex; justify-content: space-between; padding-left: 5%; padding-bottom: 30px;\">\n<div class=\"img_div1\" style=\"zoom: 0.9; max-width: 222px; width: 100%;\"><img class=\"img_logo\" style=\"max-width: 222px; width: 100%; max-height: 73px; height: 100%; margin-top: 20px;\" src=\"{{$backendUrl}}/site/gircn/emails/GIRLogo.webp\" alt=\"logo\"></div>\n<div class=\"img_left\" style=\"width: 100%; height: 88px; background: url(\'{{$backendUrl}}/site/gircn/emails/yeMei.webp\') no-repeat; background-size: 100% 100%; margin-left: 10%; margin-right: -1px;\">\n<p class=\"img_left_p1\" style=\"padding-top: 42px; font-size: 14px; font-weight: 400; color: #333333; text-align: right; padding-right: 12%;\">{{$dateTime}}</p>\n<p class=\"img_left_p2\" style=\"padding-top: 12px; font-size: 14px; font-weight: 400; color: #0d318c; text-align: right; padding-right: 12%;\"><a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$homeUrl}}\">首页</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$myAccountUrl}}\">我的账户</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$contactUsUrl}}\">联系我们</a></p>\n</div>\n</div>\n</div>\n<!-- 内容 -->\n<div>\n<div style=\"margin-left: 5%; margin-right: 5%;\">\n<p style=\"font-weight: 400; font-size: 14px; color: #333333; line-height: 25px; text-align: left; margin-bottom: 20px;\">尊敬的{{$userName}}，您好： <br><br>这是<a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$homeUrl}}\">{{$homePage}}</a>的自动发送邮件。 请不要回复这封邮件。 <br><br>感谢您选择在{{$siteName}}（<a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$homeUrl}}\">{{$homePage}}</a>）订购。 <br>我们已经收到了您的订单，订单号：<span style=\"color: #0d318c;\">{{$orderNumber}}</span>。 <br><br><span style=\"font-weight: bold;\">您的订单已经完成支付，我们的报告将会在48小时内发送到您的邮箱{{$userEmail}}</span></p>\n<p style=\"font-weight: bold; font-size: 16px; color: #0d318c; margin-bottom: 20px;\">订单汇总</p>\n<div style=\"margin-bottom: 20px;\">\n<div style=\"background: #EEF3FF; display: flex; padding-left: 10px; padding-right: 10px; margin-bottom: 10px;\">\n<div class=\"div1_w\" style=\"width: 43%; margin-top: 12px; margin-bottom: 15px; font-weight: bold; font-size: 14px; color: #333333;\">报告名称</div>\n<div style=\"width: 15%; margin-top: 12px; margin-bottom: 15px; font-weight: bold; font-size: 14px; color: #333333;\">语言</div>\n<div style=\"width: 20%; margin-top: 12px; margin-bottom: 15px; font-weight: bold; font-size: 14px; color: #333333;\">版本</div>\n<div style=\"width: 10%; margin-top: 12px; margin-bottom: 15px; font-weight: bold; font-size: 14px; color: #333333;\">数量</div>\n<div class=\"div1_w2\" style=\"width: 12%; margin-top: 12px; margin-bottom: 15px; font-weight: bold; font-size: 14px; color: #333333;\">价格</div>\n</div>\n@foreach ($goods as $item)\n<div style=\"display: flex; padding-left: 10px; padding-right: 10px;\">\n<div class=\"div1_w\" style=\"width: 43%; font-weight: 500; font-size: 14px; color: #333333; display: flex;\"><img class=\"img1\" src=\"{{$item[\'thumb\']}}\" alt=\"book\" style=\"width: 61px; height: 80px; margin-right: 5px;\">\n<div style=\"padding-right: 5px;\">\n<p style=\"font-weight: 400; font-size: 13px; color: #333333; line-height: 20px;\"><a href=\"{{$item[\'link\']}}\">{{$item[\'name\']}}</a></p>\n<p style=\"font-weight: 400; font-size: 12px; color: #666666; line-height: 20px;\">报告编码:{{$item[\'product_id\']}}</p>\n</div>\n</div>\n<div style=\"width: 15%; font-weight: 400; font-size: 13px; color: #333333; word-wrap: break-word; word-break: break-all;\">{{$item[\'language\']}}</div>\n<div style=\"width: 20%; font-weight: 400; font-size: 13px; color: #333333; word-wrap: break-word; word-break: break-all;\">{{$item[\'price_edition\']}}</div>\n<div style=\"width: 10%; font-weight: 400; font-size: 13px; color: #333333;\">{{$item[\'goods_number\']}}</div>\n<div class=\"div1_w2\" style=\"width: 12%; font-weight: 400; font-size: 13px; color: #333333; word-wrap: break-word; word-break: break-all;\">￥{{$item[\'goods_present_price\']}}</div>\n</div>\n@endforeach\n<div style=\"height: 1px; border: 1px solid #E3E9F9; margin-bottom: 10px; margin-top: 20px;\"></div>\n<div style=\"display: flex; justify-content: flex-end; padding-left: 10px; padding-right: 10px;\">\n<div style=\"margin-bottom: 15px; font-weight: 500; font-size: 14px; color: #333333; margin-right: 20px;\"><span>订单总额：</span><br><span>优惠金额：</span><br><span>实付金额：</span></div>\n<div style=\"margin-bottom: 15px; font-weight: 500; font-size: 14px; color: #333333; text-align: right;\"><span>￥ {{$orderAmount}}</span><br><span>-￥{{$preferentialAmount}}</span><br><span style=\"color: #d53801; font-weight: bold;\">￥{{$orderActuallyPaid}}</span></div>\n</div>\n</div>\n<div style=\"width: 100%; height: 40px; background: #EEF3FF; border: 1px solid #B2BFE1; border-bottom: none; display: flex; align-items: center;\"><img src=\"biaoTi.webp\" alt=\"biaoTi\">\n<p style=\"margin-left: 8px; font-weight: bold; font-size: 16px; color: #0d318c;\">订单详情</p>\n</div>\n<ul style=\"padding: 0px; width: 100%; display: flex; flex-wrap: wrap; margin-bottom: 30px; border-left: 1px solid #B2BFE1; border-right: 1px solid #B2BFE1; border-bottom: 1px solid #B2BFE1;\">\n<li style=\"width: 36%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; border-right: 1px solid #B2BFE1; background: #EEF3FF; height: auto; line-height: 32px; font-size: 14px; font-weight: bold; color: #333;\">联系人</li>\n<li style=\"width: 64%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; background: #F7F9FF; height: auto; line-height: 32px; font-size: 14px; font-weight: 400; color: #333333;\">{{$userName}}</li>\n<li style=\"width: 36%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; border-right: 1px solid #B2BFE1; background: #EEF3FF; height: auto; line-height: 32px; font-size: 14px; font-weight: bold; color: #333;\">电子邮箱</li>\n<li style=\"width: 64%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; background: #F7F9FF; height: auto; line-height: 32px; font-size: 14px; font-weight: 400; color: #333333;\">{{$userEmail}}</li>\n<li style=\"width: 36%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; border-right: 1px solid #B2BFE1; background: #EEF3FF; height: auto; line-height: 32px; font-size: 14px; font-weight: bold; color: #333;\">公司名称</li>\n<li style=\"width: 64%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; background: #F7F9FF; height: auto; line-height: 32px; font-size: 14px; font-weight: 400; color: #333333;\">{{$userCompany}}</li>\n<li style=\"width: 36%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; border-right: 1px solid #B2BFE1; background: #EEF3FF; height: auto; line-height: 32px; font-size: 14px; font-weight: bold; color: #333;\">地区</li>\n<li style=\"width: 64%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; background: #F7F9FF; height: auto; line-height: 32px; font-size: 14px; font-weight: 400; color: #333333;\">{{$userAddress}}</li>\n<li style=\"width: 36%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; border-right: 1px solid #B2BFE1; background: #EEF3FF; height: auto; line-height: 32px; font-size: 14px; font-weight: bold; color: #333;\">联系电话</li>\n<li style=\"width: 64%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; background: #F7F9FF; height: auto; line-height: 32px; font-size: 14px; font-weight: 400; color: #333333;\">{{$userPhone}}</li>\n<li style=\"width: 36%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; border-right: 1px solid #B2BFE1; background: #EEF3FF; height: auto; line-height: 32px; font-size: 14px; font-weight: bold; color: #333;\">订单状态</li>\n<li style=\"width: 64%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; background: #F7F9FF; height: auto; line-height: 32px; font-size: 14px; font-weight: 400; color: #333333;\">{{$orderStatus}}</li>\n<li style=\"width: 36%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; border-right: 1px solid #B2BFE1; background: #EEF3FF; height: auto; line-height: 32px; font-size: 14px; font-weight: bold; color: #333;\">支付方式</li>\n<li style=\"width: 64%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; background: #F7F9FF; height: auto; line-height: 32px; font-size: 14px; font-weight: 400; color: #333333;\">{{$paymentMethod}}</li>\n<li style=\"width: 36%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; border-right: 1px solid #B2BFE1; background: #EEF3FF; height: auto; line-height: 32px; font-size: 14px; font-weight: bold; color: #333;\">订单总额</li>\n<li style=\"width: 64%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; background: #F7F9FF; height: auto; line-height: 32px; font-size: 14px; font-weight: 400; color: #333333;\">￥{{$orderAmount}}</li>\n<li style=\"width: 36%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; border-right: 1px solid #B2BFE1; background: #EEF3FF; height: auto; line-height: 32px; font-size: 14px; font-weight: bold; color: #333;\">优惠金额</li>\n<li style=\"width: 64%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; background: #F7F9FF; height: auto; line-height: 32px; font-size: 14px; font-weight: 400; color: #333333;\">￥{{$preferentialAmount}}</li>\n<li style=\"width: 36%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; border-right: 1px solid #B2BFE1; background: #EEF3FF; height: auto; line-height: 32px; font-size: 14px; font-weight: bold; color: #333;\">实付金额</li>\n<li style=\"width: 64%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; background: #F7F9FF; height: auto; line-height: 32px; font-size: 14px; font-weight: 400; color: #333333;\">￥{{$orderActuallyPaid}}</li>\n</ul>\n<p style=\"font-size: 14px; font-weight: 500; color: #333333; margin-bottom: 30px; margin-top: 40px;\">如果您有任何疑问，请<a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$contactUsUrl}}\">联系我们</a>。 <br><br><br>至此,</p>\n<p style=\"font-size: 14px; font-weight: bold; color: #333333; line-height: 20px;\">{{$siteName}} 客服团队</p>\n<p style=\"font-size: 14px; font-weight: 400; color: #333333; line-height: 20px; margin-bottom: 40px;\">因为您是{{$siteName}}网站的注册会员，您会收到这封邮件。<br>想了解更多信息，请登录您的账号：<a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$homeUrl}}\">{{$homePage}}</a>并提交您的想法，或者使用联系我们的客服。</p>\n</div>\n</div>\n<!-- 底部 -->\n<div style=\"width: 100%; background: #F1F5FF; padding-top: 15px;\">\n<div class=\"bottom_div\" style=\"display: flex; flex-wrap: wrap; margin-left: 5%; margin-right: 5%;\">\n<div class=\"bottom_div_l\" style=\"width: 50%; padding-left: 4%; display: flex; flex-direction: column; justify-content: end;\">\n<p style=\"font-weight: 500; font-size: 14px; color: #0d318c; margin-bottom: 15px;\"><img src=\"{{$backendUrl}}/site/gircn/emails/phone.webp\" alt=\"phone\"> 电话: <a style=\"color: #0d318c; text-decoration: none;\" href=\"tel:{{$sitePhone}}\">{{$sitePhone}}</a></p>\n<p style=\"font-weight: 500; font-size: 14px; color: #0d318c; margin-bottom: 5px;\"><img src=\"{{$backendUrl}}/site/gircn/emails/email.webp\" alt=\"email\"> 邮箱: <a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$toSiteEmail}}\">{{$siteEmail}}</a></p>\n</div>\n<div class=\"bottom_div_r\" style=\"width: 50%; padding-left: 2%; margin-bottom: -15px;\">\n<div style=\"display: flex; justify-content: space-between;\"><img style=\"width: 200px; height: 66px; margin-top: 30px;\" src=\"{{$backendUrl}}/site/gircn/emails/logo2.webp\" alt=\"logo\"> <img style=\"width: 100px; height: 100px;\" src=\"{{$backendUrl}}/site/gircn/emails/erWeiMa.webp\" alt=\"erWeiMa\"></div>\n</div>\n</div>\n<div class=\"bottom_img1\" style=\"background: url(\'{{$backendUrl}}/site/gircn/emails/yeJiao.webp\') no-repeat; background-size: 100% 100%; width: 100%; height: 41px;\"></div>\n<div class=\"bottom_img2\" style=\"background: url(\'{{$backendUrl}}/site/gircn/emails/yeJiao.png\') no-repeat; background-size: 100% 100%; width: 100%; height: 41px;\"></div>\n</div>\n</div>\n</div>\n<style>\n    * {\n        list-style:none !important;    \n        padding: 0;\n        margin: 0;\n        box-sizing: border-box;\n        font-family: HarmonyOS Sans SC ;\n        font-weight: 500;\n    }\n\n    @media screen and (max-width: 768px) {\n        /* 头部 */\n        .img_div1 {\n            max-width: 122px  !important;\n            max-height: 45px  !important;\n        }\n        .img_logo {\n            max-width: 122px  !important;\n            max-height: 45px  !important;\n        }\n        .img_left {\n            height: 60px !important;\n        }\n        .img_left_p1 {\n            padding-top: 25px !important;\n            font-size: 12px !important;\n            padding-right: 9% !important;\n        }\n        .img_left_p2 {\n            padding-top: 0px !important;\n            font-size: 12px !important;\n            padding-right: 9% !important;\n        }\n\n        /* 内容 */\n        .img1 {\n            display: none !important;\n        }\n        .div1_w {\n            width: 36% !important;\n        }\n        .div1_w2 {\n            width: 16% !important;\n        }\n\n        /* 底部 */\n        .bottom_div {\n        }\n        .bottom_div_l {\n            width: 100% !important;\n        }\n        .bottom_div_r {\n            width: 100% !important;\n            margin-bottom: 5px !important;\n        }\n        .bottom_img1 {\n            display: none !important;\n        }\n    }\n    @media screen and (min-width: 769px) {\n        /* 底部 */\n        .bottom_img2 {\n            display: none !important;\n        }\n    }\n</style>', 2, 'joe@globalinforesearch.com,sales@globalinforesearch.com,ssgotg@qyresearch.com,accounts@qyresearch.com', 1, 0, 'payment', 10, 220, 1727072032, 1, 1710490748);
INSERT INTO `email_scenes` VALUES (18, '下单后未付款', '下单后未付款', '<div style=\"display: flex; justify-content: center; padding-bottom: 60px; margin-left: 5px; margin-right: 5px;\">\n<div style=\"max-width: 760px; width: 100%; margin-top: 40px; background: #FFFFFF; box-shadow: 0px 0px 8px 0px #B2C8FF;\"><!-- 头部 -->\n<div>\n<div style=\"display: flex; justify-content: space-between; padding-left: 5%; padding-bottom: 30px;\">\n<div class=\"img_div1\" style=\"zoom: 0.9; max-width: 222px; width: 100%;\"><img class=\"img_logo\" style=\"max-width: 222px; width: 100%; max-height: 73px; height: 100%; margin-top: 20px;\" src=\"{{$backendUrl}}/site/gircn/emails/GIRLogo.webp\" alt=\"logo\"></div>\n<div class=\"img_left\" style=\"width: 100%; height: 88px; background: url(\'{{$backendUrl}}/site/gircn/emails/yeMei.webp\') no-repeat; background-size: 100% 100%; margin-left: 10%; margin-right: -1px;\">\n<p class=\"img_left_p1\" style=\"padding-top: 42px; font-size: 14px; font-weight: 400; color: #333333; text-align: right; padding-right: 12%;\">{{$dateTime}}</p>\n<p class=\"img_left_p2\" style=\"padding-top: 12px; font-size: 14px; font-weight: 400; color: #0d318c; text-align: right; padding-right: 12%;\"><a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$homeUrl}}\">首页</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$myAccountUrl}}\">我的账户</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$contactUsUrl}}\">联系我们</a></p>\n</div>\n</div>\n</div>\n<!-- 内容 -->\n<div>\n<div style=\"margin-left: 5%; margin-right: 5%;\">\n<p style=\"font-weight: 400; font-size: 14px; color: #333333; line-height: 25px; text-align: left; margin-bottom: 20px;\">尊敬的{{$userName}}，您好： <br><br>这是<a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$homeUrl}}\">{{$homePage}}</a>的自动发送邮件。 请不要回复这封邮件。 <br><br>感谢您选择在{{$siteName}}（<a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$homeUrl}}\">{{$homePage}}</a>）订购。 <br>我们已经收到了您的订单，订单号：<span style=\"color: #0d318c;\">{{$orderNumber}}</span>。 <br><br><span style=\"color: #333333; font-weight: bold;\">温馨提示：</span> 您现在的订单状态是：<span style=\"color: #d53801;\">待支付</span>，这意味着还没有完成付款，<br>我们只保留您的订单在<span style=\"color: #d53801;\">7天</span>内。您需要支付<span style=\"color: #d53801;\">￥{{$orderActuallyPaid}}元</span>。</p>\n<p style=\"font-weight: 400; font-size: 14px; color: #333333; line-height: 24px; margin-bottom: 20px;\">如果您想在线支付，<span style=\"color: #0d318c; text-decoration-line: underline;\"><a style=\"font-family: Microsoft YaHei SC; font-weight: 400; text-decoration: underline;\" href=\"{{$paymentLink}}\"> 请点击这里继续支付&gt;&gt;&gt; </a><br>@if($userId!=0)<a href=\"{{$homeUrl}}\" target=\"_blank\" style=\"font-family: Microsoft YaHei; text-decoration: underline;\" rel=\"noopener\"> 请在这里点击查看订单细节</a> <br>@endif</span></p>\n<div style=\"width: 100%; height: 40px; background: #EEF3FF; border: 1px solid #B2BFE1; border-bottom: none; display: flex; align-items: center;\"><img src=\"{{$backendUrl}}/site/gircn/emails/biaoTi.webp\" alt=\"biaoTi\">\n<p style=\"margin-left: 8px; font-weight: bold; font-size: 16px; color: #0d318c;\">订单详情</p>\n</div>\n<ul style=\"padding: 0px; width: 100%; display: flex; flex-wrap: wrap; margin-bottom: 30px; border-left: 1px solid #B2BFE1; border-right: 1px solid #B2BFE1; border-bottom: 1px solid #B2BFE1;\">\n<li style=\"width: 36%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; border-right: 1px solid #B2BFE1; background: #EEF3FF; height: auto; line-height: 32px; font-size: 14px; font-weight: bold; color: #333;\">联系人</li>\n<li style=\"width: 64%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; background: #F7F9FF; height: auto; line-height: 32px; font-size: 14px; font-weight: 400; color: #333333;\">{{$userName}}</li>\n<li style=\"width: 36%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; border-right: 1px solid #B2BFE1; background: #EEF3FF; height: auto; line-height: 32px; font-size: 14px; font-weight: bold; color: #333;\">电子邮箱</li>\n<li style=\"width: 64%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; background: #F7F9FF; height: auto; line-height: 32px; font-size: 14px; font-weight: 400; color: #333333;\">{{$userEmail}}</li>\n<li style=\"width: 36%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; border-right: 1px solid #B2BFE1; background: #EEF3FF; height: auto; line-height: 32px; font-size: 14px; font-weight: bold; color: #333;\">公司名称</li>\n<li style=\"width: 64%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; background: #F7F9FF; height: auto; line-height: 32px; font-size: 14px; font-weight: 400; color: #333333;\">{{$userCompany}}</li>\n<li style=\"width: 36%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; border-right: 1px solid #B2BFE1; background: #EEF3FF; height: auto; line-height: 32px; font-size: 14px; font-weight: bold; color: #333;\">地区</li>\n<li style=\"width: 64%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; background: #F7F9FF; height: auto; line-height: 32px; font-size: 14px; font-weight: 400; color: #333333;\">{{$userAddress}}</li>\n<li style=\"width: 36%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; border-right: 1px solid #B2BFE1; background: #EEF3FF; height: auto; line-height: 32px; font-size: 14px; font-weight: bold; color: #333;\">联系电话</li>\n<li style=\"width: 64%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; background: #F7F9FF; height: auto; line-height: 32px; font-size: 14px; font-weight: 400; color: #333333;\">{{$userPhone}}</li>\n<li style=\"width: 36%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; border-right: 1px solid #B2BFE1; background: #EEF3FF; height: auto; line-height: 32px; font-size: 14px; font-weight: bold; color: #333;\">订单状态</li>\n<li style=\"width: 64%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; background: #F7F9FF; height: auto; line-height: 32px; font-size: 14px; font-weight: 400; color: #333333;\">{{$orderStatus}}</li>\n<li style=\"width: 36%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; border-right: 1px solid #B2BFE1; background: #EEF3FF; height: auto; line-height: 32px; font-size: 14px; font-weight: bold; color: #333;\">支付方式</li>\n<li style=\"width: 64%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; background: #F7F9FF; height: auto; line-height: 32px; font-size: 14px; font-weight: 400; color: #333333;\">{{$paymentMethod}}</li>\n<li style=\"width: 36%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; border-right: 1px solid #B2BFE1; background: #EEF3FF; height: auto; line-height: 32px; font-size: 14px; font-weight: bold; color: #333;\">订单总额</li>\n<li style=\"width: 64%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; background: #F7F9FF; height: auto; line-height: 32px; font-size: 14px; font-weight: 400; color: #333333;\">￥{{$orderAmount}}</li>\n<li style=\"width: 36%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; border-right: 1px solid #B2BFE1; background: #EEF3FF; height: auto; line-height: 32px; font-size: 14px; font-weight: bold; color: #333;\">优惠金额</li>\n<li style=\"width: 64%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; background: #F7F9FF; height: auto; line-height: 32px; font-size: 14px; font-weight: 400; color: #333333;\">￥{{$preferentialAmount}}</li>\n<li style=\"width: 36%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; border-right: 1px solid #B2BFE1; background: #EEF3FF; height: auto; line-height: 32px; font-size: 14px; font-weight: bold; color: #333;\">实付金额</li>\n<li style=\"width: 64%; padding-top: 6px; padding-bottom: 6px; padding-left: 3%; border-top: 1px solid #B2BFE1; background: #F7F9FF; height: auto; line-height: 32px; font-size: 14px; font-weight: 400; color: #333333;\">￥{{$orderActuallyPaid}}</li>\n</ul>\n<p style=\"font-weight: bold; font-size: 16px; color: #0d318c; margin-bottom: 20px;\">订单汇总</p>\n<div>\n<div style=\"background: #EEF3FF; display: flex; padding-left: 10px; padding-right: 10px; margin-bottom: 10px;\">\n<div class=\"div1_w\" style=\"width: 43%; margin-top: 12px; margin-bottom: 15px; font-weight: bold; font-size: 14px; color: #333333;\">报告名称</div>\n<div style=\"width: 15%; margin-top: 12px; margin-bottom: 15px; font-weight: bold; font-size: 14px; color: #333333;\">语言</div>\n<div style=\"width: 20%; margin-top: 12px; margin-bottom: 15px; font-weight: bold; font-size: 14px; color: #333333;\">版本</div>\n<div style=\"width: 10%; margin-top: 12px; margin-bottom: 15px; font-weight: bold; font-size: 14px; color: #333333;\">数量</div>\n<div class=\"div1_w2\" style=\"width: 12%; margin-top: 12px; margin-bottom: 15px; font-weight: bold; font-size: 14px; color: #333333;\">价格</div>\n</div>\n@foreach ($goods as $item)\n<div style=\"display: flex; padding-left: 10px; padding-right: 10px;\">\n<div class=\"div1_w\" style=\"width: 43%; font-weight: 500; font-size: 14px; color: #333333; display: flex;\"><img class=\"img1\" src=\"{{$item[\'thumb\']}}\" alt=\"book\" style=\"width: 61px; height: 80px; margin-right: 5px;\">\n<div style=\"padding-right: 5px;\">\n<p style=\"font-weight: 400; font-size: 13px; color: #333333; line-height: 20px;\"><a href=\"{{$item[\'link\']}}\">{{$item[\'name\']}}</a></p>\n<p style=\"font-weight: 400; font-size: 12px; color: #666666; line-height: 20px;\">报告编码:{{$item[\'product_id\']}}</p>\n</div>\n</div>\n<div style=\"width: 15%; font-weight: 400; font-size: 13px; color: #333333; word-wrap: break-word; word-break: break-all;\">{{$item[\'language\']}}</div>\n<div style=\"width: 20%; font-weight: 400; font-size: 13px; color: #333333; word-wrap: break-word; word-break: break-all;\">{{$item[\'price_edition\']}}</div>\n<div style=\"width: 10%; font-weight: 400; font-size: 13px; color: #333333;\">{{$item[\'goods_number\']}}</div>\n<div class=\"div1_w2\" style=\"width: 12%; font-weight: 400; font-size: 13px; color: #333333; word-wrap: break-word; word-break: break-all;\">￥{{$item[\'goods_present_price\']}}</div>\n</div>\n@endforeach\n<div style=\"height: 1px; border: 1px solid #E3E9F9; margin-bottom: 10px; margin-top: 20px;\"></div>\n<div style=\"display: flex; justify-content: flex-end; padding-left: 10px; padding-right: 10px;\">\n<div style=\"margin-bottom: 15px; font-weight: 500; font-size: 14px; color: #333333; margin-right: 20px;\"><span>订单总额：</span><br><span>优惠金额：</span><br><span>实付金额：</span></div>\n<div style=\"margin-bottom: 15px; font-weight: 500; font-size: 14px; color: #333333; text-align: right;\"><span>￥ {{$orderAmount}}</span><br><span>-￥{{$preferentialAmount}}</span><br><span style=\"color: #d53801; font-weight: bold;\">￥{{$orderActuallyPaid}}</span></div>\n</div>\n</div>\n<p style=\"font-size: 14px; font-weight: 500; color: #333333; margin-bottom: 30px; margin-top: 40px;\">如果您有任何疑问，请<a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$contactUsUrl}}\">联系我们</a>。 <br><br><br>至此,</p>\n<p style=\"font-size: 14px; font-weight: bold; color: #333333; line-height: 20px;\">{{$siteName}} 客服团队</p>\n<p style=\"font-size: 14px; font-weight: 400; color: #333333; line-height: 20px; margin-bottom: 40px;\">因为您是{{$siteName}}网站的注册会员，您会收到这封邮件。<br>想了解更多信息，请登录您的账号：<a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$homeUrl}}\">{{$homePage}}</a>并提交您的想法，或者使用联系我们的客服。</p>\n</div>\n</div>\n<!-- 底部 -->\n<div style=\"width: 100%; background: #F1F5FF; padding-top: 15px;\">\n<div class=\"bottom_div\" style=\"display: flex; flex-wrap: wrap; margin-left: 5%; margin-right: 5%;\">\n<div class=\"bottom_div_l\" style=\"width: 50%; padding-left: 4%; display: flex; flex-direction: column; justify-content: end;\">\n<p style=\"font-weight: 500; font-size: 14px; color: #0d318c; margin-bottom: 15px;\"><img src=\"{{$backendUrl}}/site/gircn/emails/phone.webp\" alt=\"phone\"> 电话: <a style=\"color: #0d318c; text-decoration: none;\" href=\"tel:{{$sitePhone}}\">{{$sitePhone}}</a></p>\n<p style=\"font-weight: 500; font-size: 14px; color: #0d318c; margin-bottom: 5px;\"><img src=\"{{$backendUrl}}/site/gircn/emails/email.webp\" alt=\"email\"> 邮箱: <a style=\"color: #0d318c; text-decoration: none;\" href=\"{{$toSiteEmail}}\">{{$siteEmail}}</a></p>\n</div>\n<div class=\"bottom_div_r\" style=\"width: 50%; padding-left: 2%; margin-bottom: -15px;\">\n<div style=\"display: flex; justify-content: space-between;\"><img style=\"width: 200px; height: 66px; margin-top: 30px;\" src=\"{{$backendUrl}}/site/gircn/emails/logo2.webp\" alt=\"logo\"> <img style=\"width: 100px; height: 100px;\" src=\"{{$backendUrl}}/site/gircn/emails/erWeiMa.webp\" alt=\"erWeiMa\"></div>\n</div>\n</div>\n<div class=\"bottom_img1\" style=\"background: url(\'{{$backendUrl}}/site/gircn/emails/yeJiao.webp\') no-repeat; background-size: 100% 100%; width: 100%; height: 41px;\"></div>\n<div class=\"bottom_img2\" style=\"background: url(\'{{$backendUrl}}/site/gircn/emails/yeJiao.png\') no-repeat; background-size: 100% 100%; width: 100%; height: 41px;\"></div>\n</div>\n</div>\n</div>\n<style>\n    * {\n        list-style:none !important;    \n        padding: 0;\n        margin: 0;\n        box-sizing: border-box;\n        font-family: HarmonyOS Sans SC ;\n        font-weight: 500;\n    }\n\n    @media screen and (max-width: 768px) {\n        /* 头部 */\n        .img_div1 {\n            max-width: 122px  !important;\n            max-height: 45px  !important;\n        }\n        .img_logo {\n            max-width: 122px  !important;\n            max-height: 45px  !important;\n        }\n        .img_left {\n            height: 60px !important;\n        }\n        .img_left_p1 {\n            padding-top: 25px !important;\n            font-size: 12px !important;\n            padding-right: 9% !important;\n        }\n        .img_left_p2 {\n            padding-top: 0px !important;\n            font-size: 12px !important;\n            padding-right: 9% !important;\n        }\n\n        /* 内容 */\n        .img1 {\n            display: none !important;\n        }\n        .div1_w {\n            width: 36% !important;\n        }\n        .div1_w2 {\n            width: 16% !important;\n        }\n\n        /* 底部 */\n        .bottom_div {\n        }\n        .bottom_div_l {\n            width: 100% !important;\n        }\n        .bottom_div_r {\n            width: 100% !important;\n            margin-bottom: 5px !important;\n        }\n        .bottom_img1 {\n            display: none !important;\n        }\n    }\n    @media screen and (min-width: 769px) {\n        /* 底部 */\n        .bottom_img2 {\n            display: none !important;\n        }\n    }\n</style>', 2, 'joe@globalinforesearch.com,sales@globalinforesearch.com', 1, 0, 'placeOrder', 10, 220, 1727072019, 1, 1710490858);

-- ----------------------------
-- Records of message_categories
-- ----------------------------
INSERT INTO `message_categories` VALUES (1, '申请样本', 'productSample', 'success', 1, 0, 19, 1705548472, 1, 1705900961);
INSERT INTO `message_categories` VALUES (2, '定制报告', 'customized', 'info', 1, 1, 19, 1705548480, 19, 1705548520);
INSERT INTO `message_categories` VALUES (3, '联系我们', 'contactUs', 'warning', 1, 2, 19, 1705548490, 19, 1705548547);
INSERT INTO `message_categories` VALUES (4, '申请节选', 'excerpt', NULL, 1, 3, 19, 1706250065, 1, 1706516924);


-- ----------------------------
-- Records of message_language_versions
-- ----------------------------
INSERT INTO `message_language_versions` VALUES (1, '中文版', 1, 2, 0, 0, 25, 1712110663);
INSERT INTO `message_language_versions` VALUES (2, '英文版', 1, 0, 0, 0, 0, 0);

-- ----------------------------
-- Records of news_category
-- ----------------------------
INSERT INTO `news_category` VALUES (1, '行业新闻', 'Industry-news', 1, 1, NULL, NULL, NULL, 25, 0, 79, 1719305182);
INSERT INTO `news_category` VALUES (2, '免费咨询', 'Free', 2, 1, NULL, NULL, NULL, 25, 0, 79, 1719305190);
INSERT INTO `news_category` VALUES (3, '公司动态', 'Company-news', 3, 1, NULL, NULL, NULL, 25, 0, 79, 1719305197);


-- ----------------------------
-- Records of pays
-- ----------------------------
INSERT INTO `pays` VALUES (3, '支付宝', '/site/gircn/setting/payment/zhiFuBao.webp', 'info login1', 'info key1', '同步回调地址1', '异步回调地址1', '回调签名验证secretKey1', 1, 1, NULL, 'ALIPAL', 19, 1705030897, 25, 1722924759);
INSERT INTO `pays` VALUES (4, '微信支付', '/site/gircn/setting/payment/weiXin.webp', '33333', '3333', '333', '33', '3', 1, 0, NULL, 'WECHATPAY', 1, 1705389156, 207, 1726210916);

-- ----------------------------
-- Records of price_edition_values
-- ----------------------------
INSERT INTO `price_edition_values` VALUES (120, 'PDF版', 25, 1, '%s', '报告将以PDF电子版的形式通过Email发送给您', 0, 1, 1, 1, 79, 1702278915, 79, 1710927265);
INSERT INTO `price_edition_values` VALUES (121, 'PDF+Excel版', 25, 1, '%s+3000', '报告将以PDF电子版的形式通过Email发送给您', 1, 2, 1, 1, 79, 1702278915, 79, 1726130055);
INSERT INTO `price_edition_values` VALUES (163, 'PDF+Excel+Word+纸质版', 25, 1, '%s+5000', '报告将以PDF+Word+Excel电子版的形式通过Email发送，同时纸质版将通过顺丰快递给您', 1, 4, 1, 1, 207, 1726110517, 79, 1726130055);

-- ----------------------------
-- Records of price_editions
-- ----------------------------
INSERT INTO `price_editions` VALUES (25, '1', 100, 1, 1, 25, 1699954022, 79, 1726130061);

-- ----------------------------
-- Records of product_category
-- ----------------------------
INSERT INTO `product_category` VALUES (1, '化工及材料', 0, 'chemical-material', NULL, NULL, '/site/gircn/products/industry/icons/chemical_material.png', '/site/gircn/products/industry/icons/chemical_material_hover.png', 1, 1, 1, 1, 1, 100, 0.00, 1, 0, 0, '化工及材料市场研究报告', '化工行业分析报告,材料市场调研报告,化工及材料市场研究报告', '本栏目对化工行业和材料市场进行了深入的调研与研究，通过数据分析和市场趋势预测，为您提供最新的市场动态和竞争格局。我们通过对化工及材料市场的深入了解和分析，为您提供专业、准确的研究报告和数据支持。', NULL, '研究报告,行业细分报告,细分研究报告,IPO上市调研报告', '催化剂,己烷,清洗剂,标签', 1726799773, 0, 220, 0);
INSERT INTO `product_category` VALUES (2, '机械及设备', 0, 'machinery-equipment', NULL, NULL, '/site/gircn/products/industry/icons/machinery_equipment.png', '/site/gircn/products/industry/icons/machinery_equipment_hover.png', 2, 1, 0, 1, 1, 100, 0.00, 1, 0, 0, '机械及设备研究报告', '机械及设备研究报告,机械行业研究报告,机械设备行业报告', '本栏目提供了机械及设备研究报告、机械行业研究报告、机械设备行业报告等多种研究报告，内容涉及机械设备的市场现状、技术趋势、应用领域等多个方面。', NULL, '研究报告,行业细分报告,细分研究报告,IPO上市调研报告', '过滤器,热泵,空调', 1725502515, 0, 25, 0);
INSERT INTO `product_category` VALUES (3, '医疗设备及耗材', 0, 'medical-devices-consumables', NULL, NULL, '/site/gircn/products/industry/icons/medical_devices_consumables.png', '/site/gircn/products/industry/icons/medical_devices_consumables_hover.png', 3, 1, 1, 1, 1, 100, 0.00, 1, 0, 0, '医疗设备及耗材市场研究报告', '医疗设备及耗材市场研究报告,医疗设备市场研究报告,医疗耗材行业报告', '医疗设备及耗材市场研究报告提供了深入洞察医疗行业的全面数据分析，覆盖了市场趋势、竞争格局和未来展望。本栏目汇总了医疗设备和耗材领域的最新发展，为决策者、投资者和行业从业者提供了宝贵的见解。', NULL, '研究报告,行业细分报告,细分研究报告,IPO上市调研报告', '助听器,输液泵,雾化器,治疗仪', 1725502533, 0, 25, 0);
INSERT INTO `product_category` VALUES (4, '消费品', 0, 'consumer-goods', NULL, NULL, '/site/gircn/products/industry/icons/consumer_goods.png', '/site/gircn/products/industry/icons/consumer_goods_hover.png', 4, 1, 1, 1, 1, 100, 0.00, 1, NULL, NULL, '消费品市场研究报告', '消费品研究报告,消费品市场研究报告', '提供最详细，最权威的消费品研究报告和市场研究报告，我们拥有多年的经验，秉持专业精神，为您提供最真实，最客观的数据和分析，帮助您更好的了解市场，把握机遇，做出明智的决策！', NULL, '研究报告,行业细分报告,细分研究报告,IPO上市调研报告', '洗发水,蓝牙耳机,雾化器,净水器,手套', 1727072302, 0, 25, 0);
INSERT INTO `product_category` VALUES (5, '电子及半导体', 0, 'electronics-semiconductor', NULL, NULL, '/site/gircn/products/industry/icons/electronics_semiconductor.png', '/site/gircn/products/industry/icons/electronics_semiconductor_hover.png', 5, 1, 1, 1, 1, 100, 0.00, 1, 0, 0, '电子及半导体市场报告', '全球半导体市场报告,中国半导体市场规模,电子行业调研报告', '全球半导体市场报告，深入剖析全球及中国半导体市场规模及发展趋势，提供详尽的电子行业调研数据和报告，助您把握市场先机。', NULL, '研究报告,行业细分报告,细分研究报告,IPO上市调研报告', '半导体,电阻,电阻器,振荡器', 1725502558, 0, 25, 0);
INSERT INTO `product_category` VALUES (6, '软件及商业服务', 0, 'service-software', NULL, NULL, '/site/gircn/products/industry/icons/service_software.png', '/site/gircn/products/industry/icons/service_software_hover.png', 6, 1, 1, 1, 1, 100, 0.00, 1, NULL, NULL, '软件及商业服务市场研究报告', '软件软件市场调研报告,商业服务市场研究报告', '软件及商业服务市场研究报告页面提供全面深入的软件及商业服务市场分析，包括行业现状、市场竞争格局、未来趋势、市场规模等。本报告基于详尽的市场调研数据，为投资者、企业领导和相关从业者提供重要的市场参考和支持。', NULL, '研究报告,行业细分报告,细分研究报告,IPO上市调研报告,行业分析报告', 'App,软件,测试服务', 1727072303, 0, 25, 0);
INSERT INTO `product_category` VALUES (7, '药品及保健品', 0, 'pharma-healthcare', NULL, NULL, '/site/gircn/products/industry/icons/pharma_healthcare.png', '/site/gircn/products/industry/icons/pharma_healthcare_hover.png', 7, 1, 1, 0, 1, 100, 0.00, 1, NULL, NULL, '药品及保健品市场研究报告,药品市场调研报告', '药品市场研究报告,保健品研究报告', '本栏目提供有关药品及保健品市场的深入分析，包括市场调研、竞争格局、消费者行为、价格趋势等多方面信息。我们致力于帮助您更好地了解市场现状，制定有效的营销策略，从而增加收益。', NULL, '研究报告,行业细分报告,细分研究报告,IPO上市调研报告,行业分析报告', '保健胶囊,头孢,治疗,补充剂', 1725502581, 0, 25, 0);
INSERT INTO `product_category` VALUES (8, '汽车及交通', 0, 'automobile-transportation', NULL, NULL, '/site/gircn/products/industry/icons/automobile_transportation.png', '/site/gircn/products/industry/icons/automobile_transportation_hover.png', 8, 1, 1, 0, 1, 100, 0.00, 1, NULL, NULL, '汽车及交通市场研究报告', '汽车行业分析,新能源汽车研究报告', '汽车及交通市场研究报告提供了新能源汽车行业的深入分析，包括市场现状、产业链结构、竞争格局和发展趋势等。本栏目不仅对现有市场进行了全面研究，同时也探讨了新能源汽车市场未来的发展方向和趋势', NULL, '研究报告,行业细分报告,细分研究报告,IPO上市调研报告,行业分析报告', '新能源汽车,摩托车,电动汽车,汽车', 1725502594, 0, 25, 0);
INSERT INTO `product_category` VALUES (9, '食品及饮料', 0, 'food-beverages', NULL, NULL, '/site/gircn/products/industry/icons/food_beverages.png', '/site/gircn/products/industry/icons/food_beverages_hover.png', 9, 1, 1, 0, 1, 100, 0.00, 1, NULL, NULL, '食品市场调研报告-饮料市场研究报告', '食品市场调研报告,饮料市场研究报告', '食品及饮料市场研究报告为您提供深入的行业分析。本报告基于最新的市场研究数据，对食品和饮料行业的市场现状、竞争格局、发展趋势等进行了深入剖析。我们致力于为您提供有价值的参考信息，帮助您把握商机。 ', NULL, '研究报告,行业细分报告,细分研究报告,IPO上市调研报告,行业分析报告', '有机食品,果汁,酸奶,牛奶', 1725502603, 0, 25, 0);
INSERT INTO `product_category` VALUES (10, '更多（医药）', 0, 'more-medicine', NULL, NULL, '/site/gircn/products/industry/icons/more_medicine.png', '/site/gircn/products/industry/icons/more_medicine_hover.png', 10, 1, 1, 0, 1, 100, 0.00, 1, NULL, NULL, '医药市场行业细分研究报告', '医药市场行业细分研究报告', '此栏目提供关于医药市场行业细分研究报告的内容，包括当前市场发展的状况、竞争格局、未来趋势以及企业案例分析等，旨在帮助读者全面了解医药行业市场发展状况，预测未来趋势，为企业提供参考和决策依据。', NULL, '研究报告,行业细分报告,细分研究报告,IPO上市调研报告,行业分析报告', '生物制药,病毒抗原,临床试验,制剂,抗原', 1725502618, 0, 25, 0);
INSERT INTO `product_category` VALUES (11, '能源及电力', 0, 'energy-power', NULL, NULL, '/site/gircn/products/industry/icons/energy_power.png', '/site/gircn/products/industry/icons/energy_power_hover.png', 11, 1, 1, 0, 1, 100, 0.00, 1, NULL, NULL, '能源及电力市场研究报告', '能源市场调研报告,电力市场研究报告', '为您提供最详细的市场数据和最新的行业资讯，帮助您深入了解能源和电力市场的现状和发展趋势，提供全方位的市场研究服务', NULL, '研究报告,行业细分报告,细分研究报告,IPO上市调研报告,行业分析报告', '新能源,太阳能,风电', 1725502630, 0, 25, 0);
INSERT INTO `product_category` VALUES (12, '包装', 0, 'packaging', NULL, NULL, '/site/gircn/products/industry/icons/packaging.png', '/site/gircn/products/industry/icons/packaging_hover.png', 12, 1, 1, 0, 1, 100, 0.00, 1, NULL, NULL, '包装市场研究报告', '包装市场研究报告, 食品包装调研报告', '包装市场研究报告-提供详尽的包装行业趋势与发展前景分析，帮助您深入了解市场竞争、投资机会、技术动态和发展趋势。', NULL, '研究报告,行业细分报告,细分研究报告,IPO上市调研报告,行业分析报告', '纸箱,包装膜,包装', 1725502640, 0, 25, 0);
INSERT INTO `product_category` VALUES (13, '农业', 0, 'agriculture', NULL, NULL, '/site/gircn/products/industry/icons/agriculture.png', '/site/gircn/products/industry/icons/agriculture_hover.png', 13, 1, 1, 0, 1, 100, 0.00, 1, NULL, NULL, '农业报告-农业研究报告', '农业报告,农业研究报告', '供最全面的农业行业报告与研究，及时掌握农业趋势，分享最新农业研究成果。本站汇聚海量农业数据及各类农业研究报告，帮助您洞察农业市场，助力农业产业发展。', NULL, '研究报告,行业细分报告,细分研究报告,IPO上市调研报告,行业分析报告', '水产养殖,播种机,肥料', 1725502652, 0, 25, 0);
INSERT INTO `product_category` VALUES (14, '网络及通信', 0, 'internet-communication', NULL, NULL, '/site/gircn/products/industry/icons/internet_communication.png', '/site/gircn/products/industry/icons/internet_communication_hover.png', 14, 1, 0, 0, 1, 100, 0.00, 1, NULL, NULL, '网络及通信市场研究报告', '网络行业分析报告,通信行业调研报告', ' 网络及通信市场研究报告，为您提供专业的网络及通信行业市场趋势分析。本报告通过对市场环境的深入了解，采用科学的方法和准确的数依据，为您提供有价值的行业分析研报。', NULL, '研究报告,行业细分报告,细分研究报告,IPO上市调研报告,行业分析报告', '计算机,对讲机,终端', 1725502668, 0, 25, 0);
INSERT INTO `product_category` VALUES (15, '新兴行业', 0, 'new-technology', NULL, NULL, '/site/gircn/products/industry/icons/new_technology.png', '/site/gircn/products/industry/icons/new_technology_hover.png', 15, 1, 0, 0, 1, 100, 0.00, 1, NULL, NULL, '新兴行业研究报告', '新兴行业研究报告', '新兴行业研究报告，提供最全面的行业分析、市场趋势、竞争态势和未来预测。本站汇聚众多专业人士的深度研究报告，为您深度解析各个行业的发展趋势。', NULL, '研究报告,行业细分报告,细分研究报告,IPO上市调研报告,行业分析报告', '人工智能,元宇宙,AI', 1725502680, 0, 25, 0);
INSERT INTO `product_category` VALUES (16, '医疗护理', 0, 'medical-care', NULL, NULL, '/site/gircn/products/industry/icons/medical_care.png', '/site/gircn/products/industry/icons/medical_care_hover.png', 16, 1, 0, 0, 1, 100, 0.00, 1, NULL, NULL, '医疗护理市场研究报告', '医疗护理市场研究报告,医疗护理行业分析报告', '本栏目报告对医疗护理市场进行全面研究，深入剖析行业现状、竞争格局和发展趋势。通过对国内外市场规模、产业链结构、主要企业竞争力和未来前景等方面的详细分析，为企业决策提供数据支持。', NULL, '研究报告,行业细分报告,细分研究报告,IPO上市调研报告,行业分析报告', '母婴护理,老年人护理', 1725502693, 0, 25, 0);
INSERT INTO `product_category` VALUES (17, '其他行业', 0, 'others', NULL, NULL, '/site/gircn/products/industry/icons/others.png', '/site/gircn/products/industry/icons/others_hover.png', 17, 1, 0, 0, 1, 100, 0.00, 1, NULL, NULL, '其他行业研究报告', '其他行业研究报告', '本页面提供最新的其他行业研究报告和市场调研信息，包括市场现状、竞争格局、未来趋势等，帮助读者深入了解行业动态，把握市场机会。我们将持续更新本页面，敬请关注！', NULL, '研究报告,行业细分报告,细分研究报告,IPO上市调研报告,行业分析报告', NULL, 1725502712, 0, 25, 0);

-- ----------------------------
-- Records of sensitive_words
-- ----------------------------
INSERT INTO `sensitive_words` VALUES (28, 'Marijuana', 1, 100, 1719304616, 1717639571, 207, 73);
INSERT INTO `sensitive_words` VALUES (29, 'Cannabis', 1, 100, 1719304615, 1718615896, 207, 79);
INSERT INTO `sensitive_words` VALUES (30, 'Gambling', 1, 100, 1719304615, 1718615904, 207, 79);
INSERT INTO `sensitive_words` VALUES (31, 'Casino', 1, 100, 1719304614, 1718615914, 207, 79);
INSERT INTO `sensitive_words` VALUES (32, 'Betting', 1, 100, 1725002294, 1718615924, 207, 79);
INSERT INTO `sensitive_words` VALUES (33, 'CeShi', 0, 100, 1727055879, 1718960570, 220, 207);

-- ----------------------------
-- Records of sync_field
-- ----------------------------
INSERT INTO `sync_field` VALUES (1, 'ff01', 'name', 2, 100, '报告名称', 1, 1, 100, 1, 1718291800, 167, 1718291800, 167);
INSERT INTO `sync_field` VALUES (2, 'ff02', 'english_name', 2, 100, '报告名称(英)', 1, 0, 100, 1, 1718291800, 167, 1718291800, 167);
INSERT INTO `sync_field` VALUES (3, 'ff09', 'price', 1, 100, '基础价', 1, 1, 100, 1, 1718291800, 167, 1726728903, 25);
INSERT INTO `sync_field` VALUES (4, 'ff17', 'keywords', 2, 100, '关键词', 1, 0, 100, 1, 1718291800, 167, 1718291800, 167);
INSERT INTO `sync_field` VALUES (5, 'ff18', 'url', 2, 100, '自定义连接', 1, 0, 100, 1, 1718291800, 167, 1718291800, 167);
INSERT INTO `sync_field` VALUES (6, 'ff03', 'category_id', 2, 100, '分类', 1, 1, 100, 1, 1718291800, 167, 1718291800, 167);
INSERT INTO `sync_field` VALUES (7, 'ff06', 'published_date', 3, 100, '出版时间', 1, 1, 100, 1, 1718291800, 167, 1718291800, 167);
INSERT INTO `sync_field` VALUES (8, 'ff14', 'author', 2, 100, '作者', 1, 1, 100, 1, 1718291800, 167, 1718291800, 167);
INSERT INTO `sync_field` VALUES (9, 'ff15', 'country_id', 2, 100, '地区', 1, 0, 100, 1, 1718291800, 167, 1718291800, 167);
INSERT INTO `sync_field` VALUES (10, 'ff07', 'description', 2, 100, '详情', 1, 0, 100, 2, 1718291800, 167, 1718291800, 167);
INSERT INTO `sync_field` VALUES (11, 'ff08', 'description_en', 2, 100, '详情(英)', 0, 0, 100, 2, 1718291800, 167, 1726729042, 25);
INSERT INTO `sync_field` VALUES (12, 'ff10', 'table_of_content', 2, 100, '正文目录', 1, 0, 100, 2, 1718291800, 167, 1718291800, 167);
INSERT INTO `sync_field` VALUES (13, 'ff11', 'table_of_content_en', 2, 100, '正文目录(英)', 0, 0, 100, 2, 1718291800, 167, 1726729040, 25);
INSERT INTO `sync_field` VALUES (14, 'ff12', 'tables_and_figures', 2, 100, '图表目录', 1, 0, 100, 2, 1718291800, 167, 1718291800, 167);
INSERT INTO `sync_field` VALUES (15, 'ff13', 'tables_and_figures_en', 2, 100, '图表目录(英)', 0, 0, 100, 2, 1718291800, 167, 1726728940, 25);
INSERT INTO `sync_field` VALUES (16, 'ff16', 'companies_mentioned', 2, 100, '提及公司', 1, 0, 100, 2, 1718291800, 167, 1718291800, 167);
INSERT INTO `sync_field` VALUES (17, 'ff04', 'pages', 1, 100, '页数', 1, 0, 100, 1, 1718291800, 167, 1718291800, 167);
INSERT INTO `sync_field` VALUES (18, 'ff05', 'tables', 1, 100, '图表数', 1, 0, 100, 1, 1718291800, 167, 1718291800, 167);
INSERT INTO `sync_field` VALUES (19, 'ff22', 'classification', 2, 100, '分类', 1, 0, 100, 1, 1718291800, 167, 1718291800, 167);
INSERT INTO `sync_field` VALUES (20, 'ff23', 'application', 2, 100, '应用', 1, 0, 100, 1, 1718291800, 167, 1718291800, 167);
INSERT INTO `sync_field` VALUES (21, 'ff24', 'definition', 2, 100, '定义', 1, 0, 99, 2, 1718291800, 167, 1718700291, 176);
INSERT INTO `sync_field` VALUES (22, 'ff25', 'overview', 2, 100, '概述', 1, 0, 100, 2, 1718291800, 167, 1718291800, 167);
INSERT INTO `sync_field` VALUES (23, 'ff26', 'last_scale', 2, 100, '去年规模', 1, 0, 100, 1, 1718291800, 167, 1718291800, 167);
INSERT INTO `sync_field` VALUES (24, 'ff27', 'current_scale', 2, 100, '今年规模', 1, 0, 100, 1, 1718291800, 167, 1718291800, 167);
INSERT INTO `sync_field` VALUES (25, 'ff28', 'future_scale', 2, 100, '未来规模', 1, 0, 100, 1, 1718291800, 167, 1718291800, 167);
INSERT INTO `sync_field` VALUES (26, 'ff29', 'cagr', 2, 100, 'cagr', 1, 0, 100, 1, 1718291800, 167, 1718593998, 167);
INSERT INTO `sync_field` VALUES (27, 'ff21', 'publisher_code', 2, 100, '出版商code', 1, 0, 100, 1, 1718291800, 167, 1718593942, 167);

-- ----------------------------
-- Records of system_values
-- ----------------------------
INSERT INTO `system_values` VALUES (36, 33, '网站名称', 'siteName', '', 'siteName', '环洋市场咨询(Global Info Research)', 1, 1, 1, 1, 0, 1707011141, 1, 1707011141, 0);
INSERT INTO `system_values` VALUES (37, 33, 'QQ咨询', 'QQConsulting', '', 'QQConsulting', '1692545020', 1, 1, 1, 1, 0, 1707011164, 1, 1722481223, 25);
INSERT INTO `system_values` VALUES (38, 33, '联系电话', 'sitePhone', '', 'sitePhone', '176 6505 2062', 1, 1, 1, 1, 0, 1707011194, 1, 1726711199, 79);
INSERT INTO `system_values` VALUES (39, 33, '联系邮箱', 'siteEmail', '', 'siteEmail', 'report@globalinforesearch.com', 1, 1, 1, 1, 0, 1707011218, 1, 1719208537, 79);
INSERT INTO `system_values` VALUES (40, 33, '版权提示', 'CopyrightNotice', '', 'CopyrightNotice', 'Copyright © 2016-%year Global Info Research 版权所有', 1, 1, 1, 1, 0, 1707011245, 1, 1726713722, 25);
INSERT INTO `system_values` VALUES (41, 33, 'ICP备案号', 'icp', '', 'icp', '粤ICP备2023009619号-1', 1, 1, 1, 1, 0, 1707011266, 1, 1707011266, 0);
INSERT INTO `system_values` VALUES (42, 33, '公安备案号', 'police', '', 'police', '粤公网安备44010602008759号', 1, 1, 1, 1, 0, 1707011286, 1, 1707011286, 0);
INSERT INTO `system_values` VALUES (43, 33, '版权信息', 'CopyrightInfo', '', 'CopyrightInfo', 'Copyright © 2016-%year Global Info Research 版权所有', 1, 1, 1, 1, 0, 1707011308, 1, 1726713716, 25);
INSERT INTO `system_values` VALUES (44, 33, '服务方式', 'Service', '', 'Service', '电子版或纸质版', 1, 1, 1, 1, 0, 1707011329, 1, 1707011329, 0);
INSERT INTO `system_values` VALUES (45, 33, '顶部logo', 'topLogo', '', 'topLogo', '/site/gircn/setting/network/logo.webp', 3, 1, 1, 1, 0, 1707011353, 1, 1721974921, 175);
INSERT INTO `system_values` VALUES (46, 33, '底部logo', 'botLogo', '', 'botLogo', '/site/gircn/setting/network/logo_b.webp', 3, 1, 1, 1, 0, 1707011375, 1, 1721977488, 25);
INSERT INTO `system_values` VALUES (48, 34, '打开新标签页', 'newTab', '', 'newTab', '0', 1, 1, 1, 0, 0, 1707016011, 1, 1726801203, 220);
INSERT INTO `system_values` VALUES (49, 34, '能否用F12键', 'f12', '', 'f12', '1', 1, 1, 1, 0, 0, 1707016044, 1, 1727171634, 220);
INSERT INTO `system_values` VALUES (51, 34, '能否复制【报告详情】页内容', 'copy', '', 'copy', '0', 1, 1, 1, 0, 0, 1707016070, 1, 1727171634, 220);
INSERT INTO `system_values` VALUES (52, 41, 'sphinx开关', 'sphinx', '', 'sphinx', '1', 1, 1, 1, 1, 0, 1709005405, 1, 1726628624, 25);
INSERT INTO `system_values` VALUES (53, 33, '默认报告缩略图', 'Default Report Image', '', 'default_report_img', '/site/gircn/setting/network/default_report_img.webp', 3, 1, 1, 1, 0, 1722236207, 25, 1724229694, 207);
INSERT INTO `system_values` VALUES (54, 33, '页脚文字', 'footer text', '', 'footer_text', '<span style=\"color: #FFFFFF;\"><a href=\"https://www.globalinforesearch.com.cn\" target=\"_blank\" rel=\"nofllow\" style=\"color: #BEE831;\">环洋市场</a>出版商，<a href=\"https://www.globalinforesearch.com.cn/report-categories/1\" target=\"_blank\" rel=\"nofllow\" style=\"color: #BEE831;\">化学材料</a>发展前景，<a href=\"https://www.globalinforesearch.com.cn/report-categories/2\" target=\"_blank\" rel=\"nofllow\" style=\"color: #BEE831;\">机械设备</a>分析投资趋势，个性化<a href=\"https://www.globalinforesearch.com.cn/news\" target=\"_blank\" rel=\"nofllow\" style=\"color: #BEE831;\">新闻资讯</a>，查阅<a href=\"https://www.globalinforesearch.com.cn/report-categories\" target=\"_blank\" style=\"color: #BEE831;\" title=\"投资趋势报告\" rel=\"nofllow\">最新报告</a></span>', 1, 1, 1, 1, 0, 1712732536, 25, 1726646959, 220);
INSERT INTO `system_values` VALUES (55, 37, 'English', 'EN', '', 'en', 'https://www.marketmonitorglobal.com/', 1, 1, 1, 1, 0, 1712825302, 79, 1712914805, 165);
INSERT INTO `system_values` VALUES (56, 37, '中文', 'Chinese', '', 'chinese', 'https://www.marketmonitorglobal.com.cn/', 1, 1, 1, 1, 0, 1712903054, 165, 1718960302, 207);
INSERT INTO `system_values` VALUES (57, 33, '图片地址', 'Image Address', '', 'image_address', 'http://img.globalinforesearch.com.cn', 1, 1, 1, 0, 0, 1717652318, 165, 1726715629, 25);
INSERT INTO `system_values` VALUES (58, 45, '自动同步数据开关', 'autoSyncData', '', 'autoSyncData', '1', 1, 1, 1, 1, 0, 1709005405, 1, 1726652228, 25);
INSERT INTO `system_values` VALUES (59, 45, '同步报告url', 'syncProductUrl', '', 'syncProductUrl', 'https://hzzb.wanyunapp.com/openapi/v1/gir_cn', 1, 1, 1, 1, 0, 1718612314, 167, 1726630639, 25);
INSERT INTO `system_values` VALUES (60, 45, '同步报告token', 'syncProductToken', '', 'syncProductToken', '6869eec12d49ec06d5f5f85d43c838ac57b9de9a561ba9875f563243712c0e20', 1, 1, 1, 1, 0, 1718612377, 167, 1726630646, 25);
INSERT INTO `system_values` VALUES (61, 45, '通知数据同步结果URL', 'notifyDataResUrl', '', 'notifyDataResUrl', 'https://hzzb.wanyunapp.com/openapi/v1/updateStatus', 1, 1, 1, 1, 0, 1718612515, 167, 1718612515, 0);
INSERT INTO `system_values` VALUES (62, 45, '通知数据同步Token', 'notifyDataSyncToken', '', 'notifyDataSyncToken', '6869eec12d49ec06e2cb987e1b20f3585cf196eef3fcebb2c2121f2dd9f4f025', 1, 1, 1, 1, 0, 1718612558, 167, 1718612558, 0);
INSERT INTO `system_values` VALUES (63, 45, '通知结果表名', 'notifyResTable', '', 'notifyResTable', 'gir_cn', 1, 1, 1, 1, 0, 1718613416, 167, 1726630597, 25);
INSERT INTO `system_values` VALUES (64, 39, '产品应用截取规则2', 'application_sub_rules_2', '', 'application_sub_rules_2', '主要应用，2023年市场份额', 1, 1, 1, 1, 0, 1718868294, 167, 1718869669, 167);
INSERT INTO `system_values` VALUES (65, 39, '产品应用截取规则1', 'application_sub_rules_1', '', 'application_sub_rules_1', '主要应用，2024年市场份额', 1, 1, 1, 1, 0, 1718868326, 167, 1718869649, 167);
INSERT INTO `system_values` VALUES (66, 38, '产品类型截取规则1', 'classification_sub_rules_1', '', 'classification_sub_rules_1', '主要分类，2023年市场份额', 1, 1, 1, 1, 0, 1718868414, 167, 1718869574, 167);
INSERT INTO `system_values` VALUES (67, 38, '产品类型截取规则2', 'classification_sub_rules_2', '', 'classification_sub_rules_2', '主要分类，2024年市场份额', 1, 1, 1, 1, 0, 1718868414, 167, 1718869599, 167);
INSERT INTO `system_values` VALUES (68, 39, '产品应用截取规则3', 'application_sub_rules_3', '', 'application_sub_rules_3', '主要应用，2025年市场份额', 1, 1, 1, 0, 0, 1718956259, 207, 1726796945, 220);
INSERT INTO `system_values` VALUES (69, 38, '产品类型截取规则3', 'classification_sub_rules_3', '', 'classification_sub_rules_3', '主要分类，2025年市场份额', 1, 1, 1, 0, 0, 1718956438, 207, 1718957546, 207);
INSERT INTO `system_values` VALUES (71, 33, '备用图片地址', 'img_address', '', 'img_address', 'http://img.globalinforesearch.com.cn', 1, 1, 1, 0, 0, 1719196800, 165, 1726730911, 79);
INSERT INTO `system_values` VALUES (74, 41, '连接host', 'host', '', 'host', '39.108.67.106', 2, 1, 1, 1, 0, 1719366324, 167, 1726627504, 25);
INSERT INTO `system_values` VALUES (75, 41, '连接端口', 'port', '', 'port', '9316', 2, 1, 1, 1, 0, 1719366355, 167, 1726627485, 25);
INSERT INTO `system_values` VALUES (77, 33, 'QQ咨询', 'QQConsulting', '', 'QQConsulting', '2504727233', 1, 1, 1, 1, 0, 1707011164, 1, 1707011164, 0);
INSERT INTO `system_values` VALUES (78, 33, '微信咨询', 'WXConsulting', '', 'WXConsulting', '/site/gircn/setting/network/weiXinZhiXun.webp', 3, 1, 1, 1, 0, 1707011164, 1, 1721984146, 25);
INSERT INTO `system_values` VALUES (79, 33, 'sina', 'sina', '', 'report_share', '/site/gircn/setting/network/weiBo.webp', 3, 1, 1, 1, 0, 1707011164, 1, 1722240783, 25);
INSERT INTO `system_values` VALUES (80, 33, 'wechat', 'wechat', '', 'report_share', '/site/gircn/setting/network/WeChat.webp', 3, 1, 1, 1, 0, 1707011164, 1, 1722240791, 25);
INSERT INTO `system_values` VALUES (81, 33, 'qzone', 'qzone', '', 'report_share', '/site/gircn/setting/network/pengYouQUan.webp', 3, 1, 1, 1, 0, 1707011164, 1, 1722240865, 25);
INSERT INTO `system_values` VALUES (82, 33, '微信公众号', 'WXgongzhonghao', '', 'WXgongzhonghao', '/site/gircn/setting/network/gongZongHao.webp', 3, 1, 1, 1, 0, 1721980848, 175, 1721984167, 25);
INSERT INTO `system_values` VALUES (83, 33, '公安备案号图标', 'police_icon', '', 'police_icon', '/site/gircn/setting/network/police_record.png', 3, 1, 1, 1, 0, 1721984379, 25, 1721984379, 0);
INSERT INTO `system_values` VALUES (84, 33, '公安备案号链接', 'police_link', '', 'police_link', 'http://www.beian.gov.cn/portal/registerSystemInfo?recordcode=44010602008759', 1, 1, 1, 1, 0, 1721985129, 25, 1726730741, 79);
INSERT INTO `system_values` VALUES (85, 33, 'ICP备案链接', 'icp_link', '', 'icp_link', 'https://beian.miit.gov.cn/', 1, 1, 1, 1, 0, 1721985170, 25, 1721985170, 0);
INSERT INTO `system_values` VALUES (86, 33, '支付方式', 'PayMethod', '', 'PayMethod', 'Email发送或顺丰快递', 1, 1, 1, 1, 0, 1707011329, 1, 1707011329, 0);
INSERT INTO `system_values` VALUES (87, 33, '默认报告高清图', 'Default Report Image', '', 'default_report_high_img', '/site/gircn/setting/network/default_report_high_img.webp', 3, 1, 1, 1, 0, 1722236207, 25, 1724229674, 207);
INSERT INTO `system_values` VALUES (88, 33, '默认报告缩略图2', 'Default Report Image', '', 'default_report_img2', '/site/gircn/setting/network/default_report_img2.webp', 3, 1, 1, 1, 0, 1722236207, 25, 1724229680, 207);
INSERT INTO `system_values` VALUES (89, 33, '客户评价PDF', 'Customer Comment PDF', '', 'customer_comment_pdf', '/site/gircn/setting/network/GIR-ke-hu-ping-jia.pdf', 1, 1, 1, 1, 0, 1722317142, 25, 1722317311, 25);
INSERT INTO `system_values` VALUES (90, 33, '公司简介PDF', 'Company profile pdf', '', 'company_profile_pdf', '/site/gircn/setting/network/GIR-CN-gong-si-jian-jie2022.pdf', 1, 1, 1, 1, 0, 1722317266, 25, 1722317304, 25);
INSERT INTO `system_values` VALUES (91, 36, '白名单免安全校验1', 'white_ip_security_check', '', 'white_ip_security_check', '39.108.67.106', 2, 1, 1, 1, 0, 1722390266, 25, 1725257028, 25);
INSERT INTO `system_values` VALUES (93, 42, '窗口时间', 'window_time', '', 'window_time', '5', 1, 1, 1, 1, 0, 1726650820, 79, 1727056293, 25);
INSERT INTO `system_values` VALUES (94, 42, '请求次数', 'req_limit', '', 'req_limit', '10', 1, 1, 1, 1, 0, 1726650872, 79, 1727228331, 220);
INSERT INTO `system_values` VALUES (95, 42, 'ip白名单', 'ip_white_rules', '', 'ip_white_rules', '127.0.0.1\n39.108.67.106\n172.22.121.60\n220.181.108.*\n113.24.225.*\n116.179.32.*\n116.179.37.*\n14.145.*.*', 1, 1, 1, 1, 0, 1726650958, 79, 1727228348, 220);
INSERT INTO `system_values` VALUES (96, 42, '是否开启限流', 'is_open_limit_req', '', 'is_open_limit_req', '1', 1, 1, 1, 1, 0, 1726650994, 79, 1727228303, 220);
INSERT INTO `system_values` VALUES (97, 36, '是否开启接口安全校验', 'is_open_check_security', '', 'is_open_check_security', '1', 1, 1, 1, 0, 0, 1723018276, 167, 1727083363, 220);

-- ----------------------------
-- Records of systems
-- ----------------------------
INSERT INTO `systems` VALUES (33, '网站信息', 'Website Information', 'site_info', 1, 1, 1, 0, 1707011087, 1707011087);
INSERT INTO `systems` VALUES (34, '报告设置', 'Report Settings', 'reports', 1, 1, 1, 165, 1707014954, 1712910161);
INSERT INTO `systems` VALUES (36, '系统设置', 'System Settings', 'system', 1, 1, 1, 0, 1709005292, 1709005292);
INSERT INTO `systems` VALUES (37, '站点语言', 'Site Language', 'site_lan', 1, 1, 79, 167, 1712825260, 1715226221);
INSERT INTO `systems` VALUES (38, '报告产品类型截取规则', 'classificationSubRules', 'classificationSubRules', 1, 1, 167, 207, 1718868214, 1718957618);
INSERT INTO `systems` VALUES (39, '报告产品应用领域截取规则', 'applicationSubRules', 'applicationSubRules', 1, 1, 167, 0, 1718868248, 1718868248);
INSERT INTO `systems` VALUES (41, 'sphinx连接配置', 'sphinxConnectInfo', 'sphinxConnectInfo', 1, 1, 167, 0, 1719366275, 1719366275);
INSERT INTO `systems` VALUES (42, 'IP限流策略', 'ip_limit_rules', 'ip_limit_rules', 1, 1, 79, 0, 1726650781, 1726650781);
INSERT INTO `systems` VALUES (45, '同步数据配置', 'syncDataSet', 'syncDataSet', 1, 1, 167, 0, 1727229280, 1727229280);

SET FOREIGN_KEY_CHECKS = 1;
