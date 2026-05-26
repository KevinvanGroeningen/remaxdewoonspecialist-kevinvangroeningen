<?php
/**
 * REMAX De Woonspecialist — Aanmelden endpoint (PHP-versie)
 * ─────────────────────────────────────────────────────────
 * Identieke functionaliteit als /api/aanmelden uit app.js (Node):
 *   • Honeypot-detectie
 *   • Basisvalidatie van verplichte velden
 *   • Lokale log-backup (.leads.log)
 *   • POST naar Notion-database via cURL
 *
 * Vereisten: PHP 8.0+ (Hostinger Business heeft dit standaard)
 * Optioneel: php-intl extensie voor mooie EUR-formatting (anders fallback)
 *
 * Configuratie: kopieer secrets.example.php → secrets.php en vul in.
 */

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// ─── Alleen POST toegestaan ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// ─── Lees JSON body ──────────────────────────────────────────────────
$raw = file_get_contents('php://input');
$p = json_decode($raw, true) ?: [];

// ─── Honeypot — bots vullen verborgen velden in ──────────────────────
if (!empty($p['_gotcha'])) {
    echo json_encode(['success' => true]);
    exit;
}

// ─── Basisvalidatie ──────────────────────────────────────────────────
$errors = [];
if (empty($p['voornaam']) || empty($p['achternaam']))                                     $errors[] = 'naam';
if (empty($p['email']) || !filter_var($p['email'], FILTER_VALIDATE_EMAIL))                $errors[] = 'email';
if (empty($p['telefoon']))                                                                $errors[] = 'telefoon';
if (empty($p['maximumPrice']))                                                            $errors[] = 'maximumPrice';
if (empty($p['locaties']) || !is_array($p['locaties']) || count($p['locaties']) === 0)    $errors[] = 'locaties';
if (empty($p['woningtypes']) || !is_array($p['woningtypes']) || count($p['woningtypes']) === 0) $errors[] = 'woningtypes';

if (count($errors) > 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Verplichte velden ontbreken: ' . implode(', ', $errors)]);
    exit;
}

// ─── Hulpfuncties ────────────────────────────────────────────────────
function formatEUR($amount) {
    if (!$amount) return '—';
    if (extension_loaded('intl')) {
        $fmt = numfmt_create('nl_NL', NumberFormatter::CURRENCY);
        numfmt_set_attribute($fmt, NumberFormatter::FRACTION_DIGITS, 0);
        return numfmt_format_currency($fmt, (float)$amount, 'EUR');
    }
    return '€ ' . number_format((float)$amount, 0, ',', '.');
}

function buildSummary($p) {
    $lines = [];
    $lines[] = '📍 Locaties: ' . implode(', ', $p['locaties'] ?? []);
    if (!empty($p['wijken']) && is_array($p['wijken'])) {
        foreach ($p['wijken'] as $stad => $wijken) {
            if (is_array($wijken) && count($wijken) > 0) {
                $lines[] = "   ↳ {$stad}: " . implode(', ', $wijken);
            }
        }
    }
    if (!empty($p['zoekgebiedBuiten'])) $lines[] = '📍 Buiten Utrecht: ' . $p['zoekgebiedBuiten'];
    $lines[] = '🏡 Woningtype: ' . implode(', ', $p['woningtypes'] ?? []);
    $lines[] = '💶 Vraagprijs: ' . formatEUR($p['minimumPrice'] ?? null) . ' – ' . formatEUR($p['maximumPrice'] ?? null);
    if (!empty($p['maxBodMetOverbieden'])) $lines[] = '💰 Max bod (incl. overbieden): ' . formatEUR($p['maxBodMetOverbieden']);
    if (!empty($p['bedrooms'])) $lines[] = '🛏️ Min. slaapkamers: ' . $p['bedrooms'];
    if (!empty($p['livingAreaFrom']) || !empty($p['livingAreaTo'])) {
        $lines[] = '📐 Woonoppervlakte: ' . ($p['livingAreaFrom'] ?? '—') . ' – ' . ($p['livingAreaTo'] ?? '—') . ' m²';
    }
    if (!empty($p['liftRequired']))         $lines[] = '🛗 Lift: ' . $p['liftRequired'];
    if (!empty($p['outdoorSpace']))         $lines[] = '🌳 Buitenruimte: ' . $p['outdoorSpace'];
    if (!empty($p['minimumEnergyLabel']))   $lines[] = '⚡ Min. energielabel: ' . $p['minimumEnergyLabel'];
    if (!empty($p['buildingYear']))         $lines[] = '🏗️ Bouwjaar: ' . $p['buildingYear'];
    if (!empty($p['woonwensen']))           $lines[] = '💬 Extra wensen: ' . $p['woonwensen'];
    if (!empty($p['hypotheekgesprek']))     $lines[] = '✦ Wil ook gratis hypotheekgesprek';
    return implode("\n", $lines);
}

