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

namespace Opctim\BrunoLang\V1\Block;

use Opctim\BrunoLang\V1\Utils\ParsedBlockData;
use Opctim\BrunoLang\V1\Utils\Utils;
use Opctim\BrunoLang\V1\Tag\TextBlockTag;

abstract class TextBlock extends Block
{
    /**
     * @param string $name
     * @param string|null $value
     */
    public function __construct(
        string $name,
        private ?string $value = null,
    )
    {
        parent::__construct($name);
    }

    public static function fromParsedBlockData(ParsedBlockData $parsedBlockData): static
    {
        $value = implode(PHP_EOL, $parsedBlockData->getEntries());

        if (in_array(TextBlockTag::class, class_parents(static::class), true)) {
            return new static($value);
        }

        return new static($parsedBlockData->getName(), $value);
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): static
    {
        $this->value = $value;

        return $this;
    }

    protected function buildEntries(): string
    {
        return Utils::indentString((string)$this->value);
    }

    protected function getStartBracket(): string
    {
        return '{';
    }

    protected function getEndBracket(): string
    {
        return '}';
    }
}
