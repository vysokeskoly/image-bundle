Image Bundle
============

[![Latest Stable Version](https://img.shields.io/packagist/v/vysokeskoly/image-bundle.svg)](https://packagist.org/packages/vysokeskoly/image-bundle)
[![License](https://img.shields.io/packagist/l/vysokeskoly/image-bundle.svg)](https://packagist.org/packages/vysokeskoly/image-bundle)
[![Checks](https://github.com/vysokeskoly/image-bundle/actions/workflows/checks.yaml/badge.svg)](https://github.com/vysokeskoly/image-bundle/actions/workflows/checks.yaml)
[![Build](https://github.com/vysokeskoly/image-bundle/actions/workflows/php-checks.yaml/badge.svg)](https://github.com/vysokeskoly/image-bundle/actions/workflows/php-checks.yaml)
[![Coverage Status](https://coveralls.io/repos/github/vysokeskoly/image-bundle/badge.svg)](https://coveralls.io/github/vysokeskoly/image-bundle)

Changelog
---------
See CHANGELOG.md.

Installation
-----------------------------

### Step 1

Download using *composer*

    "require": {
        "vysokeskoly/image-bundle" : "^5.0"
    },

### Step 2

Add VysokeSkolyImageBundle bundle to AppKernel to list of loaded bundles.

```php
$bundles = [
    // ..
    new VysokeSkoly\ImageBundle\VysokeSkolyImageBundle(),                            
    // ..
];
```

### Step 3

Configure required parameters for this bundle.

**Implementation of ImageRepositoryInterface**

- for example class `My\ImageRepository` implements `ImageRepositoryInterface`
- its Autowired, so you do not have to specify it explicitly

**config.yml**

    # EDU ImageBundle
    vysoke_skoly_image:
        image_formats:
            preview1x:
                width: 260
                height: 175
            preview2x:
                width: 520
                height: 350
            cropped:
                width: 100
                height: 100
                crop:
                    x: 20
                    y: 20
                    x2: 120
                    y2: 120
            cropped2:
                width: 100
                height: 100
                crop:
                    x: 20
                    y: 20
                    width: 100
                    height: 100
        
    # [OPTIONAL]
    services:
        app.repository.image:
            class: My\ImageRepository
