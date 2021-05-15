<?php

declare(strict_types=1);

namespace PackageVersions;

use Composer\InstalledVersions;
use OutOfBoundsException;

class_exists(InstalledVersions::class);

/**
 * This class is generated by composer/package-versions-deprecated, specifically by
 * @see \PackageVersions\Installer
 *
 * This file is overwritten at every run of `composer install` or `composer update`.
 *
 * @deprecated in favor of the Composer\InstalledVersions class provided by Composer 2. Require composer-runtime-api:^2 to ensure it is present.
 */
final class Versions
{
    /**
     * @deprecated please use {@see self::rootPackageName()} instead.
     *             This constant will be removed in version 2.0.0.
     */
    const ROOT_PACKAGE_NAME = 'proradis/project';

    /**
     * Array of all available composer packages.
     * Dont read this array from your calling code, but use the \PackageVersions\Versions::getVersion() method instead.
     *
     * @var array<string, string>
     * @internal
     */
    const VERSIONS          = array (
  'cboden/ratchet' => 'v0.3.6@84df35d2a6576985b9e81b564d3c25809f8d647e',
  'composer/package-versions-deprecated' => '1.11.99.1@7413f0b55a051e89485c5cb9f765fe24bb02a7b6',
  'doctrine/annotations' => '1.12.1@b17c5014ef81d212ac539f07a1001832df1b6d3b',
  'doctrine/cache' => '1.11.0@a9c1b59eba5a08ca2770a76eddb88922f504e8e0',
  'doctrine/collections' => '1.6.7@55f8b799269a1a472457bd1a41b4f379d4cfba4a',
  'doctrine/common' => '2.13.3@f3812c026e557892c34ef37f6ab808a6b567da7f',
  'doctrine/dbal' => '2.13.1@c800380457948e65bbd30ba92cc17cda108bf8c9',
  'doctrine/deprecations' => 'v0.5.3@9504165960a1f83cc1480e2be1dd0a0478561314',
  'doctrine/event-manager' => '1.1.1@41370af6a30faa9dc0368c4a6814d596e81aba7f',
  'doctrine/inflector' => '1.4.4@4bd5c1cdfcd00e9e2d8c484f79150f67e5d355d9',
  'doctrine/instantiator' => '1.4.0@d56bf6102915de5702778fe20f2de3b2fe570b5b',
  'doctrine/lexer' => '1.2.1@e864bbf5904cb8f5bb334f99209b48018522f042',
  'doctrine/orm' => '2.7.5@01187c9260cd085529ddd1273665217cae659640',
  'doctrine/persistence' => '1.3.8@7a6eac9fb6f61bba91328f15aa7547f4806ca288',
  'doctrine/reflection' => '1.2.2@fa587178be682efe90d005e3a322590d6ebb59a5',
  'evenement/evenement' => 'v2.1.0@6ba9a777870ab49f417e703229d53931ed40fd7a',
  'guzzle/common' => 'v3.9.2@2e36af7cf2ce3ea1f2d7c2831843b883a8e7b7dc',
  'guzzle/http' => 'v3.9.2@1e8dd1e2ba9dc42332396f39fbfab950b2301dc5',
  'guzzle/parser' => 'v3.9.2@6874d171318a8e93eb6d224cf85e4678490b625c',
  'guzzle/stream' => 'v3.9.2@60c7fed02e98d2c518dae8f97874c8f4622100f0',
  'jean85/pretty-package-versions' => '1.6.0@1e0104b46f045868f11942aea058cd7186d6c303',
  'jms/metadata' => '1.7.0@e5854ab1aa643623dc64adde718a8eec32b957a8',
  'jms/parser-lib' => '1.0.0@c509473bc1b4866415627af0e1c6cc8ac97fa51d',
  'mongodb/mongodb' => '1.8.0@953dbc19443aa9314c44b7217a16873347e6840d',
  'phpcollection/phpcollection' => '0.5.0@f2bcff45c0da7c27991bbc1f90f47c4b7fb434a6',
  'phpoption/phpoption' => '1.7.5@994ecccd8f3283ecf5ac33254543eb0ac946d525',
  'psr/log' => '1.0.0@fe0936ee26643249e916849d48e3a51d5f5e278b',
  'react/event-loop' => 'v0.4.3@8bde03488ee897dc6bb3d91e4e17c353f9c5252f',
  'react/promise' => 'v2.8.0@f3cff96a19736714524ca0dd1d4130de73dbbbc4',
  'react/socket' => 'v0.4.6@cf074e53c974df52388ebd09710a9018894745d2',
  'react/stream' => 'v0.4.6@44dc7f51ea48624110136b535b9ba44fd7d0c1ee',
  'symfony/config' => 'v2.5.3@70dc14f297e43e5551a9048d9ec83fd17f5a4d9c',
  'symfony/console' => 'v3.2.14@eced439413608647aeff243038a33ea246b2b33a',
  'symfony/debug' => 'v3.4.47@ab42889de57fdfcfcc0759ab102e2fd4ea72dcae',
  'symfony/dependency-injection' => 'v2.5.3@54529fdc797a88c030441773adadcc759bb102c2',
  'symfony/event-dispatcher' => 'v3.2.14@b8de6ee252af19330dd72ad5fc0dd4658a1d6325',
  'symfony/filesystem' => 'v2.8.52@7ae46872dad09dffb7fe1e93a0937097339d0080',
  'symfony/http-foundation' => 'v2.8.52@3929d9fe8148d17819ad0178c748b8d339420709',
  'symfony/polyfill-ctype' => 'v1.22.1@c6c942b1ac76c82448322025e084cadc56048b4e',
  'symfony/polyfill-mbstring' => 'v1.22.1@5232de97ee3b75b0360528dae24e73db49566ab1',
  'symfony/polyfill-php54' => 'v1.20.0@37285b1d5d13f37c8bee546d8d2ad0353460c4c7',
  'symfony/polyfill-php55' => 'v1.20.0@c17452124a883900e1d73961f9075a638399c1a0',
  'symfony/polyfill-php80' => 'v1.22.1@dc3063ba22c2a1fd2f45ed856374d79114998f91',
  'symfony/routing' => 'v2.6.13@0a1764d41bbb54f3864808c50569ac382b44d128',
  'zeedhi-inc/serializer' => '0.16.0@4770092a8c004ecae5655776c805e88469ee1477',
  'zeedhi/framework' => '2.3.4@b4b73d093069e94a281c4227d85477e96d3e2238',
  'zeedhi/libraries' => 'dev-master@8aed29c5f24bbda77863f8bcc322f3771c72661c',
  'proradis/project' => '1.0.0+no-version-set@',
);

