<?php

namespace App\Filament\Pages;

use App\Models\HomepageSetting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageHomepage extends Page
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-home-modern';

    protected string $view = 'filament.pages.manage-homepage';

    public ?array $data = [];

    public function mount(): void
    {
        $setting = HomepageSetting::getActive();
        $this->form->fill($setting?->data ?? []);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Hero Section')
                    ->schema([
                        TextInput::make('hero.badgeText')->label('Badge Text'),
                        TextInput::make('hero.titlePrefix')->label('Title Prefix'),
                        TextInput::make('hero.titleHighlight')->label('Title Highlight'),
                        TextInput::make('hero.titleSuffix')->label('Title Suffix'),
                        Textarea::make('hero.description')->label('Description'),
                        TextInput::make('hero.imageUrl')->label('Hero Image URL'),
                    ])->columns(2),
                Section::make('Statistics')
                    ->schema([
                        Repeater::make('stats')
                            ->schema([
                                TextInput::make('id')->required(),
                                TextInput::make('label')->required(),
                                TextInput::make('displayValue')->required(),
                            ])
                            ->defaultItems(4)
                            ->columns(3),
                    ]),
                Section::make('Why Choose')
                    ->schema([
                        TextInput::make('whyChoose.title')->label('Title'),
                        Textarea::make('whyChoose.description')->label('Description'),
                        Repeater::make('whyChoose.features')
                            ->schema([
                                TextInput::make('icon'),
                                TextInput::make('title')->required(),
                                Textarea::make('description'),
                            ])
                            ->columns(3),
                        Repeater::make('whyChoose.bullets')
                            ->schema([
                                TextInput::make('value')->label('Bullet Point'),
                            ]),
                    ]),
                Section::make('Testimonials')
                    ->schema([
                        Repeater::make('testimonials')
                            ->schema([
                                TextInput::make('name')->required(),
                                TextInput::make('degree'),
                                Textarea::make('text')->required(),
                                TextInput::make('image')->label('Image URL'),
                            ])
                            ->columns(2),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Settings')
                ->action('save'),
        ];
    }

    public function save(): void
    {
        $setting = HomepageSetting::getActive();
        if (!$setting) {
            $setting = HomepageSetting::create([
                'key' => 'default',
                'is_active' => true,
                'data' => $this->form->getState(),
            ]);
        } else {
            $setting->update(['data' => $this->form->getState()]);
        }

        Notification::make()
            ->title('Settings saved successfully!')
            ->success()
            ->send();
    }

    public static function getNavigationLabel(): string
    {
        return 'Homepage Settings';
    }
}
