<?php

return [
    // most basic scenario
    [
        // buffered jobs
        [
            ['name' => '1'],
            ['name' => '2'],
        ],
        // queued jobs
        [['name' => '3']],
        // expected job order
        ['1', '2'],
    ],
    // rank dictates queue order
    [
        // buffered jobs
        [
            ['name' => '1', 'job_rank' => 5],
            ['name' => '2', 'job_rank' => 1],
        ],
        // queued jobs
        [['name' => '3']],
        // expected job order
        ['2', '1'],
    ],
    // mutex prevents jobs from being queued
    [
        // buffered jobs
        [
            ['name' => '1', 'mutex_id' => 'a'],
            ['name' => '2', 'mutex_id' => 'a'],
        ],
        // queued jobs
        [['name' => '3', 'mutex_id' => 'a']],
        // expected job order
        [],
    ],
    // mutex only allows one job to be queued
    [
        // buffered jobs
        [
            ['name' => '1', 'mutex_id' => 'a'],
            ['name' => '2', 'mutex_id' => 'a'],
        ],
        // queued jobs
        [['name' => '3', 'mutex_id' => 'b']],
        // expected job order
        ['1'],
    ],
    // mutex and rank allows only the top-ranked job to run
    [
        // buffered jobs
        [
            ['name' => '1', 'mutex_id' => 'a', 'job_rank' => 5],
            ['name' => '2', 'mutex_id' => 'a', 'job_rank' => 1],
        ],
        // queued jobs
        [['name' => '3', 'mutex_id' => 'b']],
        // expected job order
        ['2'],
    ],
    // run after in the future prevents job from being queued now
    [
        // buffered jobs
        [
            ['name' => '1', 'run_after' => 60],
            ['name' => '2', 'run_after' => 60],
        ],
        // queued jobs
        [['name' => '3']],
        // expected job order
        [],
    ],
    // mutexed run after in the future allows other mutexed job to become queued
    [
        // buffered jobs
        [
            ['name' => '1', 'mutex_id' => 'a', 'run_after' => 60],
            ['name' => '2', 'mutex_id' => 'a'],
        ],
        // queued jobs
        [['name' => '3', 'mutex_id' => 'b']],
        // expected job order
        ['2'],
    ],
    // rank does not allow future jobs to affect current jobs
    [
        // buffered jobs
        [
            ['name' => '1', 'mutex_id' => 'a', 'job_rank' => 1, 'run_after' => 60],
            ['name' => '2', 'mutex_id' => 'a', 'job_rank' => 5],
        ],
        // queued jobs
        [['name' => '3', 'mutex_id' => 'b']],
        // expected job order
        ['2'],
    ],
];
