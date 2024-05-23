<?php

namespace App\Tests\Entity;

use App\Entity\Bank;
use App\Entity\Card;
use App\Entity\DeckOfCards;
use App\Entity\Game;
use App\Entity\Player;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class GameTest extends TestCase
{
    private Game $game;

    protected function setUp(): void
    {
        $player = new Player();
        $bank = new Bank();
        $deck = new DeckOfCards();
        $logger = new NullLogger();
        $this->game = new Game($player, $bank, $deck, $logger);
    }

    public function testGetPlayer(): void
    {
        $player = $this->game->getPlayer();
        $this->assertInstanceOf(Player::class, $player);
    }

    public function testGetBank(): void
    {
        $bank = $this->game->getBank();
        $this->assertInstanceOf(Bank::class, $bank);
    }

    public function testGetDeck(): void
    {
        $deck = $this->game->getDeck();
        $this->assertInstanceOf(DeckOfCards::class, $deck);
        $this->assertCount(52, $deck->getCards());
    }

    public function testPlayerDrawCard(): void
    {
        $this->game->playerDrawCard();
        $this->assertCount(1, $this->game->getPlayer()->getHand()->getCards());
    }

    public function testBankPlay(): void
    {
        $this->game->bankPlay();
        $this->assertGreaterThanOrEqual(17, $this->game->getBank()->getHand()->getValue());
    }

    public function testWinnerByHigherValue(): void
    {
        $player = $this->game->getPlayer();
        $bank = $this->game->getBank();

        // Test player wins with higher value
        $player->getHand()->addCard(new Card('Hearts', '10'));
        $player->getHand()->addCard(new Card('Spades', '10'));
        $bank->getHand()->addCard(new Card('Diamonds', '10'));
        $bank->getHand()->addCard(new Card('Clubs', '7'));

        $result = $this->game->getResult();
        $this->assertEquals('Player wins!', $result);

        // Reset
        $player->getHand()->getCards()->clear();
        $bank->getHand()->getCards()->clear();

        // Test bank wins with higher value
        $player->getHand()->addCard(new Card('Hearts', '10'));
        $player->getHand()->addCard(new Card('Spades', '7'));
        $bank->getHand()->addCard(new Card('Diamonds', '10'));
        $bank->getHand()->addCard(new Card('Clubs', '10'));

        $result = $this->game->getResult();
        $this->assertEquals('Bank wins!', $result);
    }

    public function testBankWinsPlayerBusts(): void
    {
        $player = $this->game->getPlayer();
        $bank = $this->game->getBank();

        $player->getHand()->addCard(new Card('Hearts', '10'));
        $player->getHand()->addCard(new Card('Spades', '9'));
        $player->getHand()->addCard(new Card('Clubs', '5'));
        $bank->getHand()->addCard(new Card('Diamonds', '10'));
        $bank->getHand()->addCard(new Card('Clubs', '7'));

        $result = $this->game->getResult();
        $this->assertEquals('Bank wins!', $result);
    }

    public function testPlayerWinsBankBusts(): void
    {
        $player = $this->game->getPlayer();
        $bank = $this->game->getBank();

        $player->getHand()->addCard(new Card('Hearts', '10'));
        $player->getHand()->addCard(new Card('Spades', '8'));
        $bank->getHand()->addCard(new Card('Diamonds', '10'));
        $bank->getHand()->addCard(new Card('Clubs', '8'));
        $bank->getHand()->addCard(new Card('Clubs', '6'));

        $result = $this->game->getResult();
        $this->assertEquals('Player wins!', $result);
    }

    public function testToArray(): void
    {
        $array = $this->game->toArray();
        $this->assertIsArray($array);
        $this->assertArrayHasKey('player', $array);
        $this->assertArrayHasKey('bank', $array);
        $this->assertArrayHasKey('deck', $array);
    }

    public function testFromArray(): void
    {
        $data = $this->game->toArray();
        $gameFromData = new Game(new Player(), new Bank(), new DeckOfCards(), new NullLogger());
        $gameFromData->fromArray($data, new NullLogger());

        $this->assertEquals($this->game->toArray(), $gameFromData->toArray());
    }
}
