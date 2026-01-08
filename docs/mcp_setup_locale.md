# Setup Locale dei Server MCP per base_predict_fila3_mono

## Panoramica

Questo documento fornisce istruzioni dettagliate per la configurazione e l'avvio dei server MCP (Model Context Protocol) in ambiente locale per il progetto base_predict_fila3_mono, seguendo le regole di sviluppo e le convenzioni stabilite.

## Prerequisiti

Prima di procedere con il setup dei server MCP, assicurarsi di avere:

1. Node.js (versione 16 o superiore) e npm installati
2. Accesso al repository del progetto base_predict_fila3_mono
3. Permessi di scrittura nelle directory di configurazione

## Configurazione dei Server MCP

### 1. File di Configurazione

Il file di configurazione principale per i server MCP si trova in:

```
/var/www/html/_bases/base_predict_fila3_mono/mcp_config.json
```

Questo file contiene la configurazione per tutti i server MCP utilizzati nel progetto. La struttura del file è la seguente:

```json
{
  "mcpServers": {
    "sequential-thinking": {
      "command": "npx",
      "args": ["-y", "@modelcontextprotocol/server-sequential-thinking"],
      "env": {}
    },
    "memory": {
      "command": "npx",
      "args": ["-y", "@modelcontextprotocol/server-memory"],
      "env": {}
    },
    "fetch": {
      "command": "npx",
      "args": ["-y", "@modelcontextprotocol/server-fetch"],
      "env": {}
    },
    "filesystem": {
      "command": "npx",
      "args": ["-y", "@modelcontextprotocol/server-filesystem"],
      "env": {}
    },
    "postgres": {
      "command": "npx",
      "args": ["-y", "@modelcontextprotocol/server-postgres"],
      "env": {}
    },
    "redis": {
      "command": "npx",
      "args": ["-y", "@modelcontextprotocol/server-redis"],
      "env": {}
    },
    "puppeteer": {
      "command": "npx",
      "args": ["-y", "@modelcontextprotocol/server-puppeteer"],
      "env": {}
    },
    "mysql": {
      "command": "npx",
      "args": ["-y", "@modelcontextprotocol/server-mysql"],
      "env": {
        "MYSQL_HOST": "localhost",
        "MYSQL_PORT": "3306",
        "MYSQL_USER": "root",
        "MYSQL_PASSWORD": "",
        "MYSQL_DATABASE": "laravel"
      }
    }
  }
}
```

### 2. Configurazione per Editor

#### Windsurf

Per configurare i server MCP per Windsurf, creare o modificare il file:

```
~/.codeium/windsurf/mcp_config.json
```

Utilizzare la stessa struttura del file di configurazione principale.

#### Cursor

Per configurare i server MCP per Cursor, creare o modificare il file:

```
~/.cursor/mcp.json
```

o, su Windows:

```
C:\Users\<username>\.cursor\mcp.json
```

Utilizzare la seguente struttura:

```json
{
  "mcp": {
    "servers": {
      "sequential-thinking": {
        "command": "npx",
        "args": ["-y", "@modelcontextprotocol/server-sequential-thinking"]
      },
      "memory": {
        "command": "npx",
        "args": ["-y", "@modelcontextprotocol/server-memory"]
      },
      "fetch": {
        "command": "npx",
        "args": ["-y", "@modelcontextprotocol/server-fetch"]
      },
      "filesystem": {
        "command": "npx",
        "args": ["-y", "@modelcontextprotocol/server-filesystem"]
      },
      "postgres": {
        "command": "npx",
        "args": ["-y", "@modelcontextprotocol/server-postgres"]
      },
      "redis": {
        "command": "npx",
        "args": ["-y", "@modelcontextprotocol/server-redis"]
      },
      "puppeteer": {
        "command": "npx",
        "args": ["-y", "@modelcontextprotocol/server-puppeteer"]
      },
      "mysql": {
        "command": "npx",
        "args": ["-y", "@modelcontextprotocol/server-mysql"],
        "env": {
          "MYSQL_HOST": "localhost",
          "MYSQL_PORT": "3306",
          "MYSQL_USER": "root",
          "MYSQL_PASSWORD": "",
          "MYSQL_DATABASE": "laravel"
        }
      }
    }
  }
}
```

## Avvio dei Server MCP

### Avvio Manuale

Per avviare manualmente i server MCP, eseguire i seguenti comandi dalla directory principale del progetto:

```bash
# Avvio del server sequential-thinking
cd /var/www/html/_bases/base_predict_fila3_mono && npx -y @modelcontextprotocol/server-sequential-thinking &

# Avvio del server memory
cd /var/www/html/_bases/base_predict_fila3_mono && npx -y @modelcontextprotocol/server-memory &

# Avvio del server fetch
cd /var/www/html/_bases/base_predict_fila3_mono && npx -y @modelcontextprotocol/server-fetch &

# Avvio del server filesystem
cd /var/www/html/_bases/base_predict_fila3_mono && npx -y @modelcontextprotocol/server-filesystem &

# Avvio del server postgres
cd /var/www/html/_bases/base_predict_fila3_mono && npx -y @modelcontextprotocol/server-postgres &

# Avvio del server redis
cd /var/www/html/_bases/base_predict_fila3_mono && npx -y @modelcontextprotocol/server-redis &

# Avvio del server puppeteer
cd /var/www/html/_bases/base_predict_fila3_mono && npx -y @modelcontextprotocol/server-puppeteer &

# Avvio del server mysql
cd /var/www/html/_bases/base_predict_fila3_mono && npx -y @modelcontextprotocol/server-mysql &
```

