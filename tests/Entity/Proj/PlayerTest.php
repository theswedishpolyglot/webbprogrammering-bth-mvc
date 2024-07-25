<?php

namespace App\Tests\Proj\Entity;

use App\Entity\Proj\Card;
use App\Entity\Proj\Player;
use PHPUnit\Framework\TestCase;

class PlayerTest extends TestCase
{
    public function testPlayerHand(): void
    {
        $player = new Player();
        $this->assertCount(0, $player->getHand()->getCards());

        $card = new Card('Hearts', '7');
        $player->getHand()->addCard($card);

        $this->assertCount(1, $player->getHand()->getCards());
        $this->assertEquals(7, $player->getHand()->getValue());
    }

    public function testPlayerIsBusted(): void
    {
        $player = new Player();
        $this->assertFalse($player->isBusted());
        $player->getHand()->addCard(new Card('Hearts', '10'));
        $player->getHand()->addCard(new Card('Spades', '9'));
        $player->getHand()->addCard(new Card('Clubs', '5'));
        $this->assertTrue($player->isBusted());
    }
}
