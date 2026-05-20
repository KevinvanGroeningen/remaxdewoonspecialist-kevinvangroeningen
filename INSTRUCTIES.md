# 🚀 REMAX De Woonspecialist — Opstartinstructies

## Wat heb je gekregen?

| Bestand | Omschrijving |
|---|---|
| `index.html` | De volledige landingspagina (RE/MAX huisstijl) |
| `server.js` | Node.js backend die leads opslaat in Notion |
| `package.json` | Lijst met benodigde software-pakketten |
| `.env.example` | Sjabloon voor je geheime instellingen |

---

## Stap 1 — Notion koppelen

Je Leads database staat al klaar in Notion. Je hebt alleen een **Notion Integration Token** nodig zodat de server er gegevens in mag schrijven.

1. Ga naar https://www.notion.so/my-integrations
2. Klik op **"+ New integration"**
3. Geef het een naam: bijv. **"REMAX Website"**
4. Kopieer de **Internal Integration Token** (begint met `secret_...`)
5. Ga in Notion naar je **Leads database** → klik rechtsbovenin op `···` → **"Add connections"** → zoek op "REMAX Website" en klik erop

---

## Stap 2 — Installeren op je computer

Je hebt **Node.js** nodig. Nog niet geïnstalleerd? Download via https://nodejs.org (kies de LTS-versie).

Open daarna een Terminal/Opdrachtprompt in de map met de bestanden:

```bash
# Maak het .env-bestand aan
cp .env.example .env

# Pas .env aan met je Notion token (open in Kladblok of Notepad)
# Zet je NOTION_TOKEN= (de secret_... code van stap 1)

# Installeer de benodigde software
npm install

# Start de website
npm start
```

De website draait nu op http://localhost:3000

---

## Stap 3 — Online zetten (hosting)

Voor een echte website online heb je een hosting-provider nodig. Aanbevolen opties:

### Optie A: Railway (eenvoudigst, gratis tier)
1. Ga naar https://railway.app en maak een gratis account
2. Klik "New Project" → "Deploy from GitHub repo"
3. Upload je bestanden via GitHub (of vraag je webdeveloper)
4. Voeg de omgevingsvariabelen toe (NOTION_TOKEN, etc.) in de Railway-instellingen
5. Je krijgt automatisch een URL zoals: `https://remax-woonspecialist.railway.app`

### Optie B: Render (ook gratis tier)
1. Ga naar https://render.com
2. Nieuwe "Web Service" → koppel GitHub
3. Build command: `npm install`
4. Start command: `node server.js`
5. Voeg omgevingsvariabelen toe

### Optie C: Eigen server / webhosting
Stuur de bestanden naar je hostingprovider en zorg dat Node.js beschikbaar is (veel hostingproviders ondersteunen dit). Zet de omgevingsvariabelen in het controlepaneel van je hosting.

---

## Wat gebeurt er als iemand het formulier invult?

1. Bezoeker vult het formulier in op jouw website
2. De server ontvangt de gegevens
3. Er wordt automatisch een nieuwe Lead aangemaakt in jouw **Notion Leads database** met:
   - Naam, e-mail, telefoonnummer
   - Max. budget, woningtype, locaties, koopmoment
   - Status = **Nieuw** (blauw)
   - Type = **Aankoop**
   - Bron = **Website zoekopdracht**
   - Volgende actie = **Bellen voor kennismakingsgesprek**
4. Jij ziet de nieuwe lead direct in Notion!

---

## Eigen domein koppelen

Zodra de website online staat, kun je via jouw domeinprovider (bijv. TransIP, SIDN, GoDaddy) een domein koppelen aan je hosting-URL. Bijv. `zoekopdracht.jouwkantoor.nl`

---

## Vragen?

Neem contact op met je webdeveloper of gebruik Claude voor verdere aanpassingen. De Notion database-ID is: `4bb8bddd-2ddf-445d-ac12-1d14ba3d109b`
