# REMAX De Woonspecialist — Aankoopmakelaar Utrecht

Landingspagina voor Kevin van Groeningen, aankoopmakelaar bij REMAX De Woonspecialist in Utrecht. Bezoekers kunnen een gratis & vrijblijvende persoonlijke zoekopdracht starten, en optioneel een gratis hypotheekgesprek aanvragen.

## Live-versies

| Versie | Bestand | Doel |
|--------|---------|------|
| Nederlands (default) | `index.html` | Hoofdpagina voor Nederlandstalige bezoekers |
| English | `index-en.html` | Volledig vertaalde Engelse variant |

Beide pagina's hebben een `NL / EN` taalswitch in de header die naar de andere variant linkt.

## Tech-stack

- **Frontend**: statische HTML/CSS/JS — geen build-step nodig
- **Fonts**: Inter + Instrument Serif (Google Fonts)
- **Backend** *(optioneel)*: Node.js + Express + Notion API (zie `server.js`)
- **Formulier**: 2-staps wizard met Realmex-compatibele veldnamen

## Lokaal draaien

Static-site preview (geen backend):
```bash
python3 -m http.server 8765
# → http://localhost:8765
```

Volledige stack mét Notion-backend:
```bash
cp .env.example .env
# Vul .env in met je Notion-token & database-id
npm install
npm start
# → http://localhost:3000
```

Zie `INSTRUCTIES.md` voor uitgebreide Nederlandstalige setup-instructies (Notion-koppeling, etc.).

## Bestanden

### Productie
- `index.html` — Hoofdpagina (NL)
- `index-en.html` — Engelse variant
- `kevin.jpg` — Portretfoto makelaar
- `utrecht.avif` — Hero-achtergrond (Utrechtse gracht)
- `server.js` — Node.js backend (Notion-integratie)
- `package.json` — Node dependencies
- `.env.example` — Sjabloon voor secrets

### Design-varianten (referentie)
Niet voor productie — design-experimenten uit ontwikkeling:
- `index-banners.html` — 3 hero-banner alternatieven
- `index-hero-alts.html` — 4 hero-layout alternatieven
- `index-steps-alts.html` — 3 stappenplan-layouts
- `index-bold.html` — Bold/brutalist style
- `index-editorial.html` — Editorial/magazine style
- `index-linen.html` — Warme/minimal style
- `index-nocturne.html` — Dark premium style

## Formulier — Realmex-veldmapping

Het 2-staps formulier stuurt een JSON-payload met velden die 1-op-1 overeenkomen met Realmex' zoekopdracht-velden:

| Form-key | Realmex-veld |
|---|---|
| `minimumPrice` | Minimum Price (Vraagprijs van) |
| `maximumPrice` | Maximum Price (Vraagprijs tot) |
| `maxBodMetOverbieden` | Max bod incl. overbieden (intern) |
| `bedrooms` | Bedrooms |
| `livingAreaFrom` / `livingAreaTo` | Living Area |
| `liftRequired` | Lift required |
| `outdoorSpace` | Outdoor space |
| `minimumEnergyLabel` | Minimum energy label |
| `buildingYear` | Building year |

Extra velden voor interne workflow: `locaties` (steden), `wijken` (per stad), `zoekgebiedBuiten`, `woningtypes`, `woonwensen`, `hypotheekgesprek` (opt-in).

## Deployment

Aanbevolen: **Vercel** of **Netlify** voor statische hosting (gratis tier voldoende). Backend (Notion-server.js) kan op **Render** of **Railway** draaien.

Zie ook de launch-checklist in chat-historie of vraag voor een uitgebreid deploy-document.

## Contact

Kevin van Groeningen · Aankoopmakelaar
📞 06 24 419 419
✉️ kevinvangroeningen@remax.nl
🏢 REMAX De Woonspecialist · Utrecht
