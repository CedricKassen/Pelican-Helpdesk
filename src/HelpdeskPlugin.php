<?php

namespace CedricKassen\Helpdesk;

use App\Contracts\Plugins\HasPluginSettings;
use App\Traits\EnvironmentWriterTrait;
use CedricKassen\Helpdesk\Models\HelpdeskSetting;
use CedricKassen\Helpdesk\Support\TicketOptions;
use Filament\Contracts\Plugin;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Panel;
use Filament\Schemas\Components\Section;

class HelpdeskPlugin implements HasPluginSettings, Plugin
{
    use EnvironmentWriterTrait;

    public function getId(): string
    {
        return 'helpdesk';
    }

    public function register(Panel $panel): void
    {
        $id = str($panel->getId())->title();

        $panel->discoverResources(
            plugin_path($this->getId(), "src/Filament/$id/Resources"),
            "CedricKassen\\Helpdesk\\Filament\\$id\\Resources",
        );
    }

    public function boot(Panel $panel): void {}

    public function getSettingsFormData(): array
    {
        return array_merge(config('helpdesk'), [
            'categories' => TicketOptions::categories(),
            'priorities' => TicketOptions::priorities(),
        ]);
    }

    public function getSettingsForm(): array
    {
        return [
            Section::make(trans('helpdesk::tickets.settings.email_section'))
                ->schema([
                    Toggle::make('email_enabled')
                        ->label(trans('helpdesk::tickets.settings.email_enabled'))
                        ->helperText(trans('helpdesk::tickets.settings.email_enabled_help'))
                        ->default(fn () => config('helpdesk.email_enabled')),
                ]),
            Section::make(trans('helpdesk::tickets.settings.webhook_section'))
                ->schema([
                    TextInput::make('webhook_url')
                        ->label(trans('helpdesk::tickets.settings.webhook_url'))
                        ->helperText(trans('helpdesk::tickets.settings.webhook_url_help'))
                        ->url()
                        ->maxLength(255)
                        ->default(fn () => config('helpdesk.webhook_url')),
                    Select::make('webhook_type')
                        ->label(trans('helpdesk::tickets.settings.webhook_type'))
                        ->helperText(trans('helpdesk::tickets.settings.webhook_type_help'))
                        ->options([
                            'discord' => trans('helpdesk::tickets.settings.webhook_type_discord'),
                            'generic' => trans('helpdesk::tickets.settings.webhook_type_generic'),
                        ])
                        ->placeholder(trans('helpdesk::tickets.settings.webhook_type_auto'))
                        ->default(fn () => config('helpdesk.webhook_type')),
                ]),
            Section::make(trans('helpdesk::tickets.settings.categories_section'))
                ->description(trans('helpdesk::tickets.settings.categories_help'))
                ->schema([
                    $this->optionRepeater('categories', trans('helpdesk::tickets.settings.add_category')),
                ]),
            Section::make(trans('helpdesk::tickets.settings.priorities_section'))
                ->description(trans('helpdesk::tickets.settings.priorities_help'))
                ->schema([
                    $this->optionRepeater('priorities', trans('helpdesk::tickets.settings.add_priority')),
                ]),
        ];
    }

    public function saveSettings(array $data): void
    {
        $this->writeToEnvironment([
            'HELPDESK_EMAIL_ENABLED' => $data['email_enabled'] ? 'true' : 'false',
            'HELPDESK_WEBHOOK_URL' => $data['webhook_url'] ?? '',
            'HELPDESK_WEBHOOK_TYPE' => $data['webhook_type'] ?? '',
        ]);

        HelpdeskSetting::set(TicketOptions::SETTING_CATEGORIES, TicketOptions::normalize($data['categories'] ?? []));
        HelpdeskSetting::set(TicketOptions::SETTING_PRIORITIES, TicketOptions::normalize($data['priorities'] ?? []));
        TicketOptions::flushCache();

        Notification::make()
            ->title(trans('admin/setting.save_success'))
            ->success()
            ->send();
    }

    protected function optionRepeater(string $name, string $addLabel): Repeater
    {
        return Repeater::make($name)
            ->hiddenLabel()
            ->addActionLabel($addLabel)
            ->reorderable()
            ->columns(2)
            ->schema([
                TextInput::make('label')
                    ->label(trans('helpdesk::tickets.settings.option_label'))
                    ->required(),
                Select::make('color')
                    ->label(trans('helpdesk::tickets.settings.option_color'))
                    ->selectablePlaceholder(false)
                    ->default('gray')
                    ->options([
                        'primary' => trans('helpdesk::tickets.settings.colors.primary'),
                        'success' => trans('helpdesk::tickets.settings.colors.success'),
                        'warning' => trans('helpdesk::tickets.settings.colors.warning'),
                        'danger' => trans('helpdesk::tickets.settings.colors.danger'),
                        'info' => trans('helpdesk::tickets.settings.colors.info'),
                        'gray' => trans('helpdesk::tickets.settings.colors.gray'),
                    ]),
                Hidden::make('value'),
            ]);
    }
}
