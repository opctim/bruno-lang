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

namespace Opctim\BrunoLang\V1\Tag;

use Opctim\BrunoLang\V1\Interface\TagInterface;
use Opctim\BrunoLang\V1\Block\DictionaryBlock;
use Opctim\BrunoLang\V1\Block\Entry\DictionaryBlockEntry;
use Opctim\BrunoLang\V1\Block\TextBlock;

abstract class TextBlockTag extends TextBlock implements TagInterface
{
    public function __construct(string $value)
    {
        parent::__construct(static::getTagName(), $value);
    }
}