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
 * Game.php
 *
 * This is the main file for your game logic.
 *
 * In this PHP file, you are going to defines the rules of the game.
 */

declare(strict_types=1);

namespace Bga\Games\skullking;


require_once(APP_GAMEMODULE_PATH . "module/table/table.game.php");

use \Bga\GameFramework\Actions\CheckAction;

include('Pending.php'); // ATTENTION

class Game extends \Table
{
    public array $_SUIT_CARDS; // ATTENTION
    public array $_SPECIAL_CARDS;
    public array $_PIRATE_CARDS;
    public array $_ROUNDS;

    public $db_card;


    public static $instance = null; //ATTENTION

    /**
     * Your global variables labels:
     *
     * Here, you can assign labels to global variables you are using for this game. You can use any number of global
     * variables with IDs between 10 and 99. If your game has options (variants), you also have to associate here a
     * label to the corresponding ID in `gameoptions.inc.php`.
     *
     * NOTE: afterward, you can get/set the global variables with `getGameStateValue`, `setGameStateInitialValue` or
     * `setGameStateValue` functions.
     */
    public function __construct()
    {
        parent::__construct();

        require 'material.inc.php';

        // EXPERIMENTAL to avoid deadlocks.  This locks the global table early in the game constructor.
        $this->bSelectGlobalsForUpdate = true;

        $this->initGameStateLabels([
            "kraken_mode" => 100,
            "white_whale_mode" => 101,
            "loot_mode" => 102,
            "pirate_powers_mode" => 103,

            "first_player_round" => 10,
            "first_player_trick" => 11,
            "round_max_bid" => 12,
            "requested_color" => 13,
            "requested_color_cannot_change" => 14,
            "tigress_role" => 15,
            "end_of_round" => 16,
            "round_nb" => 17,
            "kraken" => 18,
            "white_whale" => 19,
            "loot_1_id_play" => 20,
            "loot_1_id_win" => 21,
            "loot_2_id_play" => 22,
            "loot_2_id_win" => 23,
            "loot_nb_in_turn" => 24,
            "rosie_container" => 25,
            "rascal_container" => 26,
            "juanita_container" => 27,
            "harry_container" => 28,
            "end_of_game" => 29,


        ]);

        self::$instance = $this; // ATTENTION

        $this->db_card = self::getNew("module.common.deck");
        $this->db_card->init("card");
    }

    /**
     * Returns the game name.
     *
     * IMPORTANT: Please do not modify.
     */
    protected function getGameName()
    {
        return "skullking";
    }


    /////////////////////////////////////////////////////////////////////////////////  
    //       _____                        _____       _ _   _       _ _          _   _             
    //      / ____|                      |_   _|     (_) | (_)     | (_)        | | (_)            
    //     | |  __  __ _ _ __ ___   ___    | |  _ __  _| |_ _  __ _| |_ ______ _| |_ _  ___  _ __  
    //     | | |_ |/ _` | '_ ` _ \ / _ \   | | | '_ \| | __| |/ _` | | |_  / _` | __| |/ _ \| '_ \ 
    //     | |__| | (_| | | | | | |  __/  _| |_| | | | | |_| | (_| | | |/ / (_| | |_| | (_) | | | |
    //      \_____|\__,_|_| |_| |_|\___| |_____|_| |_|_|\__|_|\__,_|_|_/___\__,_|\__|_|\___/|_| |_|
    //                                                                                               
    /////////////////////////////////////////////////////////////////////////////////    


    protected function setupNewGame($players, $options = [])
    {
        // Set the colors of the players with HTML color code. The default below is red/green/blue/orange/brown. The
        // number of colors defined here must correspond to the maximum number of players allowed for the gams.
        $gameinfos = $this->getGameinfos();
        $default_colors = $gameinfos['player_colors'];

        foreach ($players as $player_id => $player) {
            // Now you can access both $player_id and $player array
            $query_values[] = vsprintf("('%s', '%s', '%s', '%s', '%s')", [
                $player_id,
                array_shift($default_colors),
                $player["player_canal"],
                addslashes($player["player_name"]),
                addslashes($player["player_avatar"]),
            ]);
        }

        // Create players based on generic information.
        //
        // NOTE: You can add extra field on player table in the database (see dbmodel.sql) and initialize
        // additional fields directly here.
        static::DbQuery(
            sprintf(
                "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES %s",
                implode(",", $query_values)
            )
        );

        $this->reattributeColorsBasedOnPreferences($players, $gameinfos["player_colors"]);
        $this->reloadPlayersBasicInfos();

        // Init global values with their initial values.

        $this->setGameStateInitialValue("round_max_bid", 0);
        $this->setGameStateInitialValue("round_nb", 9);
        $this->setGameStateInitialValue("requested_color", 0);
        $this->setGameStateInitialValue("requested_color_cannot_change", 0);
        $this->setGameStateInitialValue("tigress_role", 0);
        $this->setGameStateInitialValue("end_of_round", 0);
        $this->setGameStateInitialValue("kraken", 0);
        $this->setGameStateInitialValue("white_whale", 0);
        $this->setGameStateInitialValue("loot_1_id_play", 0);
        $this->setGameStateInitialValue("loot_1_id_win", 0);
        $this->setGameStateInitialValue("loot_2_id_play", 0);
        $this->setGameStateInitialValue("loot_2_id_win", 0);
        $this->setGameStateInitialValue("loot_nb_in_turn", 0);
        $this->setGameStateInitialValue("rosie_container", 0);
        $this->setGameStateInitialValue("rascal_container", 0);
        $this->setGameStateInitialValue("juanita_container", 0);
        $this->setGameStateInitialValue("harry_container", 0);
        $this->setGameStateInitialValue("end_of_game", 0);

        // STATS

        self::initStat( 'table', 'sk_captured', 0 );
        self::initStat( 'table', 'pirate_captured', 0 );
        self::initStat( 'table', 'mermaid_captured', 0 );

        self::initStat( 'player', 'total_round_1', 0 );
        self::initStat( 'player', 'total_round_2', 0 );
        self::initStat( 'player', 'total_round_3', 0 );
        self::initStat( 'player', 'total_round_4', 0 );
        self::initStat( 'player', 'total_round_5', 0 );
        self::initStat( 'player', 'total_round_6', 0 );
        self::initStat( 'player', 'total_round_7', 0 );
        self::initStat( 'player', 'total_round_8', 0 );
        self::initStat( 'player', 'total_round_9', 0 );
        self::initStat( 'player', 'total_round_10', 0 );


        // First Player
        $first_player_id = $this->getUniqueValueFromDb("SELECT player_id FROM player WHERE player_no=1");
        $this->setGameStateInitialValue("first_player_round", $first_player_id);
        $this->setGameStateInitialValue("first_player_trick", $first_player_id);

        // TODO: Setup the initial game situation here.

        $cards = [];
        foreach (array_keys($this->_SUIT_CARDS) as $color) {
            for ($value = 1; $value <= 14; $value++) {
                $cards[] = array("type" => $color, "type_arg" => $value, "nbr" => 1);
            }
        }

        for ($value = 1; $value <= 5; $value++) {
            $cards[] = array("type" => 'pirate', "type_arg" => $value, "nbr" => 1);
        }

        $cards[] = array("type" => 'escape', "type_arg" => 0, "nbr" => 5);
        $cards[] = array("type" => 'tigress', "type_arg" => 0, "nbr" => 1);
        $cards[] = array("type" => 'skull_king', "type_arg" => 0, "nbr" => 1);
        $cards[] = array("type" => 'mermaid', "type_arg" => 1, "nbr" => 1);
        $cards[] = array("type" => 'mermaid', "type_arg" => 2, "nbr" => 1);

        if ($this->getGameStateValue("kraken_mode") == 2) {
            $cards[] = array("type" => 'kraken', "type_arg" => 0, "nbr" => 1);
        }

        if ($this->getGameStateValue("white_whale_mode") == 2) {
            $cards[] = array("type" => 'white_whale', "type_arg" => 0, "nbr" => 1);
        }

        if ($this->getGameStateValue("loot_mode") == 2) {
            $cards[] = array("type" => 'loot', "type_arg" => 0, "nbr" => 2);
        }

        $this->db_card->createCards($cards, 'deck');
        $this->db_card->shuffle('deck');





        // Activate first player once everything has been initialized and ready.
        ///$this->activeNextPlayer();




        /************ Init Pending *****/


        foreach (array_keys($players) as $player_id) {
            $this->addPendingFirst($player_id, "PlayCard");
        }
    }

    /////////////////////////////////////////////////////////////////////////////////  
    //               _            _ _ _____        _            
    //              | |     /\   | | |  __ \      | |           
    //     __ _  ___| |_   /  \  | | | |  | | __ _| |_ __ _ ___ 
    //    / _` |/ _ \ __| / /\ \ | | | |  | |/ _` | __/ _` / __|
    //   | (_| |  __/ |_ / ____ \| | | |__| | (_| | || (_| \__ \
    //    \__, |\___|\__/_/    \_\_|_|_____/ \__,_|\__\__,_|___/
    //     __/ |                                                
    //    |___/                                                 
    /////////////////////////////////////////////////////////////////////////////////  

