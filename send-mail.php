<?php
declare(strict_types=1);

// Fixed addresses prevent this endpoint from becoming an open mail relay.
const MAIL_FROM = 'info@uweb.by';
const MAIL_TO = 'sitehub24@gmail.com';
const MAX_BODY_BYTES = 16384;
const RATE_WINDOW = 600;
$formReturnPath = '/#contact';

function respond(int $status, bool $ok, string $message): void
{
    global $formReturnPath;
    http_response_code($status);
    header('Cache-Control: no-store');
    header('X-Content-Type-Options: nosniff');
    if (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['ok' => $ok, 'message' => $message], JSON_UNESCAPED_UNICODE);
    } else {
        header('Content-Type: text/html; charset=UTF-8');
        $title = $ok ? 'Заявка отправлена' : 'Заявку не удалось отправить';
        $safeMessage = htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        echo '<!doctype html><html lang="ru"><meta charset="utf-8">'
            . '<meta name="viewport" content="width=device-width,initial-scale=1">'
            . '<title>' . $title . ' — uweb.by</title>'
            . '<style>body{margin:0;background:#f6f8ff;color:#202e55;font:18px/1.7 Arial,sans-serif}'
            . 'main{max-width:600px;margin:12vh auto;padding:24px}h1{font-size:32px;line-height:1.25}'
            . 'a{color:#425fe2;display:inline-block;margin:12px 20px 0 0}</style>'
            . '<main><strong>uweb.by</strong><h1>' . $title . '</h1><p>' . $safeMessage . '</p>'
            . '<a href="' . $formReturnPath . '">Вернуться на сайт</a>'
            . '<a href="https://t.me/develop_by">Написать в Telegram</a></main></html>';
    }
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    respond(405, false, 'Используйте форму на сайте для отправки заявки.');
}

// Check browser origin, but do not require an Origin header from older browsers.
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$originHost = $origin === '' ? '' : parse_url($origin, PHP_URL_HOST);
$requestHost = parse_url('http://' . ($_SERVER['HTTP_HOST'] ?? ''), PHP_URL_HOST);
if (($_SERVER['HTTP_SEC_FETCH_SITE'] ?? '') === 'cross-site'
    || ($origin !== '' && (!$originHost || strcasecmp($originHost, (string) $requestHost) !== 0))) {
    respond(403, false, 'Отправьте заявку через форму на uweb.by.');
}

