# Implementazioni MCP per Laravel e Modulo AI

## Introduzione

Questo documento esplora le implementazioni pratiche del Model Context Protocol (MCP) nel contesto di Laravel e del modulo AI di Windsurf/Xot. Analizzeremo diversi approcci di implementazione, confrontando le soluzioni disponibili e fornendo esempi concreti di codice per ciascun caso d'uso.

## Pacchetti Laravel per MCP

### Laravel MCP di InnoGE

[InnoGE/laravel-mcp](https://github.com/InnoGE/laravel-mcp) è un pacchetto che semplifica lo sviluppo di server MCP con Laravel, utilizzando principalmente il trasporto STDIO.

#### Caratteristiche principali:
- Supporto per risorse Eloquent
- Sistema di strumenti estensibile
- Facile integrazione con comandi Artisan

#### Esempio di implementazione:

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use InnoGE\LaravelMcp\Commands\ServesMcpServer;
use App\Models\User;
use InnoGE\LaravelMcp\Resources\EloquentResourceProvider;
use App\MCP\Tools\CustomAnalysisTool;

class McpServerCommand extends Command
{
    use ServesMcpServer;

    protected $signature = 'mcp:serve';
    protected $description = 'Avvia un server MCP per il modulo AI';

    public function handle(): int
    {
        return $this->serveMcp('windsurf-ai-assistant', '1.0.0');
    }

    private function getTools(): array
    {
        return [
            CustomAnalysisTool::class,
        ];
    }

    private function getResources(): array
    {
        return [
            new EloquentResourceProvider(User::query(), 'users', 'Utenti dell\'applicazione')
        ];
    }
}
```

### Laravel MCP Server di OP.GG

[opgginc/laravel-mcp-server](https://github.com/opgginc/laravel-mcp-server) è una soluzione enterprise che utilizza Server-Sent Events (SSE) per una comunicazione più sicura e controllata.

#### Caratteristiche principali:
- Trasporto SSE per ambienti di produzione
- Architettura Pub/Sub con adattatori
- Supporto per Laravel Octane
- Controlli di sicurezza avanzati

#### Configurazione di base:

```php
// config/mcp-server.php
return [
    'adapters' => [
        'redis' => [
            'connection' => env('MCP_REDIS_CONNECTION', 'default'),
            'prefix' => env('MCP_REDIS_PREFIX', 'mcp:'),
        ],
    ],
    'default_adapter' => env('MCP_DEFAULT_ADAPTER', 'redis'),
    'route_prefix' => env('MCP_ROUTE_PREFIX', 'mcp'),
    'middleware' => [
        'web',
        // Aggiungi middleware di autenticazione personalizzato
        \App\Http\Middleware\AuthenticateMcpRequests::class,
    ],
];
```

### Laravel Helper Tools di jsonallen

[jsonallen/laravel-mcp](https://github.com/jsonallen/laravel-mcp) fornisce strumenti specifici per migliorare lo sviluppo Laravel attraverso l'integrazione con Cursor IDE.

#### Strumenti disponibili:
- `tail_log_file`: Visualizzazione dei log Laravel
- `search_log_errors`: Ricerca di errori nei log
- `run_artisan_command`: Esecuzione di comandi Artisan
- `show_model`: Visualizzazione delle informazioni sui modelli

## Casi d'Uso Implementati

### 1. Assistente per Analisi dei Dati

Questo caso d'uso consente agli analisti di interagire con i dati dell'applicazione attraverso linguaggio naturale.

#### Implementazione con EloquentResourceProvider:

```php
namespace App\MCP\Tools;

use InnoGE\LaravelMcp\Tools\Tool;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class SalesAnalysisTool implements Tool
{
    public function getName(): string
    {
        return 'analyze-sales';
    }

    public function getDescription(): string
    {
        return 'Analizza i dati di vendita per periodo e categoria';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'start_date' => [
                    'type' => 'string',
                    'description' => 'Data di inizio (formato YYYY-MM-DD)',
                ],
                'end_date' => [
                    'type' => 'string',
                    'description' => 'Data di fine (formato YYYY-MM-DD)',
                ],
                'category' => [
                    'type' => 'string',
                    'description' => 'Categoria di prodotti (opzionale)',
                ],
            ],
            'required' => ['start_date', 'end_date'],
        ];
    }

    public function execute(array $arguments): string
    {
        $startDate = $arguments['start_date'];
        $endDate = $arguments['end_date'];
        $category = $arguments['category'] ?? null;
        
        $query = Order::whereBetween('created_at', [$startDate, $endDate]);
        
        if ($category) {
            $query->whereHas('products', function ($q) use ($category) {
                $q->where('category', $category);
            });
        }
        
        $totalSales = $query->sum('total');
        $orderCount = $query->count();
        $averageOrderValue = $orderCount > 0 ? $totalSales / $orderCount : 0;
        
        return json_encode([
            'period' => "$startDate to $endDate",
            'category' => $category ?? 'All categories',
            'total_sales' => $totalSales,
            'order_count' => $orderCount,
            'average_order_value' => $averageOrderValue,
        ]);
    }
}
```

### 2. Generazione di Contenuti SEO

Questo caso d'uso automatizza la creazione e l'ottimizzazione di contenuti per il sito web.

#### Implementazione con NeuronAI:

```php
namespace App\MCP\Tools;

