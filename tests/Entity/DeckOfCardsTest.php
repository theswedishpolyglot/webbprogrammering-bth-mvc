<?php

namespace App\Tests\Entity;

use App\Entity\Card;
use App\Entity\DeckOfCards;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class DeckOfCardsTest extends TestCase
{
    public function testDeckInitialization(): void
    {
        $deck = new DeckOfCards();
        $cards = $deck->getCards();

        $this->assertCount(52, $cards);
    }

    public function testShuffleDeck(): void
    {
        $deck = new DeckOfCards();
        $originalOrder = $deck->getCards()->toArray();
        $deck->shuffle(new NullLogger());
        $shuffledOrder = $deck->getCards()->toArray();

        $this->assertNotEquals($originalOrder, $shuffledOrder);
    }

    public function testDrawCard(): void
    {
        $deck = new DeckOfCards();
        $card = $deck->drawCard(new NullLogger());

        $this->assertInstanceOf(Card::class, $card);
        $this->assertCount(51, $deck->getCards());
    }

    public function testResetDeck(): void
    {
        $deck = new DeckOfCards();
        $deck->drawCard(new NullLogger());
        $deck->resetDeck();

        $this->assertCount(52, $deck->getCards());
    }

    public function testGetCards(): void
    {
        $deck = new DeckOfCards();
        $cards = $deck->getCards();

        $this->assertInstanceOf(ArrayCollection::class, $cards);
        $this->assertCount(52, $cards);
    }

    public function testGetSortedCards(): void
    {
        $deck = new DeckOfCards();
        $sortedCards = $deck->getSortedCards();

        $this->assertEquals('2 ♣', (string)$sortedCards[0]);
        $this->assertEquals('Ace ♠', (string)$sortedCards[51]);
    }

    public function testSetCards(): void
    {
        $deck = new DeckOfCards();
        $newCards = new ArrayCollection([new Card('Hearts', '2'), new Card('Diamonds', '3')]);
        $deck->setCards($newCards);

        $this->assertCount(2, $deck->getCards());
        $this->assertEquals('2 ♥', (string)$deck->getCards()->first());
    }

    public function testToString(): void
    {
        $deck = new DeckOfCards();
        $this->assertEquals('Deck of Cards: 52 cards remaining', $deck->__toString());
    }

    public function testDetailedString(): void
    {
        $deck = new DeckOfCards();
        $detailString = $deck->detailedString();

        $this->assertIsString($detailString);
        $this->assertStringContainsString('2 ♥', $detailString);
        $this->assertStringContainsString('Ace ♠', $detailString);
    }
}
