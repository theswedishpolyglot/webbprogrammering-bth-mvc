<?php

namespace App\Tests\Entity;

use App\Entity\Card;
use App\Entity\CardHand;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class CardHandTest extends TestCase
{
    public function testAddCard(): void
    {
        $hand = new CardHand();
        $card = new Card('Hearts', 'Ace');
        $hand->addCard($card);
        $this->assertCount(1, $hand->getCards());
    }

    public function testGetCards(): void
    {
        $hand = new CardHand();
        $card1 = new Card('Hearts', '2');
        $card2 = new Card('Spades', 'Ace');
        $hand->addCard($card1);
        $hand->addCard($card2);

        $cards = $hand->getCards();

        $this->assertInstanceOf(ArrayCollection::class, $cards);
        $this->assertCount(2, $cards);
        $this->assertSame($card1, $cards->get(0));
        $this->assertSame($card2, $cards->get(1));
    }

    public function testGetValue(): void
    {
        $hand = new CardHand();
        $hand->addCard(new Card('Hearts', '2'));
        $hand->addCard(new Card('Spades', 'Ace'));
        $this->assertEquals(16, $hand->getValue(), 'Ace should be counted as 14 plus the other card value.');

        $hand = new CardHand();
        $hand->addCard(new Card('Hearts', '2'));
        $hand->addCard(new Card('Spades', 'Ace'));
        $hand->addCard(new Card('Clubs', '10'));
        $this->assertEquals(13, $hand->getValue(), 'Ace should be counted as 1 when adding 14 would exceed 21.');
    }

    public function testToString(): void
    {
        $hand = new CardHand();
        $hand->addCard(new Card('Hearts', '2'));
        $hand->addCard(new Card('Spades', 'Ace'));

        $this->assertEquals('2 ♥, Ace ♠', $hand->__toString());
    }
}
