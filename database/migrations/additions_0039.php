<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\File;

return new class extends Migration
{
    public function up()
    {
        $supervisorConfPath = '/etc/supervisor/conf.d/upload-recording-segments.conf';

        if (!file_exists($supervisorConfPath)) {
            $configContent = <<<EOL
[program:upload-recording-segments]
process_name=%(program_name)s
directory=/var/www/taggy/current
command=php /var/www/taggy/current/artisan taggy:upload-recording-segments
autostart=true
autorestart=true
user=taggy
redirect_stderr=true
stdout_logfile=/var/www/taggy/current/storage/logs/upload-recording-segments.log
stopwaitsecs=3600
EOL;

            File::put($supervisorConfPath, $configContent);

            exec('sudo supervisorctl reread && sudo supervisorctl update');
        }
    }
};
