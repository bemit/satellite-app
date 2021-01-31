<?php

namespace App\RouteHandler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Satellite\KernelRoute\Annotations\Route;
use Satellite\Response\Response;

/**
 * @Route(name="home", path="/", method="GET")
 */
class Home implements RequestHandlerInterface {
    public function handle(ServerRequestInterface $request): ResponseInterface {
        $html = <<<HTML
<!doctype HTML>
<html style="background: #0e1a27; color:#d5d5d5;font-family: sans-serif; text-align: center;">
    <h2 style="font-size: 2rem;">
        <span style="display: inline-flex; align-items: center;">Satellite <span style="font-size: 2em;">🛰️</span></span>
    </h2>
    <p style="text-align: center; position: fixed; bottom: 0; left: 0; right: 0;">
        <a
            href="https://github.com/bemit/satellite-app" target="_blank"
            style="display: inline-flex; align-items: center; color: #d5d5d5; text-decoration: none;"
        ><span style="padding-right: 12px;">Docs</span> <svg class="octicon octicon-mark-github v-align-middle" height="24" width="24" viewBox="0 0 16 16" aria-hidden="true"><path fill-rule="evenodd" fill="#d5d5d5" d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0016 8c0-4.42-3.58-8-8-8z"></path></svg></a>
    </p>
</html>
HTML;
        return (new Response($html))->html();
    }
}
