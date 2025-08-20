<?php

return [
    'initial-setup' => [
        'recovery-password' => [
            'title' => 'Welcome!',
            'intro' => '<div>This is your recovery password. Think of it as your spare key: if you ever forget your normal password, this one will help you get back in.</div><div>Please write it down or keep it somewhere safe. If it gets lost, the only way to unlock your Taggy Box will be to reset it.</div><div>Don’t worry — just keep this key safe and you’re all set!</div>',
            'copy' => 'Copy',
            'copied' => 'Copied!',
            'continue' => 'Continue',
        ],

        'setup-user' => [
            'title' => 'Let’s set up your admin user',
            'intro' => '<div>This account will be your main key to the Taggy Box. With it, you’ll be able to log in, manage settings, and invite others later on.</div><div>Choose a name, email, and password you’ll remember. Don’t worry — you can always add more users afterward.</div>',
            'fields' => [
                'name' => 'Name',
                'email' => 'Email',
                'password' => 'Password',
            ],
            'submit' => 'Setup user',
        ],
    ],
    'settings' => [
        'heading' => 'Settings',
        'software-update' => [
            'current-version' => 'Current version',
            'software-is-current' => 'The software version is current.',
            'new-software-available' => 'New software is available: <strong>:version</strong>',
            'are-you-sure' => 'Are you sure you want to update the software now?',
            'update-was-started' => 'Update to version :version was started in the background. Please don\'t power off the Taggy box.',
            'update-is-running' => 'Update is running.',

        ],
    ],
];
