<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/connectdb.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['reply' => 'Method not allowed']);
    exit();
}

$raw     = file_get_contents('php://input');
$data    = json_decode($raw, true);
$message = trim((string)($data['message'] ?? ''));

if ($message === '') {
    echo json_encode(['reply' => 'Send me a question and I\'ll help.']);
    exit();
}

$text = mb_strtolower($message);

function containsAny(string $hay, array $needles): bool {
    foreach ($needles as $n) {
        if ($n !== '' && mb_strpos($hay, $n) !== false) return true;
    }
    return false;
}

/* ---- Refund / return ---- */
if (containsAny($text, ['refund', 'return'])) {
    echo json_encode([
        'reply'       => "Refunds: If your order is eligible, you can request a refund within our policy window. Contact us with your order number and we'll sort it out.",
        'suggestions' => ['"Track my order"', '"How long is delivery?"', '"Contact support"'],
    ]);
    exit();
}

/* ---- Delivery ---- */
if (containsAny($text, ['delivery', 'shipping', 'postage'])) {
    echo json_encode([
        'reply'       => "As a digital store, all purchases are delivered instantly to your account after payment — no waiting, no postage.",
        'suggestions' => ['"Track my order"', '"Refund policy"'],
    ]);
    exit();
}

/* ---- Order tracking ---- */
if (containsAny($text, ['order', 'track', 'tracking', 'purchase'])) {
    echo json_encode([
        'reply'       => "You can view all your orders in Settings → Orders. If you need more help, tell me your order number and I'll do my best to assist.",
        'suggestions' => ['"Refund policy"', '"Delivery time"'],
    ]);
    exit();
}

/* ---- Product search ---- */
if (containsAny($text, ['do you have', 'in stock', 'price', 'available', 'ps5', 'xbox', 'pc', 'switch', 'platform', 'game'])) {
    $query  = preg_replace('/[^a-z0-9\s\-]/i', ' ', $message);
    $query  = trim(preg_replace('/\s+/', ' ', $query));
    $tokens = array_values(array_filter(explode(' ', mb_strtolower($query)), fn($t) => mb_strlen($t) >= 3));

    if (empty($tokens)) {
        echo json_encode(['reply' => 'Tell me the game name and platform (e.g., "Elden Ring PC").']);
        exit();
    }

    $likes  = [];
    $params = [];
    foreach ($tokens as $i => $tok) {
        $likes[]       = "LOWER(name) LIKE :t$i";
        $params[":t$i"] = "%$tok%";
    }

    $sql = "SELECT gid, name, price, platform
            FROM games
            WHERE " . implode(' AND ', $likes) . "
            ORDER BY name ASC LIMIT 4";

    try {
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$rows) {
            echo json_encode([
                'reply'       => "I couldn't find that exact match. Try a shorter game name or mention the platform (PC / PlayStation / Xbox / Switch).",
                'suggestions' => ['"Show me popular games"', '"Refund policy"'],
            ]);
            exit();
        }

        $lines = [];
        foreach ($rows as $r) {
            $lines[] = htmlspecialchars($r['name']) . " ({$r['platform']}) — £" . number_format((float)$r['price'], 2);
        }

        echo json_encode([
            'reply'       => "Here's what I found:\n" . implode("\n", $lines) . "\n\nWant a link to any of these?",
            'suggestions' => ['"Do you have this on PlayStation?"', '"What\'s the refund policy?"'],
        ]);
        exit();

    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['reply' => 'Sorry, I had trouble searching right now. Please try again.']);
        exit();
    }
}

/* ---- Default ---- */
echo json_encode([
    'reply'       => "I can help with: product availability, prices, platforms, delivery, refunds, and order tracking. What do you need?",
    'suggestions' => ['"Do you have [game] on PC?"', '"Track my order"', '"Refund policy"'],
]);