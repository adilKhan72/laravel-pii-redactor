<?php

return [
    /*
     * Built-in detectors to run, in this order (most specific first). Remove a type to stop redacting it.
     */
    'types' => ['SSN', 'ROUTING_NUMBER', 'ACCOUNT_NUMBER', 'CARD_NUMBER', 'EMAIL', 'PHONE', 'NAME', 'DATE_OF_BIRTH'],

    /*
     * Extra patterns for identifiers specific to your domain, run after the built-ins.
     * 'TYPE' => ['/regex/', ...]  →  matches become "[TYPE]".
     */
    'custom' => [
        // 'EMPLOYEE_ID' => ['/\bEMP-\d{6}\b/'],
    ],
];
