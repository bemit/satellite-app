#!/bin/bash
# start the local php development server

port=3333
bind=0.0.0.0

php -S ${bind}:${port} -c php.ini ./server.php display_errors=0