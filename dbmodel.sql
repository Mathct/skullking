
-- ------
-- BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
-- skullking implementation : © <Mathieu Chatrain> <mathieu.chatrain@gmail.com> && <Yannick Priol> <camertwo@hotmail.com>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-- -----

-- dbmodel.sql

CREATE TABLE IF NOT EXISTS `pending` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `player_id` int(10) NULL,  
  `function` varchar(50) NULL,
  `target` varchar(50) NULL,
  `arg` varchar(50) NULL,  
  `arg2` varchar(50) NULL,
  `arg3` varchar(50) NULL,
  `arg4` varchar(50) NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1000 ;

CREATE TABLE IF NOT EXISTS `card` (
  `card_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `card_type` varchar(16) NOT NULL,
  `card_type_arg` int(11) NOT NULL,
  `card_location` varchar(16) NOT NULL,
  `card_location_arg` int(11) NOT NULL,
  PRIMARY KEY (`card_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--  `card`
--   `card_id` 
--   `card_type`         non utilisé
--   `card_type_arg`     non utilisé
--   `card_location`     non utilisé
--   `card_location_arg` non utilisé


CREATE TABLE IF NOT EXISTS `scoring` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `round` int(5) NOT NULL DEFAULT 0,
  `cards` int(5) NOT NULL DEFAULT 0,
  `player_id` int(20) NOT NULL DEFAULT 0,
  `bid` int(5) NOT NULL DEFAULT 0,
  `tricks` int(5) NOT NULL DEFAULT 0,
  `tricks_vp` int(5) NOT NULL DEFAULT 0,
  `bonus_vp` int(5) NOT NULL DEFAULT 0,
  `total_round` int(5) NOT NULL DEFAULT 0,
  `type_bid` int(5) NOT NULL DEFAULT 0,
  `bonus_trick` int(5) NOT NULL DEFAULT 0,
  `bonus_rascal` int(5) NOT NULL DEFAULT 0,
  `bonus_loot` int(5) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;



ALTER TABLE `player` ADD `player_bid` int(5) NOT NULL DEFAULT -1;
ALTER TABLE `player` ADD `player_bid_validated` int(5) NOT NULL DEFAULT 0;
ALTER TABLE `player` ADD `player_tricks` int(5) NOT NULL DEFAULT 0;
ALTER TABLE `player` ADD `player_turn` int(5) NOT NULL DEFAULT 0;
ALTER TABLE `player` ADD `player_bonus_trick` int(5) NOT NULL DEFAULT 0;
ALTER TABLE `player` ADD `player_bonus_rascal` int(5) NOT NULL DEFAULT 0;
ALTER TABLE `player` ADD `player_bonus_loot` int(5) NOT NULL DEFAULT 0;