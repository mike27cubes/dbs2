--
-- manyqueries #1 test data
--

-- {{{ indv.abc

ALTER TABLE `DbSmart2_Tests` ADD `flag` TINYINT ( 1 ) UNSIGNED NOT NULL DEFAULT 0 AFTER `id`, ADD INDEX (`flag`);

-- }}}
-- {{{ indv.abd

ALTER TABLE `DbSmart2_Tests` ADD `flag2` TINYINT ( 1 ) UNSIGNED NOT NULL DEFAULT 0 AFTER `flag`, ADD INDEX (`flag2`);

-- }}}
-- {{{ indv2.001

CREATE TABLE IF NOT EXISTS `DbSmart2_Tests2` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT, PRIMARY KEY (`id`)) ENGINE=InnoDb DEFAULT CHARSET=utf8;

-- }}}
-- {{{ indv2.002

CREATE TABLE IF NOT EXISTS `DbSmart2_Tests3` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT, PRIMARY KEY (`id`)) ENGINE=InnoDb DEFAULT CHARSET=utf8;

-- }}}
-- {{{ indv2.3

CREATE TABLE IF NOT EXISTS `DbSmart2_Tests4` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT, PRIMARY KEY (`id`)) ENGINE=InnoDb DEFAULT CHARSET=utf8;

-- }}}
