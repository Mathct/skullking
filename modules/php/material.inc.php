<?php


$this->_SUIT_CARDS = [
    "green" => ["name" => clienttranslate('Parrot')],
    "purple" => ["name" => clienttranslate('Pirate Map')],
    "yellow" => ["name" => clienttranslate('Treasure Chest')],
    "black" => ["name" => clienttranslate('Jolly Roger')]
];

$this->_SPECIAL_CARDS = [
    "tigress" => [
        "name" => clienttranslate('Tigress (x1)'),
        "name2" => clienttranslate('Tigress'),
        "desc_1" => clienttranslate('The cunning Tigress chooses her battles wisely. When you play the Tigress, you must declare whether she will count as a Pirate or an Escape; the choice is yours! She takes on all characteristics of either a pirate or an escape.'),
        "desc_2" => clienttranslate('')
    ],
    "skull_king" => [
        "name" => clienttranslate('Skull King (x1)'),
        "desc_1" => clienttranslate('The scourge of the seas is the trump of Pirates and beats all numbered cards and Pirates (including the Tigress, when played as a Pirate). The only ones who can defeat him are the Mermaids, luring him into the sea with their precious treasure.'),
        "desc_2" => clienttranslate('If a Pirate, the Skull King; and a Mermaid are all played in the same trick, the Mermaid always wins the trick, regardless of order of play. Only the Mermaid capturing the Skull King bonus is earned. ')
    ],
    "mermaid" => [
        "name" => clienttranslate('Mermaid (x2)'),
        "desc_1" => clienttranslate('Mermaids beat all numbered units but lose to all of the Pirates, with the exception of the Skull King, who is lured by their treasure. If both Mermaids end up in the same trick, the first one played wins the trick.'),
        "desc_2" => clienttranslate('If a Pirate, the Skull King; and a Mermaid are all played in the same trick, the Mermaid always wins the trick, regardless of order of play. Only the Mermaid capturing the Skull King bonus is earned. ')
    ],
    "escape" => [
        "name" => clienttranslate('Escape (x5)'),
        "desc_1" => clienttranslate('The escape cards have value in being able to be played to \'not win \'. They lose to all other cards. They are very handy to help assure you get your bid by not winning more tricks than were bid.'),
        "desc_2" => clienttranslate('In the rare event that each player plays an escape card, tigress as an escape, or a loot card, in the same trick; the first card played wins the trick.')
    ],
    "pirate" => [
        "name" => clienttranslate('Pirate (x5)'),
        "desc_1" => clienttranslate('Pirate cards beat all numbered cards. They are of equal rank with each other, so if more than one Pirate card is played in a trick, the person who played the first pirate wins the trick.'),
        "desc_2" => clienttranslate('If a Pirate, the Skull King; and a Mermaid are all played in the same trick, the Mermaid always wins the trick, regardless of order of play. Only the Mermaid capturing the Skull King bonus is earned. ')
    ],
    "loot" => [
        "name" => clienttranslate('Loot( x2)'),
        "desc_1" => clienttranslate('When you play a loot card, you enter into an alliance with the player who captures it. If both of you bid correctly, you are each awarded 20 bonus points.'),
        "desc_2" => clienttranslate('If you lead a trick with a loot card and the cards that follow are all escpaes then you would win the trick. No alliance was formed, so no bonus is awarded.')
    ],
    "kraken" => [
        "name" => clienttranslate('Kraken (x1)'),
        "desc_1" => clienttranslate('When played, the trick is destroyed entirely as the Kraken consumes all. No one wins the trick and the cards are set aside. The next trick is led by the player who would have won the trick. The Kraken and the White Whale are ancient rivals. When played in the same trick the second one played wins the battle. Tat card then sets the action to be applied.'),
        "desc_2" => clienttranslate('When a Kraken leads a trick, there is no suit for others to follow.')
    ],
    "white_whale" => [
        "name" => clienttranslate('White Whale (x1)'),
        "desc_1" => clienttranslate('When played, special cards are destroyed and can’t win! highest numbered card wins the trick, regardless of the suit. If there is a tie, the first one played is the winner. If only special cards are played, the trick is descarded, and the whale leads the next trick. The Kraken and the White Whale are ancient rivals. When played in the same trick the second one played wins the battle. Tat card then sets the action to be applied.'),
        "desc_2" => clienttranslate('When a White Whale leads a trick, there is no suit for others to follow.')
    ]
];


$this->_PIRATE_CARDS = [
    1 => [
        "name" => clienttranslate('Rosie D\'Laney'),
        "ability" => clienttranslate('Choose any player, including yourself, to lead the next trick.')
    ],
    2 => [
        "name" => clienttranslate('Bendt the Bandit'),
        "ability" => clienttranslate('Add 2 cards to your hand from the deck and then discard 2 cards.')
    ],
    3 => [
        "name" => clienttranslate('Rascal of Roatan'),
        "ability" => clienttranslate('Bet 0, 10 or 20 points. Earn the points if you bid correct, lose them if you fail!')
    ],
    4 => [
        "name" => clienttranslate('Juanita Jade'),
        "ability" => clienttranslate('Privately look through any cards not dealt that round to see which are not in play.')
    ],
    5 => [
        "name" => clienttranslate('Harry the Giant'),
        "ability" => clienttranslate('You may choose to change your bid by plus or minus 1, or to leave it the same.')
    ]
];

$this->ROUNDS = [
    1 => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
    2 => [2, 2, 4, 4, 6, 6, 8, 8, 10, 10],
    3 => [6, 7, 8, 9, 10],
    4 => [5, 5, 5, 5, 5],
    5 => [10, 10, 10, 10, 10, 10, 10, 10, 10, 10],
    6 => [9, 9, 7, 7, 5, 5, 3, 3, 1, 1],
    7 => [1]
];
