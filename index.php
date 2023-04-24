<?php
require "Api.php";
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode( '/', $uri );
// print_r();
$objFeedController = new Api();
$objFeedController->{$uri['4']}();
?>