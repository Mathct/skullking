/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * skullking implementation : © <Mathieu Chatrain> <mathieu.chatrain@gmail.com> && <Yannick Priol> <camertwo@hotmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * skullking.js
 *
 * skullking user interface script
 * 
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

 //Tisaac way to debug ;)
var isDebug = window.location.host == 'studio.boardgamearena.com' || window.location.hash.indexOf('debug') > -1;
var debug = isDebug ? console.info.bind(window.console) : function () {};


define([
    "dojo","dojo/_base/declare",
    "ebg/core/gamegui",
    "ebg/counter",
    "ebg/stock",
    g_gamethemeurl + 'modules/js/game.js'
],
function (dojo, declare) {

    /* CONSTANTS HERE */
    const TOOLTIP_DELAY = 500;
    const CARD_WIDTH = 128;
    const CARD_HEIGHT = 179;
    const CARDS_PER_ROW = 14;

    return declare("bgagame.skullking", [customgame.game], {
        constructor: function(){
            console.log('skullking constructor');


              

        },

            
 /////////////////////////////////////////////////////////////////////////////////           
//    _____                      _____        _            
//   / ____|                    |  __ \      | |           
//  | |  __  __ _ _ __ ___   ___| |  | | __ _| |_ __ _ ___ 
//  | | |_ |/ _` | '_ ` _ \ / _ \ |  | |/ _` | __/ _` / __|
//  | |__| | (_| | | | | | |  __/ |__| | (_| | || (_| \__ \
//   \_____|\__,_|_| |_| |_|\___|_____/ \__,_|\__\__,_|___/
//                                                        
/////////////////////////////////////////////////////////////////////////////////
        
setup: function( gamedatas )
{
    console.log( "Starting game setup" );


    this.players = gamedatas.players; // A RAJOUTER POUR MOTEUR (UTILITY METHODS)
 

    // counters    
    //this.tile_counter = {};

    this.suit_cards = gamedatas.suit_cards;
    this.special_cards = gamedatas.special_cards;
    this.pirate_cards = gamedatas.pirate_cards;


    this.my_hand = gamedatas.my_hand;


    console.log( 'gamedatas.table', gamedatas.table);
	this.table = gamedatas.table;


    this.round_max_bid = parseInt(gamedatas.round_max_bid); 
    this.round_nb = parseInt(gamedatas.round_nb); 
    this.total_rounds = 10; // A modifier selon l'option
  

    this.first_player_trick = gamedatas.first_player_trick;
    this.first_player_round = gamedatas.first_player_round;

    this.timer = 5;
    if (this.getGameUserPreference(101) !== null && this.getGameUserPreference(101) !== undefined) {
        this.timer = this.getGameUserPreference(101);
    }



    this.setupPlayersBoard();
    this.setupBoard();
    this.setupCounters();
    this.setupTooltips();

    this.setupNotifications();
    


  // THOUN
   this.addHelp(); 


    console.log( "Ending game setup" );
},

/////////////////////////////////////////////////////////////////////////////////   
//         _____ _        _            
//        / ____| |      | |           
//       | (___ | |_ __ _| |_ ___  ___ 
//        \___ \| __/ _` | __/ _ \/ __|
//        ____) | || (_| | ||  __/\__ \
//       |_____/ \__\__,_|\__\___||___/
//                                    
/////////////////////////////////////////////////////////////////////////////////    


///////////////////////////////////////////////////
//// Game & client states

// onEnteringState: this method is called each time we are entering into a new game state.
//                  You can use this method to perform some user interface changes at this moment.
//
onEnteringState: function( stateName, args )
{
    
    console.log('Entering state: '+stateName, args);

      
    
    switch( stateName )
    {
        case 'playerTurn':
            this.args = args.args;
            //console.log( this.args);

            this.possibles = [];
            
            if(this.isCurrentPlayerActive()) {
                this.args.selectable.forEach(sid => {
                    dojo.addClass(sid,"selectable");
                    this.possibles.push(sid);
                });


                this.args.selected.forEach(sid => {
                    dojo.addClass(sid,"selected");   
                });

                if (this.args.selectablemulti) {
                    this.args.selectablemulti.forEach(sid => {
                        dojo.addClass(sid,"selectablemulti");
                        this.possibles.push(sid);
                    });
   
                }

                this.setupConnections(this.possibles);
                

                if(args.args.titleyou != null)
                {
                    $('pagemaintitletext').innerHTML = this.format_string_recursive(_(args.args.titleyou).replace('${you}', this.divYou()).replace(/#opponent#/g,args.args.opponent).replace('#nb#',args.args.nb).replace('#nb2#',args.args.nb2).replace('#icon#',args.args.icon).replace('#icon2#',args.args.icon2), args.args);
                }
            
            }
            else{
                if(args.args.title != null)
                {
                    $('pagemaintitletext').innerHTML = this.format_string_recursive(_(args.args.title).replace('${actplayer}', this.divActPlayer()).replace('#nb#',args.args.nb).replace('#nb2#',args.args.nb2).replace('#icon#',args.args.icon).replace('#icon2#',args.args.icon2), args.args);  
                }
            }

            break;



        case 'playerTurnMultiBid': // waiting for bid
            this.args = args.args;
            //console.log( this.args);


            dojo.addClass('table_cards_container', 'hidden');
            dojo.removeClass('bids_container', 'hidden'); //on affiche le container des paris

            if(this.isCurrentPlayerActive())
            {
                if (!document.getElementById('bid_0')) { // on le remplit s'il est vide
                    this.setupBids();
                }
         

                this.args.selectable[this.player_id].forEach(sid => {
                    dojo.addClass(sid,"selectable");
                });

                this.setupConnections(this.args.selectable[this.player_id]);
    
            }
            break;



        case 'playerTurnMultiConfirmBid':
            this.args = args.args;


            dojo.removeClass('bids_container', 'hidden');
            dojo.addClass('table_cards_container', 'hidden');
    
            if(this.isCurrentPlayerActive())
            {
                
                this.args.selectable[this.player_id].forEach(sid => {
                    dojo.addClass(sid,"selectable");
                });

                
                this.args.selected[this.player_id].forEach(sid => {
                    dojo.addClass(sid,"selected");   
                });

                this.setupConnections(this.args.selectable[this.player_id]);
    
            }
            break;


        case 'playerTurnMulti': // spectator or bid is done
            this.args = args.args;

            dojo.removeClass('bids_container', 'hidden');
            dojo.addClass('table_cards_container', 'hidden');  
            this.setupBids();          
     
            break;

    
        case 'dummmy':
            break;
    }
},

// onLeavingState: this method is called each time we are leaving a game state.
//                 You can use this method to perform some user interface changes at this moment.
//
onLeavingState: function( stateName )
{
    console.log( 'Leaving state: '+stateName );

    dojo.query(".selectable").removeClass("selectable");
    dojo.query(".selected").removeClass("selected");
    dojo.query(".selectablemulti").removeClass("selectablemulti");
    dojo.query(".selectedmulti").removeClass("selectedmulti");

    switch( stateName )
    {
    
        case 'playerTurn':
            this.removeConnections();            
            break;
            
        case 'playerTurnMultiBid':
            this.removeConnections(); 
            if(this.isSpectator == false ) {
                this.players[this.player_id].bid_validated = 0;
                console.log(this.players[this.player_id]);
            }           
            break;

        case 'playerTurnMultiConfirmBid':
            if(this.isSpectator == false ) {
                this.players[this.player_id].bid_validated = 0;
                console.log(this.players[this.player_id]);
            }
            this.removeConnections();            
            break;
    
        case 'dummy':
            break;
    }               
}, 

// onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
//                        action status bar (ie: the HTML links in the status bar).
//        
onUpdateActionButtons: function( stateName, args )
{
    console.log( 'onUpdateActionButtons: '+stateName, args );
              
    if( this.isCurrentPlayerActive() )
    {            
        switch( stateName )
        {
            case "playerTurn":
                for( var nb in args.buttons )
                { 
                    if(args.buttons[nb] == "cancel")
                    {
                        this.addActionButton( 'cancel', _("Cancel") ,'onOpButton', null, null, 'red' );
                    }
                    if(args.buttons[nb] == "pass")
                    {
                        this.addActionButton( 'pass', _("Pass") ,'onOpButton', null, null, 'red' );
                    }
                    if(args.buttons[nb] == "continue")
                    {
                        this.addActionButton( 'continue', _("Continue") ,'onOpButton', null, null, 'blue' );
                    }
                    if(args.buttons[nb] == "yes")
                    {
                        this.addActionButton( 'yes', _("Yes") ,'onOpButton', null, null, 'blue' );
                        this.startActionTimer('yes', this.timer, 1);
                    }
                    if(args.buttons[nb] == "no")
                    {
                        this.addActionButton( 'no', _("No") ,'onOpButton', null, null, 'red' );
                    }
                    if(args.buttons[nb] == "confirm") {
                        this.addActionButton( 'confirm', _("Confirm") ,'onOpButton', null, null, 'blue' );
                    }
                    if(args.buttons[nb] == "tigress_pirate")
                    {
                        //this.addActionButton( 'tigress_pirate', _("Pirate") ,'onOpButton', null, null, 'blue' );
                        this.addActionButton( 'tigress_pirate', `<div class="pirate_bt"`,'onOpButton', null, null, 'none' );
                    }
                    if(args.buttons[nb] == "tigress_escape")
                    {
                        //this.addActionButton( 'tigress_escape', _("Escape") ,'onOpButton', null, null, 'blue' );
                        this.addActionButton( 'tigress_escape', `<div class="escape_bt"`,'onOpButton', null, null, 'none' );
                    }
                    if(args.buttons[nb] == "validatemulti")
                    {
                        this.addActionButton( 'validatemulti', _("Validate selection") ,'onOpValidate_Multi_Bendt', null, null, 'blue' );
                        dojo.addClass( 'validatemulti', 'disabled');
                                
                    }
            }
                break;

            
                case "playerTurnMultiConfirmBid":
                    const player_id2 = this.getCurrentPlayerId()
                    
                    for( var nb in args.buttons[player_id2])
                    { 
                        if(args.buttons[player_id2][nb] == "yes")
                            {
                                this.addActionButton( 'yes', _("Yes") ,'onOpButtonConfirmBid', null, null, 'blue' );
                                this.startActionTimer('yes', this.timer, 1);
                            }
                            if(args.buttons[player_id2][nb] == "no")
                            {
                                this.addActionButton( 'no', _("No") ,'onOpButtonConfirmBid', null, null, 'red' );
                            }
                           
                    }
                    break;
    }
    }
},        

/////////////////////////////////////////////////////////////////////////////////         
//   _    _ _   _ _ _ _                          _   _               _     
//  | |  | | | (_) (_) |                        | | | |             | |    
//  | |  | | |_ _| |_| |_ _   _   _ __ ___   ___| |_| |__   ___   __| |___ 
//  | |  | | __| | | | __| | | | | '_ ` _ \ / _ \ __| '_ \ / _ \ / _` / __|
//  | |__| | |_| | | | |_| |_| | | | | | | |  __/ |_| | | | (_) | (_| \__ \
//   \____/ \__|_|_|_|\__|\__, | |_| |_| |_|\___|\__|_| |_|\___/ \__,_|___/
//                         __/ |                                           
//                        |___/                                            
/////////////////////////////////////////////////////////////////////////////////  

divYou : function() {
    
    var color = this.players[this.player_id].color;
    var color_bg = "";
    var you = "<span style=\"font-weight:bold;color:#" + color + ";" + color_bg + "\">" + _("You") + "</span>";
    return you;
},

divActPlayer : function() {        	
    var color = this.players[this.getActivePlayerId()].color;
    var name = this.players[this.getActivePlayerId()].name;
    var color_bg = "";
    var you = "<span style=\"font-weight:bold;color:#" + color + ";" + color_bg + "\">" + name + "</span>";
    return you;
},

format_string_recursive : function(log, args) {
    try {
        if (log && args && !args.processed) {
            args.processed = true;
           
        }
    } catch (e) {
        console.error(log,args,"Exception thrown", e.stack);
    }
    return this.inherited(arguments);
},

/*************************************************
 * 
 *  setup connections from this.args.selectable
 * on each beginning of new State (Player Turn)
 * 
 ************************************************/

setupConnections: function(selectables) {
    this.connections = [];

    selectables.forEach(elt_id => {
        const element = document.getElementById(elt_id);

        const resourceClickHandler = (evt) => this.onSelect(evt);
        element.addEventListener('click', resourceClickHandler);
        this.connections.push({ element, event: 'click', handler: resourceClickHandler });
    });

},


/*************************************************
 * 
 *  reset all connections 
 *  on leaving a State
 * 
 ************************************************/

removeConnections: function() {
    this.connections.forEach(connection => {
        const { element, event, handler } = connection;
        element.removeEventListener(event, handler);
    });
    this.connections = [];
},




/////////////////////////////////////////////////////////////////////////////////  
//         _____  _                       _                  _   _             
//        |  __ \| |                     ( )                | | (_)            
//        | |__) | | __ _ _   _  ___ _ __|/ ___    __ _  ___| |_ _  ___  _ __  
//        |  ___/| |/ _` | | | |/ _ \ '__| / __|  / _` |/ __| __| |/ _ \| '_ \ 
//        | |    | | (_| | |_| |  __/ |    \__ \ | (_| | (__| |_| | (_) | | | |
//        |_|    |_|\__,_|\__, |\___|_|    |___/  \__,_|\___|\__|_|\___/|_| |_|
//                         __/ |                                               
//                        |___/                                                
/////////////////////////////////////////////////////////////////////////////////  

     
stopEvent:function (evt) {
    if (evt) {
        evt.preventDefault();
        evt.stopPropagation();
    }
},



onSelect: function(evt)
{        	 
    // Preventing default browser reaction
     this.stopEvent( evt );

    if(this.isCurrentPlayerActive() && !this._helpMode)
    {
        
        if(evt.currentTarget.classList.contains('selectable'))
        {
               
            this.bgaPerformAction('actSelect', { arg1: evt.currentTarget.id });
        }
    
        else if(evt.currentTarget.classList.contains('selectablemulti'))
        {
            const elementId = "#" + evt.currentTarget.id;

            dojo.query(elementId).removeClass("selectablemulti");
            setTimeout(function() {
            dojo.query(elementId).addClass("selectedmulti");
            }, 10);

            setTimeout(function() {
            var elements = document.querySelectorAll('.selectedmulti');
            var nombreElements = elements.length;
            var boutonvalidate = document.getElementById('validatemulti');
            if (boutonvalidate !== null)
            {
                if(nombreElements==2)
                {
                    dojo.removeClass( 'validatemulti', 'disabled');
                }
                else
                {
                    dojo.addClass( 'validatemulti', 'disabled');
                }
            }
            }, 50);

        }

        else if(evt.currentTarget.classList.contains('selectedmulti')) 
        {
            const elementId = "#" + evt.currentTarget.id;

            dojo.query(elementId).removeClass("selectedmulti");
            setTimeout(function() {
            dojo.query(elementId).addClass("selectablemulti");
            }, 10);
         
            setTimeout(function() {
            var elements = document.querySelectorAll('.selectedmulti');
            var nombreElements = elements.length;
            var boutonvalidate = document.getElementById('validatemulti');
            if (boutonvalidate !== null)
            {
                if(nombreElements==2)
                {
                    dojo.removeClass( 'validatemulti', 'disabled');
                }
                else
                {
                    dojo.addClass( 'validatemulti', 'disabled');
                }
            }
            }, 50);

            
        }

    }

},

onOpButton: function(evt)
{
    
    // Preventing default browser reaction
    this.stopEvent( evt );
    
    this.bgaPerformAction('actButton', { arg1: evt.currentTarget.id });
    
    

},


onOpButtonConfirmBid: function(evt)
{
    
    // Preventing default browser reaction
    this.stopEvent( evt );
    
    this.bgaPerformAction('actConfirmBid', { arg1: evt.currentTarget.id });
    
    

},


onPlayerHandSelectionChanged: function()
{
    console.log( "onPlayerHandSelectionChanged" );

},

onOpValidate_Multi_Bendt: function(evt) {

    // Preventing default browser reaction
    this.stopEvent( evt );

    

     // Sélectionnez tous les éléments avec la classe spécifiée
     const elementsAvecClasse = document.querySelectorAll(".selectedmulti");

     // Convertissez la NodeList en un tableau et extrayez les IDs
     const ids = Array.from(elementsAvecClasse, element => element.id);

     let result = "";

     ids.forEach(function(id) {
        const parts = id.split("_"); // Split l'ID avec '_'
        result += (result ? "_" : "") + parts[parts.length - 1]; // Ajoute _ sauf pour le premier élément
    
    });

     
    this.bgaPerformAction('actValidate_Multi_Bendt', { arg1: result});
    
},

showLastScore: function(arg = 0) {
    

    this.bgaPerformAction('actShowLastScore', {arg1: arg}, {lock: false, checkAction: false});

},



/*************************************************
 * 
 *  players' panel  lines
 *  made of groups icon + counter
 * 
 ************************************************/


setupPlayersBoard: function() {
    console.log('Setting up the players board');

    
    Object.values(this.players).forEach((player) => {
        const playerBoardElement = document.getElementById("player_board_" + player.id);
        playerBoardElement.insertAdjacentHTML("beforeend", `<div class="b_board" id="ab_board_${player.id}"></div>`);
        playerBoardElement.insertAdjacentHTML("beforeend", `<div class="a_board" id="ai_board_${player.id}"></div>`);

  
        if( this.gamedatas.state_id == '3') {
            this.setupPanelBid( player);
        }
        
        const aiBoard = document.getElementById("ai_board_" + player.id);
        const abBoard = document.getElementById("ab_board_" + player.id);

    
        if( player.id == this.first_player_round) {
            const firstPlayerGroup = `
            <div class="icon-group">
                <div class="first" id="icon_first_player_round"></div>
            </div>`
            abBoard.insertAdjacentHTML("beforeend", firstPlayerGroup);
        }

        


        if( player.id ==this.player_id) {
                           
            abBoard.insertAdjacentHTML('beforeend', `
                <div id="help-mode-switch">
                    <input type="checkbox" class="checkbox" id="help-mode-chk" />
                    <label class="label" for="help-mode-chk">
                        <div class="ball"></div>
                    </label>
                    <svg aria-hidden="true" focusable="false" data-prefix="fad" data-icon="question-circle" class="svg-inline--fa fa-question-circle fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                        <g class="fa-group">
                            <path class="fa-secondary" fill="currentColor" d="M256 8C119 8 8 119.08 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm0 422a46 46 0 1 1 46-46 46.05 46.05 0 0 1-46 46zm40-131.33V300a12 12 0 0 1-12 12h-56a12 12 0 0 1-12-12v-4c0-41.06 31.13-57.47 54.65-70.66 20.17-11.31 32.54-19 32.54-34 0-19.82-25.27-33-45.7-33-27.19 0-39.44 13.14-57.3 35.79a12 12 0 0 1-16.67 2.13L148.82 170a12 12 0 0 1-2.71-16.26C173.4 113 208.16 90 262.66 90c56.34 0 116.53 44 116.53 102 0 77-83.19 78.21-83.19 106.67z" opacity="0.4"></path>
                            <path class="fa-primary" fill="currentColor" d="M256 338a46 46 0 1 0 46 46 46 46 0 0 0-46-46zm6.66-248c-54.5 0-89.26 23-116.55 63.76a12 12 0 0 0 2.71 16.24l34.7 26.31a12 12 0 0 0 16.67-2.13c17.86-22.65 30.11-35.79 57.3-35.79 20.43 0 45.7 13.14 45.7 33 0 15-12.37 22.66-32.54 34C247.13 238.53 216 254.94 216 296v4a12 12 0 0 0 12 12h56a12 12 0 0 0 12-12v-1.33c0-28.46 83.19-29.67 83.19-106.67 0-58-60.19-102-116.53-102z"></path>
                        </g>
                    </svg>
                </div>
            `);
            const helpModeSwitchElement = document.getElementById('help-mode-switch');
            helpModeSwitchElement.style.display = 'inline-block';
            const helpModeCheckbox = document.getElementById('help-mode-chk');
            helpModeCheckbox.addEventListener('change', () => {
                this.toggleHelpMode(helpModeCheckbox.checked);
            });
            this.addTooltip("help-mode-switch", "", _("Toggle Tooltips on Mobile mode."));

        }





    });

},



setupBoard: function () {
    console.log('Setting up the board');

    
    const gameBoardHTML = `
        <div id="resized_id">
            <div id="board_id">

                <div id="etoile"></div>
                <div id="skull2"></div>

                <div id="corner_1"></div>
                <div id="corner_2"></div>
                <div id="corner_3"></div>
                <div id="corner_4"></div>
                

                <div id="yohoho_container" class="hidden">
                <div id="yo" class="hidden"></div>
                <div id="ho_1" class="hidden"></div>
                <div id="ho_2" class="hidden"></div>
                </div> 

                <div id="round_nb">${_("Round ")}${this.round_nb} / ${this.total_rounds}</div>

                <div id="table_cards_container" class="cards-container">
                    <div class="titre">${_("Cards played")}</div>
                    <div id="table_cards" class="cards"></div>
                    
                </div> 
                <div id="bids_container" class="bids-container hidden"></div>
                <div id="tigress_container" class="bids-container hidden">
                    <div id="tigress_pirate" class="card card_pirate"></div>
                    <div id="tigress_escape" class="card card_escape"></div>
                </div>
                <div id="rosie_container" class="bids-container"></div>
    
                <div id="hand_container" class="cards-container">
                    <div class="titre" id="my_cards_title">${_("My cards")}</div>
                    <div id="my_cards" class="cards"></div>
                </div>
                
                <div id="deck_container" class="deck-container hidden">
                    <div class="titre_deck">${_("Deck")}</div>
                    <div id="deck_cards" class="cards"></div>
                </div> 

               

            </div>
        </div>   
    `
    


    const gamePlayArea = document.getElementById("game_play_area");
    gamePlayArea.insertAdjacentHTML("beforeend", gameBoardHTML);

    this.setupStocks();

    this.setupRosie();
   
    if( parseInt(this.gamedatas.rascal_container) == this.player_id) {
        this.setupRascal();
    }    
    if( parseInt(this.gamedatas.harry_container) == this.player_id) {
        this.setupHarry( this.gamedatas.harry_bids );
    }

    if( parseInt(this.gamedatas.juanita_container) == this.player_id) {
        dojo.removeClass( 'deck_container', 'hidden');
        dojo.addClass('table_cards_container', 'hidden');
    } 

    if(this.isSpectator ) {
        
        dojo.addClass('hand_container', 'hidden');
        
        let board = document.getElementById("board_id");
        board.style.height = "700px";
    }

    // ICON SHOW SCORE

    if((!this.isSpectator)&&(this.gamedatas.end_of_game == 0))
    {
        const element = document.getElementById('ab_board_'+this.player_id);

        
        const icon = document.createElement('div');
        icon.id = 'icon_score';
        element.appendChild(icon);

        html = "<div class='tooltip_content'><span class='tooltip_desc'>"+_('Scorepad')+"</span></div>";
        this.addCustomTooltip( 'icon_score', html);
        


        document.getElementById("icon_score").addEventListener("click", (evt) => {
        this.showLastScore(); 
        });
    }

    

},

setupBids: function () {
    console.log('setup Bids');

    console.log(' round_max_bid', this.round_max_bid );
    console.log(this.players[this.player_id]);

    const bidsContainer = document.getElementById("bids_container");

    for (let i = 0; i <= this.round_max_bid; i++) {
        const bidHTML = `
            <div id="bid_${i}" class="bid" style="background-position: ${i * -100}% 0%;"></div>
        `;
        bidsContainer.insertAdjacentHTML('beforeend', bidHTML);
    }

    if(this.isSpectator == false ) {
        if(this.players[this.player_id].bid_validated == 1) {
            dojo.addClass('bid_'+this.gamedatas.players[this.player_id].bid,"selected");
        }
    }


},



removeBids: function () {
    // vérifier pour les listeners
    document.getElementById('bids_container').innerHTML = '';
},

removeAllPlayersBidTrick: function() {
    
    document.querySelectorAll('.bid_pannel').forEach(el => this.destroy(el));
    document.querySelectorAll('.trick_compteur').forEach(el => el.innerText = 0);
    document.querySelectorAll('.trick_pannel').forEach(el => this.destroy(el));
},

removeOnePlayerBidTrick: function(player) {
    const bid = document.getElementById('bid_pannel_'+player.id);
    this.destroy(bid);

    const trick = document.getElementById('trick_pannel_'+player.id);
    this.destroy(trick);



},

setupPanelBid: function (player) {
    console.log('panel_bid', player);

    
    const bidpannel = document.createElement('div');
    bidpannel.id = 'bid_pannel_'+player.id;
    bidpannel.className = 'bid_pannel';
    bidpannel.style.backgroundPositionX = `${player.bid * -100}%`;
    const container = document.getElementById('ai_board_'+player.id);
    container.prepend(bidpannel);

    html = "<div class='tooltip_content'><span class='tooltip_desc'>"+_('Bid')+"</span></div>";
    this.addCustomTooltip( 'bid_pannel_'+player.id, html);


    const trickpannel = document.createElement('div');
    trickpannel.id = 'trick_pannel_'+player.id;
    trickpannel.className = 'trick_pannel';
    container.prepend(trickpannel);

    html = "<div class='tooltip_content'><span class='tooltip_desc'>"+_('Tricks')+"</span></div>";
    this.addCustomTooltip( 'trick_pannel_'+player.id, html);

    const containertrick = document.getElementById('trick_pannel_'+player.id);

    const trickimage = document.createElement('div');
    trickimage.id = 'trick_image_'+player.id;
    trickimage.className = 'trick_image';
    containertrick.appendChild(trickimage);

    const trickcompteur = document.createElement('div');
    trickcompteur.id = 'trick_compteur_'+player.id;
    trickcompteur.className = 'trick_compteur';
    containertrick.appendChild(trickcompteur);
    const compteur = document.getElementById('trick_compteur_'+player.id);
    compteur.innerText = player.tricks;


    
},



setupRosie: function () {

   const container = document.getElementById('rosie_container');

    Object.values(this.players).forEach((player) => {


       // Création du mini-container pour chaque joueur
        const playerContainer = document.createElement('div');
        playerContainer.classList.add('rosie-player-container'); // On pourra styler ça
        playerContainer.id = `rosie_${player.id}`;
        // Création de l'avatar
        const avatarImage = document.getElementById('avatar_' + player.id);
        if (avatarImage) {
            const newAvatar = document.createElement('img');
            newAvatar.src = avatarImage.src;
            newAvatar.id = 'rosie_avatar_' + player.id;
            newAvatar.classList.add('emblem');
            playerContainer.appendChild(newAvatar);
        }

        // Ajout du nom du joueur
        const nameSpan = document.createElement('span');
        const player_css = this.getFormattedPlayerName(player.id); // nom formaté du joueur

        nameSpan.innerHTML = player_css;
        playerContainer.appendChild(nameSpan);

        // Ajout du mini-container dans le container principal
        container.appendChild(playerContainer);

        // Tooltip si besoin
        this.addTooltipHtml('rosie_avatar_' + player.id, player.name, '');
    });

    if( this.gamedatas.rosie_container == 0 || this.isCurrentPlayerActive() == false) {
        dojo.addClass('rosie_container', 'hidden');
    }

    if( this.gamedatas.rosie_container != 0 && this.isCurrentPlayerActive() == true) {
        dojo.addClass('table_cards_container', 'hidden');
    }


},

setupRascal: function () {
    console.log('setup Rascal');

    const bidsContainer = document.getElementById("bids_container");
    const bidHTML = `
        <div id="bid_0" class="bid" style="background-position: 0% 0%;"></div>
        <div id="bid_10" class="bid" style="background-position: -1000% 0%;"></div>
        <div id="bid_20" class="bid" style="background-position: -1100% 0%;"></div>
    `;
    bidsContainer.insertAdjacentHTML('beforeend', bidHTML);
    dojo.removeClass('bids_container', 'hidden');

    if( this.gamedatas.rascal_container != 0 && this.isCurrentPlayerActive() == true) {
        dojo.addClass('table_cards_container', 'hidden');
    }
},

setupHarry: function (harry_bids) {
    console.log('setup Harry');

    console.log(harry_bids);

    const bidsContainer = document.getElementById("bids_container");

    Object.values(harry_bids).forEach( bid => {
        const bidHTML = `
            <div id="bid_${bid}" class="bid" style="background-position: ${bid * -100}% 0%;"></div>
        `;
        bidsContainer.insertAdjacentHTML('beforeend', bidHTML);
    });
    dojo.removeClass('bids_container', 'hidden');

    if( this.gamedatas.harry_container != 0 && this.isCurrentPlayerActive() == true) {
        dojo.addClass('table_cards_container', 'hidden');
    }
},



setupStocks: function() {

    // Stock pour la main du joueur
    this.handStock = this.createStockForCards(this, $('my_cards'));
    this.handStock.setSelectionMode(0);
    this.handStock.setOverlap(60, 0);
    for( var card_id = 1; card_id <= 70; card_id++) {
        this.handStock.addItemType(card_id, card_id, g_gamethemeurl + 'img/SK_cards.jpg', card_id-1);
    }
    //this.hand.onChangeSelection = this.onActionPlayCard.bind(this);
    //this.handStock.setSelectionAppearance('class');
    this.handStock.onItemCreate = this.setupNewCard.bind(this); //add tooltip


    // Stock pour la table : pas de weight pour la table pour ne pas classer les cartes selon leur type.
    this.tableStock = this.createStockForCards(this, $('table_cards'));
    for( var card_id = 1; card_id <= 70; card_id++) {
        this.tableStock.addItemType(card_id, 0, g_gamethemeurl + 'img/SK_cards.jpg', card_id-1);
    }
    this.tableStock.setSelectionMode(0);
    this.tableStock.use_vertical_overlap_as_offset = false;
    this.tableStock.vertical_overlap = -15;
    this.tableStock.onItemCreate = this.setupNewCard.bind(this); //add tooltip


    // Stock pour le deck pour Juanita
    this.deckStock = this.createStockForCards(this, $('deck_cards'));
    this.deckStock.setSelectionMode(0);
    this.deckStock.setOverlap(30, 0);
    this.deckStock.autowidth = true;
    this.deckStock.use_vertical_overlap_as_offset = false; // OK

    for( var card_id = 1; card_id <= 70; card_id++) {
        this.deckStock.addItemType(card_id, card_id, g_gamethemeurl + 'img/SK_cards.jpg', card_id-1);
    }

    this.deckStock.onItemCreate = this.setupNewCard.bind(this); //add tooltip




    // Cards in player's hand
    Object.values(this.my_hand).forEach( card =>
    {
        const card_type = this.getStockCardType(card);
        this.handStock.addToStockWithId(card_type, card.id);
    } );
    this.handStock.updateDisplay();

    // Prévoir le cas pour Tigress ( 2 autres cartes ?) 
    console.log('this_table', this.table);
    Object.values(this.table).forEach((card) => {

        console.log('this_table_card', card);


        const card_type = this.getStockCardType(card);
        this.tableStock.addToStockWithId(card_type, card.id);

        const player = this.players[card.location_arg];

        const card_div = document.getElementById('table_cards_item_' + card.id);
        dojo.place('<div class="player-title" style="color: #' + player.color + '">' + player.name + '</div>', card_div);
    });

    Object.values(this.gamedatas.deck).forEach( card =>
    {
        const card_type = this.getStockCardType(card);
        this.deckStock.addToStockWithId(card_type, card.id);
    } );
    this.deckStock.updateDisplay();



    //Tigress

    if(this.gamedatas.tigress_role != 0)
    {

        const card = document.getElementById('table_cards_item_'+this.gamedatas.tigress_cardid);

        if(this.gamedatas.tigress_role == 1)
        {
        const icon = document.createElement('div');
        icon.id = 'icon_tigress';
        icon.className = 'pirate_icon';
        card.appendChild(icon);
        }

        if(this.gamedatas.tigress_role == 2)
        {
        const icon = document.createElement('div');
        icon.id = 'icon_tigress';
        icon.className = 'escape_icon';
        card.appendChild(icon);
        }

    }


},





getStockCardType: function( card ) {
    //console.log(card);
    let card_id;
    if (card.type == 'green') {
        card_id = card.type_arg;
    } else if (card.type == 'purple') {
        card_id = 14 + parseInt(card.type_arg);
    } else if (card.type == 'yellow') {
        card_id = 28 + parseInt(card.type_arg);
    } else if (card.type == 'black') {
        card_id = 42 + parseInt(card.type_arg);
    } else if (card.type == 'pirate') {
        card_id = 56 + parseInt(card.type_arg) ; 
    } else if (card.type == 'mermaid') {
        card_id = 61 + parseInt(card.type_arg);
    } else if (card.type == 'skull_king') {
        card_id = 64;
    } else if (card.type == 'tigress') {
        card_id = 65;
    } else if (card.type == 'escape') {
        card_id = 66;
    } else if (card.type == 'kraken') {
        card_id = 67;
    } else if (card.type == 'white_whale') {
        card_id = 68;
    } else if (card.type == 'loot') {
        card_id = 69;
    }
    return card_id

},


createStockForCards: function(page, element)
{
    let stock = new ebg.stock();
    stock.create(page, element, CARD_WIDTH, CARD_HEIGHT);
    stock.image_items_per_row = CARDS_PER_ROW;

    /*addItemType(type: number, weight: number, image: string, image_position: number )*/


    return stock;
},


setupNewCard: function( card_div, card_type_id, card_id ) {
    // récupère automatiquement ces 3 propriétés générés par le constructeur de la classe Stock
    //console.log('card_div',card_div );
    //console.log('card_type_id',card_type_id );
    //console.log('card_id',card_id );

    let html = '<div class="tooltip_content">';

    const x = '-' + (card_type_id-1) % 14 + '00%';
    const y = '-' + Math.floor((card_type_id-1) / 14) + '00%';

    // Ajout de la carte (image) à gauche
    html += `<div class="card_container">
               <div id="sk_card_toolt_${card_type_id}" class="card" style="background-position:${x} ${y};"></div>
            </div>`;
    // Ajout des informations à droite
    html += `<div class="card_info_container">`;

    let card_name;
    let card_desc;
    let card_desc_2;
    let pirate_name;
    let pirate_ability;

    if( card_type_id < 15) {
        card_name = this.suit_cards['green'].name;
    }
    else if( card_type_id < 29) {
        card_name = this.suit_cards['purple'].name;
    }
    else if( card_type_id < 43) {
        card_name = this.suit_cards['yellow'].name;
    }
    else if( card_type_id < 57) {
        card_name = this.suit_cards['black'].name;
    }
    else if( card_type_id < 62) {
        card_name = this.special_cards['pirate'].name;
        card_desc = this.special_cards['pirate'].desc_1;
        card_desc_2 = this.special_cards['pirate'].desc_2;
    }
    else if(( card_type_id == 62)||( card_type_id == 63)) {
        card_name = this.special_cards['mermaid'].name;
        card_desc = this.special_cards['mermaid'].desc_1;
        card_desc_2 = this.special_cards['pirate'].desc_2;
    } 
    else if( card_type_id == 64) {
        card_name = this.special_cards['skull_king'].name;
        card_desc = this.special_cards['skull_king'].desc_1;
        card_desc_2 = this.special_cards['pirate'].desc_2;
    }
    else if( card_type_id == 65) {
        card_name = this.special_cards['tigress'].name;
        card_desc = this.special_cards['tigress'].desc_1;
        card_desc_2 = this.special_cards['pirate'].desc_2;
    }
    else if( card_type_id == 66) {
        card_name = this.special_cards['escape'].name;
        card_desc = this.special_cards['escape'].desc_1;
        
    }  
    else if( card_type_id == 67) {
        card_name = this.special_cards['kraken'].name;
        card_desc = this.special_cards['kraken'].desc_1;
        
    } 
    else if( card_type_id == 68) {
        card_name = this.special_cards['white_whale'].name;
        card_desc = this.special_cards['white_whale'].desc_1;
        
    } 
    else if( card_type_id == 69) {
        card_name = this.special_cards['loot'].name;
        card_desc = this.special_cards['loot'].desc_1;
       
    }  
    // Ajout du nom de la carte
    html += `<span class='tooltip_title'>${_(card_name)}</span>`;
    if(( card_type_id > 56)&&( card_type_id < 66)) {
        html += `<br><span class='tooltip_desc'>${_(card_desc)}</span>`;
        html += `<br><span class='tooltip_info'>${_(card_desc_2)}</span>`;
console.log('PIRATE POWER',parseInt(this.gamedatas.pirate_powers_mode));

        if( parseInt(this.gamedatas.pirate_powers_mode) > 1 && card_type_id < 62 ) {
            console.log('PIRATE',this.pirate_cards[card_type_id-56]);
            pirate_name = this.pirate_cards[card_type_id-56].name;
            pirate_ability = this.pirate_cards[card_type_id-56].ability;
            html += `<br><span class='tooltip_title'>${_(pirate_name)}</span>`;
            html += `<br><span class='tooltip_desc'>${_(pirate_ability)}</span>`;
        }



        html += '</div></div>'; // Fermeture des div 
        this.addCustomTooltip( card_id, html );
    }
    if( card_type_id >= 66) {
        html += `<br><span class='tooltip_desc'>${_(card_desc)}</span>`;
        html += '</div></div>'; // Fermeture des div 
        this.addCustomTooltip( card_id, html );
        
    }

    
    

},



setupCounters: function() {

    console.log( 'setting up counters');

        Object.values(this.players).forEach(player => {
        // Compteur pour les tiles
    /*    this.tile_counter[player.id] = new ebg.counter();
        this.tile_counter[player.id].create('tile_counter_' + player.id);
        const tile_value = isNaN(this.tiles_collected[player.id]) ? 0 : this.tiles_collected[player.id];
        this.tile_counter[player.id].toValue(tile_value);*/

    });

},   


setupTooltips:function () {

    html = "<div class='tooltip_content'><span class='tooltip_desc'>"+_('Round first player')+"</span></div>";
    this.addCustomTooltip( `icon_first_player_round`, html);

},

onScreenWidthChange: function () {
},


/* THOUN HELP BUTTON*/

addHelp: function() {
    // Créer l'élément bouton
    const helpButton = document.createElement('div');
    helpButton.id = 'skullking_help_button';
    helpButton.textContent = '?';

    // Ajouter le bouton au body
    document.body.appendChild(helpButton);

    // Ajouter un gestionnaire d'événement pour afficher une aide (modifiable selon besoin)
    helpButton.addEventListener('click', () => {
        this.showHelpModal();
    });
},


showHelpModal: function() {
    // Vérifie si la modale existe déjà
    if (document.getElementById('helpModal')) return;

    // Création de la modale
    const modal = document.createElement('div');
    modal.id = 'helpModal';
    modal.className = 'modal';




    let html = '<div class="modal-content">';
        html += '<span class="close">&times;</span>';
        //html += '<div class="tooltip_content">';
        html += "<div class='tooltip_bigtitle'>"+_('Cards Summary')+"</div>";
        html += "<div class='tooltip_icons_container'>";

        html += "<div class='tooltip_icon_container'>";
          html += `<div class="icon_container">
                     <div id="sk_icon_toolt_" class="icon" style="background-position:-200% 0%;"></div>
                   </div>`;
        html += `<div class="info_container">`;
        html += "<div class='tooltip_subtitle'>"+_('the skull king')+"</div>";
        html += "<div class='tooltip_desc'>"+_('Defeats all cards <u>except</u> the Mermaids.')+"</div>";
        html += "</div></div>";

        html += "<div class='tooltip_icon_container'>";
          html += `<div class="icon_container">
                     <div id="sk_icon_toolt_" class="icon" style="background-position:-000% 0%;"></div>
                   </div>`;
        html += `<div class="info_container">`;
        html += "<div class='tooltip_subtitle'>"+_('pirates')+"</div>";
        html += "<div class='tooltip_desc'>"+_('Defeat all cards <u>except</u> the Skull King.')+"</div>";
        html += "</div></div>";


        html += "<div class='tooltip_icon_container'>";
          html += `<div class="icon_container">
                     <div id="sk_icon_toolt_" class="icon" style="background-position:-100% 0%;"></div>
                   </div>`;
        html += `<div class="info_container">`;
        html += "<div class='tooltip_subtitle'>"+_('mermaids')+"</div>";
        html += "<div class='tooltip_desc'>"+_('Defeat all numbered cards <u>and</u> the Skull King.')+"</div>";
        html += "</div></div>";

        html += "<div class='tooltip_icon_container'>";
          html += `<div class="icon_container">
                     <div id="sk_icon_toolt_" class="icon" style="background-position:-1100% 0%;"></div>
                   </div>`;
        html += `<div class="info_container">`;        
        html += "<div class='tooltip_subtitle'>"+_('trump suit')+"</div>";
        html += "<div class='tooltip_desc'>"+_('Defeat all the <u>standard</u> suits. Highest trump wins.')+"</div>";
        html += "</div></div>";
        
        html += "<div class='tooltip_icon_container'>";
          html += `<div class="icon_container">
                     <div id="sk_icon_toolt_" class="icon" style="background-position:-800% 0%;"></div>
                     <div id="sk_icon_toolt_" class="icon" style="background-position:-900% 0%;"></div>
                     <div id="sk_icon_toolt_" class="icon" style="background-position:-1000% 0%;"></div>
                   </div>`;
        html += `<div class="info_container">`;        
        html += "<div class='tooltip_subtitle'>"+_('standard suits')+"</div>";
        html += "<div class='tooltip_desc'>"+_('high number wins of suit <u>that was lead</u>.')+"</div>";
        html += "</div></div>";

        html += "<div class='tooltip_icon_container'>";
          html += `<div class="icon_container">
                     <div id="sk_icon_toolt_" class="icon" style="background-position:-400% 0%;"></div>
                   </div>`;
        html += `<div class="info_container">`;        
        html += "<div class='tooltip_subtitle'>"+_('escapes')+"</div>";
        html += "<div class='tooltip_desc'>"+_('Lose to all cards, even previously played escapes.')+"</div>";
        html += "</div></div>";
        
        html += "<div class='tooltip_icon_container'>";
          html += `<div class="icon_container">
                     <div id="sk_icon_toolt_" class="icon" style="background-position:-300% 0%;"></div>
                   </div>`;
        html += `<div class="info_container">`;        
        html += "<div class='tooltip_subtitle'>"+_('the tigress')+"</div>";
        html += "<div class='tooltip_desc'>"+_('Either Escape or Pirate. Declare when played.')+"</div>";
        html += "</div></div>";

        html += "<div class='tooltip_icon_container'>";
          html += `<div class="icon_container">
                     <div id="sk_icon_toolt_" class="icon" style="background-position:-500% 0%;"></div>
                   </div>`;
        html += `<div class="info_container">`;        
        html += "<div class='tooltip_subtitle'>"+_('kraken')+"</div>";
        html += "<div class='tooltip_desc'>"+_('Trick discarded. Next trick led by player who would have won the trick..')+"</div>";
        html += "</div></div>";

        html += "<div class='tooltip_icon_container'>";
        html += `<div class="icon_container">
                <div id="sk_icon_toolt_" class="icon" style="background-position:-600% 0%;"></div>
            </div>`;
        html += `<div class="info_container">`;        
        html += "<div class='tooltip_subtitle'>"+_('white whale')+"</div>";
        html += "<div class='tooltip_desc'>"+_('High number wins. Suits and special card don\'t matter.')+"</div>";
        html += "</div></div>";

        html += "<div class='tooltip_desc'>"+_('<i>When the Kraken and White Whale are played in the same trick, only the second beast\'s effect applies. The first is defeated and becomes an escape card.</i>')+"</div>";

        html += "<div class='tooltip_icon_container'>";
        html += `<div class="icon_container">
                <div id="sk_icon_toolt_" class="icon" style="background-position:-700% 0%;"></div>
            </div>`;
        html += `<div class="info_container">`;        
        html += "<div class='tooltip_subtitle'>"+_('loot')+"</div>";
        html += "<div class='tooltip_desc'>"+_('Form an allaince between you and anther player.')+"</div>";
        html += "</div></div>";

        html += "<div class='tooltip_desc'>"+_('<i>Playing a loot card enters you into an alliance with the player who captures it. If both of you bid correctly, you are each awarded 20 bonus points.</i>')+"</div>";



        html += '</div>'


        html += '</div></div>';

    modal.innerHTML = html;


    document.body.appendChild(modal);

    // Sélection des éléments de la modale
    const closeButton = modal.querySelector('.close');

    // Affichage de la modale
    modal.style.display = 'flex';

    // Fermeture en cliquant sur la croix
    closeButton.addEventListener('click', () => modal.remove());

    // Fermeture en cliquant en dehors de la modale
    window.addEventListener('click', (event) => {
        if (event.target === modal) modal.remove();
    });
},

showDeckModal: function() {
    // Vérifie si la modale existe déjà
    if (document.getElementById('deckModal')) return;

    // Création de la modale
    const deck_modal = document.createElement('div');
    deck_modal.id = 'deckModal';
    deck_modal.className = 'modal';




    deck_modal.innerHTML = `
        <div class="modal-content">
            <span class="close">&times;</span>
            let html = '<div class="tooltip_content">'
            <div id='deck_cards' class='cards'></div>
        </div>
    `;


    document.body.appendChild(deck_modal);

    // Sélection des éléments de la modale
    const deck_closeButton = deck_modal.querySelector('.close');

    // Affichage de la modale
    deck_modal.style.display = 'flex';

    // Fermeture en cliquant sur la croix
    deck_closeButton.addEventListener('click', () => deck_modal.remove());

    // Fermeture en cliquant en dehors de la modale
    window.addEventListener('click', (event) => {
        if (event.target === deck_modal) deck_modal.remove();
    });
},

/* THOUN HELP BUTTON*/


animateAndRemoveCard: function(token_css) {
    const tokenElement = document.getElementById(token_css);

    if (!tokenElement) 
        return Promise.resolve();

    if (this.instantaneousMode) {
        return Promise.resolve(); // Important pour compatibilité avec `await`
    }

    return new Promise((resolve) => {
        tokenElement.classList.add("sprite-disappear");
        tokenElement.addEventListener("animationend", resolve, { once: true });
    });
},

///////////////////////////////////////////////////////////////////////////////// 
//       _   _       _   _  __ _           _   _                 
//      | \ | |     | | (_)/ _(_)         | | (_)                
//      |  \| | ___ | |_ _| |_ _  ___ __ _| |_ _  ___  _ __  ___ 
//      | . ` |/ _ \| __| |  _| |/ __/ _` | __| |/ _ \| '_ \/ __|
//      | |\  | (_) | |_| | | | | (_| (_| | |_| | (_) | | | \__ \
//      |_| \_|\___/ \__|_|_| |_|\___\__,_|\__|_|\___/|_| |_|___/
//                                                                 
/////////////////////////////////////////////////////////////////////////////////  

/*
    notif_placePenguin: async function(args) {
        debug('notif_placePenguin: player places a Penguin', args);





        await this.wait(400);
    },

*/

notif_showBids: async function(args) {
    // anime l'affichage des paris dans les players panels

    //on enlève les bids
    this.removeBids();
    dojo.addClass('bids_container', 'hidden');
    dojo.removeClass('table_cards_container', 'hidden');

    Object.values(args.bids).forEach((player) => {
        this.setupPanelBid( player );
        
    });

    

},


notif_playCard: async function(args) {
    // déplace une carte jouée de la main du joueur actif vers le prochain emplacement de la table avec la déco du joueur
    // ou la fait apparaître pour les autres joueurs

	const card = args.card_before;


	// Add the card to the table

    this.table[card.id] = card; // remplacer l'indice par table.length si ça sert à quelque chose...
    const div_id = this.player_id == card.location_arg ? `my_cards_item_${card.id}` : undefined;
    //const div_id = undefined;

    const card_type = this.getStockCardType(card);

    const player = this.players[card.location_arg];



    this.tableStock.addToStockWithId(card_type, card.id, div_id);
    const card_div = document.getElementById('table_cards_item_' + card.id);
    dojo.place('<div class="player-title" style="color: #' + player.color + '">' + player.name + '</div>', card_div);

    //this.createCardTooltip( card.type, card.id);

        // Destroy the card for the current player
    if (this.player_id == card.location_arg) {

        this.handStock.removeFromStockById(card.id);
        //delete this.my_hand[card.id];
        //dojo.query('#hand_cards .stockitem').removeClass('unselectable');
    }


},

notif_endTrick: async function(args) {
    // on met à jour le score pour les cartes spéciales capturées  
    // fait disparaître les cartes posées sur la table
    // met à jour le pari du joueur qui a remporté le pli
    // change le joueur actif

    this.table = [];

    const winner_id = args.winner_id;
    await this.tableStock.removeAllTo('overall_player_board_' + winner_id);

    
    this.removeOnePlayerBidTrick(args.winner_infos);
    this.setupPanelBid(args.winner_infos);

    
},


notif_endRound: async function(args) {
    // on met à jour le score à la fin de la manche en fonction des paris
    this.first_player_round = args.first_player_round;
    const icon = document.getElementById("icon_first_player_round");
    const newBoard = document.getElementById("ab_board_" + args.next_player_round);
    //newBoard.appendChild(icon.parentElement);
    newBoard.prepend(icon.parentElement); // insère en premier

    // on supprime tous les bids
    document.querySelectorAll('.bid-group').forEach(el => el.remove());
    Object.values(this.players).forEach((player) => {
        this.players[player.id].bid = 0;
        this.players[player.id].bid_validated = 0;
        
    });

    // on met à jour le pari maximal
    this.round_max_bid = args.round_max_bid;
    console.log( 'new round max bid', this.round_max_bid);

    //on met à jour le n° du round
    this.round_nb += 1;
    document.getElementById('round_nb').innerText = _('Round ') + this.round_nb + ' / ' + this.total_rounds;

    this.removeAllPlayersBidTrick();
},




notif_drawCards: async function(args) {
    // chaque joueur reçoît des cartes lors de la nouvelle manche
    Object.values(args.cards).forEach(card => {
        const card_type = this.getStockCardType(card);
        this.handStock.addToStockWithId(card_type, card.id, undefined);
    });
},


notif_krakenEffect: async function(args) {

    for (const card of Object.values(args.cards)) { // pas de forEach quand il y a des await
        await this.animateAndRemoveCard(`table_cards_item_${card.id}`); 
        await this.wait(200); // Petit délai pour éviter un retrait trop rapide
    }

    await this.tableStock.removeAll();
},

/*notif_whaleEffect: async function(args) {
  // les cartes spéciale deviennent des escapes ?
},*/

notif_lootEffect: async function(args) {
  // relie deux joueurs avec un icone loot
},



notif_rosieEffect: async function(args) {
  // choisis le joueur suivant
  dojo.removeClass('rosie_container', 'hidden');
  dojo.addClass('table_cards_container', 'hidden');
},

notif_rosieEffectDone: async function(args) {
  // choisis le joueur suivant
  dojo.addClass('rosie_container', 'hidden');
  dojo.removeClass('table_cards_container', 'hidden');
},



notif_bendtEffect: async function(args) {
  //ajoute deux cartes
    Object.values(args.cards).forEach(card => {
        const card_type = this.getStockCardType(card);
        this.handStock.addToStockWithId(card_type, card.id, undefined);
    });

},

notif_bendtEffectDone: async function(args) {
  //défausse deux cartes


    for (const card of Object.values(args.cards)) { // pas de forEach quand il y a des await
        await this.animateAndRemoveCard(`my_cards_item_${card.id}`); 
        await this.wait(200); // Petit délai pour éviter un retrait trop rapide
        await this.handStock.removeFromStockById(card.id);
    }

},



notif_rascalEffect: async function(args) {
  // parie 0,10 ou 20
  dojo.removeClass('bids_container', 'hidden');
  dojo.addClass('table_cards_container', 'hidden');
  this.setupRascal();
},

notif_rascalEffectDone: async function(args) {
  // parie 0,10 ou 20
    this.removeBids();
    dojo.addClass('bids_container', 'hidden');
    dojo.removeClass('table_cards_container', 'hidden');
},



notif_juanitaEffect: async function(args) {
  // voir le deck
  console.log('juanita_effect');

    Object.values(args.deck).forEach( card =>
    {
        const card_type = this.getStockCardType(card);
        this.deckStock.addToStockWithId(card_type, card.id);
    } );
    this.deckStock.updateDisplay();

  dojo.removeClass( 'deck_container', 'hidden');
  dojo.addClass('table_cards_container', 'hidden');
},

notif_juanitaEffectDone: async function(args) {
  // voir le deck
  console.log('juanita_effect_done');
  await this.deckStock.removeAll();
  dojo.addClass( 'deck_container', 'hidden');
  dojo.removeClass('table_cards_container', 'hidden');
},



notif_harryEffect: async function(args) {
  // bid +1 ou -1
  dojo.removeClass('bids_container', 'hidden');
  dojo.addClass('table_cards_container', 'hidden');
  this.setupHarry(args.harry_bids);
},

notif_harryEffectDone: async function(args) {
  // bid +1 ou -1
  this.removeBids();
  dojo.addClass('bids_container', 'hidden');
  dojo.removeClass('table_cards_container', 'hidden');

    
},

notif_majBidHarry: async function(args) {
  // bid +1 ou -1
    this.removeOnePlayerBidTrick(args.bid_infos);
    this.setupPanelBid(args.bid_infos);
},


notif_yohoho: async function(args) {

    if(this.getGameUserPreference(102) == 1  && this.bgaAnimationsActive())
    {

        dojo.removeClass('yohoho_container', 'hidden');
        
        setTimeout(() => 
            {   dojo.removeClass('yo', 'hidden');          
                dojo.addClass( 'yo', 'animateyohoho');
            }, "100");
        setTimeout(() => 
            {   
                dojo.removeClass('ho_1', 'hidden');          
                dojo.addClass( 'ho_1', 'animateyohoho');
            }, "600");
        setTimeout(() => 
            {   
                dojo.removeClass('ho_2', 'hidden');         
                dojo.addClass( 'ho_2', 'animateyohoho');
            }, "1100");

        setTimeout(() => 
            {   dojo.addClass('yo', 'hidden');
                dojo.addClass('ho_1', 'hidden');
                dojo.addClass('ho_2', 'hidden');
                dojo.addClass('yohoho_container', 'hidden');
            }, "2000");

        }
  
    },


    notif_score: function( args ){
             
        this.scoreCtrl[ args.playerid ].toValue( args.score );
    },
  
    notif_scoreButton: function( args ){
        
        
        const title = document.getElementById('popin_tableWindow_title');

        const text = title.textContent;
        const match = text.match(/Round (\d+)/);
        const roundNumber = parseInt(match[1], 10);
        
        if(args.viewround > 1)
        {
            const carre1 = document.createElement('div');
            carre1.id = 'carre1';
            carre1.className = 'chevron_gauche';
            title.prepend(carre1);

            if(!this.isSpectator)
            {
                document.getElementById("carre1").addEventListener("click", (evt) => {
                this.showLastScore(roundNumber - 1); 
                });
            }

        }

        else
        {
            const carre1 = document.createElement('div');
            carre1.id = 'carre1';
            carre1.className = 'carreneutre';
            title.prepend(carre1);
        }

        if(args.viewround  < args.round )
        {
            const carre2 = document.createElement('div');
            carre2.id = 'carre2';
            carre2.className = 'chevron_droite';
            title.append(carre2);

            if(!this.isSpectator)
            {
                document.getElementById("carre2").addEventListener("click", (evt) => {
                this.showLastScore(roundNumber + 1); 
                });
            }
        }

        else
        {
            const carre2 = document.createElement('div');
            carre2.id = 'carre2';
            carre2.className = 'carreneutre';
            title.append(carre2);
        }
       
        
    },

    notif_tigressRole: function( args ){
             
        const card = document.getElementById('table_cards_item_'+args.card_id);

        if(card){

            if(args.role == 1)
            {
                const icon = document.createElement('div');
                icon.id = 'icon_tigress';
                icon.className = 'pirate_icon';
                card.appendChild(icon);
            }

            if(args.role == 2)
            {
                const icon = document.createElement('div');
                icon.id = 'icon_tigress';
                icon.className = 'escape_icon';
                card.appendChild(icon);
            }
        }
        
    },

    notif_removeIconScore: function()
    {
        const element = document.getElementById('icon_score');
        if((element)&&(!this.isSpectator))
        {
            element.remove();
        }
      
    },



/*******************************
 ****** UTILS TISAAC *******
 ******************************/


/*******************************
 ****** HELP MODE TISAAC *******
    ******************************/
/**
 * Toggle help mode
 */
toggleHelpMode(b) {
    if (b) 
        this.activateHelpMode();
    else 
        this.desactivateHelpMode();
},

activateHelpMode() {
    this._helpMode = true;
    dojo.addClass('ebd-body', 'help-mode');
    this._displayedTooltip = null;

    this._boundCloseTooltip = this.closeCurrentTooltip.bind(this); // ✅ Store reference
    document.body.addEventListener('click', this._boundCloseTooltip);
},

desactivateHelpMode() {
    this.closeCurrentTooltip();
    this._helpMode = false;
    dojo.removeClass('ebd-body', 'help-mode');

    if (this._boundCloseTooltip) {
        document.body.removeEventListener('click', this._boundCloseTooltip); // ✅ Remove using same reference
        this._boundCloseTooltip = null;
    }
},


closeCurrentTooltip() {
    if (!this._helpMode) 
        return;
    if (this._displayedTooltip == null) 
        return;
    else {
        this._displayedTooltip.close();
        this._displayedTooltip = null;
    }
},

    /*
    * Custom connect that keep track of all the connections
    *  and wrap clicks to make it work with help mode
    */
connect(node, action, callback) {
    this._connections.push(dojo.connect($(node), action, callback));
},

onClick(node, callback, temporary = true) {
    let safeCallback = (evt) => {
        evt.stopPropagation();
        if (this.isInterfaceLocked()) 
            return false;
        if (this._helpMode) 
            return false;
        callback(evt);
    };

    if (temporary) {
        this.connect($(node), 'click', safeCallback);
        dojo.removeClass(node, 'unselectable');
        dojo.addClass(node, 'selectable');
        this._selectableNodes.push(node);
    } else {
        dojo.connect($(node), 'click', safeCallback);
    }
},

    /**
     * Tooltip to work with help mode
     */


    addCustomTooltip(id, html, config = {}) {
        config = Object.assign(
            {
                delay: 400,
                midSize: true,
                forceRecreate: false,
            },
            config,
        );
    
        let isMobile = window.matchMedia('(pointer: coarse)').matches;
        let longPressTimer = null;
    
        let getContent = () => {
            let content = typeof html === 'function' ? html() : html;
            if (config.midSize) {
                content = '<div class="midSizeDialog">' + content + '</div>';
            }
            return content;
        };
    
        let node = $(id);
        if (!node) return;
    
        // Nettoyer l'ancien tooltip si nécessaire
        if (this.tooltips[id]) {
            const existing = this.tooltips[id];
            // Vérifie si l’élément DOM a changé ou si on force la recréation
            if (config.forceRecreate || existing._targetNode !== node) {
                existing.destroy();
                delete this.tooltips[id];
            } else {
                // On met simplement à jour le contenu
                existing.getContent = getContent;
                return;
            }
        }
    
        let tooltip = new dijit.Tooltip({
            getContent,
            position: this.defaultTooltipPosition,
            showDelay: config.delay,
        });
        tooltip._targetNode = node; // Pour détecter les changements ultérieurs
    
        this.tooltips[id] = tooltip;
        dojo.addClass(id, 'tooltipable');
    
        // Empêcher l'affichage au simple clic sur mobile
        dojo.connect(node, 'click', (evt) => {
            if (isMobile && !this._helpMode) {
                evt.stopPropagation();
                return;
            }
    
            if (!this._helpMode) {
                tooltip.close();
            } else {
                evt.stopPropagation();
                if (tooltip.state === 'SHOWING') {
                    this.closeCurrentTooltip();
                } else {
                    this.closeCurrentTooltip();
                    tooltip.open(node);
                    this._displayedTooltip = tooltip;
                }
            }
        });
    
        tooltip.showTimeout = null;
    
        // Gestion du long press sur mobile
        dojo.connect(node, 'touchstart', () => {
            if (isMobile) {
                longPressTimer = setTimeout(() => {
                    tooltip.open(node);
                }, 500);
            }
        });
    
        dojo.connect(node, 'touchend', () => {
            if (isMobile) {
                clearTimeout(longPressTimer);
            }
        });
    
        dojo.connect(node, 'touchmove', () => {
            if (isMobile) {
                clearTimeout(longPressTimer);
            }
        });
    
        // Gestion PC classique
        dojo.connect(node, 'mouseenter', (evt) => {
            evt.stopPropagation();
            if (!this._helpMode && !this._dragndropMode) {
                if (isMobile) return;
    
                if (tooltip.showTimeout != null)
                    clearTimeout(tooltip.showTimeout);
    
                tooltip.showTimeout = setTimeout(() => {
                    if (node) tooltip.open(node);
                }, config.delay);
            }
        });
    
        dojo.connect(node, 'mouseleave', (evt) => {
            evt.stopPropagation();
            if (!this._helpMode && !this._dragndropMode) {
                tooltip.close();
                if (tooltip.showTimeout != null)
                    clearTimeout(tooltip.showTimeout);
            }
        });
    },



destroyTooltip(elem) {
    if (elem && elem.id && this.tooltips[elem.id]) {
        clearTimeout(this.tooltips[elem.id].showTimeout);
        this.tooltips[elem.id].close();
        this.tooltips[elem.id].destroy();
        delete this.tooltips[elem.id];
    }
},

destroy(elem, delayRemove = false) {
    this.destroyTooltip(elem);
    this.empty(elem);
    if(!delayRemove) 
    elem.remove();
},

empty(container) {

    container = $(container);
    container.childNodes.forEach((node) => {
    //!! destroy node makes gap in LOOP because of removing them
    this.destroy(node,true);
    });
    container.childNodes.forEach((node) => {
    node.remove();
    });
    container.innerHTML = '';
},









});             
});