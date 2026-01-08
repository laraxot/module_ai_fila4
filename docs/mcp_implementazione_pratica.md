# Implementazione Pratica dei Server MCP in base_predict_fila3_mono

## Panoramica

Questo documento fornisce linee guida pratiche per l'implementazione dei server MCP (Model Context Protocol) nel progetto base_predict_fila3_mono, seguendo le regole di sviluppo e le convenzioni di codice stabilite.

## Principi di Implementazione

L'implementazione dei server MCP in base_predict_fila3_mono segue questi principi fondamentali:

1. **Strict Types**: Tutti i file PHP devono utilizzare `declare(strict_types=1)`.
2. **Tipizzazione Completa**: Tutte le proprietà, i parametri e i valori di ritorno devono essere tipizzati.
3. **Documentazione**: Tutte le classi e i metodi devono essere documentati con DocBlocks completi.
4. **SOLID**: Applicare i principi SOLID in tutto il codice.
5. **Disaccoppiamento**: Utilizzare contratti/interfacce per il disaccoppiamento.
6. **PHPStan**: Configurare correttamente i livelli di PHPStan per ogni modulo.

## Implementazione dei Server MCP

### Configurazione del Service Provider

```php
<?php

declare(strict_types=1);

namespace Modules\AI\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\AI\Services\MCPService;
use Modules\AI\Services\Contracts\MCPServiceContract;
use Modules\Xot\Providers\XotBaseServiceProvider;

class MCPServiceProvider extends XotBaseServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->singleton(MCPServiceContract::class, function ($app) {
            return new MCPService(
                config('ai.mcp.servers')
            );
        });
        
        $this->app->alias(MCPServiceContract::class, 'mcp');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../Config/mcp.php' => config_path('ai/mcp.php'),
        ], 'config');
    }
}
```

### Definizione dei Contratti

```php
<?php

declare(strict_types=1);

namespace Modules\AI\Services\Contracts;

interface MCPServiceContract
{
    /**
     * Ottiene l'istanza del server MCP sequential-thinking.
     *
     * @return SequentialThinkingServerContract
     */
    public function sequentialThinking(): SequentialThinkingServerContract;
    
    /**
     * Ottiene l'istanza del server MCP memory.
     *
     * @return MemoryServerContract
     */
    public function memory(): MemoryServerContract;
    
    // Altri metodi per gli altri server MCP...
}
```

### Implementazione del Servizio MCP

```php
<?php

declare(strict_types=1);

namespace Modules\AI\Services;

use Modules\AI\Services\Contracts\MCPServiceContract;
use Modules\AI\Services\Contracts\SequentialThinkingServerContract;
use Modules\AI\Services\Contracts\MemoryServerContract;
use Modules\AI\Services\Servers\SequentialThinkingServer;
use Modules\AI\Services\Servers\MemoryServer;

class MCPService implements MCPServiceContract
{
    /**
     * @var array<string, mixed>
     */
    private array $config;
    
    /**
     * @var array<string, object>
     */
    private array $instances = [];
    
    /**
     * @param array<string, mixed> $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }
    
    /**
     * {@inheritdoc}
     */
    public function sequentialThinking(): SequentialThinkingServerContract
    {
        if (!isset($this->instances['sequential-thinking'])) {
            $this->instances['sequential-thinking'] = new SequentialThinkingServer(
                $this->config['sequential-thinking'] ?? []
            );
        }
        
        return $this->instances['sequential-thinking'];
    }
    
    /**
     * {@inheritdoc}
     */
    public function memory(): MemoryServerContract
    {
        if (!isset($this->instances['memory'])) {
            $this->instances['memory'] = new MemoryServer(
                $this->config['memory'] ?? []
            );
        }
        
        return $this->instances['memory'];
    }
    
    // Altri metodi per gli altri server MCP...
}
```

### Implementazione dei Server Specifici

```php
<?php

declare(strict_types=1);

namespace Modules\AI\Services\Servers;

use Modules\AI\Services\Contracts\SequentialThinkingServerContract;
use Modules\AI\DataObjects\ThoughtData;

class SequentialThinkingServer implements SequentialThinkingServerContract
{
    /**
     * @var array<string, mixed>
     */
    private array $config;
    
    /**
     * @param array<string, mixed> $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }
    
    /**
     * Analizza un testo utilizzando il pensiero sequenziale.
     *
     * @param string $text Il testo da analizzare
     * @param array<string> $aspects Gli aspetti da analizzare
     *
     * @return array<string, mixed> I risultati dell'analisi
     */
    public function analyze(string $text, array $aspects): array
    {
        // Implementazione dell'analisi sequenziale
        // ...
        
        return [
            'readability' => [
                'score' => 85,
                'level' => 'advanced',
            ],
            'seo' => [
                'score' => 78,
                'suggestions' => [
                    'Aggiungere più parole chiave',
                    'Migliorare i meta tag',
                ],
            ],
            // Altri risultati...
        ];
    }
    
    /**
     * Genera un pensiero sequenziale.
     *
     * @param string $thought Il pensiero corrente
     * @param int $thoughtNumber Il numero del pensiero
     * @param int $totalThoughts Il numero totale di pensieri
     * @param bool $nextThoughtNeeded Se è necessario un altro pensiero
     *
     * @return ThoughtData I dati del pensiero generato
     */
    public function generateThought(
        string $thought,
        int $thoughtNumber,
        int $totalThoughts,
        bool $nextThoughtNeeded
    ): ThoughtData {
        // Implementazione della generazione del pensiero
        // ...
        
        return new ThoughtData(
            thought: $thought,
            thoughtNumber: $thoughtNumber,
            totalThoughts: $totalThoughts,
            nextThoughtNeeded: $nextThoughtNeeded
        );
    }
}
```

