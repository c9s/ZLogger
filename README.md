ZLogger
==========

Server
------

```php
<?php
$logger = new ZLogger\FileLogger(array( 
    'bind' => 'tcp://127.0.0.1:5555',
    'path' => 'log/info.log',
));
echo "Listening " . $logger->bind . "...\n";
$logger->start();
```

Client
------

```php
<?php
$logger = new ZLogger\Client(array( 
    'socket_id' => 'logger_sock',
    'timeout' => 1000,
    'retry' => 3,
    'console' => true,
));

$logger->info( "Hello $i" );
```

Log Message Structure
-----------------------

    [
        type: {message type},
        behavior: {  }
        user:  {user name}
        message: {message name}
    ]

log type define:

* info
* debug
* warn
* notice
* exception


