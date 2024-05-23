<?php

namespace App\Tests\Entity;

use App\Entity\Bank;
use App\Entity\DeckOfCards;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class BankTest extends TestCase
{
    public function testBankPlay(): void
    {
        $bank = new Bank();
        $deck = new DeckOfCards();
        $logger = new NullLogger();
        $bank->play($deck, $logger);
        $this->assertGreaterThanOrEqual(17, $bank->getHand()->getValue());
    }
}
