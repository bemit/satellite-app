#!/bin/bash
# start the local php development server

port=3333
bind=0.0.0.0

cd web && php -S ${bind}:${port} -c php.ini ./index.php display_errors=0