    protected function getAllDatas(): array
    {
        $result = [];

        // WARNING: We must only return information visible by the current player.
        $current_player_id = (int) $this->getCurrentPlayerId();

        $sql = "SELECT player_no no FROM player WHERE player_id = $current_player_id";
        $current_player_no = $this->getUniqueValueFromDb($sql);
        if (is_null($current_player_no)) {
            $current_player_no = 0;
        }


        // Get information about players.
        // NOTE: you can retrieve some extra field you added for "player" table in `dbmodel.sql` if you need it.
        $result["players"] = $this->getCollectionFromDb(
            "SELECT `player_id` `id`, `player_score` `score`, `player_bid` `bid`, `player_bid_validated` `bid_validated`, `player_tricks` `tricks`, `player_turn` `turn`, `player_bonus_trick` `bonus_trick`, `player_bonus_rascal` `bonus_rascal` 
            FROM `player`"
        );

        $sql = "SELECT player_no no, player_id id, player_score score, player_name name, player_color color, player_bid bid, player_bid_validated bid_validated, player_tricks tricks, player_turn turn, player_bonus_trick bonus_trick, player_bonus_rascal bonus_rascal 
            FROM player ";
        $sql .= " ORDER BY (player_no >= $current_player_no) DESC, player_no ASC";
        $result['players_ordered'] = $this->getObjectListFromDB($sql);

        $tricks_taken = $this->db_card->countCardsByLocationArgs('discard');
        foreach ($result["players"] as $player_id => $player) {
            $result["players"][$player_id]["tricks_taken"] = isset($tricks_taken[$player_id]) ? $tricks_taken[$player_id] / count($result["players"]) : 0;
        }

        $result['state_id'] = $this->gamestate->state_id();


        $result['my_hand'] = $this->db_card->getPlayerHand($current_player_id);
        $result['deck'] = $this->getGameStateValue('juanita_container') == $current_player_id ? $this->db_card->getCardsInLocation('deck') : [];
        //$result['deck'] =  $this->db_card->getCardsInLocation('deck');

        $result['table'] = $this->getCardsOnTableOrdered();

        $result['first_player_round'] = $this->getGameStateValue('first_player_round');
        $result['first_player_trick'] = $this->getGameStateValue('first_player_trick');

        $result['round_max_bid'] = $this->getGameStateValue('round_max_bid');
        $result['round_nb'] = $this->getGameStateValue('round_nb');

        $result['suit_cards'] = $this->_SUIT_CARDS;
        $result['special_cards'] = $this->_SPECIAL_CARDS;
        $result['pirate_cards'] = $this->_PIRATE_CARDS;

        $sql = "SELECT id, round, cards, player_id, bid, tricks, tricks_vp, bonus_vp, total_round, type_bid
            FROM scoring ";
        $result['scoring'] = $this->getObjectListFromDB($sql);

        $result['tigress_role'] = $this->getGameStateValue('tigress_role');
        if($this->getGameStateValue('tigress_role') != 0)
        {
            $result['tigress_cardid'] = self::getUniqueValueFromDB("SELECT card_id FROM card WHERE card_type='tigress'");
        }

        $result['rosie_container'] = $this->getGameStateValue('rosie_container');
        $result['rascal_container'] = $this->getGameStateValue('rascal_container');
        $result['juanita_container'] = $this->getGameStateValue('juanita_container');
        $result['harry_container'] = $this->getGameStateValue('harry_container');

        if ($this->getGameStateValue('harry_container') != 0) {
            $player_harry = $this->getGameStateValue('harry_container');
            $player_bid = $result["players"][$player_harry]['bid'];
            $max_bid = $result['round_max_bid'];
            $harry_bids = array();

            if ($player_bid >= 1) {
                $harry_bids[] = $player_bid - 1;
            }

            $harry_bids[] = $player_bid;

            if ($player_bid < $max_bid) {
                $harry_bids[] = $player_bid + 1;
            }


            $result['harry_bids'] = $harry_bids;
        }

        $result['pirate_powers_mode'] = $this->getGameStateValue('pirate_powers_mode');

        $result['end_of_game'] = game::$instance->getGameStateValue("end_of_game");




        // TODO: Gather all information about current game situation (visible by player $current_player_id).

        return $result;
    }


    /////////////////////////////////////////////////////////////////////////////////  
    //     _____                      _____                                   _             
    //    / ____|                    |  __ \                                 (_)            
    //   | |  __  __ _ _ __ ___   ___| |__) | __ ___   __ _ _ __ ___  ___ ___ _  ___  _ __  
    //   | | |_ |/ _` | '_ ` _ \ / _ \  ___/ '__/ _ \ / _` | '__/ _ \/ __/ __| |/ _ \| '_ \ 
    //   | |__| | (_| | | | | | |  __/ |   | | | (_) | (_| | | |  __/\__ \__ \ | (_) | | | |
    //    \_____|\__,_|_| |_| |_|\___|_|   |_|  \___/ \__, |_|  \___||___/___/_|\___/|_| |_|
    //                                                 __/ |                                
    //                                                |___/                                 
    /////////////////////////////////////////////////////////////////////////////////  

    public function getGameProgression()
    {
        $round = $this->getGameStateValue("round_nb");

        return $round*10;
    }


    /////////////////////////////////////////////////////////////////////////////////  
    //     _    _ _   _ _ _ _            __                  _   _                 
    //    | |  | | | (_) (_) |          / _|                | | (_)                
    //    | |  | | |_ _| |_| |_ _   _  | |_ _   _ _ __   ___| |_ _  ___  _ __  ___ 
    //    | |  | | __| | | | __| | | | |  _| | | | '_ \ / __| __| |/ _ \| '_ \/ __|
    //    | |__| | |_| | | | |_| |_| | | | | |_| | | | | (__| |_| | (_) | | | \__ \
    //     \____/ \__|_|_|_|\__|\__, | |_|  \__,_|_| |_|\___|\__|_|\___/|_| |_|___/
    //                           __/ |                                             
    //                          |___/                                              
    /////////////////////////////////////////////////////////////////////////////////  

    function addPending($player_id, $function, $arg = NULL, $arg2 = NULL, $arg3 = NULL, $arg4 = NULL)
    {
        $sql = "INSERT INTO pending (player_id, function, arg, arg2, arg3, arg4) VALUES (" . $player_id . ", '" . $function . "', '" . $arg . "', '" . $arg2 . "', '" . $arg3 . "', '" . $arg4 . "')";
        self::DbQuery($sql);
    }


    function addPendingFirst($player_id, $function, $arg = NULL, $arg2 = NULL, $arg3 = NULL, $arg4 = NULL)
    {
        $minid = self::getUniqueValueFromDB("select min(id) from pending") - 1;
        $sql = "INSERT INTO pending (id, player_id, function, arg, arg2) VALUES (" . $minid . "," . $player_id . ", '" . $function . "', '" . $arg . "', '" . $arg2 . "')";
        self::DbQuery($sql);
    }

    function checkArgs($arg1)
    {
        $ret = self::argPlayerTurn();

        if (!in_array($arg1, $ret['selectable']) && !in_array($arg1, $ret['buttons'])) {
            throw new \BgaSystemException("Not a valid selection");
        }
    }

    public function getCardsOnTableOrdered()
    {
        $current = $this->getGameStateValue('first_player_trick');
        $current_no = self::getUniqueValueFromDB("SELECT player_no FROM player WHERE player_id = $current");

        self::dump('current', $current);

        self::dump('current_no', $current_no);

        $sql = "SELECT player_id FROM player ORDER BY (player_no >= $current_no) DESC, player_no ASC";
        $ordered_ids = $this->getObjectListFromDB($sql, true);
        // ⬅️ Retourne un array simple [2037568, 2037569, ...]

        self::dump('ordered_ids', $ordered_ids);


        $tableCards = $this->db_card->getCardsInLocation('table');


        // Indexe les cartes par player_id
        $cards_by_player = array_column($tableCards, null, 'location_arg');

        self::dump('cards_by_player', $cards_by_player);


        // Trie les cartes selon l’ordre
        $ordered_cards = [];
        foreach ($ordered_ids as $player_id) {
            $player_id = (int) $player_id;
            if (isset($cards_by_player[$player_id])) {
                $ordered_cards[] = $cards_by_player[$player_id];
                //$card_id = $cards_by_player[$player_id]['id'];
                //self::dump('card_id', $card_id);
                //$ordered_cards[$card_id] = $cards_by_player[$player_id];
                //self::dump('cards_by_player', $cards_by_player[$player_id]);
            }
        }
        self::dump('ordered_cards', $ordered_cards);
        return $ordered_cards;
    }

