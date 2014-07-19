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
        $job_options = array(
             'queue_name' => 'default',
             'start_time' => new DateTime(),
                             // '+1 hour'
                             // '2014-07-20 00:15:00'
                             // 'now'
             'depends_on' => array(
                 12,         // job id
                 $job,       // job object
             ),
             'priority'     => 10,
                               // use `nice`'s semantics:
                               // - lower numbers run sooner
                               // - allow -20 to 20
             'max_failures' => 3,
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

### Handling a Job
~~~php
    namespace Some\Class;

    class Name extends JobRunner
    {
        public function run(array $job_params, array $job_options)
        {
            // do some stuff to process the job

            if ($something_went_wrong) {
                // something went wrong, but if there
                // are attempts remaining try again
                return $this->markAsFailure();
            } elseif ($something_else_went_wrong) {
                // do not try again
                return $this->markAsPermanentFailure();
            } elseif ($what_else_goes_wrong) {
                throw new Exception('same as $this->markAsFailure()');
            }
            else {
                // success is assumed, otherwise
            }
        }

        public function isReadyToRun(array $job_params, array $job_options)
        {
             return false;
        }
    }
~~~