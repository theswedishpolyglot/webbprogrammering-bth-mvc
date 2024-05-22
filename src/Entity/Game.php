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
        } elseif ($bankValue > 21 || $playerValue > $bankValue) {
            return 'Player wins!';
        } else {
            return 'Bank wins!';
        }
    }

    public function toArray(): array
    {
        return [
            'player' => array_map(fn ($card) => $card->toArray(), $this->player->getHand()->getCards()->toArray()),
            'bank' => array_map(fn ($card) => $card->toArray(), $this->bank->getHand()->getCards()->toArray()),
            'deck' => array_map(fn ($card) => $card->toArray(), $this->deck->getCards()->toArray()),
        ];
    }

    public static function fromArray(array $data, LoggerInterface $logger): self
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

        return new self($player, $bank, $deck, $logger);
    }
}
