#
# Table structure for table 'tx_mklib_wordlist'
#
CREATE TABLE tx_mklib_wordlist (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    hidden tinyint(4) DEFAULT '0' NOT NULL,
    starttime int(11) DEFAULT '0' NOT NULL,
    endtime int(11) DEFAULT '0' NOT NULL,
    fe_group int(11) DEFAULT '0' NOT NULL,

    blacklisted tinyint(4) DEFAULT '0' NOT NULL,
    whitelisted tinyint(4) DEFAULT '0' NOT NULL,
    word varchar(255) DEFAULT '' NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid),
    KEY blacklisted (blacklisted),
    KEY whitelisted (whitelisted)
);

#
# Table structure for table 'static_countries'
#
CREATE TABLE static_countries (
	### zip rule fields
	zipcode_rule tinyint(3) DEFAULT '0' NOT NULL,
	zipcode_length tinyint(3) DEFAULT '0' NOT NULL
);

#
# Table structure for table 'tx_scheduler_task'
#
CREATE TABLE tx_scheduler_task (
	freezedetected int(11) unsigned DEFAULT '0' NOT NULL,
	tx_mklib_lastrun datetime DEFAULT '0000-00-00 00:00:00',
);
