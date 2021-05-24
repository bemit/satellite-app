# Console for Orbiter\Satellite 🛰️

- [Setup](../../README.md#setup)
    - [Config](../../README.md#config)
- [Implemented PSRs](../../README.md#psrs)
- [Used Packages](../../README.md#used-packages)
- [Features](README.md)
    - [Events](feature-events.md)
    - [Routing](feature-routing.md)
        - [Middleware](feature-middleware.md)
    - [Console](feature-console.md)
    - [DI](feature-di.md)
- [System Default Events](satellite-events.md)

## Feature Console

Execute PHP code from the commandline, register commands before or during launching.

- `php cli <command> <..attr> <..b>`
- like: `php cli version` or `php cli help`

Create a command in the `app/Commands` folder and use annotations to register the options:

```php
<?php

namespace App\Commands;

use Satellite\KernelConsole\Annotations\Command;
use Satellite\KernelConsole\Annotations\CommandOperand;

/**
 * Class Demo
 *
 * @Command(
 *     name="demo",
 *     handler="handle",
 *     operands={
 *          @CommandOperand(name="name", description="the name to welcome")
 *     }
 * )
 */
class Demo {
    public function handle(\GetOpt\Command $command) {
        error_log('Hi ' . (isset($command->getOperands()[0]) ? $command->getOperands()[0] : 'there') . '!');
    }
}
```

And it is ready for execution:

```bash
php cli demo
# prints: Hi there!

php cli demo Folks
# prints: Hi Folks!
```

Create a command manually and register it in `config/events.php` to the app:

```php
<?php

use Satellite\Event\EventListenerInterface;

return static function(
    EventListenerInterface $event,
    Satellite\SatelliteAppInterface $app
): void {
    $event->on(Satellite\KernelConsole\Console::class, static function(Satellite\KernelConsole\Console $console, GetOpt\GetOpt $get_opt) {
        $cmd = new GetOpt\Command('demo2', function(GetOpt\Command $command) {
            error_log('Hi ' . (isset($command->getOperands()[0]) ? $command->getOperands()[0]->getValue() : 'there') . '!');
        });
        $cmd->addOperand(new GetOpt\Operand('name'));
        $get_opt->addCommand($cmd);
        return $console;
    });
};
```

For more details on how to use `GetOpt` to build commands with more details, options and operands see [getopt-php docs.](https://github.com/getopt-php/getopt-php).
