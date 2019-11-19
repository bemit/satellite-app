# Orbiter\Satellite

Easy to use Event & Middleware Mini-Framework, powered by popular projects and PSR's.

- [Config](#markdown-header-config)
- [Setup](#markdown-header-setup)
- [Download Build](#markdown-header-download-build)

## Config

// todo

## Setup

```bash

composer create-project orbiter/satellite-app ./satellite

cd ./satellite
 
```

### Linux, PHP Dev Server

Setup correct user rights, then start and open http://localhost:3333

```bash 
chmod +x start.sh

./start.sh
```

### PHP Command-Line

Execute defined commands.

```bash 
php cli <command> <..attr> <..b>
```

### Docker, docker-compose

Includes configurable PHP Dockerfile with:

- PHP 7.3
- Apache
- OPCache
- MySQL Client lib's (pdo, pdo_mysql, mysqli)
- Apache Mods: rewrite, deflate, brotli
- customize in [Dockerfile](Dockerfile)

Config's are system defaults, see files `docker-*`

Start containers specified in `docker-compose.yml`, then open: http://localhost:3333

```bash
docker-compose up
```

### Web-Server

On a web-server the `web/index.php` file serves as public entry point.

The cli could be used for e.g. cron's.

- Apache: point server/vhost root to `/web` and use the included `.htaccess`
- nginx directive:

```.conf
location / {
    # DOCROOT must contain absolute path to `web`, in this example 
    root {DOCROOT};
    # try to serve file directly, fallback to index.php
    try_files $uri /index.php$is_args$args;
}
```

## Download Build

# Licence

This project is free software distributed under the **MIT License**.

See: [LICENCE](LICENCE).

### Contributors

By committing your code to the code repository you agree to release the code under the MIT License attached to the repository without the expectation of consideration.

# Copyright

Maintained by [Michael Becker](https://mlbr.xyz)
