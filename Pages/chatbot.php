<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
session_start();

require_once 'C:\xampp\htdocs\Team_Project_TP2_Games_Store\Pages\connectdb.php';

// Basic hardening
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['reply' => 'Method not allowed']);
  exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

$message = trim((string)($data['message'] ?? ''));
$sessionId = trim((string)($data['sessionId'] ?? session_id()));

if ($message === '') {
  echo json_encode(['reply' => 'Send me a question and I’ll help.']);
  exit;
}

// ---- Simple intent detection ----
$text = mb_strtolower($message);

function containsAny(string $hay, array $needles): bool {
  foreach ($needles as $n) {
    if ($n !== '' && mb_strpos($hay, $n) !== false) return true;
  }
  return false;
}

// FAQs
if (containsAny($text, ['refund', 'return'])) {
  echo json_encode([
    'reply' => "Refunds/Returns: If your order is eligible, you can request a return/refund within our policy window. Tell me your order number and I’ll point you to the right step.",
    'suggestions' => ['“Track my order”', '“How long is delivery?”', '“Contact support”']
  ]);
  exit;
}

if (containsAny($text, ['delivery', 'shipping', 'postage'])) {
  echo json_encode([
    'reply' => "Delivery: We offer standard delivery options depending on your location. If you tell me your postcode/city (or just ‘UK’), I can estimate typical delivery times we show at checkout.",
    'suggestions' => ['“Track my order”', '“Refund policy”']
  ]);
  exit;
}

if (containsAny($text, ['order', 'track', 'tracking'])) {
  echo json_encode([
    'reply' => "To track an order, reply with your order number (e.g., CB12345). If you’re logged in, you can also check your Orders page.",
    'suggestions' => ['“Refund policy”', '“Delivery time”']
  ]);
  exit;
}

// Product search intent
if (containsAny($text, ['do you have', 'in stock', 'price', 'available', 'ps5', 'xbox', 'pc', 'switch', 'steam', 'platform'])) {
  // naive keyword extraction: keep letters/numbers/spaces
  $query = preg_replace('/[^a-z0-9\s\-]/i', ' ', $message);
  $query = trim(preg_replace('/\s+/', ' ', $query));

  // Split into tokens and keep decent ones
  $tokens = array_values(array_filter(explode(' ', mb_strtolower($query)), function($t){
    return mb_strlen($t) >= 3;
  }));

  // If no good tokens, ask to rephrase
  if (count($tokens) === 0) {
    echo json_encode(['reply' => "Tell me the game name and platform (e.g., “Elden Ring PC”)."]);
    exit;
  }

  // Build LIKE conditions
  $likes = [];
  $params = [];
  foreach ($tokens as $i => $tok) {
    $likes[] = "LOWER(g.title) LIKE :t$i";
    $params[":t$i"] = "%$tok%";
  }

  // Adjust table/columns to your schema:
  // games: id, title, price, platform, stock, slug
  $sql = "SELECT g.id, g.title, g.price, g.platform, g.stock, g.slug
          FROM games g
          WHERE " . implode(' AND ', $likes) . "
          ORDER BY g.stock DESC, g.title ASC
          LIMIT 3";

  try {
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$rows) {
      echo json_encode([
        'reply' => "I couldn’t find that exact match. Try a shorter game name or tell me the platform (PC / PS5 / Xbox / Switch).",
        'suggestions' => ['“Show popular games”', '“Refund policy”']
      ]);
      exit;
    }

    $lines = [];
    foreach ($rows as $r) {
      $title = (string)$r['title'];
      $platform = (string)$r['platform'];
      $price = number_format((float)$r['price'], 2);
      $stock = (int)$r['stock'];

      $stockText = $stock > 0 ? "In stock" : "Out of stock";
      $lines[] = "$title ($platform) — £$price — $stockText";
    }

    echo json_encode([
      'reply' => "Here’s what I found:\n" . implode("\n", $lines) . "\n\nWant me to link you to one of these? Reply with the title.",
      'suggestions' => ['“Do you have this on PS5?”', '“What’s delivery time?”']
    ]);
    exit;

  } catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['reply' => 'Server error while searching products.']);
    exit;
  }
}

// Default fallback
echo json_encode([
  'reply' => "I can help with: product availability/prices, platforms, delivery, refunds, and order tracking. What do you need?",
  'suggestions' => ['“Do you have [game] on PC?”', '“Track my order”', '“Refund policy”']
]);