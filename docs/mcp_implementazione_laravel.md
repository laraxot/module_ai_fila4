# Implementazione MCP in Laravel

## Introduzione

Questo documento fornisce una guida dettagliata all'implementazione del Model Context Protocol (MCP) in applicazioni Laravel, con particolare attenzione alle best practices di sviluppo, all'integrazione con l'architettura esistente e alla conformità con gli standard del progetto.

## Indice

1. [Architettura dell'Implementazione](#architettura-dellimplementazione)
2. [Installazione e Configurazione](#installazione-e-configurazione)
3. [Sviluppo di Strumenti MCP](#sviluppo-di-strumenti-mcp)
4. [Integrazione con l'Architettura Esistente](#integrazione-con-larchitettura-esistente)
5. [Testing e Debugging](#testing-e-debugging)
6. [Deployment e Monitoraggio](#deployment-e-monitoraggio)
7. [Conformità con PHPStan Livello 9](#conformità-con-phpstan-livello-9)

## Architettura dell'Implementazione

L'implementazione MCP in Laravel segue un'architettura modulare che si integra con il sistema esistente:

```
Modules/
└── AI/
    ├── app/
    │   ├── Console/
    │   │   └── Commands/
    │   │       └── MCPServeCommand.php
    │   ├── Events/
    │   │   └── MCPInteractionEvent.php
    │   ├── Listeners/
    │   │   └── MCPInteractionListener.php
    │   ├── MCP/
    │   │   ├── Server/
    │   │   │   ├── MCPServer.php
    │   │   │   └── Transports/
    │   │   │       ├── SSETransport.php
    │   │   │       └── StdioTransport.php
    │   │   └── Tools/
    │   │       ├── AbstractTool.php
    │   │       └── [StrumentiSpecifici].php
    │   ├── Providers/
    │   │   └── MCPServiceProvider.php
    │   └── Services/
    │       └── MCPSecurityService.php
    ├── config/
    │   └── mcp.php
    ├── docs/
    │   ├── MCP_INTEGRATION_GUIDE.md
    │   ├── MCP_CASI_USO.md
    │   └── MCP_IMPLEMENTAZIONE_LARAVEL.md
    ├── resources/
    │   └── views/
    │       └── mcp/
    │           └── inspector.blade.php
    └── routes/
        └── api.php
```

### Componenti Principali

1. **MCPServer**: Gestisce la comunicazione tra l'applicazione Laravel e gli LLM
2. **Transports**: Implementa diversi protocolli di comunicazione (SSE, STDIO)
3. **Tools**: Strumenti specifici che espongono funzionalità dell'applicazione agli LLM
4. **Events e Listeners**: Sistema di eventi per monitorare e reagire alle interazioni MCP
5. **Services**: Servizi di supporto per sicurezza, logging e altre funzionalità trasversali

## Installazione e Configurazione

### Prerequisiti

- Laravel 8.x o superiore
- PHP 8.0 o superiore
- Composer
- Laravel Octane (consigliato per implementazioni SSE)

### Installazione

1. Installare un pacchetto MCP per Laravel:

```bash
composer require innoge/laravel-mcp
# oppure
composer require opgginc/laravel-mcp-server
```

2. Pubblicare i file di configurazione:

```bash
php artisan vendor:publish --tag=mcp-config
```

3. Configurare il file `.env`:

```
MCP_CONNECTION=sse
MCP_PROVIDER=anthropic
MCP_API_KEY=your_api_key_here
MCP_MODEL=claude-3-opus-20240229
```

### Configurazione

Il file di configurazione `config/mcp.php` contiene tutte le impostazioni necessarie:

```php
<?php
declare(strict_types=1);

return [
    'default' => env('MCP_CONNECTION', 'sse'),
    
    'connections' => [
        'stdio' => [
            'driver' => 'stdio',
        ],
        'sse' => [
            'driver' => 'sse',
            'path' => '/mcp/sse',
            'middleware' => ['web', 'auth'],
        ],
    ],
    
    'providers' => [
        'anthropic' => [
            'api_key' => env('ANTHROPIC_API_KEY'),
            'model' => env('ANTHROPIC_MODEL', 'claude-3-opus-20240229'),
        ],
        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'model' => env('OPENAI_MODEL', 'gpt-4-turbo'),
        ],
    ],
    
    'tools' => [
        // Elenco degli strumenti abilitati
        \Modules\AI\MCP\Tools\GetUserInfo::class,
        \Modules\AI\MCP\Tools\AnalyzeLogFile::class,
        \Modules\AI\MCP\Tools\RunArtisanCommand::class,
    ],
    
    'security' => [
        'allowed_ips' => env('MCP_ALLOWED_IPS', '127.0.0.1'),
        'rate_limit' => [
            'enabled' => true,
            'max_attempts' => 60,
            'decay_minutes' => 1,
        ],
    ],
    
    'logging' => [
        'enabled' => true,
        'channel' => 'mcp',
        'level' => 'info',
    ],
];
```

## Sviluppo di Strumenti MCP

### Struttura Base di uno Strumento

Gli strumenti MCP devono seguire una struttura standard:

```php
<?php
declare(strict_types=1);

namespace Modules\AI\MCP\Tools;

use Modules\AI\MCP\Tools\AbstractTool;
use Modules\AI\MCP\Tools\ToolProperty;

class ExampleTool extends AbstractTool
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'example_tool';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Descrizione dettagliata dello strumento.';
    }

    /**
     * @return array<int, ToolProperty>
     */
    public function getProperties(): array
    {
        return [
            new ToolProperty(
                name: 'param1',
                type: 'string',
                description: 'Descrizione del parametro',
                required: true
            ),
            new ToolProperty(
                name: 'param2',
                type: 'integer',
                description: 'Altro parametro',
                required: false
            ),
        ];
    }

    /**
     * @param array<string, mixed> $parameters
     * @return mixed
     */
    public function handle(array $parameters): mixed
    {
        // Implementazione della logica dello strumento
        $param1 = $parameters['param1'];
        $param2 = $parameters['param2'] ?? null;
        
        // Logica di business
        
        return [
            'result' => 'Risultato dell\'operazione',
            'additional_info' => [
                'key1' => 'value1',
                'key2' => 'value2',
            ],
        ];
    }
}
```

### Generazione di Strumenti

Per semplificare la creazione di nuovi strumenti, è possibile utilizzare un comando Artisan:

```php
<?php
declare(strict_types=1);

namespace Modules\AI\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;

class MakeMCPToolCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'make:mcp-tool {name : Il nome dello strumento MCP}';

    /**
     * @var string
     */
    protected $description = 'Crea un nuovo strumento MCP';

    /**
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * @param \Illuminate\Filesystem\Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        $name = $this->argument('name');
        $className = Str::studly($name) . 'Tool';
        $toolName = Str::snake($name);
        
        $stub = $this->files->get(__DIR__ . '/stubs/mcp-tool.stub');
        $stub = str_replace(
            ['{{className}}', '{{toolName}}'],
            [$className, $toolName],
            $stub
        );
        
        $path = app_path('MCP/Tools/' . $className . '.php');
        $this->makeDirectory($path);
        $this->files->put($path, $stub);
        
        $this->info("Strumento MCP [{$className}] creato con successo.");
        
        if ($this->confirm('Vuoi registrare lo strumento nel file di configurazione?')) {
            $this->registerTool($className);
        }
    }

    /**
     * @param string $path
     * @return string
     */
    protected function makeDirectory(string $path): string
    {
        $directory = dirname($path);
        
        if (!$this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory, 0755, true);
        }
        
        return $directory;
    }

    /**
     * @param string $className
     * @return void
     */
    protected function registerTool(string $className): void
    {
        $configPath = config_path('mcp.php');
        $config = $this->files->get($configPath);
        
        $toolClass = "\\App\\MCP\\Tools\\{$className}::class";
        
        if (strpos($config, $toolClass) === false) {
            $pattern = "/'tools' => \[\s*/";
            $replacement = "'tools' => [\n        {$toolClass},\n        ";
            $config = preg_replace($pattern, $replacement, $config);
            
            $this->files->put($configPath, $config);
            $this->info("Strumento registrato nel file di configurazione.");
        } else {
            $this->warn("Lo strumento è già registrato nel file di configurazione.");
        }
    }
}
```

## Integrazione con l'Architettura Esistente

### Service Provider

Il Service Provider registra tutti i componenti MCP nel container di Laravel:

```php
<?php
declare(strict_types=1);

namespace Modules\AI\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\AI\MCP\Server\MCPServer;
use Modules\AI\MCP\Server\Transports\SSETransport;
use Modules\AI\MCP\Server\Transports\StdioTransport;
use Modules\AI\Services\MCPSecurityService;

class MCPServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/mcp.php', 'mcp'
        );
        
        $this->app->singleton(MCPServer::class, function ($app) {
            $config = $app['config']['mcp'];
            $connection = $config['default'];
            
            $transport = match ($connection) {
                'sse' => new SSETransport($config['connections']['sse']),
                'stdio' => new StdioTransport(),
                default => throw new \InvalidArgumentException("Unsupported MCP connection: {$connection}"),
            };
            
            return new MCPServer($transport, $config);
        });
        
        $this->app->singleton(MCPSecurityService::class);
    }

    /**
     * @return void
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/mcp.php' => config_path('mcp.php'),
            ], 'mcp-config');
            
            $this->commands([
                \Modules\AI\Console\Commands\MCPServeCommand::class,
                \Modules\AI\Console\Commands\MakeMCPToolCommand::class,
            ]);
        }
        
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'ai');
    }
}
```

### Integrazione con Moduli Esistenti

Per integrare MCP con i moduli esistenti, è importante seguire i principi di disaccoppiamento:

```php
<?php
declare(strict_types=1);

namespace Modules\AI\MCP\Tools;

use Modules\AI\MCP\Tools\AbstractTool;
use Modules\AI\MCP\Tools\ToolProperty;
use Modules\Xot\Contracts\UserContract;
use Illuminate\Contracts\Container\Container;

class GetUserInfo extends AbstractTool
{
    /**
     * @var \Modules\Xot\Contracts\UserContract
     */
    protected $userModel;
    
    /**
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * @param \Illuminate\Contracts\Container\Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->userModel = $container->make(UserContract::class);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'get_user_info';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Recupera informazioni su un utente.';
    }

    /**
     * @return array<int, ToolProperty>
     */
    public function getProperties(): array
    {
        return [
            new ToolProperty(
                name: 'user_id',
                type: 'integer',
                description: 'ID dell\'utente',
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
        $userId = (int) $parameters['user_id'];
        $user = $this->userModel::findOrFail($userId);
        
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => $user->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
```

## Testing e Debugging

### Test Unitari

È importante scrivere test unitari per ogni strumento MCP:

```php
<?php
declare(strict_types=1);

namespace Tests\Unit\Modules\AI\MCP\Tools;

use Tests\TestCase;
use Modules\AI\MCP\Tools\GetUserInfo;
use Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Contracts\Container\Container;

class GetUserInfoTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var \Modules\AI\MCP\Tools\GetUserInfo
     */
    protected $tool;
    
    /**
     * @var \Modules\User\Models\User
     */
    protected $user;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        
        $container = $this->app->make(Container::class);
        $this->tool = new GetUserInfo($container);
    }

    /**
     * @test
     * @return void
     */
    public function it_returns_user_info(): void
    {
        $result = $this->tool->handle(['user_id' => $this->user->id]);
        
        $this->assertEquals($this->user->id, $result['id']);
        $this->assertEquals('Test User', $result['name']);
        $this->assertEquals('test@example.com', $result['email']);
        $this->assertArrayHasKey('created_at', $result);
    }

    /**
     * @test
     * @return void
     */
    public function it_throws_exception_for_nonexistent_user(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        
        $this->tool->handle(['user_id' => 999]);
    }
}
```

### MCP Inspector

Per testare manualmente gli strumenti MCP, è possibile utilizzare MCP Inspector:

```php
<?php
declare(strict_types=1);

namespace Modules\AI\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class MCPInspectorController extends Controller
{
    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('ai::mcp.inspector', [
            'sseUrl' => url('/mcp/sse'),
            'tools' => config('mcp.tools'),
        ]);
    }
}
```

## Deployment e Monitoraggio

### Configurazione Octane

Per ottenere le migliori performance con MCP, specialmente con implementazioni SSE:

```php
<?php
declare(strict_types=1);

// config/octane.php

return [
    'server' => env('OCTANE_SERVER', 'swoole'),
    
    'listeners' => [
        \Modules\AI\Listeners\MCPConnectionListener::class,
    ],
    
    'swoole' => [
        'options' => [
            'http_compression' => true,
            'http_compression_level' => 6,
            'compression_min_length' => 20,
            'open_http2_protocol' => true,
            'worker_num' => 8,
            'max_request_execution_time' => 60,
        ],
    ],
];
```

### Logging e Monitoraggio

Implementazione di un sistema di logging specifico per MCP:

```php
<?php
declare(strict_types=1);

namespace Modules\AI\Listeners;

use Illuminate\Support\Facades\Log;
use Modules\AI\Events\MCPInteractionEvent;

class MCPInteractionListener
{
    /**
     * @param \Modules\AI\Events\MCPInteractionEvent $event
     * @return void
     */
    public function handle(MCPInteractionEvent $event): void
    {
        $tool = $event->tool;
        $parameters = $event->parameters;
        $result = $event->result;
        $userId = $event->userId;
        
        Log::channel('mcp')->info('MCP Interaction', [
            'tool' => $tool,
            'parameters' => $parameters,
            'result_type' => is_array($result) ? 'array' : gettype($result),
            'user_id' => $userId,
            'timestamp' => now()->toIso8601String(),
            'ip' => request()->ip(),
            'duration_ms' => $event->duration,
        ]);
    }
}
```

## Conformità con PHPStan Livello 9

Per garantire la conformità con PHPStan livello 9, è necessario seguire alcune best practices:

### Tipizzazione Corretta

```php
<?php
declare(strict_types=1);

namespace Modules\AI\MCP\Tools;

/**
 * Classe che rappresenta una proprietà di uno strumento MCP.
 */
class ToolProperty
{
    /**
     * @var string
     */
    public string $name;
    
    /**
     * @var string
     */
    public string $type;
    
    /**
     * @var string
     */
    public string $description;
    
    /**
     * @var bool
     */
    public bool $required;
    
    /**
     * @var mixed|null
     */
    public mixed $default;

    /**
     * @param string $name Nome della proprietà
     * @param string $type Tipo della proprietà (string, integer, boolean, array, object)
     * @param string $description Descrizione della proprietà
     * @param bool $required Se la proprietà è obbligatoria
     * @param mixed|null $default Valore predefinito
     */
    public function __construct(
        string $name,
        string $type,
        string $description,
        bool $required = false,
        mixed $default = null
    ) {
        $this->name = $name;
        $this->type = $type;
        $this->description = $description;
        $this->required = $required;
        $this->default = $default;
    }

    /**
     * Converte la proprietà in un array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'description' => $this->description,
            'required' => $this->required,
            'default' => $this->default,
        ];
    }
}
```

### Gestione dei Valori Mixed

```php
<?php
declare(strict_types=1);

namespace Modules\AI\MCP\Server;

use Modules\AI\MCP\Tools\AbstractTool;

class MCPServer
{
    /**
     * @var array<int, AbstractTool>
     */
    protected array $tools = [];
    
    /**
     * @var array<string, mixed>
     */
    protected array $config;
    
    /**
     * @var \Modules\AI\MCP\Server\TransportInterface
     */
    protected $transport;

    /**
     * @param \Modules\AI\MCP\Server\TransportInterface $transport
     * @param array<string, mixed> $config
     */
    public function __construct(TransportInterface $transport, array $config)
    {
        $this->transport = $transport;
        $this->config = $config;
        
        $this->registerTools();
    }
    
    /**
     * Registra gli strumenti configurati.
     *
     * @return void
     */
    protected function registerTools(): void
    {
        $toolClasses = $this->config['tools'] ?? [];
        
        foreach ($toolClasses as $toolClass) {
            $tool = app($toolClass);
            
            if ($tool instanceof AbstractTool) {
                $this->tools[] = $tool;
            }
        }
    }
    
    /**
     * Gestisce una richiesta MCP.
     *
     * @param mixed $request
     * @return mixed
     */
    public function handleRequest(mixed $request): mixed
    {
        if (!is_array($request)) {
            return $this->createErrorResponse('Invalid request format');
        }
        
        $toolName = $request['tool'] ?? null;
        $parameters = $request['parameters'] ?? [];
        
        if (!is_string($toolName)) {
            return $this->createErrorResponse('Tool name must be a string');
        }
        
        if (!is_array($parameters)) {
            return $this->createErrorResponse('Parameters must be an array');
        }
        
        $tool = $this->findTool($toolName);
        
        if (!$tool) {
            return $this->createErrorResponse("Tool not found: {$toolName}");
        }
        
        try {
            $result = $tool->handle($parameters);
            return $this->createSuccessResponse($result);
        } catch (\Exception $e) {
            return $this->createErrorResponse($e->getMessage());
        }
    }
    
    /**
     * Trova uno strumento per nome.
     *
     * @param string $name
     * @return \Modules\AI\MCP\Tools\AbstractTool|null
     */
    protected function findTool(string $name): ?AbstractTool
    {
        foreach ($this->tools as $tool) {
            if ($tool->getName() === $name) {
                return $tool;
            }
        }
        
        return null;
    }
    
    /**
     * Crea una risposta di successo.
     *
     * @param mixed $data
     * @return array<string, mixed>
     */
    protected function createSuccessResponse(mixed $data): array
    {
        return [
            'status' => 'success',
            'data' => $data,
        ];
    }
    
    /**
     * Crea una risposta di errore.
     *
     * @param string $message
     * @return array<string, mixed>
     */
    protected function createErrorResponse(string $message): array
    {
        return [
            'status' => 'error',
            'message' => $message,
        ];
    }
}
```

---

## Conclusioni

L'implementazione di MCP in Laravel offre numerose opportunità per migliorare le applicazioni esistenti e sviluppare nuove funzionalità basate su AI. Seguendo le best practices descritte in questo documento, è possibile creare un'implementazione robusta, sicura e conforme agli standard del progetto.

Per ulteriori dettagli sui casi d'uso specifici, consultare il documento [MCP_CASI_USO.md](./MCP_CASI_USO.md).

---

*Ultimo aggiornamento: Maggio 2025*
