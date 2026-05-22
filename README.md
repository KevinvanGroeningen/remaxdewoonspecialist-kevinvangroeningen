# REMAX De Woonspecialist — Aankoopmakelaar Utrecht

Landingspagina + Node.js backend voor Kevin van Groeningen, aankoopmakelaar bij REMAX De Woonspecialist in Utrecht. Bezoekers starten een gratis & vrijblijvende persoonlijke zoekopdracht; submissies worden opgeslagen in een Notion-database.

🌐 **Live**: *(domein-URL hier zodra gedeployed)*
📦 **Repo**: [github.com/KevinvanGroeningen/remaxdewoonspecialist-kevinvangroeningen](https://github.com/KevinvanGroeningen/remaxdewoonspecialist-kevinvangroeningen)

---

## Projectstructuur

```
remax-woonspecialist/                ← Repo-root = Hostinger public_html/
├── index.html                       ← NL hoofdpagina
├── index-en.html                    ← EN volledig vertaalde variant
├── aanmelden.php                    ← PHP form-handler (Notion + log)
├── secrets.example.php              ← Template voor Notion-credentials
├── .htaccess                        ← HTTPS, caching, security, blokkeert Node-bronfiles
├── kevin.jpg                        ← Portretfoto makelaar
├── utrecht.avif                     ← Hero achtergrond
├── robots.txt
├── sitemap.xml
├── app.js                           ← Express server (alleen voor lokale dev — geblokt op productie)
├── package.json                     ← Node-dependencies (lokaal)
├── .env.example                     ← Sjabloon Node-secrets (lokaal)
├── .gitignore                       ← Excludes .env, secrets.php, node_modules
├── Procfile / railway.json / render.yaml  ← Oude deploy-configs (ongebruikt)
├── README.md                        ← Dit bestand
├── DEPLOY.md                        ← Hostinger Business deploy-guide
├── INSTRUCTIES.md                   ← NL setup-guide
└── _design-variants/                ← 7 referentie-designs (niet voor productie, geblokt)
```

**Productie-stack**: Hostinger Business Web Hosting · PHP 8 + Apache
**Backend**: `aanmelden.php` (mirror van de oude Express-endpoint)
**Lokaal dev kan via Node OF PHP** — zie `DEPLOY.md`.

## Lokaal draaien

```bash
# Eénmalig: dependencies installeren
npm install

# Eénmalig: secrets configureren
cp .env.example .env
# Vul .env in (Notion token + database ID)

# Productie-modus
npm start                     # → http://localhost:3000

# Development-modus (auto-restart bij wijzigingen)
npm run dev

# Alleen statische preview (geen backend)
npm run preview               # → http://localhost:8080
```

## Backend

**Productie (Hostinger)**: één PHP-bestand handelt alles af:

| Endpoint | Method | Beschrijving |
|---|---|---|
| `POST /aanmelden.php` | POST | Form submission — opslaat in Notion + log naar `.leads.log` |

Statische assets (HTML, CSS, JS, images) serveert Hostinger's Apache
direct uit `public_html/`. Geen server-side render nodig.

**Lokaal (Node, alternatief)**: `app.js` exposeert dezelfde functionaliteit
via Express op `/api/aanmelden` + `/api/health` — handig om snel te
testen zonder PHP te draaien.

Beide endpoints accepteren JSON met dezelfde Realmex-compatibele velden.
Zie [`aanmelden.php`](./aanmelden.php) voor de productie-mapping.

## Form → Realmex veld-mapping

| JSON-key | Realmex-veld |
|---|---|
| `minimumPrice` / `maximumPrice` | Vraagprijs van/tot |
| `maxBodMetOverbieden` | Max bod incl. overbieden (intern, niet naar Realmex) |
| `bedrooms` | Bedrooms (minimaal) |
| `livingAreaFrom` / `livingAreaTo` | Living area m² |
| `liftRequired` | Lift required |
| `outdoorSpace` | Outdoor space |
| `minimumEnergyLabel` | Minimum energy label |
| `buildingYear` | Building year |
| `locaties` (array) | Hoofdsteden zoekgebied |
| `wijken` (object) | Wijken-selectie per stad |
| `zoekgebiedBuiten` | Vrije tekst buiten Utrecht |
| `woningtypes` (array) | Soort woning |
| `hypotheekgesprek` (bool) | Opt-in voor hypotheekgesprek |

## Deployment

Zie [`DEPLOY.md`](./DEPLOY.md) voor stap-voor-stap instructies.

**Productie-setup**: Hostinger Business Web Hosting (€/mnd incl. domein)
- GitHub-repo gekoppeld via hPanel → GIT (auto-sync bij `git push`)
- PHP 8 + Apache draait `aanmelden.php` zonder configuratie
- Notion-credentials in `secrets.php` (niet in git, gemaakt via hPanel)
- Gratis Let's Encrypt SSL via "Forceer HTTPS"

## Contact

Kevin van Groeningen · Aankoopmakelaar
📞 06 24 419 419 · ✉️ kevinvangroeningen@remax.nl
🏢 REMAX De Woonspecialist · Musicallaan 5, Utrecht
