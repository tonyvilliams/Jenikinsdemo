-- tiki_innodb.sql is run after tiki.sql if InnoDB is being installed
-- $Id: tiki_innodb.sql 46619 2013-07-10 22:36:54Z arildb $

-- Force Tiki fulltext search off, when InnoDB is run
insert into tiki_preferences (name, value) values ('feature_search_fulltext', 'n');
