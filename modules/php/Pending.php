<?php

namespace Bga\Games\skullking;   // ATTENTION NOM DU JEU
use APP_GameClass;

require_once 'Pirates.php'; // Inclure le fichier contenant les fonctions

class Pending extends APP_GameClass
{
    use PiratesTrait; // ATTENTION

    public function __construct($player_id)
    {
        $this->player_id = $player_id;
        $p = self::getObjectFromDB("SELECT * FROM player WHERE player_id = {$player_id}");
        $this->player_no = $p['player_no'];
        $this->player_id = $p['player_id'];
        $this->player_name = $p['player_name'];
        $this->player_score = $p['player_score'];
        $this->player_color = $p['player_color'];


        $this->color = ['green', 'purple', 'yellow', 'black'];
        $this->special = ['escape', 'mermaid', 'pirate', 'tigress', 'skull_king'];

        /// PREFERENCE DE CONFIRMATION

        $this->player_pref_confirm = game::$instance->getUniqueValueFromDB("SELECT pgp_value FROM bga_user_preferences WHERE pgp_player='{$this->player_id}' AND pgp_preference_id = 100");
    }

    function argPlayCard($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} must play a card');
        $ret['titleyou'] = clienttranslate('${you} must play a card');

        $color_request = 0;

        $player_turn = self::getUniqueValueFromDB("SELECT player_turn FROM player WHERE player_id={$this->player_id}");
        $all_cards = self::getObjectListFromDB("SELECT card_id FROM card WHERE card_location = 'hand' AND card_location_arg = '{$this->player_id}'", true);

        if (($player_turn != 1) && ($all_cards != null)) // si le joueur n'a pas joué son tour ET qu'il a encore des cartes en main
        {

            if (game::$instance->getGameStateValue("requested_color") != 0) // si couleur requise on recupere celle-ci
            {
                $color_request = $this->color[game::$instance->getGameStateValue("requested_color") - 1];
            }


            $green_cards = self::getObjectListFromDB("SELECT card_id FROM card WHERE card_location = 'hand' AND card_location_arg = '{$this->player_id}' AND card_type = 'green'", true);
            $purple_cards = self::getObjectListFromDB("SELECT card_id FROM card WHERE card_location = 'hand' AND card_location_arg = '{$this->player_id}' AND card_type = 'purple'", true);
            $yellow_cards = self::getObjectListFromDB("SELECT card_id FROM card WHERE card_location = 'hand' AND card_location_arg = '{$this->player_id}' AND card_type = 'yellow'", true);
            $black_cards = self::getObjectListFromDB("SELECT card_id FROM card WHERE card_location = 'hand' AND card_location_arg = '{$this->player_id}' AND card_type = 'black'", true);
            $special_cards = self::getObjectListFromDB("SELECT card_id FROM card WHERE card_location = 'hand' AND card_location_arg = '{$this->player_id}' AND card_type != 'green' AND card_type != 'purple'AND card_type != 'yellow' AND card_type != 'black'", true);


            if ($color_request == 0)    // si la couleur demandée n'a pas été encore définie dans le tour le joueur peut jouer ce qu'il veut
            {
                foreach ($all_cards as $card) {
                    $ret["selectable"][] = 'my_cards_item_' . $card;
                }
            } else    // si la couleur demandée a été définie dans le tour 
            {
                $cards = [];
                $player_color_request = 0; // variable pour savoir si le joueur à la couleur demandée

                foreach ($this->color as $color) {
                    if (($color == $color_request) && (${$color . '_cards'} != null)) // si le joueur à au moins une carte de la couleur demandée
                    {
                        $cards = array_merge(${$color . '_cards'}, $special_cards); // le joueur ne peut jouer que la couleur demandée ou les cartes spéciales
                        foreach ($cards as $card) {
                            $ret["selectable"][] = 'my_cards_item_' . $card;
                        }

                        $player_color_request = 1; //le joueur a la couleur demandée
                    }
                }

                if ($player_color_request == 0) //le joueur n'a pas la couleur demandée, il peut jouer ce qu'il veut dans ses all_cards

                    foreach ($all_cards as $card) {
                        $ret["selectable"][] = 'my_cards_item_' . $card;
                    }
            }
        }

