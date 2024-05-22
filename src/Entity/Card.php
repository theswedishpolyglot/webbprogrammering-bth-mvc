<?php

namespace App\Entity;

use JsonSerializable;

class Card implements JsonSerializable
{
    private string $suit;
    private string $value;

    private const SUIT_SYMBOLS = [
        'Hearts'   => '♥',
        'Diamonds' => '♦',
        'Clubs'    => '♣',
        'Spades'   => '♠'
    ];

    public function __construct(string $suit, string $value)
    {
        $this->suit = $suit;
        $this->value = $value;
    }

    public function getSuit(): string
    {
        return $this->suit;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'suit' => $this->getSuitSymbol(),
            'value' => $this->value
        ];
    }

    public function __toString()
    {
        return $this->value . ' ' . self::SUIT_SYMBOLS[$this->suit];
    }

    private function getSuitSymbol(): string
    {
        return self::SUIT_SYMBOLS[$this->suit] ?? '?';
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'suit' => $this->suit,
            'value' => $this->value
        ];
    }

    /**
     * @param array<string, string> $data An array with 'suit' and 'value' as keys with their corresponding string values.
     * @return self Returns an instance of Card.
     */
    public static function fromArray(array $data): self
    {
        return new self($data['suit'], $data['value']);
    }

}
