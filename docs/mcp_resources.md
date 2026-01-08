# Risorse e Riferimenti MCP

## Documentazione Ufficiale

- [Model Context Protocol - Introduzione](https://modelcontextprotocol.io/introduction)
- [MCP - Specifiche Tecniche](https://modelcontextprotocol.io/specification)
- [MCP - Esempi di Implementazione](https://modelcontextprotocol.io/examples)

## Repository GitHub Principali

- [modelcontextprotocol/servers](https://github.com/modelcontextprotocol/servers) - Repository ufficiale dei server MCP
- [jsonallen/laravel-mcp](https://github.com/jsonallen/laravel-mcp) - Strumenti helper per Laravel
- [InnoGE/laravel-mcp](https://github.com/InnoGE/laravel-mcp) - Pacchetto per sviluppare server MCP con Laravel
- [opgginc/laravel-mcp-server](https://github.com/opgginc/laravel-mcp-server) - Implementazione SSE per Laravel
- [elevenlabs/elevenlabs-mcp](https://github.com/elevenlabs/elevenlabs-mcp) - Server MCP per sintesi vocale
- [punkpeye/awesome-mcp-servers](https://github.com/punkpeye/awesome-mcp-servers) - Lista curata di server MCP
- [appcypher/awesome-mcp-servers](https://github.com/appcypher/awesome-mcp-servers) - Altra collezione di server MCP

## Articoli e Tutorial

- [AI Agents in PHP with MCP](https://inspector.dev/ai-agents-in-php-with-mcp-model-context-protocol/) - Guida all'implementazione di agenti AI in PHP
- [Building and Testing a Laravel Portfolio API MCP Server Integration](https://stuartmason.co.uk/posts/building-and-testing-a-laravel-portfolio-api-mcp-server-integration) - Tutorial pratico
- [Easy LLM Integration for Laravel: MCP Server](https://www.reddit.com/r/laravel/comments/1k2w5tj/easy_llm_integration_for_laravel_mcp_server/) - Discussione su Reddit
- [How to Connect Cursor to 100 MCP Servers Within Minutes](https://composio.dev/blog/how-to-connect-cursor-to-100-mcp-servers-within-minutes/) - Guida all'integrazione
- [Cursor MCP vs Windsurf MCP Using Composio MCP Server](https://dev.to/composiodev/cursor-mcp-vs-windsurf-mcp-using-composio-mcp-server-1748) - Confronto tra implementazioni

## Servizi e Strumenti

- [Composio MCP](https://mcp.composio.dev/) - Piattaforma per connettere Cursor a server MCP
- [MCP Get](https://mcp-get.com/) - Directory di server MCP
- [Cursor Directory MCP](https://cursor.directory/mcp) - Elenco di server MCP per Cursor
- [Tavily MCP](https://docs.tavily.com/documentation/mcp) - Server MCP per ricerca web
- [Smithery AI](https://smithery.ai/) - Strumenti AI con supporto MCP
- [Firecrawl MCP](https://www.firecrawl.dev/blog/best-mcp-servers-for-cursor) - Server MCP per ricerca web

## Integrazioni Database

- [MySQL MCP Server](https://github.com/designcomputer/mysql_mcp_server) - Server MCP per MySQL
<<<<<<< HEAD
- [Supabase MCP](https://supabase.com/project_docs/guides/getting-started/mcp) - Integrazione Supabase con MCP
=======
- [Supabase MCP](https://supabase.com/docs/guides/getting-started/mcp) - Integrazione Supabase con MCP
>>>>>>> 901402b (.)
- [ADB MySQL MCP Server](https://github.com/aliyun/alibabacloud-adb-mysql-mcp-server) - Server MCP per ADB MySQL
- [Neon MCP](https://neon.tech/guides/cursor-mcp-neon) - Integrazione Neon con MCP

## Framework e SDK

- [Laravel MCP SDK](https://mcp.so/server/laravel-mcp-sdk/mohamedahmed01) - SDK per Laravel
- [Neuron AI](https://docs.neuron-ai.dev/) - Framework PHP per AI con supporto MCP
- [Google GenAI Toolbox](https://github.com/googleapis/genai-toolbox) - Strumenti Google per AI generativa

## Articoli su Sicurezza e Best Practices

- [Unlocking AI Potential: How to Quickly Set Up a Cursor MCP Server](https://www.apideck.com/blog/unlocking-ai-potential-how-to-quickly-set-up-a-cursor-mcp-server) - Guida con focus sulla sicurezza
- [Basic MCP SSE Server Requests/Responds](https://forum.cursor.com/t/basic-mcp-sse-server-requests-responds/64356) - Discussione tecnica su SSE

## Comunità e Forum

- [Forum Cursor - MCP](https://forum.cursor.com/t/how-to-use-mcp-server/50064) - Discussioni sulla community
- [Reddit - Best MCP Servers](https://www.reddit.com/r/cursor/comments/1j1ovbr/whats_are_the_best_mcp_servers_you_guys_are_using/) - Raccomandazioni della community

## Implementazioni Specifiche per Casi d'Uso

- [Laravel Portfolio API MCP Server](https://stuartmason.co.uk/posts/building-and-testing-a-laravel-portfolio-api-mcp-server-integration) - Server MCP per portfolio
- [Cursor Postgres MCP Server](https://forum.cursor.com/t/a-cursor-postgres-mcp-server-that-works/56389) - Server MCP per PostgreSQL
- [Magic MCP](https://github.com/21st-dev/magic-mcp) - Implementazione flessibile di MCP

## Risorse Interne Correlate

- [Panoramica MCP](./MCP_OVERVIEW.md)
- [Implementazioni MCP](./MCP_IMPLEMENTAZIONI.md)
- [Pacchetti MCP](./MCP_PACKAGES.md)

## Configurazione MCP per Cursor

Per configurare un server MCP in Cursor, è necessario aggiungere le seguenti informazioni al file `.cursor/settings.json`:

```json
{
  "mcp": {
    "servers": [
      {
        "name": "Laravel AI Assistant",
        "transport": "stdio",
        "command": "php artisan mcp:serve",
        "cwd": "${workspaceFolder}"
      }
    ]
  }
}
```

Per server SSE:

```json
{
  "mcp": {
    "servers": [
      {
        "name": "Laravel AI Assistant",
        "transport": "sse",
        "url": "http://localhost:8000/mcp/sse"
      }
    ]
  }
}
```
