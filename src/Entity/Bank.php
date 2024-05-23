<?php

namespace App\Entity;

use Psr\Log\LoggerInterface;

/**
 * Class Bank
 *
 * Represents the dealer (bank) in the card game. The bank is a player with specific rules for drawing cards.
 */
class Bank extends Player
{
    /**
     * The bank plays its turn by drawing cards from the deck until its hand value reaches at least 17.
     *
     * @param DeckOfCards $deck The deck of cards to draw from.
     * @param LoggerInterface $logger The logger to log actions such as drawing cards.
     *
     * @return void
     */
    public function play(DeckOfCards $deck, LoggerInterface $logger): void
    {
        while ($this->getHand()->getValue() < 17) {
            $card = $deck->drawCard($logger);
            if ($card) {
                $this->getHand()->addCard($card);
            }
        }
    }
}