    private function __construct()
    {
    }

    /**
     * @psalm-pure
     *
     * @psalm-suppress ImpureMethodCall we know that {@see InstalledVersions} interaction does not
     *                                  cause any side effects here.
     */
    public static function rootPackageName() : string
    {
        if (!class_exists(InstalledVersions::class, false) || !InstalledVersions::getRawData()) {
            return self::ROOT_PACKAGE_NAME;
        }

        return InstalledVersions::getRootPackage()['name'];
    }

    /**
     * @throws OutOfBoundsException If a version cannot be located.
     *
     * @psalm-param key-of<self::VERSIONS> $packageName
     * @psalm-pure
     *
     * @psalm-suppress ImpureMethodCall we know that {@see InstalledVersions} interaction does not
     *                                  cause any side effects here.
     */
    public static function getVersion(string $packageName): string
    {
        if (class_exists(InstalledVersions::class, false) && InstalledVersions::getRawData()) {
            return InstalledVersions::getPrettyVersion($packageName)
                . '@' . InstalledVersions::getReference($packageName);
        }

        if (isset(self::VERSIONS[$packageName])) {
            return self::VERSIONS[$packageName];
        }

        throw new OutOfBoundsException(
            'Required package "' . $packageName . '" is not installed: check your ./vendor/composer/installed.json and/or ./composer.lock files'
        );
    }
}