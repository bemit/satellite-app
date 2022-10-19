<?php declare(strict_types=1);

use Satellite\Launch\SatelliteLaunch;

(static function() {
    $container = (require __DIR__ . '/assemble.php')();

    /**
     * @var SatelliteLaunch $launch
     */
    $launch = $container->get(SatelliteLaunch::class);
    $launch->ignition();
})();
