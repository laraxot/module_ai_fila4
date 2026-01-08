# Configurazione MCP Server per Editor in base_predict_fila3_mono

## Panoramica

Questo documento descrive la configurazione ottimizzata degli MCP server per l'editor utilizzato nel progetto base_predict_fila3_mono. La configurazione è stata progettata per sfruttare i server MCP installati nella directory centralizzata `/var/www/html/_bases/mcp-servers`.

## Server MCP Utilizzati

I seguenti server MCP sono stati configurati per l'uso con questo progetto:

1. **Sequential Thinking** - Per la risoluzione di problemi complessi passo dopo passo
2. **Memory** - Per memorizzare e recuperare informazioni durante le conversazioni
3. **Fetch** - Per effettuare richieste HTTP verso API esterne
4. **Filesystem** - Per operazioni sicure sul filesystem
5. **Postgres** - Per interagire con database PostgreSQL
6. **Redis** - Per cache, code e pub/sub in Laravel

## File di Configurazione

### 1. Configurazione Cursor IDE

La configurazione per Cursor IDE è definita nel file `.cursor/settings.json` del progetto:

```json
{
  "mcp": {
    "servers": [
      {
        "name": "sequential-thinking",
        "transport": "stdio",
        "command": "npx -y @modelcontextprotocol/server-sequential-thinking",
        "cwd": "${workspaceFolder}"
      },
      {
        "name": "memory",
        "transport": "stdio",
        "command": "npx -y @modelcontextprotocol/server-memory",
        "cwd": "${workspaceFolder}"
      },
      {
        "name": "fetch",
        "transport": "stdio",
        "command": "npx -y @modelcontextprotocol/server-fetch",
        "cwd": "${workspaceFolder}"
      },
      {
        "name": "filesystem",
        "transport": "stdio",
        "command": "npx -y @modelcontextprotocol/server-filesystem",
        "cwd": "${workspaceFolder}"
      },
      {
        "name": "postgres",
        "transport": "stdio",
        "command": "npx -y @modelcontextprotocol/server-postgres",
        "cwd": "${workspaceFolder}"
      },
      {
        "name": "redis",
        "transport": "stdio",
        "command": "npx -y @modelcontextprotocol/server-redis",
        "cwd": "${workspaceFolder}"
      },
      {
        "name": "puppeteer",
        "transport": "stdio",
        "command": "npx -y @modelcontextprotocol/server-puppeteer",
        "cwd": "${workspaceFolder}"
      }
    ]
  }
}
```

### 2. Configurazione Windsurf

La configurazione per Windsurf è definita nel file `/home/zorin/.codeium/windsurf/mcp_config.json`:

```json
{
  "mcp": {
    "servers": {
      "default": {
        "host": "localhost",
        "port": 3000,
        "protocol": "http",
        "timeout": 30000,
        "retryAttempts": 3,
        "retryDelay": 1000
      },
      "development": {
        "host": "localhost",
        "port": 3001,
        "protocol": "http",
        "timeout": 30000,
        "retryAttempts": 3,
        "retryDelay": 1000
      },
      "production": {
        "host": "localhost",
        "port": 3002,
        "protocol": "http",
        "timeout": 30000,
        "retryAttempts": 3,
        "retryDelay": 1000
      },
      "sequential-thinking": {
        "type": "stdio",
        "command": "npx",
        "args": ["-y", "@modelcontextprotocol/server-sequential-thinking"]
      },
      "memory": {
        "type": "stdio",
        "command": "npx",
        "args": ["-y", "@modelcontextprotocol/server-memory"]
      },
      "fetch": {
        "type": "stdio",
        "command": "npx",
        "args": ["-y", "@modelcontextprotocol/server-fetch"]
      },
      "filesystem": {
        "type": "stdio",
        "command": "npx",
        "args": ["-y", "@modelcontextprotocol/server-filesystem"]
      },
      "postgres": {
        "type": "stdio",
        "command": "npx",
        "args": ["-y", "@modelcontextprotocol/server-postgres"]
      },
      "redis": {
        "type": "stdio",
        "command": "npx",
        "args": ["-y", "@modelcontextprotocol/server-redis"]
      },
      "puppeteer": {
        "command": "npx",
        "args": ["-y", "@modelcontextprotocol/server-puppeteer"],
        "env": {}
      }
    },
    "logging": {
      "level": "info",
      "file": "/var/log/mcp.log",
      "maxSize": 10485760,
      "maxFiles": 5
    },
    "security": {
      "allowedOrigins": ["*"],
      "allowedMethods": ["GET", "POST", "PUT", "DELETE", "OPTIONS"],
      "allowedHeaders": ["Content-Type", "Authorization"],
      "maxRequestSize": 10485760
    },
    "cache": {
      "enabled": true,
      "ttl": 3600,
      "maxSize": 104857600
    },
    "monitoring": {
      "enabled": true,
      "metrics": {
        "cpu": true,
        "memory": true,
        "disk": true,
        "network": true
      },
      "alerting": {
        "enabled": true,
        "thresholds": {
          "cpu": 80,
          "memory": 80,
          "disk": 80
        }
      }
    }
  }
}
```

