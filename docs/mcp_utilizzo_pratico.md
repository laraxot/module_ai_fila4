# Utilizzo Pratico dei Server MCP in Progetti Windsurf/Xot

## Indice
1. [Introduzione](#introduzione)
2. [Implementazione in Moduli Laravel](#implementazione-in-moduli-laravel)
3. [Esempi di Strumenti MCP](#esempi-di-strumenti-mcp)
4. [Integrazione con Filament](#integrazione-con-filament)
5. [Casi d'Uso Avanzati](#casi-duso-avanzati)
6. [Best Practices](#best-practices)
7. [Sicurezza e Autorizzazioni](#sicurezza-e-autorizzazioni)

## Introduzione

Questo documento fornisce esempi pratici e linee guida per l'utilizzo dei server MCP nei progetti Windsurf/Xot, seguendo le regole di progetto e le convenzioni di namespace.

## Implementazione in Moduli Laravel

### Struttura delle Cartelle

Quando implementi l'integrazione MCP in un modulo Laravel, segui questa struttura:

```
/Modules/NomeModulo/
├── app/
│   ├── MCP/
│   │   ├── Tools/
│   │   │   ├── CustomTool.php
│   │   │   └── ...
│   │   └── Providers/
│   │       └── McpServiceProvider.php
│   ├── Console/
│   │   └── Commands/
│   │       └── ServeMcpCommand.php
│   └── Providers/
│       └── NomeModuloServiceProvider.php
└── config/
    └── mcp.php
```

### Service Provider

Registra i tuoi strumenti MCP nel service provider del modulo:

```php
<?php

declare(strict_types=1);

namespace Modules\NomeModulo\Providers;

use Modules\Xot\Providers\XotBaseServiceProvider;
use Modules\NomeModulo\App\Console\Commands\ServeMcpCommand;

class NomeModuloServiceProvider extends XotBaseServiceProvider
{
    public string $name = 'NomeModulo';
    protected string $module_dir = __DIR__;
    protected string $module_ns = __NAMESPACE__;
    
    public function boot(): void
    {
        parent::boot();
        
        if ($this->app->runningInConsole()) {
            $this->commands([
                ServeMcpCommand::class,
            ]);
        }
    }
    
    public function register(): void
    {
        parent::register();
        
        $this->mergeConfigFrom(
            module_path($this->name, 'config/mcp.php'), 'modules.nomemodulo.mcp'
        );
    }
}
```

### Comando Artisan

Crea un comando Artisan per servire il server MCP:

```php
<?php

declare(strict_types=1);

namespace Modules\NomeModulo\App\Console\Commands;

use Illuminate\Console\Command;
use InnoGE\LaravelMcp\Commands\ServesMcpServer;
use Modules\NomeModulo\App\MCP\Tools\CustomTool;

class ServeMcpCommand extends Command
{
    use ServesMcpServer;

    protected $signature = 'nomemodulo:mcp:serve';
    protected $description = 'Avvia un server MCP per il modulo';

    public function handle(): int
    {
        return $this->serveMcp('windsurf-nomemodulo-assistant', '1.0.0');
    }

    private function getTools(): array
    {
        return [
            CustomTool::class,
        ];
    }

    private function getResources(): array
    {
        return [
            // Definisci qui le risorse esposte dal server MCP
        ];
    }
}
```

## Esempi di Strumenti MCP

### Strumento di Base

Ecco un esempio di strumento MCP di base che segue le convenzioni di Windsurf/Xot:

```php
<?php

declare(strict_types=1);

namespace Modules\NomeModulo\App\MCP\Tools;

use InnoGE\LaravelMcp\Tools\Tool;

class CustomTool implements Tool
{
    public function getName(): string
    {
        return 'custom-tool';
    }

    public function getDescription(): string
    {
        return 'Descrizione dello strumento personalizzato';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'param1' => [
                    'type' => 'string',
                    'description' => 'Descrizione del parametro',
                ],
                'param2' => [
                    'type' => 'integer',
                    'description' => 'Parametro numerico',
                ],
            ],
            'required' => ['param1'],
        ];
    }

    public function execute(array $arguments): string
    {
        $param1 = $arguments['param1'];
        $param2 = $arguments['param2'] ?? 0;
        
        // Implementazione dello strumento
        
        return json_encode([
            'result' => 'Risultato dell\'operazione',
            'param1' => $param1,
            'param2' => $param2,
        ]);
    }
}
```

### Strumento per Modelli Eloquent

Questo esempio mostra come creare uno strumento che interagisce con i modelli Eloquent, seguendo le regole di tipizzazione per PHPStan livello 9:

```php
<?php

declare(strict_types=1);

namespace Modules\NomeModulo\App\MCP\Tools;

use InnoGE\LaravelMcp\Tools\Tool;
use Modules\User\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserQueryTool implements Tool
{
    public function getName(): string
    {
        return 'query-users';
    }

    public function getDescription(): string
    {
        return 'Interroga gli utenti del sistema in base a criteri specifici';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'search' => [
                    'type' => 'string',
                    'description' => 'Termine di ricerca per nome o email',
                ],
                'role' => [
                    'type' => 'string',
                    'description' => 'Filtra per ruolo (opzionale)',
                ],
                'limit' => [
                    'type' => 'integer',
                    'description' => 'Numero massimo di risultati',
                ],
            ],
            'required' => ['search'],
        ];
    }

    /**
     * Esegue la query sugli utenti
     *
     * @param array<string, mixed> $arguments
     * @return string
     */
    public function execute(array $arguments): string
    {
        $search = is_string($arguments['search']) ? $arguments['search'] : '';
        $role = isset($arguments['role']) && is_string($arguments['role']) ? $arguments['role'] : null;
        $limit = isset($arguments['limit']) && is_numeric($arguments['limit']) ? (int)$arguments['limit'] : 10;
        
        $query = User::query()
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        
        if ($role !== null) {
            $query->whereHas('roles', function ($q) use ($role) {
                $q->where('name', $role);
            });
        }
        
        /** @var Collection<int, User> $users */
        $users = $query->limit($limit)->get();
        
        $results = $users->map(function (User $user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at?->format('Y-m-d H:i:s'),
            ];
        })->toArray();
        
        return json_encode([
            'count' => count($results),
            'users' => $results,
        ]);
    }
}
```

### Strumento con Spatie Data Objects

Questo esempio utilizza Spatie Laravel Data per la gestione dei dati, seguendo le best practices di Windsurf/Xot:

```php
<?php

declare(strict_types=1);

namespace Modules\NomeModulo\App\MCP\Tools;

use InnoGE\LaravelMcp\Tools\Tool;
use Modules\NomeModulo\Datas\ReportData;
use Modules\NomeModulo\Datas\ReportRequestData;
use Illuminate\Support\Carbon;

class GenerateReportTool implements Tool
{
    public function getName(): string
    {
        return 'generate-report';
    }

    public function getDescription(): string
    {
        return 'Genera un report basato su parametri specifici';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'report_type' => [
                    'type' => 'string',
                    'enum' => ['sales', 'users', 'activity'],
                    'description' => 'Tipo di report da generare',
                ],
                'start_date' => [
                    'type' => 'string',
                    'description' => 'Data di inizio (formato YYYY-MM-DD)',
                ],
                'end_date' => [
                    'type' => 'string',
                    'description' => 'Data di fine (formato YYYY-MM-DD)',
                ],
                'format' => [
                    'type' => 'string',
                    'enum' => ['json', 'csv', 'pdf'],
                    'description' => 'Formato del report',
                ],
            ],
            'required' => ['report_type', 'start_date', 'end_date'],
        ];
    }

    /**
     * Esegue la generazione del report
     *
     * @param array<string, mixed> $arguments
     * @return string
     */
    public function execute(array $arguments): string
    {
        // Validazione e conversione dei dati di input
        $reportType = is_string($arguments['report_type']) ? $arguments['report_type'] : 'sales';
        $startDate = is_string($arguments['start_date']) ? $arguments['start_date'] : Carbon::now()->subMonth()->format('Y-m-d');
        $endDate = is_string($arguments['end_date']) ? $arguments['end_date'] : Carbon::now()->format('Y-m-d');
        $format = isset($arguments['format']) && is_string($arguments['format']) ? $arguments['format'] : 'json';
        
        // Creazione del Data Object per la richiesta
        $requestData = new ReportRequestData(
            reportType: $reportType,
            startDate: $startDate,
            endDate: $endDate,
            format: $format
        );
        
        // Generazione del report (implementazione simulata)
        $reportData = $this->generateReport($requestData);
        
        // Conversione del Data Object in JSON
        return json_encode($reportData->toArray());
    }
    
    /**
     * Genera il report in base ai parametri forniti
     *
     * @param ReportRequestData $requestData
     * @return ReportData
     */
    private function generateReport(ReportRequestData $requestData): ReportData
    {
        // Implementazione della generazione del report
        // Questo è solo un esempio
        
        return new ReportData(
            id: uniqid('report_'),
            type: $requestData->reportType,
            startDate: $requestData->startDate,
            endDate: $requestData->endDate,
            format: $requestData->format,
            generatedAt: Carbon::now()->format('Y-m-d H:i:s'),
            data: [
                'total' => 1000,
                'items' => [
                    ['name' => 'Item 1', 'value' => 100],
                    ['name' => 'Item 2', 'value' => 200],
                    ['name' => 'Item 3', 'value' => 300],
                ],
            ]
        );
    }
}
```

### Data Objects

Ecco gli esempi dei Data Objects utilizzati nello strumento precedente:

```php
<?php

declare(strict_types=1);

namespace Modules\NomeModulo\Datas;

use Spatie\LaravelData\Data;

class ReportRequestData extends Data
{
    public function __construct(
        public readonly string $reportType,
        public readonly string $startDate,
        public readonly string $endDate,
        public readonly string $format,
    ) {}
}
```

```php
<?php

declare(strict_types=1);

namespace Modules\NomeModulo\Datas;

use Spatie\LaravelData\Data;

class ReportData extends Data
{
    /**
     * @param string $id
     * @param string $type
     * @param string $startDate
     * @param string $endDate
     * @param string $format
     * @param string $generatedAt
     * @param array<string, mixed> $data
     */
    public function __construct(
        public readonly string $id,
        public readonly string $type,
        public readonly string $startDate,
        public readonly string $endDate,
        public readonly string $format,
        public readonly string $generatedAt,
        public readonly array $data,
    ) {}
}
```

## Integrazione con Filament

### Widget MCP per Filament

Questo esempio mostra come creare un widget Filament che utilizza un server MCP:

```php
<?php

declare(strict_types=1);

namespace Modules\NomeModulo\App\Filament\Widgets;

use Filament\Widgets\Widget;
use Modules\Xot\Filament\Widgets\XotBaseWidget;
use Illuminate\Support\Facades\Http;

class McpAssistantWidget extends XotBaseWidget
{
    public static string $view = 'nomemodulo::filament.widgets.mcp-assistant';
    
    protected int|string|array $columnSpan = 'full';
    
    /**
     * @var array<string, mixed>
     */
    public array $data = [];
    
    /**
     * @var string
     */
    public string $query = '';
    
    public function mount(): void
    {
        $this->data = [
            'results' => [],
            'loading' => false,
        ];
    }
    
    public function executeQuery(): void
    {
        $this->data['loading'] = true;
        
        try {
            // Esempio di chiamata a un server MCP tramite API
            $response = Http::post('http://localhost:3000/api/mcp', [
                'query' => $this->query,
                'tool' => 'custom-tool',
                'params' => [
                    'param1' => 'valore1',
                    'param2' => 42,
                ],
            ]);
            
            $this->data['results'] = $response->json();
        } catch (\Exception $e) {
            $this->data['error'] = $e->getMessage();
        } finally {
            $this->data['loading'] = false;
        }
    }
}
```

### Vista Blade per il Widget

```blade
<x-filament::widget>
    <x-filament::section>
        <div class="space-y-4">
            <h2 class="text-lg font-medium">Assistente MCP</h2>
            
            <div class="flex space-x-2">
                <x-filament::input.wrapper>
                    <x-filament::input
                        type="text"
                        wire:model="query"
                        placeholder="Inserisci la tua domanda..."
                    />
                </x-filament::input.wrapper>
                
                <x-filament::button wire:click="executeQuery" wire:loading.attr="disabled">
                    <span wire:loading.remove>Esegui</span>
                    <span wire:loading>Elaborazione...</span>
                </x-filament::button>
            </div>
            
            @if($data['loading'])
                <div class="flex justify-center">
                    <x-filament::loading-indicator class="h-5 w-5" />
                    <span class="ml-2">Elaborazione in corso...</span>
                </div>
            @endif
            
            @if(isset($data['error']))
                <div class="p-4 bg-red-50 text-red-700 rounded-lg">
                    {{ $data['error'] }}
                </div>
            @endif
            
            @if(!empty($data['results']))
                <div class="p-4 bg-gray-50 rounded-lg">
                    <pre class="text-sm overflow-auto">{{ json_encode($data['results'], JSON_PRETTY_PRINT) }}</pre>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament::widget>
```

## Casi d'Uso Avanzati

### Integrazione con Spatie Laravel Queues

Questo esempio mostra come utilizzare Spatie QueueableAction con MCP:

```php
<?php

declare(strict_types=1);

namespace Modules\NomeModulo\App\MCP\Tools;

use InnoGE\LaravelMcp\Tools\Tool;
use Modules\NomeModulo\Actions\ProcessDataAction;
use Spatie\QueueableAction\QueueableAction;

class ProcessLargeDatasetTool implements Tool
{
    public function getName(): string
    {
        return 'process-large-dataset';
    }

    public function getDescription(): string
    {
        return 'Elabora un grande dataset in background';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'dataset_id' => [
                    'type' => 'string',
                    'description' => 'ID del dataset da elaborare',
                ],
                'options' => [
                    'type' => 'object',
                    'description' => 'Opzioni di elaborazione',
                ],
            ],
            'required' => ['dataset_id'],
        ];
    }

    /**
     * Esegue l'elaborazione del dataset in background
     *
     * @param array<string, mixed> $arguments
     * @return string
     */
    public function execute(array $arguments): string
    {
        $datasetId = is_string($arguments['dataset_id']) ? $arguments['dataset_id'] : '';
        $options = isset($arguments['options']) && is_array($arguments['options']) ? $arguments['options'] : [];
        
        // Utilizza QueueableAction per eseguire l'elaborazione in background
        $action = app(ProcessDataAction::class);
        $action->onQueue('datasets')->execute($datasetId, $options);
        
        return json_encode([
            'status' => 'processing',
            'message' => 'Elaborazione avviata in background',
            'job_id' => $datasetId,
        ]);
    }
}
```

### Action Class

```php
<?php

declare(strict_types=1);

namespace Modules\NomeModulo\Actions;

use Spatie\QueueableAction\QueueableAction;
use Modules\NomeModulo\Models\Dataset;
use Illuminate\Support\Facades\Log;

class ProcessDataAction
{
    use QueueableAction;
    
    /**
     * Elabora un dataset
     *
     * @param string $datasetId
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    public function execute(string $datasetId, array $options = []): array
    {
        Log::info("Avvio elaborazione dataset: {$datasetId}", $options);
        
        // Implementazione dell'elaborazione del dataset
        // ...
        
        return [
            'status' => 'completed',
            'dataset_id' => $datasetId,
            'processed_at' => now()->format('Y-m-d H:i:s'),
        ];
    }
}
```

## Best Practices

### Tipizzazione per PHPStan Livello 9

Segui queste linee guida per garantire la compatibilità con PHPStan livello 9:

1. **Usa `list<string>` invece di `array<int, string>` o `string[]`** per array numerici:

```php
/**
 * @var list<string>
 */
protected $supportedFormats = ['json', 'csv', 'pdf'];
```

2. **Specifica correttamente i tipi per tutte le proprietà**:

```php
/**
 * @var array<string, string>
 */
protected $mappings = ['key1' => 'value1', 'key2' => 'value2'];

/**
 * @var string
 */
protected $primaryKey = 'id';

/**
 * @var bool
 */
public $enabled = true;
```

3. **Verifica sempre il tipo prima di usare valori che potrebbero essere `mixed`**:

```php
// ERRATO
$value = (string) $data['key'];

// CORRETTO
$value = is_string($data['key'] ?? '') 
    ? $data['key'] 
    : (is_scalar($data['key'] ?? '') ? (string)$data['key'] : '');
```

### Sicurezza e Autorizzazioni

Implementa controlli di sicurezza adeguati per i tuoi strumenti MCP:

```php
<?php

declare(strict_types=1);

namespace Modules\NomeModulo\App\MCP\Tools;

use InnoGE\LaravelMcp\Tools\Tool;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class SecureOperationTool implements Tool
{
    public function getName(): string
    {
        return 'secure-operation';
    }

    public function getDescription(): string
    {
        return 'Esegue un\'operazione protetta da autorizzazioni';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'resource_id' => [
                    'type' => 'string',
                    'description' => 'ID della risorsa',
                ],
                'operation' => [
                    'type' => 'string',
                    'enum' => ['view', 'edit', 'delete'],
                    'description' => 'Operazione da eseguire',
                ],
            ],
            'required' => ['resource_id', 'operation'],
        ];
    }

    /**
     * Esegue un'operazione protetta da autorizzazioni
     *
     * @param array<string, mixed> $arguments
     * @return string
     */
    public function execute(array $arguments): string
    {
        $resourceId = is_string($arguments['resource_id']) ? $arguments['resource_id'] : '';
        $operation = is_string($arguments['operation']) ? $arguments['operation'] : '';
        
        // Verifica che l'utente sia autenticato
        if (!Auth::check()) {
            return json_encode([
                'error' => 'Utente non autenticato',
                'code' => 401,
            ]);
        }
        
        // Verifica le autorizzazioni
        $user = Auth::user();
        if (!$user || !Gate::forUser($user)->allows("{$operation}-resource", $resourceId)) {
            return json_encode([
                'error' => 'Operazione non autorizzata',
                'code' => 403,
            ]);
        }
        
        // Esegui l'operazione
        // ...
        
        return json_encode([
            'status' => 'success',
            'message' => "Operazione {$operation} eseguita con successo sulla risorsa {$resourceId}",
        ]);
    }
}
```

## Conclusioni

Seguendo queste linee guida e gli esempi forniti, puoi implementare server MCP nei tuoi progetti Windsurf/Xot in modo coerente con le regole di progetto e le best practices. Ricorda di mantenere una documentazione aggiornata per ogni strumento MCP che crei, in modo che altri sviluppatori possano facilmente comprendere e utilizzare le tue implementazioni.
