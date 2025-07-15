<?php

/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * skullking implementation : © <Mathieu Chatrain> <mathieu.chatrain@gmail.com> && <Yannick Priol> <camertwo@hotmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * states.inc.php
 *
 * skullking game states description
 *
 */



$machinestates = [

    // The initial state. Please do not modify.

    1 => [
        "name" => "gameSetup",
        "description" => "",
        "type" => "manager",
        "action" => "stGameSetup",
        "transitions" => ["" => 4]
    ],


    2 => [
        "name" => "pending",
        "description" => '',
        "type" => "game",
        "action" => "stPending",
        "updateGameProgression" => true,
        "transitions" => ["end" => 99, "player" => 3, "multi" => 4, "same" => 2]
    ],


    3 => [
        "name" => "playerTurn",
        "description" => clienttranslate('${actplayer} must take an action'),
        "descriptionmyturn" => clienttranslate('${you} must take an action'),
        "type" => "activeplayer",
        "args" => "argPlayerTurn",
        "possibleactions" => ["actSelect", "actButton", "actValidate_Multi_Bendt", "actShowLastScore"],
        "transitions" => ["next" => 2, "zombiePass" => 2, "end" => 99]
    ],


    4 => [
        "name" => "playerTurnMulti",
        "description" => clienttranslate('The other players must perform their actions'),
        "descriptionmyturn" => clienttranslate('${you} must take an action'),
        "type" => "multipleactiveplayer",
        "initialprivate" => 50,
        "action" => 'st_MultiPlayerActivation',
        "possibleactions" => ["actSelect", "actShowLastScore"],
        "transitions" => ["next" => 5, "same" => 4, "zombiePass" => 4, "end" => 99]
    ],

    50 => [
        "name" => "playerTurnMultiBid",
        "descriptionmyturn" => clienttranslate('${you} must choose your bid'),
        "type" => "private",
        "args" => "argBid",
        "possibleactions" => ["actSelect", "actShowLastScore"],
        "transitions" => ['confirmbid' => 51, "same" => 50]
    ],

    51 => [
        "name" => "playerTurnMultiConfirmBid",
        "descriptionmyturn" => clienttranslate('${you} must confirm'),
        "type" => "private",
        "args" => "argConfirm",
        "possibleactions" => ["actConfirmBid", "actShowLastScore"],
        "transitions" => ['backtochoosebid' => 50]
    ],

    5 => [
        "name" => "displayBid",
        "description" => '',
        "type" => "game",
        "action" => "stDisplayBid",
        "transitions" => ["end" => 99, "next" => 2]
    ],


    // Final state.
    // Please do not modify (and do not overload action/args methods).
    99 => [
        "name" => "gameEnd",
        "description" => clienttranslate("End of game"),
        "type" => "manager",
        "action" => "stGameEnd",
        "args" => "argGameEnd"
    ],

];