$naam    = trim($p['voornaam'] . ' ' . $p['achternaam']);
$summary = buildSummary($p);

// ─── Log naar bestand (backup als Notion faalt) ──────────────────────
// Bestand begint met '.' zodat het via standaard listings verborgen is.
// .htaccess blokkeert daarnaast directe HTTP-toegang.
$logFile = __DIR__ . '/.leads.log';
$logEntry  = "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$logEntry .= '📥 ' . date('d-m-Y H:i:s') . " — Nieuwe lead\n";
$logEntry .= "   Naam:     {$naam}\n";
$logEntry .= "   Email:    {$p['email']}\n";
$logEntry .= "   Telefoon: {$p['telefoon']}\n";
$logEntry .= preg_replace('/^/m', '   ', $summary) . "\n";
$logEntry .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
@file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);

// ─── Laad Notion-credentials + e-mail-config ─────────────────────────
// Zoek secrets.php eerst BUITEN public_html (survives Git auto-deploy
// die untracked files in public_html kan opruimen), valt terug op de
// oude locatie binnen public_html voor lokale dev / backward compat.
$secretsCandidates = [
    dirname(__DIR__) . '/secrets.php',   // <home>/secrets.php  ← productie
    __DIR__ . '/secrets.php',            // public_html/secrets.php  ← lokale dev
];
$secrets = [];
foreach ($secretsCandidates as $sp) {
    if (file_exists($sp)) { $secrets = require $sp; break; }
}
$NOTION_TOKEN       = $secrets['NOTION_TOKEN']       ?? getenv('NOTION_TOKEN')       ?: '';
$NOTION_DATABASE_ID = $secrets['NOTION_DATABASE_ID'] ?? getenv('NOTION_DATABASE_ID') ?: '';
$NOTIFY_EMAIL       = $secrets['NOTIFY_EMAIL']       ?? getenv('NOTIFY_EMAIL')       ?: '';
$NOTIFY_FROM        = $secrets['NOTIFY_FROM']        ?? getenv('NOTIFY_FROM')
                      ?: 'website@' . ($_SERVER['HTTP_HOST'] ?? 'localhost');

// Geen Notion geconfigureerd → succes (lead staat in log)
if (!$NOTION_TOKEN || !$NOTION_DATABASE_ID) {
    echo json_encode(['success' => true, 'notion' => 'skipped']);
    exit;
}

// ─── POST naar Notion API ────────────────────────────────────────────
$properties = [
    'Naam'              => ['title'        => [['text' => ['content' => $naam]]]],
    'Email'             => ['email'        => $p['email']],
    'Telefoon'          => ['phone_number' => $p['telefoon']],
    'Status'            => ['select'       => ['name' => 'Nieuw']],
    'Type'              => ['select'       => ['name' => 'Aankoop']],
    'Bron'              => ['rich_text'    => [['text' => ['content' => $p['bron'] ?? 'Website zoekopdracht']]]],
    'Max. budget'       => ['number'       => (float)$p['maximumPrice']],
    'Gewenste locaties' => ['rich_text'    => [['text' => ['content' => implode(', ', $p['locaties'])]]]],
    'Woningtype'        => ['multi_select' => array_map(fn($t) => ['name' => $t], $p['woningtypes'])],
    'Woonwensen'        => ['rich_text'    => [['text' => ['content' => $summary]]]],
    'Eerste contact'    => ['date'         => ['start' => date('Y-m-d')]],
    'Volgende actie'    => ['rich_text'    => [['text' => ['content' => 'Bellen voor kennismakingsgesprek']]]],
];

