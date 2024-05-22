<?php

namespace App\Entity;

use Psr\Log\LoggerInterface;

class Bank extends Player
{
    public function play(DeckOfCards $deck, LoggerInterface $logger): void
    {
        while ($this->getHand()->getValue() < 17) {
            $card = $deck->drawCard($logger);
            if ($card) {
                $this->getHand()->addCard($card);
            }
        }
    }
}
