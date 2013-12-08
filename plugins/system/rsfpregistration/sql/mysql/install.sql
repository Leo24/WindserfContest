INSERT IGNORE INTO `#__rsform_config` (`SettingName`, `SettingValue`) VALUES('registration_form', '0');
INSERT IGNORE INTO `#__rsform_config` (`SettingName`, `SettingValue`) VALUES('redirect_url', '');

CREATE TABLE IF NOT EXISTS `#__rsform_registration` (
  `form_id` int(11) NOT NULL,
  `reg_merge_vars` text NOT NULL,
  `activation` tinyint(1) NOT NULL,
  `cbactivation` tinyint(1) NOT NULL,
  `published` tinyint(1) NOT NULL,
  PRIMARY KEY (`form_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;