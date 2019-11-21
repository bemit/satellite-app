# Console for Orbiter\Satellite 🛰️

- [Setup](../#setup)
    - [Config](../#config)
- [Implemented PSRs](../#psrs)
- [Used Packages](../#used-packages)

## Features
- [Events](feature-events.md)
- [Routing](feature-routing.md)
    - [Middleware](feature-middleware.md)
- [Console](feature-console.md)
- [DI](feature-di.md)

## Feature Console

Execute PHP code from the commandline, register commands before or during launching.

- `php cli <command> <..attr> <..b>`
- like: `php cli version` or `php cli help`

Create a command and automatically register it to the app:

```php
<?php
use Satellite\KernelConsole\Command;
use Satellite\KernelConsole\ConsoleEvent;

/**
 * @var GetOpt\Command $command
 */
$command = Command::create(
    'hi',
     static function(ConsoleEvent $evt) {
        error_log('Hi ' .
            
            ($evt->operands[0] && $evt->operands[0]->getValue() ?
                $evt->operands[0]->getValue() :
                'there')

            . '!');
    },
    [] // options
);

$command->addOperands([
    new GetOpt\Operand('name', GetOpt\Operand::OPTIONAL),
]);
```

And it is ready for execution:

```bash
php cli hi
# prints: Hi there!

php cli hi Folks
# prints: Hi Folks!
```

Create a command natively and register it manually to the app:

```php
<?php

use Satellite\KernelConsole\Console;
use Satellite\KernelConsole\ConsoleEvent;

$command = new GetOpt\Command(
    'hi', // name
    static function(ConsoleEvent $evt) {
         error_log('Hi ' .
             ($evt->operands[0] && $evt->operands[0]->getValue() ?
                 $evt->operands[0]->getValue() :
                 'there')
             . '!');
    }, 
    [] // options
);

$command->addOperands([
    new GetOpt\Operand('name', GetOpt\Operand::OPTIONAL),
]);

Console::addCommand('hi', $command);
```

For more details on how to use `GetOpt` to build commands with more details, options and operands see [getopt-php docs.](https://github.com/getopt-php/getopt-php).
