# ztracker  

## Configuration  
    
Add the following to your composer.json:  
    
    ```
    "require": {
        "tricolor/ztracker": "~0.0.7.6"
    },
    "repositories": [
        {
            "type": "cvs",
            "url": "https://github.com/liubole/ztracker"
        }
    ],
    ```
    
Add the following to your "index.php"(or the entrance of request, both client and server):  
    
    ```
    global $trace_open;
    $trace_open = class_exists('Tricolor\ZTracker\Core\GlobalTracer');
    if ($trace_open) {
        // rabbitmq config
        \Tricolor\ZTracker\Config\Collector::rabbitConfig($trace_rabbitmq);
        // rate control
        \Tricolor\ZTracker\Config\Collector::$sampleRate = 5;
        // default output file: /tmp/biz-ztrace.log
        \Tricolor\ZTracker\Config\BizLogger::$output = '/tmp/trace_logs/biz-ztrace.log';
    }
    ```
    
## Usage  

Client(index.php):  
    
    ```
    global $trace_open;
    if ($trace_open) {
        try {
            $tracer = \Tricolor\ZTracker\Core\GlobalTracer::tracer();
            $tracer->newSpan()
                ->kind(Tricolor\ZTracker\Core\SpanKind\Server)
                ->shared(false);
            $tracer->currentSpan()->putTag('request_url', (string)$_SERVER['REQUEST_URI']);
            $tracer->log('get', $_GET);
            $tracer->log('post', $_POST);
            $tracer->log('server', $_SERVER);
        } catch (\Exception $e) {}
    }
    ```
    
Server(index.php):  

    ```
    global $trace_open;
    if ($trace_open) {
        try {
            $tracer = Tricolor\ZTracker\Core\GlobalTracer::tracer();
            $tracer->injector(Tricolor\ZTracker\Carrier\CarrierType\HttpHeader)->extract();
            $tracer->currentSpan()
                ->name(Tricolor\ZTracker\Common\Util::getServerApi())
                ->shared(true)
                ->kind(Tricolor\ZTracker\Core\SpanKind\Server);
            $tracer->log('get', $_GET);
            $tracer->log('post', $_POST);
            $tracer->log('server', $_SERVER);
        } catch (\Exception $e) {}
    }
    ```

If we want to use it at other place:  

    ```
    global $trace_open;
    if ($trace_open) {
        $mysqlSpan = \Tricolor\ZTracker\Core\GlobalTracer::tracer()
            ->newChildSpan()->name('mysql.insert.user')
            ->shared(false)->kind(\Tricolor\ZTracker\Core\SpanKind\Client);
    }
    ```

## Further reading  

1. Google Dapper: https://research.google.com/archive/papers/dapper-2010-1.pdf  
2. Zipkin: https://zipkin.io/  
GitHub - openzipkin/zipkin: https://github.com/openzipkin/zipkin  
4. OpenTracing: http://opentracing.io/   
OpenTracing API · GitHub: https://github.com/opentracing  
    
    