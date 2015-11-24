<?php

$codebase_path = dirname(dirname(dirname(dirname(__DIR__))));
$config_path = __FILE__;

$template_config = [
    'program_name'            => null,
    'command'                 => null,
    'process_name'            => '%(program_name)s_%(process_num)d',
    'numprocs'                => null,
    'numprocs_start'          => 0,
    'autorestart'             => 'true',
    'autostart'               => 'true',
    'startsecs'               => 0,
    'startretries'            => 3,
    'user'                    => 'apache',
    'stopsignal'              => 'TERM',
    'stderr_logfile'          => '/var/log/hodor/%(program_name)s_%(process_num)d.error.log',
    'stderr_logfile_maxbytes' => '10MB',
    'stderr_logfile_backups'  => 2,
    'stdout_logfile'          => '/var/log/hodor/%(program_name)s_%(process_num)d.debug.log',
    'stdout_logfile_maxbytes' => '10MB',
    'stdout_logfile_backups'  => 2,
];

$program_details = [
    [
        'program_name' => 'hodor-superqueuer-default',
        'command'      => "/usr/bin/env php '{$codebase_path}/src/Hodor/Daemon/../../../bin/superqueuer.php' -c '{$config_path}'",
        'numprocs'     => 1,
    ],
    [
        'program_name' => 'hodor-bufferer-default',
        'command'      => "/usr/bin/env php '{$codebase_path}/src/Hodor/Daemon/../../../bin/buffer-worker.php' -c '{$config_path}' -q 'default'",
        'numprocs'     => 10,
    ],
    [
        'program_name' => 'hodor-bufferer-special',
        'command'      => "/usr/bin/env php '{$codebase_path}/src/Hodor/Daemon/../../../bin/buffer-worker.php' -c '{$config_path}' -q 'special'",
        'numprocs'     => 10,
    ],
    [
        'program_name' => 'hodor-worker-default',
        'command'      => "/usr/bin/env php '{$codebase_path}/src/Hodor/Daemon/../../../bin/job-worker.php' -c '{$config_path}' -q 'default'",
        'numprocs'     => 10,
    ],
    [
        'program_name' => 'hodor-worker-intense',
        'command'      => "/usr/bin/env php '{$codebase_path}/src/Hodor/Daemon/../../../bin/job-worker.php' -c '{$config_path}' -q 'intense'",
        'numprocs'     => 2,
    ],
];

$expected_configs = [];
foreach ($program_details as $program_detail) {
    $expected_configs[$program_detail['program_name']] = array_merge(
        $template_config,
        $program_detail
    );
}

return $expected_configs;
