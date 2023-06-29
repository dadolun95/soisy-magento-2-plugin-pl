<?php
namespace Soisy\PaymentMethod\Log;

use Magento\Framework\Logger\Handler\Base;

class Handler extends Base
{
    protected $loggerType = Logger::INFO;
    protected $fileName = '/var/log/pagolight.log';
}
