# Integrazione MCP con Database in Laravel

## Panoramica

Questo documento esplora l'integrazione del Model Context Protocol (MCP) con database in applicazioni Laravel, con particolare attenzione ai casi d'uso, alle implementazioni disponibili e alle best practices di sicurezza.

## Indice

1. [Introduzione](#introduzione)
2. [Casi d'Uso](#casi-duso)
3. [Implementazioni Disponibili](#implementazioni-disponibili)
4. [Architettura di Integrazione](#architettura-di-integrazione)
5. [Esempi di Implementazione](#esempi-di-implementazione)
6. [Sicurezza e Best Practices](#sicurezza-e-best-practices)
7. [Risorse e Riferimenti](#risorse-e-riferimenti)

## Introduzione

L'integrazione di MCP con database in Laravel consente agli agenti AI di interagire con i dati dell'applicazione in modo strutturato e sicuro. Questa integrazione apre numerose possibilità per l'automazione, l'analisi dei dati e l'assistenza agli sviluppatori.

MCP (Model Context Protocol) fornisce un'interfaccia standardizzata che permette agli LLM (Large Language Models) di comunicare con strumenti esterni, inclusi database. In Laravel, questa integrazione può essere implementata attraverso vari approcci, da semplici wrapper per query SQL a integrazioni più sofisticate con Eloquent ORM.

## Casi d'Uso

### 1. Assistenza allo Sviluppo

- **Generazione di Query Ottimizzate**: L'agente AI può analizzare la struttura del database e generare query Eloquent ottimizzate.
- **Debugging di Query**: Identificazione e risoluzione di problemi nelle query, come N+1 o mancanza di indici.
- **Suggerimenti di Schema**: Analisi dello schema del database e suggerimenti per miglioramenti strutturali.

### 2. Analisi dei Dati

- **Reportistica Dinamica**: Generazione di report basati su dati del database in risposta a richieste in linguaggio naturale.
- **Identificazione di Pattern**: Analisi dei dati per identificare trend, anomalie o correlazioni.
- **Visualizzazione Dati**: Creazione di visualizzazioni appropriate basate sul tipo di dati e sulle domande dell'utente.

### 3. Automazione di Processi

- **ETL Intelligente**: Processi di estrazione, trasformazione e caricamento guidati da AI.
- **Migrazione Dati**: Assistenza nella migrazione di dati tra schemi o database diversi.
- **Pulizia Dati**: Identificazione e correzione di inconsistenze, duplicati o dati mancanti.

### 4. Interazione Utente

- **Chatbot con Accesso ai Dati**: Chatbot che possono rispondere a domande basate sui dati dell'applicazione.
- **Assistenti Virtuali**: Assistenti che possono eseguire operazioni sul database in risposta a comandi in linguaggio naturale.
- **Ricerca Semantica**: Ricerca nei dati del database utilizzando query in linguaggio naturale.

## Implementazioni Disponibili

### 1. Server MCP per MySQL

Diverse implementazioni di server MCP per MySQL sono disponibili:

- **mysql_mcp_server**: Implementazione di base che consente agli LLM di eseguire query SQL su database MySQL.
- **alibabacloud-adb-mysql-mcp-server**: Implementazione specifica per ADB MySQL di Alibaba Cloud.
- **benborla/mcp-server-mysql**: Implementazione con funzionalità di sicurezza avanzate.

### 2. Server MCP per PostgreSQL

- **Server PostgreSQL per Cursor**: Implementazione ottimizzata per l'uso con l'IDE Cursor.
- **Neon PostgreSQL MCP**: Integrazione con il database serverless Neon.

### 3. Server MCP per Database Generici

- **quarkiverse/quarkus-mcp-servers/jdbc**: Implementazione basata su JDBC per supportare vari database.
- **bytebase/dbhub**: Strumento per la gestione di database con supporto MCP.

### 4. Implementazioni Laravel-Specifiche

- **jsonallen/laravel-mcp**: Implementazione specifica per Laravel che integra Eloquent ORM con MCP.
- **Laravel Helper Tools**: Strumenti MCP per interagire con vari aspetti di Laravel, incluso il database.

## Architettura di Integrazione

L'integrazione di MCP con database in Laravel segue generalmente questa architettura:

1. **Server MCP**: Gestisce la comunicazione tra l'LLM e l'applicazione Laravel.
2. **Adapter Layer**: Traduce le richieste MCP in operazioni Eloquent o query SQL.
3. **Security Layer**: Applica regole di sicurezza, validazione e sanitizzazione.
4. **Database Layer**: Esegue le operazioni sul database e restituisce i risultati.

```
┌─────────┐     ┌──────────┐     ┌───────────────┐     ┌─────────────┐     ┌──────────┐
│   LLM   │────▶│ MCP Server│────▶│ Adapter Layer │────▶│Security Layer│────▶│ Database │
└─────────┘     └──────────┘     └───────────────┘     └─────────────┘     └──────────┘
                                                              │
                                                              ▼
                                                      ┌─────────────────┐
                                                      │ Eloquent Models │
                                                      └─────────────────┘
```

## Esempi di Implementazione

### 1. Query Builder con MCP

```php
<?php
declare(strict_types=1);

namespace Modules\AI\MCP\Tools;

use JsonAllen\LaravelMCP\Tool;
use JsonAllen\LaravelMCP\ToolProperty;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class QueryDatabaseTool extends Tool
{
    /**
     * @var array<string>
     */
    protected array $allowedTables = [
        'users',
        'products',
        'orders',
        'categories',
    ];

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'query_database';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Esegue una query sul database utilizzando Eloquent Query Builder.';
    }

    /**
     * @return array<int, ToolProperty>
     */
    public function getProperties(): array
    {
        return [
            new ToolProperty(
                name: 'table',
                type: 'string',
                description: 'Nome della tabella da interrogare',
                required: true
            ),
            new ToolProperty(
                name: 'select',
                type: 'array',
                description: 'Colonne da selezionare',
                required: false
            ),
            new ToolProperty(
                name: 'where',
                type: 'array',
                description: 'Condizioni WHERE (array di [colonna, operatore, valore])',
                required: false
            ),
            new ToolProperty(
                name: 'order_by',
                type: 'object',
                description: 'Ordinamento (colonna e direzione)',
                required: false
            ),
            new ToolProperty(
                name: 'limit',
                type: 'integer',
                description: 'Numero massimo di risultati',
                required: false
            ),
        ];
    }

    /**
     * @param array<string, mixed> $parameters
     * @return array<string, mixed>
     */
    public function handle(array $parameters): array
    {
        $table = $parameters['table'];
        
        // Verifica che la tabella sia nella whitelist
        if (!in_array($table, $this->allowedTables)) {
            return [
                'error' => 'Tabella non autorizzata',
                'message' => 'Solo le tabelle nella whitelist possono essere interrogate',
                'allowed_tables' => $this->allowedTables,
            ];
        }
        
        // Costruisci la query
        $query = DB::table($table);
        
        // Select
        if (isset($parameters['select']) && is_array($parameters['select'])) {
            $query->select($parameters['select']);
        }
        
        // Where
        if (isset($parameters['where']) && is_array($parameters['where'])) {
            foreach ($parameters['where'] as $condition) {
                if (is_array($condition) && count($condition) === 3) {
                    $query->where($condition[0], $condition[1], $condition[2]);
                }
            }
        }
        
        // Order By
        if (isset($parameters['order_by']) && is_array($parameters['order_by'])) {
            $column = $parameters['order_by']['column'] ?? 'id';
            $direction = $parameters['order_by']['direction'] ?? 'asc';
            $query->orderBy($column, $direction);
        }
        
        // Limit
        if (isset($parameters['limit']) && is_int($parameters['limit'])) {
            $query->limit(min($parameters['limit'], 100)); // Limita a max 100 risultati
        } else {
            $query->limit(10); // Default limit
        }
        
        // Esegui la query
        $results = $query->get();
        
        // Ottieni informazioni sulle colonne
        $columns = Schema::getColumnListing($table);
        
        return [
            'table' => $table,
            'columns' => $columns,
            'count' => $results->count(),
            'results' => $results->toArray(),
        ];
    }
}
```

### 2. Eloquent Model Explorer

```php
<?php
declare(strict_types=1);

namespace Modules\AI\MCP\Tools;

use JsonAllen\LaravelMCP\Tool;
use JsonAllen\LaravelMCP\ToolProperty;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ExploreModelTool extends Tool
{
    /**
     * @var array<string>
     */
    protected array $allowedModels = [
        'App\\Models\\User',
        'App\\Models\\Product',
        'App\\Models\\Order',
        'App\\Models\\Category',
    ];

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'explore_model';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Esplora un modello Eloquent, le sue proprietà e relazioni.';
    }

    /**
     * @return array<int, ToolProperty>
     */
    public function getProperties(): array
    {
        return [
            new ToolProperty(
                name: 'model',
                type: 'string',
                description: 'Nome completo del modello Eloquent (es. App\\Models\\User)',
                required: true
            ),
        ];
    }

    /**
     * @param array<string, mixed> $parameters
     * @return array<string, mixed>
     */
    public function handle(array $parameters): array
    {
        $modelName = $parameters['model'];
        
        // Verifica che il modello sia nella whitelist
        if (!in_array($modelName, $this->allowedModels)) {
            return [
                'error' => 'Modello non autorizzato',
                'message' => 'Solo i modelli nella whitelist possono essere esplorati',
                'allowed_models' => $this->allowedModels,
            ];
        }
        
        // Verifica che il modello esista
        if (!class_exists($modelName)) {
            return [
                'error' => 'Modello non trovato',
                'model' => $modelName,
            ];
        }
        
        // Crea un'istanza del modello
        /** @var Model $model */
        $model = new $modelName();
        
        // Ottieni il nome della tabella
        $table = $model->getTable();
        
        // Ottieni la struttura della tabella
        $columns = Schema::getColumnListing($table);
        $columnDetails = [];
        
        foreach ($columns as $column) {
            $type = Schema::getColumnType($table, $column);
            $columnDetails[$column] = [
                'type' => $type,
                'nullable' => Schema::getConnection()->getDoctrineColumn($table, $column)->getNotnull() === false,
            ];
        }
        
        // Ottieni le relazioni
        $relations = $this->getModelRelations($model);
        
        // Ottieni i metodi accessori
        $accessors = $this->getModelAccessors($model);
        
        return [
            'model' => $modelName,
            'table' => $table,
            'primary_key' => $model->getKeyName(),
            'columns' => $columnDetails,
            'fillable' => $model->getFillable(),
            'hidden' => $model->getHidden(),
            'casts' => $model->getCasts(),
            'timestamps' => $model->usesTimestamps(),
            'relations' => $relations,
            'accessors' => $accessors,
        ];
    }
    
    /**
     * Ottiene le relazioni di un modello.
     *
     * @param Model $model
     * @return array<string, string>
     */
    private function getModelRelations(Model $model): array
    {
        $relations = [];
        $reflection = new \ReflectionClass($model);
        
        foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->class === get_class($model) && $method->getNumberOfParameters() === 0) {
                try {
                    $return = $method->invoke($model);
                    
                    if (
                        method_exists($return, 'getRelated') ||
                        $return instanceof \Illuminate\Database\Eloquent\Relations\Relation
                    ) {
                        $relations[$method->getName()] = [
                            'type' => (new \ReflectionClass($return))->getShortName(),
                            'related_model' => get_class($return->getRelated()),
                        ];
                    }
                } catch (\Exception $e) {
                    // Ignora le eccezioni
                }
            }
        }
        
        return $relations;
    }
    
    /**
     * Ottiene gli accessori di un modello.
     *
     * @param Model $model
     * @return array<string, string>
     */
    private function getModelAccessors(Model $model): array
    {
        $accessors = [];
        $reflection = new \ReflectionClass($model);
        
        foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            $methodName = $method->getName();
            
            if (
                $method->class === get_class($model) &&
                $method->getNumberOfParameters() === 0 &&
                strpos($methodName, 'get') === 0 &&
                strpos($methodName, 'Attribute') !== false
            ) {
                $accessorName = Str::snake(substr($methodName, 3, -9));
                $accessors[$accessorName] = $methodName;
            }
        }
        
        return $accessors;
    }
}
```

### 3. Natural Language Query Tool

```php
<?php
declare(strict_types=1);

namespace Modules\AI\MCP\Tools;

use JsonAllen\LaravelMCP\Tool;
use JsonAllen\LaravelMCP\ToolProperty;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use NeuronAI\Agent;
use NeuronAI\Providers\OpenAI\OpenAI;

class NaturalLanguageQueryTool extends Tool
{
    /**
     * @var array<string>
     */
    protected array $allowedTables = [
        'users',
        'products',
        'orders',
        'categories',
    ];
    
    /**
     * @var Agent
     */
    protected Agent $agent;
    
    /**
     * @param Agent|null $agent
     */
    public function __construct(?Agent $agent = null)
    {
        if ($agent === null) {
            $this->agent = new Agent(new OpenAI(
                key: env('OPENAI_API_KEY'),
                model: 'gpt-4-turbo'
            ));
        } else {
            $this->agent = $agent;
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'natural_language_query';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Esegue una query sul database utilizzando una domanda in linguaggio naturale.';
    }

    /**
     * @return array<int, ToolProperty>
     */
    public function getProperties(): array
    {
        return [
            new ToolProperty(
                name: 'question',
                type: 'string',
                description: 'Domanda in linguaggio naturale',
                required: true
            ),
            new ToolProperty(
                name: 'tables',
                type: 'array',
                description: 'Tabelle da considerare (default: tutte le tabelle consentite)',
                required: false
            ),
        ];
    }

    /**
     * @param array<string, mixed> $parameters
     * @return array<string, mixed>
     */
    public function handle(array $parameters): array
    {
        $question = $parameters['question'];
        $tables = $parameters['tables'] ?? $this->allowedTables;
        
        // Filtra le tabelle consentite
        $tables = array_intersect($tables, $this->allowedTables);
        
        if (empty($tables)) {
            return [
                'error' => 'Nessuna tabella valida specificata',
                'allowed_tables' => $this->allowedTables,
            ];
        }
        
        // Ottieni lo schema delle tabelle
        $schema = [];
        foreach ($tables as $table) {
            $columns = Schema::getColumnListing($table);
            $columnDetails = [];
            
            foreach ($columns as $column) {
                $type = Schema::getColumnType($table, $column);
                $columnDetails[$column] = $type;
            }
            
            $schema[$table] = $columnDetails;
        }
        
        // Genera la query SQL utilizzando l'LLM
        $prompt = "Dato il seguente schema di database:\n\n";
        foreach ($schema as $table => $columns) {
            $prompt .= "Tabella: {$table}\n";
            foreach ($columns as $column => $type) {
                $prompt .= "- {$column} ({$type})\n";
            }
            $prompt .= "\n";
        }
        
        $prompt .= "Genera una query SQL per rispondere alla seguente domanda:\n\n{$question}\n\n";
        $prompt .= "Restituisci solo la query SQL senza spiegazioni o commenti.";
        
        $sqlQuery = $this->agent->chat($prompt);
        
        // Esegui la query SQL
        try {
            $results = DB::select($sqlQuery);
            
            return [
                'question' => $question,
                'sql_query' => $sqlQuery,
                'results' => $results,
            ];
        } catch (\Exception $e) {
            return [
                'error' => 'Errore nell\'esecuzione della query',
                'message' => $e->getMessage(),
                'question' => $question,
                'sql_query' => $sqlQuery,
            ];
        }
    }
}
```

## Sicurezza e Best Practices

L'integrazione di MCP con database richiede particolare attenzione alla sicurezza. Ecco alcune best practices da seguire:

### 1. Controllo degli Accessi

- **Whitelist di Tabelle e Modelli**: Limitare l'accesso solo alle tabelle e ai modelli esplicitamente consentiti.
- **Controllo delle Operazioni**: Consentire solo operazioni di lettura per default, limitando le operazioni di scrittura a casi specifici.
- **Autenticazione e Autorizzazione**: Implementare meccanismi di autenticazione per le richieste MCP e autorizzazione basata su ruoli.

### 2. Protezione da SQL Injection

- **Parametrizzazione delle Query**: Utilizzare sempre query parametrizzate o Eloquent Query Builder.
- **Validazione degli Input**: Validare rigorosamente tutti gli input prima di utilizzarli nelle query.
- **Sanitizzazione**: Sanitizzare i dati di input per rimuovere caratteri potenzialmente dannosi.

### 3. Limitazione delle Risorse

- **Limiti di Query**: Implementare limiti sul numero di risultati restituiti.
- **Timeout**: Impostare timeout per le query per evitare operazioni troppo lunghe.
- **Rate Limiting**: Limitare il numero di richieste che possono essere effettuate in un determinato periodo.

### 4. Logging e Monitoraggio

- **Audit Trail**: Registrare tutte le query eseguite tramite MCP.
- **Monitoraggio delle Performance**: Monitorare le performance delle query per identificare potenziali problemi.
- **Alerting**: Configurare alert per query sospette o comportamenti anomali.

### 5. Isolamento dell'Ambiente

- **Database Separati**: Considerare l'utilizzo di database separati o repliche di sola lettura per le query MCP.
- **Utenti Database Dedicati**: Utilizzare utenti database con permessi limitati per le operazioni MCP.
- **Containerizzazione**: Isolare il server MCP in un container separato con accesso limitato alle risorse.

## Risorse e Riferimenti

<<<<<<< HEAD
- [Documentazione Ufficiale MCP](../../../project_docs/references/mcp_documentation.md)
- [Laravel MCP SDK](../../../project_docs/references/laravel_mcp_sdk.md)
- [MySQL MCP Server](../../../project_docs/references/mysql_mcp_server.md)
- [PostgreSQL MCP Server](../../../project_docs/references/postgresql_mcp_server.md)
- [Neon PostgreSQL Integration](../../../project_docs/references/neon_postgresql_integration.md)
- [Supabase MCP Integration](../../../project_docs/references/supabase_mcp_integration.md)
=======
- [Documentazione Ufficiale MCP](../../../docs/references/mcp_documentation.md)
- [Laravel MCP SDK](../../../docs/references/laravel_mcp_sdk.md)
- [MySQL MCP Server](../../../docs/references/mysql_mcp_server.md)
- [PostgreSQL MCP Server](../../../docs/references/postgresql_mcp_server.md)
- [Neon PostgreSQL Integration](../../../docs/references/neon_postgresql_integration.md)
- [Supabase MCP Integration](../../../docs/references/supabase_mcp_integration.md)
>>>>>>> 901402b (.)

Per ulteriori informazioni sull'implementazione di MCP in Laravel, consulta la [Guida all'Integrazione MCP](./MCP_INTEGRATION_GUIDE.md) e i [Casi d'Uso di MCP](./MCP_CASI_USO.md).

---

*Ultimo aggiornamento: Maggio 2025*
