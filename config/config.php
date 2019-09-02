<?php

return [
    /**
     * Domain + optional port. Don't add the api path like "/druid/v2"
     */
    'broker_url'      => env('DRUID_BROKER_URL', ''),

    /**
     * Domain + optional port. Don't add the api path like "/druid/coordinator/v1"
     */
    'coordinator_url' => env('DRUID_COORDINATOR_URL', ''),

    /**
     * Domain + optional port. Don't add the api path like "/druid/indexer/v1"
     */
    'overlord_url'    => env('DRUID_OVERLORD_URL', ''),

    /**
     * The number of times we will try to do a retry in case of a failure. So if retries is 2, we will try to
     * execute the query in worst case 3 times.
     *
     * First time is the normal attempt to execute the query.
     * Then we do the FIRST retry.
     * Then we do the SECOND retry.
     */
    'retries'         => 2,

    /**
     * When a query fails to be executed, this is the delay before a query is retried.
     * Default is 500 ms, which is 0.5 seconds.
     *
     * Set to 0 to disable they delay between retries.
     */
    'retry_delay_ms'  => 500,
];