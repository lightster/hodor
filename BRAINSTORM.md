# lzq

## Functionality

 - Queue jobs
   - To run now
   - To run later
   - To run after other jobs complete
   - To run after other criteria are met

 - Run jobs
   - As quickly and fairly as possible
   - Across multiple servers
   - Without using more resources than are available

## Usage

### Queueing a Job
~~~php
    Q::push(
        $job_name = 'job_to_run',
                    // 'silex_service'
                    // 'Some\Class\Name'
                    // 'silex_service#methodName'
                    // 'Some\Class\Name#methodName'
        $job_params = array(
            'param1' => 'value1',
            'param2' => 'value2',
        ),
        $options = array(
             'queue_name' => 'default',
             'start_time' => new DateTime(),
                             // '+1 hour'
                             // '2014-07-20 00:15:00'
                             // 'now'
             'depends_on' => array(
                 12,         // job id
                 $job,       // job object
             ),
        )
    );

    // other $job_name possibility
    $job_name = array(
        'type'   => 'pimple',
                    // 'silex' - same as 'pimple'
                    // 'class'
        'name'   => 'silex_service',
        'method' => 'methodName',
    );
~~~
