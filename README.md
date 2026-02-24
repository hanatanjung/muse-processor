# muse-processor
Combine multilanguage lyrics into single score and convert score into PPT

CodeIgniter 4.7

## Requirements

PHP >= 8.4
PHP extensions: simplexml, zip

## Errors on Deployement
`Fatal error: Declaration of CodeIgniter\Log\Logger::emergency(Stringable|string $message, array $context = []): void must be compatible with PsrExt\Log\LoggerInterface::emergency($message, array $context = []) in /var/www/html/vendor/codeigniter4/framework/system/Log/Logger.php on line 162`

This error is caused by CodeIgniter and shared hosting Logger. For that reason, make sure every `string|Stringable $message` in /vendor/codeigniter4/framework/system/Log/Logger.php to be replaced with only `$message`