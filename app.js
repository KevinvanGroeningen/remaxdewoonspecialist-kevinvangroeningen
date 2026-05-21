/**
 * REMAX De Woonspecialist — Zoekopdracht Backend
 * ─────────────────────────────────────────────────
 * Express-server die statische assets uit /public serveert
 * en formulierinzendingen verwerkt:
 *   • Opslaat in Notion Leads database
 *   • E-mail-notificatie naar makelaar
 *
 * Vereisten:  npm install
 * Start:      npm start  (of: node server.js)
 *
 * Production: zie DEPLOY.md voor Railway / Render / Vercel setup
 */

require('dotenv').config();
const express = require('express');
const cors = require('cors');
const path = require('path');
const { Client } = require('@notionhq/client');

const app = express();
const PORT = process.env.PORT || 3000;
const NODE_ENV = process.env.NODE_ENV || 'development';

// ─── Notion client ───────────────────────────────
const notion = process.env.NOTION_TOKEN ? new Client({ auth: process.env.NOTION_TOKEN }) : null;
const NOTION_DATABASE_ID = process.env.NOTION_DATABASE_ID || '';

if (!notion) {
  console.warn('⚠️  NOTION_TOKEN niet gezet — inzendingen worden gelogd maar niet opgeslagen.');
}

// ─── Middleware ──────────────────────────────────
app.use(cors());
app.use(express.json({ limit: '50kb' }));
app.use(express.static(path.join(__dirname, 'public'), {
  maxAge: NODE_ENV === 'production' ? '1d' : 0,
  etag: true
}));

// ─── Health-check ────────────────────────────────
app.get('/api/health', (_req, res) => {
  res.json({
    status: 'ok',
    env: NODE_ENV,
    notion: notion ? 'connected' : 'disabled',
    timestamp: new Date().toISOString()
  });
});

// ─── Hulpfuncties ────────────────────────────────
function formatEUR(amount) {
  if (!amount) return '—';
  return new Intl.NumberFormat('nl-NL', { style: 'currency', currency: 'EUR', maximumFractionDigits: 0 }).format(amount);
}

function buildSummary(p) {
  const lines = [
    `📍 Locaties: ${(p.locaties || []).join(', ')}`,
  ];
  if (p.wijken && Object.keys(p.wijken).length > 0) {
    Object.entries(p.wijken).forEach(([stad, wijken]) => {
      lines.push(`   ↳ ${stad}: ${wijken.join(', ')}`);
    });
  }
  if (p.zoekgebiedBuiten) lines.push(`📍 Buiten Utrecht: ${p.zoekgebiedBuiten}`);
  lines.push(`🏡 Woningtype: ${(p.woningtypes || []).join(', ')}`);
  lines.push(`💶 Vraagprijs: ${formatEUR(p.minimumPrice)} – ${formatEUR(p.maximumPrice)}`);
  if (p.maxBodMetOverbieden) lines.push(`💰 Max bod (incl. overbieden): ${formatEUR(p.maxBodMetOverbieden)}`);
  if (p.bedrooms) lines.push(`🛏️ Min. slaapkamers: ${p.bedrooms}`);
  if (p.livingAreaFrom || p.livingAreaTo) {
    lines.push(`📐 Woonoppervlakte: ${p.livingAreaFrom || '—'} – ${p.livingAreaTo || '—'} m²`);
  }
  if (p.liftRequired) lines.push(`🛗 Lift: ${p.liftRequired}`);
  if (p.outdoorSpace) lines.push(`🌳 Buitenruimte: ${p.outdoorSpace}`);
  if (p.minimumEnergyLabel) lines.push(`⚡ Min. energielabel: ${p.minimumEnergyLabel}`);
  if (p.buildingYear) lines.push(`🏗️ Bouwjaar: ${p.buildingYear}`);
  if (p.woonwensen) lines.push(`💬 Extra wensen: ${p.woonwensen}`);
  if (p.hypotheekgesprek) lines.push(`✦ Wil ook gratis hypotheekgesprek`);
  return lines.join('\n');
}

