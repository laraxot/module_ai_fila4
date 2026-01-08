# MCP Server Consigliati per il Modulo AI

## Scopo del Modulo
Il modulo AI integra agenti di intelligenza artificiale, LLM e automazioni conversazionali.

## Server MCP Consigliati
- `sequential-thinking`: Per orchestrare ragionamenti complessi e agenti AI multi-step.
- `memory`: Per gestire la memoria conversazionale e lo stato degli agenti.
- `fetch`: Per eseguire chiamate HTTP/API verso servizi esterni.
- `filesystem`: Per operazioni su file (upload, parsing, ecc).
- `puppeteer`: Per automazioni browser (scraping, test, estrazione dati da web app).

## Configurazione Minima Esempio
```json
{
  "mcpServers": {
    "sequential-thinking": { "command": "npx", "args": ["-y", "@modelcontextprotocol/server-sequential-thinking"] },
    "memory": { "command": "npx", "args": ["-y", "@modelcontextprotocol/server-memory"] },
    "fetch": { "command": "npx", "args": ["-y", "@modelcontextprotocol/server-fetch"] },
    "filesystem": { "command": "npx", "args": ["-y", "@modelcontextprotocol/server-filesystem"] },
    "puppeteer": { "command": "npx", "args": ["-y", "@modelcontextprotocol/server-puppeteer"] }
  }
}
```

## Note
- Puoi aggiungere override locali per esigenze specifiche.
- Consulta sempre la guida MCP_SERVER_SETUP.md per aggiornamenti.