if (!empty($p['minimumPrice']))        $properties['Min. budget']                 = ['number' => (float)$p['minimumPrice']];
if (!empty($p['maxBodMetOverbieden'])) $properties['Max. bod (incl. overbieden)'] = ['number' => (float)$p['maxBodMetOverbieden']];
if (!empty($p['bedrooms']))            $properties['Min. slaapkamers']            = ['number' => (int)$p['bedrooms']];

$payload = json_encode([
    'parent'     => ['database_id' => $NOTION_DATABASE_ID],
    'properties' => $properties,
], JSON_UNESCAPED_UNICODE);

$ch = curl_init('https://api.notion.com/v1/pages');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => [
        'Authorization: Bearer ' . $NOTION_TOKEN,
        'Notion-Version: 2022-06-28',
        'Content-Type: application/json',
    ],
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_CONNECTTIMEOUT => 5,
]);
$response = curl_exec($ch);
$status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

$notionOk = ($status >= 200 && $status < 300);
if (!$notionOk) {
    // Lead staat al in lokaal logbestand. Notion-fout intern loggen.
    @file_put_contents($logFile,
        "❌ Notion error (HTTP {$status}): " . ($curlErr ?: substr((string)$response, 0, 500)) . "\n\n",
        FILE_APPEND | LOCK_EX);
}

