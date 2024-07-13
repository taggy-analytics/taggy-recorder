<?php

use App\Support\Traits\BashMigration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use BashMigration;

    public function up()
    {
        $this->removeFile('/etc/supervisor/conf.d/soketi.conf');

        $this->writeFileIfNotExists('/etc/supervisor/conf.d/reverb.conf', '[program:reverb]
directory=/var/www/taggy/current
command=php /var/www/taggy/current/artisan reverb:start --no-interaction
process_name=%(program_name)s
autostart=true
autorestart=true
user=taggy
numprocs=1
startsecs=1
redirect_stderr=true
stdout_logfile=/var/www/taggy/current/storage/logs/reverb.log
stdout_logfile_maxbytes=5MB
stdout_logfile_backups=3
stopwaitsecs=10
stopsignal=SIGTERM
stopasgroup=true
killasgroup=true');

        $this->updateSupervisor();

        $this->createOrUpdateDotEnvValue('HLS_SEGMENT_DURATION', 3);

        $this->createOrUpdateDotEnvValue('REVERB_APP_ID', 'taggy');
        $this->createOrUpdateDotEnvValue('REVERB_APP_KEY', '6aCJdAR1Pw5et49U3Nil');
        $this->createOrUpdateDotEnvValue('REVERB_APP_SECRET', '2IvVV6S9044pELJzwsyy');
        $this->createOrUpdateDotEnvValue('REVERB_SERVER_PORT', 6001);
    }
};