    function winnerOfTrick()
    {
        // DECLARATION DES VARIABLES
        $ordre_players = array();
        $ordre_card_type = array();
        $first_pirate = 0;
        $first_mermaid = 0;
        $first_escape = 0;
        $skull_king = 0;
        $best_green = 0;
        $best_purple = 0;
        $best_yellow = 0;
        $best_black = 0;
        $color = ['green', 'purple', 'yellow', 'black'];
        $first_player_trick = game::$instance->getGameStateValue("first_player_trick");
        $requested_color_nb = 0;
        $requested_color_name = null;
        $winner = 0;

        $kraken = 0;
        $white_whale = 0;
        $player_white_whale = 0;
        $bests_nb = array();

        $loot1_play = game::$instance->getGameStateValue("loot_1_id_play");
        $loot2_play = game::$instance->getGameStateValue("loot_2_id_play");
        $loot_nb_in_turn = game::$instance->getGameStateValue("loot_nb_in_turn");
        $iconloot = "<div class='icon_log' title='' style='background-position-x: -600%; background-position-y: -400%;'></div>";



        // CHANGEMENT DES VARIABLES

        $requested_color_nb = game::$instance->getGameStateValue("requested_color");
        if ($requested_color_nb != 0) {
            $requested_color_name = $color[$requested_color_nb - 1];
        }

        $ordre_players[] = $first_player_trick;
        $next = game::$instance->getPlayerAfter($first_player_trick);
        $count_players = count(self::getObjectListFromDB("SELECT player_id id FROM player", true));
        for ($i = 1; $i <= $count_players - 1; $i++) {

            $ordre_players[] = $next;
            $next = game::$instance->getPlayerAfter($next);
        }


        foreach ($ordre_players as $player) {
            $type = self::getUniqueValueFromDB("SELECT card_type FROM card WHERE card_location = 'table' AND card_location_arg = '{$player}'");

            if (($type == 'kraken') && ($white_whale == 0)) {
                $kraken = 1;
                game::$instance->setGameStateValue("kraken", 1);
            }

            if (($type == 'white_whale') && ($kraken == 0)) {
                $white_whale = 1;
                $player_white_whale = $player;
                game::$instance->setGameStateValue("white_whale", 1);
            }

            if (($type == 'kraken') && ($white_whale == 1)) {
                $kraken = 1;
                $white_whale = 0;
                game::$instance->setGameStateValue("kraken", 1);
                game::$instance->setGameStateValue("white_whale", 0);
            }

            if (($type == 'white_whale') && ($kraken == 1)) {
                $white_whale = 1;
                $kraken = 0;
                $player_white_whale = $player;
                game::$instance->setGameStateValue("white_whale", 1);
                game::$instance->setGameStateValue("kraken", 0);
            }

            if ($type == 'tigress') {
                if (game::$instance->getGameStateValue("tigress_role") == 1) {
                    $ordre_card_type[] = 'pirate';
                }
                if (game::$instance->getGameStateValue("tigress_role") == 2) {
                    $ordre_card_type[] = 'escape';
                }
            }

            if ($type == 'loot') {
                $ordre_card_type[] = 'escape';
            } 
            
            else {
                $ordre_card_type[] = $type;
            }
        }




        $index = array_search('pirate', $ordre_card_type);
        if ($index !== false) {
            $first_pirate = $ordre_players[$index];
        }

        $index = array_search('mermaid', $ordre_card_type);
        if ($index !== false) {
            $first_mermaid = $ordre_players[$index];
        }

        $index = array_search('skull_king', $ordre_card_type);
        if ($index !== false) {
            $skull_king = $ordre_players[$index];
        }

        $index = array_search('escape', $ordre_card_type);
        if ($index !== false) {
            $first_escape = $ordre_players[$index];
        }

        $green = self::getUniqueValueFromDB("SELECT card_location_arg FROM card WHERE card_type = 'green' AND card_location = 'table' ORDER BY card_type_arg DESC LIMIT 1");
        if ($green != null) {
            $best_green = $green;
        }
        $purple = self::getUniqueValueFromDB("SELECT card_location_arg FROM card WHERE card_type = 'purple' AND card_location = 'table' ORDER BY card_type_arg DESC LIMIT 1");
        if ($purple != null) {
            $best_purple = $purple;
        }
        $yellow = self::getUniqueValueFromDB("SELECT card_location_arg FROM card WHERE card_type = 'yellow' AND card_location = 'table' ORDER BY card_type_arg DESC LIMIT 1");
        if ($yellow != null) {
            $best_yellow = $yellow;
        }
        $black = self::getUniqueValueFromDB("SELECT card_location_arg FROM card WHERE card_type = 'black' AND card_location = 'table' ORDER BY card_type_arg DESC LIMIT 1");
        if ($black != null) {
            $best_black = $black;
        }

        if ($white_whale == 1) {
            $bests_nb = self::getObjectListFromDB("SELECT card_location_arg FROM card WHERE card_type IN ('green', 'purple', 'yellow', 'black') AND card_location = 'table' AND card_type_arg = (SELECT MAX(card_type_arg) FROM card WHERE card_type IN ('green', 'purple', 'yellow', 'black') AND card_location = 'table')", true);
        }


        if ($white_whale == 0) {
            // WINNER

            if (($skull_king != 0) && ($first_mermaid == 0)) {
                $winner = $skull_king;
            } elseif (($skull_king != 0) && ($first_mermaid != 0)) {
                $winner = $first_mermaid;
            } elseif ($first_pirate != 0) {
                $winner = $first_pirate;
            } elseif ($first_mermaid != 0) {
                $winner = $first_mermaid;
            } elseif ($best_black != 0) {
                $winner = $best_black;
            } else {
                if ($requested_color_name == 'green') {
                    $winner = $best_green;
                }

                if ($requested_color_name == 'purple') {
                    $winner = $best_purple;
                }

                if ($requested_color_name == 'yellow') {
                    $winner = $best_yellow;
                }

                if ($requested_color_nb == 0) // si y a que des escapes ou des loots
                {
                    $winner = $first_escape;

                    $type_escape = self::getUniqueValueFromDB("SELECT card_type FROM card WHERE card_location = 'table' AND card_location_arg = '{$winner}'");

                    if($type_escape == 'loot')
                    {
                        if($loot_nb_in_turn == 1)
                        {
                            $loot_nb_in_turn = 0; // si y a qu'1 seul loot en premiere position... il n'est pas pris en compte
                            
                        }

                        if($loot_nb_in_turn == 2)
                        {
                            $loot_nb_in_turn = 1; // si y a 2 loot et 1 en premiere position... le premier ne sera pas pris en compte... le second prendra la place du premier 
                            $loot1_play = $loot2_play;
                            $loot2_play = 0;
                            game::$instance->setGameStateValue("loot_1_id_play", $loot1_play);
                            game::$instance->setGameStateValue("loot_2_id_play", 0);
                            
                        }


                    }


                    

                }
            }

            if ($kraken == 0) {
                game::$instance->DbQuery("UPDATE player set player_tricks = player_tricks +1 WHERE player_id = '{$winner}' ");

                //LOOT
                if ($loot_nb_in_turn != 0) // si y a des loot pris en compte
                {
                    if ($loot_nb_in_turn == 1) // si y en qu'1 durant le même tour
                    {
                        if ($loot2_play == 0) // si c'est le 1er du round
                        {
                            game::$instance->setGameStateValue("loot_1_id_win", $winner);
                            game::$instance->DbQuery("UPDATE player set player_bonus_loot = player_bonus_loot +20 WHERE player_id = '{$winner}'");
                            game::$instance->DbQuery("UPDATE player set player_bonus_loot = player_bonus_loot +20 WHERE player_id = '{$loot1_play}'");

                            $winner_name = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id = '{$winner}'");
                            $play_name = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id = '{$loot1_play}'");
                            $play_color = self::getUniqueValueFromDB("SELECT player_color FROM player WHERE player_id = '{$loot1_play}'");

                            game::$instance->notifyAllPlayers(
                            'message',
                            clienttranslate('${player_name} and ${opponent} share ${icon}'),
                            array(
                                'opponent' =>    [   'log' => '<b style="color: #${color};">${opponent_name}</b>',
                                                    'args'=> ['opponent_name' => $play_name, 'color'=>$play_color]
                                                ],

                                'player_name' => $winner_name,
                                'icon' => $iconloot,
                                
                            )
                            );

                        } else // si c'est le 2eme du round
                        {
                            game::$instance->setGameStateValue("loot_2_id_win", $winner);
                            game::$instance->DbQuery("UPDATE player set player_bonus_loot = player_bonus_loot +20 WHERE player_id = '{$winner}'");
                            game::$instance->DbQuery("UPDATE player set player_bonus_loot = player_bonus_loot +20 WHERE player_id = '{$loot2_play}'");
                            
                            $winner_name = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id = '{$winner}'");
                            $play_name = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id = '{$loot2_play}'");
                            $play_color = self::getUniqueValueFromDB("SELECT player_color FROM player WHERE player_id = '{$loot2_play}'");

                            game::$instance->notifyAllPlayers(
                            'message',
                            clienttranslate('${player_name} and ${opponent} share ${icon}'),
                            array(
                                'opponent' =>    [   'log' => '<b style="color: #${color};">${opponent_name}</b>',
                                                    'args'=> ['opponent_name' => $play_name, 'color'=>$play_color]
                                                ],

                                'player_name' => $winner_name,
                                'icon' => $iconloot,
                                
                            )
                            );

                        }
                    }

                    if ($loot_nb_in_turn == 2) // si y en 2 durant le même tour
                    {
                        game::$instance->setGameStateValue("loot_1_id_win", $winner);
                        game::$instance->setGameStateValue("loot_2_id_win", $winner);
                        game::$instance->DbQuery("UPDATE player set player_bonus_loot = player_bonus_loot +40 WHERE player_id = '{$winner}'");
                        game::$instance->DbQuery("UPDATE player set player_bonus_loot = player_bonus_loot +20 WHERE player_id = '{$loot1_play}'");
                        game::$instance->DbQuery("UPDATE player set player_bonus_loot = player_bonus_loot +20 WHERE player_id = '{$loot2_play}'");

                        $winner_name = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id = '{$winner}'");
                        $play_name1 = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id = '{$loot1_play}'");
                        $play_color1 = self::getUniqueValueFromDB("SELECT player_color FROM player WHERE player_id = '{$loot1_play}'");
                        $play_name2 = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id = '{$loot2_play}'");
                        $play_color2 = self::getUniqueValueFromDB("SELECT player_color FROM player WHERE player_id = '{$loot2_play}'");

                        game::$instance->notifyAllPlayers(
                            'message',
                            clienttranslate('${player_name} and ${opponent} share ${icon}'),
                            array(
                                'opponent' =>    [   'log' => '<b style="color: #${color};">${opponent_name}</b>',
                                                    'args'=> ['opponent_name' => $play_name1, 'color'=>$play_color1]
                                                ],

                                'player_name' => $winner_name,
                                'icon' => $iconloot,
                                
                            )
                            );

                        game::$instance->notifyAllPlayers(
                            'message',
                            clienttranslate('${player_name} and ${opponent} share ${icon}'),
                            array(
                                'opponent' =>    [   'log' => '<b style="color: #${color};">${opponent_name}</b>',
                                                    'args'=> ['opponent_name' => $play_name2, 'color'=>$play_color2]
                                                ],

                                'player_name' => $winner_name,
                                'icon' => $iconloot,
                                
                            )
                            );
                    }
                }
            }
        }

        if ($white_whale == 1) {

            $count = count($bests_nb);
            if ($count == 0) {
                $winner = $player_white_whale;
            } else {
                if ($count == 1) {
                    $winner = $bests_nb[0];
                    game::$instance->DbQuery("UPDATE player set player_tricks = player_tricks +1 WHERE player_id = '{$winner}' ");

                    //LOOT
                    if ($loot_nb_in_turn != 0) {
                        if ($loot_nb_in_turn == 1) {
                            if ($loot2_play == 0) {
                                game::$instance->setGameStateValue("loot_1_id_win", $winner);
                                game::$instance->DbQuery("UPDATE player set player_bonus_loot = player_bonus_loot +20 WHERE player_id = '{$winner}'");
                                game::$instance->DbQuery("UPDATE player set player_bonus_loot = player_bonus_loot +20 WHERE player_id = '{$loot1_play}'");

                                $winner_name = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id = '{$winner}'");
                                $play_name = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id = '{$loot1_play}'");
                                $play_color = self::getUniqueValueFromDB("SELECT player_color FROM player WHERE player_id = '{$loot1_play}'");

                                game::$instance->notifyAllPlayers(
                                'message',
                                clienttranslate('${player_name} and ${opponent} share ${icon}'),
                                array(
                                    'opponent' =>    [   'log' => '<b style="color: #${color};">${opponent_name}</b>',
                                                        'args'=> ['opponent_name' => $play_name, 'color'=>$play_color]
                                                    ],

                                    'player_name' => $winner_name,
                                    'icon' => $iconloot,
                                    
                                )
                                );
                            } else {
                                game::$instance->setGameStateValue("loot_2_id_win", $winner);
                                game::$instance->DbQuery("UPDATE player set player_bonus_loot = player_bonus_loot +20 WHERE player_id = '{$winner}'");
                                game::$instance->DbQuery("UPDATE player set player_bonus_loot = player_bonus_loot +20 WHERE player_id = '{$loot2_play}'");

                                $winner_name = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id = '{$winner}'");
                                $play_name = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id = '{$loot2_play}'");
                                $play_color = self::getUniqueValueFromDB("SELECT player_color FROM player WHERE player_id = '{$loot2_play}'");

                                game::$instance->notifyAllPlayers(
                                'message',
                                clienttranslate('${player_name} and ${opponent} share ${icon}'),
                                array(
                                    'opponent' =>    [   'log' => '<b style="color: #${color};">${opponent_name}</b>',
                                                        'args'=> ['opponent_name' => $play_name, 'color'=>$play_color]
                                                    ],

                                    'player_name' => $winner_name,
                                    'icon' => $iconloot,
                                    
                                )
                                );
                            }
                        }

                        if ($loot_nb_in_turn == 2) {
                            game::$instance->setGameStateValue("loot_1_id_win", $winner);
                            game::$instance->setGameStateValue("loot_2_id_win", $winner);
                            game::$instance->DbQuery("UPDATE player set player_bonus_loot = player_bonus_loot +40 WHERE player_id = '{$winner}'");
                            game::$instance->DbQuery("UPDATE player set player_bonus_loot = player_bonus_loot +20 WHERE player_id = '{$loot1_play}'");
                            game::$instance->DbQuery("UPDATE player set player_bonus_loot = player_bonus_loot +20 WHERE player_id = '{$loot2_play}'");

                            $winner_name = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id = '{$winner}'");
                            $play_name1 = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id = '{$loot1_play}'");
                            $play_color1 = self::getUniqueValueFromDB("SELECT player_color FROM player WHERE player_id = '{$loot1_play}'");
                            $play_name2 = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id = '{$loot2_play}'");
                            $play_color2 = self::getUniqueValueFromDB("SELECT player_color FROM player WHERE player_id = '{$loot2_play}'");

                            game::$instance->notifyAllPlayers(
                                'message',
                                clienttranslate('${player_name} and ${opponent} share ${icon}'),
                                array(
                                    'opponent' =>    [   'log' => '<b style="color: #${color};">${opponent_name}</b>',
                                                        'args'=> ['opponent_name' => $play_name1, 'color'=>$play_color1]
                                                    ],

                                    'player_name' => $winner_name,
                                    'icon' => $iconloot,
                                    
                                )
                                );

                            game::$instance->notifyAllPlayers(
                                'message',
                                clienttranslate('${player_name} and ${opponent} share ${icon}'),
                                array(
                                    'opponent' =>    [   'log' => '<b style="color: #${color};">${opponent_name}</b>',
                                                        'args'=> ['opponent_name' => $play_name2, 'color'=>$play_color2]
                                                    ],

                                    'player_name' => $winner_name,
                                    'icon' => $iconloot,
                                    
                                )
                                );
                        }
                    }
                } else {
                    foreach ($ordre_players as $player) {
                        if (in_array($player, $bests_nb)) {
                            $winner = $player;
                            break;
                        }
                    }
                    game::$instance->DbQuery("UPDATE player set player_tricks = player_tricks +1 WHERE player_id = '{$winner}' ");

                    //LOOT
                    if ($loot_nb_in_turn != 0) {
                        if ($loot_nb_in_turn == 1) {
                            if ($loot2_play == 0) {
                                game::$instance->setGameStateValue("loot_1_id_win", $winner);
                                game::$instance->DbQuery("UPDATE player set player_bonus_loot = player_bonus_loot +20 WHERE player_id = '{$winner}'");
                                game::$instance->DbQuery("UPDATE player set player_bonus_loot = player_bonus_loot +20 WHERE player_id = '{$loot1_play}'");

                                $winner_name = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id = '{$winner}'");
                                $play_name = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id = '{$loot1_play}'");
                                $play_color = self::getUniqueValueFromDB("SELECT player_color FROM player WHERE player_id = '{$loot1_play}'");

                                game::$instance->notifyAllPlayers(
                                'message',
                                clienttranslate('${player_name} and ${opponent} share ${icon}'),
                                array(
                                    'opponent' =>    [   'log' => '<b style="color: #${color};">${opponent_name}</b>',
                                                        'args'=> ['opponent_name' => $play_name, 'color'=>$play_color]
                                                    ],

                                    'player_name' => $winner_name,
                                    'icon' => $iconloot,
                                    
                                )
                                );
                            } else {
                                game::$instance->setGameStateValue("loot_2_id_win", $winner);
                                game::$instance->DbQuery("UPDATE player set player_bonus_loot = player_bonus_loot +20 WHERE player_id = '{$winner}'");
                                game::$instance->DbQuery("UPDATE player set player_bonus_loot = player_bonus_loot +20 WHERE player_id = '{$loot2_play}'");

                                $winner_name = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id = '{$winner}'");
                                $play_name = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id = '{$loot2_play}'");
                                $play_color = self::getUniqueValueFromDB("SELECT player_color FROM player WHERE player_id = '{$loot2_play}'");

                                game::$instance->notifyAllPlayers(
                                'message',
                                clienttranslate('${player_name} and ${opponent} share ${icon}'),
                                array(
                                    'opponent' =>    [   'log' => '<b style="color: #${color};">${opponent_name}</b>',
                                                        'args'=> ['opponent_name' => $play_name, 'color'=>$play_color]
                                                    ],

                                    'player_name' => $winner_name,
                                    'icon' => $iconloot,
                                    
                                )
                                );
                            }
                        }

                        if ($loot_nb_in_turn == 2) {
                            game::$instance->setGameStateValue("loot_1_id_win", $winner);
                            game::$instance->setGameStateValue("loot_2_id_win", $winner);
                            game::$instance->DbQuery("UPDATE player set player_bonus_loot = player_bonus_loot +40 WHERE player_id = '{$winner}'");
                            game::$instance->DbQuery("UPDATE player set player_bonus_loot = player_bonus_loot +20 WHERE player_id = '{$loot1_play}'");
                            game::$instance->DbQuery("UPDATE player set player_bonus_loot = player_bonus_loot +20 WHERE player_id = '{$loot2_play}'");

                            $winner_name = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id = '{$winner}'");
                            $play_name1 = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id = '{$loot1_play}'");
                            $play_color1 = self::getUniqueValueFromDB("SELECT player_color FROM player WHERE player_id = '{$loot1_play}'");
                            $play_name2 = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id = '{$loot2_play}'");
                            $play_color2 = self::getUniqueValueFromDB("SELECT player_color FROM player WHERE player_id = '{$loot2_play}'");

                            game::$instance->notifyAllPlayers(
                                'message',
                                clienttranslate('${player_name} and ${opponent} share ${icon}'),
                                array(
                                    'opponent' =>    [   'log' => '<b style="color: #${color};">${opponent_name}</b>',
                                                        'args'=> ['opponent_name' => $play_name1, 'color'=>$play_color1]
                                                    ],

                                    'player_name' => $winner_name,
                                    'icon' => $iconloot,
                                    
                                )
                                );

                            game::$instance->notifyAllPlayers(
                                'message',
                                clienttranslate('${player_name} and ${opponent} share ${icon}'),
                                array(
                                    'opponent' =>    [   'log' => '<b style="color: #${color};">${opponent_name}</b>',
                                                        'args'=> ['opponent_name' => $play_name2, 'color'=>$play_color2]
                                                    ],

                                    'player_name' => $winner_name,
                                    'icon' => $iconloot,
                                    
                                )
                                );
                        }
                    }
                }
            }
        }

        return $winner;
    }

