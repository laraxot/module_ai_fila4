# Pacchetti MCP per Laravel

## Introduzione

Questo documento fornisce una panoramica dei principali pacchetti disponibili per implementare server Model Context Protocol (MCP) in Laravel. Ogni pacchetto viene analizzato in termini di funzionalità, casi d'uso ideali e requisiti tecnici.

## Pacchetti Principali

### InnoGE Laravel MCP

**Repository**: [github.com/InnoGE/laravel-mcp](https://github.com/InnoGE/laravel-mcp)

**Caratteristiche principali**:
- Supporto per il trasporto STDIO
- Integrazione con modelli Eloquent
- Sistema di strumenti estensibile
- Facile configurazione tramite comandi Artisan

**Casi d'uso ideali**:
- Sviluppo locale e prototipazione
- Integrazione con Cursor IDE
- Progetti di piccole e medie dimensioni

**Requisiti**:
- PHP 8.1+
- Laravel 10.0+

**Installazione**:
```bash
composer require innoge/laravel-mcp
```

### OP.GG Laravel MCP Server

**Repository**: [github.com/opgginc/laravel-mcp-server](https://github.com/opgginc/laravel-mcp-server)

**Caratteristiche principali**:
- Trasporto SSE (Server-Sent Events)
- Architettura Pub/Sub con adattatori
- Supporto per Laravel Octane
- Sicurezza avanzata per ambienti enterprise

**Casi d'uso ideali**:
- Ambienti di produzione
- Applicazioni enterprise
- Sistemi con requisiti di sicurezza elevati

**Requisiti**:
- PHP 8.2+
- Laravel 10.0+
- Laravel Octane (raccomandato)

**Installazione**:
```bash
composer require opgginc/laravel-mcp-server
```

### Laravel Helper Tools

**Repository**: [github.com/jsonallen/laravel-mcp](https://github.com/jsonallen/laravel-mcp)

**Caratteristiche principali**:
- Strumenti specifici per sviluppo Laravel
- Integrazione con Cursor IDE
- Funzionalità di debugging e analisi

**Casi d'uso ideali**:
- Assistenza allo sviluppo
- Debugging di applicazioni Laravel
- Analisi di modelli e relazioni

**Strumenti inclusi**:
- `tail_log_file`: Visualizzazione dei log Laravel
- `search_log_errors`: Ricerca di errori nei log
- `run_artisan_command`: Esecuzione di comandi Artisan
- `show_model`: Visualizzazione delle informazioni sui modelli

**Installazione**:
```bash
git clone https://github.com/jsonallen/laravel-mcp.git
```

## Confronto tra Pacchetti

| Caratteristica | InnoGE/laravel-mcp | opgginc/laravel-mcp-server | jsonallen/laravel-mcp |
|----------------|--------------------|-----------------------------|------------------------|
| Trasporto | STDIO | SSE | STDIO |
| Sicurezza | Base | Avanzata | Base |
| Integrazione DB | Eloquent | Eloquent + Redis | Limitata |
| Scalabilità | Media | Alta | Bassa |
| Facilità d'uso | Alta | Media | Alta |
| Documentazione | Media | Buona | Limitata |
| Manutenzione | Attiva | Attiva | Limitata |

## Implementazione Consigliata per Windsurf/Xot

Per l'integrazione con il modulo AI di Windsurf/Xot, si consiglia di utilizzare:

1. **Ambiente di sviluppo**: `InnoGE/laravel-mcp` per la sua facilità d'uso e rapida configurazione
2. **Ambiente di produzione**: `opgginc/laravel-mcp-server` per le sue caratteristiche di sicurezza e scalabilità

### Struttura delle Cartelle Consigliata

```
/Modules/AI/
├── app/
│   ├── MCP/
│   │   ├── Tools/
│   │   │   ├── AnalysisTools/
│   │   │   ├── ContentTools/
│   │   │   └── DevTools/
│   │   ├── Resources/
│   │   └── Providers/
│   ├── Console/
│   │   └── Commands/
│   │       └── ServeMcpCommand.php
│   └── Providers/
│       └── McpServiceProvider.php
└── config/
    └── mcp.php
```

## Estensioni e Personalizzazioni

### Creazione di Strumenti Personalizzati

Per creare strumenti personalizzati, implementare l'interfaccia `Tool`:

```php
namespace Modules\AI\App\MCP\Tools;

use InnoGE\LaravelMcp\Tools\Tool;

class CustomTool implements Tool
{
    public function getName(): string
    {
        return 'custom-tool-name';
    }

    public function getDescription(): string
    {
        return 'Descrizione dello strumento personalizzato';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'param1' => [
                    'type' => 'string',
                    'description' => 'Descrizione del parametro',
                ],
            ],
            'required' => ['param1'],
        ];
    }

    public function execute(array $arguments): string
    {
        // Implementazione dello strumento
        $param1 = $arguments['param1'];
        
        // Logica personalizzata
        
        return json_encode([
            'result' => 'Risultato dell\'operazione',
        ]);
    }
}
```

### Integrazione con Modelli Eloquent

Per esporre modelli Eloquent come risorse MCP:

```php
use InnoGE\LaravelMcp\Resources\EloquentResourceProvider;
use Modules\User\Models\User;
use Modules\Blog\Models\Article;

// Nel metodo getResources()
return [
    new EloquentResourceProvider(User::query(), 'users', 'Utenti dell\'applicazione'),
    new EloquentResourceProvider(Article::query(), 'articles', 'Articoli del blog'),
];
```

## Risorse Correlate

- [Panoramica MCP](./MCP_OVERVIEW.md)
- [Implementazioni MCP](./MCP_IMPLEMENTAZIONI.md)
- [Tutorial di Implementazione](../../../project_docs/tutorials/MCP_IMPLEMENTATION.md)
