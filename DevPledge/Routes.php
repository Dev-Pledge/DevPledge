<?php
/**
 * @var \FastRoute\RouteCollector $r
 */

$r->addRoute( 'GET', '/en/january-cash-advice', 'OnePage@creditHelp' );
$r->addRoute( 'GET', '/', 'OnePage@creditHelp' );