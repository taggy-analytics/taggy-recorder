<?php

namespace App\Console\Commands;

use App\Enums\WebsocketEventType;
use Illuminate\Console\Command;

class HandleMothershipWebsocketsEvent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'taggy:handle-mothership-websockets-event {eventType} {entityId} {--data=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Handle websocket events from the mothership';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dataJson = $this->option('data');
        $data = json_decode($dataJson, true);

        app(\App\Actions\HandleMothershipWebsocketsEvent::class)
            ->execute(
                WebsocketEventType::from($this->argument('eventType')),
                $this->argument('entityId'),
                $data
            );
    }
}
