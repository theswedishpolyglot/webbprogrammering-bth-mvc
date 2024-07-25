<?php

namespace App\Entity\Proj;

use Doctrine\Common\Collections\ArrayCollection;
use Psr\Log\LoggerInterface;

/**
 * Class DeckOfCards
 *
 * Represents a deck of playing cards.
 */
class DeckOfCards
{
    /**
     * @var ArrayCollection<int, Card> The collection of cards in the deck.
     */
    private ArrayCollection $cards;

    /**
     * DeckOfCards constructor.
     * Initializes a new deck of cards.
     */
    public function __construct()
    {
        $this->initializeDeck();
    }

    /**
     * Initializes the deck with 52 playing cards.
     *
     * @return void
     */
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

    /**
     * Shuffles the deck of cards.
     *
     * @param LoggerInterface $logger The logger to log the shuffling action.
     * @return void
     */
    public function shuffle(LoggerInterface $logger): void
    {
        $cards = $this->cards->toArray();
        shuffle($cards);
        $this->cards = new ArrayCollection($cards);
        $logger->info('Deck shuffled.');
    }

    /**
     * Draws a card from the deck.
     *
     * @param LoggerInterface $logger The logger to log the draw action.
     * @return Card|null The card drawn, or null if the deck is empty.
     */
    public function drawCard(LoggerInterface $logger): ?Card
    {
        if (!$this->cards->isEmpty()) {
            $card = $this->cards->first();
            if ($card !== false) {
                $this->cards->removeElement($card);
                $logger->info('Card drawn:', ['card' => $card->__toString()]);
                return $card;
            }
        }
        $logger->info('Deck is empty, no card drawn.');
        return null;
    }

    /**
     * Resets the deck to its initial state.
     *
     * @return void
     */
    public function resetDeck(): void
    {
        $this->initializeDeck();
    }

    /**
     * Gets the cards in the deck.
     *
     * @return ArrayCollection<int, Card> The collection of cards in the deck.
     */
    public function getCards(): ArrayCollection
    {
        return $this->cards;
    }

    /**
     * Returns a sorted array of cards from the deck.
     *
     * @return array<int, Card> The sorted array of cards.
     */
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

    /**
     * Sets the cards in the deck.
     *
     * @param ArrayCollection<int, Card> $cards The collection of cards to set in the deck.
     * @return void
     */
    public function setCards(ArrayCollection $cards): void
    {
        $this->cards = $cards;
    }

    /**
     * Gets the string representation of the deck.
     *
     * @return string The string representation of the deck.
     */
    public function __toString()
    {
        return 'Deck of Cards: ' . $this->cards->count() . ' cards remaining';
    }

    /**
     * Returns a detailed string representation of the deck.
     *
     * @return string The detailed string representation of the deck.
     */
    public function detailedString()
    {
        $cardDetails = [];
        foreach ($this->cards as $card) {
            $cardDetails[] = $card->__toString();
        }
        return implode(', ', $cardDetails);
    }
}
