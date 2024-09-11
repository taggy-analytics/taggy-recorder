<?php

namespace App\Console\Commands;

use App\Enums\WebsocketEventType;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Spatie\LaravelIgnition\Facades\Flare;

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
        Flare::context('commandData', $this->option('data'));

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
