# Deploy-handleiding — REMAX De Woonspecialist

Stap-voor-stap om de Node.js-app live te krijgen op je eigen domein.

---

## Fase 1 · Domein kopen (≈ 10 min)

Kies een Nederlandse provider voor een `.nl`-domein:
- **[TransIP](https://www.transip.nl/domeinen/)** — €11,99/jr voor `.nl`
- **[Hostnet](https://www.hostnet.nl/domeinregistratie)** — €9,95/jr
- **[Mijn Domein](https://www.mijndomein.nl/)** — €7,50/jr

**Tip**: Suggesties:
- `kevin-vangroeningen.nl`
- `aankoopmakelaar-utrecht.nl`
- `kvg-aankoop.nl`
- `woonspecialist-utrecht.nl`

> ⚠️ Koop **alleen** het domein. Hosting komt in stap 2 (gratis bij Render/Railway).

---

## Fase 2 · Node hosting opzetten (≈ 15 min)

Twee gelijkwaardige opties — kies één.

### Optie A · Render.com (gratis tier, slaapt na 15 min inactiviteit)

1. **Account aanmaken**: [render.com](https://render.com/) → "Sign up with GitHub"
2. **Connect GitHub**: autoriseer Render voor je repo's
3. **New +** → **Web Service** → kies `remaxdewoonspecialist-kevinvangroeningen`
4. **Settings**:
   - Name: `remax-woonspecialist`
   - Region: `Frankfurt`
   - Branch: `main`
   - Runtime: `Node`
   - Build Command: `npm install`
   - Start Command: `node app.js`
   - Plan: **Free** (of `Starter $7/mnd` voor altijd-aan)
5. **Environment Variables** (Add):
   - `NODE_ENV` = `production`
   - `NOTION_TOKEN` = `secret_...` *(zie stap 3)*
   - `NOTION_DATABASE_ID` = `...` *(zie stap 3)*
6. **Create Web Service** → wacht ~3 min op build → live URL als `https://remax-woonspecialist.onrender.com`

> Alternatief: gebruik de `render.yaml` blueprint die al in de repo staat → Dashboard → New → **Blueprint** → kies repo.

### Optie B · Railway.app (€5/mnd, altijd aan, sneller)

1. **Account**: [railway.app](https://railway.app/) → "Login with GitHub"
2. **New Project** → **Deploy from GitHub repo** → kies `remaxdewoonspecialist-kevinvangroeningen`
3. Railway detecteert automatisch de `railway.json` config
4. **Variables** tab → Add:
   - `NOTION_TOKEN` = `secret_...`
   - `NOTION_DATABASE_ID` = `...`
   - `NODE_ENV` = `production`
5. **Settings** → **Generate Domain** voor tijdelijke `*.up.railway.app` URL
6. Project draait direct, kost ~€3-5/mnd

---

## Fase 3 · Notion-integratie (≈ 10 min)

Voor lead-opslag heb je een Notion integration token + database ID nodig.

### 3.1 Notion Integration aanmaken
1. Ga naar [notion.so/my-integrations](https://www.notion.so/my-integrations)
2. **+ New integration**
3. Naam: `REMAX Website`
4. Associated workspace: jouw workspace
5. **Submit** → kopieer de **Internal Integration Token** (begint met `secret_...` of `ntn_...`)

### 3.2 Database delen met de integratie
1. Open je Notion **Leads database**
2. Klik op `···` rechtsboven → **Add connections** → zoek "REMAX Website" → klik erop
3. Kopieer de Database ID:
   - URL: `notion.so/<workspace>/<DATABASE-ID>?v=...`
   - De `DATABASE-ID` is een 32-karakter UUID

### 3.3 Database properties (verplicht)
De server verwacht deze kolommen in je Notion-database:

| Property | Type |
|---|---|
| Naam | Title |
| Email | Email |
| Telefoon | Phone |
| Status | Select (incl. optie "Nieuw") |
| Type | Select (incl. optie "Aankoop") |
| Bron | Text |
| Max. budget | Number |
| Min. budget | Number |
| Max. bod (incl. overbieden) | Number |
| Min. slaapkamers | Number |
| Gewenste locaties | Text |
| Woningtype | Multi-select |
| Woonwensen | Text |
| Eerste contact | Date |
| Volgende actie | Text |

### 3.4 Env vars in Render/Railway zetten
Plak de tokens in de Environment Variables van de host (zie stap 2.5 of 2.4).

---

## Fase 4 · Eigen domein koppelen (≈ 30 min)

### 4.1 In Render of Railway
**Render**: Dashboard → Service → **Settings** → **Custom Domain** → Add → vul `kevin-vangroeningen.nl` in → kopieer de DNS-records die Render geeft

**Railway**: Project → **Settings** → **Networking** → **Add Custom Domain** → idem

### 4.2 DNS bij je domein-provider
Bij TransIP/Hostnet/etc:
1. Ga naar **DNS** of **DNS-beheer**
2. Verwijder bestaande A/AAAA records voor `@`
3. Voeg toe wat Render/Railway voorschrijft, meestal:
   - **CNAME**: `www` → `<jouw-app>.onrender.com` (of `.up.railway.app`)
   - **A**: `@` → het door de host opgegeven IP-adres
4. (Voor Render) Een **ALIAS** of **ANAME** record voor `@` als je provider dat ondersteunt — anders een redirect van root naar `www`

Wacht **5 min tot 24 uur** voor DNS-propagatie. Daarna is HTTPS automatisch actief (Let's Encrypt).

---

## Fase 5 · Productie-checks (≈ 20 min)

### 5.1 Canonical URLs aanpassen
In `public/index.html` en `public/index-en.html`, zoek-en-vervang `kevin-vangroeningen.nl` met je echte domein:

```bash
cd ~/remax-woonspecialist
grep -rl "kevin-vangroeningen.nl" public/*.html public/sitemap.xml | xargs sed -i '' 's|kevin-vangroeningen.nl|jouw-domein.nl|g'
git add -A && git commit -m "Update canonical URLs to production domain"
git push
```

Render/Railway deployen automatisch.

### 5.2 Eerste testsubmissie
- Open je domein
- Vul stap 1 + stap 2 in
- Submit
- Check je Notion-database → de lead moet verschijnen
- Check ook de Render/Railway logs voor `📥 Nieuwe lead`

### 5.3 Google Search Console
1. [search.google.com/search-console](https://search.google.com/search-console) → Property toevoegen
2. Verifieer via DNS (TXT-record bij je domein-provider)
3. Submit sitemap: `https://jouw-domein.nl/sitemap.xml`

### 5.4 Privacyverklaring + KvK
- Schrijf `public/privacy.html` (of gebruik [iubenda](https://www.iubenda.com/))
- Update de footer-link in beide index-files
- Voeg KvK + BTW-nummer toe in de footer

---

## Snelle deploy-checklist

- [ ] Domein gekocht
- [ ] Render of Railway account + GitHub gekoppeld
- [ ] Service deployt succesvol (`/api/health` returns OK)
- [ ] Notion integration aangemaakt + database gedeeld
- [ ] Env vars `NOTION_TOKEN` + `NOTION_DATABASE_ID` ingesteld
- [ ] Notion-database heeft alle vereiste kolommen
- [ ] Custom domain in Render/Railway toegevoegd
- [ ] DNS records bij domein-provider ingesteld
- [ ] HTTPS actief op eigen domein
- [ ] Canonical URLs in HTML aangepast
- [ ] Test-submissie → lead in Notion zichtbaar
- [ ] Google Search Console + sitemap submitted
- [ ] Privacyverklaring + KvK in footer

## Aanvullende stappen

- **Analytics**: voeg [Plausible](https://plausible.io/) toe (€9/mnd, AVG-proof, geen cookie-banner nodig) of GA4
- **Email-notificatie naar Kevin**: extend `app.js` met [Resend](https://resend.com/) of [SendGrid](https://sendgrid.com/) — Notion alleen is voldoende, maar e-mail is sneller
- **Realmex-koppeling**: stuur in `app.js` na succesvolle Notion-creatie ook door naar de Realmex API of webhook
- **Uptime monitoring**: [UptimeRobot](https://uptimerobot.com/) (gratis) — mailt je als de site down is
