<?php declare(strict_types=1);

use Satellite\Launch\SatelliteLaunchInterface;

(static function() {
    $container = (require __DIR__ . '/assemble.php')();

    /**
     * @var SatelliteLaunchInterface $launch
     */
    $launch = $container->get(SatelliteLaunchInterface::class);
    $launch->ignition();
})();