    function scoreRound()
    {
        $players = self::getObjectListFromDB("SELECT player_id FROM player", true);
        $round = $this->getGameStateValue("round_nb");
        $cards = $this->getGameStateValue("round_max_bid");

        foreach ($players as $player) {
            
            
            $bid = self::getUniqueValueFromDB("SELECT player_bid FROM player WHERE player_id = '{$player}'");
            $tricks = self::getUniqueValueFromDB("SELECT player_tricks FROM player WHERE player_id = '{$player}'");

            // BONUS

            $bonus_vp = 0;
            $bonus_trick = 0;
            $bonus_rascal = 0;

            if ($tricks == $bid) {
                $bonus_trick = self::getUniqueValueFromDB("SELECT player_bonus_trick FROM player WHERE player_id = '{$player}'");
                $bonus_rascal = self::getUniqueValueFromDB("SELECT player_bonus_rascal FROM player WHERE player_id = '{$player}'");
                $bonus_vp = $bonus_trick + $bonus_rascal;
            }

            if ($tricks != $bid) {
                
                $bonus_rascal = self::getUniqueValueFromDB("SELECT player_bonus_rascal FROM player WHERE player_id = '{$player}'");
                $bonus_vp = $bonus_trick - $bonus_rascal;
            }

            //VP TRICKS

            if ($bid == 0) {
                if ($tricks == 0) {
                    $tricks_vp = $cards * 10;
                } else {
                    $tricks_vp = $cards * (-10);
                }
            }

            if ($bid >= 1) {
                if ($tricks == $bid) {
                    $tricks_vp = $tricks * 20;
                } else {
                    $diff = $tricks - $bid;
                    if ($diff > 0) {
                        $tricks_vp = $diff * (-10);
                    }
                    if ($diff < 0) {
                        $tricks_vp = $diff * 10;
                    }
                }
            }

           
            $total_round = $tricks_vp + $bonus_vp;


            $rappel_bonus_trick = self::getUniqueValueFromDB("SELECT player_bonus_trick FROM player WHERE player_id = '{$player}'");
            $rappel_bonus_rascal = self::getUniqueValueFromDB("SELECT player_bonus_rascal FROM player WHERE player_id = '{$player}'");
            $rappel_bonus_loot = self::getUniqueValueFromDB("SELECT player_bonus_loot FROM player WHERE player_id = '{$player}'");

            // INSERT BD SCORING

            self::DbQuery("INSERT INTO scoring (round, cards, player_id, bid, tricks, tricks_vp, bonus_vp, total_round, bonus_trick, bonus_rascal, bonus_loot) VALUES ($round, $cards, $player, $bid, $tricks, $tricks_vp, $bonus_vp, $total_round, $rappel_bonus_trick, $rappel_bonus_rascal, $rappel_bonus_loot)");
        }

        // BONUS LOOT CARD

        $play1 = game::$instance->getGameStateValue("loot_1_id_play");
        $win1 = game::$instance->getGameStateValue("loot_1_id_win");
        $play2 = game::$instance->getGameStateValue("loot_2_id_play");
        $win2 = game::$instance->getGameStateValue("loot_2_id_win");

        $round = $this->getGameStateValue("round_nb");

        if (($play1 != 0)&&($win1 != 0)) {
            $bid1 = self::getUniqueValueFromDB("SELECT player_bid FROM player WHERE player_id = '{$play1}'");
            $tricks1 = self::getUniqueValueFromDB("SELECT player_tricks FROM player WHERE player_id = '{$play1}'");
            $bid2 = self::getUniqueValueFromDB("SELECT player_bid FROM player WHERE player_id = '{$win1}'");
            $tricks2 = self::getUniqueValueFromDB("SELECT player_tricks FROM player WHERE player_id = '{$win1}'");

            if (($bid1 == $tricks1) && ($bid2 == $tricks2)) {
                game::$instance->DbQuery("UPDATE scoring set bonus_vp = bonus_vp + 20 WHERE player_id = '{$play1}' AND round = '{$round}'");
                game::$instance->DbQuery("UPDATE scoring set bonus_vp = bonus_vp + 20 WHERE player_id = '{$win1}' AND round = '{$round}'");
                game::$instance->DbQuery("UPDATE scoring set total_round = total_round + 20 WHERE player_id = '{$play1}' AND round = '{$round}'");
                game::$instance->DbQuery("UPDATE scoring set total_round = total_round + 20 WHERE player_id = '{$win1}' AND round = '{$round}'");
            }
        }

        if (($play2 != 0)&&($win2 != 0)) {
            $bid1 = self::getUniqueValueFromDB("SELECT player_bid FROM player WHERE player_id = '{$play2}'");
            $tricks1 = self::getUniqueValueFromDB("SELECT player_tricks FROM player WHERE player_id = '{$play2}'");
            $bid2 = self::getUniqueValueFromDB("SELECT player_bid FROM player WHERE player_id = '{$win2}'");
            $tricks2 = self::getUniqueValueFromDB("SELECT player_tricks FROM player WHERE player_id = '{$win2}'");

            if (($bid1 == $tricks1) && ($bid2 == $tricks2)) {
                game::$instance->DbQuery("UPDATE scoring set bonus_vp = bonus_vp + 20 WHERE player_id = '{$play2}' AND round = '{$round}'");
                game::$instance->DbQuery("UPDATE scoring set bonus_vp = bonus_vp + 20 WHERE player_id = '{$win2}' AND round = '{$round}'");
                game::$instance->DbQuery("UPDATE scoring set total_round = total_round + 20 WHERE player_id = '{$play2}' AND round = '{$round}'");
                game::$instance->DbQuery("UPDATE scoring set total_round = total_round + 20 WHERE player_id = '{$win2}' AND round = '{$round}'");
            }
        }


        foreach ($players as $player) {
            $score_round = self::getUniqueValueFromDB("SELECT total_round FROM scoring WHERE player_id = '{$player}' AND round = '{$round}'");
            game::$instance->DbQuery("UPDATE player set player_score = player_score + $score_round WHERE player_id = '{$player}'");
            $new_score = self::getUniqueValueFromDB("SELECT player_score FROM player WHERE player_id = '{$player}'");

            game::$instance->notifyAllPlayers(
                'score',
                '',
                array(
                    'playerid' => $player,
                    'score' => $new_score,

                )
            );

            // STATS
            game::$instance->incStat($score_round, 'total_round_'.$round, $player);

        }


        // SCORING ROUND DIALOG

        $scoringdialog_playernames = [''];
        $scoringdialog_cards = array(clienttranslate("Cards Played"));
        $scoringdialog_bid = array(clienttranslate("Bid"));
        $scoringdialog_trick = array(clienttranslate("Tricks Won"));
        $scoringdialog_trick_vp = array(clienttranslate("Tricks Score"));
        $scoringdialog_bonus = array(clienttranslate("Bonus Validated"));
        $scoringdialog_total = array(clienttranslate("Round Total"));
        $scoringdialog_detail = array(clienttranslate("Bonus Details:"));
        $scoringdialog_bonustrick = array(clienttranslate("Tricks Bonus:"));
        $scoringdialog_bonusrascal = array(clienttranslate("Rascal Bonus"));
        $scoringdialog_bonusloot = array(clienttranslate("Loot Card Bonus"));

        $player_info = $this->loadPlayersBasicInfos();
        foreach ($player_info as $player) {

            $cards = self::getUniqueValueFromDB("SELECT cards FROM scoring WHERE player_id = '{$player["player_id"]}' AND round = '{$round}'");
            array_push($scoringdialog_cards, $cards);
            $bid = self::getUniqueValueFromDB("SELECT bid FROM scoring WHERE player_id = '{$player["player_id"]}' AND round = '{$round}'");
            array_push($scoringdialog_bid, $bid);
            $trick = self::getUniqueValueFromDB("SELECT tricks FROM scoring WHERE player_id = '{$player["player_id"]}' AND round = '{$round}'");
            array_push($scoringdialog_trick, $trick);
            $trick_vp = self::getUniqueValueFromDB("SELECT tricks_vp FROM scoring WHERE player_id = '{$player["player_id"]}' AND round = '{$round}'");
            array_push($scoringdialog_trick_vp, $trick_vp);
            $bonus = self::getUniqueValueFromDB("SELECT bonus_vp FROM scoring WHERE player_id = '{$player["player_id"]}' AND round = '{$round}'");
            array_push($scoringdialog_bonus, $bonus);
            $total = self::getUniqueValueFromDB("SELECT total_round FROM scoring WHERE player_id = '{$player["player_id"]}' AND round = '{$round}'");
            array_push($scoringdialog_total, $total);
            $bonustrick = self::getUniqueValueFromDB("SELECT bonus_trick FROM scoring WHERE player_id = '{$player["player_id"]}' AND round = '{$round}'");
            array_push($scoringdialog_bonustrick, $bonustrick);
            $bonusrascal = self::getUniqueValueFromDB("SELECT bonus_rascal FROM scoring WHERE player_id = '{$player["player_id"]}' AND round = '{$round}'");
                if($bonusrascal != 0)
                {
                    $bonusrascal = '+/- '.$bonusrascal;
                }
            array_push($scoringdialog_bonusrascal, $bonusrascal);
            $bonusloot = self::getUniqueValueFromDB("SELECT bonus_loot FROM scoring WHERE player_id = '{$player["player_id"]}' AND round = '{$round}'");
            array_push($scoringdialog_bonusloot, $bonusloot);


            array_push($scoringdialog_playernames, [
                'str' => '${player_name}',
                'args' => ['player_name' => $player["player_name"]],
                'type' => 'header'
            ]);

        

        }

        $table = [
            $scoringdialog_playernames,
            $scoringdialog_bid,
            $scoringdialog_trick,
            $scoringdialog_trick_vp,
            $scoringdialog_detail,
            $scoringdialog_bonustrick,
            $scoringdialog_bonusrascal,
            $scoringdialog_bonusloot,
            $scoringdialog_bonus,
            $scoringdialog_total,
        ];

        
        $this->notifyAllPlayers("tableWindow", '', array(
            "id" => 'finalScoring',
            "title" => clienttranslate("Round").' '.$round,
            "table" => $table,
            "closing" => clienttranslate("Close")
        ));

        
        self::notifyAllPlayers( 'simplePause', '', [ 'time' => 3000] );





    }


