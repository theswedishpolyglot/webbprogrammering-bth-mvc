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

    public function jsonSerialize()
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
}
