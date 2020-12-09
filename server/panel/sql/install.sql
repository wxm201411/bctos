/*
 Navicat Premium Data Transfer

 Source Server         : 192.168.0.8-2
 Source Server Type    : MySQL
 Source Server Version : 50731
 Source Host           : 192.168.0.8:3307
 Source Schema         : bctos_cn

 Target Server Type    : MySQL
 Target Server Version : 50731
 File Encoding         : 65001

 Date: 26/11/2020 16:58:58
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
USE bctos_panel;
DROP TABLE IF EXISTS `wp_action`;
CREATE TABLE `wp_action`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` char(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '行为唯一标识',
  `title` char(80) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '行为说明',
  `remark` char(140) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '行为描述',
  `rule` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '行为规则',
  `log` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '日志规则',
  `type` tinyint(2) UNSIGNED NOT NULL DEFAULT 1 COMMENT '类型',
  `status` tinyint(2) NOT NULL DEFAULT 0 COMMENT '状态',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 14 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '系统行为表' ROW_FORMAT = Dynamic;


DROP TABLE IF EXISTS `wp_action_log`;
CREATE TABLE `wp_action_log`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `action_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '行为id',
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '执行用户id',
  `action_ip` bigint(20) NOT NULL COMMENT '执行行为者ip',
  `model` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '触发行为的表',
  `record_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '触发行为的数据id',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '日志备注',
  `status` tinyint(2) NOT NULL DEFAULT 1 COMMENT '状态',
  `create_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '执行行为的时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `action_ip_ix`(`action_ip`) USING BTREE,
  INDEX `action_id_ix`(`action_id`) USING BTREE,
  INDEX `user_id_ix`(`user_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 35 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '行为日志表' ROW_FORMAT = Dynamic;


DROP TABLE IF EXISTS `wp_admin_log`;
CREATE TABLE `wp_admin_log`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL COMMENT '用户ID',
  `ip` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '用户IP地址',
  `content` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '日志内容',
  `mod` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '应用名',
  `cTime` int(10) NULL DEFAULT NULL COMMENT '记录时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;


DROP TABLE IF EXISTS `wp_analysis`;
CREATE TABLE `wp_analysis`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `sports_id` int(10) NULL DEFAULT 0 COMMENT 'sports_id',
  `type` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'type',
  `time` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'time',
  `total_count` int(10) NULL DEFAULT 0 COMMENT 'total_count',
  `follow_count` int(10) NULL DEFAULT 0 COMMENT 'follow_count',
  `aver_count` int(10) NULL DEFAULT 0 COMMENT 'aver_count',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;


DROP TABLE IF EXISTS `wp_api_log`;
CREATE TABLE `wp_api_log`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `create_at` int(10) NULL DEFAULT 0 COMMENT '创建时间',
  `url` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'url',
  `param` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT 'param',
  `server_ip` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'server_ip',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;


DROP TABLE IF EXISTS `wp_app_shop`;
CREATE TABLE `wp_app_shop`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `create_at` int(10) NULL DEFAULT 0 COMMENT '创建时间',
  `update_at` int(10) NULL DEFAULT 0 COMMENT '更新时间',
  `uid` int(10) NULL DEFAULT 0 COMMENT '用户',
  `status` tinyint(2) NULL DEFAULT 0 COMMENT '状态',
  `type` tinyint(2) NOT NULL DEFAULT 0 COMMENT '扩展类型',
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '扩展名',
  `content` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '内容',
  `img` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '截图',
  `attach` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '安装包',
  `is_top` int(10) NULL DEFAULT 0 COMMENT '置顶',
  `view_count` int(10) NULL DEFAULT 0 COMMENT '浏览数',
  `price` int(10) NULL DEFAULT 0 COMMENT '价格',
  `logo` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '扩展封面',
  `download_count` int(10) NULL DEFAULT 0 COMMENT '下载数',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

DROP TABLE IF EXISTS `wp_app_shop_user`;
CREATE TABLE `wp_app_shop_user`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `create_at` int(10) NULL DEFAULT 0 COMMENT '创建时间',
  `uid` int(10) NULL DEFAULT 0 COMMENT '用户',
  `status` tinyint(2) NULL DEFAULT 0 COMMENT '支付状态',
  `app_id` int(10) NULL DEFAULT 0 COMMENT '扩展ID',
  `price` int(10) NULL DEFAULT 0 COMMENT '支付积分',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 2 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Fixed;

DROP TABLE IF EXISTS `wp_app_shop_web`;
CREATE TABLE `wp_app_shop_web`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '站名',
  `logo` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT 'Logo',
  `url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '网址',
  `wealth` int(10) NULL DEFAULT 0 COMMENT '余额',
  `web_key` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '授权许可证',
  `uid` int(10) NULL DEFAULT 0 COMMENT '用户',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 2 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

DROP TABLE IF EXISTS `wp_apps`;
CREATE TABLE `wp_apps`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(40) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '插件名或标识',
  `title` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '中文名',
  `description` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '插件描述',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态',
  `config` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '配置',
  `author` varchar(40) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '作者',
  `version` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '版本号',
  `create_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '安装时间',
  `has_adminlist` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否有后台列表',
  `type` tinyint(1) NULL DEFAULT 0 COMMENT '插件类型 0 普通插件 1 微信插件 2 易信插件',
  `cate_id` int(11) NULL DEFAULT NULL,
  `is_show` tinyint(2) NULL DEFAULT 1,
  `theme` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '主题名称，为空时取默认主题',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `name`(`name`) USING BTREE,
  INDEX `sti`(`status`, `is_show`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 301 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '微信插件表' ROW_FORMAT = Dynamic;

INSERT INTO `wp_apps` VALUES (299, 'docker', '容器管理', '', 1, '[]', '凡星', '0.1', 0, 1, 0, NULL, 1, NULL);

DROP TABLE IF EXISTS `wp_area`;
CREATE TABLE `wp_area`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `title` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '地区名',
  `pid` int(10) NULL DEFAULT 0 COMMENT '上级编号',
  `sort` int(10) NULL DEFAULT 0 COMMENT '排序号',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 659 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

DROP TABLE IF EXISTS `wp_auth_extend`;
CREATE TABLE `wp_auth_extend`  (
  `group_id` mediumint(10) UNSIGNED NOT NULL COMMENT '用户id',
  `extend_id` mediumint(8) UNSIGNED NOT NULL COMMENT '扩展表中数据的id',
  `type` tinyint(1) UNSIGNED NOT NULL COMMENT '扩展类型标识 1:栏目分类权限;2:模型权限',
  UNIQUE INDEX `group_extend_type`(`group_id`, `extend_id`, `type`) USING BTREE,
  INDEX `uid`(`group_id`) USING BTREE,
  INDEX `group_id`(`extend_id`) USING BTREE
) ENGINE = MyISAM CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '用户组与分类的对应关系表' ROW_FORMAT = Fixed;


DROP TABLE IF EXISTS `wp_auth_group`;
CREATE TABLE `wp_auth_group`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `title` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '分组名称',
  `icon` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT '图标',
  `description` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '描述信息',
  `status` tinyint(2) NULL DEFAULT 1 COMMENT '状态',
  `type` tinyint(2) NULL DEFAULT 1 COMMENT '类型',
  `rules` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '权限',
  `manager_id` int(10) NULL DEFAULT 0 COMMENT '管理员ID',
  `pbid` int(10) NULL DEFAULT 0 COMMENT 'pbid',
  `is_default` tinyint(1) NULL DEFAULT 0 COMMENT '是否默认自动加入',
  `qr_code` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '微信二维码',
  `wechat_group_id` int(10) NULL DEFAULT -1 COMMENT '微信端的分组ID',
  `wechat_group_name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '微信端的分组名',
  `wechat_group_count` int(10) NULL DEFAULT 0 COMMENT '微信端用户数',
  `is_del` tinyint(1) NULL DEFAULT 0 COMMENT '是否已删除',
  `menu_rule` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '菜单权限',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 196 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

INSERT INTO `wp_auth_group` VALUES (2, '超级管理员', NULL, '所有从公众号自动注册的粉丝用户都会自动加入这个用户组', 1, 0, '1,2,5,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,56,57,58,59,60,61,62,63,64,65,66,67,68,69,70,71,72,73,74,79,80,82,83,84,88,89,90,91,92,93,96,97,100,102,103,195', 0, NULL, 0, NULL, NULL, NULL, NULL, 0, '1,2,3,4,7,8,852,12,882,889,890,891,892,893,894,897,898');

DROP TABLE IF EXISTS `wp_auth_group_access`;
CREATE TABLE `wp_auth_group_access`  (
  `uid` int(10) NULL DEFAULT NULL COMMENT '用户id',
  `group_id` mediumint(8) UNSIGNED NOT NULL COMMENT '用户组id',
  `cTime` int(11) NULL DEFAULT NULL,
  UNIQUE INDEX `uid_group_id`(`uid`, `group_id`) USING BTREE,
  INDEX `uid`(`uid`) USING BTREE,
  INDEX `group_id`(`group_id`) USING BTREE
) ENGINE = MyISAM CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Fixed;

INSERT INTO `wp_auth_group_access` VALUES (1, 2, NULL);


DROP TABLE IF EXISTS `wp_auth_rule`;
CREATE TABLE `wp_auth_rule`  (
  `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '规则id,自增主键',
  `name` char(80) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '规则唯一英文标识',
  `title` char(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '规则中文描述',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否有效(0:无效,1:有效)',
  `condition` varchar(300) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '规则附加条件',
  `group` char(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '默认分组',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `name`(`name`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 280 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

INSERT INTO `wp_auth_rule` VALUES (241, 'Admin/Rule/createRule', '权限节点管理', 1, '', '默认分组');
INSERT INTO `wp_auth_rule` VALUES (242, 'Admin/AuthManager/index', '用户组管理', 1, '', '默认分组');
INSERT INTO `wp_auth_rule` VALUES (243, 'Admin/User/index', '用户信息', 1, '', '用户管理');

DROP TABLE IF EXISTS `wp_auto_reply`;
CREATE TABLE `wp_auto_reply`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `video_id` int(10) NULL DEFAULT 0 COMMENT '视频素材id',
  `voice_id` int(10) NULL DEFAULT 0 COMMENT '语音素材id',
  `image_material` int(10) NULL DEFAULT 0 COMMENT '素材图片id',
  `manager_id` int(10) NULL DEFAULT 0 COMMENT '管理员ID',
  `img_id` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT '上传图片',
  `news_id` int(10) NULL DEFAULT 0 COMMENT '图文',
  `msg_type` char(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '消息类型',
  `keyword` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '关键词',
  `text_id` int(10) NULL DEFAULT 0 COMMENT '文本素材id',
  `pbid` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '公众号id',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;


DROP TABLE IF EXISTS `wp_block`;
CREATE TABLE `wp_block`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `page` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '页面',
  `block_id` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '块元素',
  `content` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '配置参数',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 6 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;


DROP TABLE IF EXISTS `wp_blockchain_channel`;
CREATE TABLE `wp_blockchain_channel`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `channel_name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '通道名称',
  `version` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '通道版本号',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;


DROP TABLE IF EXISTS `wp_cache_keys`;
CREATE TABLE `wp_cache_keys`  (
  `table_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `key_rule` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `map_field` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `data_field` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `extra` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

DROP TABLE IF EXISTS `wp_channel`;
CREATE TABLE `wp_channel`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '频道ID',
  `pid` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '上级频道ID',
  `title` char(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '频道标题',
  `url` char(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '频道连接',
  `sort` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '导航排序',
  `create_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '状态',
  `target` tinyint(2) UNSIGNED NOT NULL DEFAULT 0 COMMENT '新窗口打开',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;


DROP TABLE IF EXISTS `wp_city`;
CREATE TABLE `wp_city`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `city` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `manager_uids` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
  `cTime` int(11) NULL DEFAULT NULL,
  `logo` int(11) NULL DEFAULT NULL COMMENT '城市分站LOGO',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

DROP TABLE IF EXISTS `wp_comment`;
CREATE TABLE `wp_comment`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `aim_table` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '评论关联数据表',
  `aim_id` int(10) NULL DEFAULT 0 COMMENT '评论关联ID',
  `cTime` int(10) NULL DEFAULT 0 COMMENT '评论时间',
  `follow_id` int(10) NULL DEFAULT 0 COMMENT 'follow_id',
  `content` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '评论内容',
  `is_audit` tinyint(2) NULL DEFAULT 0 COMMENT '是否审核',
  `uid` int(10) NULL DEFAULT 0 COMMENT 'uid',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;


DROP TABLE IF EXISTS `wp_common_category`;
CREATE TABLE `wp_common_category`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `slug` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '分类标识',
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '分类标题',
  `pid` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '上一级分类',
  `app` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '所属应用',
  `sort` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '排序号',
  `is_show` tinyint(2) NULL DEFAULT 1 COMMENT '是否显示',
  `wpid` int(10) NOT NULL DEFAULT 0 COMMENT 'wpid',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;


DROP TABLE IF EXISTS `wp_common_category_link`;
CREATE TABLE `wp_common_category_link`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `category_id` int(10) NULL DEFAULT 0 COMMENT '分类ID',
  `app` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '应用名',
  `data_id` int(10) NULL DEFAULT 0 COMMENT '应用数据ID',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;


DROP TABLE IF EXISTS `wp_common_category_meta`;
CREATE TABLE `wp_common_category_meta`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `category_id` int(10) NOT NULL DEFAULT 0 COMMENT '分类ID',
  `meta_key` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'key',
  `meta_value` longtext CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT 'value',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;


DROP TABLE IF EXISTS `wp_config`;
CREATE TABLE `wp_config`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '配置ID',
  `name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '配置名称',
  `type` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '配置类型',
  `title` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '配置说明',
  `group` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '配置分组',
  `extra` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '配置值',
  `remark` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '配置说明',
  `create_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '状态',
  `value` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '配置值',
  `sort` smallint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_name`(`name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 76 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '系统配置表' ROW_FORMAT = Dynamic;

INSERT INTO `wp_config` VALUES (1, 'WEB_SITE_TITLE', 1, '网站标题', 1, '', '网站标题前台显示标题', 1378898976, 1430825115, 1, '小韦云面板', 0);
INSERT INTO `wp_config` VALUES (2, 'WEB_SITE_DESCRIPTION', 2, '网站描述', 1, '', '网站搜索引擎描述', 1378898976, 1379235841, 1, '小韦云面板是基于docker容器实现的一键部署PHP集成环境的管理面板，WEB端使用PHP+MYSQL实现。它主要实现网站部署，数据库管理，文件管理，计划任务，容器管理等功能', 9);
INSERT INTO `wp_config` VALUES (3, 'WEB_SITE_KEYWORD', 2, '网站关键字', 1, '', '网站搜索引擎关键字', 1378898976, 1381390100, 1, '小韦云面板 小韦云科技 PHP集成环境 容器管理 docker-web', 8);
INSERT INTO `wp_config` VALUES (9, 'CONFIG_TYPE_LIST', 3, '配置类型列表', 6, '', '主要用于数据解析和页面表单的生成', 1378898976, 1379235348, 1, '0:数字\r\n1:字符\r\n2:文本\r\n3:数组\r\n4:枚举', 2);
INSERT INTO `wp_config` VALUES (10, 'WEB_SITE_ICP', 1, '网站备案号', 1, '', '设置在网站底部显示的备案号，如“沪ICP备12007941号-2', 1378900335, 1379235859, 1, '', 9);
INSERT INTO `wp_config` VALUES (20, 'CONFIG_GROUP_LIST', 3, '配置分组', 6, '', '配置分组', 1379228036, 1384418383, 1, '1:基本\r\n3:用户\r\n6:开发\r\n99:高级', 4);
INSERT INTO `wp_config` VALUES (36, 'ADMIN_ALLOW_IP', 2, '后台允许访问IP', 99, '', '多个用逗号分隔，如果不配置表示不限制IP访问', 1387165454, 1387165553, 1, '', 12);
INSERT INTO `wp_config` VALUES (38, 'WEB_SITE_VERIFY', 4, '登录验证码', 3, '0:关闭\r\n1:开启', '登录时是否需要验证码', 1378898976, 1406859544, 1, '0', 2);
INSERT INTO `wp_config` VALUES (42, 'ACCESS', 2, '未登录时可访问的页面', 6, '', '不区分大小写', 1390656601, 1390664079, 1, 'Home/User/*\r\nHome/Index/*\r\nHome/Help/*\r\nhome/weixin/*\r\nadmin/File/*\r\nhome/File/*\r\nhome/Forum/*\r\nHome/Material/detail', 0);
INSERT INTO `wp_config` VALUES (46, 'SYSTEM_UPDATRE_VERSION', 0, '系统升级最新版本号', 0, '', '记录当前系统的版本号，这是与官方比较是否有升级包的唯一标识，不熟悉者只勿改变其数值', 1393764702, 1394337646, 1, '20160708', 0);
INSERT INTO `wp_config` VALUES (50, 'COPYRIGHT', 1, '版权信息', 1, '', '', 1401018910, 1401018910, 1, '版本由小韦云科技有限公司所有', 3);
INSERT INTO `wp_config` VALUES (60, 'TONGJI_CODE', 2, '第三方统计JS代码', 99, '', '', 1428634717, 1428634717, 1, '', 0);
INSERT INTO `wp_config` VALUES (75, 'SCAN_LOGIN', 4, '是否开启扫码登录', 10, '0:关闭\r\n1:开启', '', 0, 0, 0, '0', 0);

DROP TABLE IF EXISTS `wp_credit_config`;
CREATE TABLE `wp_credit_config`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `title` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '规则名称',
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '规则标识名',
  `mod` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '应用英文名，核心功能默认为common',
  `mTime` int(10) NULL DEFAULT 0 COMMENT '更新时间',
  `score` int(10) NULL DEFAULT 0 COMMENT '积分值',
  `type` tinyint(1) NULL DEFAULT 0 COMMENT '规则类型 0是公众号积分规则 1是非公众号积分规则 2是可变积分规则',
  `wpid` int(10) NOT NULL DEFAULT 0 COMMENT 'wpid',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 52 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

DROP TABLE IF EXISTS `wp_credit_data`;
CREATE TABLE `wp_credit_data`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `uid` int(10) NOT NULL DEFAULT 0 COMMENT '用户ID',
  `credit_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '规则标识名',
  `credit_title` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '积分描述',
  `score` int(10) NULL DEFAULT 0 COMMENT '积分值',
  `cTime` int(10) NOT NULL DEFAULT 0 COMMENT '记录时间',
  `admin_uid` int(10) NULL DEFAULT 0 COMMENT '操作者UID，0表示系统自动增加',
  `wpid` int(10) NOT NULL DEFAULT 0 COMMENT 'wpid',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;


DROP TABLE IF EXISTS `wp_credit_grade`;
CREATE TABLE `wp_credit_grade`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '等级名称',
  `icon` int(10) NULL DEFAULT NULL COMMENT '等级图标',
  `mTime` int(10) NULL DEFAULT NULL COMMENT '更新时间',
  `score` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '累计积分要求的值',
  `wpid` int(10) NOT NULL DEFAULT 0 COMMENT 'wpid',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;


DROP TABLE IF EXISTS `wp_cron`;
CREATE TABLE `wp_cron`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `type` char(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '任务类型',
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '任务名称',
  `time_type` char(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'day' COMMENT '执行周期',
  `time_day` tinyint(4) NULL DEFAULT 0 COMMENT 'N号',
  `time_hour` tinyint(2) NULL DEFAULT 0 COMMENT 'N点',
  `time_minute` tinyint(2) NULL DEFAULT 0 COMMENT 'N分',
  `time_week` char(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '周N',
  `site_id` char(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '备份网站',
  `max_keep` int(10) NULL DEFAULT 3 COMMENT '保留最新',
  `exclude` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '排除规则',
  `shell_content` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '脚本内容',
  `update_at` int(10) NULL DEFAULT 0 COMMENT '最近执行',
  `database_id` char(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '备份数据库',
  `jump_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'URL地址',
  `dir_path` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '备份目录',
  `time_day2` int(10) NULL DEFAULT 1 COMMENT 'N天',
  `time_cron` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '定时任务时间',
  `database` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '数据库版本',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 70 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '计划任务' ROW_FORMAT = DYNAMIC;

DROP TABLE IF EXISTS `wp_custom_menu`;
CREATE TABLE `wp_custom_menu`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `pid` int(10) NULL DEFAULT 0 COMMENT '一级菜单',
  `title` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '菜单名',
  `from` char(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '来源 0一级菜单，1素材 2URL 3自定义',
  `type` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '内容类型：\r\ntext:素材文本\r\nimg:素材图片\r\nnews:素材图文\r\nvideo:素材视频\r\nvoice：素材语音\r\nurl:URL地址\r\nclick：点击推事件\r\nscancode_push：扫码推事件 \r\nscancode_waitmsg：扫码带提示\r\npic_sysphoto：弹出系统拍照发图  \r\npic_photo_or_album： 弹出拍照或者相册发图  \r\npic_weixin：弹出微信相册发图器  \r\nlocation_select：弹出地理位置选择器',
  `sort` tinyint(4) NULL DEFAULT 0 COMMENT '排序号',
  `rule_id` int(11) NULL DEFAULT 0 COMMENT '个性化菜单ID，0表示默认菜单',
  `material` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'material',
  `url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'URL',
  `keyword` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '关键词',
  `appid` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'appid',
  `pagepath` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'pagepath',
  `appurl` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'appurl',
  `pbid` int(10) NULL DEFAULT 0 COMMENT '公众号id',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;


DROP TABLE IF EXISTS `wp_custom_menu_rule`;
CREATE TABLE `wp_custom_menu_rule`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `tag_id` int(11) NULL DEFAULT 0,
  `sex` tinyint(1) NULL DEFAULT 0 COMMENT '0 不限 1 男 2女',
  `os` tinyint(4) NULL DEFAULT 0 COMMENT '0不限 1ios 2android 3other',
  `city` int(11) NULL DEFAULT NULL,
  `province` int(11) NULL DEFAULT NULL,
  `country` int(11) NULL DEFAULT NULL,
  `lang` char(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `menuid` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '微信返回的ID,用于后续删除菜单接口',
  `pbid` int(10) NOT NULL DEFAULT 0 COMMENT '公众号id',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;


DROP TABLE IF EXISTS `wp_custom_reply_mult`;
CREATE TABLE `wp_custom_reply_mult`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `keyword` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '关键词',
  `keyword_type` tinyint(2) NULL DEFAULT 0 COMMENT '关键词类型',
  `mult_ids` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '多图文ID',
  `wpid` int(10) NOT NULL DEFAULT 0 COMMENT 'wpid',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;


DROP TABLE IF EXISTS `wp_custom_reply_news`;
CREATE TABLE `wp_custom_reply_news`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `keyword` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '关键词',
  `keyword_type` tinyint(2) NULL DEFAULT 0 COMMENT '关键词类型',
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '标题',
  `intro` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '简介',
  `cate_id` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '所属类别',
  `cover` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT '封面图片',
  `content` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '内容',
  `cTime` int(10) NULL DEFAULT 0 COMMENT '发布时间',
  `sort` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '排序号',
  `view_count` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '浏览数',
  `jump_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '外链',
  `author` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '作者',
  `show_type` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT '显示方式',
  `is_show` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '1' COMMENT '图片是否显示在内容页',
  `wpid` int(10) NOT NULL DEFAULT 0 COMMENT 'wpid',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;


DROP TABLE IF EXISTS `wp_custom_reply_text`;
CREATE TABLE `wp_custom_reply_text`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `keyword` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '关键词',
  `keyword_type` tinyint(2) NULL DEFAULT 0 COMMENT '关键词类型',
  `content` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '回复内容',
  `view_count` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '浏览数',
  `sort` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '排序号',
  `wpid` int(10) NOT NULL DEFAULT 0 COMMENT 'wpid',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;


DROP TABLE IF EXISTS `wp_custom_sendall`;
CREATE TABLE `wp_custom_sendall`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `ToUserName` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'token',
  `FromUserName` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'openid',
  `cTime` int(10) NULL DEFAULT 0 COMMENT '创建时间',
  `msgType` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '消息类型',
  `manager_id` int(10) NULL DEFAULT 0 COMMENT '管理员id',
  `content` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '内容',
  `media_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '多媒体文件id',
  `is_send` int(10) NULL DEFAULT 0 COMMENT '是否已经发送',
  `uid` int(10) NULL DEFAULT 0 COMMENT '粉丝uid',
  `news_group_id` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '图文组id',
  `video_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '视频标题',
  `video_description` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '视频描述',
  `video_thumb` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '视频缩略图',
  `voice_id` int(10) NULL DEFAULT 0 COMMENT '语音id',
  `image_id` int(10) NULL DEFAULT 0 COMMENT '图片id',
  `video_id` int(10) NULL DEFAULT 0 COMMENT '视频id',
  `send_type` int(10) NULL DEFAULT 0 COMMENT '发送方式',
  `send_openids` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '指定用户',
  `group_id` int(10) NULL DEFAULT 0 COMMENT '分组id',
  `diff` int(10) NULL DEFAULT 0 COMMENT '区分消息标识',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;


DROP TABLE IF EXISTS `wp_database`;
CREATE TABLE `wp_database`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `database` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `db_name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '数据库名',
  `db_user` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '用户名',
  `db_passwd` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '密码',
  `db_set` char(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'utf8mb4' COMMENT '字符集',
  `power` char(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'utf8mb4' COMMENT '权限',
  `ip` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '指定IP',
  `backup_at` int(10) NULL DEFAULT 0 COMMENT '备份',
  `remark` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '备注',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 27 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '数据库' ROW_FORMAT = DYNAMIC;

INSERT INTO `wp_database` VALUES (25, 't2_cn', 't2_cn', '5fbf0b2215501', 'utf8mb4', '%', '', 1606355962, '123');
INSERT INTO `wp_database` VALUES (26, 't3_cn', 't3_cn', '5fbf0b3459f2d', 'utf8mb4', 'localhost', '', 0, '');

DROP TABLE IF EXISTS `wp_debug_log`;
CREATE TABLE `wp_debug_log`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `data_post` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
  `cTime_format` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `cTime` int(10) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 174 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

DROP TABLE IF EXISTS `wp_docker`;
CREATE TABLE `wp_docker`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `CONTAINER_ID` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '容器ID',
  `IMAGE` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '镜像',
  `COMMAND` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '命令',
  `CREATED` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '创建时间',
  `STATUS` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '状态',
  `PORTS` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '端口',
  `NAMES` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '容器名',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;


DROP TABLE IF EXISTS `wp_file`;
CREATE TABLE `wp_file`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '文件ID',
  `name` char(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '原始文件名',
  `savename` char(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '保存名称',
  `savepath` char(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '文件保存路径',
  `ext` char(5) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '文件后缀',
  `mime` char(40) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '文件mime类型',
  `size` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '文件大小',
  `md5` char(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '文件md5',
  `sha1` char(40) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '文件 sha1编码',
  `location` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '文件保存位置',
  `create_time` int(10) UNSIGNED NOT NULL COMMENT '上传时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_md5`(`md5`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 87 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '文件表' ROW_FORMAT = Dynamic;

DROP TABLE IF EXISTS `wp_images`;
CREATE TABLE `wp_images`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `REPOSITORY` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '仓库名',
  `TAG` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '标签',
  `IMAGE_ID` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '镜像ID',
  `CREATED` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '创建时间',
  `SIZE` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '大小',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;


DROP TABLE IF EXISTS `wp_import`;
CREATE TABLE `wp_import`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `attach` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '上传文件',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;


DROP TABLE IF EXISTS `wp_install`;
CREATE TABLE `wp_install`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `title` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '软件名称',
  `dev` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '开发商',
  `intro` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '介绍',
  `status` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT '运行状态',
  `is_audit` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT '审核状态',
  `is_del` tinyint(2) NULL DEFAULT 0 COMMENT '是否已删除',
  `download_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '下载地址',
  `database` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '数据库版本',
  `db_set` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'db_set',
  `db_file` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'db_file',
  `redis` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'redis',
  `redis_file` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'redis_file',
  `memcached` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'memcached',
  `memcached_file` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'memcached_file',
  `php_version` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'php_version',
  `php_func` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'php_func',
  `php_ext` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'php_ext',
  `rewrite_mod` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'rewrite_mod',
  `rewrite` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT 'rewrite',
  `public_path` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'public_path',
  `index_file` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'index_file',
  `admin_user` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'admin_user',
  `admin_passwd` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'admin_passwd',
  `rm_file` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'rm_file',
  `download_type` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'git' COMMENT 'download_type',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 14 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '一键部署' ROW_FORMAT = DYNAMIC;


DROP TABLE IF EXISTS `wp_keyword`;
CREATE TABLE `wp_keyword`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `keyword` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '关键词',
  `addon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '关键词所属插件',
  `aim_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '插件表里的ID值',
  `cTime` int(10) NULL DEFAULT 0 COMMENT '创建时间',
  `keyword_length` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '关键词长度',
  `keyword_type` tinyint(2) NULL DEFAULT 0 COMMENT '匹配类型',
  `extra_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '文本扩展',
  `extra_int` int(10) NULL DEFAULT 0 COMMENT '数字扩展',
  `request_count` int(10) NULL DEFAULT 0 COMMENT '请求数',
  `wpid` int(10) NOT NULL DEFAULT 0 COMMENT 'wpid',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `keyword_token`(`keyword`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;


DROP TABLE IF EXISTS `wp_lang`;
CREATE TABLE `wp_lang`  (
  `lang_id` int(10) NOT NULL AUTO_INCREMENT,
  `key` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'key',
  `appname` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '所属应用名称',
  `filetype` tinyint(2) NULL DEFAULT 0 COMMENT '针对的文件类型，0:php,1:js',
  `zh-cn` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '中文',
  `en` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '英文',
  `zh-tw` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '繁体',
  PRIMARY KEY (`lang_id`) USING BTREE,
  UNIQUE INDEX `app`(`appname`, `key`, `filetype`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1863 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

DROP TABLE IF EXISTS `wp_manager`;
CREATE TABLE `wp_manager`  (
  `uid` int(10) NOT NULL DEFAULT 0 COMMENT '用户ID',
  `has_public` tinyint(2) NULL DEFAULT 0 COMMENT '是否配置公众号',
  `headface_url` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '管理员头像',
  `GammaAppId` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '摇电视的AppId',
  `GammaSecret` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '摇电视的Secret',
  `copy_right` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '授权信息',
  `tongji_code` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '统计代码',
  `website_logo` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '网站LOGO',
  PRIMARY KEY (`uid`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;


DROP TABLE IF EXISTS `wp_material_file`;
CREATE TABLE `wp_material_file`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `file_id` int(10) NULL DEFAULT 0 COMMENT '上传文件',
  `cover_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '本地URL',
  `media_id` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT '微信端图文消息素材的media_id',
  `wechat_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '微信端的文件地址',
  `cTime` int(10) NULL DEFAULT 0 COMMENT '创建时间',
  `manager_id` int(10) NULL DEFAULT 0 COMMENT '管理员ID',
  `title` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '素材名称',
  `type` int(10) NULL DEFAULT 0 COMMENT '类型',
  `introduction` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '描述',
  `is_use` int(10) NULL DEFAULT 1 COMMENT '可否使用',
  `aim_id` int(10) NULL DEFAULT 0 COMMENT '添加来源标识id',
  `aim_table` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '来源表名',
  `pbid` int(10) NOT NULL DEFAULT 0 COMMENT 'pbid',
  `admin_uid` int(11) NULL DEFAULT 0 COMMENT '操作员UID',
  `pub_id` int(11) NULL DEFAULT 0 COMMENT '共享素材ID，用于去重',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

DROP TABLE IF EXISTS `wp_material_image`;
CREATE TABLE `wp_material_image`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `cover_id` int(10) NULL DEFAULT 0 COMMENT '图片在本地的ID',
  `cover_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '本地URL',
  `media_id` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT '微信端图文消息素材的media_id',
  `wechat_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '微信端的图片地址',
  `cTime` int(10) NULL DEFAULT 0 COMMENT '创建时间',
  `manager_id` int(10) NULL DEFAULT 0 COMMENT '管理员ID',
  `is_use` int(10) NULL DEFAULT 1 COMMENT '可否使用',
  `aim_id` int(10) NULL DEFAULT 0 COMMENT '添加来源标识id',
  `aim_table` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '来源表名',
  `pbid` int(10) NOT NULL DEFAULT 0 COMMENT 'pbid',
  `admin_uid` int(11) NULL DEFAULT 0 COMMENT '操作员UID',
  `pub_id` int(11) NULL DEFAULT 0 COMMENT '共享素材ID，用于去重',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

DROP TABLE IF EXISTS `wp_material_news`;
CREATE TABLE `wp_material_news`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `title` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '标题',
  `author` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '作者',
  `cover_id` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT '封面',
  `intro` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '摘要',
  `content` longtext CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '内容',
  `link` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '外链',
  `group_id` int(10) NULL DEFAULT 0 COMMENT '多图文组的ID',
  `thumb_media_id` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '图文消息的封面图片素材id（必须是永久mediaID）',
  `media_id` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT '微信端图文消息素材的media_id',
  `manager_id` int(10) NULL DEFAULT 0 COMMENT '管理员ID',
  `cTime` int(10) NULL DEFAULT 0 COMMENT '发布时间',
  `url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '图文页url',
  `is_use` int(10) NULL DEFAULT 1 COMMENT '可否使用',
  `aim_id` int(10) NULL DEFAULT 0 COMMENT '添加来源标识id',
  `aim_table` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '来源表名',
  `update_time` int(10) NULL DEFAULT 0 COMMENT 'update_time',
  `pbid` int(10) NOT NULL DEFAULT 0 COMMENT 'pbid',
  `admin_uid` int(11) NULL DEFAULT 0 COMMENT '操作员UID',
  `pub_id` int(11) NULL DEFAULT 0 COMMENT '共享素材ID，用于去重',
  `forbit_del` tinyint(2) NULL DEFAULT 0 COMMENT '是否禁止删除',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;


DROP TABLE IF EXISTS `wp_material_text`;
CREATE TABLE `wp_material_text`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `content` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '文本内容',
  `uid` int(10) NULL DEFAULT 0 COMMENT 'uid',
  `is_use` int(10) NULL DEFAULT 1 COMMENT '可否使用',
  `aim_id` int(10) NULL DEFAULT 0 COMMENT '添加来源标识id',
  `aim_table` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '来源表名',
  `pbid` int(10) NOT NULL DEFAULT 0 COMMENT 'pbid',
  `admin_uid` int(11) NULL DEFAULT 0 COMMENT '操作员UID',
  `pub_id` int(11) NULL DEFAULT 0 COMMENT '共享素材ID，用于去重',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;


DROP TABLE IF EXISTS `wp_menu`;
CREATE TABLE `wp_menu`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `pid` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT '上级菜单',
  `title` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '菜单名',
  `url_type` tinyint(2) NULL DEFAULT 0 COMMENT '链接类型',
  `addon_name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '插件名',
  `url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '外链',
  `icon` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '图标',
  `is_hide` tinyint(2) NULL DEFAULT 0 COMMENT '是否隐藏',
  `sort` int(10) NULL DEFAULT 0 COMMENT '排序号',
  `place` tinyint(1) NULL DEFAULT 0 COMMENT '0：运营端，1：开发端',
  `target` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '打开方式',
  `rule` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '2' COMMENT '权限',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 899 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

INSERT INTO `wp_menu` VALUES (1, '0', '开发功能', 1, '', 'admin/Apps/index', '', 1, 99, 0, '_self', NULL);
INSERT INTO `wp_menu` VALUES (2, '1', '应用管理', 1, '', 'admin/Apps/index', NULL, 0, 1, 0, '_self', NULL);
INSERT INTO `wp_menu` VALUES (3, '1', '数据模型', 1, '', 'admin/Model/index', NULL, 0, 2, 0, '_self', NULL);
INSERT INTO `wp_menu` VALUES (4, '1', '菜单管理', 1, '', 'admin/Menu/lists', NULL, 0, 3, 0, '_self', NULL);
INSERT INTO `wp_menu` VALUES (7, '1', '网站设置', 1, '', 'admin/Config/group', NULL, 0, 6, 0, '_self', NULL);
INSERT INTO `wp_menu` VALUES (8, '1', '配置管理', 1, '', 'admin/Config/index', NULL, 0, 7, 0, '_self', NULL);
INSERT INTO `wp_menu` VALUES (12, '1', '清除缓存', 1, '', 'admin/Update/delcache', NULL, 0, 11, 0, '_self', NULL);
INSERT INTO `wp_menu` VALUES (852, '1', '权限配置', 1, '', 'admin/rules/index', '', 1, 8, 0, '_self', '2');
INSERT INTO `wp_menu` VALUES (882, '0', '容器管理', 0, 'docker', '', '', 0, 4, 0, '_self', '2');
INSERT INTO `wp_menu` VALUES (888, '1', '在线升级', 1, '', 'admin/Update/index', '', 0, 8, 0, '_self', '2');
INSERT INTO `wp_menu` VALUES (889, '0', '网站', 1, '', 'admin/site/lists', '', 0, 0, 0, '_self', '2');
INSERT INTO `wp_menu` VALUES (890, '0', '数据库', 1, '', 'admin/database/lists', '', 0, 0, 0, '_self', '2');
INSERT INTO `wp_menu` VALUES (891, '0', '文件', 1, '', 'admin/site/files', '', 0, 0, 0, '_blank', '2');
INSERT INTO `wp_menu` VALUES (892, '0', '终端', 1, '', 'admin/xterm/lists', '', 0, 0, 0, '_self', '2');
INSERT INTO `wp_menu` VALUES (893, '0', '计划任务', 1, '', 'admin/cron/lists', '', 0, 0, 0, '_self', '2');
INSERT INTO `wp_menu` VALUES (894, '0', '软件设置', 1, '', 'admin/Soft/lists', '', 0, 0, 0, '_self', '2');
INSERT INTO `wp_menu` VALUES (898, '0', '一键部署', 1, '', 'admin/install/lists', NULL, 0, 0, 0, '_self', '2');

DROP TABLE IF EXISTS `wp_message`;
CREATE TABLE `wp_message`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `bind_keyword` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '关联关键词',
  `preview_openids` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '预览人OPENID',
  `group_id` int(10) NULL DEFAULT 0 COMMENT '群发对象',
  `type` tinyint(2) NULL DEFAULT 0 COMMENT '素材来源',
  `media_id` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '微信素材ID',
  `send_type` tinyint(1) NULL DEFAULT 0 COMMENT '发送方式',
  `send_openids` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '要发送的OpenID',
  `msg_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'msg_id',
  `content` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '文本消息内容',
  `msgtype` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '消息类型',
  `appmsg_id` int(10) NULL DEFAULT 0 COMMENT '图文id',
  `voice_id` int(10) NULL DEFAULT 0 COMMENT '语音id',
  `video_id` int(10) NULL DEFAULT 0 COMMENT '视频id',
  `cTime` int(10) NULL DEFAULT 0 COMMENT '群发时间',
  `pbid` int(10) NOT NULL DEFAULT 0 COMMENT 'pbid',
  `image_id` int(10) NULL DEFAULT 0 COMMENT '图片ID',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;


DROP TABLE IF EXISTS `wp_model`;
CREATE TABLE `wp_model`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '模型ID',
  `name` char(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '模型标识',
  `title` char(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '模型名称',
  `extend` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '继承的模型',
  `relation` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '继承与被继承模型的关联字段',
  `need_pk` tinyint(1) UNSIGNED NULL DEFAULT 1 COMMENT '新建表时是否需要主键字段',
  `field_sort` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '表单字段排序',
  `field_group` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '1:基础' COMMENT '字段分组',
  `attribute_list` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '属性列表（表的字段）',
  `template_list` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '列表模板',
  `template_add` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '新增模板',
  `template_edit` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '编辑模板',
  `list_grid` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '列表定义',
  `list_row` smallint(2) UNSIGNED NULL DEFAULT 10 COMMENT '列表数据长度',
  `search_key` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '默认搜索字段',
  `search_list` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '高级搜索的字段',
  `create_time` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '更新时间',
  `status` tinyint(3) UNSIGNED NULL DEFAULT 0 COMMENT '状态',
  `engine_type` varchar(25) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'MyISAM' COMMENT '数据库引擎',
  `addon` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '所属插件',
  `file_md5` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `name`(`name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2380 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '系统模型表' ROW_FORMAT = Dynamic;

INSERT INTO `wp_model` VALUES (1, 'user', '用户信息表', 0, '', 0, '[\"come_from\",\"nickname\",\"password\",\"truename\",\"mobile\",\"email\",\"sex\",\"headimgurl\",\"city\",\"province\",\"country\",\"language\",\"score\",\"unionid\",\"login_count\",\"reg_ip\",\"reg_time\",\"last_login_ip\",\"last_login_time\",\"status\",\"is_init\",\"is_audit\"]', '1:基础', '', '', '', '', 'headimgurl|url_img_html:头像\r\nlogin_name:登录账号\r\nlogin_password:登录密码\r\nnickname|deal_emoji:用户昵称\r\nsex|get_name_by_status:性别\r\ngroup:分组\r\nscore:金币值\r\nids:操作:set_login?uid=[uid]|设置登录账号,detail?uid=[uid]|详细资料,[EDIT]|编辑', 20, '', '', 1436929111, 1441187405, 1, 'MyISAM', 'core', NULL);
INSERT INTO `wp_model` VALUES (3, 'menu', '公众号管理员菜单', 0, '', 1, '[\"menu_type\",\"pid\",\"title\",\"url_type\",\"addon_name\",\"url\",\"target\",\"is_hide\",\"sort\"]', '1:基础', '', '', '', '', 'title:菜单名\r\nmenu_type|get_name_by_status:菜单类型\r\naddon_name:插件名\r\nurl:外链\r\ntarget|get_name_by_status:打开方式\r\nis_hide|get_name_by_status:隐藏\r\nsort:排序号\r\nids:操作:[EDIT]|编辑,[DELETE]|删除', 65535, '', '', 1435215960, 1437623073, 1, 'MyISAM', 'core', NULL);
INSERT INTO `wp_model` VALUES (20, 'auth_group', '用户组', 0, '', 1, '[\"title\",\"description\"]', '1:基础', '', '', '', '', 'title:分组名称\r\ndescription:描述\r\nqr_code:二维码\r\nids:操作:export?id=[id]|导出用户,[EDIT]|编辑,[DELETE]|删除', 20, 'title', '', 1437633503, 1447660681, 1, 'MyISAM', 'core', NULL);
INSERT INTO `wp_model` VALUES (1225, 'public_config', '公共配置信息', 0, '', 1, NULL, '1:基础', NULL, '', '', '', NULL, 10, '', '', 0, 0, 0, 'MyISAM', 'core', NULL);
INSERT INTO `wp_model` VALUES (2347, 'app_shop', '扩展商城', 0, '', 1, NULL, '1:基础', NULL, '', '', '', NULL, 20, '', '', 0, 0, 0, 'MyISAM', 'core', NULL);
INSERT INTO `wp_model` VALUES (2350, 'app_shop_web', '网站信息', 0, '', 1, NULL, '1:基础', NULL, '', '', '', NULL, 20, '', '', 0, 0, 0, 'MyISAM', 'core', NULL);
INSERT INTO `wp_model` VALUES (2351, 'app_shop_user', '用户下载记录', 0, '', 1, NULL, '1:基础', NULL, '', '', '', NULL, 20, '', '', 0, 0, 0, 'MyISAM', 'admin', NULL);
INSERT INTO `wp_model` VALUES (2363, 'docker', '容器管理', 0, '', 1, NULL, '1:基础', NULL, '', '', '', NULL, 1, '', '', 0, 0, 0, 'MyISAM', 'docker', NULL);
INSERT INTO `wp_model` VALUES (2364, 'images', '镜像', 0, '', 1, NULL, '1:基础', NULL, '', '', '', NULL, 20, '', '', 0, 0, 0, 'MyISAM', 'docker', NULL);
INSERT INTO `wp_model` VALUES (2366, 'block', '块元素', 0, '', 1, NULL, '1:基础', NULL, '', '', '', NULL, 20, '', '', 0, 0, 0, 'MyISAM', 'core', NULL);
INSERT INTO `wp_model` VALUES (2370, 'site', '网站', 0, '', 1, NULL, '1:基础', NULL, '', '', '', NULL, 20, 'title:网站搜索', '', 0, 0, 0, 'MyISAM', 'admin', NULL);
INSERT INTO `wp_model` VALUES (2371, 'site_backup', '网站备份', 0, '', 1, NULL, '1:基础', NULL, '', '', '', NULL, 20, '', '', 0, 0, 0, 'MyISAM', 'admin', NULL);
INSERT INTO `wp_model` VALUES (2372, 'xterm', '终端历史操作', 0, '', 1, NULL, '1:基础', NULL, '', '', '', NULL, 200, '', '', 0, 0, 0, 'MyISAM', 'admin', NULL);
INSERT INTO `wp_model` VALUES (2373, 'database', '数据库', 0, '', 1, NULL, '1:基础', NULL, '', '', '', NULL, 20, 'title:数据库搜索', '', 0, 0, 0, 'MyISAM', 'admin', NULL);
INSERT INTO `wp_model` VALUES (2374, 'cron', '计划任务', 0, '', 1, NULL, '1:基础', NULL, '', '', '', NULL, 2000, '', '', 0, 0, 0, 'MyISAM', 'admin', NULL);
INSERT INTO `wp_model` VALUES (2375, 'soft', '应用商城', 0, '', 1, NULL, '1:基础', NULL, '', '', '', NULL, 20, '', '', 0, 0, 0, 'MyISAM', 'admin', NULL);
INSERT INTO `wp_model` VALUES (2379, 'install', '一键部署', 0, '', 1, NULL, '1:基础', NULL, '', '', '', NULL, 20, '', '', 0, 0, 0, 'MyISAM', 'admin', NULL);

DROP TABLE IF EXISTS `wp_notice`;
CREATE TABLE `wp_notice`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;


DROP TABLE IF EXISTS `wp_payment`;
CREATE TABLE `wp_payment`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `out_trade_no` char(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `total_fee` int(11) NOT NULL,
  `appid` char(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `openid` char(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `callback` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `prepay_id` char(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `code_url` char(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `return_code` char(16) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `return_msg` char(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `result_code` char(16) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `err_code_des` char(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `cTime` int(10) NOT NULL,
  `param` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
  `res_data` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
  `is_pay` tinyint(1) NULL DEFAULT 0,
  `after_pay_res` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
  `pbid` int(10) NOT NULL DEFAULT 0 COMMENT 'pbid',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `out_trade_no`(`out_trade_no`, `appid`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

DROP TABLE IF EXISTS `wp_payment_order`;
CREATE TABLE `wp_payment_order`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `from` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '回调地址',
  `orderName` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '订单名称',
  `single_orderid` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '订单号',
  `price` decimal(10, 2) NULL DEFAULT NULL COMMENT '价格',
  `wecha_id` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'OpenID',
  `paytype` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '支付方式',
  `showwxpaytitle` tinyint(2) NOT NULL DEFAULT 0 COMMENT '是否显示标题',
  `status` tinyint(2) NOT NULL DEFAULT 0 COMMENT '支付状态',
  `aim_id` int(10) NULL DEFAULT 0 COMMENT 'aim_id',
  `uid` int(10) NULL DEFAULT 0 COMMENT '用户uid',
  `wpid` int(10) NOT NULL DEFAULT 0 COMMENT 'wpid',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;


DROP TABLE IF EXISTS `wp_payment_scan`;
CREATE TABLE `wp_payment_scan`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `appid` char(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `callback` char(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `product_id` char(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `out_trade_no` char(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `total_fee` int(11) NULL DEFAULT NULL,
  `cTime` int(10) NOT NULL,
  `product` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `shorturl_res` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
  `order_param` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `order_data` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
  `order_res` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `appid_product_id`(`appid`, `product_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 14 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;
DROP TABLE IF EXISTS `wp_payment_set`;
CREATE TABLE `wp_payment_set`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `ctime` int(10) NULL DEFAULT 0 COMMENT '创建时间',
  `wxappid` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'AppID',
  `wxpaysignkey` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '支付密钥',
  `wxappsecret` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'AppSecret',
  `zfbname` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '帐号',
  `pid` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'PID',
  `key` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'KEY',
  `partnerid` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '财付通标识',
  `partnerkey` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '财付通Key',
  `wappartnerid` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '财付通标识WAP',
  `wappartnerkey` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'WAP财付通Key',
  `wxpartnerkey` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '微信partnerkey',
  `wxpartnerid` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '微信partnerid',
  `quick_security_key` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '银联在线Key',
  `quick_merid` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '银联在线merid',
  `quick_merabbr` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '商户名称',
  `wpid` int(10) NULL DEFAULT 0 COMMENT '商店ID',
  `wxmchid` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '微信支付商户号',
  `wx_cert_pem` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '上传证书',
  `wx_key_pem` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '上传密匙',
  `shop_pay_score` int(10) NULL DEFAULT 0 COMMENT '支付返积分',
  `deposit` int(10) NULL DEFAULT 10 COMMENT '支付定金百分比',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

DROP TABLE IF EXISTS `wp_phinxlog`;
CREATE TABLE `wp_phinxlog`  (
  `version` bigint(20) NOT NULL,
  `migration_name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `start_time` timestamp(0) NULL DEFAULT NULL,
  `end_time` timestamp(0) NULL DEFAULT NULL,
  `breakpoint` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`version`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;


DROP TABLE IF EXISTS `wp_picture`;
CREATE TABLE `wp_picture`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id自增',
  `path` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '路径',
  `url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '图片链接',
  `md5` char(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '文件md5',
  `sha1` char(40) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '文件 sha1编码',
  `status` tinyint(2) NOT NULL DEFAULT 0 COMMENT '状态',
  `create_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `system` tinyint(10) NULL DEFAULT 0,
  `wpid` int(10) NOT NULL DEFAULT 0 COMMENT 'wpid',
  `social_id` int(11) NULL DEFAULT 1,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `status`(`id`, `status`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 11939 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

DROP TABLE IF EXISTS `wp_plugin`;
CREATE TABLE `wp_plugin`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(40) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '插件名或标识',
  `title` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '中文名',
  `description` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '插件描述',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态',
  `config` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '配置',
  `author` varchar(40) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '作者',
  `version` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '版本号',
  `create_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '安装时间',
  `has_adminlist` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否有后台列表',
  `cate_id` int(11) NULL DEFAULT NULL,
  `is_show` tinyint(2) NULL DEFAULT 1,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `name`(`name`) USING BTREE,
  INDEX `sti`(`status`, `is_show`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 130 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '系统插件表' ROW_FORMAT = Dynamic;

DROP TABLE IF EXISTS `wp_public_auth`;
CREATE TABLE `wp_public_auth`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` char(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `title` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `type_0` tinyint(1) NULL DEFAULT 0 COMMENT '普通订阅号的开关',
  `type_1` tinyint(1) NULL DEFAULT 0 COMMENT '微信认证订阅号的开关',
  `type_2` tinyint(1) NULL DEFAULT 0 COMMENT '普通服务号的开关',
  `type_3` tinyint(1) NULL DEFAULT 0 COMMENT '微信认证服务号的开关',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 36 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;


DROP TABLE IF EXISTS `wp_public_check`;
CREATE TABLE `wp_public_check`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `na` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `msg` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `wpid` int(10) NOT NULL DEFAULT 0 COMMENT 'wpid',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 396 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;


DROP TABLE IF EXISTS `wp_public_config`;
CREATE TABLE `wp_public_config`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `pbid` int(11) NULL DEFAULT 0,
  `pkey` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '配置规则名',
  `pvalue` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '配置值',
  `mtime` int(10) NULL DEFAULT 0 COMMENT '设置时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 131 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

DROP TABLE IF EXISTS `wp_public_follow`;
CREATE TABLE `wp_public_follow`  (
  `openid` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `uid` int(11) NULL DEFAULT NULL,
  `has_subscribe` tinyint(1) NULL DEFAULT 0,
  `syc_status` tinyint(1) NULL DEFAULT 2 COMMENT '0 开始同步中 1 更新用户信息中 2 完成同步',
  `remark` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `unionid` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '微信第三方ID',
  `pbid` int(10) NOT NULL DEFAULT 0 COMMENT 'pbid',
  UNIQUE INDEX `openid`(`openid`, `pbid`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

DROP TABLE IF EXISTS `wp_publics`;
CREATE TABLE `wp_publics`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `uid` int(10) NULL DEFAULT 0 COMMENT '用户ID',
  `public_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '公众号名称',
  `public_id` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '公众号原始id',
  `interface_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '接口地址',
  `headface_url` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '公众号头像',
  `area` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '地区',
  `addon_status` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '插件状态',
  `is_use` tinyint(2) NULL DEFAULT 0 COMMENT '是否为当前公众号',
  `type` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT '公众号类型',
  `appid` char(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'AppID',
  `secret` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'AppSecret',
  `encodingaeskey` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'EncodingAESKey',
  `tips_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '提示关注公众号的文章地址',
  `domain` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '自定义域名',
  `is_bind` tinyint(2) NULL DEFAULT 0 COMMENT '是否为微信开放平台绑定账号',
  `mch_id` char(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '商户号',
  `partner_key` char(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '支付密钥',
  `cert_pem` int(11) NULL DEFAULT 0 COMMENT '证书cert',
  `key_pem` int(11) NULL DEFAULT 0 COMMENT '证书key',
  `authorizer_refresh_token` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'authorizer_refresh_token',
  `check_file` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '微信验证文件',
  `app_type` tinyint(2) NULL DEFAULT 0 COMMENT '公众号类型',
  `wpid` int(11) NULL DEFAULT 0 COMMENT 'wpid',
  `order_payok_messageid` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '交易完成通知的模板ID',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

INSERT INTO `wp_publics` VALUES (1, 1, '直播打卡挑战', 'gh_646280fabe21', NULL, NULL, NULL, NULL, 0, '1', 'wxbe8c175aa84aef9e', 'bca9cc806e370b7f5754c52a3ada5986', '', NULL, NULL, 0, '1488433962', 'bca9cc806e370b7f5754c52a3ada5986', NULL, NULL, NULL, NULL, 1, NULL, NULL);

DROP TABLE IF EXISTS `wp_qr_admin`;
CREATE TABLE `wp_qr_admin`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `action_name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '类型',
  `group_id` int(10) NULL DEFAULT 0 COMMENT '用户组',
  `tag_ids` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '用户标签',
  `qr_code` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '二维码',
  `material` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '扫码后的回复内容',
  `mult_pic` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '多图上传',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;
DROP TABLE IF EXISTS `wp_qr_code`;
CREATE TABLE `wp_qr_code`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `qr_code` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '二维码',
  `addon` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '二维码所属插件',
  `aim_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '插件表里的ID值',
  `cTime` int(10) NULL DEFAULT 0 COMMENT '创建时间',
  `action_name` char(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '二维码类型',
  `extra_text` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '文本扩展',
  `extra_int` int(10) NULL DEFAULT 0 COMMENT '数字扩展',
  `request_count` int(10) NULL DEFAULT 0 COMMENT '请求数',
  `scene_id` int(10) NULL DEFAULT 0 COMMENT '场景ID',
  `expire_seconds` int(11) NULL DEFAULT 2592000 COMMENT '有效期',
  `pbid` int(10) NOT NULL DEFAULT 0 COMMENT 'wpid',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;
DROP TABLE IF EXISTS `wp_request_log`;
CREATE TABLE `wp_request_log`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `md5` char(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '接口名称',
  `url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '序列化后的参数',
  `param` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
  `res` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
  `error_code` char(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0',
  `msg` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `server_ip` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '服务器IP地址',
  `cTime` int(10) NOT NULL COMMENT '记录时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;


DROP TABLE IF EXISTS `wp_site`;
CREATE TABLE `wp_site`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '网站名',
  `domain` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '域名',
  `path` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '网站目录',
  `database` char(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'not' COMMENT '数据库',
  `db_user` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '用户名',
  `db_passwd` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '密码',
  `db_set` char(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'utf8mb4' COMMENT '字符集',
  `php_version` char(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'php-72' COMMENT 'PHP版本',
  `public_path` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '运行目录',
  `ssl` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '证书',
  `backup_at` int(10) NULL DEFAULT 0 COMMENT '备份',
  `open_basedir` tinyint(2) NULL DEFAULT 1 COMMENT '防跨站攻击',
  `recode_log` tinyint(2) NULL DEFAULT 1 COMMENT '写访问日志',
  `rewrite_mod` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '伪静态模板',
  `ssl_open` tinyint(2) NULL DEFAULT 0 COMMENT 'SSL状态',
  `ssl_force` tinyint(2) NULL DEFAULT 0 COMMENT '强制HTTPS',
  `remark` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '备注',
  `status` tinyint(2) NULL DEFAULT 1 COMMENT '状态',
  `create_at` int(10) NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 31 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '网站' ROW_FORMAT = Dynamic;
DROP TABLE IF EXISTS `wp_site_backup`;
CREATE TABLE `wp_site_backup`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `backup_at` int(10) NULL DEFAULT 0 COMMENT '备份时间',
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '标题',
  `file_size` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT '文件大小',
  `site_id` int(10) NULL DEFAULT 0 COMMENT '网站ID',
  `type` tinyint(2) NULL DEFAULT 0 COMMENT '类型',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 65 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '网站备份' ROW_FORMAT = DYNAMIC;
DROP TABLE IF EXISTS `wp_sn_code`;
CREATE TABLE `wp_sn_code`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `sn` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'SN码',
  `uid` int(10) NULL DEFAULT 0 COMMENT '粉丝UID',
  `cTime` int(10) NULL DEFAULT 0 COMMENT '创建时间',
  `is_use` tinyint(2) NULL DEFAULT 0 COMMENT '是否已使用',
  `use_time` int(10) NULL DEFAULT 0 COMMENT '使用时间',
  `target_id` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '来源ID',
  `prize_id` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '奖项ID',
  `status` tinyint(2) NOT NULL DEFAULT 1 COMMENT '是否可用',
  `prize_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '奖项',
  `can_use` tinyint(2) NULL DEFAULT 1 COMMENT '是否可用',
  `server_addr` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '服务器IP',
  `admin_uid` int(10) NULL DEFAULT 0 COMMENT '核销管理员ID',
  `wpid` int(10) NOT NULL DEFAULT 0 COMMENT 'wpid',
  `openid` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'openid',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `id`(`uid`, `target_id`) USING BTREE,
  INDEX `addon`(`target_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;


DROP TABLE IF EXISTS `wp_soft`;
CREATE TABLE `wp_soft`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `title` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '软件名称',
  `dev` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '开发商',
  `intro` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '介绍',
  `status` tinyint(2) NULL DEFAULT 0 COMMENT '状态',
  `image` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '对应镜像',
  `docker` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '容器名',
  `can_edit` tinyint(2) NULL DEFAULT 0 COMMENT '可配置',
  `can_del` tinyint(2) NULL DEFAULT 1 COMMENT '可删除',
  `config_html` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '配置模板',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 24 CHARACTER SET = utf8 CHECKSUM = 1 COLLATE = utf8_general_ci COMMENT = '应用商城' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of wp_soft
-- ----------------------------
INSERT INTO `wp_soft` VALUES (1, 'php-7.2', '小韦云', 'PHP是世界上最好的语言', 1, 'bctos-php:7.2-fpm-alpine', 'php72', 1, 0, 'php');
INSERT INTO `wp_soft` VALUES (2, 'mysql-5.7', '官方', 'MYSQL是PHP界中的主流数据库', 1, 'mysql:5.7', 'mysql57', 1, 0, 'mysql');
INSERT INTO `wp_soft` VALUES (3, 'nginx', '官方', '高性能的http服务器', 1, 'nginx:1.19-alpine', 'nginx', 1, 0, 'nginx');
INSERT INTO `wp_soft` VALUES (6, 'php-8.0', '小韦云', 'PHP是世界上最好的语言', 1, 'bctos-php:8.0-rc-fpm-alpine', 'php80', 1, 0, 'php');
INSERT INTO `wp_soft` VALUES (7, 'php-7.4', '小韦云', 'PHP是世界上最好的语言', 1, 'bctos-php:7.4-fpm-alpine', 'php74', 1, 0, 'php');
INSERT INTO `wp_soft` VALUES (8, 'php-7.3', '小韦云', 'PHP是世界上最好的语言', 1, 'bctos-php:7.3-fpm-alpine', 'php73', 1, 0, 'php');
INSERT INTO `wp_soft` VALUES (9, 'php-7.1', '小韦云', 'PHP是世界上最好的语言', 1, 'bctos-php:7.1-fpm-alpine', 'php71', 1, 0, 'php');
INSERT INTO `wp_soft` VALUES (10, 'php-7.0', '小韦云', 'PHP是世界上最好的语言', 1, 'bctos-php:7.0-fpm-alpine', 'php70', 1, 0, 'php');
INSERT INTO `wp_soft` VALUES (11, 'php-5.6', '小韦云', 'PHP是世界上最好的语言', 1, 'bctos-php:5.6-fpm-alpine', 'php56', 1, 0, 'php');
INSERT INTO `wp_soft` VALUES (12, 'mysql-8.0', '官方', 'MYSQL是PHP界中的主流数据库', 1, 'mysql:8.0', 'mysql80', 1, 0, 'mysql');
INSERT INTO `wp_soft` VALUES (13, 'mysql-5.6', '官方', 'MYSQL是PHP界中的主流数据库', 1, 'mysql:5.6', 'mysql56', 1, 0, 'mysql');
INSERT INTO `wp_soft` VALUES (14, 'mysql-5.5', '官方', 'MYSQL是PHP界中的主流数据库', 1, 'mysql:5.5', 'mysql55', 1, 0, 'mysql');
INSERT INTO `wp_soft` VALUES (15, 'mysql-mariadb-10.4', '官方', 'MariaDB完全兼容MySQL，是MySQL的代替品', 1, 'mariadb:10.4', 'mysql-mariadb104', 1, 0, 'mysql');
INSERT INTO `wp_soft` VALUES (16, 'mysql-mariadb-10.3', '官方', 'MariaDB完全兼容MySQL，是MySQL的代替品', 1, 'mariadb:10.3', 'mysql-mariadb103', 1, 0, 'mysql');
INSERT INTO `wp_soft` VALUES (17, 'mysql-mariadb-10.2', '官方', 'MariaDB完全兼容MySQL，是MySQL的代替品', 1, 'mariadb:10.2', 'mysql-mariadb102', 1, 0, 'mysql');
INSERT INTO `wp_soft` VALUES (18, 'mysql-mariadb-10.1', '官方', 'MariaDB完全兼容MySQL，是MySQL的代替品', 1, 'mariadb:10.1', 'mysql-mariadb101', 1, 0, 'mysql');
INSERT INTO `wp_soft` VALUES (19, 'mysql-mariadb-10.0', '官方', 'MariaDB完全兼容MySQL，是MySQL的代替品', 1, 'mariadb:10.0', 'mysql-mariadb100', 1, 0, 'mysql');
INSERT INTO `wp_soft` VALUES (21, 'redis-5.0', '官方', '高性能的key-value数据库', 0, 'redis:5.0-alpine', 'redis50', 1, 1, NULL);
INSERT INTO `wp_soft` VALUES (22, 'redis-6.0', '官方', '高性能的key-value数据库', 0, 'redis:6.0-alpine', 'redis60', 1, 1, NULL);
INSERT INTO `wp_soft` VALUES (23, 'memcached-1.6', '官方', '分布式的高速缓存系统', 0, 'memcached:1.6-alpine', 'memcached16', 1, 1, NULL);

-- ----------------------------
-- Table structure for wp_system_notice
-- ----------------------------
DROP TABLE IF EXISTS `wp_system_notice`;
CREATE TABLE `wp_system_notice`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '公告标题',
  `content` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '公告内容',
  `create_time` int(10) NULL DEFAULT 0 COMMENT '发布时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 8 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

DROP TABLE IF EXISTS `wp_template_messages`;
CREATE TABLE `wp_template_messages`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `pbid` int(10) NULL DEFAULT 0 COMMENT '公众号id',
  `cTime` int(10) NULL DEFAULT 0 COMMENT '创建时间',
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '消息标题',
  `content` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '消息内容',
  `sender` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '发起人',
  `jamp_url` varchar(555) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '跳转url',
  `send_type` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT '发送方式',
  `send_openids` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '发送openid',
  `group_id` int(10) NULL DEFAULT 0 COMMENT '发送分组id',
  `send_count` int(10) NULL DEFAULT 0 COMMENT '发送人数',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;


DROP TABLE IF EXISTS `wp_transfers_recode`;
CREATE TABLE `wp_transfers_recode`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  ` mch_appid` char(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `openid` char(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `amount` int(11) NOT NULL,
  `partner_trade_no` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `cTime` int(10) NOT NULL,
  `status` tinyint(2) NULL DEFAULT 1 COMMENT '0 表示已下发 1 表示待下发 2 延时下发 3 下发失败 4 自定义失败原因',
  `wait_time` int(10) NULL DEFAULT NULL,
  `more_param` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
  `log_md5` char(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `remark` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `act_id` int(11) NULL DEFAULT NULL,
  `act_mod` char(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 26 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

DROP TABLE IF EXISTS `wp_update_version`;
CREATE TABLE `wp_update_version`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `version` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '版本号',
  `title` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '升级包名',
  `description` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '描述',
  `create_date` int(10) NULL DEFAULT 0 COMMENT '创建时间',
  `download_count` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '下载统计',
  `package` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '升级包地址',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;


DROP TABLE IF EXISTS `wp_user`;
CREATE TABLE `wp_user`  (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `nickname` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '用户名',
  `password` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '登录密码',
  `truename` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '真实姓名',
  `mobile` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '联系电话',
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '邮箱地址',
  `sex` tinyint(2) NULL DEFAULT 0 COMMENT '性别',
  `headimgurl` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '头像地址',
  `city` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '城市',
  `province` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '省份',
  `country` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '国家',
  `language` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '语言',
  `score` int(10) NULL DEFAULT 0 COMMENT '积分值',
  `unionid` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '微信第三方ID',
  `experience` int(10) NULL DEFAULT 0 COMMENT '经验值',
  `login_count` int(10) NULL DEFAULT 0 COMMENT '登录次数',
  `reg_ip` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '注册IP',
  `reg_time` int(10) NULL DEFAULT 0 COMMENT '注册时间',
  `last_login_ip` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '最近登录IP',
  `last_login_time` int(10) NULL DEFAULT 0 COMMENT '最近登录时间',
  `status` tinyint(2) NULL DEFAULT 1 COMMENT '状态',
  `is_init` tinyint(2) NULL DEFAULT 0 COMMENT '初始化状态',
  `is_audit` tinyint(2) NULL DEFAULT 0 COMMENT '审核状态',
  `subscribe_time` int(10) NULL DEFAULT 0 COMMENT '用户关注公众号时间',
  `remark` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '微信用户备注',
  `groupid` int(10) NULL DEFAULT 0 COMMENT '微信端的分组ID',
  `come_from` tinyint(1) NULL DEFAULT 0 COMMENT '来源',
  `login_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'login_name',
  `login_password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '登录密码',
  `manager_id` int(10) NULL DEFAULT 0 COMMENT '公众号管理员ID',
  `level` tinyint(2) NULL DEFAULT 0 COMMENT '-1:机器人0:粉丝1:超级管理员2:A级管理员\r\n3:B级管理员\r\n4:C级管理员',
  `membership` char(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT '会员等级',
  `bind_openid` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '绑定的openid',
  `audit_time` int(10) NULL DEFAULT 0 COMMENT '审核通过时间',
  `grade` int(10) NULL DEFAULT 0 COMMENT '当前用户的等级',
  `wpid` int(11) NULL DEFAULT 0,
  `tagid_list` varchar(555) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '用户被打上的标签ID列表',
  `in_blacklist` tinyint(2) NULL DEFAULT 0 COMMENT '是否拉黑',
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '详细地址',
  `intro` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '个性签名',
  `reward_money` float NULL DEFAULT 0 COMMENT '钱包',
  `salt` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'DZ密码加密KEY',
  `social_id` int(10) NULL DEFAULT 1 COMMENT '社区ID',
  `social_rule` int(10) NULL DEFAULT 0 COMMENT '社区角色',
  `weiba_id` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '吧主权限，版块id',
  `userid` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '加密用户标识',
  `industry` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '行业',
  `hobby` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '爱好',
  `birthday` int(10) NULL DEFAULT 0 COMMENT 'birthday',
  `area` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'area',
  `audit_reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '审核不通过的原因',
  `price` int(10) NULL DEFAULT 20 COMMENT '单价',
  `copyright` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '版权信息',
  PRIMARY KEY (`uid`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 53763 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

INSERT INTO `wp_user` VALUES (1, 'admin', '95f78b3dde0f5d3c4dca7f0e2ad7acf2', '超级管理员', '18123611365', 'admin@weiphp.cn', 1, 'https://wx.qlogo.cn/mmopen/vi_32/PiajxSqBRaEJFBe2dVib0WHhQy4oSoR6jJR4ytwtAS5iaTibL06SJwiaghzOfpsGkc67IIYk6xKvZ73LCsrukDHsVbA/132', 'Shenzhen', 'Guangdong', 'China', 'zh_CN', 87970, NULL, 0, 2072, '0', 1474905117, '3232235529', 1606375639, 1, 1, 1, 1532575436, '', 0, 0, 'admin', '123456', 0, 2, '0', 'o4jRyt2BdS79xCkWER1bDsBzRYCo', NULL, 0, 86, NULL, 0, 'Room 2103 Zhianshanwu Building', '666', 0, NULL, 1, 0, NULL, NULL, '3428,3429', '3426,3427', 1262361600, '浙江省 杭州市 上城区', NULL, 20, NULL);
DROP TABLE IF EXISTS `wp_visit_log`;
CREATE TABLE `wp_visit_log`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `wpid` int(10) NULL DEFAULT 0 COMMENT 'publicid',
  `module_name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'module_name',
  `controller_name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'controller_name',
  `action_name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'action_name',
  `uid` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT 'uid',
  `ip` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'ip',
  `brower` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'brower',
  `param` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT 'param',
  `referer` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'referer',
  `cTime` int(10) NULL DEFAULT 0 COMMENT '时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 70709 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;
DROP TABLE IF EXISTS `wp_weixin_log`;
CREATE TABLE `wp_weixin_log`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cTime` int(11) NULL DEFAULT NULL,
  `cTime_format` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `data` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
  `data_post` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;


DROP TABLE IF EXISTS `wp_weixin_message`;
CREATE TABLE `wp_weixin_message`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `ToUserName` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'Token',
  `FromUserName` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'OpenID',
  `CreateTime` int(10) NULL DEFAULT 0 COMMENT '创建时间',
  `MsgType` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '消息类型',
  `MsgId` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '消息ID',
  `Content` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '文本消息内容',
  `PicUrl` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '图片链接',
  `MediaId` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '多媒体文件ID',
  `Format` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '语音格式',
  `ThumbMediaId` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '缩略图的媒体id',
  `Title` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '消息标题',
  `Description` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '消息描述',
  `Url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'Url',
  `collect` tinyint(1) NULL DEFAULT 0 COMMENT '收藏状态',
  `deal` tinyint(1) NULL DEFAULT 0 COMMENT '处理状态',
  `is_read` tinyint(1) NULL DEFAULT 0 COMMENT '是否已读',
  `type` tinyint(1) NULL DEFAULT 0 COMMENT '消息分类',
  `is_material` int(10) NULL DEFAULT 0 COMMENT '设置为文本素材',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;


DROP TABLE IF EXISTS `wp_xterm`;
CREATE TABLE `wp_xterm`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `command` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '命令',
  `update_at` int(10) NULL DEFAULT 0 COMMENT '更新时间',
  `count` int(10) NULL DEFAULT 0 COMMENT '使用次数',
  `status` tinyint(2) NULL DEFAULT 1 COMMENT '点击后立即执行',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 17 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '终端历史操作' ROW_FORMAT = DYNAMIC;

INSERT INTO `wp_xterm` VALUES (2, 'll', 1604388903, 0, 0);
INSERT INTO `wp_xterm` VALUES (9, 'pwd && ll', 0, 0, 1);

SET FOREIGN_KEY_CHECKS = 1;
