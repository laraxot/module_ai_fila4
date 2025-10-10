<?php

declare(strict_types=1);

namespace Modules\AI\Filament\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Modules\Xot\Filament\Pages\XotBasePage;
use Webmozart\Assert\Assert;

use function Safe\file_get_contents;

class FineTuning extends XotBasePage
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog';

    protected string $view = 'ai::filament.pages.fine-tuning';

    public string $learning_rate = '0.001';

    public int $batch_size = 32;

    public int $epochs = 10;

    public string $dataset = 'dataset1';

    /** @var TemporaryUploadedFile */
    public $dataset_file;

    // Metodo rimosso: safeTranslate() non utilizzato

    /**
     * Schema del form.
     */
    protected function getFormSchema(): array
    {
        return [
            TextInput::make('learning_rate')
                ->label('Learning Rate')
                ->required()
                ->numeric()
                ->minValue(0)
                ->helperText('Set the learning rate for fine-tuning'),

            TextInput::make('batch_size')
                ->label('Batch Size')
                ->required()
                ->numeric()
                ->minValue(1)
                ->helperText('Number of samples per batch'),

            TextInput::make('epochs')
                ->label('Epochs')
                ->required()
                ->numeric()
                ->minValue(1)
                ->helperText('Number of training epochs'),

            Select::make('dataset')
                ->label('Dataset')
                ->options([
                    'dataset1' => 'Dataset 1',
                    'dataset2' => 'Dataset 2',
                ])
                ->required(),
            FileUpload::make('dataset_file')
                ->label('Dataset File')
                ->required()
                ->helperText('Upload the dataset file for training'),
        ];
    }

    /**
     * Avvia il processo di fine-tuning.
     */
    public function startFineTuning(): void
    {
        $data = [
            'learning_rate' => (float) $this->learning_rate,
            'batch_size' => (int) $this->batch_size,
            'epochs' => (int) $this->epochs,
            'dataset' => $this->dataset,
        ];

        if ($this->dataset_file) {
            $data['dataset_file'] = $this->dataset_file->getRealPath(); // Percorso del file caricato
        }

        Assert::string($apiEndpoint = Config::get('ai.backend_api.fine_tuning_url'));

        $response = $this->sendFineTuningRequest($data, $apiEndpoint);

        if ($response->successful()) {
            Notification::make()
                ->title('Success')
                ->body('Fine-tuning started successfully')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Error')
                ->body('Fine-tuning failed to start')
                ->danger()
                ->send();
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function sendFineTuningRequest(array $data, string $endpoint): Response
    {
        Assert::string($dataset_file = $data['dataset_file']);
        Assert::string($content = file_get_contents($dataset_file));

        return Http::attach('dataset_file', $content, basename($dataset_file))
            ->post($endpoint, $data);
    }

    /**
     * Restituisce le azioni del form, come il pulsante per avviare il fine-tuning.
     *
     * @return array<int, \Filament\Actions\Action>
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('submit')
                ->label('Start Fine-tuning')
                ->action('startFineTuning')
                ->color('primary'),
        ];
    }
}
