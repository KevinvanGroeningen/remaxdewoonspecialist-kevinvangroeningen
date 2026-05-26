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

    // ─── Optioneel: e-mail-notificatie bij elke nieuwe lead ─────
    // Adres waarheen de notificatie wordt gestuurd. Leeg laten of
    // weglaten = geen e-mail.
    'NOTIFY_EMAIL'       => 'kevinvangroeningen@remax.nl',

    // Optioneel: From-adres in de e-mail. Default: SMTP_USER als die
    // gezet is, anders website@<huidige host>.
    // 'NOTIFY_FROM'     => 'noreply@jouw-domein.nl',

    // ─── SMTP-verzending (vereist op Hostinger — mail() werkt niet) ─
    // Maak in hPanel → Emails een mailbox aan op je eigen domein
    // (bv. noreply@jouw-domein.nl), kopieer hier de credentials.
    // Default host: smtp.hostinger.com:465 (SSL).
    // 'SMTP_HOST'       => 'smtp.hostinger.com',
    // 'SMTP_PORT'       => 465,
    'SMTP_USER'          => 'noreply@jouw-domein.nl',
    'SMTP_PASS'          => 'het-wachtwoord-van-de-mailbox',
];