     /// LOGS

    function getLogsType($card_id)
    {
        $type = 0;
        $card_type = self::getUniqueValueFromDB("SELECT card_type FROM card WHERE card_id='{$card_id}'");
        $card_type_arg = intval(self::getUniqueValueFromDB("SELECT card_type_arg FROM card WHERE card_id='{$card_id}'"));

        
        if ($card_type == 'green')
        {
            $type = 100;
            $type = $type + $card_type_arg;
        }

        elseif ($card_type == 'purple')
        {
            $type = 200;
            $type = $type + $card_type_arg;
        }

        elseif ($card_type == 'yellow')
        {
            $type = 300;
            $type = $type + $card_type_arg;
        }

        elseif ($card_type == 'black')
        {
            $type = 400;
            $type = $type + $card_type_arg;
        }

        else
        {
            $type = 500;
        }

        ///////////////////////////

        if (($type > 100) && ($type < 200)) {
            $dizaine_et_unite = $type % 100;
            $variable = ($dizaine_et_unite-1)*-100;
            
            return "<div class='icon_log' title='' style='background-position-x: {$variable}%; background-position-y: 0%;'></div>";
        }

        elseif (($type > 200) && ($type < 300)) {
            $dizaine_et_unite = $type % 100;
            $variable = ($dizaine_et_unite-1)*-100;
            
            return "<div class='icon_log' title='' style='background-position-x: {$variable}%; background-position-y: -100%;'></div>";
        }

        elseif (($type > 300) && ($type < 400)) {
            $dizaine_et_unite = $type % 100;
            $variable = ($dizaine_et_unite-1)*-100;
            
            return "<div class='icon_log' title='' style='background-position-x: {$variable}%; background-position-y: -200%;'></div>";
        }

        elseif (($type > 400) && ($type < 500)) {
            $dizaine_et_unite = $type % 100;
            $variable = ($dizaine_et_unite-1)*-100;
            
            return "<div class='icon_log' title='' style='background-position-x: {$variable}%; background-position-y: -300%;'></div>";
        }

        else
        {
            if ($card_type == 'pirate')
            {
                return "<div class='icon_log' title='' style='background-position-x: 0%; background-position-y: -400%;'></div>";
            }

            if ($card_type == 'mermaid')
            {
                return "<div class='icon_log' title='' style='background-position-x: -200%; background-position-y: -400%;'></div>";
            }

            if ($card_type == 'escape')
            {
                return "<div class='icon_log' title='' style='background-position-x: -100%; background-position-y: -400%;'></div>";
            }

            if ($card_type == 'skull_king')
            {
                return "<div class='icon_log' title='' style='background-position-x: -300%; background-position-y: -400%;'></div>";
            }

            if ($card_type == 'tigress')
            {
                if (game::$instance->getGameStateValue("tigress_role") == 1) {
                    return "<div class='icon_log' title='' style='background-position-x: 0%; background-position-y: -400%;'></div>";
                }
                if (game::$instance->getGameStateValue("tigress_role") == 2) {
                    return "<div class='icon_log' title='' style='background-position-x: -100%; background-position-y: -400%;'></div>";
                }
                
            }

            if ($card_type == 'kraken')
            {
                return "<div class='icon_log' title='' style='background-position-x: -400%; background-position-y: -400%;'></div>";
            }

            if ($card_type == 'white_whale')
            {
                return "<div class='icon_log' title='' style='background-position-x: -500%; background-position-y: -400%;'></div>";
            }

            if ($card_type == 'loot')
            {
                return "<div class='icon_log' title='' style='background-position-x: -600%; background-position-y: -400%;'></div>";
            }
        
        
        }
        
       
    }

