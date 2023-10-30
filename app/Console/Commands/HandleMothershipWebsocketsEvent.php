<?php

namespace App\Console\Commands;

use App\Enums\WebsocketEventType;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

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
        $dataJson = base64_decode($this->option('data'));
        $data = json_decode(trim($dataJson), true);

        Log::channel('websocket')
            ->info(json_encode([
                'eventType' => $this->argument('eventType'),
                'entityId' => (int) $this->argument('entityId'),
                'data' => $data,
            ], JSON_PRETTY_PRINT));

        app(\App\Actions\HandleMothershipWebsocketsEvent::class)
            ->execute(
                WebsocketEventType::from($this->argument('eventType')),
                $this->argument('entityId'),
                $data
            );
    }
}
