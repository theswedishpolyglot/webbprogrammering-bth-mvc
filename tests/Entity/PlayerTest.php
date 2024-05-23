<?php

namespace App\Tests\Entity;

use App\Entity\Card;
use App\Entity\Player;
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
}
