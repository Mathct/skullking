<?php

namespace Bga\Games\skullking; // ATTENTION

trait PiratesTrait  // ATTENTION
{

    //////////////////////// ROSIE ///////////////////////////////

    public function argPower1($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} can choose any player to lead the next trick thanks to Rosie D\'Laney');
        $ret['titleyou'] = clienttranslate('Rosie D\'Laney: ${you} must choose any player to lead the next trick');

        return $ret;
    }

    public function Power1($parg1, $parg2, $varg1, $varg2)
    {

        game::$instance->notifyPlayer(
            $this->player_id,
            'rosieEffect',
            '',
            array()
        );
        game::$instance->setGameStateValue("rosie_container", $this->player_id);
        game::$instance->addPending($this->player_id, "Power1_Step2", $parg1);
    }

    public function argPower1_Step2($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} can choose any player to lead the next trick thanks to Rosie D\'Laney');
        $ret['titleyou'] = clienttranslate('Rosie D\'Laney: ${you} must choose any player to lead the next trick');

        $players = self::getObjectListFromDB("SELECT player_id FROM player", true);
        foreach ($players as $player) {
            $ret["selectable"][] = 'rosie_' . $player;
        }




        return $ret;
    }

    public function Power1_Step2($parg1, $parg2, $varg1, $varg2)
    {

        if ($this->player_pref_confirm == 1) {

            $explode = explode('_', $varg1);
            $id = end($explode);

            $new_name = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id = '{$id}'");

            game::$instance->setGameStateValue("first_player_trick", $id);
            $nextplayer = $id;

            self::DbQuery("DELETE FROM `pending`;");

            // NOUVEL ORDRE PENDING
            $count_players = count(self::getObjectListFromDB("SELECT player_id id FROM player", true));
            for ($i = 1; $i <= $count_players; $i++) {

                game::$instance->addPendingFirst($nextplayer, "PlayCard");
                $nextplayer = game::$instance->getPlayerAfter($nextplayer);
            }

            game::$instance->notifyAllPlayers(
                'message',
                clienttranslate('${player_name} chooses the first player of the trick thanks to Rosie D\'Laney.'),
                array(
                    'player_name' => $this->player_name,



                )
            );

            game::$instance->notifyAllPlayers(
                'message',
                clienttranslate('${player_name} must start the trick.'),
                array(
                    'player_name' => $new_name,


                )
            );

            game::$instance->setGameStateValue("rosie_container", 0);

            game::$instance->notifyPlayer(
                $this->player_id,
                'rosieEffectDone',
                '',
                array()
            );

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

        if ($this->player_pref_confirm == 2) {
            game::$instance->addPending($this->player_id, "Power1_Step2_Confirm", $varg1, $parg1);
        }
    }

    public function argPower1_Step2_Confirm($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} can choose any player to lead the next trick thanks to Rosie D\'Laney');
        $ret['titleyou'] = clienttranslate('${you} must confirm');

        $ret["selected"][] = $parg1;

        $ret['buttons'][] = 'yes';
        $ret['buttons'][] = 'no';

        return $ret;
    }

    public function Power1_Step2_Confirm($parg1, $parg2, $varg1, $varg2)
    {
        if ($varg1 == 'no') {
            game::$instance->addPending($this->player_id, "Power1_Step2", $parg2);
        }

        if ($varg1 == 'yes') {

            $explode = explode('_', $parg1);
            $id = end($explode);

            $new_name = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id = '{$id}'");

            game::$instance->setGameStateValue("first_player_trick", $id);
            $nextplayer = $id;

            self::DbQuery("DELETE FROM `pending`;");

            // NOUVEL ORDRE PENDING
            $count_players = count(self::getObjectListFromDB("SELECT player_id id FROM player", true));
            for ($i = 1; $i <= $count_players; $i++) {

                game::$instance->addPendingFirst($nextplayer, "PlayCard");
                $nextplayer = game::$instance->getPlayerAfter($nextplayer);
            }

            game::$instance->notifyAllPlayers(
                'message',
                clienttranslate('${player_name} chooses the first player of the trick thanks to Rosie D\'Laney.'),
                array(
                    'player_name' => $this->player_name,



                )
            );

            game::$instance->notifyAllPlayers(
                'message',
                clienttranslate('${player_name} must start the trick.'),
                array(
                    'player_name' => $new_name,


                )
            );

            game::$instance->setGameStateValue("rosie_container", 0);

            game::$instance->notifyPlayer(
                $this->player_id,
                'rosieEffectDone',
                '',
                array()
            );

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



    //////////////////////// BENDT ///////////////////////////////

    public function argPower2($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} drew 2 cards and must discard 2 of them thanks to Bendt the Bandit');
        $ret['titleyou'] = clienttranslate('Bendt the Bandit: ${you} have drawn 2 cards. Select 2 cards to discard');


        return $ret;
    }

    public function Power2($parg1, $parg2, $varg1, $varg2)
    {

        $cards = game::$instance->db_card->pickCards(2, 'deck', $this->player_id);

        game::$instance->notifyPlayer(
            $this->player_id,
            'bendtEffect',
            '',
            array(
                'cards' => $cards,
            )
        );
        game::$instance->addPending($this->player_id, "Power2_Step2", $parg1);
    }

    public function argPower2_Step2($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selectablemulti"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} drew 2 cards and must discard 2 of them thanks to Bendt the Bandit');
        $ret['titleyou'] = clienttranslate('Bendt the Bandit: ${you} have drawn 2 cards. Select 2 cards to discard');

        $all_cards = self::getObjectListFromDB("SELECT card_id FROM card WHERE card_location = 'hand' AND card_location_arg = '{$this->player_id}'", true);

        foreach ($all_cards as $card) {
            $ret["selectablemulti"][] = 'my_cards_item_' . $card;
        }


        $ret['buttons'][] = 'validatemulti';


        return $ret;
    }

    public function Power2_Step2($parg1, $parg2, $varg1, $varg2)
    {
        if ($this->player_pref_confirm == 1) {
            // ici le $varg1 n'est pas l'id du bouton mais la selection générée par le actValidate (voir game.php)
            $ids = explode('_', $varg1);
            $cards = array();
            $player_id = $this->player_id;

            foreach ($ids as $id) {
                game::$instance->db_card->moveCard($id, 'discard', 0);
                $cards[] = ['id' => $id];
            }



            game::$instance->notifyPlayer(
                $player_id,
                'bendtEffectDone',
                '',
                array(
                    'cards' => $cards,
                )
            );


            $nextplayer = $parg1;

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

        if ($this->player_pref_confirm == 2) {
            game::$instance->addPending($this->player_id, "Power2_Step2_Confirm", $varg1, $parg1);
        }
    }

    public function argPower2_Step2_Confirm($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} drew 2 cards and must discard 2 of them thanks to Bendt the Bandit');
        $ret['titleyou'] = clienttranslate('${you} must confirm');

        $ids = explode('_', $parg1);
        foreach ($ids as $id) {
            $ret["selected"][] = "my_cards_item_" . $id;
        }

        $ret['buttons'][] = 'yes';
        $ret['buttons'][] = 'no';

        return $ret;
    }

    public function Power2_Step2_Confirm($parg1, $parg2, $varg1, $varg2)
    {
        if ($varg1 == 'no') {
            game::$instance->addPending($this->player_id, "Power2_Step2", $parg2);
        }

        if ($varg1 == 'yes') {

            $ids = explode('_', $parg1);
            $cards = array();
            $player_id = $this->player_id;

            foreach ($ids as $id) {
                game::$instance->db_card->moveCard($id, 'discard', 0);
                $cards[] = ['id' => $id];
            }



            game::$instance->notifyPlayer(
                $player_id,
                'bendtEffectDone',
                '',
                array(
                    'cards' => $cards,
                )
            );


            $nextplayer = $parg2;

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

    //////////////////////// RASCAL ///////////////////////////////

    public function argPower3($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} can bet any bonus points thanks to Rascal of Roatan');
        $ret['titleyou'] = clienttranslate('Rascal of Roatan: ${you} can bet any bonus points. Earn the points if you bid correct, lose them if you fail!');


        return $ret;
    }

    public function Power3($parg1, $parg2, $varg1, $varg2)
    {
        game::$instance->notifyPlayer(
            $this->player_id,
            'rascalEffect',
            '',
            array()
        );
        game::$instance->setGameStateValue("rascal_container", $this->player_id);
        game::$instance->addPending($this->player_id, "Power3_Step2", $parg1);
    }

    public function argPower3_Step2($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} can bet any bonus points thanks to Rascal of Roatan');
        $ret['titleyou'] = clienttranslate('Rascal of Roatan: ${you} can bet any bonus points. Earn the points if you bid correct, lose them if you fail!');

        $ret["selectable"][] = 'bid_0';
        $ret["selectable"][] = 'bid_10';
        $ret["selectable"][] = 'bid_20';

        return $ret;
    }

    public function Power3_Step2($parg1, $parg2, $varg1, $varg2)
    {
        if ($this->player_pref_confirm == 1) {
            $explode = explode('_', $varg1);
            $bonus = intval($explode[1]);

            game::$instance->DbQuery("UPDATE player set player_bonus_rascal = $bonus WHERE player_id = '{$this->player_id}'");

            game::$instance->notifyPlayer(
                $this->player_id,
                'rascalEffectDone',
                '',
                array()
            );

            game::$instance->setGameStateValue("rascal_container", 0);

            $nextplayer = $parg1;

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

        if ($this->player_pref_confirm == 2) {
            game::$instance->addPending($this->player_id, "Power3_Step2_Confirm", $parg1, $varg1);
        }
    }

    public function argPower3_Step2_Confirm($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} can bet any bonus points thanks to Rascal of Roatan');
        $ret['titleyou'] = clienttranslate('${you} must confirm');

        $ret["selected"][] = $parg2;

        $ret['buttons'][] = 'yes';
        $ret['buttons'][] = 'no';

        return $ret;
    }

    public function Power3_Step2_Confirm($parg1, $parg2, $varg1, $varg2)
    {
        if ($varg1 == 'no') {
            game::$instance->addPending($this->player_id, "Power3_Step2", $parg1);
        }

        if ($varg1 == 'yes') {

            $explode = explode('_', $parg2);
            $bonus = intval($explode[1]);

            game::$instance->DbQuery("UPDATE player set player_bonus_rascal = $bonus WHERE player_id = '{$this->player_id}'");

            game::$instance->notifyPlayer(
                $this->player_id,
                'rascalEffectDone',
                '',
                array()
            );

            game::$instance->setGameStateValue("rascal_container", 0);

            $nextplayer = $parg1;

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

    //////////////////////// JUANITA ///////////////////////////////

    public function argPower4($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} can see the deck thanks to Juanita Jade');
        $ret['titleyou'] = clienttranslate('Juanita Jade: ${you} can see the deck');


        return $ret;
    }

    public function Power4($parg1, $parg2, $varg1, $varg2)
    {
        game::$instance->notifyPlayer(
            $this->player_id,
            'juanitaEffect',
            '',
            array(
                'deck' => game::$instance->db_card->getCardsInLocation('deck')
            )
        );
        game::$instance->setGameStateValue("juanita_container", $this->player_id);
        game::$instance->addPending($this->player_id, "Power4_Step2", $parg1);
    }

    public function argPower4_Step2($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} can see the deck thanks to Juanita Jade');
        $ret['titleyou'] = clienttranslate('Juanita Jade: ${you} can see the deck');

        $ret['buttons'][] = 'continue';

        return $ret;
    }

    public function Power4_Step2($parg1, $parg2, $varg1, $varg2)
    {
        if ($this->player_pref_confirm == 1) {
            game::$instance->notifyPlayer(
                $this->player_id,
                'juanitaEffectDone',
                '',
                array()
            );

            game::$instance->setGameStateValue("juanita_container", 0);

            $nextplayer = $parg1;

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
        if ($this->player_pref_confirm == 2) {
            game::$instance->addPending($this->player_id, "Power4_Step2_Confirm", $parg1);
        }
    }

    public function argPower4_Step2_Confirm($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} can see the deck thanks to Juanita Jade');
        $ret['titleyou'] = clienttranslate('${you} must confirm');


        $ret['buttons'][] = 'yes';
        $ret['buttons'][] = 'no';

        return $ret;
    }

    public function Power4_Step2_Confirm($parg1, $parg2, $varg1, $varg2)
    {
        if ($varg1 == 'no') {
            game::$instance->addPending($this->player_id, "Power4_Step2", $parg1);
        }

        if ($varg1 == 'yes') {
            game::$instance->notifyPlayer(
                $this->player_id,
                'juanitaEffectDone',
                '',
                array()
            );

            game::$instance->setGameStateValue("juanita_container", 0);

            $nextplayer = $parg1;

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

    //////////////////////// HARRY FIN DE TURN ///////////////////////////////

    public function argPower5($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} can change the bid thanks to Harry the Giant');
        $ret['titleyou'] = clienttranslate('Harry the Giant: ${you} can change your bid');


        return $ret;
    }

    public function Power5($parg1, $parg2, $varg1, $varg2)
    {

        $player_bid = self::getUniqueValueFromDB("SELECT player_bid FROM player WHERE player_id={$this->player_id}");;
        $max_bid = game::$instance->getGameStateValue("round_max_bid");
        $harry_bids = array();

        if ($player_bid >= 1) {
            $harry_bids[] = $player_bid - 1;
        }

        $harry_bids[] = $player_bid;

        if ($player_bid < $max_bid) {
            $harry_bids[] = $player_bid + 1;
        }


        game::$instance->notifyPlayer(
            $this->player_id,
            'harryEffect',
            '',
            array(
                'harry_bids' => $harry_bids,
            )
        );

        game::$instance->setGameStateValue("harry_container", $this->player_id);
        game::$instance->addPending($this->player_id, "Power5_Step2", $parg1);
    }

    public function argPower5_Step2($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} can change the bid thanks to Harry the Giant');
        $ret['titleyou'] = clienttranslate('Harry the Giant: ${you} can change your bid');

        $player_bid = self::getUniqueValueFromDB("SELECT player_bid FROM player WHERE player_id={$this->player_id}");;
        $max_bid = game::$instance->getGameStateValue("round_max_bid");
        $harry_bids = array();

        if ($player_bid >= 1) {
            $harry_bids[] = $player_bid - 1;
        }

        $harry_bids[] = $player_bid;

        if ($player_bid < $max_bid) {
            $harry_bids[] = $player_bid + 1;
        }

        foreach ($harry_bids as $bid) {
            $ret["selectable"][] = 'bid_' . $bid;
        }



        return $ret;
    }

    public function Power5_Step2($parg1, $parg2, $varg1, $varg2)
    {
        if ($this->player_pref_confirm == 1) {
            $explode = explode('_', $varg1);
            $new_bid = $explode[1];
            game::$instance->DbQuery("UPDATE player set player_bid = $new_bid WHERE player_id = '{$this->player_id}'");

            $sql = "SELECT player_no no, player_id id, player_score score, player_name name, player_color color, player_bid bid, player_bid_validated bid_validated, player_tricks tricks, player_turn turn, player_bonus_trick bonus_trick, player_bonus_rascal bonus_rascal 
            FROM player ";
            $sql .= "WHERE player_id = '{$this->player_id}'";
            $bid_infos = $this->getObjectFromDB($sql);


            game::$instance->notifyPlayer(
                $this->player_id,
                'harryEffectDone',
                '',
                array(
                    'bid_infos' => $bid_infos,
                )
            );

            game::$instance->notifyAllPlayers(
                    'majBidHarry',
                    '',
                    array(
                        'bid_infos' => $bid_infos,


                    )
            );

            game::$instance->setGameStateValue("harry_container", 0);

            $nextplayer = $parg1;

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

        if ($this->player_pref_confirm == 2) {
            game::$instance->addPending($this->player_id, "Power5_Step2_Confirm", $parg1, $varg1);
        }
    }

    public function argPower5_Step2_Confirm($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} can change the bid thanks to Harry the Giant');
        $ret['titleyou'] = clienttranslate('${you} must confirm');

        $ret["selected"][] = $parg2;

        $ret['buttons'][] = 'yes';
        $ret['buttons'][] = 'no';




        return $ret;
    }

    public function Power5_Step2_Confirm($parg1, $parg2, $varg1, $varg2)
    {

        if ($varg1 == 'no') {
            game::$instance->addPending($this->player_id, "Power5_Step2", $parg1);
        }

        if ($varg1 == 'yes') {
            $explode = explode('_', $parg2);
            $new_bid = $explode[1];
            game::$instance->DbQuery("UPDATE player set player_bid = $new_bid WHERE player_id = '{$this->player_id}'");

            $sql = "SELECT player_no no, player_id id, player_score score, player_name name, player_color color, player_bid bid, player_bid_validated bid_validated, player_tricks tricks, player_turn turn, player_bonus_trick bonus_trick, player_bonus_rascal bonus_rascal 
            FROM player ";
            $sql .= "WHERE player_id = '{$this->player_id}'";
            $bid_infos = $this->getObjectFromDB($sql);


            game::$instance->notifyPlayer(
                $this->player_id,
                'harryEffectDone',
                '',
                array(
                    'bid_infos' => $bid_infos,
                )
            );

            game::$instance->notifyAllPlayers(
                    'majBidHarry',
                    '',
                    array(
                        'bid_infos' => $bid_infos,


                    )
            );

            game::$instance->setGameStateValue("harry_container", 0);

            $nextplayer = $parg1;

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

    //////////////////////// HARRY FIN DE ROUND ///////////////////////////////

    public function argPower6($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} can change the bid thanks to Harry the Giant');
        $ret['titleyou'] = clienttranslate('Harry the Giant: ${you} can change your bid');


        return $ret;
    }

    public function Power6($parg1, $parg2, $varg1, $varg2)
    {

        $player_bid = self::getUniqueValueFromDB("SELECT player_bid FROM player WHERE player_id={$this->player_id}");;
        $max_bid = game::$instance->getGameStateValue("round_max_bid");
        $harry_bids = array();

        if ($player_bid >= 1) {
            $harry_bids[] = $player_bid - 1;
        }

        $harry_bids[] = $player_bid;

        if ($player_bid < $max_bid) {
            $harry_bids[] = $player_bid + 1;
        }


        game::$instance->notifyPlayer(
            $this->player_id,
            'harryEffect',
            '',
            array(
                'harry_bids' => $harry_bids,
            )
        );

        game::$instance->setGameStateValue("harry_container", $this->player_id);
        game::$instance->addPending($this->player_id, "Power6_Step2", $parg1);
    }

    public function argPower6_Step2($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} can change the bid thanks to Harry the Giant');
        $ret['titleyou'] = clienttranslate('Harry the Giant: ${you} can change your bid');

        $player_bid = self::getUniqueValueFromDB("SELECT player_bid FROM player WHERE player_id={$this->player_id}");;
        $max_bid = game::$instance->getGameStateValue("round_max_bid");
        $harry_bids = array();

        if ($player_bid >= 1) {
            $harry_bids[] = $player_bid - 1;
        }

        $harry_bids[] = $player_bid;

        if ($player_bid < $max_bid) {
            $harry_bids[] = $player_bid + 1;
        }

        foreach ($harry_bids as $bid) {
            $ret["selectable"][] = 'bid_' . $bid;
        }



        return $ret;
    }

    public function Power6_Step2($parg1, $parg2, $varg1, $varg2)
    {
        if ($this->player_pref_confirm == 1) {
            $explode = explode('_', $varg1);
            $new_bid = $explode[1];
            game::$instance->DbQuery("UPDATE player set player_bid = $new_bid WHERE player_id = '{$this->player_id}'");

            $sql = "SELECT player_no no, player_id id, player_score score, player_name name, player_color color, player_bid bid, player_bid_validated bid_validated, player_tricks tricks, player_turn turn, player_bonus_trick bonus_trick, player_bonus_rascal bonus_rascal 
            FROM player ";
            $sql .= "WHERE player_id = '{$this->player_id}'";
            $bid_infos = $this->getObjectFromDB($sql);


            game::$instance->notifyPlayer(
                $this->player_id,
                'harryEffectDone',
                '',
                array(
                    'bid_infos' => $bid_infos,
                )
            );

            game::$instance->notifyAllPlayers(
                    'majBidHarry',
                    '',
                    array(
                        'bid_infos' => $bid_infos,


                    )
            );

            game::$instance->setGameStateValue("harry_container", 0);

            //SCORE ROUND

            game::$instance->scoreRound();

            //INIT END OF ROUND

            game::$instance->DbQuery("UPDATE player set player_bid = -1 ");
            game::$instance->DbQuery("UPDATE player set player_bid_validated = 0 ");
            game::$instance->DbQuery("UPDATE player set player_tricks = 0 ");
            game::$instance->DbQuery("UPDATE player set player_bonus_trick = 0 ");
            game::$instance->DbQuery("UPDATE player set player_bonus_rascal = 0 ");
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
            }
        }

        if ($this->player_pref_confirm == 2) {
            game::$instance->addPending($this->player_id, "Power6_Step2_Confirm", $parg1, $varg1);
        }
    }

    public function argPower6_Step2_Confirm($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} can change the bid thanks to Harry the Giant');
        $ret['titleyou'] = clienttranslate('${you} must confirm');

        $ret["selected"][] = $parg2;

        $ret['buttons'][] = 'yes';
        $ret['buttons'][] = 'no';




        return $ret;
    }

    public function Power6_Step2_Confirm($parg1, $parg2, $varg1, $varg2)
    {

        if ($varg1 == 'no') {
            game::$instance->addPending($this->player_id, "Power6_Step2", $parg1);
        }

        if ($varg1 == 'yes') {
            $explode = explode('_', $parg2);
            $new_bid = $explode[1];
            game::$instance->DbQuery("UPDATE player set player_bid = $new_bid WHERE player_id = '{$this->player_id}'");

            $sql = "SELECT player_no no, player_id id, player_score score, player_name name, player_color color, player_bid bid, player_bid_validated bid_validated, player_tricks tricks, player_turn turn, player_bonus_trick bonus_trick, player_bonus_rascal bonus_rascal 
            FROM player ";
            $sql .= "WHERE player_id = '{$this->player_id}'";
            $bid_infos = $this->getObjectFromDB($sql);


            game::$instance->notifyPlayer(
                $this->player_id,
                'harryEffectDone',
                '',
                array(
                    'bid_infos' => $bid_infos,
                )
            );

            game::$instance->notifyAllPlayers(
                    'majBidHarry',
                    '',
                    array(
                        'bid_infos' => $bid_infos,


                    )
            );

            game::$instance->setGameStateValue("harry_container", 0);

            //SCORE ROUND

            game::$instance->scoreRound();

            //INIT END OF ROUND

            game::$instance->DbQuery("UPDATE player set player_bid = -1 ");
            game::$instance->DbQuery("UPDATE player set player_bid_validated = 0 ");
            game::$instance->DbQuery("UPDATE player set player_tricks = 0 ");
            game::$instance->DbQuery("UPDATE player set player_bonus_trick = 0 ");
            game::$instance->DbQuery("UPDATE player set player_bonus_rascal = 0 ");
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
            }
        }
    }
}