    function getLogsSpecial($type)
    {
        if($type ==  'kraken')
        {
            return "<div class='icon_log' title='' style='background-position-x: -400%; background-position-y: -400%;'></div>";
        }

        if($type == 'white_whale')
        {
            return "<div class='icon_log' title='' style='background-position-x: -500%; background-position-y: -400%;'></div>";
        }
               
       
    }

    function logBonus($bonus)
    {
        $count = count($bonus);

        if($count >=2)
        {
            $player_id = $bonus[0];
            $player_name= self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id = '{$player_id}'");

            array_shift($bonus);
            foreach($bonus as $info)
            {
                if($info == 1)
                {
                    $log = "<div class='icon_log' title='' style='background-position-x: -1300%; background-position-y: 0%;'></div>";
                    game::$instance->notifyAllPlayers(
                    'message',
                    clienttranslate('${player_name} wins ${log} <b>(+10 Bonus)</b>'),
                    array(
                        'player_name' => $player_name,
                        'log' => $log,

                    )
                    );

                }

                if($info == 2)
                {
                    $log = "<div class='icon_log' title='' style='background-position-x: -1300%; background-position-y: -100%;'></div>";
                    game::$instance->notifyAllPlayers(
                    'message',
                    clienttranslate('${player_name} wins ${log} <b>(+10 Bonus)</b>'),
                    array(
                        'player_name' => $player_name,
                        'log' => $log,

                    )
                    );

                }

                if($info == 3)
                {
                    $log = "<div class='icon_log' title='' style='background-position-x: -1300%; background-position-y: -200%;'></div>";
                    game::$instance->notifyAllPlayers(
                    'message',
                    clienttranslate('${player_name} wins ${log} <b>(+10 Bonus)</b>'),
                    array(
                        'player_name' => $player_name,
                        'log' => $log,

                    )
                    );

                }

                if($info == 4)
                {
                    $log = "<div class='icon_log' title='' style='background-position-x: -1300%; background-position-y: -300%;'></div>";
                    game::$instance->notifyAllPlayers(
                    'message',
                    clienttranslate('${player_name} wins ${log} <b>(+20 Bonus)</b>'),
                    array(
                        'player_name' => $player_name,
                        'log' => $log,

                    )
                    );

                }

                if($info == 5)
                {
                    $log = "<div class='icon_log' title='' style='background-position-x: 0%; background-position-y: -400%;'></div>";  //pirate
                    $log1 = "<div class='icon_log' title='' style='background-position-x: -200%; background-position-y: -400%;'></div>"; //sirene
                    game::$instance->notifyAllPlayers(
                    'message',
                    clienttranslate('${player_name} captures ${log1} with ${log} <b>(+20 Bonus)</b>'),
                    array(
                        'player_name' => $player_name,
                        'log1' => $log1,
                        'log' => $log,

                    )
                    );

                }

                if($info == 6)
                {
                    $log = "<div class='icon_log' title='' style='background-position-x: 0%; background-position-y: -400%;'></div>";  //pirate
                    $log1 = "<div class='icon_log' title='' style='background-position-x: -300%; background-position-y: -400%;'></div>"; //skull
                    game::$instance->notifyAllPlayers(
                    'message',
                    clienttranslate('${player_name} captures ${log} with ${log1} <b>(+30 Bonus)</b>'),
                    array(
                        'player_name' => $player_name,
                        'log1' => $log1,
                        'log' => $log,

                    )
                    );

                }

                if($info == 7)
                {
                    $log = "<div class='icon_log' title='' style='background-position-x: -200%; background-position-y: -400%;'></div>"; //sirene
                    $log1 = "<div class='icon_log' title='' style='background-position-x: -300%; background-position-y: -400%;'></div>"; //skull
                    game::$instance->notifyAllPlayers(
                    'message',
                    clienttranslate('${player_name} captures ${log1} with ${log} <b>(+40 Bonus)</b>'),
                    array(
                        'player_name' => $player_name,
                        'log1' => $log1,
                        'log' => $log,

                    )
                    );

                }






            }
          
        }


    }

    

