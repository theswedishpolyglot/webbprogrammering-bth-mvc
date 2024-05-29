<?php

namespace App\Entity;

use JsonSerializable;

/**
 * Class Card
 *
 * Represents a playing card with a suit and a value.
 */
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

    /**
     * Card constructor.
     *
     * @param string $suit The suit of the card.
     * @param string $value The value of the card.
     */
    public function __construct(string $suit, string $value)
    {
        $this->suit = $suit;
        $this->value = $value;
    }

    /**
     * Get the suit of the card.
     *
     * @return string The suit of the card.
     */
    public function getSuit(): string
    {
        return $this->suit;
    }

    /**
     * Get the value of the card.
     *
     * @return string The value of the card.
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Convert the card to a JSON-serializable format.
     *
     * @return array<string, mixed> The JSON-serializable representation of the card.
     */
    public function jsonSerialize(): array
    {
        return [
            'suit' => $this->getSuitSymbol(),
            'value' => $this->value
        ];
    }

    /**
     * Get the string representation of the card.
     *
     * @return string The string representation of the card.
     */
    public function __toString()
    {
        return $this->value . ' ' . self::SUIT_SYMBOLS[$this->suit];
    }

    /**
     * Get the symbol for the suit of the card.
     *
     * @return string The symbol for the suit.
     */
    private function getSuitSymbol(): string
    {
        return self::SUIT_SYMBOLS[$this->suit] ?? '?';
    }

    /**
     * Convert the card to an array format.
     *
     * @return array<string, string> The array representation of the card.
     */
    public function toArray(): array
    {
        return [
            'suit' => $this->suit,
            'value' => $this->value
        ];
    }

    /**
     * Create a Card instance from an array.
     *
     * @param array<string, string> $data An array with 'suit' and 'value' as keys with their corresponding string values.
     * @return self Returns an instance of Card.
     */
    public static function fromArray(array $data): self
    {
        return new self($data['suit'], $data['value']);
    }
}
