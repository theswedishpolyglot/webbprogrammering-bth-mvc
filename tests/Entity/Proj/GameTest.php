<?php

namespace App\Tests\Proj\Entity;

use App\Entity\Proj\Bank;
use App\Entity\Proj\Card;
use App\Entity\Proj\CardHand;
use App\Entity\Proj\DeckOfCards;
use App\Entity\Proj\Game;
use App\Entity\Proj\Player;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * @SuppressWarnings("TooManyPublicMethods")
 */
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
        $player = new Player();
        $bank = new Bank();
        $deck = new DeckOfCards();
        $logger = new NullLogger();
        
        $this->assertCount(52, $deck->getCards(), 'Deck should start with 52 cards.');
        $game = new Game($player, $bank, $deck, $logger, 2, 10);
        $playerHands = $game->getPlayerHands();
        $this->assertCount(2, $playerHands, 'There should be 2 player hands.');
        $game->playerDrawCard(0);
        $this->assertNotNull($playerHands[0], 'Player hand 0 should not be null.');
        $this->assertInstanceOf(CardHand::class, $playerHands[0], 'Player hand 0 should be an instance of CardHand.');
        $this->assertCount(1, $playerHands[0]->getCards(), 'Player hand 0 should have 1 card.');
        $this->assertCount(51, $deck->getCards(), 'Deck should have 51 cards after one draw.');
        $drawnCard = $playerHands[0]->getCards()->first();
        $this->assertNotNull($drawnCard, 'Drawn card should not be null.');
        $this->assertNotContains($drawnCard, $deck->getCards()->toArray(), 'Drawn card should not be in the deck.');
        $game->playerDrawCard(1);
        $this->assertNotNull($playerHands[1], 'Player hand 1 should not be null.');
        $this->assertInstanceOf(CardHand::class, $playerHands[1], 'Player hand 1 should be an instance of CardHand.');
        $this->assertCount(1, $playerHands[1]->getCards(), 'Player hand 1 should have 1 card.');
        $this->assertCount(50, $deck->getCards(), 'Deck should have 50 cards after two draws.');
    }

    public function testBankPlay(): void
    {
        $game = new Game(new Player(), new Bank(), new DeckOfCards(), new NullLogger(), 1, 10);
        $game->bankPlay();
        $this->assertGreaterThanOrEqual(17, $game->getBank()->getHand()->getValue());
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
    
        $results = $this->game->getResult();
        $this->assertContains('Lost', $results);
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
    
        $results = $this->game->getResult();
        $this->assertContains('Won', $results);
    }

    public function testToArray(): void
    {
        $array = $this->game->toArray();
        $this->assertIsArray($array);
        $this->assertArrayHasKey('playerHands', $array);
        $this->assertArrayHasKey('bank', $array);
        $this->assertArrayHasKey('deck', $array);
    }

    public function testFromArray(): void
    {
        $expectedData = [
            'playerHands' => [
                [
                    'cards' => array_map(fn($card) => $card->toArray(), $this->game->getPlayer()->getHand()->getCards()->toArray()),
                    'stayed' => false,
                    'busted' => false,
                ],
            ],
            'bank' => array_map(fn($card) => $card->toArray(), $this->game->getBank()->getHand()->getCards()->toArray()),
            'deck' => array_map(fn($card) => $card->toArray(), $this->game->getDeck()->getCards()->toArray()),
            'betAmount' => 10
        ];
    
        $gameFromData = new Game(new Player(), new Bank(), new DeckOfCards(), new NullLogger());
        $gameFromData->fromArray($expectedData, new NullLogger());
    
        $this->assertEquals($this->game->toArray(), $gameFromData->toArray());
    }

    public function testFromArrayAddCardLine(): void
    {
        $data = [
            'playerHands' => [
                [
                    'cards' => [['suit' => 'Hearts', 'value' => '2']],
                    'stayed' => false,
                    'busted' => false,
                ],
            ],
            'bank' => [
                ['suit' => 'Spades', 'value' => 'Ace'],
                ['suit' => 'Diamonds', 'value' => 'King'],
            ],
            'deck' => [],
            'betAmount' => 10
        ];
    
        $logger = new NullLogger();
        $game = new Game(new Player(), new Bank(), new DeckOfCards(), $logger, 1, 10);
        $game->fromArray($data, $logger);
    
        $bankHand = $game->getBank()->getHand();
        $this->assertCount(2, $bankHand->getCards());
        $this->assertEquals('Ace', $bankHand->getCards()[0]->getValue());
        $this->assertEquals('Spades', $bankHand->getCards()[0]->getSuit());
        $this->assertEquals('King', $bankHand->getCards()[1]->getValue());
        $this->assertEquals('Diamonds', $bankHand->getCards()[1]->getSuit());
    }

    public function testAllPlayersDone(): void
    {
        $player = new Player();
        $bank = new Bank();
        $deck = new DeckOfCards();
        $logger = new NullLogger();
        $game = new Game($player, $bank, $deck, $logger, 2, 10);

        // All hands stayed
        foreach ($game->getPlayerHands() as $hand) {
            $hand->setStayed(true);
        }
        $this->assertTrue($game->allPlayersDone());

        // One hand busted, one stayed
        $playerHands = $game->getPlayerHands();
        $playerHands[0]?->setBusted(true);
        $this->assertTrue($game->allPlayersDone());

        // One hand active, one stayed
        $playerHands[0]?->setBusted(false);
        $playerHands[0]?->setStayed(false);
        $this->assertFalse($game->allPlayersDone());

        // No hands busted or stayed
        $playerHands[1]?->setStayed(false);
        $this->assertFalse($game->allPlayersDone());
    }

    public function testGetBetAmount(): void
    {
        $player = new Player();
        $bank = new Bank();
        $deck = new DeckOfCards();
        $logger = new NullLogger();
        $betAmount = 50;
        $game = new Game($player, $bank, $deck, $logger, 2, $betAmount);

        $this->assertEquals($betAmount, $game->getBetAmount(), "Expected bet amount to be $betAmount.");
    }

    public function testPlayerHandBusted(): void
    {
        $playerHands = $this->game->getPlayerHands();
        $playerHand = $playerHands[0];
        $playerHand?->addCard(new Card('Hearts', '10'));
        $playerHand?->addCard(new Card('Spades', '9'));
        $playerHand?->addCard(new Card('Clubs', '5'));
        $this->assertGreaterThan(21, $playerHand?->getValue());
        $bankHand = $this->game->getBank()->getHand();
        $bankHand->addCard(new Card('Diamonds', '10'));
        $bankHand->addCard(new Card('Clubs', '7'));
        $results = $this->game->getResult();
        $this->assertContains('Busted', $results);
    }
}