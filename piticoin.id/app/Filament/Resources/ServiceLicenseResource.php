<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceLicenseResource\Pages;
use App\Models\ServiceLicense;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ServiceLicenseResource extends Resource
{
    protected static ?string $model = ServiceLicense::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationGroup = 'Ekosistem Locomo';
    protected static ?string $navigationLabel = 'Pengawasan Lisensi Layanan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Lisensi & Kepemilikan')
                    ->schema([
                        Forms\Components\TextInput::make('license_code')
                            ->label('Kode Lisensi Resmi')
                            ->required()
                            ->readOnly(),
                        Forms\Components\Select::make('service_id')
                            ->label('Layanan Ekosistem')
                            ->relationship('service', 'name')
                            ->required(),
                        Forms\Components\Select::make('user_id')
                            ->label('Pemegang Lisensi (Penerima Manfaat)')
                            ->relationship('user', 'name')
                            ->required(),
                        Forms\Components\Select::make('registered_by_id')
                            ->label('Pendaftar / Agen Pembina')
                            ->relationship('registeredBy', 'name')
                            ->required(),
                        Forms\Components\Select::make('target_type')
                            ->label('Jenis Pendaftaran')
                            ->options([
                                'SELF' => 'Pribadi (Untuk Diri Sendiri)',
                                'OTHER' => 'Kemitraan (Untuk Orang Lain)',
                            ])
                            ->default('SELF')
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->label('Status Lisensi')
                            ->options([
                                'ACTIVE' => 'Aktif Terverifikasi',
                                'PENDING_PAYMENT' => 'Menunggu Pembayaran',
                                'EXPIRED' => 'Kedaluwarsa',
                                'SUSPENDED' => 'Dibekukan / Evaluasi',
                            ])
                            ->default('ACTIVE')
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Data Finansial & Transaksi')
                    ->schema([
                        Forms\Components\Select::make('payment_method')
                            ->label('Metode Pembayaran')
                            ->options([
                                'GOLD' => 'Koin Emas',
                                'FIAT' => 'Rupiah (QRIS / Transfer)',
                                'FREE' => 'Aktivasi Bebas Biaya',
                            ])
                            ->default('GOLD'),
                        Forms\Components\TextInput::make('amount_paid')
                            ->label('Nominal Terbayar')
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('currency')
                            ->label('Mata Uang')
                            ->default('GOLD'),
                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('Masa Berlaku Lisensi'),
                    ])->columns(2),

                Forms\Components\Section::make('Data Isian Wizard Pendaftaran (JSON Payload)')
                    ->description('Data detail unit usaha, kontak kasir, atau identitas siswa yang disubmit pengguna.')
                    ->schema([
                        Forms\Components\Textarea::make('beneficiary_data')
                            ->label('Data Isian Formulir')
                            ->rows(8)
                            ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : $state)
                            ->columnSpanFull()
                            ->readOnly(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('license_code')
                    ->label('Kode Lisensi')
                    ->searchable()
                    ->copyable()
                    ->badge()
                    ->color('warning'),
                Tables\Columns\TextColumn::make('service.name')
                    ->label('Layanan')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pemilik / Penerima')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('registeredBy.name')
                    ->label('Agen Pendaftar')
                    ->sortable(),
                Tables\Columns\TextColumn::make('target_type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'SELF' => 'info',
                        'OTHER' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ACTIVE' => 'success',
                        'PENDING_PAYMENT' => 'warning',
                        'EXPIRED', 'SUSPENDED' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('amount_paid')
                    ->label('Terbayar')
                    ->formatStateUsing(fn ($record) => $record->amount_paid . ' ' . $record->currency),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Terbit')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'ACTIVE' => 'Aktif',
                        'PENDING_PAYMENT' => 'Menunggu Pembayaran',
                        'SUSPENDED' => 'Dibekukan',
                    ]),
                Tables\Filters\SelectFilter::make('service_id')
                    ->label('Layanan')
                    ->relationship('service', 'name'),
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
            'index' => Pages\ListServiceLicenses::route('/'),
            'create' => Pages\CreateServiceLicense::route('/create'),
            'edit' => Pages\EditServiceLicense::route('/{record}/edit'),
        ];
    }
}
