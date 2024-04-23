<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;

class CardHand
{
    private $cards;

    public function __construct()
    {
        $this->cards = new ArrayCollection();
    }

    public function addCard(Card $card)
    {
        $this->cards->add($card);
    }

    public function getCards(): ArrayCollection
    {
        return $this->cards;
    }
}
