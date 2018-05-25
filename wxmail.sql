/*
Navicat MySQL Data Transfer

Source Server         : 3数据库
Source Server Version : 50717
Source Host           : 39.108.245.11:3306
Source Database       : wxmail

Target Server Type    : MYSQL
Target Server Version : 50717
File Encoding         : 65001

Date: 2018-05-25 17:00:52
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for le_official_app
-- ----------------------------
DROP TABLE IF EXISTS `le_official_app`;
CREATE TABLE `le_official_app` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `appname` varchar(255) DEFAULT NULL,
  `appid` varchar(255) DEFAULT NULL,
  `appsecret` varchar(255) DEFAULT NULL,
  `token` varchar(100) DEFAULT NULL,
  `encodeKey` varchar(255) DEFAULT NULL,
  `access_token` varchar(500) DEFAULT NULL,
  `expire_time` int(11) DEFAULT NULL,
  `app_qrcode` varchar(255) DEFAULT NULL COMMENT '二维码',
  `status` int(11) DEFAULT '1',
  `template_id` varchar(255) DEFAULT NULL COMMENT '模板消息id',
  `fail_template_id` varchar(255) DEFAULT NULL COMMENT '助力失败模板id',
  `start_template_id` varchar(255) DEFAULT NULL COMMENT '活动开始模板id',
  `max_num` int(11) DEFAULT '1' COMMENT '最大用户数',
  `target_num` int(11) DEFAULT NULL COMMENT '任务目标人数',
  `type` int(11) DEFAULT '0' COMMENT '活动类型',
  `book_step` int(11) DEFAULT '10' COMMENT '完成一个人减少的书籍数量',
  `is_template` tinyint(1) DEFAULT '1' COMMENT '是否推送模板',
  `cat_id` int(11) DEFAULT '0' COMMENT '活动分类id',
  `rest_book` int(11) DEFAULT '0' COMMENT '剩余书籍数',
  `is_end` tinyint(1) DEFAULT '0' COMMENT '活动是否结束',
  `activity_name` varchar(255) DEFAULT NULL COMMENT '活动名称',
  `originer_id` varchar(100) DEFAULT NULL COMMENT '原始人id',
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL COMMENT '活动结束时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of le_official_app
-- ----------------------------
INSERT INTO `le_official_app` VALUES ('2', '快秒付', 'wx91c4f987c0adf005', '75fd9a454f05551e56def249ee3a9299', 'td02', null, null, null, null, '0', '6adGMe0jcmpiGfPR0Lhd9vfSzgGd4-0psN9-B6XivbI', null, null, '500', null, '0', null, '1', null, '0', '1', null, null, null, null);
INSERT INTO `le_official_app` VALUES ('1', '福利堡', 'wx546ec637c7491959', 'caed2284c49c2f604911ee8fac2db441', 'td03', null, null, null, null, '0', 'SCsMhIPigyvOanM09flVFZPtQwqyQEzRFJi-iALauPk', 'zrDwXdszH69ZtzuQMFpS1iwnT7Xb0w5UK9PaS0VfhYQ', null, '3000', '20', '1', '1', '1', '2', '0', '1', '亲子送书活动', '263', '2018-05-25 16:59:53', '2018-05-25 16:59:57');
INSERT INTO `le_official_app` VALUES ('7', '表白信', 'wx01d48b0e88d21274', '3ad1dec320823c6ee8a3ab00a305c511', 'td07', null, null, null, null, '1', null, null, null, '1', null, '0', '10', '1', '0', '0', '0', null, null, null, null);

-- ----------------------------
-- Table structure for le_others_letter
-- ----------------------------
DROP TABLE IF EXISTS `le_others_letter`;
CREATE TABLE `le_others_letter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `editor` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `create_time` int(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Records of le_others_letter
-- ----------------------------
INSERT INTO `le_others_letter` VALUES ('1', '所以，你想知道我喜欢的是谁咯？', '匿名人X', null);
INSERT INTO `le_others_letter` VALUES ('3', '其实也没什么可以说的，毕竟，这封信是给Ta写的。你是不是很想知道？？？', '匿名人X', null);
INSERT INTO `le_others_letter` VALUES ('4', '我有一堆情话，想在余生讲给喜欢的人听。但是这封信只有指定的人才能看到,是这样的吧？', '匿名人X', null);
INSERT INTO `le_others_letter` VALUES ('5', '不知道从什么时候开始，在什么东西上面都有个日期，秋刀鱼会过期，肉罐头会过期，连保鲜纸都会过期。我开始怀疑，不禁想问下你，我们的友谊会过期吗？', '匿名人X', null);
INSERT INTO `le_others_letter` VALUES ('6', '爱情这东西，时间很关键，认识得太早或太晚，都不行。这封信也是，你打开的时间错误！', '匿名人X', null);
INSERT INTO `le_others_letter` VALUES ('9', '哈哈哈哈，你是看不到的，为什么还要进来看啊,这么关心我喜欢谁？', '匿名人X', null);
INSERT INTO `le_others_letter` VALUES ('7', '说出来你可能不信，这段话是后台自动码上去的:)', '匿名人X', null);
INSERT INTO `le_others_letter` VALUES ('8', '这个东西挺有意思的......真的', '匿名人X', null);
INSERT INTO `le_others_letter` VALUES ('10', '想知道我喜欢谁，为什么不直接问我呢？虽然问了我也不一定回答。', '匿名人X', null);
INSERT INTO `le_others_letter` VALUES ('11', '我比较健忘，忘了想说什么了......', '匿名人X', null);
INSERT INTO `le_others_letter` VALUES ('12', '我知道你会这么做，看到这封信，就想知道信里面是什么。我很想告诉你，看到这封信，你会发觉没有什么特别，回头看又觉得很好。', '匿名人X', null);
INSERT INTO `le_others_letter` VALUES ('13', '当你年轻时，什么都想知道。可是老了的时候，你可能又觉得，有些东西不知道也无所谓。', '匿名人X', null);
INSERT INTO `le_others_letter` VALUES ('14', '我也没有想过。以前我只是想知道，爱情到底是怎么开始的。现在我知道了，很多事情不知不觉就来了。我还以为没什么，但是我开始担心Ta、关注Ta、了解Ta，我就知道了。所以，能不能帮我一个忙？帮我保存下秘密。', '匿名人X', null);

-- ----------------------------
-- Table structure for le_user
-- ----------------------------
DROP TABLE IF EXISTS `le_user`;
CREATE TABLE `le_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `openid` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `headimgurl` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nickname` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sex` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '用户性别',
  `create_time` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  `ip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '用户IP地址',
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '用户所在省-市',
  `source_openid` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '扫描的二维码的用户的推荐人openid',
  `source_user_letter_id` int(11) DEFAULT NULL COMMENT '所扫描二维码的id',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Records of le_user
-- ----------------------------
INSERT INTO `le_user` VALUES ('17', 'ohFHb0TSqAkrvjOtUW9Xx92GtqU0', 'http://thirdwx.qlogo.cn/mmopen/1LlgQzJVOyBXo11nGY18phMHgs2Z6MhvkaYhDeEoaC5qboXFrYqOqHU3UAYYvuXyqgquQv6lFBibAHtKH6Ovou0aibxsPibdvXJ/132', 'Reykjavík', '2', '1527228994', '1527229593', null, null, null, null);
INSERT INTO `le_user` VALUES ('8', 'ohFHb0RFOQQIsJnSLxeGm0wQ17P4', 'http://thirdwx.qlogo.cn/mmopen/Q3auHgzwzM6x1lKnDo84LBPuvPRHXBaysBXgLPSblNYOYN7gYxepg8BGdciaq3uY06MCoUO4hibkQRs2GrzdGFSSCETGRfIibZHY9EQMicibCYx4/132', 'Leopold', '1', '1526892603', '1527235779', null, null, null, null);
INSERT INTO `le_user` VALUES ('6', 'ohFHb0f1cvPx8R-uNFleqL_nzTmU', 'http://thirdwx.qlogo.cn/mmopen/1r7Eib7UicgYpKnDJkFPVBP85Tb1Y4zfwHfdb5vKzrdLOvXib4BDWDS3nltcN5ibibw80uggQrzNVk8E0SQUEnoTdVicT3Jym4atIX/132', '方圆Lite?', '0', '1526696270', '1527219501', null, null, null, null);
INSERT INTO `le_user` VALUES ('9', 'ohFHb0VgKnerL5cNlKGXyS3DOWz8', 'http://thirdwx.qlogo.cn/mmopen/kHDxgm8sUzoKVONMbJwdQOSyURNiaYibEciba3YAB9m7ZTkLzatWx4JZcranakGVl0Mozy3leraGgJ6EexY91JeBpdHFdHKibufK/132', '景行', '2', '1526975563', null, null, null, null, null);
INSERT INTO `le_user` VALUES ('10', 'ohFHb0ZX2NKZNTHsEVhqjJ8TezGs', 'http://thirdwx.qlogo.cn/mmopen/vyOlVyFvjZcbvnsSCtK6ibKic7ooHUAfVYOJEbrMvMMwt5eSUzd8PibEPQUpswk28Y4TYMZRV07ADQq8VKMlOPM0nu1AdmGeODL/132', '张老师', '2', '1527130692', null, null, null, null, null);
INSERT INTO `le_user` VALUES ('11', 'ohFHb0XGjCJI7-UCssOm_itYEIcQ', 'http://thirdwx.qlogo.cn/mmopen/1r7Eib7UicgYpKnDJkFPVBPxcdMkgPCqGZ7AuLgwicbgxiaBmCRZhSWibf2Ilm315Z0OhuFpBxedtXk7JOqHeU9l90o62UCeibx5tv/132', '王教授', '1', '1527145345', '1527152763', null, null, null, null);
INSERT INTO `le_user` VALUES ('12', 'ohFHb0QSiXliKk-33UMMvNybJ2gM', 'http://thirdwx.qlogo.cn/mmopen/vyOlVyFvjZcbvnsSCtK6ibI8G098ib7UnOrZy3a5VnTFVLr8tNKNgC9ejxfxIHV0akAVMOL4APibsVj16iag08VzLRARf6b5383y/132', '丘老师', '2', '1527146105', null, null, null, null, null);
INSERT INTO `le_user` VALUES ('13', 'ohFHb0XIvnfHG5y4kWPwU6GWKDe4', 'http://thirdwx.qlogo.cn/mmopen/Ohv7B10B2B2Uh65Y8icUO4q8yM3tuSk3Hkqatsvnfic5IakQueM91fszTRTTReea6lU4uSo2rnNlUjF5KAZicdR0uX1M9mCdtvN/132', '汪老师', '2', '1527152073', null, null, null, null, null);
INSERT INTO `le_user` VALUES ('14', 'ohFHb0dlHPDF4pVoETKyTVXMNs_Q', 'http://thirdwx.qlogo.cn/mmopen/Ohv7B10B2B04fSX7Pa46jKtz9nMia9PyECQfNbTamBYnlppficpatWUWNr9D7MKA1e7aLQGI1WjVFePt14vwanFg/132', '晓锋不懂你??', '1', '1527152202', null, null, null, null, null);
INSERT INTO `le_user` VALUES ('15', 'ohFHb0Zuzvui5VuK-X07Rgc_RAGc', 'http://thirdwx.qlogo.cn/mmopen/1r7Eib7UicgYpKnDJkFPVBP4cY0q0U9baOlPeCIByVgjR3W4NJ10P7x5OdYSZGUX5AMuu7Oz08mzco3t4iazR0ayXsEdiaE2GKc0/132', '麦霸?吴老师', '0', '1527219663', '1527219699', null, null, null, null);
INSERT INTO `le_user` VALUES ('16', 'ohFHb0WrSvoCwRSFWHmQAGj6Xf4o', 'http://thirdwx.qlogo.cn/mmopen/Ohv7B10B2B26ekw3qxly0CxSXkVjLQMSwfCDibqk3y9MIg8wkLZmEpfqLxpM0sGwWiarYxlRgJOb4CApxQgzvyhr7ib82syn6Cib/132', '崔群主', '2', '1527219816', '1527228449', null, null, null, null);
INSERT INTO `le_user` VALUES ('18', 'ohFHb0QZ8Je1KGCYUvThJaucLGM4', 'http://thirdwx.qlogo.cn/mmopen/vyOlVyFvjZfycAe5xu3CYtOiacv0V2hXNIWMyoXb2icM3AWLicM9LuGXNUObOfRe7Gbsh2iaYKz6suO6ZDg3bsAz4uqvmJuq02et/132', '夏', '0', '1527229021', null, null, null, null, null);
INSERT INTO `le_user` VALUES ('19', 'ohFHb0Yl4zfB7Mb53pEdy0dd7zfQ', 'http://thirdwx.qlogo.cn/mmopen/1r7Eib7UicgYoErpOKkX1mjrbuGicG4A5icLTlUAjgHT5miayc0kic4XFfuuV2ibx5HlynxrtHQmOib0vGpibSDUK8ae2AEwWCz7kpruR/132', 'sucy', '2', '1527229024', '1527229082', null, null, null, null);
INSERT INTO `le_user` VALUES ('20', 'ohFHb0enjgxQVa2BqSTHp4cNVlwI', 'http://thirdwx.qlogo.cn/mmopen/1LlgQzJVOyCcK2IBQynlFRtKDzWibVpVDGfbib4XpfZSqYAWYNRXqhEYBcq4xSib1b4ogcCOSOCfEU5F9cU8hE8ynlt74ibWeQ6B/132', '小新', '2', '1527229260', '1527229396', null, null, null, null);
INSERT INTO `le_user` VALUES ('21', 'ohFHb0WpzjWrUses-w2pENLu13SY', 'http://thirdwx.qlogo.cn/mmopen/1r7Eib7UicgYoErpOKkX1mjgst8kvmsmd72unVReZCpszoVRhTDWY7weS6GbM69hnA5gRmicykJIPQTC8ccDv1dMuYtYkroQic7a/132', '白老师', '1', '1527229314', null, null, null, null, null);
INSERT INTO `le_user` VALUES ('22', 'ohFHb0Vj1QoA_54MTpg9kMb3-_rI', 'http://thirdwx.qlogo.cn/mmopen/Ohv7B10B2B2xkkEBiatvw5Gd8rSrUmt7XP284d9BapCu3ZM6Msn4MrDB0aUBkcmIXNhZBUf8N0D6SBAYia7rjk9htdVNUOuiboJ/132', 'CL', '1', '1527231958', null, null, null, null, null);
INSERT INTO `le_user` VALUES ('23', 'ohFHb0XoLpRA5Fv97ahEar0Q1FEs', 'http://thirdwx.qlogo.cn/mmopen/Q3auHgzwzM62o4EVQCtvUs1UDmMP8GZvsSjoydJTjkBqicxyTEe1KlHnKsyQLIWEVkO93oxFkYOgq3gEMDBVmTG9xS4cPACSCmqhBuEA0ibYg/132', '请回答957', '2', '1527233528', '1527234734', null, null, null, null);
INSERT INTO `le_user` VALUES ('24', 'ohFHb0U2ujk317OELxwSP1tqyqMk', 'http://thirdwx.qlogo.cn/mmopen/1r7Eib7UicgYqHQddMibDr1poJMkUMUOXcGajMIGja9r0oCm8ZibagibW4pQ5drLHrswtC8ckTrltWkic3ATkyeSD4sjFXmlU7s3mia/132', '阿璐璐-', '2', '1527233547', '1527233581', null, null, null, null);
INSERT INTO `le_user` VALUES ('25', 'ohFHb0Vpp3APbSv2dWDpwOJGbuTs', 'http://thirdwx.qlogo.cn/mmopen/vyOlVyFvjZfycAe5xu3CYrfEiaTkVtb2s9zLkotic8LcdAIY6iaRLfdwtRwdPOhW6BGHWA77AEiaV4Oibkpm4VWCB62QTSX8z138b/132', '贝贝乖', '2', '1527233769', null, null, null, null, null);
INSERT INTO `le_user` VALUES ('26', 'ohFHb0TKLZUtu9lXOb7VrHT46C1Y', 'http://thirdwx.qlogo.cn/mmopen/Ohv7B10B2B1C7bAYQkTaRiau1Le8ObsdW6LowAv6mw0rLvTpjtZ5IVnGWqCam2Rntaic5Kk2TsL3aicEMBXaFsOsJa3ExQBhZQT/132', '橘子粥头', '0', '1527234148', null, null, null, null, null);
INSERT INTO `le_user` VALUES ('27', 'ohFHb0fx4ceAZwLFxLFKLyp2vRrI', 'http://thirdwx.qlogo.cn/mmopen/1LlgQzJVOyBXo11nGY18pssBMJqoLo40Bb02n93FL1nx6MBZ7XZWuGgNwnx6SFnSUVTvqT2ZfgQ6iauhY3icndqDmujdjH7A60/132', 'XD', '2', '1527234152', '1527234171', null, null, null, null);
INSERT INTO `le_user` VALUES ('28', 'ohFHb0V-mh_s1IDdjkHVIXRGBF_0', 'http://thirdwx.qlogo.cn/mmopen/1r7Eib7UicgYoErpOKkX1mjvRmPBM7HiaoT6zkLFicKLvIpUOf0FZZYZ3DlesHQFqqBw4gyiaxplxNUvKyUI2mrEwdAkrtfs8Giay7/132', '圣诞岛', '1', '1527234203', null, null, null, null, null);
INSERT INTO `le_user` VALUES ('29', 'ohFHb0dgV-Uma42y9Jq1B4G1v6l4', 'http://thirdwx.qlogo.cn/mmopen/vyOlVyFvjZfycAe5xu3CYjLlVBlumGMibvzQrJan583V2OOeQibKhzAnAKDg5NYklyXTA7OEBlicT61qNVmnQUmR7hicXLG5rBY1/132', '尼加拉瓜', '2', '1527234548', null, null, null, null, null);
INSERT INTO `le_user` VALUES ('30', 'ohFHb0TA-gEB2kDXNm4NroHApjUA', 'http://thirdwx.qlogo.cn/mmopen/vyOlVyFvjZdRGj07Z7f68ibKIbVzyxXDfKt1K6BvtYk8LEXjYk65SgTZBjolFVkyXXYDFpHsfz5ywd4HLAyGd3mwR5MOtBEC7/132', '小鱼', '1', '1527235167', null, null, null, null, null);
INSERT INTO `le_user` VALUES ('31', 'ohFHb0S3F8xaPlPYLNHJ1J-U44hA', 'http://thirdwx.qlogo.cn/mmopen/vyOlVyFvjZcbvnsSCtK6ibDvG1iaVAm3ibRbE56AdI2WG46UtscbZHEaKibMNcPicRPLaQFqibwIaOs5CtoquOvvYkqGHcTJopgwpM/132', '窓付き', '2', '1527235198', null, null, null, null, null);
INSERT INTO `le_user` VALUES ('32', 'ohFHb0eq47uQi3aByVDsvuZzGwwg', 'http://thirdwx.qlogo.cn/mmopen/1r7Eib7UicgYpKnDJkFPVBP1WAFvkHTIBPqXCdmNqFBo3IpgmHEHj2iaIjiaH1EuiaePsVUMElsWLr9318V9Rmd7icFnaL6M89AZZM/132', '包老师', '2', '1527235533', null, null, null, null, null);
INSERT INTO `le_user` VALUES ('33', 'ohFHb0SvQqKN5_0niz06qsBra5lA', 'http://thirdwx.qlogo.cn/mmopen/vyOlVyFvjZe1RPWt9toea7zibI6FzWG4MJ2jauiazl8nx4mx7v6jdicqhMJNhc1vib3puNlDsWSPNaPBmAOOD8NjRPaHu9g05ZSk/132', '向老师', '2', '1527235560', '1527237087', null, null, null, null);
INSERT INTO `le_user` VALUES ('34', 'ohFHb0TWg3Dd8FvyIMhDlP7jhWW4', 'http://thirdwx.qlogo.cn/mmopen/1r7Eib7UicgYoErpOKkX1mjpYUQA1IUaCp4gKZfhNfTRZOMH6njEabpicLRSsGDCiaWxz80bPactWnWuqDXGWNm3OiaadcNmmMR1K/132', '阿Q', '2', '1527235650', null, null, null, null, null);
INSERT INTO `le_user` VALUES ('35', 'ohFHb0clNq8wWLSfehlwOsxG8J2w', 'http://thirdwx.qlogo.cn/mmopen/1r7Eib7UicgYqoFueicFAcMTToMpNEksRuI2icniaBIgiby1WLmsCOMxF25Eibmm4zhfqutYRojbGA6W01KUibGlrgtOgPWJavH1QRld/132', 'PINk', '2', '1527237479', null, null, null, null, null);

-- ----------------------------
-- Table structure for le_user_letter
-- ----------------------------
DROP TABLE IF EXISTS `le_user_letter`;
CREATE TABLE `le_user_letter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `openid` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nickname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `letter` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '信件内容',
  `others_letter` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `accepter_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '接收者姓名',
  `poster_img_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '生成海报的图片地址',
  `create_time` int(20) DEFAULT NULL,
  `user_qr` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `headimgurl` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=206 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Records of le_user_letter
-- ----------------------------
INSERT INTO `le_user_letter` VALUES ('185', '8', 'ohFHb0RFOQQIsJnSLxeGm0wQ17P4', 'Leopold', '哦，你就是那个我喜欢的人', '你是看不到的', 'Leopold', 'h5/letter/img/wx_head-ohFHb0RFOQQIsJnSLxeGm0wQ17P4-1527231860-91.jpg', '1527231861', 'h5/letter/user_img/20180525/1527231862.jpeg', 'http://thirdwx.qlogo.cn/mmopen/Q3auHgzwzM6x1lKnDo84LBPuvPRHXBaysBXgLPSblNYOYN7gYxepg8BGdciaq3uY06MCoUO4hibkQRs2GrzdGFSSCETGRfIibZHY9EQMicibCYx4/132');
INSERT INTO `le_user_letter` VALUES ('186', '22', 'ohFHb0Vj1QoA_54MTpg9kMb3-_rI', 'CL', '谢恩来傻逼', '谢恩来智障', 'x625253252', 'h5/letter/img/wx_head-ohFHb0Vj1QoA_54MTpg9kMb3-_rI-1527232013-69.jpg', '1527232013', 'h5/letter/user_img/20180525/1527232016.jpeg', 'http://thirdwx.qlogo.cn/mmopen/Ohv7B10B2B2xkkEBiatvw5Gd8rSrUmt7XP284d9BapCu3ZM6Msn4MrDB0aUBkcmIXNhZBUf8N0D6SBAYia7rjk9htdVNUOuiboJ/132');
INSERT INTO `le_user_letter` VALUES ('187', '8', 'ohFHb0RFOQQIsJnSLxeGm0wQ17P4', 'Leopold', '你是猪', '东华帝君', 'LC', 'h5/letter/img/wx_head-ohFHb0RFOQQIsJnSLxeGm0wQ17P4-1527232281-42.jpg', '1527232281', 'h5/letter/user_img/20180525/1527232283.jpeg', 'http://thirdwx.qlogo.cn/mmopen/Q3auHgzwzM6x1lKnDo84LBPuvPRHXBaysBXgLPSblNYOYN7gYxepg8BGdciaq3uY06MCoUO4hibkQRs2GrzdGFSSCETGRfIibZHY9EQMicibCYx4/132');
INSERT INTO `le_user_letter` VALUES ('188', '23', 'ohFHb0XoLpRA5Fv97ahEar0Q1FEs', '请回答957', 'NM$L', '看你妈呢', 'Leopold', 'h5/letter/img/wx_head-ohFHb0XoLpRA5Fv97ahEar0Q1FEs-1527233575-34.jpg', '1527233575', 'h5/letter/user_img/20180525/1527233577.jpeg', 'http://thirdwx.qlogo.cn/mmopen/Q3auHgzwzM62o4EVQCtvUs1UDmMP8GZvsSjoydJTjkBqicxyTEe1KlHnKsyQLIWEVkO93oxFkYOgq3gEMDBVmTG9xS4cPACSCmqhBuEA0ibYg/132');
INSERT INTO `le_user_letter` VALUES ('189', '24', 'ohFHb0U2ujk317OELxwSP1tqyqMk', '阿璐璐-', '啊啊啊啊', '啊啊啊啊啊', '啊啊啊啊', 'h5/letter/img/wx_head-ohFHb0U2ujk317OELxwSP1tqyqMk-1527233775-54.jpg', '1527233775', 'h5/letter/user_img/20180525/1527233777.jpeg', 'http://thirdwx.qlogo.cn/mmopen/1r7Eib7UicgYqHQddMibDr1poJMkUMUOXcGajMIGja9r0oCm8ZibagibW4pQ5drLHrswtC8ckTrltWkic3ATkyeSD4sjFXmlU7s3mia/132');
INSERT INTO `le_user_letter` VALUES ('190', '25', 'ohFHb0Vpp3APbSv2dWDpwOJGbuTs', '贝贝乖', '看看逼', '老公在吗？速速微我520', 'Leopold', 'h5/letter/img/wx_head-ohFHb0Vpp3APbSv2dWDpwOJGbuTs-1527233932-22.jpg', '1527233932', 'h5/letter/user_img/20180525/1527233935.jpeg', 'http://thirdwx.qlogo.cn/mmopen/vyOlVyFvjZfycAe5xu3CYrfEiaTkVtb2s9zLkotic8LcdAIY6iaRLfdwtRwdPOhW6BGHWA77AEiaV4Oibkpm4VWCB62QTSX8z138b/132');
INSERT INTO `le_user_letter` VALUES ('191', '25', 'ohFHb0Vpp3APbSv2dWDpwOJGbuTs', '贝贝乖', '看看逼', '老公在吗？速速微我50', '请回答957', 'h5/letter/img/wx_head-ohFHb0Vpp3APbSv2dWDpwOJGbuTs-1527234338-82.jpg', '1527234338', 'h5/letter/user_img/20180525/1527234340.jpeg', 'http://thirdwx.qlogo.cn/mmopen/vyOlVyFvjZfycAe5xu3CYrfEiaTkVtb2s9zLkotic8LcdAIY6iaRLfdwtRwdPOhW6BGHWA77AEiaV4Oibkpm4VWCB62QTSX8z138b/132');
INSERT INTO `le_user_letter` VALUES ('192', '23', 'ohFHb0XoLpRA5Fv97ahEar0Q1FEs', '请回答957', '看看逼', '看你妈呢', '贝贝乖', 'h5/letter/img/wx_head-ohFHb0XoLpRA5Fv97ahEar0Q1FEs-1527234471-92.jpg', '1527234471', 'h5/letter/user_img/20180525/1527234472.jpeg', 'http://thirdwx.qlogo.cn/mmopen/Q3auHgzwzM62o4EVQCtvUs1UDmMP8GZvsSjoydJTjkBqicxyTEe1KlHnKsyQLIWEVkO93oxFkYOgq3gEMDBVmTG9xS4cPACSCmqhBuEA0ibYg/132');
INSERT INTO `le_user_letter` VALUES ('193', '25', 'ohFHb0Vpp3APbSv2dWDpwOJGbuTs', '贝贝乖', '看看逼', '', '尼加拉瓜', 'h5/letter/img/wx_head-ohFHb0Vpp3APbSv2dWDpwOJGbuTs-1527234521-93.jpg', '1527234521', 'h5/letter/user_img/20180525/1527234524.jpeg', 'http://thirdwx.qlogo.cn/mmopen/vyOlVyFvjZfycAe5xu3CYrfEiaTkVtb2s9zLkotic8LcdAIY6iaRLfdwtRwdPOhW6BGHWA77AEiaV4Oibkpm4VWCB62QTSX8z138b/132');
INSERT INTO `le_user_letter` VALUES ('194', '25', 'ohFHb0Vpp3APbSv2dWDpwOJGbuTs', '贝贝乖', '看看逼', '', 'XD', 'h5/letter/img/wx_head-ohFHb0Vpp3APbSv2dWDpwOJGbuTs-1527234578-69.jpg', '1527234578', 'h5/letter/user_img/20180525/1527234580.jpeg', 'http://thirdwx.qlogo.cn/mmopen/vyOlVyFvjZfycAe5xu3CYrfEiaTkVtb2s9zLkotic8LcdAIY6iaRLfdwtRwdPOhW6BGHWA77AEiaV4Oibkpm4VWCB62QTSX8z138b/132');
INSERT INTO `le_user_letter` VALUES ('195', '25', 'ohFHb0Vpp3APbSv2dWDpwOJGbuTs', '贝贝乖', '老公在吗？速速微我520可看看逼', '', '毛毛仔', 'h5/letter/img/wx_head-ohFHb0Vpp3APbSv2dWDpwOJGbuTs-1527234684-13.jpg', '1527234684', 'h5/letter/user_img/20180525/1527234686.jpeg', 'http://thirdwx.qlogo.cn/mmopen/vyOlVyFvjZfycAe5xu3CYrfEiaTkVtb2s9zLkotic8LcdAIY6iaRLfdwtRwdPOhW6BGHWA77AEiaV4Oibkpm4VWCB62QTSX8z138b/132');
INSERT INTO `le_user_letter` VALUES ('196', '29', 'ohFHb0dgV-Uma42y9Jq1B4G1v6l4', '尼加拉瓜', '我看你他妈是癞蛤蟆跳悬崖想装蝙蝠侠', '', '贝贝乖', 'h5/letter/img/wx_head-ohFHb0dgV-Uma42y9Jq1B4G1v6l4-1527234701-47.jpg', '1527234701', 'h5/letter/user_img/20180525/1527234703.jpeg', 'http://thirdwx.qlogo.cn/mmopen/vyOlVyFvjZfycAe5xu3CYjLlVBlumGMibvzQrJan583V2OOeQibKhzAnAKDg5NYklyXTA7OEBlicT61qNVmnQUmR7hicXLG5rBY1/132');
INSERT INTO `le_user_letter` VALUES ('197', '25', 'ohFHb0Vpp3APbSv2dWDpwOJGbuTs', '贝贝乖', '看看逼', '', '窓付き', 'h5/letter/img/wx_head-ohFHb0Vpp3APbSv2dWDpwOJGbuTs-1527234734-64.jpg', '1527234734', 'h5/letter/user_img/20180525/1527234736.jpeg', 'http://thirdwx.qlogo.cn/mmopen/vyOlVyFvjZfycAe5xu3CYrfEiaTkVtb2s9zLkotic8LcdAIY6iaRLfdwtRwdPOhW6BGHWA77AEiaV4Oibkpm4VWCB62QTSX8z138b/132');
INSERT INTO `le_user_letter` VALUES ('198', '25', 'ohFHb0Vpp3APbSv2dWDpwOJGbuTs', '贝贝乖', '在吗？速速微我50呢', '', '阿Q', 'h5/letter/img/wx_head-ohFHb0Vpp3APbSv2dWDpwOJGbuTs-1527234847-27.jpg', '1527234847', 'h5/letter/user_img/20180525/1527234849.jpeg', 'http://thirdwx.qlogo.cn/mmopen/vyOlVyFvjZfycAe5xu3CYrfEiaTkVtb2s9zLkotic8LcdAIY6iaRLfdwtRwdPOhW6BGHWA77AEiaV4Oibkpm4VWCB62QTSX8z138b/132');
INSERT INTO `le_user_letter` VALUES ('199', '25', 'ohFHb0Vpp3APbSv2dWDpwOJGbuTs', '贝贝乖', '看看逼', '', '小鱼', 'h5/letter/img/wx_head-ohFHb0Vpp3APbSv2dWDpwOJGbuTs-1527234896-63.jpg', '1527234896', 'h5/letter/user_img/20180525/1527234898.jpeg', 'http://thirdwx.qlogo.cn/mmopen/vyOlVyFvjZfycAe5xu3CYrfEiaTkVtb2s9zLkotic8LcdAIY6iaRLfdwtRwdPOhW6BGHWA77AEiaV4Oibkpm4VWCB62QTSX8z138b/132');
INSERT INTO `le_user_letter` VALUES ('200', '25', 'ohFHb0Vpp3APbSv2dWDpwOJGbuTs', '贝贝乖', '看看逼呢', '', '颖火虫.', 'h5/letter/img/wx_head-ohFHb0Vpp3APbSv2dWDpwOJGbuTs-1527234940-99.jpg', '1527234940', 'h5/letter/user_img/20180525/1527234942.jpeg', 'http://thirdwx.qlogo.cn/mmopen/vyOlVyFvjZfycAe5xu3CYrfEiaTkVtb2s9zLkotic8LcdAIY6iaRLfdwtRwdPOhW6BGHWA77AEiaV4Oibkpm4VWCB62QTSX8z138b/132');
INSERT INTO `le_user_letter` VALUES ('201', '25', 'ohFHb0Vpp3APbSv2dWDpwOJGbuTs', '贝贝乖', '在吗？看看逼呢', '', 'PINk', 'h5/letter/img/wx_head-ohFHb0Vpp3APbSv2dWDpwOJGbuTs-1527235023-91.jpg', '1527235023', null, 'http://thirdwx.qlogo.cn/mmopen/vyOlVyFvjZfycAe5xu3CYrfEiaTkVtb2s9zLkotic8LcdAIY6iaRLfdwtRwdPOhW6BGHWA77AEiaV4Oibkpm4VWCB62QTSX8z138b/132');
INSERT INTO `le_user_letter` VALUES ('202', '25', 'ohFHb0Vpp3APbSv2dWDpwOJGbuTs', '贝贝乖', '在吗？看看逼呢', '', 'PINk', 'h5/letter/img/wx_head-ohFHb0Vpp3APbSv2dWDpwOJGbuTs-1527235029-58.jpg', '1527235029', 'h5/letter/user_img/20180525/1527235032.jpeg', 'http://www.mukzz.pw/tpwx/h5/letter/img/wx_head-ohFHb0Vpp3APbSv2dWDpwOJGbuTs-1527235023-91.jpg');
INSERT INTO `le_user_letter` VALUES ('203', '25', 'ohFHb0Vpp3APbSv2dWDpwOJGbuTs', '贝贝乖', '老公在吗？速速微我一个任天堂的钱呢', '', '巴图', 'h5/letter/img/wx_head-ohFHb0Vpp3APbSv2dWDpwOJGbuTs-1527235118-14.jpg', '1527235118', 'h5/letter/user_img/20180525/1527235120.jpeg', 'http://thirdwx.qlogo.cn/mmopen/vyOlVyFvjZfycAe5xu3CYrfEiaTkVtb2s9zLkotic8LcdAIY6iaRLfdwtRwdPOhW6BGHWA77AEiaV4Oibkpm4VWCB62QTSX8z138b/132');
INSERT INTO `le_user_letter` VALUES ('204', '33', 'ohFHb0SvQqKN5_0niz06qsBra5lA', '向老师', '愿你是我，不期而遇的喜欢', '呵呵', 'Le', 'h5/letter/img/wx_head-ohFHb0SvQqKN5_0niz06qsBra5lA-1527235690-44.jpg', '1527235690', 'h5/letter/user_img/20180525/1527235692.jpeg', 'http://thirdwx.qlogo.cn/mmopen/vyOlVyFvjZe1RPWt9toea7zibI6FzWG4MJ2jauiazl8nx4mx7v6jdicqhMJNhc1vib3puNlDsWSPNaPBmAOOD8NjRPaHu9g05ZSk/132');
INSERT INTO `le_user_letter` VALUES ('205', '32', 'ohFHb0eq47uQi3aByVDsvuZzGwwg', '包老师', '愿你是我,不期而遇的喜欢', '放假放假就', 'LeLeopolderer', 'h5/letter/img/wx_head-ohFHb0eq47uQi3aByVDsvuZzGwwg-1527235749-95.jpg', '1527235749', 'h5/letter/user_img/20180525/1527235751.jpeg', 'http://thirdwx.qlogo.cn/mmopen/1r7Eib7UicgYpKnDJkFPVBP1WAFvkHTIBPqXCdmNqFBo3IpgmHEHj2iaIjiaH1EuiaePsVUMElsWLr9318V9Rmd7icFnaL6M89AZZM/132');
