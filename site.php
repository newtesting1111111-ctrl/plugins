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
        if ($extraPrice !== null && ((!is_int($extraPrice) && !is_float($extraPrice))
            || !is_finite((float) $extraPrice) || $extraPrice < 0)) {
            throw new RuntimeException('Цена допработы должна быть числом или null.');
        }
    }
    $maxCampaigns = $candidate['calculator']['maxCampaigns'] ?? null;
    if (!is_int($maxCampaigns) || $maxCampaigns < 1 || $maxCampaigns > 50) {
        throw new RuntimeException('maxCampaigns: целое число от 1 до 50.');
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

function site_faq(): array
{
    return [
        ['id' => 'price', 'question' => 'Сколько стоит сайт?',
            'answer' => 'Лендинг — от ' . site_money('landing') . ".\nСайт компании — от " . site_money('company')
                . ".\nИнтернет-магазин — от " . site_money('shop') . ".\nИтоговую цену согласуем после обсуждения объёма."],
        ['id' => 'leads', 'question' => 'Когда появятся заявки?',
            'answer' => 'После запуска сайту нужны посетители. Реклама помогает их привлечь. Число обращений зависит от спроса, предложения, бюджета и удобства сайта — фиксированное количество заявок не обещаем.'],
        ['id' => 'format', 'question' => 'Лендинг или сайт компании?',
            'answer' => 'Для одной услуги и рекламы обычно достаточно лендинга. Для нескольких направлений лучше подойдёт сайт компании с отдельными страницами. Подберём формат под вашу задачу и бюджет.'],
        ['id' => 'scope', 'question' => 'Что входит в разработку?',
            'answer' => 'Структура, дизайн, мобильная версия, формы обращения, запуск и передача доступов. Количество страниц, материалы и дополнительные интеграции согласуем до начала работ.'],
        ['id' => 'time', 'question' => 'Сколько времени займёт работа?',
            'answer' => 'Срок зависит от формата, функций и готовности материалов. Назовём срок вместе со стоимостью до начала разработки.'],
        ['id' => 'ads', 'question' => 'Сколько стоит реклама?',
            'answer' => 'Настройка и ведение одной кампании — от ' . site_money('adsMonthly') . '/мес. Каждая следующая — +'
                . site_money('additionalCampaign') . '/мес. Бюджет на клики оплачивается отдельно. Можно начать только с сайта.'],
        ['id' => 'seo', 'question' => 'А SEO можно подключить?',
            'answer' => 'Да. Комплексное SEO — от ' . site_money('seoMonthly') . '/мес. Обсудим его как отдельный канал привлечения после запуска сайта.'],
        ['id' => 'technology', 'question' => 'На чём вы делаете сайты?',
            'answer' => 'Используем собственный код и CMS под задачу. Выбор зависит от функций, бюджета и того, как вы планируете редактировать сайт.'],
    ];
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
    $oneTime = $settings['prices'][$formats[$service]];
    $extraLabels = [];
    $unknown = [];
    foreach ($extraIds as $id) {
        if (!array_key_exists($id, $settings['extras'])) throw new InvalidArgumentException('Неизвестная дополнительная работа.');
        $extra = $settings['extras'][$id];
        $extraLabels[] = $extra['label'];
        if ($extra['price'] === null) $unknown[] = $extra['label'];
        else $oneTime += $extra['price'];
    }
    $ads = ($data['promotion'] ?? '') === 'ppc';
    $adsMonthly = $ads ? $settings['prices']['adsMonthly'] + ($campaigns - 1) * $settings['prices']['additionalCampaign'] : 0;
    $seoMonthly = $seo === '1' ? $settings['prices']['seoMonthly'] : 0;
    return ['oneTime' => $oneTime, 'monthly' => $adsMonthly + $seoMonthly, 'adsMonthly' => $adsMonthly,
        'seoMonthly' => $seoMonthly, 'withAds' => $ads, 'withSeo' => $seo === '1', 'campaigns' => $campaigns, 'extras' => $extraLabels, 'unknown' => $unknown];
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
    if ($estimate['withAds'] || $estimate['withSeo']) $text .= 'Продвижение всего: от ' . $money($estimate['monthly']) . "/мес.\n";
    if ($estimate['extras'] !== []) $text .= 'Дополнительные задачи: ' . implode(', ', $estimate['extras']) . ".\n";
    if ($estimate['unknown'] !== []) $text .= 'Требуется оценка: ' . implode(', ', $estimate['unknown']) . ".\n";
    return $text . "Итоговые объём, стоимость и сроки согласуются отдельно.\n";
}
