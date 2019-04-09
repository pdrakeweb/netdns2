# NET_DNS2

## Acquia Customizations

### PHP 7.x Compatibility

This library has been forked and updated to have PHP 7.x compatibility added to
the 1.3.2 release.

## Testing

1. Install composer
1. Install dev dependencies
  `composer install --dev`

### PHPUnit Tests

1. Run the tests.
  `php tests/AllTests.php`

### PHP Compatibility

1. Inform PHPCS about the PHPCompatibility standard.
  `vendor/bin/phpcs --config-set installed_paths vendor/phpcompatibility/php-compatibility`
1. Run the compatibility check.
  `./vendor/bin/phpcs -p ./Net --standard=PHPCompatibility`