### 3. Configurazione Cursor MCP

La configurazione per Cursor MCP è definita nel file `/mnt/c/Users/Marco/.cursor/mcp.json`:

```json
{
  "mcpServers": {
    "sequential-thinking": {
      "type": "stdio",
      "command": "npx",
      "args": ["-y", "@modelcontextprotocol/server-sequential-thinking"]
    },
    "memory": {
      "type": "stdio",
      "command": "npx",
      "args": ["-y", "@modelcontextprotocol/server-memory"]
    },
    "fetch": {
      "type": "stdio",
      "command": "npx",
      "args": ["-y", "@modelcontextprotocol/server-fetch"]
    },
    "filesystem": {
      "type": "stdio",
      "command": "npx",
      "args": ["-y", "@modelcontextprotocol/server-filesystem"]
    },
    "postgres": {
      "type": "stdio",
      "command": "npx",
      "args": ["-y", "@modelcontextprotocol/server-postgres"]
    },
    "redis": {
      "type": "stdio",
      "command": "npx",
      "args": ["-y", "@modelcontextprotocol/server-redis"]
    },
    "puppeteer": {
      "command": "npx",
      "args": ["-y", "@modelcontextprotocol/server-puppeteer"],
      "env": {}
    },
    "mysql": {
      "type": "stdio",
      "command": "uvx",
      "args": [
        "--from",
        "mysql-mcp-server",
        "mysql_mcp_server"
      ],
      "env": {
        "MYSQL_HOST": "localhost",
        "MYSQL_PORT": "3306",
        "MYSQL_USER": "marco",
        "MYSQL_PASSWORD": "marco",
        "MYSQL_DATABASE": "marco"
      }
    }
  },
  "mcp": {
    "logging": {
      "level": "debug",
      "file": "C:\\Users\\Marco\\.cursor\\logs\\mcp.log",
      "maxSize": 10485760,
      "maxFiles": 5
    },
    "security": {
      "allowedOrigins": ["http://localhost:*"],
      "allowedMethods": ["GET", "POST", "PUT", "DELETE", "OPTIONS"],
      "allowedHeaders": ["Content-Type", "Authorization"],
      "maxRequestSize": 10485760
    },
    "cache": {
      "enabled": true,
      "ttl": 3600,
      "maxSize": 104857600,
      "directory": "C:\\Users\\Marco\\.cursor\\cache"
    },
    "monitoring": {
      "enabled": true,
      "metrics": {
        "cpu": true,
        "memory": true,
        "disk": true
      },
      "alerting": {
        "enabled": true,
        "thresholds": {
          "cpu": 90,
          "memory": 90,
          "disk": 90
        }
      }
    },
    "workspace": {
      "path": "\\\\wsl$\\Ubuntu-22.04\\var\\www\\html\\_bases\\base_predict_fila3_mono",
      "exclude": [
        "vendor",
        "node_modules",
        ".git",
        "storage/logs",
        "storage/framework/cache",
        "storage/framework/sessions",
        "storage/framework/views",
        "bootstrap/cache",
        "public/hot",
        "public/storage"
      ],
      "maxFileSize": 10485760,
      "phpstan": {
        "enabled": true,
        "level": 10,
        "memoryLimit": "2G",
        "paths": [
          "app",
          "config",
          "database",
          "resources",
          "routes",
          "Modules"
        ]
      }
    }
  }
}
```

## Integrazione con Laravel

Per integrare questi server MCP con Laravel, è stato installato il pacchetto `InnoGE/laravel-mcp`. Questo permette di creare server MCP personalizzati direttamente all'interno dell'applicazione Laravel.

### Esempio di Comando Artisan

```php
<?php

declare(strict_types=1);

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

## Manutenzione e Aggiornamento

Per aggiornare i server MCP, seguire questi passaggi:

1. Aggiornare il repository MCP centralizzato:
   ```bash
   cd /var/www/html/_bases/mcp-servers
   git pull
   npm install
   npm run build
   ```

2. Verificare che i server funzionino correttamente:
   ```bash
   cd /var/www/html/_bases/mcp-servers/src/sequentialthinking
   npm start
   ```

3. Aggiornare i file di configurazione se necessario.

## Troubleshooting

Se si verificano problemi con i server MCP, verificare:

1. Che Node.js sia installato e aggiornato (v18+)
2. Che tutte le dipendenze siano installate correttamente
3. Che i percorsi nei file di configurazione siano corretti
4. Che i server MCP siano avviati correttamente

## Collegamenti

- [Documentazione MCP Ufficiale](https://docs.cursor.com/context/model-context-protocol)
- [Repository MCP Servers](https://github.com/modelcontextprotocol/servers)
- [InnoGE/laravel-mcp](https://github.com/InnoGE/laravel-mcp)
- [MCP_SETUP.md](./MCP_SETUP.md)
- [MCP_UTILIZZO_PRATICO.md](./MCP_UTILIZZO_PRATICO.md)
