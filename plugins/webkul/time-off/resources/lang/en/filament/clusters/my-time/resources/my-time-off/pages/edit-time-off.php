<?php

return [
    'notification' => [
        'title'              => 'Time Off updated',
        'body'               => 'The time off has been updated successfully.',
        'action_not_allowed' => [
            'title' => 'Action Not Allowed',
            'body'  => 'You cannot modify this leave request as it is in a locked state.',
        ],
    ],

    'header-actions' => [
        'delete' => [
            'notification' => [
                'title' => 'Time Off deleted',
                'body'  => 'The time off has been deleted successfully.',
            ],
        ],
    ],
];
