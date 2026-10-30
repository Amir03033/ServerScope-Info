<?php

namespace App\Filament\Resources\PageMeta;

use App\Filament\Concerns\AdminOnly;
use App\Filament\Locales;
use App\Filament\Resources\PageMeta\Pages\ManagePageMeta;
use App\Models\PageMeta;
use App\Support\Seo;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * Title and meta description of the fixed pages, with a length indicator.
 */
class PageMetaResource extends Resource
{
    use AdminOnly;

    protected static ?string $model = PageMeta::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMagnifyingGlass;

    protected static ?string $modelLabel = 'pagina';

    protected static ?string $pluralModelLabel = 'SEO van vaste pagina\'s';

    protected static ?string $navigationLabel = 'SEO';

    protected static string|\UnitEnum|null $navigationGroup = 'Inhoud';

    protected static ?int $navigationSort = 6;

    protected static ?string $slug = 'seo';

    public static function titleField(string $name = 'title'): TextInput
    {
        return TextInput::make($name)->label('Title')->maxLength(200)->live(debounce: 300)
            ->hint(fn (?string $state) => Seo::lengthHint($state, Seo::TITLE))
            ->hintColor(fn (?string $state) => Seo::lengthColor($state, Seo::TITLE));
    }

    public static function descriptionField(string $name = 'description'): Textarea
    {
        return Textarea::make($name)->label('Meta description')->rows(3)->maxLength(300)->live(debounce: 300)
            ->hint(fn (?string $state) => Seo::lengthHint($state, Seo::DESCRIPTION))
            ->hintColor(fn (?string $state) => Seo::lengthColor($state, Seo::DESCRIPTION));
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Placeholder::make('defaults')->label('Standaard (als de velden leeg zijn)')
                ->content(fn (PageMeta $record) => trans(PageMeta::PAGES[$record->page][2], [], $record->locale).' — '.trans(PageMeta::PAGES[$record->page][3], [], $record->locale)),
            self::titleField(),
            self::descriptionField(),
            Toggle::make('noindex')->label('Niet laten indexeren (noindex)'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('page')
            ->columns([
                TextColumn::make('page')->label('Pagina')->formatStateUsing(fn (string $state) => PageMeta::PAGES[$state][0] ?? $state)
                    ->description(fn (PageMeta $r) => '/'.$r->locale.(PageMeta::PAGES[$r->page][1] ?? '')),
                TextColumn::make('locale')->label('Taal')->badge()->formatStateUsing(fn (string $state) => strtoupper($state)),
                TextColumn::make('effective_title')->label('Title')->state(fn (PageMeta $r) => Seo::title($r->page, $r->locale))->wrap()
                    ->description(fn (PageMeta $r) => Seo::lengthHint(Seo::title($r->page, $r->locale), Seo::TITLE).(blank($r->title) ? ' · standaard' : '')),
                TextColumn::make('effective_description')->label('Description')->state(fn (PageMeta $r) => Seo::description($r->page, $r->locale))->wrap()->limit(90)
                    ->description(fn (PageMeta $r) => Seo::lengthHint(Seo::description($r->page, $r->locale), Seo::DESCRIPTION).(blank($r->description) ? ' · standaard' : '')),
                IconColumn::make('noindex')->label('Noindex')->boolean(),
            ])
            ->filters([SelectFilter::make('locale')->label('Taal')->options(Locales::OPTIONS)])
            ->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManagePageMeta::route('/')];
    }
}
