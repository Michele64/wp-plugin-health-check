=== Controllo stato plugin ===
Contributors: michelechesi
Tags: plugin, manutenzione, sicurezza, aggiornamenti
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.1.14
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Verifica tutti i plugin installati rispetto alla directory di WordPress.org e segnala i plugin chiusi, orfani o non aggiornati da tempo.

== Description ==

Ad ogni controllo, Controllo stato plugin analizza tutti i plugin installati e li confronta con la directory di WordPress.org. Vengono rilevati:

* Plugin rimossi dalla directory (ad esempio per problemi di sicurezza o violazioni delle linee guida)
* Plugin che non ricevono un aggiornamento da molti mesi
* Plugin testati solo con una versione di WordPress notevolmente più vecchia

I risultati compaiono direttamente come colonna aggiuntiva nell'elenco standard dei plugin, oltre che in una pagina di report dedicata sotto **Strumenti → Stato plugin**. Le soglie per "Verificare" e "Azione necessaria" sono liberamente configurabili. La notifica via email è attiva per impostazione predefinita (mensile) e può essere personalizzata o disattivata nelle impostazioni.

**Nota sul valore «Testato fino a»:** questo valore è mantenuto dall'autore stesso del plugin e non è verificato da nessun altro. Un rilievo basato su di esso è un indizio, non una prova — la data dell'ultimo aggiornamento dice in genere di più sul reale stato di manutenzione.

== Installation ==

1. Scaricare il file ZIP attuale dal [repository GitHub](https://github.com/Michele64/wp-plugin-health-check/releases)
2. Caricarlo da **Plugin → Aggiungi nuovo → Carica plugin**, oppure estrarlo in `wp-content/plugins/`
3. Attivare il plugin

Gli aggiornamenti vengono recuperati automaticamente dal repository GitHub — non è necessario alcun server di aggiornamento separato.

== Changelog ==

= 1.1.14 =
* Il popup "Visualizza dettagli" (Descrizione, Installazione, Changelog, Avviso di aggiornamento) viene ora localizzato, invece di mostrare sempre il readme.txt tedesco. Per ogni lingua supportata è incluso un readme-{locale}.txt nello stesso formato, letto automaticamente in base alla lingua del sito. Anche la breve descrizione sotto il nome del plugin nell'elenco plugin viene ora tradotta.
* Aggiunta una sezione "Upgrade Notice" al readme (prima non esisteva)

= 1.1.13 =
* Aggiunte le traduzioni in danese, italiano e francese

= 1.1.12 =
* Aggiunta la traduzione inglese (en_US e en_GB). In precedenza la cartella /languages conteneva solo il modello .pot, senza alcun file .mo compilato — poiché i testi sorgente sono in tedesco, qualsiasi sito non tedesco mostrava il testo originale tedesco per mancanza di una traduzione.

= 1.1.11 =
* Seguito della 1.1.10: lo scorrimento verso l'alto dopo "Controlla ora" non funzionava in modo affidabile perché veniva eseguito troppo presto, prima del successivo ripristino della posizione di scorrimento da parte del browser. Ora viene forzato anche all'evento load e dopo un breve ritardo; l'URL di reindirizzamento è inoltre univoco per ogni controllo (timestamp anziché un valore fisso).

= 1.1.10 =
* Corretto: dopo "Controlla ora" la pagina si posizionava sull'ultima posizione di scorrimento visitata invece che sul report appena generato, perché l'URL di reindirizzamento è identico ad ogni controllo e il browser ripristina per essa la posizione di scorrimento memorizzata. Ora lo scorrimento viene riportato esplicitamente in cima alla pagina.

= 1.1.9 =
* Corretto: dopo il salvataggio delle impostazioni la pagina saltava in cima invece di tornare alla sezione impostazioni (al reindirizzamento mancava l'ancora)

= 1.1.8 =
* La notifica via email supporta ora più indirizzi destinatari separati da virgola. Ogni indirizzo viene validato singolarmente; gli indirizzi non validi vengono scartati al salvataggio e segnalati con un avviso.

= 1.1.7 =
* Corretto un esito errato "Azione necessaria" per i plugin dotati di un proprio controllo aggiornamenti (incluso questo stesso plugin, tramite GitHub): il controllo "conosciuto nella directory di WordPress.org" verificava in precedenza solo la presenza di una voce nel transient update_plugins — che però viene popolato anche da sistemi di aggiornamento di terze parti, non solo da WordPress.org. Ora viene verificato anche il formato dell'id ("w.org/plugins/…"), presente solo nelle voci realmente provenienti da wordpress.org.

= 1.1.6 =
* Aggiunta l'autenticazione GitHub opzionale per il controllo aggiornamenti (costante WPHC_GITHUB_TOKEN in wp-config.php) per evitare errori 403 dovuti ai limiti di frequenza di GitHub o al rilevamento di abusi basato su IP negli hosting condivisi

= 1.1.5 =
* La notifica via email è ora attiva per impostazione predefinita (in precedenza: disattivata)
* Aggiunto un hook di attivazione affinché il cron venga effettivamente configurato in linea con il valore predefinito su una nuova installazione

= 1.1.4 =
* Modificata la frequenza predefinita della notifica via email a "mensile" (in precedenza: "settimanale")

= 1.1.3 =
* Il numero di versione viene ora letto dinamicamente dall'intestazione del plugin, invece di essere anche codificato fisso
* Aggiornato "Testato fino a" alla versione 7.1

= 1.1.2 =
* Prima release tramite il repository GitHub pubblico con aggiornamenti automatici (Plugin Update Checker)
* Predisposizione multilingua: tutti i testi visibili sono traducibili tramite il text domain "wp-plugin-health-check"
* Aggiunti README.md e readme.txt

Le note di rilascio precedenti (prima del passaggio a GitHub) non sono documentate in dettaglio. I commit più vecchi si trovano nella [cronologia dei commit su GitHub](https://github.com/Michele64/wp-plugin-health-check/commits/main).

== Upgrade Notice ==

= 1.1.13 =
Aggiunge le traduzioni in danese, italiano e francese. Nessuna azione necessaria.

= 1.1.8 =
La notifica via email accetta ora un elenco di indirizzi separati da virgola. Se avete sempre usato un solo indirizzo, per voi non cambia nulla.

= 1.1.6 =
Se il controllo aggiornamenti mostra un errore GitHub 403, ora è possibile impostare WPHC_GITHUB_TOKEN in wp-config.php per risolverlo — vedere il README del plugin per i dettagli.
