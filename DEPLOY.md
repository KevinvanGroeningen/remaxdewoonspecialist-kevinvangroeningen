# Deploy-handleiding — REMAX De Woonspecialist

Stappen om de site live te zetten, ingedeeld in 4 fasen.

---

## Fase 1 · Domein + hosting (≈ 30 min)

### 1.1 Domein kopen
Kies een hosting-/domeinprovider (advies: **TransIP** of **Hostnet** voor `.nl`):
- Suggestie: `kevin-vangroeningen.nl` of `kvg-aankoop.nl` of `woonspecialist-utrecht.nl`
- Kosten: ~€10/jr voor `.nl`

### 1.2 Hosting via Vercel (gratis, aanbevolen)

1. Maak een GitHub-account aan (als je dat nog niet hebt)
2. Maak een **private** repository "remax-woonspecialist" op github.com (zonder README of license)
3. Push deze repo:
   ```bash
   cd ~/remax-woonspecialist
   git remote add origin git@github.com:<jouw-username>/remax-woonspecialist.git
   git push -u origin main
   ```
4. Ga naar [vercel.com](https://vercel.com) → sign-in met GitHub
5. **New Project** → import "remax-woonspecialist"
6. **Framework Preset**: Other · **Output directory**: leeg laten · **Build Command**: leeg
7. **Deploy** → klaar in ~30 sec
8. Je krijgt een tijdelijke URL als `remax-woonspecialist.vercel.app` om te testen

### 1.3 Eigen domein koppelen
1. In Vercel → Project → **Settings** → **Domains** → Add `kevin-vangroeningen.nl`
2. Bij je domeinprovider: zet de DNS-records die Vercel geeft (CNAME of A-record)
3. Wacht 5-30 min, daarna is HTTPS automatisch actief

**Alternatief**: Netlify werkt identiek — kies wat je voorkeur heeft.

---

## Fase 2 · Formulier werkend maken (≈ 15 min)

De HTML verwijst nu naar `https://formspree.io/f/YOUR_FORM_ID`. Vervang dit met je eigen Formspree-ID:

### 2.1 Formspree-account
1. Maak een **gratis** account aan op [formspree.io](https://formspree.io/)
2. **New Form** → naam: "REMAX Zoekopdracht"
3. Notificatie-email: `kevinvangroeningen@remax.nl`
4. Kopieer de form-URL, bv. `https://formspree.io/f/xpzgdvkr`

### 2.2 Endpoint instellen in code
Zoek-en-vervang in beide HTML-bestanden:
```
YOUR_FORM_ID
```
Vervang met jouw ID (bv. `xpzgdvkr`).

Bestanden:
- `index.html` (regel ~3950)
- `index-en.html` (regel ~3950)

Commit en push:
```bash
git add index.html index-en.html
git commit -m "Wire form to Formspree endpoint"
git push
```

Vercel deployed automatisch binnen 30 sec.

### 2.3 Test
- Open de live site
- Vul stap 1 + stap 2 in van de form en klik op verzenden
- **Eerste keer**: Formspree stuurt een verificatie-mail naar `kevinvangroeningen@remax.nl` → klik op activeren
- Daarna komt elke inzending direct in je inbox

---

## Fase 3 · SEO basis (≈ 20 min)

### 3.1 Domein-URL fixen in code
Vervang alle `https://kevin-vangroeningen.nl/` in `index.html`, `index-en.html`, en `sitemap.xml` met je definitieve domein (bv. `https://kevinvgroeningen.com/`).

```bash
cd ~/remax-woonspecialist
# Mac/Linux:
grep -rl "kevin-vangroeningen.nl" *.html *.xml | xargs sed -i '' 's|kevin-vangroeningen.nl|jouw-echte-domein.nl|g'
git add -A && git commit -m "Update canonical URLs to production domain"
git push
```

### 3.2 Google Search Console
1. [search.google.com/search-console](https://search.google.com/search-console) → Property toevoegen
2. Verifieer eigendom (kies "DNS" → kopieer TXT record naar domeinprovider)
3. Submit sitemap: `https://jouw-domein.nl/sitemap.xml`

### 3.3 Google Business Profile koppelen
1. [google.com/business](https://business.google.com/) → vul jouw profiel aan met de URL
2. Voeg link toe in Reviews-sectie van de site (al gedaan: `https://www.google.com/search?q=REMAX+De+Woonspecialist+Utrecht+reviews`)

---

## Fase 4 · Privacy + analytics (≈ 30 min)

### 4.1 Privacyverklaring
De footer linkt nu naar `<a href="#">Privacyverklaring</a>` — vervang met je echte privacy-pagina:

Optie 1 — **iubenda.com** (€9/jaar, automatisch gegenereerd, AVG-compliant)
Optie 2 — **zelf schrijven**, bv. via [veiliginternetten.nl/privacyverklaring](https://veiliginternetten.nl/privacyverklaring/)

Plaats op `privacy.html` of een externe URL en update beide HTML-files.

### 4.2 Analytics (Plausible — privacy-vriendelijk, geen cookie-banner nodig)
1. Account op [plausible.io](https://plausible.io/) (€9/maand voor één site)
2. Voeg vlak voor `</head>` in beide HTML-files:
   ```html
   <script defer data-domain="jouw-domein.nl" src="https://plausible.io/js/script.js"></script>
   ```
3. Conversie-doel: in Plausible → Goals → "Custom event" → "form_submitted"
4. In je form-submit JS, na success, voeg toe:
   ```js
   if (window.plausible) plausible('form_submitted');
   ```

### 4.3 KvK + BTW in footer
Voeg toe aan footer in beide HTML-files:
```
KvK 12345678 · BTW NL001234567B01
```

---

## Fase 5 · Optioneel — Realmex-automatisering

Formspree heeft webhook-functionaliteit (alle plans). Stuur de form-data ook naar **Make.com** of **n8n**:

1. Formspree → Settings → **Integrations** → Webhook → URL van je Make/n8n endpoint
2. In Make/n8n: ontvang JSON, map velden naar Realmex API of CSV-export
3. Realmex contacteren voor API-credentials of bulk-import format

Velden in payload corresponderen al 1-op-1 met Realmex (zie README.md).

---

## Snelle launch-checklist

- [ ] Domein gekocht
- [ ] GitHub repo gepushed
- [ ] Vercel/Netlify gedeployed
- [ ] Domein gekoppeld + HTTPS actief
- [ ] Formspree form-ID ingesteld
- [ ] Eerste test-submission succesvol
- [ ] Canonical URLs aangepast
- [ ] Google Search Console + sitemap submitted
- [ ] Privacyverklaring opgesteld + gelinkt
- [ ] Plausible/GA4 + conversie-event live
- [ ] KvK/BTW in footer
- [ ] Mobile getest (iOS + Android)
- [ ] kevin.jpg gecomprimeerd (PageSpeed > 90)

Vragen onderweg? Bel me of kom langs op kantoor 😄
