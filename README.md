# NET_DNS2

## Acquia Customizations

### PHP 7.x Compatibility

This library has been forked and updated to have PHP 7.x compatibility added to
the 1.3.2 release.

#### each()

each() has been deprecated.  current() and next() are being used to replace it
with as little code impact as possible to reduce risk.

## Building

1. Create the build
  `./build.sh`
1. Copy the resulting artifact to the destination eg.
  `cp build/Net_DNS2-*.tgz ../fields/puppet/versions/devel/modules/php/files/`

## Testing

1. Install composer
1. Install dev dependencies
  `composer install --dev`
1. Install phpstan library if static analysis is desired (PHP >=7.0 only)
  `composer require phpstan/phpstan`

### PHPUnit Tests

The PHPUnit tests we added primarily cover the behavior with a variety of
nameserver conditions such as valid, invalid and unreachable.  The base record
type chosen for these tests is A record.  Additionally, these tests cover CNAME
and TXT record retrieval as these are the record types retrieved within our use
of this library.

1. Run the tests.
  `php tests/AllTests.php`

### PHP Compatibility

1. Inform PHPCS about the PHPCompatibility standard.
  `vendor/bin/phpcs --config-set installed_paths vendor/phpcompatibility/php-compatibility`
1. Run the compatibility check.
  `./vendor/bin/phpcs -p ./Net --standard=PHPCompatibility`

### PHP STAN

1. Run PHP-STAN against the DNS2 class that we have modified.
  `vendor/bin/phpstan analyse Net/DNS2.php`
