<?php

namespace App\Entity;

class Player
{
    private CardHand $hand;

    public function __construct()
    {
        $this->hand = new CardHand();
    }

    public function getHand(): CardHand
    {
        return $this->hand;
    }
}
