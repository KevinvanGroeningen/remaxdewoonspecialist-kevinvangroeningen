# Deploy-handleiding — REMAX De Woonspecialist

Stap-voor-stap om de website live te krijgen op je eigen domein.

> **Gekozen stack**: Hostinger Business Web Hosting met PHP-backend
> (`aanmelden.php`). Domein wordt ook bij Hostinger beheerd. De Node.js
> versie (`app.js`) staat alleen nog in de repo voor lokale ontwikkeling.

---

## Fase 1 · GitHub-repo koppelen aan hPanel (≈ 10 min)

Hostinger Business heeft een ingebouwde Git-deploy. Bij elke `git push`
op `main` synct hPanel de nieuwste bestanden naar `public_html/`.

1. **Inloggen** op [hpanel.hostinger.com](https://hpanel.hostinger.com/)
2. Open je website-hosting → links in het menu: **Geavanceerd** → **GIT**
   (of: **Bestand­manager** → tabblad **GIT**, hangt van je interface af)
3. **Repository toevoegen**:
   - Repository: `https://github.com/KevinvanGroeningen/remaxdewoonspecialist-kevinvangroeningen.git`
   - Branch: `main`
   - Path: `/public_html/`
   - Autorisatie: kies "Deploy key" → kopieer de SSH-public-key die
     hPanel toont
4. **GitHub-kant**: ga in een ander tabblad naar je repo →
   **Settings → Deploy keys → Add deploy key** → plak de key, vink
   *"Allow write access"* **uit** (read-only is genoeg) → Save
5. **Terug in hPanel** → klik **Verifiëren** of **Aankoppelen**
6. Eerste **Pull** uitvoeren. hPanel kloont nu de hele repo naar
   `public_html/`.

**⚠️ Belangrijk — alleen `public/` mag in `public_html/`**:

Onze repo heeft de site-bestanden in `public/`, maar Hostinger wil dat ze
*direct* in `public_html/` staan. Twee opties:

- **A · Subfolder als root** (aanbevolen): in hPanel zet je de **Document
  root** van het domein op `public_html/public/`. Geen verplaatsing
  nodig. (Domein → **Document Root** wijzigen.)
- **B · Path bij Git-deploy**: zet Path in de Git-instelling op
  `/public_html/public/` ipv `/public_html/`. Hostinger pulled dan
  meteen naar de juiste map.

---

## Fase 2 · Notion-integratie (≈ 10 min)

### 2.1 Notion Integration aanmaken
1. [notion.so/my-integrations](https://www.notion.so/my-integrations) →
   **+ New integration**
2. Naam: `REMAX Website`
3. Workspace: jouw workspace → **Submit**
4. Kopieer de **Internal Integration Token** (begint met `ntn_…` of `secret_…`)

### 2.2 Database delen met de integratie
1. Open je Notion **Leads-database**
2. Klik rechtsboven `···` → **Add connections** → "REMAX Website"
3. Kopieer de **Database ID** uit de URL:
   `notion.so/<workspace>/<DATABASE-ID>?v=…` — de DATABASE-ID is een
   32-karakter UUID

### 2.3 Database-properties (verplicht)
Maak in je Notion-database deze kolommen aan (anders crasht de Notion-call):

| Property                       | Type                                  |
|--------------------------------|---------------------------------------|
| Naam                           | Title                                 |
| Email                          | Email                                 |
| Telefoon                       | Phone                                 |
| Status                         | Select (incl. optie "Nieuw")          |
| Type                           | Select (incl. optie "Aankoop")        |
| Bron                           | Text (rich text)                      |
| Max. budget                    | Number                                |
| Min. budget                    | Number                                |
| Max. bod (incl. overbieden)    | Number                                |
| Min. slaapkamers               | Number                                |
| Gewenste locaties              | Text                                  |
| Woningtype                     | Multi-select                          |
| Woonwensen                     | Text                                  |
| Eerste contact                 | Date                                  |
| Volgende actie                 | Text                                  |

### 2.4 `secrets.php` aanmaken op de server

`secrets.php` wordt **niet** mee-gepusht naar GitHub (staat in `.gitignore`)
en wordt door `.htaccess` afgeschermd voor publieke HTTP-toegang.

1. hPanel → **Bestandmanager** → ga naar de document-root (zelfde map
   waar `index.html` en `aanmelden.php` staan)
2. **Nieuw bestand** → naam: `secrets.php`
3. Open in editor en plak:
   ```php
   <?php
   return [
       'NOTION_TOKEN'       => 'ntn_PLAK_HIER_JOUW_TOKEN',
       'NOTION_DATABASE_ID' => 'PLAK_HIER_DE_DATABASE_UUID',
   ];
   ```
4. Opslaan.

> 💡 Vul je `secrets.php` later aan? Geen redeploy nodig — PHP-bestanden
> worden bij elke request opnieuw gelezen.

---

## Fase 3 · Domein koppelen (≈ 15 min + DNS-propagatie)

Domein staat al bij Hostinger, dus minimale setup:

1. hPanel → **Domeinen** → kies je `.nl`-domein
2. **Website wijzigen** → koppel aan je Business-hosting-account
3. **SSL** → "Forceer HTTPS" aanzetten (gratis Let's Encrypt is automatisch
   actief)
4. Test: open `https://jouw-domein.nl` — je hoort de site te zien

DNS-propagatie kan 5 minuten tot 24 uur duren.

---

## Fase 4 · Productie-checks (≈ 20 min)

### 4.1 Canonical URLs aanpassen
In `public/index.html` en `public/index-en.html`, vervang alle
`kevin-vangroeningen.nl` door je echte domein:

```bash
cd ~/remax-woonspecialist
grep -rl "kevin-vangroeningen.nl" public/ | xargs sed -i '' 's|kevin-vangroeningen.nl|jouw-domein.nl|g'
git add -A && git commit -m "Update canonical URLs to production domain"
git push
```

Hostinger pullt automatisch (of klik **Pull** in hPanel als auto-deploy
uit staat).

### 4.2 Eerste testinzending
1. Open je domein
2. Vul stap 1 + 2 in
3. Submit
4. Check Notion-database → lead moet verschijnen
5. Backup-check: hPanel → Bestandmanager → `.leads.log` — elke lead
   wordt ook lokaal gelogd voor het geval Notion uit staat

### 4.3 Google Search Console
1. [search.google.com/search-console](https://search.google.com/search-console)
   → Property toevoegen
2. Verifieer via DNS (TXT-record bij Hostinger DNS-beheer)
3. Submit sitemap: `https://jouw-domein.nl/sitemap.xml`

### 4.4 Privacyverklaring + KvK
- Maak `public/privacy.html` (of gebruik
  [iubenda](https://www.iubenda.com/) voor gegenereerde versie)
- Update de footer-link in beide index-bestanden
- Voeg KvK + BTW-nummer toe in de footer

---

## Snelle deploy-checklist

- [ ] Hostinger Business gekoppeld aan domein
- [ ] GitHub-repo gekoppeld in hPanel → Git, eerste Pull gedaan
- [ ] Document Root staat op `public_html/public/` (of via Path-instelling)
- [ ] `secrets.php` aangemaakt met Notion-credentials
- [ ] Notion-database heeft alle 15 vereiste kolommen
- [ ] HTTPS forceer-knop aan in hPanel SSL
- [ ] Canonical URLs in HTML aangepast naar productiedomein
- [ ] Test-inzending → lead zichtbaar in Notion
- [ ] Google Search Console + sitemap submitted
- [ ] Privacyverklaring + KvK in footer

---

## Lokaal ontwikkelen

Twee opties — beide werken naast elkaar:

```bash
# Optie 1: Node/Express server (oude pad, app.js)
npm install
npm start                    # → http://localhost:3000

# Optie 2: PHP built-in server (mirror van Hostinger-productie)
php -S localhost:8765 -t public/
```

Beide serveren dezelfde HTML uit `public/`. Verschil: optie 1 gebruikt
`/api/aanmelden` (Express), optie 2 gebruikt `/aanmelden.php` (PHP).
De front-end is gepatcht voor `/aanmelden.php`, dus optie 2 is wat
op productie draait.

---

## Aanvullende stappen (later)

- **Analytics**: voeg [Plausible](https://plausible.io/) toe (€9/mnd,
  AVG-proof, geen cookie-banner nodig) of Google Analytics 4
- **E-mail-notificatie naar Kevin**: extend `aanmelden.php` met PHP's
  `mail()` of [PHPMailer](https://github.com/PHPMailer/PHPMailer) (SMTP
  via Hostinger inbegrepen) zodat je naast Notion ook direct een mail
  krijgt
- **Realmex-koppeling**: voeg een tweede cURL-call toe in
  `aanmelden.php` na de Notion-creatie, gericht op de Realmex webhook
- **Uptime monitoring**: hPanel heeft ingebouwde uptime-checks; geen
  externe service nodig
- **Backup van leads**: download `.leads.log` periodiek of stel een
  cronjob in hPanel in die hem mailt
