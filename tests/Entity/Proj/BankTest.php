<?php

namespace App\Tests\Entity\Proj;

use App\Entity\Proj\Bank;
use App\Entity\Proj\DeckOfCards;
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