use NeuronAI\Agent;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\OpenAI\OpenAI;
use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolProperty;
use App\Models\Article;

class SeoContentGenerator extends Agent
{
    public function provider(): AIProviderInterface
    {
        return new OpenAI(
            key: config('services.openai.api_key'),
            model: 'gpt-4',
        );
    }
    
    public function instructions()
    {
        return "Sei un assistente specializzato nella generazione di contenuti SEO per articoli di blog.";
    }
    
    public function tools(): array
    {
        return [
            Tool::make(
                "optimize_article_seo", 
                "Ottimizza un articolo esistente per SEO."
            )->addProperty(
                new ToolProperty(
                    name: 'article_id',
                    type: 'integer',
                    description: 'ID dell\'articolo da ottimizzare',
                    required: true
                )
            )->setCallable(function (string $article_id) {
                $article = Article::findOrFail($article_id);
                
                // Analisi SEO del contenuto esistente
                $seoAnalysis = $this->analyzeSeoFactors($article->content);
                
                return json_encode([
                    'title' => $article->title,
                    'current_content' => $article->content,
                    'seo_analysis' => $seoAnalysis,
                    'keyword_density' => $this->calculateKeywordDensity($article->content),
                    'readability_score' => $this->calculateReadabilityScore($article->content),
                ]);
            }),
            
            Tool::make(
                "generate_meta_description", 
                "Genera una meta descrizione ottimizzata per SEO."
            )->addProperty(
                new ToolProperty(
                    name: 'article_id',
                    type: 'integer',
                    description: 'ID dell\'articolo',
                    required: true
                )
            )->setCallable(function (string $article_id) {
                $article = Article::findOrFail($article_id);
                
                // Generazione della meta descrizione
                $metaDescription = substr(strip_tags($article->content), 0, 150) . '...';
                
                return $metaDescription;
            })
        ];
    }
    
    private function analyzeSeoFactors($content)
    {
        // Implementazione dell'analisi SEO
        // ...
        
        return [
            'word_count' => str_word_count(strip_tags($content)),
            'heading_count' => substr_count($content, '<h2>') + substr_count($content, '<h3>'),
            'image_count' => substr_count($content, '<img'),
            'link_count' => substr_count($content, '<a href'),
        ];
    }
    
    private function calculateKeywordDensity($content)
    {
        // Implementazione del calcolo della densità delle parole chiave
        // ...
        
        return 2.5; // Esempio
    }
    
    private function calculateReadabilityScore($content)
    {
        // Implementazione del calcolo del punteggio di leggibilità
        // ...
        
        return 75; // Esempio
    }
}
```

### 3. Assistente di Sviluppo per Debugging

Questo caso d'uso aiuta gli sviluppatori a identificare e risolvere errori nelle applicazioni Laravel.

#### Implementazione con Laravel Helper Tools:

```php
namespace App\MCP\Tools;

