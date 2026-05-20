/**
 * REMAX De Woonspecialist — Zoekopdracht Backend
 * ────────────────────────────────────────────────
 * Ontvangt formulierinzendingen van de landingspagina
 * en slaat ze op in de Notion Leads database.
 *
 * Vereisten:
 *   npm install express cors @notionhq/client dotenv
 *
 * Gebruik:
 *   1. Kopieer .env.example naar .env en vul je gegevens in
 *   2. node server.js  (of: npm start)
 */

require('dotenv').config();
const express = require('express');
const cors = require('cors');
const path = require('path');
const { Client } = require('@notionhq/client');

const app = express();
const PORT = process.env.PORT || 3000;

// ─── Notion client ───────────────────────────────
const notion = new Client({ auth: process.env.NOTION_TOKEN });
const NOTION_DATABASE_ID = process.env.NOTION_DATABASE_ID || '4bb8bddd-2ddf-445d-ac12-1d14ba3d109b';

// ─── Middleware ──────────────────────────────────
app.use(cors());
app.use(express.json());
app.use(express.static(path.join(__dirname)));  // Serveert index.html

// ─── Hulpfunctie: budget formatteren ────────────
function formatBudget(amount) {
  return new Intl.NumberFormat('nl-NL', { style: 'currency', currency: 'EUR', maximumFractionDigits: 0 }).format(amount);
}

// ─── POST /api/aanmelden ─────────────────────────
app.post('/api/aanmelden', async (req, res) => {
  const {
    naam,
    email,
    telefoon,
    budget,
    slaapkamers,
    koopmoment,
    locaties,
    woningtypes,
    woonwensen
  } = req.body;

  // Basisvalidatie
  if (!naam || !email || !telefoon || !budget || !koopmoment || !locaties || !woningtypes?.length) {
    return res.status(400).json({ error: 'Verplichte velden ontbreken.' });
  }

  // Stel de woonwensen-samenvatting op
  const woonwensenSamenvatting = [
    `📍 Locaties: ${locaties}`,
    `🏡 Woningtype: ${woningtypes.join(', ')}`,
    `💶 Max. budget: ${formatBudget(budget)}`,
    slaapkamers ? `🛏️ Min. slaapkamers: ${slaapkamers}` : null,
    `⏰ Koopmoment: ${koopmoment}`,
    woonwensen ? `💬 Extra wensen: ${woonwensen}` : null
  ].filter(Boolean).join('\n');

  try {
    // Maak pagina aan in Notion
    await notion.pages.create({
      parent: { database_id: NOTION_DATABASE_ID },
      properties: {
        // Naam (title)
        'Naam': {
          title: [{ text: { content: naam } }]
        },
        // Email
        'Email': { email },
        // Telefoon
        'Telefoon': { phone_number: telefoon },
        // Status → Nieuw
        'Status': { select: { name: 'Nieuw' } },
        // Type → Aankoop
        'Type': { select: { name: 'Aankoop' } },
        // Bron
        'Bron': { rich_text: [{ text: { content: 'Website zoekopdracht' } }] },
        // Max. budget
        'Max. budget': { number: budget },
        // Min. slaapkamers
        ...(slaapkamers ? { 'Min. slaapkamers': { number: slaapkamers } } : {}),
        // Koopmoment
        'Koopmoment': { select: { name: koopmoment } },
        // Gewenste locaties
        'Gewenste locaties': { rich_text: [{ text: { content: locaties } }] },
        // Woningtype (multi-select)
        'Woningtype': {
          multi_select: woningtypes.map(t => ({ name: t }))
        },
        // Woonwensen (samenvatting)
        'Woonwensen': { rich_text: [{ text: { content: woonwensenSamenvatting } }] },
        // Eerste contact = vandaag
        'Eerste contact': {
          date: { start: new Date().toISOString().split('T')[0] }
        },
        // Volgende actie
        'Volgende actie': {
          rich_text: [{ text: { content: 'Bellen voor kennismakingsgesprek' } }]
        }
      }
    });

    console.log(`✅ Nieuwe lead opgeslagen: ${naam} <${email}>`);
    res.json({ success: true });

  } catch (err) {
    console.error('❌ Notion error:', err.message);
    res.status(500).json({ error: 'Opslaan in Notion mislukt.' });
  }
});

// ─── Fallback: stuur index.html voor alle routes ─
app.get('*', (req, res) => {
  res.sendFile(path.join(__dirname, 'index.html'));
});

// ─── Start server ────────────────────────────────
app.listen(PORT, () => {
  console.log(`🚀 REMAX Woonspecialist server draait op http://localhost:${PORT}`);
  console.log(`📋 Leads worden opgeslagen in Notion database: ${NOTION_DATABASE_ID}`);
});