        return $ret;
    }

    function PlayCard($parg1, $parg2, $varg1, $varg2)
    {
        if ($varg1 == null) {
            $player_turn = self::getUniqueValueFromDB("SELECT player_turn FROM player WHERE player_id='{$this->player_id}'");
            $all_cards = self::getObjectListFromDB("SELECT card_id FROM card WHERE card_location = 'hand' AND card_location_arg = '{$this->player_id}'", true);

            if (($player_turn == 1) && ($all_cards != null))  // fin de tour 
            {
                game::$instance->addPending($this->player_id, "EndOfTurn");
            }

            if ($all_cards == null)  // fin de tour ET fin de round
            {
                game::$instance->addPending($this->player_id, "EndOfRound");
            }
        } else {
            // placement de card sur la table
            $explode = explode('_', $varg1);
            $card_id = end($explode);
            $type = self::getUniqueValueFromDB("SELECT card_type FROM card WHERE card_id='{$card_id}'");

            if ($this->player_pref_confirm == 1) {

                if ($type != 'tigress') {

                    if ($type == 'loot') {
                        if (game::$instance->getGameStateValue("loot_1_id_play") == 0) {
                            game::$instance->setGameStateValue("loot_1_id_play", $this->player_id);
                            $inc = game::$instance->getGameStateValue("loot_nb_in_turn") + 1;
                            game::$instance->setGameStateValue("loot_nb_in_turn", $inc);
                        } else {
                            game::$instance->setGameStateValue("loot_2_id_play", $this->player_id);
                            $inc = game::$instance->getGameStateValue("loot_nb_in_turn") + 1;
                            game::$instance->setGameStateValue("loot_nb_in_turn", $inc);
                        }
                        
                    }

                    $card_before = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg FROM card WHERE card_id = {$card_id}");
                    game::$instance->db_card->moveCard($card_id, 'table', $this->player_id);
                    $card_after = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg FROM card WHERE card_id = {$card_id}");



                    ///////////PLAY + LOG ////////////
                    $card_type = self::getUniqueValueFromDB("SELECT card_type FROM card WHERE card_id='{$card_id}'");
                    $card_type_arg = intval(self::getUniqueValueFromDB("SELECT card_type_arg FROM card WHERE card_id='{$card_id}'"));
                    $log = game::$instance->getLogsType($card_id);
                    
                    if($card_type != 'pirate')
                    {
                    game::$instance->notifyAllPlayers(
                        'playCard',
                        clienttranslate('${player_name} plays ${card}'),
                        array(
                            'player_name' => $this->player_name,
                            'card_before' => $card_before,
                            'card_after' => $card_after,
                            'card'=> $log,


                        )
                    );
                    }

                    else
                    {

                        game::$instance->notifyAllPlayers(
                        'playCard',
                        clienttranslate('${player_name} plays ${card} ${name}'),
                        array(
                            'player_name' => $this->player_name,
                            'card_before' => $card_before,
                            'card_after' => $card_after,
                            'card'=> $log,
                            'name' =>    [
                            'log' => '<b style="color: #FF0000;">${pirate_name}</b>',
                            'args' => ['pirate_name' => game::$instance->_PIRATE_CARDS[$card_type_arg]['name'], 'i18n' => ['pirate_name']]
                        ],


                        )
                    );


                    }

                    ////////////////////////////////////




                    // calcul si requested_color doit être modifiée


                    if (game::$instance->getGameStateValue("requested_color_cannot_change") == 0) {
                        if (($type == 'green') || ($type == 'purple') || ($type == 'yellow') || ($type == 'black')) {
                            game::$instance->setGameStateValue("requested_color_cannot_change", 1);
                            $index = array_search($type, $this->color);
                            game::$instance->setGameStateValue("requested_color", $index + 1);
                        }

                        if (($type == 'pirate') || ($type == 'mermaid') || ($type == 'skull_king')) {
                            game::$instance->setGameStateValue("requested_color_cannot_change", 1);
                        }
                    }




                    // end function
                    game::$instance->DbQuery("UPDATE player set player_turn = player_turn + 1 WHERE player_id = '{$this->player_id}'");
                    game::$instance->giveExtraTime($this->player_id);
                    game::$instance->addPendingFirst($this->player_id, "PlayCard");
                } else {
                    game::$instance->addPending($this->player_id, "PlayTigress", $varg1);
                }
            }

            if ($this->player_pref_confirm == 2) {

                if ($type == 'tigress') {
                    game::$instance->addPending($this->player_id, "PlayTigress", $varg1);
                }

                if ($type != 'tigress') {
                    game::$instance->addPending($this->player_id, "PlayCardConfirm", $varg1);
                }
            }
        }
    }

    function argPlayCardConfirm($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} must play a card');
        $ret['titleyou'] = clienttranslate('${you} must confirm');

        $ret["selected"][] = $parg1;

        $ret['buttons'][] = 'yes';
        $ret['buttons'][] = 'no';

        return $ret;
    }

    function PlayCardConfirm($parg1, $parg2, $varg1, $varg2)
    {
        if ($varg1 == 'no') {
            game::$instance->addPending($this->player_id, "PlayCard");
        }

        if ($varg1 == 'yes') {
            $explode = explode('_', $parg1);
            $card_id = end($explode);
            $type = self::getUniqueValueFromDB("SELECT card_type FROM card WHERE card_id='{$card_id}'");

            if ($type == 'loot') {
                if (game::$instance->getGameStateValue("loot_1_id_play") == 0) {
                    game::$instance->setGameStateValue("loot_1_id_play", $this->player_id);
                    $inc = game::$instance->getGameStateValue("loot_nb_in_turn") + 1;
                    game::$instance->setGameStateValue("loot_nb_in_turn", $inc);
                } else {
                    game::$instance->setGameStateValue("loot_2_id_play", $this->player_id);
                    $inc = game::$instance->getGameStateValue("loot_nb_in_turn") + 1;
                    game::$instance->setGameStateValue("loot_nb_in_turn", $inc);
                }
                
            }

            $card_before = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg FROM card WHERE card_id = {$card_id}");
            game::$instance->db_card->moveCard($card_id, 'table', $this->player_id);
            $card_after = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg FROM card WHERE card_id = {$card_id}");


            ///////////PLAY + LOG ////////////
            $card_type = self::getUniqueValueFromDB("SELECT card_type FROM card WHERE card_id='{$card_id}'");
            $card_type_arg = intval(self::getUniqueValueFromDB("SELECT card_type_arg FROM card WHERE card_id='{$card_id}'"));
            $log = game::$instance->getLogsType($card_id);
            
            if($card_type != 'pirate')
            {
            game::$instance->notifyAllPlayers(
                'playCard',
                clienttranslate('${player_name} plays ${card}'),
                array(
                    'player_name' => $this->player_name,
                    'card_before' => $card_before,
                    'card_after' => $card_after,
                    'card'=> $log,


                )
            );
            }

            else
            {

                game::$instance->notifyAllPlayers(
                'playCard',
                clienttranslate('${player_name} plays ${card} ${name}'),
                array(
                    'player_name' => $this->player_name,
                    'card_before' => $card_before,
                    'card_after' => $card_after,
                    'card'=> $log,
                    'name' =>    [
                    'log' => '<b style="color: #FF0000;">${pirate_name}</b>',
                    'args' => ['pirate_name' => game::$instance->_PIRATE_CARDS[$card_type_arg]['name'], 'i18n' => ['pirate_name']]
                ],


                )
            );


            }

            ////////////////////////////////////

            // calcul si requested_color doit être modifiée


            if (game::$instance->getGameStateValue("requested_color_cannot_change") == 0) {
                if (($type == 'green') || ($type == 'purple') || ($type == 'yellow') || ($type == 'black')) {
                    game::$instance->setGameStateValue("requested_color_cannot_change", 1);
                    $index = array_search($type, $this->color);
                    game::$instance->setGameStateValue("requested_color", $index + 1);
                }

                if (($type == 'pirate') || ($type == 'mermaid') || ($type == 'skull_king')) {
                    game::$instance->setGameStateValue("requested_color_cannot_change", 1);
                }
            }

            // end function
            game::$instance->DbQuery("UPDATE player set player_turn = player_turn + 1 WHERE player_id = '{$this->player_id}'");
            game::$instance->giveExtraTime($this->player_id);
            game::$instance->addPendingFirst($this->player_id, "PlayCard");
        }
    }

    function argPlayTigress($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} must play a card');
        $ret['titleyou'] = clienttranslate('${you} must choose the role of Tigress:');


        $ret['buttons'][] = 'tigress_pirate';
        $ret['buttons'][] = 'tigress_escape';
        $ret['buttons'][] = 'cancel';




        return $ret;
    }

    function PlayTigress($parg1, $parg2, $varg1, $varg2)
    {
        if ($varg1 == 'cancel') {
            game::$instance->addPending($this->player_id, "PlayCard");
        } else {

            if ($this->player_pref_confirm == 1) {

                if ($varg1 == 'tigress_pirate') {
                    if (game::$instance->getGameStateValue("requested_color_cannot_change") == 0) {
                        game::$instance->setGameStateValue("requested_color_cannot_change", 1);
                        
                    }
                    game::$instance->setGameStateValue("tigress_role", 1);
                }

                if ($varg1 == 'tigress_escape') {
                    game::$instance->setGameStateValue("tigress_role", 2);
                }

                $explode = explode('_', $parg1);
                $card_id = end($explode);
                $card_before = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg FROM card WHERE card_id = {$card_id}");
                game::$instance->db_card->moveCard($card_id, 'table', $this->player_id);
                $card_after = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg FROM card WHERE card_id = {$card_id}");

                $log = game::$instance->getLogsType($card_id);
                
                
                

                game::$instance->notifyAllPlayers(
                    'playCard',
                    clienttranslate('${player_name} plays ${card} ${name}'),
                    array(
                        'player_name' => $this->player_name,
                        'card_before' => $card_before,
                        'card_after' => $card_after,
                        'card'=> $log,
                        'name' =>    [
                        'log' => '<b style="color: #FF0000;">${pirate_name}</b>',
                        'args' => ['pirate_name' => game::$instance->_SPECIAL_CARDS["tigress"]['name2'], 'i18n' => ['pirate_name']]
                    ],


                    )
                );

                game::$instance->notifyAllPlayers(
                    'tigressRole',
                    '',
                    array(
                        'card_id' => $card_id,
                        'role' => game::$instance->getGameStateValue("tigress_role"),
                    
                    )
                );

                game::$instance->DbQuery("UPDATE player set player_turn = player_turn + 1 WHERE player_id = '{$this->player_id}'");
                game::$instance->giveExtraTime($this->player_id);
                game::$instance->addPendingFirst($this->player_id, "PlayCard");
            }

            if ($this->player_pref_confirm == 2) {
                game::$instance->addPending($this->player_id, "PlayTigressConfirm", $parg1, $varg1);
            }
        }
    }

    function argPlayTigressConfirm($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} must play a card');
        $ret['titleyou'] = clienttranslate('${you} must confirm');

        $ret["selected"][] = $parg1;

        $ret['buttons'][] = 'yes';
        $ret['buttons'][] = 'no';




        return $ret;
    }

    function PlayTigressConfirm($parg1, $parg2, $varg1, $varg2)
    {
        if ($varg1 == 'no') {
            game::$instance->addPending($this->player_id, "PlayCard");
        }

        if ($varg1 == 'yes') {

            if ($parg2 == 'tigress_pirate') {
                if (game::$instance->getGameStateValue("requested_color_cannot_change") == 0) {
                    game::$instance->setGameStateValue("requested_color_cannot_change", 1);
                    
                }
                game::$instance->setGameStateValue("tigress_role", 1);
            }

            if ($parg2 == 'tigress_escape') {
                game::$instance->setGameStateValue("tigress_role", 2);
            }

            $explode = explode('_', $parg1);
            $card_id = end($explode);
            $card_before = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg FROM card WHERE card_id = {$card_id}");
            game::$instance->db_card->moveCard($card_id, 'table', $this->player_id);
            $card_after = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg FROM card WHERE card_id = {$card_id}");


            $log = game::$instance->getLogsType($card_id);

            game::$instance->notifyAllPlayers(
                'playCard',
                clienttranslate('${player_name} plays ${card} ${name}'),
                array(
                    'player_name' => $this->player_name,
                    'card_before' => $card_before,
                    'card_after' => $card_after,
                    'card'=> $log,
                    'name' =>    [
                    'log' => '<b style="color: #FF0000;">${pirate_name}</b>',
                    'args' => ['pirate_name' => game::$instance->_SPECIAL_CARDS["tigress"]['name2'], 'i18n' => ['pirate_name']]
                    ],


                )
            );

            game::$instance->notifyAllPlayers(
                    'tigressRole',
                    '',
                    array(
                        'card_id' => $card_id,
                        'role' => game::$instance->getGameStateValue("tigress_role"),
                    
                    )
                );

            game::$instance->DbQuery("UPDATE player set player_turn = player_turn + 1 WHERE player_id = '{$this->player_id}'");
            game::$instance->giveExtraTime($this->player_id);
            game::$instance->addPendingFirst($this->player_id, "PlayCard");
        }
    }

    function argEndOfTurn($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('End of Turn');
        $ret['titleyou'] = clienttranslate('End of Turn');

        return $ret;
    }

    function EndOfTurn($parg1, $parg2, $varg1, $varg2)
    {
        // WINNER
        $winner = game::$instance->winnerOfTrick();

        $pirate_power = 0;

        $cards_played = self::getObjectListFromDB("SELECT card_id FROM card WHERE card_location = 'table'", true);
        $id_card_winner = self::getUniqueValueFromDB("SELECT card_id FROM card WHERE card_location = 'table' AND card_location_arg = '{$winner}'");
        $type_card_winner = self::getUniqueValueFromDB("SELECT card_type FROM card WHERE card_location = 'table' AND card_location_arg = '{$winner}'");
        $type_arg_card_winner = self::getUniqueValueFromDB("SELECT card_type_arg FROM card WHERE card_location = 'table' AND card_location_arg = '{$winner}'");

        $bonus = array();
        $bonus[] = $winner;

        foreach ($cards_played as $card_played) {
            if ((game::$instance->getGameStateValue("kraken") == 0) && (game::$instance->getGameStateValue("white_whale") == 0)) {
                // BONUS
                $type_card = self::getUniqueValueFromDB("SELECT card_type FROM card WHERE card_location = 'table' AND card_id = '{$card_played}'");
                $type_arg_card = self::getUniqueValueFromDB("SELECT card_type_arg FROM card WHERE card_location = 'table' AND card_id = '{$card_played}'");
                if ((($type_card == 'green') || ($type_card == 'purple') || ($type_card == 'yellow')) && ($type_arg_card == 14)) {
                    game::$instance->DbQuery("UPDATE player set player_bonus_trick = player_bonus_trick + 10 WHERE player_id = '{$winner}' ");
                    if($type_card == 'green')
                    {
                        $bonus[] = 1;
                    }

                    if($type_card == 'purple')
                    {
                        $bonus[] = 2;
                    }

                    if($type_card == 'yellow')
                    {
                        $bonus[] = 3;
                    }
                }
                if (($type_card == 'black') && ($type_arg_card == 14)) {
                    game::$instance->DbQuery("UPDATE player set player_bonus_trick = player_bonus_trick + 20 WHERE player_id = '{$winner}' ");
                    $bonus[] = 4;
                }
                if (($type_card_winner == 'pirate') && ($type_card == 'mermaid')) {
                    game::$instance->DbQuery("UPDATE player set player_bonus_trick = player_bonus_trick + 20 WHERE player_id = '{$winner}' ");
                    $bonus[] = 5;
                    //stat
                    game::$instance->incStat(1, 'mermaid_captured');
                }
                if (($type_card_winner == 'tigress') && (game::$instance->getGameStateValue("tigress_role") == 1) && ($type_card == 'mermaid')) {
                    game::$instance->DbQuery("UPDATE player set player_bonus_trick = player_bonus_trick + 20 WHERE player_id = '{$winner}' ");
                    $bonus[] = 5;
                    //stat
                    game::$instance->incStat(1, 'mermaid_captured');
                }
                if ((($type_card_winner == 'skull_king') && ($type_card == 'pirate')) || (($type_card_winner == 'skull_king') && ($type_card == 'tigress') && (game::$instance->getGameStateValue("tigress_role") == 1))) {
                    game::$instance->DbQuery("UPDATE player set player_bonus_trick = player_bonus_trick + 30 WHERE player_id = '{$winner}' ");
                    $bonus[] = 6;
                    //stat
                    game::$instance->incStat(1, 'pirate_captured');
                }
                if (($type_card_winner == 'mermaid') && ($type_card == 'skull_king')) {
                    game::$instance->DbQuery("UPDATE player set player_bonus_trick = player_bonus_trick + 40 WHERE player_id = '{$winner}' ");
                    $bonus[] = 7;
                    //stat
                    game::$instance->incStat(1, 'sk_captured');
                }

                game::$instance->db_card->moveCard($card_played, 'discard', $winner);
            }
        }

        $winner_name = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id = '{$winner}'");

        $sql = "SELECT player_no no, player_id id, player_score score, player_name name, player_color color, player_bid bid, player_bid_validated bid_validated, player_tricks tricks, player_turn turn, player_bonus_trick bonus_trick, player_bonus_rascal bonus_rascal 
            FROM player ";
        $sql .= "WHERE player_id = '{$winner}'";
        $winner_infos = $this->getObjectFromDB($sql);

        if ((game::$instance->getGameStateValue("kraken") == 0) && (game::$instance->getGameStateValue("white_whale") == 0)) {
            game::$instance->notifyAllPlayers(
                'endTrick',
                clienttranslate('${player_name} wins the trick and begins the next turn'),
                array(
                    'winner_id' => $winner,
                    'winner_infos' => $winner_infos,
                    'player_name' => $winner_name,

                )
            );

            if (game::$instance->getGameStateValue("pirate_powers_mode") == 2) {
                if ($type_card_winner == 'pirate') {
                    $pirate_power = 1;
                    game::$instance->addPending($winner, "Power" . $type_arg_card_winner, $winner);
                }
            }
        }

        if (game::$instance->getGameStateValue("kraken") == 1) {

            $cards = self::getObjectListFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg FROM card WHERE card_location = 'table'");

            foreach ($cards_played as $card_played) {
                game::$instance->db_card->moveCard($card_played, 'discard', 0);
            }

            game::$instance->notifyAllPlayers(
                'krakenEffect',
                clienttranslate('${log} destroys the trick and ${player_name} begins the next turn'),
                array(
                    'player_name' => $winner_name,
                    'cards' => $cards,
                    'log' => game::$instance->getLogsSpecial('kraken'),


                )
            );
        }

        if (game::$instance->getGameStateValue("white_whale") == 1) {
            if ($type_card_winner == 'white_whale') {

                $cards = self::getObjectListFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg FROM card WHERE card_location = 'table'");

                foreach ($cards_played as $card_played) {
                    game::$instance->db_card->moveCard($card_played, 'discard', 0);
                }


                game::$instance->notifyAllPlayers(
                    'krakenEffect',
                    clienttranslate('${log} destroys the trick because ${player_name} won the trick. ${player_name} begins the next turn'),
                    array(
                        'player_name' => $winner_name,
                        'cards' => $cards,
                        'log' => game::$instance->getLogsSpecial('white_whale'),


                    )
                );
            } else {
                foreach ($cards_played as $card_played) {
                    // BONUS
                    $type_card = self::getUniqueValueFromDB("SELECT card_type FROM card WHERE card_location = 'table' AND card_id = '{$card_played}'");
                    $type_arg_card = self::getUniqueValueFromDB("SELECT card_type_arg FROM card WHERE card_location = 'table' AND card_id = '{$card_played}'");
                    if ((($type_card == 'green') || ($type_card == 'purple') || ($type_card == 'yellow')) && ($type_arg_card == 14)) {
                        game::$instance->DbQuery("UPDATE player set player_bonus_trick = player_bonus_trick + 10 WHERE player_id = '{$winner}' ");
                        if($type_card == 'green')
                        {
                            $bonus[] = 1;
                        }

                        if($type_card == 'purple')
                        {
                            $bonus[] = 2;
                        }

                        if($type_card == 'yellow')
                        {
                            $bonus[] = 3;
                        }
                    }
                    if (($type_card == 'black') && ($type_arg_card == 14)) {
                        game::$instance->DbQuery("UPDATE player set player_bonus_trick = player_bonus_trick + 20 WHERE player_id = '{$winner}' ");
                        $bonus[] = 4;
                    }

                    game::$instance->db_card->moveCard($card_played, 'discard', $winner);
                }

                game::$instance->notifyAllPlayers(
                    'endTrick',
                    clienttranslate('${player_name} wins the trick thanks to ${log}. ${player_name} begins the next turn'),
                    array(
                        'winner_id' => $winner,
                        'winner_infos' => $winner_infos,
                        'player_name' => $winner_name,
                        'log' => game::$instance->getLogsSpecial('white_whale'),

                    )
                );
            }
        }

        game::$instance->logBonus($bonus);


        //INIT TURN
        game::$instance->setGameStateValue("requested_color", 0);
        game::$instance->setGameStateValue("requested_color_cannot_change", 0);
        game::$instance->setGameStateValue("tigress_role", 0);
        game::$instance->DbQuery("UPDATE player set player_turn = 0 ");
        game::$instance->setGameStateValue("kraken", 0);
        game::$instance->setGameStateValue("white_whale", 0);
        game::$instance->setGameStateValue("loot_nb_in_turn", 0);

        // CHANGE FIRST PLAYER TRICK
        game::$instance->setGameStateValue("first_player_trick", $winner);

        if ($pirate_power == 0) {
            $nextplayer = $winner;

            self::DbQuery("DELETE FROM `pending`;");

            // NOUVEL ORDRE PENDING
            $count_players = count(self::getObjectListFromDB("SELECT player_id id FROM player", true));
            for ($i = 1; $i <= $count_players; $i++) {

                game::$instance->addPendingFirst($nextplayer, "PlayCard");
                $nextplayer = game::$instance->getPlayerAfter($nextplayer);
            }

            game::$instance->notifyAllPlayers('message', clienttranslate('${message}'), [
            'message' => [
                'log' => '<div class="notif_newTurn">${turn}</div>',
                'args' => [
                    'turn' => clienttranslate('New Trick'),
                    'i18n' => ['turn']
                ],
                'type' => 'newRound'
            ]
            ]);
            
        }

        
    }


    function argEndOfRound($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('End of Round');
        $ret['titleyou'] = clienttranslate('End of Round');



        return $ret;
    }

    function EndOfRound($parg1, $parg2, $varg1, $varg2)
    {
        $winner = game::$instance->winnerOfTrick();

        $pirate_power = 0;

        $cards_played = self::getObjectListFromDB("SELECT card_id FROM card WHERE card_location = 'table'", true);
        $id_card_winner = self::getUniqueValueFromDB("SELECT card_id FROM card WHERE card_location = 'table' AND card_location_arg = '{$winner}'");
        $type_card_winner = self::getUniqueValueFromDB("SELECT card_type FROM card WHERE card_location = 'table' AND card_location_arg = '{$winner}'");
        $type_arg_card_winner = self::getUniqueValueFromDB("SELECT card_type_arg FROM card WHERE card_location = 'table' AND card_location_arg = '{$winner}'");

        $bonus = array();
        $bonus[] = $winner;

        foreach ($cards_played as $card_played) {
            if ((game::$instance->getGameStateValue("kraken") == 0) && (game::$instance->getGameStateValue("white_whale") == 0)) {
                // BONUS
                $type_card = self::getUniqueValueFromDB("SELECT card_type FROM card WHERE card_location = 'table' AND card_id = '{$card_played}'");
                $type_arg_card = self::getUniqueValueFromDB("SELECT card_type_arg FROM card WHERE card_location = 'table' AND card_id = '{$card_played}'");
                if ((($type_card == 'green') || ($type_card == 'purple') || ($type_card == 'yellow')) && ($type_arg_card == 14)) {
                    game::$instance->DbQuery("UPDATE player set player_bonus_trick = player_bonus_trick + 10 WHERE player_id = '{$winner}' ");
                    if($type_card == 'green')
                    {
                        $bonus[] = 1;
                    }

                    if($type_card == 'purple')
                    {
                        $bonus[] = 2;
                    }

                    if($type_card == 'yellow')
                    {
                        $bonus[] = 3;
                    }
                }
                if (($type_card == 'black') && ($type_arg_card == 14)) {
                    game::$instance->DbQuery("UPDATE player set player_bonus_trick = player_bonus_trick + 20 WHERE player_id = '{$winner}' ");
                    $bonus[] = 4;
                }
                if (($type_card_winner == 'pirate') && ($type_card == 'mermaid')) {
                    game::$instance->DbQuery("UPDATE player set player_bonus_trick = player_bonus_trick + 20 WHERE player_id = '{$winner}' ");
                    $bonus[] = 5;
                    //stat
                    game::$instance->incStat(1, 'mermaid_captured');
                }
                if (($type_card_winner == 'tigress') && (game::$instance->getGameStateValue("tigress_role") == 1) && ($type_card == 'mermaid')) {
                    game::$instance->DbQuery("UPDATE player set player_bonus_trick = player_bonus_trick + 20 WHERE player_id = '{$winner}' ");
                    $bonus[] = 5;
                    //stat
                    game::$instance->incStat(1, 'mermaid_captured');
                }
                if ((($type_card_winner == 'skull_king') && ($type_card == 'pirate')) || (($type_card_winner == 'skull_king') && ($type_card == 'tigress') && (game::$instance->getGameStateValue("tigress_role") == 1))) {
                    game::$instance->DbQuery("UPDATE player set player_bonus_trick = player_bonus_trick + 30 WHERE player_id = '{$winner}' ");
                    $bonus[] = 6;
                     //stat
                    game::$instance->incStat(1, 'pirate_captured');
                }
                if (($type_card_winner == 'mermaid') && ($type_card == 'skull_king')) {
                    game::$instance->DbQuery("UPDATE player set player_bonus_trick = player_bonus_trick + 40 WHERE player_id = '{$winner}' ");
                    $bonus[] = 7;
                    //stat
                    game::$instance->incStat(1, 'sk_captured');
                }

                game::$instance->db_card->moveCard($card_played, 'discard', $winner);
            }
        }

        $winner_name = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id = '{$winner}'");

        $sql = "SELECT player_no no, player_id id, player_score score, player_name name, player_color color, player_bid bid, player_bid_validated bid_validated, player_tricks tricks, player_turn turn, player_bonus_trick bonus_trick, player_bonus_rascal bonus_rascal 
            FROM player ";
        $sql .= "WHERE player_id = '{$winner}'";
        $winner_infos = $this->getObjectFromDB($sql);

        if ((game::$instance->getGameStateValue("kraken") == 0) && (game::$instance->getGameStateValue("white_whale") == 0)) {
            game::$instance->notifyAllPlayers(
                'endTrick',
                clienttranslate('${player_name} wins the trick'),
                array(
                    'winner_id' => $winner,
                    'winner_infos' => $winner_infos,
                    'player_name' => $winner_name,

                )
            );

            if (game::$instance->getGameStateValue("pirate_powers_mode") == 2) {
                if (($type_card_winner == 'pirate') && ($type_arg_card_winner == 5)) {
                    $pirate_power = 1;
                    game::$instance->addPending($winner, "Power6", $winner);
                }
            }
        }

        if (game::$instance->getGameStateValue("kraken") == 1) {

            $cards = self::getObjectListFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg FROM card WHERE card_location = 'table'");

            foreach ($cards_played as $card_played) {
                game::$instance->db_card->moveCard($card_played, 'discard', 0);
            }

            game::$instance->notifyAllPlayers(
                'krakenEffect',
                clienttranslate('${log} destroys the trick'),
                array(
                    'player_name' => $winner_name,
                    'cards' => $cards,
                    'log' => game::$instance->getLogsSpecial('kraken'),


                )
            );
        }

        if (game::$instance->getGameStateValue("white_whale") == 1) {
            if ($type_card_winner == 'white_whale') {

                $cards = self::getObjectListFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg FROM card WHERE card_location = 'table'");

                foreach ($cards_played as $card_played) {
                    game::$instance->db_card->moveCard($card_played, 'discard', 0);
                }


                game::$instance->notifyAllPlayers(
                    'krakenEffect',
                    clienttranslate('${log} destroys the trick because ${player_name} won the trick'),
                    array(
                        'player_name' => $winner_name,
                        'cards' => $cards,
                        'log' => game::$instance->getLogsSpecial('white_whale'),


                    )
                );
            } else {
                foreach ($cards_played as $card_played) {
                    // BONUS
                    $type_card = self::getUniqueValueFromDB("SELECT card_type FROM card WHERE card_location = 'table' AND card_id = '{$card_played}'");
                    $type_arg_card = self::getUniqueValueFromDB("SELECT card_type_arg FROM card WHERE card_location = 'table' AND card_id = '{$card_played}'");
                    if ((($type_card == 'green') || ($type_card == 'purple') || ($type_card == 'yellow')) && ($type_arg_card == 14)) {
                        game::$instance->DbQuery("UPDATE player set player_bonus_trick = player_bonus_trick + 10 WHERE player_id = '{$winner}' ");
                        if($type_card == 'green')
                        {
                            $bonus[] = 1;
                        }

                        if($type_card == 'purple')
                        {
                            $bonus[] = 2;
                        }

                        if($type_card == 'yellow')
                        {
                            $bonus[] = 3;
                        }
                    }
                    if (($type_card == 'black') && ($type_arg_card == 14)) {
                        game::$instance->DbQuery("UPDATE player set player_bonus_trick = player_bonus_trick + 20 WHERE player_id = '{$winner}' ");
                        $bonus[] = 4;
                    }

                    game::$instance->db_card->moveCard($card_played, 'discard', $winner);
                }

                game::$instance->notifyAllPlayers(
                    'endTrick',
                    clienttranslate('${player_name} wins the trick thanks to ${log}'),
                    array(
                        'winner_id' => $winner,
                        'winner_infos' => $winner_infos,
                        'player_name' => $winner_name,
                        'log' => game::$instance->getLogsSpecial('white_whale'),

                    )
                );
            }
        }


        game::$instance->logBonus($bonus);




        //INIT TURN
        game::$instance->setGameStateValue("requested_color", 0);
        game::$instance->setGameStateValue("requested_color_cannot_change", 0);
        game::$instance->setGameStateValue("tigress_role", 0);
        game::$instance->DbQuery("UPDATE player set player_turn = 0 ");
        game::$instance->setGameStateValue("kraken", 0);
        game::$instance->setGameStateValue("white_whale", 0);
        game::$instance->setGameStateValue("loot_nb_in_turn", 0);

        if ($pirate_power == 0) {

            //SCORE ROUND

            game::$instance->scoreRound();

            //INIT END OF ROUND

            game::$instance->DbQuery("UPDATE player set player_bid = -1 ");
            game::$instance->DbQuery("UPDATE player set player_bid_validated = 0 ");
            game::$instance->DbQuery("UPDATE player set player_tricks = 0 ");
            game::$instance->DbQuery("UPDATE player set player_bonus_trick = 0 ");
            game::$instance->DbQuery("UPDATE player set player_bonus_rascal = 0 ");
            game::$instance->DbQuery("UPDATE player set player_bonus_loot = 0 ");
            game::$instance->setGameStateValue("loot_1_id_play", 0);
            game::$instance->setGameStateValue("loot_2_id_play", 0);
            game::$instance->setGameStateValue("loot_1_id_win", 0);
            game::$instance->setGameStateValue("loot_2_id_win", 0);

            if (game::$instance->getGameStateValue("round_nb") < 10) {

                game::$instance->setGameStateValue("end_of_round", 1);
                $cards_discard = self::getObjectListFromDB("SELECT card_id FROM card WHERE card_location = 'discard'", true);
                foreach ($cards_discard as $card_discard) {
                    game::$instance->db_card->moveCard($card_discard, 'deck');
                }
                game::$instance->db_card->shuffle('deck');


                // CHANGE FIRST PLAYER OF ROUND AND FIRST PLAYER TRICK
                $first = game::$instance->getGameStateValue("first_player_round");
                $nextplayer = game::$instance->getPlayerAfter($first);
                game::$instance->setGameStateValue("first_player_round", $nextplayer);
                game::$instance->setGameStateValue("first_player_trick", $nextplayer);

                // CALCUL DU PROCHAIN new_round_nb
                $new_round_nb = game::$instance->getGameStateValue("round_nb") + 1;
                $count_players = count(self::getObjectListFromDB("SELECT player_id id FROM player", true));

                if ((($new_round_nb == 10) && ($count_players == 8)) || (($new_round_nb == 9) && ($count_players == 8))) {
                    $new_round_max_bid = 8;
                } elseif (($new_round_nb == 10) && ($count_players == 7)) {
                    $new_round_max_bid = 9;
                } else {
                    $new_round_max_bid = $new_round_nb;
                }


                game::$instance->notifyAllPlayers(
                    'endRound',
                    '',
                    array(
                        'next_player_round' => $nextplayer,
                        'round_max_bid' => $new_round_max_bid,

                    )
                );

                self::DbQuery("DELETE FROM `pending`;");

                // NOUVEL ORDRE PENDING

                for ($i = 1; $i <= $count_players; $i++) {

                    game::$instance->addPendingFirst($nextplayer, "PlayCard");
                    $nextplayer = game::$instance->getPlayerAfter($nextplayer);
                }
            }

            if (game::$instance->getGameStateValue("round_nb") == 10) {
                game::$instance->addPending($this->player_id, "EndOfGame");
                game::$instance->setGameStateValue("end_of_game", 1);

                game::$instance->notifyAllPlayers(
                    'removeIconScore',
                    '',
                    array(
                        

                    )
                );
            }
        }
    }

    function argEndOfGame($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('End of Game');
        $ret['titleyou'] = clienttranslate('End of Game');



        return $ret;
    }

    function EndOfGame($parg1, $parg2, $varg1, $varg2)
    {
          

        game::$instance->gamestate->nextState('end');
    }
}
