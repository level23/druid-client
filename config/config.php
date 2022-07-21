<?php

return [

    /**
     * Domain + optional port or the druid router. If this is set, it will be used for the broker,
     * coordinator and overlord.
     */
    'router_url'      => env('DRUID_ROUTER_URL', ''),

    /**
     * Domain + optional port. Don't add the api path like "/druid/v2"
     */
    'broker_url'      => env('DRUID_BROKER_URL', env('DRUID_ROUTER_URL')),

    /**
     * Domain + optional port. Don't add the api path like "/druid/coordinator/v1"
     */
    'coordinator_url' => env('DRUID_COORDINATOR_URL', env('DRUID_ROUTER_URL')),

    /**
     * Domain + optional port. Don't add the api path like "/druid/indexer/v1"
     */
    'overlord_url'    => env('DRUID_OVERLORD_URL', env('DRUID_ROUTER_URL')),

    /**
     * The maximum duration of a druid query. If the response takes longer, we will close the connection.
     */
    'timeout'         => env('DRUID_TIMEOUT', 60),

    /**
     * The maximum duration of connecting to the druid instance.
     */
    'connect_timeout' => env('DRUID_CONNECT_TIMEOUT', 10),

    /**
     * The number of times we will try to do a retry in case of a failure. So if retries is 2, we will try to
     * execute the query in worst case 3 times.
     *
     * First time is the normal attempt to execute the query.
     * Then we do the FIRST retry.
     * Then we do the SECOND retry.
     */
    'retries'         => env('DRUID_RETRIES', 2),

    /**
     * When a query fails to be executed, this is the delay before a query is retried.
     * Default is 500 ms, which is 0.5 seconds.
     *
     * Set to 0 to disable they delay between retries.
     */
    'retry_delay_ms'  => env('DRUID_RETRY_DELAY_MS', 500),

    /**
     * Amount of time in seconds to wait till we try and poll a task status again.
     */
    'polling_sleep_seconds' => env('DRUID_POLLING_SLEEP_SECONDS', 2),
];