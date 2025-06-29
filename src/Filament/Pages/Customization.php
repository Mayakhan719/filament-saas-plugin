<?php

namespace Maya\FilamentSaasPlugin\Filament\Pages;

use Maya\FilamentSaasPlugin\Models\Customization as ModelsCustomization;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;

class Customization extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationGroup = 'Settings';
    protected static string $view = 'filament.pages.customization';
    protected static ?string $navigationIcon = 'heroicon-o-paint-brush';
    protected static ?string $navigationLabel = 'Customization';
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(
            ModelsCustomization::firstOrCreate()->toArray()
        );
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('SEO')
                    ->description('Customize your site\'s SEO settings.')
                    ->schema([
                        TextInput::make('site_title')->label('Site Title'),
                        Textarea::make('meta_description')->label('Meta Description'),
                    ]),
                Forms\Components\Section::make('Appearance')
                    ->description('Customize your site\'s appearance.')
                    ->schema([
                        ColorPicker::make('primary_color')->label('Primary Color'),
                        ColorPicker::make('secondary_color')->label('Secondary Color'),
                        FileUpload::make('logo')
                            ->label('Logo')
                            ->image()
                            ->directory('settings')
                            ->imagePreviewHeight('500')
                            ->maxSize(5024),
                        FileUpload::make('favicon')
                            ->label('Favicon')
                            ->image()
                            ->directory('settings')
                            ->imagePreviewHeight('50')
                            ->maxSize(512),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Costomization')
                ->submit('save'),
        ];
    }
    protected function getCachedFormActions(): array
    {
        return $this->getFormActions();
    }
    protected function getCachedFormSchema(): array
    {
        return $this->form->getCachedSchema();
    }
    public function hasFullWidthFormActions(): bool
    {
        return false;
    }
    public function save(): void
    {
        try {
            $theme = ModelsCustomization::firstOrCreate();
            $theme->update($this->form->getState());

            Notification::make()
                ->title('Profile Updated')
                ->body('Your profile has been successfully updated.')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('There was an error updating your profile. Please try again.')
                ->danger()
                ->send();
        }
    }
}
