# PrismPHP Integration

## Introduzione
Integrazione con PrismPHP per l'analisi e la generazione di codice basata su AI. PrismPHP è una potente libreria che utilizza modelli di linguaggio avanzati per comprendere, analizzare e generare codice PHP.

### Caratteristiche
- Analisi del codice
  - Rilevamento pattern
  - Analisi della complessità
  - Identificazione vulnerabilità
  - Metriche di qualità
- Generazione di codice
  - Completamento automatico
  - Generazione da descrizioni
  - Conversione tra linguaggi
  - Documentazione automatica
- Refactoring automatico
  - Miglioramento struttura
  - Ottimizzazione performance
  - Standardizzazione stile
  - Rimozione duplicati
- Ottimizzazione del codice
  - Analisi prestazioni
  - Suggerimenti miglioramenti
  - Riduzione complessità
  - Miglioramento leggibilità

## Configurazione
Setup dell'integrazione con PrismPHP.

### Requisiti
- PHP 8.1+
- Composer
- API key PrismPHP
- Ambiente configurato
- Estensioni PHP necessarie
- Permessi file system

### Installazione
```bash
composer require prismphp/ai
```

### Configurazione
```php
// config/prism.php
return [
    'api_key' => env('PRISM_API_KEY'),
    'model' => 'gpt-4',
    'temperature' => 0.7,
    'max_tokens' => 2000,
];
```

## Utilizzo
Esempi pratici di utilizzo di PrismPHP.

### Analisi del Codice
```php
use PrismPHP\AI\CodeAnalyzer;

$analyzer = new CodeAnalyzer();
$results = $analyzer->analyze($code);
```

### Generazione Codice
```php
use PrismPHP\AI\CodeGenerator;

$generator = new CodeGenerator();
$code = $generator->generate($description);
```

### Refactoring
```php
use PrismPHP\AI\Refactoring;

$refactoring = new Refactoring();
$improvedCode = $refactoring->improve($code);
```

## Best Practices
Linee guida per un utilizzo efficace di PrismPHP.

### Suggerimenti
- Prompt design
  - Essere specifici
  - Fornire contesto
  - Definire vincoli
  - Specificare output
- Error handling
  - Gestione API errors
  - Timeout handling
  - Retry logic
  - Fallback options
- Rate limiting
  - Gestione quota
  - Caching risultati
  - Batch processing
  - Queue management
- Caching
  - Cache risultati
  - Cache configurazione
  - Cache modelli
  - Cache analisi

## Integrazione con Laravel
Esempi di integrazione con il framework Laravel.

### Service Provider
```php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use PrismPHP\AI\PrismService;

class PrismServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(PrismService::class, function ($app) {
            return new PrismService(config('prism'));
        });
    }
}
```

### Middleware
```php
namespace App\Http\Middleware;

use Closure;
use PrismPHP\AI\CodeAnalyzer;

class PrismAnalysis
{
    public function handle($request, Closure $next)
    {
        // Analisi codice
        return $next($request);
    }
}
```

## Sicurezza
Considerazioni sulla sicurezza.

### Best Practices
- Protezione API key
- Validazione input
- Sanitizzazione output
- Rate limiting
- Logging accessi 

## Troubleshooting
Risoluzione dei problemi comuni.

### Errori di Configurazione

#### Undefined array key "class"
Questo errore si verifica quando la configurazione dell'attivatore dei moduli non è correttamente definita.

```
Undefined array key "class"
at vendor/nwidart/laravel-modules/src/LaravelModulesServiceProvider.php:92
```

##### Causa
Il file di configurazione `config/modules.php` non contiene la definizione corretta dell'attivatore o manca la chiave 'class' nella configurazione dell'attivatore.

##### Soluzione
1. Verifica il file `config/modules.php` e assicurati che contenga:

```php
return [
    'activator' => 'file', // o 'database'
    
    'activators' => [
        'file' => [
            'class' => \Nwidart\Modules\Activators\FileActivator::class,
            'statuses-file' => base_path('modules_statuses.json'),
            'cache-key' => 'activator.installed',
            'cache-lifetime' => 604800,
        ],
        'database' => [
            'class' => \Nwidart\Modules\Activators\DatabaseActivator::class,
            'table' => 'module_activators',
            'cache-key' => 'activator.installed',
            'cache-lifetime' => 604800,
        ],
    ],
];
```

2. Pulisci la cache della configurazione:
```bash
php artisan config:clear
php artisan cache:clear
```

3. Se stai usando il database come attivatore, assicurati di aver eseguito le migrazioni:
```bash
php artisan migrate
```

4. Verifica i permessi del file `modules_statuses.json` se stai usando l'attivatore file:
```bash
chmod 664 modules_statuses.json
```

##### Prevenzione
- Mantieni sempre una copia di backup della configurazione
- Verifica la configurazione dopo l'aggiornamento dei pacchetti
- Usa il versioning per tracciare le modifiche alla configurazione
- Implementa controlli di validazione della configurazione