### Definizione dei Data Objects

```php
<?php

declare(strict_types=1);

namespace Modules\AI\DataObjects;

use Spatie\LaravelData\Data;

class ThoughtData extends Data
{
    public function __construct(
        public readonly string $thought,
        public readonly int $thoughtNumber,
        public readonly int $totalThoughts,
        public readonly bool $nextThoughtNeeded,
        public readonly ?bool $isRevision = null,
        public readonly ?int $revisesThought = null,
        public readonly ?int $branchFromThought = null,
        public readonly ?string $branchId = null,
        public readonly ?bool $needsMoreThoughts = null
    ) {
    }
}
```

## Utilizzo nei Controller

```php
<?php

declare(strict_types=1);

namespace Modules\AI\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\AI\Services\Contracts\MCPServiceContract;
use Modules\AI\Http\Requests\AnalyzeTextRequest;

class MCPController extends Controller
{
    /**
     * @param MCPServiceContract $mcpService
     */
    public function __construct(
        private readonly MCPServiceContract $mcpService
    ) {
    }
    
    /**
     * Analizza un testo utilizzando il server MCP sequential-thinking.
     *
     * @param AnalyzeTextRequest $request
     *
     * @return JsonResponse
     */
    public function analyzeText(AnalyzeTextRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        $analysis = $this->mcpService->sequentialThinking()->analyze(
            $validated['text'],
            $validated['aspects'] ?? ['readability', 'seo', 'sentiment']
        );
        
        return response()->json([
            'success' => true,
            'data' => $analysis,
        ]);
    }
}
```

## Utilizzo nelle Actions

```php
<?php

declare(strict_types=1);

namespace Modules\Blog\Actions;

use Modules\Blog\Models\Post;
use Modules\AI\Services\Contracts\MCPServiceContract;
use Modules\Blog\DataObjects\ContentAnalysisData;

class AnalyzePostContentAction
{
    /**
     * @param MCPServiceContract $mcpService
     */
    public function __construct(
        private readonly MCPServiceContract $mcpService
    ) {
    }

    /**
     * Analizza il contenuto di un post utilizzando sequential-thinking.
     *
     * @param Post $post Il post da analizzare
     *
     * @return ContentAnalysisData I dati dell'analisi
     */
    public function execute(Post $post): ContentAnalysisData
    {
        $analysis = $this->mcpService->sequentialThinking()->analyze(
            $post->content,
            [
                'readability',
                'seo',
                'sentiment',
                'keywords'
            ]
        );
        
        // Memorizza l'analisi per riferimento futuro
        $this->mcpService->memory()->store(
            "post_analysis_{$post->id}",
            $analysis
        );
        
        return new ContentAnalysisData(
            readabilityScore: $analysis['readability']['score'],
            seoScore: $analysis['seo']['score'],
            sentiment: $analysis['sentiment']['value'],
            keywords: $analysis['keywords']
        );
    }
}
```

## Configurazione PHPStan

Configurare PHPStan per il modulo AI con il seguente file `phpstan.neon`:

```neon
parameters:
    level: 5
    paths:
        - ./
    excludePaths:
        - vendor
        - node_modules
    checkMissingIterableValueType: false
    checkGenericClassInNonGenericObjectType: false
    ignoreErrors:
        # Aggiungi qui gli errori da ignorare
```

## Integrazione con Filament

```php
<?php

declare(strict_types=1);

namespace Modules\AI\Filament\Resources;

use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Modules\AI\Models\Analysis;
use Modules\AI\Filament\Resources\AnalysisResource\Pages;
use Modules\AI\Services\Contracts\MCPServiceContract;

class AnalysisResource extends Resource
{
    protected static ?string $model = Analysis::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-collection';
    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('content')
                    ->required(),
                Forms\Components\CheckboxList::make('aspects')
                    ->options([
                        'readability' => 'Readability',
                        'seo' => 'SEO',
                        'sentiment' => 'Sentiment',
                        'keywords' => 'Keywords',
                    ])
                    ->required(),
            ]);
    }
    
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('analyze')
                    ->action(function (Analysis $record, MCPServiceContract $mcpService) {
                        $analysis = $mcpService->sequentialThinking()->analyze(
                            $record->content,
                            $record->aspects
                        );
                        
                        $record->update([
                            'results' => $analysis,
                        ]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListAnalyses::route('/'),
            'create' => Pages\CreateAnalysis::route('/create'),
            'edit' => Pages\EditAnalysis::route('/{record}/edit'),
        ];
    }
}
```

## Conclusione

Seguendo queste linee guida per l'implementazione dei server MCP in base_predict_fila3_mono, è possibile creare un'integrazione robusta, tipizzata e conforme alle regole di sviluppo del progetto. Questa implementazione facilita l'utilizzo dei server MCP in tutti i moduli del progetto, mantenendo al contempo la struttura modulare e le convenzioni di codice stabilite.

Per ulteriori dettagli sui server MCP consigliati e la loro integrazione con i moduli, fare riferimento a:
- [MCP_SERVER_CONSIGLIATI.md](./MCP_SERVER_CONSIGLIATI.md)
- [MCP_INTEGRAZIONE_MODULI.md](./MCP_INTEGRAZIONE_MODULI.md)
- [MCP_CONFIGURAZIONE_EDITOR.md](./MCP_CONFIGURAZIONE_EDITOR.md)
