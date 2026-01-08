# Installazione e Gestione MCP Server (Model Context Protocol)

## Obiettivo
Centralizzare i server MCP in una cartella condivisa (`/var/www/html/_bases/mcp-servers`) per riutilizzarli facilmente in più progetti Laravel/PHP, mantenendo ordine, aggiornabilità e coerenza.

---

## Struttura Consigliata

```
/var/www/html/_bases/
  ├── mcp-servers/         # Tutti i server MCP condivisi
  │     ├── everything/
  │     ├── fetch/
  │     └── ... (altri server)
  ├── base_predict_fila3_mono/
  ├── altro_progetto/
  └── ...
```

---

## 1. Clonazione repository ufficiale MCP

```bash
cd /var/www/html/_bases
# Se la cartella esiste già, aggiorna con git pull
# Altrimenti clona la repository ufficiale:
git clone https://github.com/modelcontextprotocol/servers.git mcp-servers
```

> **Nota:** Se esistono già cartelle `mcp-server` o `mcp-servers` in altre posizioni, spostale qui e rimuovi i duplicati.

---

## 2. Installazione dipendenze

```bash
cd /var/www/html/_bases/mcp-servers
npm install
```

---

## 3. Build dei server

```bash
npm run build
```

---

## 4. Avvio di un MCP Server

Ogni server (es. `everything`, `fetch`, ecc.) ha la propria directory e README.
Per avviare, ad esempio, il server `everything`:

```bash
cd /var/www/html/_bases/mcp-servers/src/everything
npm install   # solo se richiesto dal README locale
npm start
```

> Consulta sempre il README della sottocartella per eventuali variabili d'ambiente o configurazioni specifiche.

---

## 5. Utilizzo da altri progetti

- Dal tuo progetto Laravel, effettua chiamate HTTP verso il server MCP in esecuzione (es. su `http://localhost:port`).
- Puoi configurare la porta e altri parametri tramite variabili d'ambiente o file `.env` nella cartella del server MCP.

---

## 6. Aggiornamento MCP Server

Per aggiornare tutti i server MCP:

```bash
cd /var/www/html/_bases/mcp-servers
git pull
npm install
npm run build
```

---

## 7. Best Practices

- **Non duplicare** la cartella MCP server nei singoli progetti.
- **Documenta** sempre la path condivisa nei README dei tuoi progetti.
- **Versiona** la cartella MCP server solo una volta (non includerla nei repository dei singoli progetti).
- **Controlla le porte**: se usi più server MCP contemporaneamente, assicurati che non ci siano conflitti di porta.
- **Backup**: se personalizzi i server, effettua backup o fork della repository.

---

## 8. Esempio di collegamento nella documentazione di progetto

```md
## Utilizzo MCP Server

Questo progetto utilizza i server MCP centralizzati in `/var/www/html/_bases/mcp-servers`.
<<<<<<< HEAD
Per avviare un server MCP, segui la guida in `/var/www/html/_bases/base_predict_fila3_mono/laravel/Modules/AI/project_docs/MCP_INSTALLAZIONE_SERVER.md`.
=======
Per avviare un server MCP, segui la guida in `/var/www/html/_bases/base_predict_fila3_mono/laravel/Modules/AI/docs/MCP_INSTALLAZIONE_SERVER.md`.
>>>>>>> 901402b (.)
```

---

## 9. Troubleshooting

- **Errore "porta occupata"**: Cambia la porta nel file di configurazione o variabile d'ambiente.
- **Problemi di permessi**: Assicurati che la cartella sia accessibile in lettura/scrittura.
- **Dipendenze mancanti**: Esegui sempre `npm install` nella root e nelle sottocartelle se richiesto.

---

## 10. Risorse utili

- [Model Context Protocol Docs](https://docs.cursor.com/context/model-context-protocol)
- [Repository ufficiale MCP server](https://github.com/modelcontextprotocol/servers)
- [README locale MCP server](../../../../../../mcp-servers/README.md)

---

> _Questa guida è pensata per essere copiata e incollata in qualsiasi README di progetto o wiki aziendale. Aggiorna i percorsi se la struttura delle cartelle cambia._ 