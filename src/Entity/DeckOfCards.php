<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Psr\Log\LoggerInterface;

class DeckOfCards
{
    private ArrayCollection $cards;

    public function __construct()
    {
        $this->initializeDeck();
    }

    private function initializeDeck(): void
    {
        $this->cards = new ArrayCollection();
        $suits = ['Hearts', 'Diamonds', 'Clubs', 'Spades'];
        $values = ['2', '3', '4', '5', '6', '7', '8', '9', '10', 'Jack', 'Queen', 'King', 'Ace'];

        foreach ($suits as $suit) {
            foreach ($values as $value) {
                $this->cards->add(new Card($suit, $value));
            }
        }
    }

    public function shuffle(LoggerInterface $logger): void
    {
        $cards = $this->cards->toArray();
        shuffle($cards);
        $this->cards = new ArrayCollection($cards);
        $logger->info('Deck shuffled.');
    }

    public function drawCard(LoggerInterface $logger)
    {
        if (!$this->cards->isEmpty()) {
            $card = $this->cards->first();
            $this->cards->removeElement($card);
            $logger->info('Card drawn:', ['card' => $card->__toString()]);
            return $card;
        } else {
            $logger->info('Deck is empty, no card drawn.');
            return null;
        }
    }

    public function resetDeck(): void
    {
        $this->initializeDeck();
    }

    public function getCards(): ArrayCollection
    {
        return $this->cards;
    }

    public function getSortedCards(): array
    {
        $cardsArray = $this->cards->toArray();
        usort($cardsArray, function ($cardA, $cardB) {
            $suitsOrder = ['Clubs' => 1, 'Diamonds' => 2, 'Hearts' => 3, 'Spades' => 4];
            $valuesOrder = array_flip(['2', '3', '4', '5', '6', '7', '8', '9', '10', 'Jack', 'Queen', 'King', 'Ace']);

            if ($suitsOrder[$cardA->getSuit()] === $suitsOrder[$cardB->getSuit()]) {
                return $valuesOrder[$cardA->getValue()] <=> $valuesOrder[$cardB->getValue()];
            }
            return $suitsOrder[$cardA->getSuit()] <=> $suitsOrder[$cardB->getSuit()];
        });

        return $cardsArray;
    }

    public function __toString()
    {
        return 'Deck of Cards: ' . $this->cards->count() . ' cards remaining';
    }

    public function detailedString()
    {
        $cardDetails = [];
        foreach ($this->cards as $card) {
            $cardDetails[] = $card->__toString();
        }
        return implode(', ', $cardDetails);
    }
}
