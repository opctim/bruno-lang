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

namespace Opctim\BrunoLang\V1\Utils;

readonly class ParsedBlockData
{
    public function __construct(
        private string $name,
        /** @var string[] */
        private array $entries
    )
    {}

    public function getName(): string
    {
        return $this->name;
    }

    public function getEntries(): array
    {
        return $this->entries;
    }
}