### Script di Avvio Automatico

Per facilitare l'avvio dei server MCP, è possibile creare uno script bash:

```bash
#!/bin/bash

# Script per avviare tutti i server MCP necessari per base_predict_fila3_mono

PROJECT_DIR="/var/www/html/_bases/base_predict_fila3_mono"

# Funzione per avviare un server MCP
start_mcp_server() {
    local server_name=$1
    echo "Avvio del server MCP $server_name..."
    cd $PROJECT_DIR && npx -y @modelcontextprotocol/server-$server_name &
    sleep 2
    echo "Server MCP $server_name avviato con PID $!"
}

# Avvio di tutti i server MCP necessari
start_mcp_server "sequential-thinking"
start_mcp_server "memory"
start_mcp_server "fetch"
start_mcp_server "filesystem"
start_mcp_server "postgres"
start_mcp_server "redis"
start_mcp_server "puppeteer"
start_mcp_server "mysql"

echo "Tutti i server MCP sono stati avviati."
```

Salvare questo script come `/var/www/html/_bases/base_predict_fila3_mono/scripts/start_mcp_servers.sh` e renderlo eseguibile:

```bash
chmod +x /var/www/html/_bases/base_predict_fila3_mono/scripts/start_mcp_servers.sh
```

## Verifica del Funzionamento

Per verificare che i server MCP siano in funzione correttamente, è possibile utilizzare il seguente script:

```php
<?php

declare(strict_types=1);

namespace Modules\AI\Console\Commands;

use Illuminate\Console\Command;
use Modules\AI\Services\Contracts\MCPServiceContract;

class TestMCPServersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:test-mcp-servers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testa la connessione ai server MCP configurati';

    /**
     * Execute the console command.
     */
    public function handle(MCPServiceContract $mcpService): int
    {
        $this->info('Verifica dei server MCP...');

        // Test del server sequential-thinking
        try {
            $result = $mcpService->sequentialThinking()->generateThought(
                'Test del server sequential-thinking',
                1,
                1,
                false
            );
            $this->info('✅ Server sequential-thinking: OK');
        } catch (\Exception $e) {
            $this->error('❌ Server sequential-thinking: ERRORE - ' . $e->getMessage());
        }

        // Test del server memory
        try {
            $result = $mcpService->memory()->store(
                'test_key',
                ['test' => 'value']
            );
            $this->info('✅ Server memory: OK');
        } catch (\Exception $e) {
            $this->error('❌ Server memory: ERRORE - ' . $e->getMessage());
        }

        // Altri test per gli altri server...

        return 0;
    }
}
```

## Risoluzione dei Problemi

### Problema: Il server MCP non si avvia

**Soluzione**:
1. Verificare che Node.js e npm siano installati correttamente
2. Verificare che il comando npx sia disponibile
3. Verificare che non ci siano altri processi che utilizzano le stesse porte

### Problema: L'editor non riesce a connettersi ai server MCP

**Soluzione**:
1. Verificare che i file di configurazione siano corretti e validi
2. Verificare che i server MCP siano in esecuzione
3. Riavviare l'editor

### Problema: Errori di permessi durante l'avvio dei server MCP

**Soluzione**:
1. Verificare i permessi della directory del progetto
2. Eseguire i comandi con i permessi appropriati

## Best Practices

1. **Avviare solo i server necessari**: Avviare solo i server MCP che sono effettivamente necessari per il task corrente per ridurre il consumo di risorse.

2. **Utilizzare variabili d'ambiente per le credenziali**: Non hardcodare le credenziali nei file di configurazione, ma utilizzare variabili d'ambiente.

3. **Monitorare i log dei server**: Monitorare i log dei server MCP per identificare e risolvere eventuali problemi.

4. **Aggiornare regolarmente i server**: Aggiornare regolarmente i server MCP per beneficiare delle ultime funzionalità e correzioni di sicurezza.

## Conclusione

Seguendo queste istruzioni, è possibile configurare e avviare correttamente i server MCP per il progetto base_predict_fila3_mono. Questi server estendono le capacità degli assistenti AI, consentendo loro di interagire con sistemi esterni, eseguire calcoli complessi e accedere a dati in tempo reale.

Per ulteriori dettagli sui server MCP consigliati e la loro implementazione, fare riferimento a:
- [MCP_SERVER_CONSIGLIATI.md](./MCP_SERVER_CONSIGLIATI.md)
- [MCP_INTEGRAZIONE_MODULI.md](./MCP_INTEGRAZIONE_MODULI.md)
- [MCP_IMPLEMENTAZIONE_PRATICA.md](./MCP_IMPLEMENTAZIONE_PRATICA.md)
