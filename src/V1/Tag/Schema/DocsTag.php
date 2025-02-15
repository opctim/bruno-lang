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

namespace Opctim\BrunoLang\V1\Tag\Schema;

use Opctim\BrunoLang\V1\Interface\TagInterface;
use Opctim\BrunoLang\V1\Tag\TextBlockTag;

class DocsTag extends TextBlockTag implements TagInterface
{
    public static function getTagName(): string
    {
        return 'docs';
    }
}