    ///////////////////////////////////////////////////////////////////////////////// 
    //     _____  _                                    _   _                 
    //    |  __ \| |                                  | | (_)                
    //    | |__) | | __ _ _   _  ___ _ __    __ _  ___| |_ _  ___  _ __  ___ 
    //    |  ___/| |/ _` | | | |/ _ \ '__|  / _` |/ __| __| |/ _ \| '_ \/ __|
    //    | |    | | (_| | |_| |  __/ |    | (_| | (__| |_| | (_) | | | \__ \
    //    |_|    |_|\__,_|\__, |\___|_|     \__,_|\___|\__|_|\___/|_| |_|___/
    //                     __/ |                                             
    //                    |___/                                              
    /////////////////////////////////////////////////////////////////////////////////


    public function actSelect(string $arg1)
    {

        if ($this->gamestate->state()['name'] == "playerTurnMulti") {

            $player_id = $this->getCurrentPlayerId();
            $player_name = self::getPlayerNameById($player_id);

            $explode = explode('_', $arg1);
            $bet = $explode[1];

            game::$instance->DbQuery("UPDATE player set player_bid = $bet WHERE player_id = '{$player_id}'");

            $this->gamestate->nextPrivateState($player_id, "confirmbid");
        } else {
            self::checkArgs($arg1);

            $pending =  self::getObjectFromDB("SELECT* FROM pending order by id desc limit 1");
            $this->callPending($pending, true, $arg1);
            self::DbQuery("DELETE FROM pending WHERE id=" . $pending['id']);
            //$this->giveExtraTime(self::getActivePlayerId());
            $this->gamestate->nextState('next');
        }
    }

    public function actButton(string $arg1)
    {

        self::checkArgs($arg1);

        $pending =  self::getObjectFromDB("SELECT* FROM pending order by id desc limit 1");
        $this->callPending($pending, true, $arg1);
        self::DbQuery("DELETE FROM pending WHERE id=" . $pending['id']);
        //$this->giveExtraTime(self::getActivePlayerId());
        $this->gamestate->nextState('next');
    }


    public function actConfirmBid(string $arg1)
    {
        if ($arg1 == 'no') {
            $player_id = $this->getCurrentPlayerId();
            $player_name = self::getPlayerNameById($player_id);

            game::$instance->DbQuery("UPDATE player set player_bid = -1 WHERE player_id = '{$player_id}'");

            $this->gamestate->nextPrivateState($player_id, "backtochoosebid");
        }

        if ($arg1 == 'yes') {
            $player_id = $this->getCurrentPlayerId();
            $player_name = self::getPlayerNameById($player_id);

            game::$instance->DbQuery("UPDATE player set player_bid_validated = 1 WHERE player_id = '{$player_id}'");

            $this->giveExtraTime($player_id);
            $this->gamestate->setPlayerNonMultiactive($player_id, 'next');
        }
    }

    public function actValidate_Multi_Bendt(string $arg1)
    {

        $pending =  self::getObjectFromDB("SELECT* FROM pending order by id desc limit 1");
        $this->callPending($pending, true, $arg1);
        self::DbQuery("delete from pending where id=" . $pending['id']);
        $this->gamestate->nextState('next');
    }


    #[CheckAction(false)]
    public function actShowLastScore(int $arg1)
    {
        
        $currentplayer_id = $this->getCurrentPlayerId();
        if($arg1 == 0)
        {
            $round = $this->getGameStateValue("round_nb");
        }

        else
        {
            $round = $arg1;
        }

        
        
        $scoringdialog_playernames = [''];
        $scoringdialog_cards = array(clienttranslate("Cards Played"));
        $scoringdialog_bid = array(clienttranslate("Bid"));
        $scoringdialog_trick = array(clienttranslate("Tricks Won"));
        $scoringdialog_trick_vp = array(clienttranslate("Tricks Score"));
        $scoringdialog_bonus = array(clienttranslate("Bonus Validated"));
        $scoringdialog_total = array(clienttranslate("Round Total"));
        $scoringdialog_detail = array(clienttranslate("Bonus Details:"));
        $scoringdialog_bonustrick = array(clienttranslate("Tricks Bonus:"));
        $scoringdialog_bonusrascal = array(clienttranslate("Rascal Bonus"));
        $scoringdialog_bonusloot = array(clienttranslate("Loot Card Bonus"));

        $player_info = $this->loadPlayersBasicInfos();

        $bid_not_validated = self::getObjectListFromDB( "SELECT player_id id FROM player WHERE player_bid_validated = 0", true );

        if($round < $this->getGameStateValue("round_nb"))
        {

            foreach ($player_info as $player) {

            $cards = self::getUniqueValueFromDB("SELECT cards FROM scoring WHERE player_id = '{$player["player_id"]}' AND round = '{$round}'");
            array_push($scoringdialog_cards, $cards);
            $bid = self::getUniqueValueFromDB("SELECT bid FROM scoring WHERE player_id = '{$player["player_id"]}' AND round = '{$round}'");
            array_push($scoringdialog_bid, $bid);
            $trick = self::getUniqueValueFromDB("SELECT tricks FROM scoring WHERE player_id = '{$player["player_id"]}' AND round = '{$round}'");
            array_push($scoringdialog_trick, $trick);
            $trick_vp = self::getUniqueValueFromDB("SELECT tricks_vp FROM scoring WHERE player_id = '{$player["player_id"]}' AND round = '{$round}'");
            array_push($scoringdialog_trick_vp, $trick_vp);
            $bonus = self::getUniqueValueFromDB("SELECT bonus_vp FROM scoring WHERE player_id = '{$player["player_id"]}' AND round = '{$round}'");
            array_push($scoringdialog_bonus, $bonus);
            $total = self::getUniqueValueFromDB("SELECT total_round FROM scoring WHERE player_id = '{$player["player_id"]}' AND round = '{$round}'");
            array_push($scoringdialog_total, $total);

            $bonustrick = self::getUniqueValueFromDB("SELECT bonus_trick FROM scoring WHERE player_id = '{$player["player_id"]}' AND round = '{$round}'");
            array_push($scoringdialog_bonustrick, $bonustrick);
            $bonusrascal = self::getUniqueValueFromDB("SELECT bonus_rascal FROM scoring WHERE player_id = '{$player["player_id"]}' AND round = '{$round}'");
                if($bonusrascal != 0)
                {
                    $bonusrascal = '+/- '.$bonusrascal;
                }
            array_push($scoringdialog_bonusrascal, $bonusrascal);
            $bonusloot = self::getUniqueValueFromDB("SELECT bonus_loot FROM scoring WHERE player_id = '{$player["player_id"]}' AND round = '{$round}'");
            array_push($scoringdialog_bonusloot, $bonusloot);

            array_push($scoringdialog_playernames, [
            'str' => '${player_name}',
            'args' => ['player_name' => $player["player_name"]],
            'type' => 'header'
            ]);

            }
        }

        if($round == $this->getGameStateValue("round_nb"))
        {

            

            foreach ($player_info as $player) {

            $cards = "-";
            array_push($scoringdialog_cards, $cards);

            if($bid_not_validated != null)
            {
                $bid = "-";
            }
            else
            {
                $bid = self::getUniqueValueFromDB("SELECT player_bid FROM player WHERE player_id = '{$player["player_id"]}'");
            }
            array_push($scoringdialog_bid, $bid);

            if($bid_not_validated != null)
            {
                $trick = "-";
            }
            else
            {
                $trick = self::getUniqueValueFromDB("SELECT player_tricks FROM player WHERE player_id = '{$player["player_id"]}'");
            }
            array_push($scoringdialog_trick, $trick);

            $trick_vp = "-";
            array_push($scoringdialog_trick_vp, $trick_vp);
            $bonus = "-";
            array_push($scoringdialog_bonus, $bonus);
            $total = "-";
            array_push($scoringdialog_total, $total);

            if($bid_not_validated != null)
            {
                $bonustrick = "-";
            }
            else
            {
                $bonustrick = self::getUniqueValueFromDB("SELECT player_bonus_trick FROM player WHERE player_id = '{$player["player_id"]}'");
            }
            array_push($scoringdialog_bonustrick, $bonustrick);

            if($bid_not_validated != null)
            {
                $bonusrascal = "-";
            }
            else
            {
                $bonusrascal = self::getUniqueValueFromDB("SELECT player_bonus_rascal FROM player WHERE player_id = '{$player["player_id"]}'");
                if($bonusrascal != 0)
                {
                    $bonusrascal = '+/- '.$bonusrascal;
                }
            }
            array_push($scoringdialog_bonusrascal, $bonusrascal);

            if($bid_not_validated != null)
            {
                $bonusloot = "-";
            }
            else
            {
                $bonusloot = self::getUniqueValueFromDB("SELECT player_bonus_loot FROM player WHERE player_id = '{$player["player_id"]}'");
            }
            array_push($scoringdialog_bonusloot, $bonusloot);

            array_push($scoringdialog_playernames, [
            'str' => '${player_name}',
            'args' => ['player_name' => $player["player_name"]],
            'type' => 'header'
            ]);

            }

        }

        



        $table = [
            $scoringdialog_playernames,
            $scoringdialog_bid,
            $scoringdialog_trick,
            $scoringdialog_trick_vp,
            $scoringdialog_detail,
            $scoringdialog_bonustrick,
            $scoringdialog_bonusrascal,
            $scoringdialog_bonusloot,
            $scoringdialog_bonus,
            $scoringdialog_total,
        ];

        $this->notifyPlayer($currentplayer_id,"tableWindow", '', array(
            
            "id" => 'finalScoring',
            "title" => clienttranslate("Round").' '.$round,
            "table" => $table,
            "closing" => clienttranslate("Close")
        ));

        $this->notifyPlayer($currentplayer_id,"scoreButton", '', array(
            "round" => $this->getGameStateValue("round_nb"),
            "viewround" => $round,
           
        ));

    

        
    }



