<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'github' => [
        'token' => env('GITHUB_TOKEN'),
        'repo' => env('GITHUB_REPO', 'syel21a5/sisdp'),
    ],

    'ai' => [
        'default' => env('AI_PROVIDER', 'deepseek'),
        
        'providers' => [
            'deepseek' => [
                'api_key' => env('DEEPSEEK_API_KEY'),
                'base_url' => 'https://api.deepseek.com/chat/completions',
                'model' => 'deepseek-chat',
            ],
            'sambanova' => [
                'api_key' => env('SAMBANOVA_API_KEY'),
                'base_url' => 'https://api.sambanova.ai/v1/chat/completions',
                'model' => 'Meta-Llama-3.1-70B-Instruct',
            ],
            'gemini' => [
                'api_key' => env('GEMINI_API_KEY'),
                'base_url' => 'https://generativelanguage.googleapis.com/v1beta/openai/chat/completions',
                'model' => env('GEMINI_MODEL', 'gemini-3.5-flash'),
            ],
        ]
    ],

];
