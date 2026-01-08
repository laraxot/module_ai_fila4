# Server MCP Raccomandati e Filosofia Zen

Questa guida elenca e motiva la scelta dei server MCP consigliati per l'integrazione in progetti Laravel, Cursor, Windsurf e Trae, secondo una filosofia di semplicità, chiarezza e coerenza.

## Filosofia Zen MCP
- **Solo ciò che funziona:** Usa solo server MCP Node.js realmente disponibili su npm e avviabili con `npx`.
- **Configurazione locale:** Tutto va dichiarato in `.cursor/mcp.json` del progetto.
- **Argomenti obbligatori:** filesystem e postgres richiedono argomenti specifici.
- **Sicurezza:** Niente credenziali hardcoded, niente servizi non attivi.
- **Manutenibilità:** Tutto locale, tutto dichiarato, tutto riproducibile.

## Server MCP consigliati

### 1. sequential-thinking
- **Scopo:** Risoluzione di problemi complessi tramite pensiero sequenziale e revisioni dinamiche.
- **Vantaggi:** Utile per task di pianificazione, analisi, brainstorming e debugging multi-step.
- **Prerequisiti:** Nessuno, funziona out-of-the-box con `npx`.

### 2. memory
- **Scopo:** Gestione di una knowledge base persistente e ragionamento su grafi di conoscenza.
- **Vantaggi:** Permette di mantenere e interrogare una memoria persistente tra sessioni.
- **Prerequisiti:** Nessuno, funziona out-of-the-box con `npx`.

### 3. filesystem
- **Scopo:** Accesso sicuro e controllato al filesystem locale.
- **Vantaggi:** Permette operazioni su file e directory in modo sicuro.
- **Prerequisiti:** Richiede di specificare almeno una directory consentita come argomento al comando.
- **Esempio:**
  ```bash
  npx -y @modelcontextprotocol/server-filesystem ./
  ```

### 4. postgres
- **Scopo:** Accesso a database PostgreSQL in sola lettura.
- **Vantaggi:** Consente query sicure e ispezione dello schema.
- **Prerequisiti:** Richiede una stringa di connessione come argomento al comando.
- **Esempio:**
  ```bash
  npx -y @modelcontextprotocol/server-postgres postgresql://user:pass@host:port/dbname
  ```

### 5. puppeteer
- **Scopo:** Automazione browser e web scraping.
- **Vantaggi:** Permette di navigare, estrarre dati e automatizzare interazioni web.
- **Prerequisiti:** Nessuno, ma richiede risorse di sistema adeguate.

### 6. redis (opzionale)
- **Scopo:** Interazione con database Redis per caching e key-value store.
- **Vantaggi:** Permette operazioni rapide su dati temporanei.
- **Prerequisiti:** Richiede che un server Redis sia attivo su `localhost:6379`.
- **Nota:** Se Redis non è attivo, il server MCP fallisce l'avvio e va omesso dalla configurazione.

## Server NON consigliati o con problemi

### fetch
- **Problematica:** Il pacchetto `@modelcontextprotocol/server-fetch` non è disponibile su npm (errore 404). Non può essere usato.

### mysql
- **Problematica:** Non esiste un pacchetto npm ufficiale `@modelcontextprotocol/server-mysql`. Non è possibile usare un server MCP MySQL tramite npx o npm. Se necessario, puoi usare uno script custom locale (es. `/bashscripts/mcp/start-mysql-mcp.sh`) solo per il progetto specifico.

---

## Installazione e uso su altri PC/progetti

1. **Copia la cartella MCP servers**
   - Copia `/var/www/html/_bases/mcp-servers` nella stessa posizione sul nuovo PC.
2. **Installa le dipendenze**
   - Vai nella cartella `mcp-servers` e lancia:
     ```bash
     npm install
     npm run build
     ```
3. **Configura il progetto**
   - Nel progetto, crea o aggiorna `.cursor/mcp.json` con solo i server MCP realmente funzionanti (vedi esempio sopra).
   - Per MySQL, se necessario, usa uno script custom locale e NON una configurazione globale.
4. **Usa solo script in /bashscripts**
   - Tutti gli script di gestione MCP devono essere in `/var/www/html/_bases/base_predict_fila3_mono/bashscripts`.
5. **Testa ogni server MCP**
   - Avvia manualmente con `npx` per verificare che funzioni e che gli argomenti siano corretti.

---

## Nota sullo script di gestione MCP

Il path corretto per lo script di gestione MCP è:

`/var/www/html/_bases/base_predict_fila3_mono/bashscripts/mcp/mcp-manager-v2.sh`

**Non usare il path:** `/var/www/html/_bases/base_predict_fila3_mono/scripts/mcp-manager-v2.sh` (non esiste o non è aggiornato).
