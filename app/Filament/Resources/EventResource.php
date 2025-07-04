<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Models\Event;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $navigationGroup = 'Event';

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Event Details')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', Str::slug($state))),
                                Forms\Components\TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),
                                Forms\Components\TextInput::make('short_name')
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Forms\Set $set, ?string $state, string $operation) {
                                        if ($operation === 'create') {
                                            $set('slug', static::generateSlug($state));
                                        }
                                    }),
                                Forms\Components\TextInput::make('tagline')
                                    ->maxLength(255),
                                Forms\Components\DateTimePicker::make('starts_at'),
                                Forms\Components\DateTimePicker::make('ends_at'),
                                Forms\Components\Select::make('timezone')
                                    ->options(timezone_identifiers_list())
                                    ->searchable(),
                                Forms\Components\TextInput::make('location')
                                    ->maxLength(255),
                                Select::make('country')
                                    ->options([
                                        'CH' => 'Switzerland',
                                        'USA' => 'United States',
                                        'DE' => 'Germany',
                                    ])
                                    ->required(),
                            ]),
                        Forms\Components\Section::make('Content')
                            ->schema([
                                Forms\Components\Textarea::make('about')
                                    ->columnSpanFull(),
                                Forms\Components\Textarea::make('afterforum_about')
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('networking_link')
                                    ->url()
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('networking_text')
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('live_link')
                                    ->url()
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('bottom_text')
                                    ->columnSpanFull(),
                                Forms\Components\Textarea::make('how_to_participate')
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('enquiries')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('past_event_url')
                                    ->url()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('pdf_agenda')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('reception')
                                    ->maxLength(255),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Status')
                            ->schema([
                                Forms\Components\Toggle::make('show_event')
                                    ->required(),
                                Forms\Components\Toggle::make('after_event')
                                    ->required(),
                            ]),
                        Forms\Components\Section::make('Visibility')
                            ->schema([
                                Forms\Components\Toggle::make('show_live')->required(),
                                Forms\Components\Toggle::make('show_register')->required(),
                                Forms\Components\Toggle::make('show_supporters')->required(),
                                Forms\Components\Toggle::make('show_presenters')->required(),
                                Forms\Components\Toggle::make('show_exhibitors')->required(),
                                Forms\Components\Toggle::make('show_agenda')->required(),
                                Forms\Components\Toggle::make('show_sponsors')->required(),
                                Forms\Components\Toggle::make('show_panels')->required(),
                                Forms\Components\Toggle::make('show_enquiries')->required(),
                                Forms\Components\Toggle::make('show_participate')->required(),
                                Forms\Components\Toggle::make('show_attendees_tab')->required(),
                                Forms\Components\Toggle::make('show_investors_tab')->required(),
                                Forms\Components\Toggle::make('show_speakers_tab')->required(),
                                Forms\Components\Toggle::make('show_speakers_section')->required(),
                                Forms\Components\Toggle::make('show_presenters_section')->required(),
                                Forms\Components\Toggle::make('show_risings_tab')->required(),
                                Forms\Components\Toggle::make('show_faq_tab')->required(),
                                Forms\Components\Toggle::make('show_risings_about')->required(),
                                Forms\Components\Toggle::make('show_right')->required(),
                                Forms\Components\Toggle::make('show_venue')->required(),
                                Forms\Components\Toggle::make('show_photos')->required(),
                                Forms\Components\Toggle::make('show_floor_plan')->required(),
                                Forms\Components\Toggle::make('show_recordings')->required(),
                                Forms\Components\Toggle::make('is24h')->required(),
                                Forms\Components\Toggle::make('in_person_meetings')->required(),
                                Forms\Components\Toggle::make('online_virtual_meetings')->required(),
                            ])->columns(2),
                        Forms\Components\Section::make('Advanced')
                            ->schema([
                                Forms\Components\TextInput::make('jotform')
                                    ->maxLength(255),
                                Forms\Components\Toggle::make('afterforum_gif_option')
                                    ->required(),
                                Forms\Components\TextInput::make('afterforum_gif_location')
                                    ->maxLength(255),
                                Forms\Components\DateTimePicker::make('attendees_updated'),
                                Forms\Components\TextInput::make('airtable_base')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('airtable_name')
                                    ->maxLength(255),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('starts_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ends_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\IconColumn::make('show_event')
                    ->boolean(),
                TextColumn::make('slug')
                    ->label('Event Link')
                    ->html()
                    ->getStateUsing(fn ($record) => '<a href="https://sachsevent.com/event/'.$record->slug.'/about"
                                      target="_blank"
                                      rel="noopener noreferrer">
                                        View Event
                                    </a>'
                    ),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Filter::make('show_event')
                    ->query(fn (Builder $query) => $query->where('show_event', true))
                    ->label('Only Shown Events'),
                SelectFilter::make('country')
                    ->options([
                        'DE' => 'Germany',
                        'USA' => 'United States',
                        'CH' => 'Switzerland',
                    ])
                    ->multiple()
                    ->label('Country'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\EventResource\RelationManagers\PresentersRelationManager::class,
            \App\Filament\Resources\EventResource\RelationManagers\SponsorsRelationManager::class,
            \App\Filament\Resources\EventResource\RelationManagers\PanelsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
        ];
    }

    protected static function generateSlug(string $text): string
    {
        $replacedSymbols = [
            'ą' => 'a', 'č' => 'c', 'ę' => 'e', 'ė' => 'e', 'į' => 'i', 'š' => 's',
            'ų' => 'u', 'ū' => 'u', 'ž' => 'z',
        ];

        $stringedText = strtr($text, $replacedSymbols);
        $gapsReplaced = preg_replace('~[^\\pL\d.]+~u', '-', $stringedText);
        $trimmedText = trim($gapsReplaced, '-');
        $preggedText = preg_replace('~[^-\w.]+~', '', $trimmedText);

        return strtolower($preggedText);
    }
}
