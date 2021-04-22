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
    faildetected int(11) unsigned DEFAULT '0' NOT NULL,
    tx_mklib_lastrun datetime DEFAULT '0000-00-00 00:00:00',
);
