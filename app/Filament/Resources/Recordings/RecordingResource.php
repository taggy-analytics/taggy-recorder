<?php

namespace App\Filament\Resources\Recordings;

use App\Enums\RecordingStatus;
use App\Enums\VideoFormat;
use App\Filament\Resources\Recordings\Pages\ManageRecordings;
use App\Models\Recording;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RecordingResource extends Resource
{
    protected static ?string $model = Recording::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('camera_id')
                    ->required()
                    ->numeric(),
                TextInput::make('name')
                    ->required(),
                Select::make('status')
                    ->options(RecordingStatus::class)
                    ->default('created')
                    ->required(),
                TextInput::make('key')
                    ->required(),
                Toggle::make('livestream_enabled')
                    ->required(),
                DateTimePicker::make('started_at'),
                Select::make('video_format')
                    ->options(VideoFormat::class),
                TextInput::make('width')
                    ->numeric(),
                TextInput::make('height')
                    ->numeric(),
                TextInput::make('rotation')
                    ->numeric(),
                DateTimePicker::make('aborted_at'),
                TextInput::make('restart_recording_id')
                    ->numeric(),
                DateTimePicker::make('stopped_at'),
                TextInput::make('data'),
                TextInput::make('uuid')
                    ->label('UUID')
                    ->required(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('camera_id')
                    ->numeric(),
                TextEntry::make('name'),
                TextEntry::make('status'),
                TextEntry::make('key'),
                IconEntry::make('livestream_enabled')
                    ->boolean(),
                TextEntry::make('started_at')
                    ->dateTime(),
                TextEntry::make('video_format'),
                TextEntry::make('width')
                    ->numeric(),
                TextEntry::make('height')
                    ->numeric(),
                TextEntry::make('rotation')
                    ->numeric(),
                TextEntry::make('aborted_at')
                    ->dateTime(),
                TextEntry::make('restart_recording_id')
                    ->numeric(),
                TextEntry::make('stopped_at')
                    ->dateTime(),
                TextEntry::make('uuid')
                    ->label('UUID'),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('camera_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('key')
                    ->searchable(),
                IconColumn::make('livestream_enabled')
                    ->boolean(),
                TextColumn::make('started_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('video_format')
                    ->searchable(),
                TextColumn::make('width')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('height')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('rotation')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('aborted_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('restart_recording_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('stopped_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('uuid')
                    ->label('UUID')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageRecordings::route('/'),
        ];
    }
}
