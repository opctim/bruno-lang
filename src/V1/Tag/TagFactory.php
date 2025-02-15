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

use Composer\Autoload\ClassLoader;
use Opctim\BrunoLang\V1\Interface\TagInterface;
use Opctim\BrunoLang\V1\Utils\ParsedBlockData;
use Opctim\BrunoLang\V1\Block\Block;
use Opctim\BrunoLang\V1\Tag\Schema\AuthAwsV4Tag;
use Opctim\BrunoLang\V1\Tag\Schema\AuthBasicTag;
use Opctim\BrunoLang\V1\Tag\Schema\AuthBearerTag;
use Opctim\BrunoLang\V1\Tag\Schema\AuthDigestTag;
use Opctim\BrunoLang\V1\Tag\Schema\AuthOauth2Tag;
use Opctim\BrunoLang\V1\Tag\Schema\AuthTag;
use Opctim\BrunoLang\V1\Tag\Schema\AuthWsseTag;
use Opctim\BrunoLang\V1\Tag\Schema\BodyFormUrlEncodedTag;
use Opctim\BrunoLang\V1\Tag\Schema\BodyGraphQlTag;
use Opctim\BrunoLang\V1\Tag\Schema\BodyGraphQlVarsTag;
use Opctim\BrunoLang\V1\Tag\Schema\BodyMultiPartFormTag;
use Opctim\BrunoLang\V1\Tag\Schema\BodyTag;
use Opctim\BrunoLang\V1\Tag\Schema\BodyTextTag;
use Opctim\BrunoLang\V1\Tag\Schema\BodyXmlTag;
use Opctim\BrunoLang\V1\Tag\Schema\ConnectTag;
use Opctim\BrunoLang\V1\Tag\Schema\DeleteTag;
use Opctim\BrunoLang\V1\Tag\Schema\GetTag;
use Opctim\BrunoLang\V1\Tag\Schema\HeadersTag;
use Opctim\BrunoLang\V1\Tag\Schema\HeadTag;
use Opctim\BrunoLang\V1\Tag\Schema\MetaTag;
use Opctim\BrunoLang\V1\Tag\Schema\OptionsTag;
use Opctim\BrunoLang\V1\Tag\Schema\ParamsPathTag;
use Opctim\BrunoLang\V1\Tag\Schema\ParamsQueryTag;
use Opctim\BrunoLang\V1\Tag\Schema\PostTag;
use Opctim\BrunoLang\V1\Tag\Schema\PutTag;
use Opctim\BrunoLang\V1\Tag\Schema\ScriptPostResponseTag;
use Opctim\BrunoLang\V1\Tag\Schema\ScriptPreRequestTag;
use Opctim\BrunoLang\V1\Tag\Schema\TestTag;
use Opctim\BrunoLang\V1\Tag\Schema\TraceTag;
use Opctim\BrunoLang\V1\Tag\Schema\VarsPostResponseTag;
use Opctim\BrunoLang\V1\Tag\Schema\VarsPreRequestTag;

class TagFactory
{
    private static array $availableTags = [];

    public static function getAvailableTags(): array
    {
        if (empty(self::$availableTags)) {
            foreach (ClassLoader::getRegisteredLoaders() as $loader) {
                foreach ($loader->getClassMap() as $class => $directory) {
                    if (str_starts_with($class, 'Opctim\\BrunoLang\\V1\\Tag\\Schema\\')) {
                        self::$availableTags[] = $class;

                        require_once $directory;
                    }
                }
            }
        }

        return self::$availableTags;
    }

    public static function findByTagName(string $tagName): ?string
    {
        foreach (self::getAvailableTags() as $tagClass) {
            if ($tagName === call_user_func($tagClass . '::getTagName')) {
                return $tagClass;
            }
        }

        return null;
    }

    public static function createFromBlockData(ParsedBlockData $parsedBlockData): (Block & TagInterface)|null
    {
        $tagClass = self::findByTagName($parsedBlockData->getName());

        if (!$tagClass) {
            return null;
        }

        return call_user_func($tagClass . '::fromParsedBlockData', $parsedBlockData);
    }
}