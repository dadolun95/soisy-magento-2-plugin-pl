<?php
namespace Soisy\PaymentMethod\Log;

class Logger extends \Monolog\Logger
{
    public function messageConstruct($class, $function, $exception, $params = [])
    {
        $separator = " | ";
        $message = $class . "::" . $function . $separator;
        foreach ($params as $key => $value) {
            $message .= "$key: " . $value . $separator;
        }
        $message .= '$ex->getMessage: ' . $exception->getMessage();
        return $message;
    }
}