// ─── Stuur notificatie-e-mail naar de makelaar ───────────────────────
$emailSent = false;
if ($NOTIFY_EMAIL) {
    $host = $_SERVER['HTTP_HOST'] ?? 'website';
    $subject = '🏠 Nieuwe lead via website — ' . $naam;

    $rows = [
        ['Naam',     htmlspecialchars($naam)],
        ['E-mail',   '<a href="mailto:' . htmlspecialchars($p['email']) . '">' . htmlspecialchars($p['email']) . '</a>'],
        ['Telefoon', '<a href="tel:' . htmlspecialchars(preg_replace('/\s+/', '', $p['telefoon'])) . '">' . htmlspecialchars($p['telefoon']) . '</a>'],
        ['Locaties', htmlspecialchars(implode(', ', $p['locaties']))],
    ];
    if (!empty($p['wijken']) && is_array($p['wijken'])) {
        foreach ($p['wijken'] as $stad => $wijken) {
            if (is_array($wijken) && count($wijken) > 0) {
                $rows[] = ['Wijken ' . htmlspecialchars($stad), htmlspecialchars(implode(', ', $wijken))];
            }
        }
    }
    if (!empty($p['zoekgebiedBuiten'])) $rows[] = ['Buiten Utrecht', htmlspecialchars($p['zoekgebiedBuiten'])];
    $rows[] = ['Woningtype', htmlspecialchars(implode(', ', $p['woningtypes']))];
    $rows[] = ['Vraagprijs', formatEUR($p['minimumPrice'] ?? null) . ' – ' . formatEUR($p['maximumPrice'] ?? null)];
    if (!empty($p['maxBodMetOverbieden'])) $rows[] = ['Max bod (incl. overbieden)', formatEUR($p['maxBodMetOverbieden'])];
    if (!empty($p['bedrooms']))            $rows[] = ['Min. slaapkamers', (int)$p['bedrooms']];
    if (!empty($p['livingAreaFrom']) || !empty($p['livingAreaTo'])) {
        $rows[] = ['Woonoppervlakte', ($p['livingAreaFrom'] ?? '—') . ' – ' . ($p['livingAreaTo'] ?? '—') . ' m²'];
    }
    if (!empty($p['liftRequired']))       $rows[] = ['Lift', htmlspecialchars($p['liftRequired'])];
    if (!empty($p['outdoorSpace']))       $rows[] = ['Buitenruimte', htmlspecialchars($p['outdoorSpace'])];
    if (!empty($p['minimumEnergyLabel'])) $rows[] = ['Min. energielabel', htmlspecialchars($p['minimumEnergyLabel'])];
    if (!empty($p['buildingYear']))       $rows[] = ['Bouwjaar', htmlspecialchars($p['buildingYear'])];
    if (!empty($p['woonwensen']))         $rows[] = ['Extra wensen', nl2br(htmlspecialchars($p['woonwensen']))];
    if (!empty($p['hypotheekgesprek']))   $rows[] = ['<strong>Hypotheekgesprek</strong>', '✦ Ja, wil ook gratis hypotheekgesprek'];

    $tableHtml = '';
    foreach ($rows as $r) {
        $tableHtml .= '<tr><td style="padding:8px 14px;border-bottom:1px solid #eee;color:#666;font-size:13px;width:160px;vertical-align:top">' . $r[0] . '</td>'
                    . '<td style="padding:8px 14px;border-bottom:1px solid #eee;color:#111;font-size:14px">' . $r[1] . '</td></tr>';
    }

    $notionBadge = $notionOk
        ? '<span style="background:#d1fae5;color:#065f46;padding:3px 9px;border-radius:4px;font-size:11px;font-weight:600">✓ In Notion</span>'
        : '<span style="background:#fee2e2;color:#991b1b;padding:3px 9px;border-radius:4px;font-size:11px;font-weight:600">⚠ Notion sync mislukt</span>';

    $body = '<!doctype html><html><body style="margin:0;padding:24px;background:#f5f5f5;font-family:-apple-system,BlinkMacSystemFont,sans-serif">'
          . '<table cellpadding="0" cellspacing="0" style="max-width:600px;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.05)">'
          . '<tr><td style="padding:24px 28px;background:#DC1432;color:#fff">'
          . '<div style="font-size:13px;letter-spacing:1px;opacity:0.85;text-transform:uppercase">REMAX De Woonspecialist</div>'
          . '<div style="font-size:22px;font-weight:700;margin-top:4px">Nieuwe zoekopdracht ontvangen</div>'
          . '<div style="font-size:14px;margin-top:8px;opacity:0.9">' . date('l j F Y · H:i') . ' · ' . $notionBadge . '</div>'
          . '</td></tr>'
          . '<tr><td style="padding:8px 0"><table cellpadding="0" cellspacing="0" style="width:100%">' . $tableHtml . '</table></td></tr>'
          . '<tr><td style="padding:18px 28px;background:#fafafa;font-size:12px;color:#777;border-top:1px solid #eee">'
          . 'Bron: ' . htmlspecialchars($p['bron'] ?? 'website') . ' &nbsp;·&nbsp; '
          . 'Antwoord via reply (gaat direct naar de klant) of bel <a href="tel:' . htmlspecialchars(preg_replace('/\s+/', '', $p['telefoon'])) . '">' . htmlspecialchars($p['telefoon']) . '</a>'
          . '</td></tr></table></body></html>';

    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: REMAX Website <' . $NOTIFY_FROM . '>',
        'Reply-To: ' . $naam . ' <' . $p['email'] . '>',
        'X-Mailer: aanmelden.php',
    ];

    // Encode subject voor UTF-8 (emoji)
    $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

    $emailSent = @mail($NOTIFY_EMAIL, $encodedSubject, $body, implode("\r\n", $headers));

    if (!$emailSent) {
        @file_put_contents($logFile, "⚠️ Mail-notificatie naar {$NOTIFY_EMAIL} faalde\n\n", FILE_APPEND | LOCK_EX);
    }
}

// ─── Response ────────────────────────────────────────────────────────
$resp = ['success' => true];
if (!$notionOk)                       $resp['notion'] = 'failed';
if ($NOTIFY_EMAIL && !$emailSent)     $resp['mail']   = 'failed';
echo json_encode($resp);
