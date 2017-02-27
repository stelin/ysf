<?php
mb_internal_encoding("UTF-8");
$hostname = gethostname();

if ($hostname == "newdev") {
    define('APPLICATION_ENV', 'testing');
} else {
    define('APPLICATION_ENV', 'production');
}