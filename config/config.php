<?php

return [
    /**
     * domain + optional port. Don't add the api path like "/druid/v2"
     */
    'broker_url'      => '',

    /**
     * domain + optional port. Don't add the api path like "/druid/coordinator/v1"
     */
    'coordinator_url' => '',

    /**
     * domain + optional port. Don't add the api path like "/druid/indexer/v1"
     */
    'overlord_url'    => '',

    /**
     * The number of times we will try to do a retry in case of a failure.
     */
    'retries'         => 2,
];