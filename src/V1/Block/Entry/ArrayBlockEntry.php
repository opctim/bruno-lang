<?php
declare(strict_types=1);

/*
 * This file is part of opctim/bruno-lang
 *
 * (c) Tim Nelles (opctim) <kontakt@timnelles.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * information in the project composer.json or on GitHub.
 */

namespace Opctim\BrunoLang\V1\Block\Entry;

use Opctim\BrunoLang\V1\Interface\BlockEntryInterface;
use Opctim\BrunoLang\V1\Utils\Utils;

class ArrayBlockEntry implements BlockEntryInterface
{
    public function __construct(
        private string $value,
        private bool $enabled = true,
        private bool $local = false
    )
    {}

    public static function fromParsedLine(string $line): static
    {
        preg_match('/^(?<MODIFIERS>[@~]*)(?<VALUE>[^~@,]+)/', $line, $matches);

        $value = trim($matches['VALUE']);
        $modifiers = trim($matches['MODIFIERS']);

        return new static($value, Utils::isEnabled($modifiers), Utils::isLocal($modifiers));
    }

    public function build(): string
    {
        return trim(
            Utils::buildEntryName($this->isEnabled(), $this->isLocal(), $this->getValue())
        );
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->getValue();
    }

    public function setValue(string $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): static
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function isLocal(): bool
    {
        return $this->local;
    }

    public function setLocal(bool $local): static
    {
        $this->local = $local;

        return $this;
    }
}