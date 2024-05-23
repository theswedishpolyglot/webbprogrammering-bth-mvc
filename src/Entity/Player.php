<?php

namespace App\Entity;

/**
 * Class Player
 *
 * Represents a player in the card game, holding a hand of cards.
 */
class Player
{
    private CardHand $hand;

    /**
     * Player constructor.
     * Initializes the player with an empty hand of cards.
     */
    public function __construct()
    {
        $this->hand = new CardHand();
    }

    /**
     * Get the player's hand of cards.
     *
     * @return CardHand The player's hand of cards.
     */
    public function getHand(): CardHand
    {
        return $this->hand;
    }
}
