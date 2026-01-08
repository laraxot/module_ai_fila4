# Integrazione dei Server MCP con i Moduli di base_predict_fila3_mono

## Panoramica

Questo documento descrive come integrare i server MCP (Model Context Protocol) con i vari moduli del progetto base_predict_fila3_mono, seguendo la struttura modulare e le convenzioni di codice stabilite.

## Principi di Integrazione

L'integrazione dei server MCP con i moduli di base_predict_fila3_mono segue questi principi fondamentali:

1. **Rispetto della Struttura Modulare**: Ogni integrazione deve rispettare la separazione delle responsabilità tra i moduli.
2. **Tipizzazione Completa**: Tutte le interazioni con i server MCP devono essere completamente tipizzate.
3. **Utilizzo di Contratti**: Preferire l'uso di interfacce/contratti per il disaccoppiamento.
4. **Documentazione Completa**: Ogni integrazione deve essere adeguatamente documentata.
5. **Conformità a PHPStan**: Il codice deve rispettare almeno il livello 5 di PHPStan, con l'obiettivo di raggiungere il livello 9.

## Integrazione per Modulo

### Modulo User

**Server MCP consigliati**: `memory`, `fetch`

**Casi d'uso**:
- Memorizzazione delle preferenze utente tramite il server `memory`
- Verifica di informazioni utente tramite API esterne con il server `fetch`

**Implementazione**:
```php
<?php

declare(strict_types=1);

namespace Modules\User\Actions;

use Modules\Xot\Contracts\UserContract;
use Modules\AI\Services\MCPService;

class StoreUserPreferencesAction
{
    public function __construct(
        private readonly MCPService $mcpService
    ) {
    }

    /**
     * Memorizza le preferenze dell'utente tramite il server MCP memory.
     */
    public function execute(UserContract $user, array $preferences): bool
    {
        return $this->mcpService->memory()->store(
            "user_preferences_{$user->id}",
            $preferences
        );
    }
}
```

### Modulo UI

**Server MCP consigliati**: `puppeteer`, `filesystem`

**Casi d'uso**:
- Testing automatizzato delle interfacce con `puppeteer`
- Gestione degli asset UI con `filesystem`

**Implementazione**:
```php
<?php

declare(strict_types=1);

namespace Modules\UI\Actions;

use Modules\AI\Services\MCPService;

class GenerateUIScreenshotsAction
{
    public function __construct(
        private readonly MCPService $mcpService
    ) {
    }

    /**
     * Genera screenshot delle interfacce UI tramite puppeteer.
     *
     * @param array<string> $routes Percorsi delle route da catturare
     * @param string $outputDir Directory di output per gli screenshot
     *
     * @return array<string, string> Mappa di route => percorso screenshot
     */
    public function execute(array $routes, string $outputDir): array
    {
        $results = [];
        
        foreach ($routes as $route) {
            $screenshotPath = $this->mcpService->puppeteer()->captureScreenshot(
                route($route),
                $outputDir . '/' . str_replace('.', '_', $route) . '.png'
            );
            
            $results[$route] = $screenshotPath;
        }
        
        return $results;
    }
}
```

### Modulo Blog

**Server MCP consigliati**: `sequential-thinking`, `memory`

**Casi d'uso**:
- Analisi dei contenuti con `sequential-thinking`
- Memorizzazione di metadati con `memory`

**Implementazione**:
```php
<?php

declare(strict_types=1);

namespace Modules\Blog\Actions;

use Modules\Blog\Models\Post;
use Modules\AI\Services\MCPService;
use Modules\Blog\DataObjects\ContentAnalysisData;

class AnalyzePostContentAction
{
    public function __construct(
        private readonly MCPService $mcpService
    ) {
    }

    /**
     * Analizza il contenuto di un post utilizzando sequential-thinking.
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

### Modulo Xot

**Server MCP consigliati**: `postgres`, `redis`, `mysql`

**Casi d'uso**:
- Ottimizzazione delle query database con `postgres` o `mysql`
- Gestione della cache con `redis`

**Implementazione**:
```php
<?php

declare(strict_types=1);

namespace Modules\Xot\Actions;

use Modules\AI\Services\MCPService;
use Modules\Xot\DataObjects\QueryAnalysisData;

class OptimizeDatabaseQueryAction
{
    public function __construct(
        private readonly MCPService $mcpService
    ) {
    }

    /**
     * Analizza e ottimizza una query SQL utilizzando il server MCP postgres.
     */
    public function execute(string $query): QueryAnalysisData
    {
        $analysis = $this->mcpService->postgres()->analyzeQuery($query);
        
        return new QueryAnalysisData(
            originalQuery: $query,
            optimizedQuery: $analysis['optimized_query'],
            estimatedCost: $analysis['estimated_cost'],
            recommendations: $analysis['recommendations']
        );
    }
}
```

## Service Provider per l'Integrazione MCP

Per facilitare l'integrazione dei server MCP con i moduli, si consiglia di implementare un Service Provider dedicato nel modulo AI:

```php
<?php

declare(strict_types=1);

namespace Modules\AI\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\AI\Services\MCPService;
use Modules\AI\Services\Contracts\MCPServiceContract;

class MCPServiceProvider extends ServiceProvider
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

## Interfaccia di Servizio MCP

```php
<?php

declare(strict_types=1);

namespace Modules\AI\Services\Contracts;

interface MCPServiceContract
{
    /**
     * Ottiene l'istanza del server MCP sequential-thinking.
     */
    public function sequentialThinking(): SequentialThinkingServerContract;
    
    /**
     * Ottiene l'istanza del server MCP memory.
     */
    public function memory(): MemoryServerContract;
    
    /**
     * Ottiene l'istanza del server MCP fetch.
     */
    public function fetch(): FetchServerContract;
    
    /**
     * Ottiene l'istanza del server MCP filesystem.
     */
    public function filesystem(): FilesystemServerContract;
    
    /**
     * Ottiene l'istanza del server MCP postgres.
     */
    public function postgres(): PostgresServerContract;
    
    /**
     * Ottiene l'istanza del server MCP redis.
     */
    public function redis(): RedisServerContract;
    
    /**
     * Ottiene l'istanza del server MCP puppeteer.
     */
    public function puppeteer(): PuppeteerServerContract;
    
    /**
     * Ottiene l'istanza del server MCP mysql.
     */
    public function mysql(): MySQLServerContract;
}
```

## Conclusione

L'integrazione dei server MCP con i moduli di base_predict_fila3_mono offre potenti capacità di estensione mantenendo la struttura modulare e le convenzioni di codice del progetto. Seguendo le linee guida in questo documento, è possibile implementare integrazioni robuste e tipizzate che migliorano significativamente le funzionalità del sistema.

Per ulteriori dettagli sui server MCP consigliati e la loro configurazione, fare riferimento a:
- [MCP_SERVER_CONSIGLIATI.md](./MCP_SERVER_CONSIGLIATI.md)
- [MCP_CONFIGURAZIONE_EDITOR.md](./MCP_CONFIGURAZIONE_EDITOR.md)
- [MCP_IMPLEMENTAZIONE_LARAVEL.md](./MCP_IMPLEMENTAZIONE_LARAVEL.md)
