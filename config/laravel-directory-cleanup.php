<?php

return [

    'directories' => [

        /*
         * Here you can specify which directories need to be cleanup. All files older than
         * the specified amount of minutes will be deleted.
         */

        storage_path('app/scenes') => [
            'deleteAllOlderThanMinutes' => 120,
        ],

        storage_path('app/segments-m3u8') => [
            'deleteAllOlderThanMinutes' => 60 * 6,
        ],
    ],

    /*
     * If a file is older than the amount of minutes specified, a cleanup policy will decide if that file
     * should be deleted. By default every file that is older than the specified amount of minutes
     * will be deleted.
     *
     * You can customize this behaviour by writing your own clean up policy. A valid policy
     * is any class that implements `Spatie\DirectoryCleanup\Policies\CleanupPolicy`.
     */
    'cleanup_policy' => \Spatie\DirectoryCleanup\Policies\DeleteEverything::class,
];
