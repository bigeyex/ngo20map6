/*
Navicat MySQL Data Transfer

Source Server         : mysql
Source Server Version : 50527
Source Host           : localhost:3306
Source Database       : zpractice

Target Server Type    : MYSQL
Target Server Version : 50527
File Encoding         : 65001

Date: 2013-03-16 12:06:05
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `user`
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `user_type` smallint(6) NOT NULL,
  `user_name` varchar(10) DEFAULT NULL,
  `user_password` varchar(10) NOT NULL,
  `user_loginname` varchar(10) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of user
-- ----------------------------

-- ----------------------------
-- Table structure for `class`
-- ----------------------------
DROP TABLE IF EXISTS `class`;
CREATE TABLE `class` (
  `class_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  PRIMARY KEY (`class_id`),
  KEY `p_key1` (`teacher_id`),
  CONSTRAINT `p_key1` FOREIGN KEY (`teacher_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of class
-- ----------------------------

-- ----------------------------
-- Table structure for `test`
-- ----------------------------
DROP TABLE IF EXISTS `test`;
CREATE TABLE `test` (
  `test_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `test_name` int(11) NOT NULL,
  PRIMARY KEY (`test_id`),
  KEY `p_key2` (`class_id`),
  CONSTRAINT `p_key2` FOREIGN KEY (`class_id`) REFERENCES `class` (`class_id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of test
-- ----------------------------

-- ----------------------------
-- Table structure for `question`
-- ----------------------------
DROP TABLE IF EXISTS `question`;
CREATE TABLE `question` (
  `question_id` int(11) NOT NULL,
  `test_id` int(11) NOT NULL,
  `question_type` smallint(2) NOT NULL COMMENT 'qusetion_type//0代表单选题，1代表多选题，2代表简答题',
  `question_content` varchar(100) NOT NULL,
  `question_value` int(4) NOT NULL DEFAULT '0' COMMENT '代表此题的分值，默认为0',
  `question_answer` varchar(50) DEFAULT NULL COMMENT 'qusetion_answer//针对简答题，级即question_type为2时候，这个项不为空;\r\nanswer_id//针对单选题和多选题，进行选择的，寻找答案库；',
  `question_checkbox1` smallint(2) DEFAULT NULL COMMENT '数字n，代表第n个选项；四个checkbox都为null，则此题为简答题',
  `question_checkbox2` smallint(2) DEFAULT NULL,
  `question_checkbox3` smallint(2) DEFAULT NULL,
  `question_checkbox4` smallint(2) DEFAULT NULL,
  PRIMARY KEY (`question_id`),
  KEY `p_key4` (`test_id`),
  CONSTRAINT `p_key4` FOREIGN KEY (`test_id`) REFERENCES `test` (`test_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of question
-- ----------------------------


-- ----------------------------
-- Table structure for `options`
-- ----------------------------
DROP TABLE IF EXISTS `options`;
CREATE TABLE `options` (
  `option_id` int(11) NOT NULL,
  `option_serial` smallint(2) NOT NULL,
  `question_id` int(11) NOT NULL,
  `option_content` varchar(50) NOT NULL,
  PRIMARY KEY (`option_id`),
  KEY `p_key3` (`question_id`),
  CONSTRAINT `p_key3` FOREIGN KEY (`question_id`) REFERENCES `question` (`question_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of options
-- ----------------------------

-- ----------------------------
-- Table structure for `answer`
-- ----------------------------
DROP TABLE IF EXISTS `answer`;
CREATE TABLE `answer` (
  `stu_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `checkbox1` smallint(2) DEFAULT NULL,
  `checkbox2` smallint(2) DEFAULT NULL,
  `checkbox3` smallint(2) DEFAULT NULL,
  `checkbox4` smallint(2) DEFAULT NULL,
  `checkbox5` smallint(2) DEFAULT NULL,
  `answer_short` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`question_id`,`stu_id`),
  KEY `p_key5` (`stu_id`),
  CONSTRAINT `p_key5` FOREIGN KEY (`stu_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `P_key6` FOREIGN KEY (`question_id`) REFERENCES `question` (`question_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of answer
-- ----------------------------
