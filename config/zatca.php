<?php

return [
    'simulation_mode' => env('ZATCA_SIMULATION_MODE', false),

    'solution_name' => env('ZATCA_SOLUTION_NAME', 'ZatcaApp'),
    'solution_version' => env('ZATCA_SOLUTION_VERSION', '1.0.0'),

    /*
    |--------------------------------------------------------------------------
    | ZATCA API base URLs
    |--------------------------------------------------------------------------
    */
    'sandbox_base_url' => env('ZATCA_SANDBOX_BASE_URL', 'https://gw-fatoora.zatca.gov.sa/e-invoicing/developer-portal'),
    'simulation_base_url' => env('ZATCA_SIMULATION_BASE_URL', 'https://gw-fatoora.zatca.gov.sa/e-invoicing/simulation'),
    'production_base_url' => env('ZATCA_PRODUCTION_BASE_URL', 'https://gw-fatoora.zatca.gov.sa/e-invoicing/core'),

    /*
    |--------------------------------------------------------------------------
    | CSR certificate template names per environment
    |--------------------------------------------------------------------------
    */
    'csr_templates' => [
        'sandbox' => 'TSTZATCA-Code-Signing',
        'simulation' => 'PREZATCA-Code-Signing',
        'production' => 'ZATCA-Code-Signing',
    ],

    /*
    |--------------------------------------------------------------------------
    | Invoice types encoded in CSR title
    | 1100 = standard + simplified
    |--------------------------------------------------------------------------
    */
    'invoice_type_code' => env('ZATCA_INVOICE_TYPE_CODE', '1100'),
];
