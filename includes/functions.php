<?php
declare(strict_types=1);

const APP_NAME = 'Hishab';
const APP_CREATOR = 'Salah Uddin Selim';

/** Renders the app's logo mark (an open-ledger icon) as inline SVG in a rounded square. */
function app_logo_mark(string $boxClasses = 'w-8 h-8', string $iconClasses = 'w-[18px] h-[18px]'): string
{
    return '<div class="' . $boxClasses . ' rounded-lg bg-brand-600 flex items-center justify-center shrink-0">'
        . '<svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="' . $iconClasses . '">'
        . '<path d="M4 5.5A1.5 1.5 0 0 1 5.5 4H11a1 1 0 0 1 1 1v15a1 1 0 0 1-1-1H5.5A1.5 1.5 0 0 1 4 17.5v-12Z"/>'
        . '<path d="M20 5.5A1.5 1.5 0 0 0 18.5 4H13a1 1 0 0 0-1 1v15a1 1 0 0 0 1-1h5.5a1.5 1.5 0 0 0 1.5-1.5v-12Z"/>'
        . '<path d="M8 9h2M8 12h2M14 9h2M14 12h2"/>'
        . '</svg></div>';
}

const BUSINESS_CATEGORIES = [
    'General Store', 'Construction', 'Agriculture', 'Electronics', 'Clothing',
    'Food & Beverage', 'Medical & Healthcare', 'Hardware', 'Auto / Parts',
    'Mobile Shop', 'Furniture', 'Fresh House (Produce)', 'Computer Services',
    'Cosmetics', 'Jewelry', 'Stationery', 'Fruits & Vegetables',
    'Fashion Accessories', 'Textile', 'Printing', 'Information Technology',
    'Garage', 'Poultry', 'Tailors', 'Gifts & Toys', 'Scrap',
];

function format_money(float $amount, string $currency = 'BDT'): string
{
    $symbol = $currency === 'BDT' ? '৳' : $currency;
    $sign = $amount < 0 ? '-' : '';
    return $sign . $symbol . ' ' . number_format(abs($amount), 2);
}

function format_date(string $datetime): string
{
    return date('d M Y', strtotime($datetime));
}

function format_date_time(string $datetime): string
{
    return date('d M Y, h:i A', strtotime($datetime));
}

/** Normalizes a phone number to a wa.me-compatible international digit string. Defaults to Bangladesh (+88). */
function to_whatsapp_number(string $phone): string
{
    $digits = preg_replace('/\D/', '', $phone) ?? '';
    if (str_starts_with($digits, '88')) {
        return $digits;
    }
    if (str_starts_with($digits, '0')) {
        return '88' . $digits;
    }
    return $digits;
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function get_store_settings(): array
{
    $row = db()->query("SELECT * FROM store_settings WHERE id = 'singleton'")->fetch();
    return $row ?: [
        'store_name' => 'My Store', 'category' => null, 'address' => '',
        'phone' => '', 'currency' => 'BDT', 'invoice_note' => 'Thank you for your business!',
    ];
}

/** Validates+saves an uploaded receipt image to local disk; returns its public path, or null if no file given. */
function save_receipt_image(?array $file): ?string
{
    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE || (int) $file['size'] === 0) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Receipt upload failed. Please try again.');
    }
    if ($file['size'] > MAX_RECEIPT_BYTES) {
        throw new RuntimeException('Receipt image must be under 5MB.');
    }

    $allowed = [
        'image/jpeg' => '.jpg',
        'image/png' => '.png',
        'image/webp' => '.webp',
        'image/heic' => '.heic',
    ];
    // Detect the real MIME type from file content, never trust the client-sent one.
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Receipt must be a JPEG, PNG, WEBP, or HEIC image.');
    }

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    $filename = bin2hex(random_bytes(16)) . $allowed[$mime];
    $dest = UPLOAD_DIR . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        throw new RuntimeException('Could not save receipt image.');
    }

    return UPLOAD_URL_PREFIX . '/' . $filename;
}
