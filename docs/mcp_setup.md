# Configurazione dei Server MCP per il Modulo AI

## Repository MCP Installato

Abbiamo installato l'intero repository ufficiale [modelcontextprotocol/servers](https://github.com/modelcontextprotocol/servers) nella directory condivisa `/var/www/html/_bases/mcp-servers`, permettendo così il riutilizzo di tutti i server MCP in tutti i progetti Windsurf/Xot.

Il repository include numerosi server MCP, tra cui:

1. **fetch** - Per effettuare richieste HTTP verso API esterne
2. **memory** - Per memorizzare e recuperare informazioni durante le conversazioni
3. **sequentialthinking** - Per risolvere problemi complessi passo dopo passo
4. **postgres** - Per interagire con database PostgreSQL
5. **redis** - Per cache, code e pub/sub in Laravel
6. **github** - Per interagire con repository GitHub
7. **filesystem** - Per operazioni sicure sul filesystem
8. **brave-search** - Per ricerche web e locali
9. **everything** - Server di riferimento con prompt, risorse e strumenti

E molti altri che possono essere utilizzati in base alle esigenze specifiche del progetto.

## Requisiti per l'Esecuzione

Per eseguire questi server MCP, sono necessari i seguenti requisiti:

- Node.js v18+ e npm
- Python 3.10+ (per alcuni server)
- Dipendenze specifiche per ciascun server

## Configurazione per Cursor IDE

Per utilizzare questi server MCP con Cursor IDE, è necessario configurare il file `.cursor/settings.json` nel progetto:

```json
{
  "mcp": {
    "servers": [
      {
        "name": "Laravel Fetch",
        "transport": "stdio",
        "command": "cd /var/www/html/_bases/mcp-servers/src/fetch && npm install && npm start",
        "cwd": "${workspaceFolder}"
      },
      {
        "name": "Laravel Memory",
        "transport": "stdio",
        "command": "cd /var/www/html/_bases/mcp-servers/src/memory && npm install && npm start",
        "cwd": "${workspaceFolder}"
      },
      {
        "name": "Sequential Thinking",
        "transport": "stdio",
        "command": "cd /var/www/html/_bases/mcp-servers/src/sequentialthinking && npm install && npm start",
        "cwd": "${workspaceFolder}"
      },
      {
        "name": "Postgres DB",
        "transport": "stdio",
        "command": "cd /var/www/html/_bases/mcp-servers/src/postgres && npm install && npm start",
        "cwd": "${workspaceFolder}"
      },
      {
        "name": "Redis Cache",
        "transport": "stdio",
        "command": "cd /var/www/html/_bases/mcp-servers/src/redis && npm install && npm start",
        "cwd": "${workspaceFolder}"
      },
      {
        "name": "GitHub Integration",
        "transport": "stdio",
        "command": "cd /var/www/html/_bases/mcp-servers/src/github && npm install && npm start",
        "cwd": "${workspaceFolder}"
      },
      {
        "name": "Filesystem Operations",
        "transport": "stdio",
        "command": "cd /var/www/html/_bases/mcp-servers/src/filesystem && npm install && npm start",
        "cwd": "${workspaceFolder}"
      }
    ]
  }
}
```

## Integrazione con Laravel

Per integrare questi server MCP con Laravel, consigliamo di utilizzare uno dei seguenti pacchetti:

