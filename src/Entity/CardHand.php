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

    public function getValue(): int
    {
        $value = 0;
        $aceCount = 0;

        foreach ($this->cards as $card) {
            if (is_numeric($card->getValue())) {
                $value += $card->getValue();
            } elseif ($card->getValue() === 'Ace') {
                $aceCount++;
                $value += 1;
            } else {
                $value += 10;
            }
        }

        while ($aceCount > 0 && $value + 13 <= 21) {
            $value += 13;
            $aceCount--;
        }

        return $value;
    }

    public function __toString(): string
    {
        return implode(', ', $this->cards->toArray());
    }
}