// ─── POST /api/aanmelden ─────────────────────────
app.post('/api/aanmelden', async (req, res) => {
  const p = req.body || {};

  // Honeypot — bots vullen verborgen velden in
  if (p._gotcha) return res.json({ success: true });

  // Basisvalidatie
  const errors = [];
  if (!p.voornaam || !p.achternaam) errors.push('naam');
  if (!p.email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(p.email)) errors.push('email');
  if (!p.telefoon) errors.push('telefoon');
  if (!p.maximumPrice) errors.push('maximumPrice');
  if (!p.locaties || p.locaties.length === 0) errors.push('locaties');
  if (!p.woningtypes || p.woningtypes.length === 0) errors.push('woningtypes');

  if (errors.length > 0) {
    return res.status(400).json({ error: `Verplichte velden ontbreken: ${errors.join(', ')}` });
  }

  const naam = `${p.voornaam} ${p.achternaam}`.trim();
  const summary = buildSummary(p);

  // Log altijd — handig voor debug en als backup
  console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
  console.log(`📥 ${new Date().toLocaleString('nl-NL')} — Nieuwe lead`);
  console.log(`   Naam:     ${naam}`);
  console.log(`   Email:    ${p.email}`);
  console.log(`   Telefoon: ${p.telefoon}`);
  console.log(summary.split('\n').map(l => `   ${l}`).join('\n'));
  console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

  // Als Notion niet is geconfigureerd, geef toch success terug
  if (!notion || !NOTION_DATABASE_ID) {
    return res.json({ success: true, notion: 'skipped' });
  }

  try {
    await notion.pages.create({
      parent: { database_id: NOTION_DATABASE_ID },
      properties: {
        'Naam': { title: [{ text: { content: naam } }] },
        'Email': { email: p.email },
        'Telefoon': { phone_number: p.telefoon },
        'Status': { select: { name: 'Nieuw' } },
        'Type': { select: { name: 'Aankoop' } },
        'Bron': { rich_text: [{ text: { content: p.bron || 'Website zoekopdracht' } }] },
        'Max. budget': { number: p.maximumPrice },
        ...(p.minimumPrice ? { 'Min. budget': { number: p.minimumPrice } } : {}),
        ...(p.maxBodMetOverbieden ? { 'Max. bod (incl. overbieden)': { number: p.maxBodMetOverbieden } } : {}),
        ...(p.bedrooms ? { 'Min. slaapkamers': { number: p.bedrooms } } : {}),
        'Gewenste locaties': { rich_text: [{ text: { content: (p.locaties || []).join(', ') } }] },
        'Woningtype': { multi_select: (p.woningtypes || []).map(t => ({ name: t })) },
        'Woonwensen': { rich_text: [{ text: { content: summary } }] },
        'Eerste contact': { date: { start: new Date().toISOString().split('T')[0] } },
        'Volgende actie': { rich_text: [{ text: { content: 'Bellen voor kennismakingsgesprek' } }] }
      }
    });

    console.log(`✅ Opgeslagen in Notion`);
    res.json({ success: true });

  } catch (err) {
    console.error('❌ Notion error:', err.body || err.message);
    // Geef geen 500 — de lead is gelogd, dus we willen de gebruiker niet teleurstellen
    res.json({ success: true, notion: 'failed' });
  }
});

// ─── Fallback: 404 voor onbekende API-paths ─────
app.use('/api/*', (_req, res) => res.status(404).json({ error: 'Not found' }));

// ─── Fallback: serveer index.html voor SPA-routes ─
app.get('*', (req, res) => {
  // Detecteer taal via Accept-Language header
  const lang = req.headers['accept-language']?.split(',')[0]?.split('-')[0] || 'nl';
  const file = lang === 'en' ? 'index-en.html' : 'index.html';
  res.sendFile(path.join(__dirname, 'public', file));
});

// ─── Start server ────────────────────────────────
app.listen(PORT, () => {
  console.log(`🚀 REMAX Woonspecialist server draait op http://localhost:${PORT}`);
  console.log(`📁 Public folder: ${path.join(__dirname, 'public')}`);
  console.log(`📋 Notion: ${notion ? `connected (db: ${NOTION_DATABASE_ID.slice(0, 8)}…)` : 'disabled (set NOTION_TOKEN to enable)'}`);
  console.log(`🌍 Environment: ${NODE_ENV}`);
});
