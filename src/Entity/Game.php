<?php

namespace App\Entity;

use Psr\Log\LoggerInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class Game
 *
 * Manages the overall game flow, including player and bank turns, and determines the winner.
 */
class Game
{
    private Player $player;
    private Bank $bank;
    private DeckOfCards $deck;
    private LoggerInterface $logger;

    /**
     * Game constructor.
     *
     * @param Player $player The player instance.
     * @param Bank $bank The bank instance.
     * @param DeckOfCards $deck The deck of cards instance.
     * @param LoggerInterface $logger The logger instance for logging actions.
     */
    public function __construct(Player $player, Bank $bank, DeckOfCards $deck, LoggerInterface $logger)
    {
        $this->player = $player;
        $this->bank = $bank;
        $this->deck = $deck;
        $this->logger = $logger;
    }

    /**
     * Get the player instance.
     *
     * @return Player The player instance.
     */
    public function getPlayer(): Player
    {
        return $this->player;
    }

    /**
     * Get the bank instance.
     *
     * @return Bank The bank instance.
     */
    public function getBank(): Bank
    {
        return $this->bank;
    }

    /**
     * Get the deck of cards instance.
     *
     * @return DeckOfCards The deck of cards instance.
     */
    public function getDeck(): DeckOfCards
    {
        return $this->deck;
    }

    /**
     * The player draws a card from the deck.
     *
     * @return void
     */
    public function playerDrawCard(): void
    {
        $card = $this->deck->drawCard($this->logger);
        if ($card) {
            $this->player->getHand()->addCard($card);
        }
    }

    /**
     * The bank plays its turn according to the rules.
     *
     * @return void
     */
    public function bankPlay(): void
    {
        $this->bank->play($this->deck, $this->logger);
    }

    /**
     * Determines the result of the game.
     *
     * @return string The result of the game ('Player wins!' or 'Bank wins!').
     */
    public function getResult(): string
    {
        $playerValue = $this->player->getHand()->getValue();
        $bankValue = $this->bank->getHand()->getValue();

        if ($playerValue > 21) {
            return 'Bank wins!';
        }
        if ($bankValue > 21 || $playerValue > $bankValue) {
            return 'Player wins!';
        }
        return 'Bank wins!';
    }

    /**
     * Converts the game state to an array.
     *
     * @return array{
     *     player: array<array<string, mixed>>,
     *     bank: array<array<string, mixed>>,
     *     deck: array<array<string, mixed>>
     * } The game state as an array.
     */
    public function toArray(): array
    {
        return [
            'player' => array_map(fn ($card) => $card->toArray(), $this->player->getHand()->getCards()->toArray()),
            'bank' => array_map(fn ($card) => $card->toArray(), $this->bank->getHand()->getCards()->toArray()),
            'deck' => array_map(fn ($card) => $card->toArray(), $this->deck->getCards()->toArray()),
        ];
    }

    /**
     * Initializes the Game instance from an array of data.
     *
     * @param array{
     *     player: array<array<string, mixed>>,
     *     bank: array<array<string, mixed>>,
     *     deck: array<array<string, mixed>>
     * } $data The game state as an array.
     * @param LoggerInterface $logger The logger instance for logging actions.
     * @return self Returns the initialized Game instance.
     */
    public function fromArray(array $data, LoggerInterface $logger): self
    {
        $deck = new DeckOfCards();
        $cards = new ArrayCollection();
        foreach ($data['deck'] as $cardData) {
            $cards->add(new Card($cardData['suit'], $cardData['value']));
        }
        $deck->setCards($cards);

        $player = new Player();
        foreach ($data['player'] as $cardData) {
            $player->getHand()->addCard(new Card($cardData['suit'], $cardData['value']));
        }

        $bank = new Bank();
        foreach ($data['bank'] as $cardData) {
            $bank->getHand()->addCard(new Card($cardData['suit'], $cardData['value']));
        }

        $this->player = $player;
        $this->bank = $bank;
        $this->deck = $deck;
        $this->logger = $logger;

        return $this;
    }
}
