<?php
/**
 * SECRETS — Notion API credentials
 * ────────────────────────────────────────────────────────────
 * Kopieer dit bestand naar `secrets.php` op de productieserver
 * en vul jouw eigen tokens in.
 *
 * BELANGRIJK:
 *   • secrets.php staat in .gitignore — wordt NIET naar GitHub gepusht
 *   • .htaccess blokkeert directe HTTP-toegang tot dit bestand
 *   • Plaats nooit echte tokens in dit example-bestand
 *
 * Notion integration token aanmaken:
 *   https://www.notion.so/my-integrations  → New integration
 *
 * Database-ID vinden:
 *   Open de Notion-database → klik "Share" → "Copy link"
 *   URL: https://www.notion.so/<workspace>/<DATABASE-ID>?v=...
 *   De DATABASE-ID is een 32-karakter UUID (zonder streepjes).
 */

return [
    'NOTION_TOKEN'       => 'ntn_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
    'NOTION_DATABASE_ID' => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
];
