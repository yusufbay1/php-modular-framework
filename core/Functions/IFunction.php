<?php

namespace Core\Functions;

use Core\Database\Env;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class IFunction
{
    private static string $secret_key = '81Y3y.98?03#_?';
    private static string $secret_iv = '81Y3y#.?98?034_';

    private static function getEncryptionParams($method): array
    {
        return [
            'key' => hash('sha256', self::$secret_key),
            'iv' => substr(hash('sha256', self::$secret_iv), 0, 16),
            'method' => $method
        ];
    }

    public static function encrypt($id): string
    {
        $params = self::getEncryptionParams("AES-256-CBC");
        $output = openssl_encrypt($id, $params['method'], $params['key'], 0, $params['iv']);
        return base64_encode($output);
    }

    public static function decrypt($id): false|string|int
    {
        $params = self::getEncryptionParams("AES-256-CBC");
        return openssl_decrypt(base64_decode($id), $params['method'], $params['key'], 0, $params['iv']);
    }

    public static function encryptUpdate(int $id): string
    {
        $params = self::getEncryptionParams("AES-256-GCM");
        $tag = '';
        $output = openssl_encrypt($id, $params['method'], $params['key'], 0, $params['iv'], $tag);
        $encoded = base64_encode($output . '::' . $tag);
        // URL-safe hale getir
        return strtr(rtrim($encoded, '='), '+/', '-_');
    }


    public static function decryptUpdate(string $data): false|string|int
    {
        $params = self::getEncryptionParams("AES-256-GCM");
        // Geri dönüştür
        $data = strtr($data, '-_', '+/');
        $data .= str_repeat('=', 3 - (3 + strlen($data)) % 4); // base64 padding

        $decoded = base64_decode($data);
        [$encrypted, $tag] = explode('::', $decoded);
        return openssl_decrypt($encrypted, $params['method'], $params['key'], 0, $params['iv'], $tag);
    }


    public static function csrf(): string
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        return '<input type="hidden" id="_token" name="_token" value="' . $token . '" />';
    }

    public static function csrfControl($token): bool
    {
        return isset($_SESSION['csrf_token']) && $_SESSION['csrf_token'] === $token;
    }

    private static function numberCharacter($value): array|string|null
    {
        return preg_replace("/[^0-9]/", "", $value);
    }

    public static function numberFilter($value): array|string|null
    {
        $trim = trim($value);
        $stripTags = strip_tags($trim, ENT_QUOTES);
        $htmlSpecialChars = htmlspecialchars($stripTags, ENT_QUOTES);
        return self::numberCharacter($htmlSpecialChars);
    }

    public static function price($value): string
    {
        return number_format($value, "2", ",", ".");
    }

    public static function security($value): string
    {
        $trimmed = trim(strip_tags($value));
        return htmlspecialchars($trimmed, ENT_QUOTES, 'UTF-8');
    }

    public static function revert($value): string
    {
        return html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    public static function hotLink($value): string
    {
        $content = strtr(mb_strtolower(trim($value), "UTF-8"), [
            'ç' => 'c', 'ğ' => 'g', 'ı' => 'i', 'ö' => 'o', 'ş' => 's', 'ü' => 'u',
            'Ç' => 'c', 'Ğ' => 'g', 'İ' => 'i', 'Ö' => 'o', 'Ş' => 's', 'Ü' => 'u'
        ]);
        $content = preg_replace("/[^a-z0-9.]+/", "-", $content);
        return trim($content, "-");
    }

    public static function generateOrderNumber($length = 20): string
    {
        $uniqueId = uniqid();
        return 'IS-' . substr($uniqueId, 0, $length);
    }

    public static function assets($path, $folder = 'public', $moduleName = null, $upFolder = '.'): string
    {
        return $moduleName ? "./app/Modules/{$moduleName}/views/{$path}" : "$upFolder/$folder/{$path}";
    }

    public static function files($path): string
    {
        return './files/' . $path;
    }

    public static function base(): ?string
    {
        return  'https://' . $_SERVER['HTTP_HOST'];
    }

    public static function clearTwigCache($admin = false): void
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator('cache', FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileInfo) {
            $dirPath = $fileInfo->getPath();
            if ($fileInfo->getFilename() === 'routes_cached.php') {
                continue;
            }
            if ($admin) {
                if (!str_contains($dirPath, DIRECTORY_SEPARATOR . 'admin')) {
                    continue;
                }
            } else {
                if (str_contains($dirPath, DIRECTORY_SEPARATOR . 'admin')) {
                    continue;
                }
            }

            $todo = ($fileInfo->isDir() ? 'rmdir' : 'unlink');
            $todo($fileInfo->getRealPath());
        }
    }

    public static function identityValidate(array|int|null|string $tcIdentificationNo): bool
    {
        $oddSum = 0;
        $evenSum = 0;
        if (!is_numeric($tcIdentificationNo) || strlen($tcIdentificationNo) != 11 || (int)log10($tcIdentificationNo) != 10 || $tcIdentificationNo[0] == '0')
            return false;

        for ($i = 0; $i <= 8; $i++)
            if ($i % 2 == 0)
                $oddSum += $tcIdentificationNo[$i];
            else
                $evenSum += $tcIdentificationNo[$i];

        $tenthDigit = ((($oddSum * 7) - $evenSum) + 10) % 10;
        if ($tenthDigit != $tcIdentificationNo[9])
            return false;

        $eleventhDigit = ($oddSum + $evenSum + $tenthDigit) % 10;

        if ($eleventhDigit != $tcIdentificationNo[10])
            return false;

        return true;
    }

    public static function datetime(): string
    {
        return date('Y-m-d H:i:s');
    }

    public static function addDaysToDate($days): string
    {
        $date = new \DateTime(self::datetime());
        $date->add(new \DateInterval('P' . $days . 'D'));
        return $date->format('Y-m-d H:i:s');
    }
}