ZLogger
==========

Server
------

    $logger = new ZLogger\Logger;
    $logger->bind( '127.0.0.1' , 8000 );
    $logger->onError(function($msg) {

    });
    $logger->onInfo(function($msg) {

    });
    $logger->onWarn(function($msg) {  });
    $logger->onNotice(function($msg) {  });

Client
------

    $client = new ZLogger\Client;
    $client->info( 'message' , [ ... extra information ]);
    $client->warn( 'message' , [ ... extra information ]);
    $client->notice( 'message' , [ ... extra information ]);

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





