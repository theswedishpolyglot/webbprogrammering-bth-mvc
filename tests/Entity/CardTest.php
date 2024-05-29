<?php

namespace App\Tests\Entity;

use App\Entity\Card;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class CardTest extends TestCase
{
    public function testCardCreation(): void
    {
        $card = new Card('Hearts', 'Ace');

        $this->assertEquals('Hearts', $card->getSuit());
        $this->assertEquals('Ace', $card->getValue());
        $this->assertEquals('Ace ♥', $card->__toString());
    }

    public function testGetSuit(): void
    {
        $card = new Card('Hearts', 'Ace');
        $this->assertEquals('Hearts', $card->getSuit());
    }

    public function testGetValue(): void
    {
        $card = new Card('Diamonds', '10');
        $this->assertEquals('10', $card->getValue());
    }

    public function testJsonSerialization(): void
    {
        $card = new Card('Spades', 'King');
        $json = $card->jsonSerialize();

        $this->assertArrayHasKey('suit', $json);
        $this->assertArrayHasKey('value', $json);
        $this->assertEquals('♠', $json['suit']);
        $this->assertEquals('King', $json['value']);
    }

    public function testCardToString(): void
    {
        $card = new Card('Spades', 'King');
        $this->assertEquals('King ♠', $card->__toString());
    }

    public function testGetSuitSymbol(): void
    {
        $card = new Card('Clubs', 'Queen');
        $reflection = new ReflectionClass($card);
        $method = $reflection->getMethod('getSuitSymbol');
        $method->setAccessible(true);
        
        $result = $method->invoke($card);
        $this->assertEquals('♣', $result);
    }

    public function testCardToArray(): void
    {
        $card = new Card('Clubs', '10');
        $this->assertEquals(['suit' => 'Clubs', 'value' => '10'], $card->toArray());
    }

    /**
     * @SuppressWarnings("StaticAccess")
     */
    public function testFromArray(): void
    {
        $data = [
            'suit' => 'Hearts',
            'value' => 'Ace'
        ];

        $card = Card::fromArray($data);

        $this->assertInstanceOf(Card::class, $card);
        $this->assertEquals('Hearts', $card->getSuit());
        $this->assertEquals('Ace', $card->getValue());
    }
}
