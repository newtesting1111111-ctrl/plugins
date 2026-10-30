<?php
declare(strict_types=1);

// Shared rendering and validation. Edit the values in settings.php only.
function site_settings(): array
{
    static $settings;
    if ($settings !== null) return $settings;
    $candidate = require __DIR__ . '/settings.php';
    if (!is_array($candidate) || !is_array($candidate['contacts'] ?? null)
        || !is_array($candidate['prices'] ?? null) || !is_array($candidate['mail'] ?? null)) {
        throw new RuntimeException('Проверьте структуру settings.php.');
    }
    foreach (['name', 'phone', 'email', 'telegram'] as $key) {
        if (!is_string($candidate['contacts'][$key] ?? null)) {
            throw new RuntimeException('Проверьте contacts.' . $key . ' в settings.php.');
        }
    }
    if (!filter_var($candidate['contacts']['email'], FILTER_VALIDATE_EMAIL)
        || !filter_var($candidate['mail']['from'] ?? '', FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('Проверьте email и mail.from в settings.php.');
    }
    $viber = $candidate['contacts']['viber'] ?? null;
    if ($viber !== null && !is_string($viber)) {
        throw new RuntimeException('Viber: укажите номер, null или пустую строку.');
    }
    foreach (['landing', 'company', 'shop', 'adsMonthly', 'additionalCampaign', 'seoMonthly'] as $key) {
        $price = $candidate['prices'][$key] ?? null;
        if ((!is_int($price) && !is_float($price)) || !is_finite((float) $price) || $price < 0) {
            throw new RuntimeException('Проверьте prices.' . $key . ' в settings.php.');
        }
    }
    if (!is_string($candidate['currency'] ?? null) || $candidate['currency'] === '') {
        throw new RuntimeException('Проверьте currency в settings.php.');
    }
    if (!is_array($candidate['extras'] ?? null)) {
        throw new RuntimeException('Проверьте extras в settings.php.');
    }
    foreach ($candidate['extras'] as $id => $extra) {
        if (!is_string($id) || preg_match('/\A[a-z][a-z0-9_-]{0,39}\z/', $id) !== 1
            || !is_array($extra) || !is_string($extra['label'] ?? null) || $extra['label'] === '') {
            throw new RuntimeException('Проверьте дополнительные работы extras в settings.php.');
        }
        $extraPrice = $extra['price'] ?? null;
        $candidate['extras'][$id]['price'] = $extraPrice;
        $type = $extra['type'] ?? 'fixed';
        if (!in_array($type, ['fixed', 'monthly', 'percent'], true)) {
            throw new RuntimeException('Тип допзадачи: fixed, monthly или percent.');
        }
        $candidate['extras'][$id]['type'] = $type;
        if ($extraPrice !== null && ((!is_int($extraPrice) && !is_float($extraPrice))
            || !is_finite((float) $extraPrice) || $extraPrice < 0)) {
            throw new RuntimeException('Цена допработы должна быть числом или null.');
        }
    }
    $maxCampaigns = $candidate['calculator']['maxCampaigns'] ?? null;
    if (!is_int($maxCampaigns) || $maxCampaigns < 1 || $maxCampaigns > 50) {
        throw new RuntimeException('maxCampaigns: целое число от 1 до 50.');
    }
    $faq = $candidate['faq'] ?? [];
    if (!is_array($faq)) throw new RuntimeException('FAQ должен быть массивом вопросов.');
    $ids = [];
    $candidate['faq'] = [];
    foreach (array_values($faq) as $index => $item) {
        if (!is_array($item) || !is_string($item['question'] ?? null) || trim($item['question']) === ''
            || !is_string($item['answer'] ?? null) || trim($item['answer']) === '') {
            throw new RuntimeException('У каждого вопроса FAQ должны быть question и answer.');
        }
        $id = $item['id'] ?? 'question-' . ($index + 1);
        if (!is_string($id) || preg_match('/\A[a-zA-Z0-9_-]{1,60}\z/', $id) !== 1) {
            throw new RuntimeException('ID вопроса: буквы, цифры, дефис и подчёркивание.');
        }
        $baseId = $id;
        $suffix = $index + 1;
        while (in_array($id, $ids, true)) $id = $baseId . '-' . $suffix++;
        $ids[] = $id;
        $candidate['faq'][] = ['id' => $id, 'question' => $item['question'], 'answer' => $item['answer']];
    }
    $settings = $candidate;
    return $settings;
}

function site_e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function site_price(string $key): string
{
    $value = site_settings()['prices'][$key];
    return number_format($value, floor($value) === (float) $value ? 0 : 2, ',', ' ');
}

function site_money(string $key): string
{
    return site_price($key) . ' ' . site_settings()['currency'];
}

function site_phone_uri(string $phone): string
{
    $digits = preg_replace('/\D/', '', $phone);
    if (!is_string($digits) || strlen($digits) < 7 || strlen($digits) > 15) {
        throw new RuntimeException('Укажите международный номер телефона в settings.php.');
    }
    return '+' . $digits;
}

function site_contacts(): array
{
    $values = site_settings()['contacts'];
    $items = [];
    if (trim($values['phone']) !== '') {
        $items['phone'] = ['label' => 'Телефон', 'value' => $values['phone'],
            'href' => 'tel:' . site_phone_uri($values['phone']), 'icon' => 'call'];
    }
    $items['email'] = ['label' => 'Email', 'value' => $values['email'],
        'href' => 'mailto:' . $values['email'], 'icon' => 'email'];
    $telegram = trim($values['telegram']);
    if ($telegram !== '') {
        $telegram = preg_replace('~^(?:https?://)?(?:www\.)?t\.me/~i', '', $telegram);
        $telegram = ltrim(rtrim((string) $telegram, '/'), '@');
        if (preg_match('/\A[A-Za-z0-9_]{1,64}\z/', $telegram) !== 1) {
            throw new RuntimeException('Проверьте имя Telegram в settings.php.');
        }
        $items['telegram'] = ['label' => 'Telegram', 'value' => '@' . $telegram,
            'href' => 'https://t.me/' . rawurlencode($telegram), 'icon' => 'telegram'];
    }
    $viber = $values['viber'] ?? $values['phone'];
    if (trim($viber) !== '') {
        $items['viber'] = ['label' => 'Viber', 'value' => $viber,
            'href' => 'viber://chat?number=' . rawurlencode(site_phone_uri($viber)), 'icon' => 'viber'];
    }
    return $items;
}

function site_contact_cards(): void
{
    foreach (site_contacts() as $key => $contact) {
        echo '<a class="contact-method" data-contact-method="' . site_e($key)
            . '" href="' . site_e($contact['href']) . '">'
            . '<span class="contact-method-icon"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#icon-'
            . site_e($contact['icon']) . '"/></svg></span>'
            . '<span class="contact-method-copy"><strong>' . site_e($contact['label'])
            . '</strong><span>' . site_e($contact['value']) . '</span></span>'
            . '<svg class="icon arrow" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 19 19 5M5 5h14v14"/></svg></a>';
    }
}

function site_format_money($value): string
{
    return number_format($value, floor($value) === (float) $value ? 0 : 2, ',', ' ') . ' ' . site_settings()['currency'];
}

function site_extra_rate(array $extra): string
{
    if ($extra['price'] === null) return 'Стоимость уточним';
    if ($extra['type'] === 'percent') return '+' . number_format($extra['price'], floor($extra['price']) === (float) $extra['price'] ? 0 : 2, ',', ' ') . '% от стоимости разработки';
    if ($extra['type'] === 'monthly') return site_format_money($extra['price']) . '/мес.';
    return $extra['price'] == 0 ? 'Бесплатно' : '+' . site_format_money($extra['price']);
}

function site_faq(): array
{
    $settings = site_settings();
    $tokens = [];
    foreach ($settings['prices'] as $key => $price) $tokens['{price:' . $key . '}'] = site_money($key);
    foreach ($settings['extras'] as $key => $extra) $tokens['{extra:' . $key . '}'] = site_extra_rate($extra);
    return array_map(static function ($item) use ($tokens): array {
        $item['answer'] = strtr($item['answer'], $tokens);
        return $item;
    }, $settings['faq']);
}

function site_process(): array
{
    return [
        ['id' => 'task', 'tab' => 'Задача', 'title' => 'Поймём, какие обращения вам нужны',
            'description' => 'Обсудим услугу, аудиторию и бюджет. Согласуем объём, цену и сроки.',
            'results' => ['Цель и формат сайта', 'Состав работ', 'Стоимость и сроки']],
        ['id' => 'structure', 'tab' => 'Структура', 'title' => 'Соберём путь к обращению',
            'description' => 'Расположим предложение, аргументы и форму в понятной последовательности.',
            'results' => ['Схема страницы', 'Главные блоки', 'Путь к заявке']],
        ['id' => 'design', 'tab' => 'Дизайн', 'title' => 'Покажем, как будет выглядеть сайт',
            'description' => 'Согласуем визуальный стиль и расположение блоков до разработки.',
            'results' => ['Ключевые экраны', 'Мобильный вид', 'Согласованное оформление']],
        ['id' => 'development', 'tab' => 'Разработка', 'title' => 'Сделаем сайт рабочим',
            'description' => 'Свой код или CMS — под задачу. Адаптируем под телефон, подключим формы и проверим обращения.',
            'results' => ['Готовые страницы', 'Рабочие формы', 'Проверка разных экранов']],
        ['id' => 'launch', 'tab' => 'Запуск', 'title' => 'Опубликуем и передадим доступы',
            'description' => 'Разместим сайт на вашем домене и объясним, как всё устроено.',
            'results' => ['Сайт на вашем домене', 'Доступы и материалы', 'Проверка после публикации']],
        ['id' => 'promotion', 'tab' => 'Продвижение', 'title' => 'Обсудим привлечение клиентов',
            'description' => 'Отдельно подберём канал и бюджет. Реклама — от ' . site_money('adsMonthly') . '/мес., бюджет на клики отдельно.',
            'results' => ['План привлечения', 'Реклама или SEO под задачу', 'Работа после запуска']],
    ];
}

function site_estimate(array $data): array
{
    $settings = site_settings();
    $source = $data['estimate_source'] ?? '';
    if ($source === '') return [];
    if ($source !== 'calculator') throw new InvalidArgumentException('Неверный источник расчёта.');
    $formats = ['Лендинг' => 'landing', 'Сайт компании' => 'company', 'Интернет-магазин' => 'shop', 'Индивидуальное решение' => 'landing'];
    $service = $data['service'] ?? '';
    if (!isset($formats[$service])) throw new InvalidArgumentException('Выберите формат сайта.');
    $campaignValue = $data['campaigns'] ?? '1';
    if (!is_string($campaignValue) || preg_match('/\A[1-9][0-9]?\z/', $campaignValue) !== 1) {
        throw new InvalidArgumentException('Проверьте число рекламных кампаний.');
    }
    $campaigns = (int) $campaignValue;
    if ($campaigns > $settings['calculator']['maxCampaigns']) throw new InvalidArgumentException('Проверьте число рекламных кампаний.');
    $seo = $data['seo'] ?? '';
    if (!in_array($seo, ['', '1'], true)) throw new InvalidArgumentException('Проверьте выбор SEO.');
    $extraValue = $data['extras'] ?? '';
    if (!is_string($extraValue) || strlen($extraValue) > 2000) throw new InvalidArgumentException('Проверьте дополнительные работы.');
    $extraIds = $extraValue === '' ? [] : array_values(array_unique(explode(',', $extraValue)));
    $base = $settings['prices'][$formats[$service]];
    $oneTime = $base;
    $extrasMonthly = 0;
    $hasMonthlyExtras = false;
    $extraLabels = [];
    $extraDetails = [];
    $unknown = [];
    foreach ($extraIds as $id) {
        if (!array_key_exists($id, $settings['extras'])) throw new InvalidArgumentException('Неизвестная дополнительная работа.');
        $extra = $settings['extras'][$id];
        $extraLabels[] = $extra['label'];
        $amount = null;
        if ($extra['price'] === null) {
            $unknown[] = $extra['label'];
        } elseif ($extra['type'] === 'monthly') {
            $amount = $extra['price'];
            $extrasMonthly += $amount;
            $hasMonthlyExtras = true;
        } elseif ($extra['type'] === 'percent') {
            $amount = round($base * $extra['price'] / 100, 2);
            $oneTime += $amount;
        } else {
            $amount = $extra['price'];
            $oneTime += $amount;
        }
        $extraDetails[] = ['label' => $extra['label'], 'type' => $extra['type'], 'rate' => $extra['price'], 'amount' => $amount];
    }
    $ads = ($data['promotion'] ?? '') === 'ppc';
    $adsMonthly = $ads ? $settings['prices']['adsMonthly'] + ($campaigns - 1) * $settings['prices']['additionalCampaign'] : 0;
    $seoMonthly = $seo === '1' ? $settings['prices']['seoMonthly'] : 0;
    return ['oneTime' => $oneTime, 'monthly' => $adsMonthly + $seoMonthly + $extrasMonthly, 'extrasMonthly' => $extrasMonthly, 'hasMonthlyExtras' => $hasMonthlyExtras, 'adsMonthly' => $adsMonthly,
        'seoMonthly' => $seoMonthly, 'withAds' => $ads, 'withSeo' => $seo === '1', 'campaigns' => $campaigns, 'extras' => $extraLabels, 'extraDetails' => $extraDetails, 'unknown' => $unknown];
}

function site_estimate_text(array $estimate): string
{
    if ($estimate === []) return '';
    $currency = site_settings()['currency'];
    $money = static function ($value) use ($currency): string {
        return number_format($value, floor($value) === (float) $value ? 0 : 2, ',', ' ') . ' ' . $currency;
    };
    $text = "\nПредварительный расчёт из калькулятора:\nРазработка и оценённые допработы: от " . $money($estimate['oneTime']) . " разово.\n";
    if ($estimate['withAds']) $text .= 'Реклама: от ' . $money($estimate['adsMonthly']) . '/мес.; кампаний: ' . $estimate['campaigns'] . ". Бюджет на клики отдельно.\n";
    if ($estimate['withSeo']) $text .= 'SEO: от ' . $money($estimate['seoMonthly']) . "/мес.\n";
    if ($estimate['withAds'] || $estimate['withSeo'] || $estimate['hasMonthlyExtras']) $text .= ($estimate['hasMonthlyExtras'] ? 'Ежемесячно всего: от ' : 'Продвижение всего: от ') . $money($estimate['monthly']) . "/мес.\n";
    foreach ($estimate['extraDetails'] as $extra) {
        $detail = $extra['label'] . ': ';
        if ($extra['amount'] === null) $detail .= 'нужна оценка';
        elseif ($extra['type'] === 'monthly') $detail .= $money($extra['amount']) . '/мес.';
        elseif ($extra['type'] === 'percent') $detail .= $extra['rate'] . '% от базовой цены разработки (' . $money($extra['amount']) . ')';
        elseif ($extra['amount'] == 0) $detail .= 'бесплатно';
        else $detail .= $money($extra['amount']) . ' разово';
        $text .= $detail . ".\n";
    }
    if ($estimate['extras'] !== []) $text .= 'Дополнительные задачи: ' . implode(', ', $estimate['extras']) . ".\n";
    if ($estimate['unknown'] !== []) $text .= 'Требуется оценка: ' . implode(', ', $estimate['unknown']) . ".\n";
    return $text . "Итоговые объём, стоимость и сроки согласуются отдельно.\n";
}