1. **[InnoGE/laravel-mcp](https://github.com/InnoGE/laravel-mcp)** - Per sviluppo locale e prototipazione
2. **[opgginc/laravel-mcp-server](https://github.com/opgginc/laravel-mcp-server)** - Per ambienti di produzione con SSE

### Esempio di Implementazione con InnoGE/laravel-mcp

```php
<?php

namespace Modules\AI\App\Console\Commands;

use Illuminate\Console\Command;
use InnoGE\LaravelMcp\Commands\ServesMcpServer;
use Modules\AI\App\MCP\Tools\CustomAnalysisTool;
use Modules\User\Models\User;
use InnoGE\LaravelMcp\Resources\EloquentResourceProvider;

class McpServerCommand extends Command
{
    use ServesMcpServer;

    protected $signature = 'ai:mcp:serve';
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

## Casi d'Uso Specifici

### 1. Fetch per API Esterne

Il server `fetch` è particolarmente utile per integrare API esterne nelle tue applicazioni Laravel:

```php
// Esempio di strumento personalizzato che utilizza fetch
namespace Modules\AI\App\MCP\Tools;

use InnoGE\LaravelMcp\Tools\Tool;
use Illuminate\Support\Facades\Http;

class ExternalApiTool implements Tool
{
    public function getName(): string
    {
        return 'fetch-external-api';
    }

    public function getDescription(): string
    {
        return 'Recupera dati da API esterne';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'url' => [
                    'type' => 'string',
                    'description' => 'URL dell\'API esterna',
                ],
                'method' => [
                    'type' => 'string',
                    'enum' => ['GET', 'POST', 'PUT', 'DELETE'],
                    'description' => 'Metodo HTTP',
                ],
                'params' => [
                    'type' => 'object',
                    'description' => 'Parametri della richiesta',
                ],
            ],
            'required' => ['url', 'method'],
        ];
    }

    public function execute(array $arguments): string
    {
        $url = $arguments['url'];
        $method = strtolower($arguments['method']);
        $params = $arguments['params'] ?? [];
        
        $response = Http::$method($url, $params);
        
        return $response->body();
    }
}
```

### 2. Memory per Persistenza delle Conversazioni

Il server `memory` è utile per mantenere il contesto delle conversazioni:

```php
// Esempio di integrazione con il server memory
namespace Modules\AI\App\MCP\Tools;

use InnoGE\LaravelMcp\Tools\Tool;

class ConversationMemoryTool implements Tool
{
    public function getName(): string
    {
        return 'save-conversation-context';
    }

    public function getDescription(): string
    {
        return 'Salva il contesto della conversazione per riferimenti futuri';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'user_id' => [
                    'type' => 'string',
                    'description' => 'ID dell\'utente',
                ],
                'context' => [
                    'type' => 'string',
                    'description' => 'Contesto da salvare',
                ],
                'key' => [
                    'type' => 'string',
                    'description' => 'Chiave per recuperare il contesto',
                ],
            ],
            'required' => ['user_id', 'context', 'key'],
        ];
    }

    public function execute(array $arguments): string
    {
        $userId = $arguments['user_id'];
        $context = $arguments['context'];
        $key = $arguments['key'];
        
        // Implementazione del salvataggio del contesto
        // Questo potrebbe utilizzare il server memory MCP o un'implementazione personalizzata
        
        return json_encode(['success' => true, 'message' => 'Contesto salvato con successo']);
    }
}
```

### 3. Sequential Thinking per Risoluzione di Problemi Complessi

Il server `sequentialthinking` è utile per risolvere problemi complessi passo dopo passo:

```php
// Esempio di integrazione con il server sequentialthinking
namespace Modules\AI\App\MCP\Tools;

use InnoGE\LaravelMcp\Tools\Tool;

class ComplexProblemSolverTool implements Tool
{
    public function getName(): string
    {
        return 'solve-complex-problem';
    }

    public function getDescription(): string
    {
        return 'Risolve problemi complessi utilizzando un approccio passo-passo';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'problem' => [
                    'type' => 'string',
                    'description' => 'Descrizione del problema da risolvere',
                ],
                'context' => [
                    'type' => 'string',
                    'description' => 'Contesto aggiuntivo per la risoluzione del problema',
                ],
            ],
            'required' => ['problem'],
        ];
    }

    public function execute(array $arguments): string
    {
        $problem = $arguments['problem'];
        $context = $arguments['context'] ?? '';
        
        // Implementazione della risoluzione del problema
        // Questo potrebbe utilizzare il server sequentialthinking MCP
        
        return json_encode([
            'steps' => [
                'Analisi del problema',
                'Identificazione delle possibili soluzioni',
                'Valutazione delle soluzioni',
                'Implementazione della soluzione ottimale',
            ],
            'solution' => 'Soluzione proposta per il problema',
        ]);
    }
}
```

## Considerazioni sulla Sicurezza

Quando utilizzi i server MCP, è importante considerare le seguenti misure di sicurezza:

1. **Autenticazione**: Implementa meccanismi di autenticazione per tutti i server MCP
2. **Validazione degli input**: Verifica sempre gli input prima dell'esecuzione
3. **Limitazione dell'accesso**: Configura i server per accedere solo alle risorse necessarie
4. **Logging**: Registra tutte le interazioni per scopi di audit e debugging

## Risorse Correlate

- [Panoramica MCP](./MCP_OVERVIEW.md)
- [Implementazioni MCP](./MCP_IMPLEMENTAZIONI.md)
- [Pacchetti MCP](./MCP_PACKAGES.md)
- [Risorse MCP](./MCP_RESOURCES.md)
