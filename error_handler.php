<?php

error_reporting(E_ALL);

// handle exceptions
function myExceptionHandler ($e)
{
    error_log($e);
    http_response_code(500);
    // on developer servers this can be switched on in php.ini
    if (ini_get('display_errors')) {
        echo $e;
    } else {
        echo "<h1>500 Internal Server Error</h1>
              An internal server error has been occurred.<br>
              Please try again later.";
    }
}
set_exception_handler('myExceptionHandler');

// handle errors
set_error_handler(function ($level, $message, $file = '', $line = 0)
{
    throw new ErrorException($message, 0, $level, $file, $line);
});

register_shutdown_function(function ()
{
    $error = error_get_last();
    if ($error !== null) {
        $e = new ErrorException(
            $error['message'], 0, $error['type'], $error['file'], $error['line']
        );
        myExceptionHandler($e);
    }
});

