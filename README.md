# opctim/bruno-lang
[![Latest Stable Version](https://poser.pugx.org/opctim/bruno-lang/v)](https://packagist.org/packages/opctim/bruno-lang) [![Total Downloads](https://poser.pugx.org/opctim/bruno-lang/downloads)](https://packagist.org/packages/opctim/bruno-lang) [![Latest Unstable Version](https://poser.pugx.org/opctim/bruno-lang/v/unstable)](https://packagist.org/packages/opctim/bruno-lang) [![License](https://poser.pugx.org/opctim/bruno-lang/license)](https://packagist.org/packages/opctim/bruno-lang) [![PHP Version Require](https://poser.pugx.org/opctim/bruno-lang/require/php)](https://packagist.org/packages/opctim/bruno-lang)

This package is a framework-agnostic, bidirectional implementation of the Bruno (.bru) file language from [usebruno.com](https://usebruno.com). 
It allows you to generate, parse, modify, and rebuild Bruno request collections programmatically or from existing .bru files. 
The primary use case is to create a collection in PHP and write it to disk, but it also supports reading and modifying 
existing collections seamlessly.

## Features

- Parsing of bruno collections (just give it the path to the collection)
- Interface to programmatically create bruno collections
- Functionality to generate .bru files with the required metadata and write collections to disk

## What do I need this for?

The main purpose of this library is to enable developers to create dev-tools for their frameworks which are capable of
generating Bruno collections from their app routes :)

So feel free to help the community by creating a bundle for your favorite framework!

## Platform requirements

- PHP >= 8.1

## Installation

Install via Composer:
    
    composer require opctim/bruno-lang


## Usage

### Programmatically creating a collection

```php
<?php declare(strict_types=1);

use Opctim\BrunoLang\V1\Block\Entry\DictionaryBlockEntry;
use Opctim\BrunoLang\V1\BruFile;
use Opctim\BrunoLang\V1\Collection;
use Opctim\BrunoLang\V1\Tag\Schema\BodyJsonTag;
use Opctim\BrunoLang\V1\Tag\Schema\HeadersTag;
use Opctim\BrunoLang\V1\Tag\Schema\MetaTag;
use Opctim\BrunoLang\V1\Tag\Schema\PostTag;
use Opctim\BrunoLang\V1\Tag\Schema\ScriptPostResponseTag;
use Opctim\BrunoLang\V1\Tag\Schema\VarsTag;

$collection = new Collection(
    // Metadata, will be written to bruno.json
    [
        'version' => '1',
        'name' => 'my_awesome_collection',
        'type' => 'collection',
        'ignore' => [
            'node_modules',
            '.git'
        ]
    ],
    // Request definitions
    [
        new BruFile('Login request', [
            new MetaTag([
                new DictionaryBlockEntry('name', 'Login request'), // should be the same as the .bru file name
                new DictionaryBlockEntry('type', 'http'),
                new DictionaryBlockEntry('seq', '1') // Used to order the requests in Bruno
            ]),
            new PostTag([
                new DictionaryBlockEntry('url', '{{baseUrl}}/api/login'),
                new DictionaryBlockEntry('body', 'json'),
                new DictionaryBlockEntry('auth', 'none'),
            ]),
            new HeadersTag([
                new DictionaryBlockEntry('Content-Type', 'application/json'),
                new DictionaryBlockEntry('X-Custom', '1234', false),
            ]),
            new BodyJsonTag(
'{
  "username": "john@example.com",
  "password": "1234"
}'
            ),
            new ScriptPostResponseTag("bru.setVar('auth_token', res.body.token)")
        ])
    ],
    // Environments, as bruno files. The BruFile::name is the environment name.
    [
        new BruFile('local', [
            new VarsTag([
                new DictionaryBlockEntry('baseUrl', 'https://localhost')
            ])
        ])
    ]
);

// Write the changes to disk
$collection->write('/path/to/my/collection');
```

### Parsing and rebuilding existing collections

```php
<?php declare(strict_types=1);

use Opctim\BrunoLang\V1\Block\Entry\DictionaryBlockEntry;
use Opctim\BrunoLang\V1\Collection;
use Opctim\BrunoLang\V1\Tag\Schema\MetaTag;

$path = '/path/to/my/collection';

$collection = Collection::parse($path);

// Change all request types to 'http'
foreach ($collection->getRequests() as $request) {
    $meta = $request->findOneBlockByName('meta');
    
    if ($meta instanceof MetaTag) {
        $entry = $meta->findOneBlockEntryByName('type');
        
        if ($entry instanceof DictionaryBlockEntry) {
            $entry->setValue('http');
        }
    }
}

// Write the changes to disk
$collection->write($path);
```

## Tests

    composer install
    vendor/bin/phpunit

## Contributing

In case Bruno adds new Tags to their language, feel free to open a PR and add them under `src/V1/Tag/Schema`. 
You basically only have to create the class following the naming schema. 

It needs to extend one of the following base types, depending on what is needed:

- [Opctim\BrunoLang\V1\Tag\DictionaryBlockTag](src%2FV1%2FTag%2FDictionaryBlockTag.php)
- [Opctim\BrunoLang\V1\Tag\ArrayBlockTag](src%2FV1%2FTag%2FArrayBlockTag.php)
- [Opctim\BrunoLang\V1\Tag\TextBlockTag](src%2FV1%2FTag%2FTextBlockTag.php)

Once you've done that, you'll need to specify the tag name inside the getTagName() method, run `composer dump-autoload`
and run the tests to ensure everything is working properly.

**Block type examples are documented here:** https://docs.usebruno.com/bru-lang/language

You can also find a (mostly) complete documentation of available tags there: https://docs.usebruno.com/bru-lang/tag-reference
