<?php declare(strict_types=1);

namespace App\Commands;

use DI\Annotation\Inject;
use Exception;
use Psr\Log\LoggerInterface;
use Satellite\KernelConsole\Annotations\Command;

class AppWarmup {
    /**
     * @Inject
     */
    protected LoggerInterface $logger;

    /**
     * @Command(name="warmup")
     * @param \GetOpt\Command $command
     *
     * @throws Exception
     */
    public function handleWarmup(\GetOpt\Command $command) {
        $this->logger->debug('App should be warm now.');
        exit(0);
    }
}
