## Logs

There's been times that people fall through the cracks, maybe they're not added to a queue or the CCB api might just fail. It is important to keep track of these error so people are not missed.

For logging we will be using the [Monolog](https://github.com/Seldaek/monolog) package, a popular package that implements the [PSR-3](https://www.php-fig.org/psr/psr-3/) interface. Logs can be found under the `logs` directory, a file for each type of log level will be created.

## Slack Message
For critical updated we have a slack notification system, it will send messages to the `code-errors` channel. 

Here are the different log levels

_Just logs_  
`DEBUG`  
`INFO`  
`NOTICE`  
_Sends slack message_  
`WARNING`  
`ERROR`  
`CRITICAL`  
`ALERT`  
`EMERGENCY`  
