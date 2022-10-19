<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use DI\Annotation\Inject;

final class AppTest extends TestCase {

    /**
     * @Inject()
     */
    protected \Satellite\Launch\SatelliteAppInterface $app;

    public function __construct(?string $name = null, array $data = [], $dataName = '') {
        parent::__construct($name, $data, $dataName);
        $container = (require __DIR__ . '/../assemble.php')();
        $container->injectOn($this);
    }

    public function testCorrectAppImplementation(): void {
        $this->assertEquals(Satellite\Launch\SatelliteApp::class, get_class($this->app));
    }
}
