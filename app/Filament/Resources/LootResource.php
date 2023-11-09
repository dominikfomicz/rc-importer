<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LootResource\Pages;
use App\Models\Loot;
use Filament\Tables\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LootResource extends Resource
{
    protected static ?string $model = Loot::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('player'),
                DatePicker::make('date'),
                TextInput::make('time'),
                TextInput::make('item'),
                TextInput::make('response'),
                TextInput::make('votes'),
                TextInput::make('class'),
                TextInput::make('instance'),
                TextInput::make('boss'),
                TextInput::make('note'),
                TextInput::make('owner'),
            ]);
    }

    /**
     * @throws \Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('player')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('date')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('item')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('response')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('instance')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('votes')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('note')->sortable()->searchable(),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('date_from'),
                        Forms\Components\DatePicker::make('date_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['date_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    }),
                SelectFilter::make('player')
                    ->options(fn (): array => Loot::query()->pluck('player', 'player')->all())
                    ->searchable(),
                SelectFilter::make('instance')
                    ->options(fn (): array => Loot::query()->pluck('instance', 'instance')->all())
                    ->searchable(),
            ], FiltersLayout::Modal)
            ->filtersTriggerAction(
                fn (Action $action) => $action
                    ->button()
                    ->label('Filters')
                    ->slideOver()
            )
            ->groups([
                Group::make('date')
                    ->collapsible(),
            ])
            ->actions([
                    Tables\Actions\ViewAction::make()
            ]);
    }
    
    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLoots::route('/'),
            'create' => Pages\CreateLoot::route('/create'),
        ];
    }    
}
