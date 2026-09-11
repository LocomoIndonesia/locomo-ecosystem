<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EcosystemServiceResource\Pages;
use App\Models\EcosystemService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EcosystemServiceResource extends Resource
{
    protected static ?string $model = EcosystemService::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-plus';
    protected static ?string $navigationGroup = 'Ekosistem Locomo';
    protected static ?string $navigationLabel = 'Katalog Layanan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identitas Layanan Ekosistem')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Layanan')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('code')
                            ->label('Slug / Kode Unik')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),
                        Forms\Components\Select::make('category')
                            ->label('Kategori')
                            ->options([
                                'BISNIS' => 'Bisnis & UMKM',
                                'EDUKASI' => 'Edukasi & Bimbel',
                                'KOMUNITAS' => 'Komunitas & Olahraga',
                                'DIGITAL' => 'Produk Digital',
                            ])
                            ->default('BISNIS')
                            ->required(),
                        Forms\Components\TextInput::make('icon')
                            ->label('Ikon (Material Symbol)')
                            ->default('storefront')
                            ->helperText('Contoh: storefront, school, sports_martial_arts, store, local_shipping'),
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi Singkat')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Tarif Aktivasi & Integrasi URL')
                    ->schema([
                        Forms\Components\TextInput::make('price_gold')
                            ->label('Tarif Koin Emas')
                            ->numeric()
                            ->default(0)
                            ->suffix('Koin Emas')
                            ->helperText('Pembayaran internal via saldo wallet pengguna'),
                        Forms\Components\TextInput::make('price_fiat')
                            ->label('Tarif Rupiah (Normal)')
                            ->numeric()
                            ->default(0)
                            ->prefix('Rp')
                            ->helperText('Pembayaran via QRIS / Transfer Bank'),
                        Forms\Components\TextInput::make('service_url')
                            ->label('URL Aplikasi Layanan (Redirect Target)')
                            ->placeholder('https://unimart.id')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('webhook_url')
                            ->label('Webhook Notifikasi API')
                            ->placeholder('https://unimart.id/api/webhook/license')
                            ->maxLength(255),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Tampilkan di Dashboard Pengguna')
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make('Konfigurasi Step Wizard Pendaftaran (JSON Schema)')
                    ->description('Admin dapat mengatur alur langkah, judul step, dan input field pendaftaran secara dinamis tanpa mengubah kodingan.')
                    ->schema([
                        Forms\Components\Textarea::make('wizard_schema')
                            ->label('JSON Wizard Schema')
                            ->rows(15)
                            ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : $state)
                            ->dehydrateStateUsing(fn ($state) => is_string($state) ? json_decode($state, true) : $state)
                            ->columnSpanFull()
                            ->helperText('Format: { "steps": [ { "id": "step_id", "title": "Judul", "fields": [ { "name": "field_name", "label": "Label", "type": "text|select|textarea", "required": true } ] } ] }'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('icon')
                    ->label('Ikon')
                    ->badge(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Layanan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode')
                    ->badge()
                    ->color('warning'),
                Tables\Columns\TextColumn::make('category')
                    ->label('Kategori')
                    ->badge(),
                Tables\Columns\TextColumn::make('price_gold')
                    ->label('Tarif Emas')
                    ->suffix(' Emas')
                    ->sortable(),
                Tables\Columns\TextColumn::make('price_fiat')
                    ->label('Tarif Rupiah')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'BISNIS' => 'Bisnis',
                        'EDUKASI' => 'Edukasi',
                        'KOMUNITAS' => 'Komunitas',
                    ]),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEcosystemServices::route('/'),
            'create' => Pages\CreateEcosystemService::route('/create'),
            'edit' => Pages\EditEcosystemService::route('/{record}/edit'),
        ];
    }
}
