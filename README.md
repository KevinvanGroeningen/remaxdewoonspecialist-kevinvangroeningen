# REMAX De Woonspecialist — Aankoopmakelaar Utrecht

Landingspagina + Node.js backend voor Kevin van Groeningen, aankoopmakelaar bij REMAX De Woonspecialist in Utrecht. Bezoekers starten een gratis & vrijblijvende persoonlijke zoekopdracht; submissies worden opgeslagen in een Notion-database.

🌐 **Live**: *(domein-URL hier zodra gedeployed)*
📦 **Repo**: [github.com/KevinvanGroeningen/remaxdewoonspecialist-kevinvangroeningen](https://github.com/KevinvanGroeningen/remaxdewoonspecialist-kevinvangroeningen)

---

## Projectstructuur

```
remax-woonspecialist/
├── public/                  ← Statisch (door Express geserveerd)
│   ├── index.html           ← NL hoofdpagina
│   ├── index-en.html        ← EN volledig vertaalde variant
│   ├── kevin.jpg            ← Portretfoto makelaar
│   ├── utrecht.avif         ← Hero achtergrond
│   ├── robots.txt
│   └── sitemap.xml
├── app.js                ← Express server (entry-point)
├── package.json             ← Dependencies + scripts
├── .env.example             ← Sjabloon voor secrets
├── .gitignore               ← Excludes .env, node_modules, etc.
├── Procfile                 ← Heroku/Railway start-command
├── railway.json             ← Railway config (+ healthcheck)
├── render.yaml              ← Render blueprint
├── README.md                ← Dit bestand
├── DEPLOY.md                ← Stap-voor-stap deploy-guide
├── INSTRUCTIES.md           ← NL setup-guide voor Kevin
└── _design-variants/        ← 7 referentie-designs (niet voor productie)
```

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

## API

| Endpoint | Method | Beschrijving |
|---|---|---|
| `GET /api/health` | GET | Health check — returns `{status, env, notion, timestamp}` |
| `POST /api/aanmelden` | POST | Form submission — opslaat in Notion + logt |
| `GET /*` | GET | Statische assets uit `/public` |

Het form-submit endpoint accepteert JSON met alle Realmex-compatibele velden. Zie [`app.js`](./app.js) voor de exacte mapping.

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

Zie [`DEPLOY.md`](./DEPLOY.md) voor stap-voor-stap instructies. Aanbevolen:
- **Railway** of **Render** voor de Node-server (vanaf €0–€5/mnd)
- **TransIP** of **Hostnet** voor `.nl`-domein (~€10/jr)

## Contact

Kevin van Groeningen · Aankoopmakelaar
📞 06 24 419 419 · ✉️ kevinvangroeningen@remax.nl
🏢 REMAX De Woonspecialist · Musicallaan 5, Utrecht
