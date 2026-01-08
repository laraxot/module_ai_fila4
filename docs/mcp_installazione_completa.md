# Guida Completa all'Installazione e Configurazione dei Server MCP

## Indice
1. [Introduzione](#introduzione)
2. [Struttura delle Directory](#struttura-delle-directory)
3. [Installazione del Repository MCP](#installazione-del-repository-mcp)
4. [Configurazione dei Server MCP](#configurazione-dei-server-mcp)
5. [Installazione delle Dipendenze](#installazione-delle-dipendenze)
6. [Configurazione di Cursor IDE](#configurazione-di-cursor-ide)
7. [Integrazione con Laravel](#integrazione-con-laravel)
8. [Troubleshooting](#troubleshooting)
9. [Aggiornamento dei Server MCP](#aggiornamento-dei-server-mcp)
10. [Risorse Correlate](#risorse-correlate)

## Introduzione

Questa guida fornisce istruzioni dettagliate per installare, configurare e utilizzare i server Model Context Protocol (MCP) con i progetti Windsurf/Xot. Il repository MCP è installato in una posizione centralizzata per permettere il riutilizzo dei server in tutti i progetti.

## Struttura delle Directory

Il repository MCP è installato nella seguente struttura:

```
/var/www/html/_bases/
└── mcp-servers/               # Directory principale del repository MCP
    ├── src/                   # Contiene tutti i server MCP
    │   ├── fetch/            # Server per richieste HTTP
    │   ├── memory/           # Server per memorizzazione contestuale
    │   ├── sequentialthinking/ # Server per risoluzione problemi complessi
    │   ├── postgres/         # Server per database PostgreSQL
    │   ├── redis/            # Server per cache e code
    │   ├── github/           # Server per integrazione GitHub
    │   ├── filesystem/       # Server per operazioni sul filesystem
    │   └── ...               # Altri server disponibili
    ├── package.json          # Dipendenze del repository
    └── README.md             # Documentazione ufficiale
```

## Installazione del Repository MCP

Per installare il repository MCP in una nuova macchina, seguire questi passaggi:

```bash
# 1. Creare la directory per i server MCP
mkdir -p /var/www/html/_bases/mcp-servers

# 2. Clonare il repository ufficiale
git clone https://github.com/modelcontextprotocol/servers.git /tmp/mcp-servers

# 3. Copiare i file nella directory di destinazione
cp -r /tmp/mcp-servers/* /var/www/html/_bases/mcp-servers/

# 4. Verificare l'installazione
ls -la /var/www/html/_bases/mcp-servers/src
```

## Configurazione dei Server MCP

Ogni server MCP richiede una configurazione specifica. Di seguito sono riportate le configurazioni per i server più comuni:

### Fetch Server

Il server Fetch permette di effettuare richieste HTTP verso API esterne.

```bash
cd /var/www/html/_bases/mcp-servers/src/fetch
npm install
```

### Memory Server

Il server Memory permette di memorizzare e recuperare informazioni durante le conversazioni.

```bash
cd /var/www/html/_bases/mcp-servers/src/memory
npm install
```

### Postgres Server

Il server Postgres permette di interagire con database PostgreSQL.

```bash
cd /var/www/html/_bases/mcp-servers/src/postgres
npm install
```

### Redis Server

Il server Redis permette di utilizzare cache, code e pub/sub.

```bash
cd /var/www/html/_bases/mcp-servers/src/redis
npm install
```

### GitHub Server

Il server GitHub permette di interagire con repository GitHub.

```bash
cd /var/www/html/_bases/mcp-servers/src/github
npm install
```

## Installazione delle Dipendenze

Per installare tutte le dipendenze necessarie per i server MCP, eseguire:

```bash
# Installare le dipendenze globali
cd /var/www/html/_bases/mcp-servers
npm install

# Installare le dipendenze per ogni server
find ./src -type f -name "package.json" -exec sh -c 'cd "$(dirname "{}")" && echo "Installing dependencies in $(pwd)" && npm install' \;
```

## Configurazione di Cursor IDE

Per utilizzare i server MCP con Cursor IDE, è necessario configurare il file `.cursor/settings.json` nel progetto. Creare questo file se non esiste:

```bash
mkdir -p /var/www/html/_bases/base_predict_fila3_mono/.cursor
```

Quindi, aggiungere la seguente configurazione:

```json
{
  "mcp": {
    "servers": [
      {
        "name": "Laravel Fetch",
        "transport": "stdio",
        "command": "cd /var/www/html/_bases/mcp-servers/src/fetch && npm start",
        "cwd": "${workspaceFolder}"
      },
      {
        "name": "Laravel Memory",
        "transport": "stdio",
        "command": "cd /var/www/html/_bases/mcp-servers/src/memory && npm start",
        "cwd": "${workspaceFolder}"
      },
      {
        "name": "Sequential Thinking",
        "transport": "stdio",
        "command": "cd /var/www/html/_bases/mcp-servers/src/sequentialthinking && npm start",
        "cwd": "${workspaceFolder}"
      },
      {
        "name": "Postgres DB",
        "transport": "stdio",
        "command": "cd /var/www/html/_bases/mcp-servers/src/postgres && npm start",
        "cwd": "${workspaceFolder}"
      },
      {
        "name": "Redis Cache",
        "transport": "stdio",
        "command": "cd /var/www/html/_bases/mcp-servers/src/redis && npm start",
        "cwd": "${workspaceFolder}"
      },
      {
        "name": "GitHub Integration",
        "transport": "stdio",
        "command": "cd /var/www/html/_bases/mcp-servers/src/github && npm start",
        "cwd": "${workspaceFolder}"
      },
      {
        "name": "Filesystem Operations",
        "transport": "stdio",
        "command": "cd /var/www/html/_bases/mcp-servers/src/filesystem && npm start",
        "cwd": "${workspaceFolder}"
      }
    ]
  }
}
```

## Integrazione con Laravel

Per integrare i server MCP con Laravel, è necessario installare uno dei seguenti pacchetti:

### InnoGE/laravel-mcp (Sviluppo Locale)

```bash
composer require innoge/laravel-mcp
```

Creare un comando Artisan per servire il server MCP:

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

### opgginc/laravel-mcp-server (Produzione)

```bash
composer require opgginc/laravel-mcp-server
```

Pubblicare la configurazione:

```bash
php artisan vendor:publish --provider="OPGG\\LaravelMcpServer\\LaravelMcpServerServiceProvider"
```

Configurare il file `config/mcp-server.php`:

```php
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
        \Modules\AI\App\Http\Middleware\AuthenticateMcpRequests::class,
    ],
];
```

## Troubleshooting

### Problemi di Installazione

Se incontri problemi durante l'installazione dei server MCP, prova i seguenti passaggi:

1. **Errori di dipendenze Node.js**:
   ```bash
   cd /var/www/html/_bases/mcp-servers
   rm -rf node_modules
   npm cache clean --force
   npm install
   ```

2. **Errori di permessi**:
   ```bash
   sudo chown -R $(whoami):$(whoami) /var/www/html/_bases/mcp-servers
   ```

3. **Errori di porta in uso**:
   Modifica la porta nel file di configurazione del server specifico o termina il processo che sta utilizzando la porta.

### Problemi di Avvio dei Server

Se un server MCP non si avvia correttamente:

1. **Verifica i log**:
   ```bash
   cd /var/www/html/_bases/mcp-servers/src/[nome-server]
   npm start > server.log 2>&1
   cat server.log
   ```

2. **Verifica le dipendenze**:
   ```bash
   cd /var/www/html/_bases/mcp-servers/src/[nome-server]
   npm list
   ```

3. **Reinstalla il server**:
   ```bash
   cd /var/www/html/_bases/mcp-servers/src/[nome-server]
   rm -rf node_modules
   npm install
   ```

## Aggiornamento dei Server MCP

Per aggiornare i server MCP all'ultima versione:

```bash
# 1. Backup della configurazione esistente
cp -r /var/www/html/_bases/mcp-servers/src/*/config /tmp/mcp-config-backup/

# 2. Clonare l'ultima versione del repository
git clone https://github.com/modelcontextprotocol/servers.git /tmp/mcp-servers-new

# 3. Sostituire i file
rm -rf /var/www/html/_bases/mcp-servers
cp -r /tmp/mcp-servers-new /var/www/html/_bases/mcp-servers

# 4. Ripristinare la configurazione
cp -r /tmp/mcp-config-backup/* /var/www/html/_bases/mcp-servers/src/

# 5. Installare le dipendenze
cd /var/www/html/_bases/mcp-servers
npm install
find ./src -type f -name "package.json" -exec sh -c 'cd "$(dirname "{}")" && echo "Installing dependencies in $(pwd)" && npm install' \;
```

## Risorse Correlate

- [Panoramica MCP](./MCP_OVERVIEW.md)
- [Implementazioni MCP](./MCP_IMPLEMENTAZIONI.md)
- [Pacchetti MCP](./MCP_PACKAGES.md)
- [Risorse MCP](./MCP_RESOURCES.md)
- [Configurazione MCP](./MCP_SETUP.md)

Per ulteriori informazioni, consultare la [documentazione ufficiale MCP](https://modelcontextprotocol.io/introduction).
