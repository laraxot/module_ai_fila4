# Integrazione MCP con Servizi Esterni in Laravel

## Panoramica

Questo documento esplora l'integrazione del Model Context Protocol (MCP) con servizi esterni in applicazioni Laravel, con particolare attenzione ai casi d'uso, alle implementazioni disponibili e alle best practices di sicurezza.

## Indice

1. [Introduzione](#introduzione)
2. [Casi d'Uso](#casi-duso)
3. [Architettura di Integrazione](#architettura-di-integrazione)
4. [Implementazioni per Servizi Comuni](#implementazioni-per-servizi-comuni)
5. [Esempi di Implementazione](#esempi-di-implementazione)
6. [Sicurezza e Best Practices](#sicurezza-e-best-practices)
7. [Risorse e Riferimenti](#risorse-e-riferimenti)

## Introduzione

L'integrazione di MCP con servizi esterni in Laravel consente agli agenti AI di interagire con API, servizi cloud, sistemi di storage e altri servizi esterni in modo strutturato e sicuro. Questa integrazione apre numerose possibilità per l'automazione, l'integrazione di sistemi e l'assistenza agli sviluppatori.

MCP (Model Context Protocol) fornisce un'interfaccia standardizzata che permette agli LLM (Large Language Models) di comunicare con strumenti esterni, inclusi servizi API di terze parti. In Laravel, questa integrazione può essere implementata attraverso vari approcci, da semplici wrapper per client HTTP a integrazioni più sofisticate con SDK specifici.

## Casi d'Uso

### 1. Integrazione con Servizi Cloud

- **Gestione di Risorse Cloud**: Creazione, modifica e monitoraggio di risorse su AWS, Google Cloud, Azure.
- **Serverless Functions**: Deployment e gestione di funzioni serverless.
- **Storage Cloud**: Gestione di file su S3, Google Cloud Storage, Azure Blob Storage.

### 2. Integrazione con API di Terze Parti

- **CRM e Marketing**: Integrazione con Salesforce, HubSpot, Mailchimp.
- **Comunicazione**: Integrazione con Slack, Discord, servizi email e SMS.
- **Pagamenti**: Integrazione con Stripe, PayPal, gateway di pagamento locali.

### 3. Automazione di Processi

- **CI/CD**: Automazione del deployment e dell'integrazione continua.
- **Monitoraggio e Alerting**: Integrazione con servizi come New Relic, Datadog, Sentry.
- **Orchestrazione**: Gestione di processi distribuiti e workflow.

### 4. Analisi e Machine Learning

- **Servizi di ML**: Integrazione con servizi di machine learning come OpenAI, Google Vertex AI, AWS SageMaker.
- **Analisi dei Dati**: Connessione a strumenti di analisi come Google Analytics, Mixpanel.
- **Elaborazione del Linguaggio Naturale**: Integrazione con servizi NLP specializzati.

## Architettura di Integrazione

L'integrazione di MCP con servizi esterni in Laravel segue generalmente questa architettura:

1. **Server MCP**: Gestisce la comunicazione tra l'LLM e l'applicazione Laravel.
2. **Adapter Layer**: Traduce le richieste MCP in chiamate ai servizi esterni.
3. **Security Layer**: Applica regole di sicurezza, validazione e sanitizzazione.
4. **Service Clients**: Gestisce la comunicazione con i servizi esterni.

```
┌─────────┐     ┌──────────┐     ┌───────────────┐     ┌─────────────┐     ┌───────────────┐
│   LLM   │────▶│ MCP Server│────▶│ Adapter Layer │────▶│Security Layer│────▶│ Service Clients│
└─────────┘     └──────────┘     └───────────────┘     └─────────────┘     └───────────────┘
                                                                                    │
                                                                                    ▼
                                                                            ┌───────────────┐
                                                                            │External Services│
                                                                            └───────────────┘
```

## Implementazioni per Servizi Comuni

### 1. Server MCP per AWS

- **aws-mcp-server**: Implementazione che consente agli LLM di interagire con servizi AWS.
- **laravel-aws-mcp**: Integrazione specifica per Laravel con AWS.

### 2. Server MCP per Google Cloud

- **gcp-mcp-server**: Implementazione per Google Cloud Platform.
- **laravel-gcp-tools**: Strumenti MCP per Laravel con GCP.

### 3. Server MCP per API Generiche

- **http-mcp-server**: Implementazione generica per chiamate HTTP.
- **api-gateway-mcp**: Gateway per multiple API con supporto MCP.

### 4. Implementazioni Laravel-Specifiche

- **laravel-external-services-mcp**: Implementazione specifica per Laravel che integra vari servizi esterni.
- **Laravel Helper Tools**: Strumenti MCP per interagire con vari servizi esterni da Laravel.

## Esempi di Implementazione

### 1. Integrazione con API RESTful

```php
<?php
declare(strict_types=1);

namespace Modules\AI\MCP\Tools;

use JsonAllen\LaravelMCP\Tool;
use JsonAllen\LaravelMCP\ToolProperty;
use Illuminate\Support\Facades\Http;

class RestApiTool extends Tool
{
    /**
     * @var array<string>
     */
    protected array $allowedDomains = [
        'api.github.com',
        'api.stripe.com',
        'api.openweathermap.org',
    ];

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'rest_api';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Esegue richieste a API RESTful esterne.';
    }

    /**
     * @return array<int, ToolProperty>
     */
    public function getProperties(): array
    {
        return [
            new ToolProperty(
                name: 'url',
                type: 'string',
                description: 'URL dell\'endpoint API',
                required: true
            ),
            new ToolProperty(
                name: 'method',
                type: 'string',
                description: 'Metodo HTTP (GET, POST, PUT, DELETE)',
                required: true
            ),
            new ToolProperty(
                name: 'headers',
                type: 'object',
                description: 'Headers della richiesta',
                required: false
            ),
            new ToolProperty(
                name: 'body',
                type: 'object',
                description: 'Body della richiesta (per POST, PUT)',
                required: false
            ),
            new ToolProperty(
                name: 'query',
                type: 'object',
                description: 'Parametri query string',
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
        $url = $parameters['url'];
        $method = strtoupper($parameters['method']);
        $headers = $parameters['headers'] ?? [];
        $body = $parameters['body'] ?? null;
        $query = $parameters['query'] ?? [];
        
        // Verifica che il dominio sia nella whitelist
        $domain = parse_url($url, PHP_URL_HOST);
        if (!in_array($domain, $this->allowedDomains)) {
            return [
                'error' => 'Dominio non autorizzato',
                'message' => 'Solo i domini nella whitelist possono essere interrogati',
                'allowed_domains' => $this->allowedDomains,
            ];
        }
        
        // Verifica che il metodo sia valido
        $validMethods = ['GET', 'POST', 'PUT', 'DELETE'];
        if (!in_array($method, $validMethods)) {
            return [
                'error' => 'Metodo non valido',
                'message' => 'Il metodo deve essere uno tra: ' . implode(', ', $validMethods),
            ];
        }
        
        // Esegui la richiesta
        try {
            $response = Http::withHeaders($headers);
            
            // Aggiungi i parametri query
            if (!empty($query)) {
                $response = $response->withQueryParameters($query);
            }
            
            // Esegui la richiesta in base al metodo
            switch ($method) {
                case 'GET':
                    $response = $response->get($url);
                    break;
                case 'POST':
                    $response = $response->post($url, $body ?? []);
                    break;
                case 'PUT':
                    $response = $response->put($url, $body ?? []);
                    break;
                case 'DELETE':
                    $response = $response->delete($url, $body ?? []);
                    break;
            }
            
            // Prepara la risposta
            return [
                'status_code' => $response->status(),
                'headers' => $response->headers(),
                'body' => $response->json() ?? $response->body(),
            ];
        } catch (\Exception $e) {
            return [
                'error' => 'Errore nella richiesta',
                'message' => $e->getMessage(),
            ];
        }
    }
}
```

### 2. Integrazione con AWS S3

```php
<?php
declare(strict_types=1);

namespace Modules\AI\MCP\Tools;

use JsonAllen\LaravelMCP\Tool;
use JsonAllen\LaravelMCP\ToolProperty;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class S3StorageTool extends Tool
{
    /**
     * @var array<string>
     */
    protected array $allowedOperations = [
        'list',
        'get',
        'put',
        'delete',
        'exists',
        'url',
    ];

    /**
     * @var array<string>
     */
    protected array $allowedDirectories = [
        'public/',
        'temp/',
        'reports/',
    ];

    /**
     * @return string
     */
    public function getName(): string
    {
        return 's3_storage';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Interagisce con lo storage S3.';
    }

    /**
     * @return array<int, ToolProperty>
     */
    public function getProperties(): array
    {
        return [
            new ToolProperty(
                name: 'operation',
                type: 'string',
                description: 'Operazione da eseguire (list, get, put, delete, exists, url)',
                required: true
            ),
            new ToolProperty(
                name: 'path',
                type: 'string',
                description: 'Percorso del file o directory',
                required: true
            ),
            new ToolProperty(
                name: 'content',
                type: 'string',
                description: 'Contenuto del file (solo per operazione put)',
                required: false
            ),
            new ToolProperty(
                name: 'recursive',
                type: 'boolean',
                description: 'Esegui l\'operazione ricorsivamente (solo per list)',
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
        $operation = $parameters['operation'];
        $path = $parameters['path'];
        $content = $parameters['content'] ?? null;
        $recursive = $parameters['recursive'] ?? false;
        
        // Verifica che l'operazione sia valida
        if (!in_array($operation, $this->allowedOperations)) {
            return [
                'error' => 'Operazione non valida',
                'message' => 'L\'operazione deve essere una tra: ' . implode(', ', $this->allowedOperations),
            ];
        }
        
        // Verifica che il percorso sia in una directory consentita
        $allowed = false;
        foreach ($this->allowedDirectories as $dir) {
            if (Str::startsWith($path, $dir)) {
                $allowed = true;
                break;
            }
        }
        
        if (!$allowed) {
            return [
                'error' => 'Percorso non autorizzato',
                'message' => 'Il percorso deve iniziare con una delle directory consentite',
                'allowed_directories' => $this->allowedDirectories,
            ];
        }
        
        // Esegui l'operazione
        try {
            $disk = Storage::disk('s3');
            
            switch ($operation) {
                case 'list':
                    $files = $disk->files($path, $recursive);
                    $directories = $disk->directories($path, $recursive);
                    return [
                        'files' => $files,
                        'directories' => $directories,
                    ];
                
                case 'get':
                    if (!$disk->exists($path)) {
                        return [
                            'error' => 'File non trovato',
                            'path' => $path,
                        ];
                    }
                    return [
                        'content' => $disk->get($path),
                        'size' => $disk->size($path),
                        'last_modified' => $disk->lastModified($path),
                    ];
                
                case 'put':
                    if ($content === null) {
                        return [
                            'error' => 'Contenuto mancante',
                            'message' => 'Il parametro content è richiesto per l\'operazione put',
                        ];
                    }
                    $disk->put($path, $content);
                    return [
                        'success' => true,
                        'path' => $path,
                        'size' => strlen($content),
                    ];
                
                case 'delete':
                    if (!$disk->exists($path)) {
                        return [
                            'error' => 'File non trovato',
                            'path' => $path,
                        ];
                    }
                    $disk->delete($path);
                    return [
                        'success' => true,
                        'path' => $path,
                    ];
                
                case 'exists':
                    return [
                        'exists' => $disk->exists($path),
                        'path' => $path,
                    ];
                
                case 'url':
                    if (!$disk->exists($path)) {
                        return [
                            'error' => 'File non trovato',
                            'path' => $path,
                        ];
                    }
                    return [
                        'url' => $disk->url($path),
                        'path' => $path,
                    ];
            }
            
            return [
                'error' => 'Operazione non implementata',
                'operation' => $operation,
            ];
        } catch (\Exception $e) {
            return [
                'error' => 'Errore nell\'operazione',
                'message' => $e->getMessage(),
                'operation' => $operation,
                'path' => $path,
            ];
        }
    }
}
```

### 3. Integrazione con OpenAI

```php
<?php
declare(strict_types=1);

namespace Modules\AI\MCP\Tools;

use JsonAllen\LaravelMCP\Tool;
use JsonAllen\LaravelMCP\ToolProperty;
use OpenAI\Laravel\Facades\OpenAI;

class OpenAITool extends Tool
{
    /**
     * @var array<string>
     */
    protected array $allowedModels = [
        'gpt-3.5-turbo',
        'gpt-4',
        'gpt-4-turbo',
        'dall-e-3',
    ];

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'openai';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Interagisce con l\'API di OpenAI.';
    }

    /**
     * @return array<int, ToolProperty>
     */
    public function getProperties(): array
    {
        return [
            new ToolProperty(
                name: 'service',
                type: 'string',
                description: 'Servizio OpenAI da utilizzare (chat, image, embedding)',
                required: true
            ),
            new ToolProperty(
                name: 'model',
                type: 'string',
                description: 'Modello da utilizzare',
                required: true
            ),
            new ToolProperty(
                name: 'prompt',
                type: 'string',
                description: 'Prompt o input per il modello',
                required: true
            ),
            new ToolProperty(
                name: 'options',
                type: 'object',
                description: 'Opzioni aggiuntive per la richiesta',
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
        $service = $parameters['service'];
        $model = $parameters['model'];
        $prompt = $parameters['prompt'];
        $options = $parameters['options'] ?? [];
        
        // Verifica che il modello sia nella whitelist
        if (!in_array($model, $this->allowedModels)) {
            return [
                'error' => 'Modello non autorizzato',
                'message' => 'Solo i modelli nella whitelist possono essere utilizzati',
                'allowed_models' => $this->allowedModels,
            ];
        }
        
        // Esegui la richiesta in base al servizio
        try {
            switch ($service) {
                case 'chat':
                    $response = OpenAI::chat()->create([
                        'model' => $model,
                        'messages' => [
                            ['role' => 'system', 'content' => $options['system_message'] ?? 'You are a helpful assistant.'],
                            ['role' => 'user', 'content' => $prompt],
                        ],
                        'temperature' => $options['temperature'] ?? 0.7,
                        'max_tokens' => $options['max_tokens'] ?? 500,
                    ]);
                    
                    return [
                        'content' => $response->choices[0]->message->content,
                        'model' => $response->model,
                        'usage' => [
                            'prompt_tokens' => $response->usage->promptTokens,
                            'completion_tokens' => $response->usage->completionTokens,
                            'total_tokens' => $response->usage->totalTokens,
                        ],
                    ];
                
                case 'image':
                    $response = OpenAI::images()->create([
                        'model' => $model,
                        'prompt' => $prompt,
                        'n' => $options['n'] ?? 1,
                        'size' => $options['size'] ?? '1024x1024',
                        'response_format' => $options['response_format'] ?? 'url',
                    ]);
                    
                    $images = [];
                    foreach ($response->data as $image) {
                        $images[] = [
                            'url' => $image->url,
                            'revised_prompt' => $image->revisedPrompt ?? null,
                        ];
                    }
                    
                    return [
                        'images' => $images,
                        'created' => $response->created,
                    ];
                
                case 'embedding':
                    $response = OpenAI::embeddings()->create([
                        'model' => $model,
                        'input' => $prompt,
                    ]);
                    
                    return [
                        'embedding' => $response->embeddings[0]->embedding,
                        'model' => $response->model,
                        'usage' => [
                            'prompt_tokens' => $response->usage->promptTokens,
                            'total_tokens' => $response->usage->totalTokens,
                        ],
                    ];
                
                default:
                    return [
                        'error' => 'Servizio non supportato',
                        'message' => 'Il servizio deve essere uno tra: chat, image, embedding',
                    ];
            }
        } catch (\Exception $e) {
            return [
                'error' => 'Errore nella richiesta OpenAI',
                'message' => $e->getMessage(),
                'service' => $service,
                'model' => $model,
            ];
        }
    }
}
```

### 4. Integrazione con Stripe

```php
<?php
declare(strict_types=1);

namespace Modules\AI\MCP\Tools;

use JsonAllen\LaravelMCP\Tool;
use JsonAllen\LaravelMCP\ToolProperty;
use Stripe\StripeClient;

class StripeTool extends Tool
{
    /**
     * @var array<string>
     */
    protected array $allowedResources = [
        'customers',
        'products',
        'prices',
        'invoices',
        'subscriptions',
    ];

    /**
     * @var array<string>
     */
    protected array $allowedOperations = [
        'list',
        'retrieve',
        'create',
        'update',
    ];

    /**
     * @var StripeClient
     */
    protected StripeClient $stripe;

    /**
     * @param StripeClient|null $stripe
     */
    public function __construct(?StripeClient $stripe = null)
    {
        if ($stripe === null) {
            $this->stripe = new StripeClient(config('services.stripe.secret'));
        } else {
            $this->stripe = $stripe;
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'stripe';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Interagisce con l\'API di Stripe.';
    }

    /**
     * @return array<int, ToolProperty>
     */
    public function getProperties(): array
    {
        return [
            new ToolProperty(
                name: 'resource',
                type: 'string',
                description: 'Risorsa Stripe (customers, products, prices, invoices, subscriptions)',
                required: true
            ),
            new ToolProperty(
                name: 'operation',
                type: 'string',
                description: 'Operazione da eseguire (list, retrieve, create, update)',
                required: true
            ),
            new ToolProperty(
                name: 'id',
                type: 'string',
                description: 'ID della risorsa (per retrieve, update)',
                required: false
            ),
            new ToolProperty(
                name: 'data',
                type: 'object',
                description: 'Dati per l\'operazione (per create, update)',
                required: false
            ),
            new ToolProperty(
                name: 'options',
                type: 'object',
                description: 'Opzioni per l\'operazione (per list)',
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
        $resource = $parameters['resource'];
        $operation = $parameters['operation'];
        $id = $parameters['id'] ?? null;
        $data = $parameters['data'] ?? [];
        $options = $parameters['options'] ?? [];
        
        // Verifica che la risorsa sia valida
        if (!in_array($resource, $this->allowedResources)) {
            return [
                'error' => 'Risorsa non valida',
                'message' => 'La risorsa deve essere una tra: ' . implode(', ', $this->allowedResources),
            ];
        }
        
        // Verifica che l'operazione sia valida
        if (!in_array($operation, $this->allowedOperations)) {
            return [
                'error' => 'Operazione non valida',
                'message' => 'L\'operazione deve essere una tra: ' . implode(', ', $this->allowedOperations),
            ];
        }
        
        // Verifica che l'ID sia presente per le operazioni che lo richiedono
        if (in_array($operation, ['retrieve', 'update']) && $id === null) {
            return [
                'error' => 'ID mancante',
                'message' => 'L\'ID è richiesto per le operazioni retrieve e update',
            ];
        }
        
        // Esegui l'operazione
        try {
            switch ($operation) {
                case 'list':
                    $result = $this->stripe->{$resource}->all($options);
                    return [
                        'data' => $result->data,
                        'has_more' => $result->has_more,
                        'total_count' => $result->total_count ?? null,
                    ];
                
                case 'retrieve':
                    $result = $this->stripe->{$resource}->retrieve($id);
                    return $result->toArray();
                
                case 'create':
                    $result = $this->stripe->{$resource}->create($data);
                    return $result->toArray();
                
                case 'update':
                    $result = $this->stripe->{$resource}->update($id, $data);
                    return $result->toArray();
            }
            
            return [
                'error' => 'Operazione non implementata',
                'operation' => $operation,
            ];
        } catch (\Exception $e) {
            return [
                'error' => 'Errore nell\'operazione Stripe',
                'message' => $e->getMessage(),
                'resource' => $resource,
                'operation' => $operation,
            ];
        }
    }
}
```

## Sicurezza e Best Practices

L'integrazione di MCP con servizi esterni richiede particolare attenzione alla sicurezza. Ecco alcune best practices da seguire:

### 1. Gestione delle Credenziali

- **Secrets Management**: Utilizzare sistemi sicuri per la gestione delle credenziali come Laravel Vault o AWS Secrets Manager.
- **Rotazione delle Chiavi**: Implementare la rotazione periodica delle chiavi API.
- **Least Privilege**: Utilizzare account di servizio con i minimi privilegi necessari.

### 2. Controllo degli Accessi

- **Whitelist di Risorse**: Limitare l'accesso solo alle risorse e operazioni esplicitamente consentite.
- **Controllo delle Operazioni**: Consentire solo operazioni di lettura per default, limitando le operazioni di scrittura a casi specifici.
- **Autenticazione e Autorizzazione**: Implementare meccanismi di autenticazione per le richieste MCP e autorizzazione basata su ruoli.

### 3. Protezione dei Dati

- **Validazione degli Input**: Validare rigorosamente tutti gli input prima di utilizzarli nelle chiamate API.
- **Sanitizzazione**: Sanitizzare i dati di input per rimuovere caratteri potenzialmente dannosi.
- **Cifratura**: Cifrare i dati sensibili in transito e a riposo.

### 4. Limitazione delle Risorse

- **Rate Limiting**: Implementare limiti sul numero di richieste che possono essere effettuate in un determinato periodo.
- **Timeout**: Impostare timeout per le chiamate API per evitare operazioni troppo lunghe.
- **Quota Management**: Monitorare e limitare l'utilizzo delle risorse per prevenire costi eccessivi.

### 5. Logging e Monitoraggio

- **Audit Trail**: Registrare tutte le chiamate API effettuate tramite MCP.
- **Monitoraggio delle Performance**: Monitorare le performance delle chiamate API per identificare potenziali problemi.
- **Alerting**: Configurare alert per comportamenti sospetti o anomali.

### 6. Gestione degli Errori

- **Graceful Degradation**: Implementare meccanismi di fallback in caso di errori nei servizi esterni.
- **Retry Logic**: Implementare logiche di retry con backoff esponenziale per gestire errori temporanei.
- **Error Reporting**: Configurare sistemi di reporting degli errori per identificare e risolvere rapidamente i problemi.

## Risorse e Riferimenti

- [Documentazione Ufficiale MCP](../../../project_docs/references/mcp_documentation.md)
- [Laravel MCP SDK](../../../project_docs/references/laravel_mcp_sdk.md)
- [AWS MCP Server](../../../project_docs/references/aws_mcp_server.md)
- [GCP MCP Server](../../../project_docs/references/gcp_mcp_server.md)
- [HTTP MCP Server](../../../project_docs/references/http_mcp_server.md)
- [Stripe MCP Integration](../../../project_docs/references/stripe_mcp_integration.md)

Per ulteriori informazioni sull'implementazione di MCP in Laravel, consulta la [Guida all'Integrazione MCP](./MCP_INTEGRATION_GUIDE.md), i [Casi d'Uso di MCP](./MCP_CASI_USO.md) e l'[Integrazione MCP con Database](./MCP_DATABASE_INTEGRATION.md).

---

*Ultimo aggiornamento: Maggio 2025*