    ///////////////////////////////////////////////////////////////////////////////// 
    //     _____                             _        _                                                    _       
    //    / ____|                           | |      | |                                                  | |      
    //    | |  __  __ _ _ __ ___   ___   ___| |_ __ _| |_ ___    __ _ _ __ __ _ _   _ _ __ ___   ___ _ __ | |_ ___ 
    //    | | |_ |/ _` | '_ ` _ \ / _ \ / __| __/ _` | __/ _ \  / _` | '__/ _` | | | | '_ ` _ \ / _ \ '_ \| __/ __|
    //    | |__| | (_| | | | | | |  __/ \__ \ || (_| | ||  __/ | (_| | | | (_| | |_| | | | | | |  __/ | | | |_\__ \
    //     \_____|\__,_|_| |_| |_|\___| |___/\__\__,_|\__\___|  \__,_|_|  \__, |\__,_|_| |_| |_|\___|_| |_|\__|___/
    //                                                                    __/ |                                   
    //                                                                   |___/                                    
    ///////////////////////////////////////////////////////////////////////////////// 


    public function argBid($player)
    {
        $args = array();

        $args["selectable"][$player] = array();

        $round = $this->getGameStateValue("round_max_bid");

        for ($i = 0; $i <= $round; $i++) {
            $args["selectable"][$player][] = 'bid_' . $i;
        }



        return $args;
    }

    public function argConfirm($player)
    {
        $args = array();

        $args["selectable"][$player] = array();
        $args["selected"][$player] = array();
        $args["buttons"][$player] = array();

        $bet = self::getUniqueValueFromDB("SELECT player_bid FROM player WHERE player_id={$player}");

        $args["selected"][$player][] = 'bid_' . $bet;

        $args["buttons"][$player][] = 'yes';
        $args["buttons"][$player][] = 'no';

        return $args;
    }


    public function argPlayerTurn()
    {
        $pending =  self::getObjectFromDB("SELECT* FROM pending ORDER BY id DESC LIMIT 1");
        $arg = $this->callPending($pending, false);

        return $arg;
    }


    ///////////////////////////////////////////////////////////////////////////////// 
    //      _____                            _        _                    _   _                 
    //     / ____|                          | |      | |                  | | (_)                
    //    | |  __  __ _ _ __ ___   ___   ___| |_ __ _| |_ ___    __ _  ___| |_ _  ___  _ __  ___ 
    //    | | |_ |/ _` | '_ ` _ \ / _ \ / __| __/ _` | __/ _ \  / _` |/ __| __| |/ _ \| '_ \/ __|
    //    | |__| | (_| | | | | | |  __/ \__ \ || (_| | ||  __/ | (_| | (__| |_| | (_) | | | \__ \
    //     \_____|\__,_|_| |_| |_|\___| |___/\__\__,_|\__\___|  \__,_|\___|\__|_|\___/|_| |_|___/
    //                                                                                       
    /////////////////////////////////////////////////////////////////////////////////     


    public function callPending($pending, $execute, $arg1 = null, $arg2 = null)
    {

        $obj = $this;
        if ($pending['player_id'] != null) {
            $obj = new Pending($pending['player_id']);
        }

        $fname = "";
        if (!$execute) {
            $fname .= "arg";
        }
        $fname .= $pending['function'];

        $ret = null;
        if (method_exists($obj, $fname)) {
            $ret = $obj->$fname($pending['arg'], $pending['arg2'], $arg1, $arg2);
        }

        return $ret;
    }


    public function stPending()
    {
        if (game::$instance->getGameStateValue("end_of_round") != 1) {
            $pending =  self::getObjectFromDB("SELECT * FROM pending ORDER BY id DESC LIMIT 1");
            if ($pending == null) {
                $this->gamestate->nextState('end');
            } else {
                $args = $this->callPending($pending, false);

                ////////////// attention changement car si on donne la main a un autre joueur sans arg l'id de l active player ne change pas 
                if ($pending['player_id'] != self::getActivePlayerId()) {


                    //change active player      
                    $this->gamestate->changeActivePlayer($pending['player_id']);
                    $this->gamestate->nextState('same');
                } else if ($args == null || (count($args['selectable']) == 0 && count($args['buttons']) == 0)) {
                    //no args required, execute
                    $this->callPending($pending, true);
                    self::DbQuery("DELETE FROM pending WHERE id=" . $pending['id']);
                    $this->gamestate->nextState('same');
                } else {


                    $this->gamestate->nextState('player');
                }
            }
        } else {
            game::$instance->setGameStateValue("end_of_round", 0);
            $this->gamestate->nextState('multi');
        }
    }


    public function st_MultiPlayerActivation()
    {
        $new_round_nb = $this->getGameStateValue("round_nb") + 1;
        $this->setGameStateValue("round_nb", $new_round_nb);

        $players = self::getObjectListFromDB("SELECT player_id id FROM player", true);
        $nbre_players = count($players);


        if ((($new_round_nb == 10) && ($nbre_players == 8)) || (($new_round_nb == 9) && ($nbre_players == 8))) {
            $nb_cards = 8;
        } elseif (($new_round_nb == 10) && ($nbre_players == 7)) {
            $nb_cards = 9;
        } else {
            $nb_cards = $new_round_nb;
        }

        $this->setGameStateValue("round_max_bid", $nb_cards);



        foreach ($players as $player) {

            $this->db_card->pickCards($nb_cards, 'deck', $player);

            $cards = $this->db_card->getPlayerHand($player);

            game::$instance->notifyPlayer(
                $player,
                'drawCards',
                '',
                array(

                    'cards' => $cards,

                )
            );
        }

        game::$instance->notifyAllPlayers(
            'message',
            clienttranslate('${message}'),
            array(
                'message' =>  [  'log' => '<div class = "notif_newRound">${round} ${nb}/10</div>',
                                'args'=> ['round' => clienttranslate('Round'), 'nb'=>$new_round_nb, 'i18n' => ['round'] ]
                            ],
            
            )
        );



        game::$instance->gamestate->setAllPlayersMultiactive();
        game::$instance->gamestate->initializePrivateStateForAllActivePlayers();
    }

    public function stDisplayBid()
    {
        game::$instance->notifyAllPlayers(
            'yohoho',
            '',
            array()
        );

        

        $bids = self::getObjectListFromDB("SELECT player_id id, player_bid bid, player_tricks tricks FROM player");

        game::$instance->notifyAllPlayers(
            'showBids',
            '',
            array(
                'bids' => $bids,
            )
        );


        self::notifyAllPlayers('simplePause', '', ['time' => 1500]);
        $this->gamestate->nextState('next');
    }

    ///////////////////////////////////////////////////////////////////////////////// 
    //     _____  ____                                    _      
    //    |  __ \|  _ \                                  | |     
    //    | |  | | |_) |  _   _ _ __   __ _ _ __ __ _  __| | ___ 
    //    | |  | |  _ <  | | | | '_ \ / _` | '__/ _` |/ _` |/ _ \
    //    | |__| | |_) | | |_| | |_) | (_| | | | (_| | (_| |  __/
    //    |_____/|____/   \__,_| .__/ \__, |_|  \__,_|\__,_|\___|
    //                         | |     __/ |                     
    //                         |_|    |___/                      
    /////////////////////////////////////////////////////////////////////////////////  


    public function upgradeTableDb($from_version) {}




    /////////////////////////////////////////////////////////////////////////////////
    //    ______               _     _      
    //   |___  /              | |   (_)     
    //      / / ___  _ __ ___ | |__  _  ___ 
    //     / / / _ \| '_ ` _ \| '_ \| |/ _ \
    //    / /_| (_) | | | | | | |_) | |  __/
    //   /_____\___/|_| |_| |_|_.__/|_|\___|
    //                                   
    /////////////////////////////////////////////////////////////////////////////////     

    protected function zombieTurn(array $state, int $active_player): void
    {
        $state_name = $state["name"];

        if ($state["type"] === "activeplayer") {
            switch ($state_name) {
                default: {
                        $player_id = $this->getActivePlayerId();
                        self::DbQuery("DELETE FROM pending WHERE player_id = {$player_id}");
                        $this->gamestate->nextState("zombiePass");
                        break;
                    }
            }

            return;
        }

        // Make sure player is in a non-blocking status for role turn.
        if ($state["type"] === "multipleactiveplayer") {
            $this->gamestate->setPlayerNonMultiactive($active_player, '');
            return;
        }

        throw new \feException("Zombie mode not supported at this game state: \"{$state_name}\".");
    }
}