use InnoGE\LaravelMcp\Tools\Tool;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class DebuggingAssistantTool implements Tool
{
    public function getName(): string
    {
        return 'debug-laravel-error';
    }

    public function getDescription(): string
    {
        return 'Analizza un errore Laravel e suggerisce soluzioni';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'error_message' => [
                    'type' => 'string',
                    'description' => 'Il messaggio di errore da analizzare',
                ],
                'stack_trace' => [
                    'type' => 'string',
                    'description' => 'Lo stack trace dell\'errore (opzionale)',
                ],
            ],
            'required' => ['error_message'],
        ];
    }

    public function execute(array $arguments): string
    {
        $errorMessage = $arguments['error_message'];
        $stackTrace = $arguments['stack_trace'] ?? '';
        
        // Analisi dei log recenti per contesto
        $recentLogs = $this->getRecentLogs();
        
        // Ricerca di errori simili nei log
        $similarErrors = $this->findSimilarErrors($errorMessage);
        
        // Controllo delle dipendenze
        $composerIssues = $this->checkComposerDependencies();
        
        // Verifica delle configurazioni
        $configIssues = $this->checkConfigurations();
        
        return json_encode([
            'error_analysis' => $this->analyzeError($errorMessage, $stackTrace),
            'similar_errors' => $similarErrors,
            'composer_issues' => $composerIssues,
            'config_issues' => $configIssues,
            'suggested_solutions' => $this->suggestSolutions($errorMessage, $stackTrace),
        ]);
    }
    
    private function getRecentLogs()
    {
        $logPath = storage_path('logs/laravel.log');
        if (!File::exists($logPath)) {
            return 'Log file not found';
        }
        
        $process = Process::fromShellCommandline("tail -n 50 {$logPath}");
        $process->run();
        
        return $process->getOutput();
    }
    
    private function findSimilarErrors($errorMessage)
    {
        // Implementazione della ricerca di errori simili
        // ...
        
        return [];
    }
    
    private function checkComposerDependencies()
    {
        $process = Process::fromShellCommandline('composer diagnose');
        $process->run();
        
        return $process->getOutput();
    }
    
    private function checkConfigurations()
    {
        // Verifica delle configurazioni comuni che causano problemi
        // ...
        
        return [];
    }
    
    private function analyzeError($errorMessage, $stackTrace)
    {
        // Analisi dell'errore per identificare la causa
        // ...
        
        return [
            'type' => 'DatabaseException', // Esempio
            'likely_cause' => 'Connection refused',
            'affected_component' => 'Database',
        ];
    }
    
    private function suggestSolutions($errorMessage, $stackTrace)
    {
        // Generazione di soluzioni basate sull'analisi
        // ...
        
        return [
            'Verifica che il servizio MySQL sia in esecuzione',
            'Controlla le credenziali del database nel file .env',
            'Assicurati che l\'host del database sia raggiungibile',
        ];
    }
}
```

## Integrazione con Cursor IDE

Per integrare il server MCP con Cursor IDE, è necessario configurare il file `.cursor/settings.json` nel progetto:

```json
{
  "mcp": {
    "servers": [
      {
        "name": "Laravel AI Assistant",
        "transport": "stdio",
        "command": "php artisan mcp:serve",
        "cwd": "${workspaceFolder}"
      }
    ]
  }
}
```

Per la versione SSE:

```json
{
  "mcp": {
    "servers": [
      {
        "name": "Laravel AI Assistant",
        "transport": "sse",
        "url": "http://localhost:8000/mcp/sse"
      }
    ]
  }
}
```

## Considerazioni per l'Implementazione

### Sicurezza

- Implementare autenticazione per tutte le richieste MCP
- Limitare l'accesso agli strumenti in base ai ruoli degli utenti
- Validare e sanitizzare tutti gli input prima dell'esecuzione
- Registrare tutte le interazioni per audit e debugging

### Prestazioni

- Utilizzare Laravel Octane per migliorare le prestazioni del server MCP
- Implementare caching per le operazioni costose
- Considerare l'utilizzo di code per operazioni asincrone
- Monitorare l'utilizzo delle risorse del server

### Manutenibilità

- Organizzare gli strumenti in categorie logiche
- Documentare ogni strumento con descrizioni chiare
- Implementare test automatici per tutti gli strumenti
- Seguire le convenzioni di Laravel per la struttura del codice

## Conclusioni

L'implementazione di server MCP nel modulo AI di Windsurf/Xot offre un potente meccanismo per estendere le capacità dell'applicazione attraverso l'integrazione con modelli di linguaggio. Scegliendo l'approccio di implementazione più adatto alle esigenze del progetto, è possibile creare assistenti AI contestuali che migliorano significativamente l'esperienza utente e l'efficienza operativa.

## Risorse Correlate

- [Panoramica MCP](./MCP_OVERVIEW.md)
<<<<<<< HEAD
- [Implementazione di Server MCP](../../../project_docs/tutorials/MCP_SERVER_IMPLEMENTATION.md)
- [Sicurezza nei Server MCP](../../../project_docs/security/MCP_SECURITY_BEST_PRACTICES.md)
=======
- [Implementazione di Server MCP](../../../docs/tutorials/MCP_SERVER_IMPLEMENTATION.md)
- [Sicurezza nei Server MCP](../../../docs/security/MCP_SECURITY_BEST_PRACTICES.md)
>>>>>>> 901402b (.)
