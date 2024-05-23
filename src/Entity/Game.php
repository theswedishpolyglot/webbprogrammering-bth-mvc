<?php

namespace App\Entity;

use Psr\Log\LoggerInterface;
use Doctrine\Common\Collections\ArrayCollection;

class Game
{
    private Player $player;
    private Bank $bank;
    private DeckOfCards $deck;
    private LoggerInterface $logger;

    public function __construct(Player $player, Bank $bank, DeckOfCards $deck, LoggerInterface $logger)
    {
        $this->player = $player;
        $this->bank = $bank;
        $this->deck = $deck;
        $this->logger = $logger;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getBank(): Bank
    {
        return $this->bank;
    }

    public function getDeck(): DeckOfCards
    {
        return $this->deck;
    }

    public function playerDrawCard(): void
    {
        $card = $this->deck->drawCard($this->logger);
        if ($card) {
            $this->player->getHand()->addCard($card);
        }
    }

    public function bankPlay(): void
    {
        $this->bank->play($this->deck, $this->logger);
    }

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
     * @return array{
     *     player: array<array<string, mixed>>,
     *     bank: array<array<string, mixed>>,
     *     deck: array<array<string, mixed>>
     * }
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
     * } $data
     * @param LoggerInterface $logger
     * @return self
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

    // /**
    //  * @param array{
    //  *     player: array<array<string, mixed>>,
    //  *     bank: array<array<string, mixed>>,
    //  *     deck: array<array<string, mixed>>
    //  * } $data
    //  * @param LoggerInterface $logger
    //  * @return self
    //  */
    // public static function fromArray(array $data, LoggerInterface $logger): self
    // {
    //     $deck = new DeckOfCards();
    //     $cards = new ArrayCollection();
    //     foreach ($data['deck'] as $cardData) {
    //         $cards->add(new Card($cardData['suit'], $cardData['value']));
    //     }
    //     $deck->setCards($cards);

    //     $player = new Player();
    //     foreach ($data['player'] as $cardData) {
    //         $player->getHand()->addCard(new Card($cardData['suit'], $cardData['value']));
    //     }

    //     $bank = new Bank();
    //     foreach ($data['bank'] as $cardData) {
    //         $bank->getHand()->addCard(new Card($cardData['suit'], $cardData['value']));
    //     }

    //     return new self($player, $bank, $deck, $logger);
    // }
}