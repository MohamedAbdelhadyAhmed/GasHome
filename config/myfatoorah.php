<?php

return [
    /**
     * API Token Key (string)
     * Accepted value:
     * Live Token: https://myfatoorah.readme.io/docs/live-token
     * Test Token: https://myfatoorah.readme.io/docs/test-token
     */
    // 'api_key' => 'V6eLUuafgYq494aQHHcvMNgfjjZVu0a9XJSiokIeyOX213fLWEWxh0RUDKFgAnD_yNFK_o1J2qwGaPg9kR5s3AcYqqbbRBfcDsjcESri22vUWCGdzrXyOaoUnkiIJG-p0U48wTUoC7WmOT_cxc6SGTz-N3lNkHSoiK_71uS543U0-so-LCp0eBxwpFLePe02BQpoHTpmgfwRg0jHm3n6UZ7wmE3V2Fcu5HAeFCUu7SOMEI3nxXe2m4o9fPPEL3EL4ryuOrFVeAW2x8gq91EBWa66QFoHniMLVWHTlEm_aJ68VWbLsNWWsna5nMG0A_dAUzG-oHg3O8eFXw8CRnoxrYo0HYlwb4Z6V5aXd5Nb8s_26gvemlqEYwOURRW8quwxBd42EqDzjMTECsdgt0LyLhDP2GzwkzZUXhKoEF-01SnPuz6Sx0bQ8b5j8Y651eZd7NRQ1rEGXedFi-prsVmEM7EDdVED3eCZe_clG_kgXDPOAextN9Exm0Fsqi9YPviUT6uh3jK8Pj8aiWgOhS8T1ndSPo_LZbC7FN8r2LObHZaVXdaweKNz-eqXHEsq0YAmgHKehtYJ5601mIfzvwceJJ-ffF-FHbDPukr5DJBOoLeQyp0vJqxcaf367vgRIeMXwFtEm-gyZOVfj9rmVXDNVbbSvXugkq7tn-4LjI8u0nNBXAcHmHHFdwEcg1lNEAWq9-BadcbL_hZLw1aet9BQ0glP7Z5B2vUQeo4sGyBIl0ipKD-R',
    'api_key' => 'rLtt6JWvbUHDDhsZnfpAhpYk4dxYDQkbcPTyGaKp2TYqQgG7FGZ5Th_WD53Oq8Ebz6A53njUoo1w3pjU1D4vs_ZMqFiz_j0urb_BH9Oq9VZoKFoJEDAbRZepGcQanImyYrry7Kt6MnMdgfG5jn4HngWoRdKduNNyP4kzcp3mRv7x00ahkm9LAK7ZRieg7k1PDAnBIOG3EyVSJ5kK4WLMvYr7sCwHbHcu4A5WwelxYK0GMJy37bNAarSJDFQsJ2ZvJjvMDmfWwDVFEVe_5tOomfVNt6bOg9mexbGjMrnHBnKnZR1vQbBtQieDlQepzTZMuQrSuKn-t5XZM7V6fCW7oP-uXGX-sMOajeX65JOf6XVpk29DP6ro8WTAflCDANC193yof8-f5_EYY-3hXhJj7RBXmizDpneEQDSaSz5sFk0sV5qPcARJ9zGG73vuGFyenjPPmtDtXtpx35A-BVcOSBYVIWe9kndG3nclfefjKEuZ3m4jL9Gg1h2JBvmXSMYiZtp9MR5I6pvbvylU_PP5xJFSjVTIz7IQSjcVGO41npnwIxRXNRxFOdIUHn0tjQ-7LwvEcTXyPsHXcMD8WtgBh-wxR8aKX7WPSsT1O8d8reb2aR7K3rkV3K82K_0OgawImEpwSvp9MNKynEAJQS6ZHe_J_l77652xwPNxMRTMASk1ZsJL',
    /**
     * Test Mode (boolean)
     * Accepted value: true for the test mode or false for the live mode
     */
    // 'test_mode' => false,
    'test_mode' => true,
    /**
     * Country ISO Code (string)
     * Accepted value: KWT, SAU, ARE, QAT, BHR, OMN, JOD, or EGY.
     */
    'country_iso' => 'SAU',
    /**
     * Save card (boolean)
     * Accepted value: true if you want to enable save card options.
     * You should contact your account manager to enable this feature in your MyFatoorah account as well.
     */
    'save_card' => true,
    /**
     * Webhook secret key (string)
     * Enable webhook on your MyFatoorah account setting then paste the secret key here.
     * The webhook link is: https://{example.com}/myfatoorah/webhook
     */
    'webhook_secret_key' => '',
    /**
     * Register Apple Pay (boolean)
     * Set it to true to show the Apple Pay on the checkout page.
     * First, verify your domain with Apple Pay before you set it to true.
     * You can either follow the steps here: https://docs.myfatoorah.com/docs/apple-pay#verify-your-domain-with-apple-pay or contact the MyFatoorah support team (tech@myfatoorah.com).
    */
    'register_apple_pay' => false
];