if ((int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > MAX_BODY_BYTES) {
    respond(413, false, 'Сообщение слишком длинное. Сократите описание проекта.');
}

$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (strpos($contentType, 'application/json') === 0) {
    $raw = file_get_contents('php://input', false, null, 0, MAX_BODY_BYTES + 1);
    if ($raw === false || strlen($raw) > MAX_BODY_BYTES) {
        respond(413, false, 'Сообщение слишком длинное.');
    }
    $data = json_decode($raw, true);
    if (!is_array($data) || json_last_error() !== JSON_ERROR_NONE) {
        respond(400, false, 'Не удалось прочитать заявку. Попробуйте ещё раз.');
    }
} elseif (strpos($contentType, 'application/x-www-form-urlencoded') === 0) {
    $data = $_POST;
} else {
    respond(415, false, 'Отправьте заявку через форму на сайте.');
}

foreach (['name', 'contact', 'service', 'message', 'website', 'context', 'site'] as $field) {
    if (isset($data[$field]) && !is_string($data[$field])) {
        respond(422, false, 'Проверьте поля заявки.');
    }
}

$context = $data['context'] ?? 'web';
if (!in_array($context, ['web', 'seo', 'ppc'], true)) {
    respond(422, false, 'Выберите услугу через форму на сайте.');
}
$formReturnPath = $context === 'web' ? '/#contact' : '/' . $context . '/#contact';

// Ignore the hidden bot field without sending email or using the rate quota.
if (trim($data['website'] ?? '') !== '') {
    respond(200, true, 'Заявка принята.');
}

$name = trim($data['name'] ?? '');
$contact = trim($data['contact'] ?? '');
$service = trim($data['service'] ?? '');
$message = trim($data['message'] ?? '');
$site = trim($data['site'] ?? '');
$serviceGroups = [
    'web' => ['Лендинг', 'Сайт компании', 'Интернет-магазин', 'Индивидуальное решение', 'План: сайт + реклама + SEO', 'Контекст Start', 'Контекст Standart', 'Контекст Pro', 'Комплексное SEO', 'Локальное SEO', 'SEO интернет-магазина', 'Технический аудит'],
    'seo' => ['Комплексное SEO', 'Локальное SEO', 'SEO интернет-магазина', 'Технический аудит'],
    'ppc' => ['Контекст Start', 'Контекст Standart', 'Контекст Pro'],
];
$services = $serviceGroups[$context];

if (preg_match('/\A.{1,80}\z/us', $name) !== 1
    || preg_match('/\A.{1,160}\z/us', $contact) !== 1
    || preg_match('/[\p{Cc}]/u', $name . $contact) !== 0
    || preg_match('/\A.{0,3000}\z/us', $message) !== 1
    || strpos($message, "\0") !== false
    || !in_array($service, $services, true)) {
    respond(422, false, 'Проверьте имя, контакт и описание проекта.');
}

// The address is included in the email only; the server never requests it.
if ($site !== '' && (strlen($site) > 2048
    || preg_match('/[\p{Cc}]/u', $site) !== 0
    || filter_var($site, FILTER_VALIDATE_URL) === false
    || !in_array(strtolower((string) parse_url($site, PHP_URL_SCHEME)), ['http', 'https'], true))) {
    respond(422, false, 'Укажите адрес сайта, начинающийся с https:// или http://.');
}

if (!function_exists('mail')) {
    error_log('uweb.by: PHP mail() is unavailable.');
    respond(503, false, 'Сейчас не удалось отправить заявку. Напишите мне в Telegram.');
}

// Private temporary storage, shared lock for simultaneous requests. Only hashes
// of IP addresses and short-lived timestamps are stored; no application text.
$rateDir = sys_get_temp_dir() . '/uweb-mail-' . substr(hash('sha256', __DIR__), 0, 24);
if (!is_dir($rateDir) && !@mkdir($rateDir, 0700, true) && !is_dir($rateDir)) {
    error_log('uweb.by: cannot create mail rate-limit storage.');
    respond(503, false, 'Сейчас не удалось отправить заявку. Попробуйте позже.');
}
$rateFile = @fopen($rateDir . '/limits.json', 'c+');
if ($rateFile === false || !flock($rateFile, LOCK_EX)) {
    if (is_resource($rateFile)) fclose($rateFile);
    respond(503, false, 'Сейчас не удалось отправить заявку. Попробуйте позже.');
}
$buckets = json_decode(stream_get_contents($rateFile), true);
$buckets = is_array($buckets) ? $buckets : [];
$now = time();
foreach ($buckets as $key => $timestamps) {
    $recent = is_array($timestamps) ? array_values(array_filter($timestamps, static function ($time) use ($now) {
        return is_int($time) && $time > $now - RATE_WINDOW;
    })) : [];
    if ($recent === []) unset($buckets[$key]);
    else $buckets[$key] = $recent;
}
$clientKey = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? 'unknown');
$clientTimes = $buckets[$clientKey] ?? [];
$total = 0;
foreach ($buckets as $timestamps) $total += count($timestamps);
if (count($clientTimes) >= 5 || $total >= 60) {
    flock($rateFile, LOCK_UN);
    fclose($rateFile);
    header('Retry-After: ' . RATE_WINDOW);
    respond(429, false, 'Слишком много попыток. Попробуйте через несколько минут.');
}
$clientTimes[] = $now;
$buckets[$clientKey] = $clientTimes;
rewind($rateFile);
$stored = ftruncate($rateFile, 0)
    && fwrite($rateFile, (string) json_encode($buckets)) !== false
    && fflush($rateFile);
flock($rateFile, LOCK_UN);
fclose($rateFile);
if (!$stored) {
    respond(503, false, 'Сейчас не удалось отправить заявку. Попробуйте позже.');
}

date_default_timezone_set('Europe/Minsk');
$applicationTitles = [
    'web' => 'Заявка на сайт и продвижение',
    'seo' => 'Заявка на SEO-продвижение',
    'ppc' => 'Заявка на контекстную рекламу',
];
$applicationTitle = $applicationTitles[$context];
$body = $applicationTitle . " — uweb.by\n\n"
    . "Формат: $service\nИмя: $name\nКонтакт: $contact\n"
    . ($site !== '' ? "Сайт: $site\n" : '')
    . "\nО проекте:\n"
    . ($message !== '' ? $message : 'Обсудим при знакомстве.')
    . "\n\nОтправлено: " . date('d.m.Y H:i') . " (Минск)\n";
$subjects = ['web' => 'Новая заявка — uweb.by', 'seo' => 'Заявка на SEO — uweb.by', 'ppc' => 'Заявка на PPC — uweb.by'];
$subjectText = $subjects[$context];
$subject = '=?UTF-8?B?' . base64_encode($subjectText) . '?=';
$headers = [
    'From: "uweb.by" <' . MAIL_FROM . '>',
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'Content-Transfer-Encoding: base64',
];
if (filter_var($contact, FILTER_VALIDATE_EMAIL) !== false) {
    $headers[] = 'Reply-To: ' . $contact;
}
$sent = @mail(MAIL_TO, $subject, chunk_split(base64_encode($body), 76, "\r\n"),
    implode("\r\n", $headers), '-f' . MAIL_FROM);
if (!$sent) {
    error_log('uweb.by: PHP mail() rejected the application.');
    respond(502, false, 'Не удалось отправить заявку. Попробуйте позже или напишите мне в Telegram.');
}
respond(200, true, 'Заявка отправлена. Спасибо! Свяжусь с вами по указанному контакту.');
