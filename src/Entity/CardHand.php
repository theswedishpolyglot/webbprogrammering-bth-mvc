<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;

class CardHand
{
    /**
     * @var ArrayCollection<int, Card>
     */
    private ArrayCollection $cards;

    public function __construct()
    {
        $this->cards = new ArrayCollection();
    }

    /**
     * Adds a card to the hand.
     * @param Card $card The card to add.
     * @return void
     */
    public function addCard(Card $card)
    {
        $this->cards->add($card);
    }

    /**
     * Gets the collection of cards.
     * @return ArrayCollection<int, Card> The collection of cards.
     */
    public function getCards(): ArrayCollection
    {
        return $this->cards;
    }

    /**
     * Calculates the total value of the hand, treating Aces as either 1 or 14 to stay under 21.
     * @return int The total value of the hand.
     */
    public function getValue(): int
    {
        $value = 0;
        $aceCount = 0;

        foreach ($this->cards as $card) {
            if (is_numeric($card->getValue())) {
                $value += (int) $card->getValue();
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
