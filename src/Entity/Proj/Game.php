<?php

namespace App\Entity\Proj;

use Psr\Log\LoggerInterface;
use Doctrine\Common\Collections\ArrayCollection;

class Game
{
    private Player $player;
    private Bank $bank;
    private DeckOfCards $deck;
    private LoggerInterface $logger;
    private int $betAmount;
    /** @var ArrayCollection<int, CardHand> */
    private ArrayCollection $playerHands;

    public function __construct(Player $player, Bank $bank, DeckOfCards $deck, LoggerInterface $logger, int $numberOfHands = 1, int $betAmount = 10)
    {
        $this->player = $player;
        $this->bank = $bank;
        $this->deck = $deck;
        $this->logger = $logger;
        $this->betAmount = $betAmount;
        $this->playerHands = new ArrayCollection();

        for ($i = 0; $i < $numberOfHands; $i++) {
            $this->playerHands->add(new CardHand());
        }
    }

    /**
     * @return ArrayCollection<int, CardHand>
     */
    public function getPlayerHands(): ArrayCollection
    {
        return $this->playerHands;
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
    public function playerDrawCard(int $handIndex): void
    {
        $card = $this->deck->drawCard($this->logger);
        if ($card && isset($this->playerHands[$handIndex])) {
            $this->playerHands[$handIndex]->addCard($card);
        }
    }

    public function bankPlay(): void
    {
        $this->bank->play($this->deck, $this->logger);
    }

    public function allPlayersDone(): bool
    {
        foreach ($this->playerHands as $hand) {
            if (!$hand->isBusted() && !$hand->isStayed()) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return array<int, string>
     */
    public function getResult(): array
    {
        $results = [];
        foreach ($this->playerHands as $hand) {
            $playerValue = $hand->getValue();
            $bankValue = $this->bank->getHand()->getValue();

            if ($playerValue > 21) {
                $results[] = 'Busted';
                continue;
            }

            if ($bankValue > 21 || $playerValue > $bankValue) {
                $results[] = 'Won';
                continue;
            }

            $results[] = 'Lost';
        }
        return $results;
    }

    public function getBetAmount(): int
    {
        return $this->betAmount;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $playerHandsArray = [];
        foreach ($this->playerHands as $hand) {
            $handArray = [
                'cards' => array_map(fn ($card) => $card->toArray(), $hand->getCards()->toArray()),
                'stayed' => $hand->isStayed(),
                'busted' => $hand->isBusted()
            ];
            $playerHandsArray[] = $handArray;
        }

        return [
            'playerHands' => $playerHandsArray,
            'bank' => array_map(fn ($card) => $card->toArray(), $this->bank->getHand()->getCards()->toArray()),
            'deck' => array_map(fn ($card) => $card->toArray(), $this->deck->getCards()->toArray()),
            'betAmount' => $this->betAmount
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function fromArray(array $data, LoggerInterface $logger): self
    {
        $deck = new DeckOfCards();
        $cards = new ArrayCollection();
        foreach ($data['deck'] as $cardData) {
            $cards->add(new Card($cardData['suit'], $cardData['value']));
        }
        $deck->setCards($cards);

        $playerHands = new ArrayCollection();
        foreach ($data['playerHands'] as $handData) {
            $hand = new CardHand();
            foreach ($handData['cards'] as $cardData) {
                $hand->addCard(new Card($cardData['suit'], $cardData['value']));
            }
            $hand->setStayed($handData['stayed']);
            $hand->setBusted($handData['busted']);
            $playerHands->add($hand);
        }

        $bank = new Bank();
        foreach ($data['bank'] as $cardData) {
            $bank->getHand()->addCard(new Card($cardData['suit'], $cardData['value']));
        }

        $this->playerHands = $playerHands;
        $this->bank = $bank;
        $this->deck = $deck;
        $this->logger = $logger;
        $this->betAmount = $data['betAmount'];

        return $this;
    }
}
