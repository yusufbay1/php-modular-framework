<?php

namespace Core\Http;

use Core\Functions\IFunction;

class Request
{
    private array $query;
    private array $request;
    private array $attributes;
    private array $cookies;
    private array $files;
    private mixed $content;
    private array $errors;
    private array $routeParams = [];

    public function __construct(array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null)
    {
        $this->query = $query;
        $this->request = $request;
        $this->attributes = $attributes;
        $this->cookies = $cookies;
        $this->files = $files;
        // $this->server = $server;
        $this->content = $content;
        $this->errors = array();
    }

    public static function createFromGlobals(): static
    {
        return new static($_GET, $_POST, [], $_COOKIE, $_FILES, $_SERVER, file_get_contents('php://input'));
    }

    public function get($key, $default = null)
    {
        return $this->query[$key] ?? $default;
    }

    public function post($key, $default = null)
    {
        $json_decode = json_decode($this->content, true);
        return $json_decode[$key] ?? $default;
    }

    public function request($key, $default = null)
    {
        return $this->request[$key] ?? $default;
    }

    public function attributes($key, $default = null)
    {
        return $this->attributes[$key] ?? $default;
    }

    public function cookies($key, $default = null)
    {
        return $this->cookies[$key] ?? $default;
    }

    public function files($key, $default = null)
    {
        return $this->files[$key] ?? $default;
    }

    public function setRouteParams(array $params): void
    {
        $this->routeParams = $params;
    }

    public function route(string $key, $default = null): ?string
    {
        return $this->routeParams[$key] ?? $default;
    }

    public function getContent()
    {
        return json_decode($this->content);
    }

    public function getContentArray()
    {
        return json_decode($this->content, true);
    }

    public function requests(): array
    {
        $array = $this->request + $this->query + $this->files;
        $array_content = json_decode($this->content, true);
        if (is_array($array_content))
            return $array + $array_content;

        return $array;
    }

    public function validate($rules): array
    {
        $data = $this->requests();
        foreach ($rules as $field => $rule) {
            $ruleParts = explode('|', $rule);
            $value = $data[$field] ?? null;

            foreach ($ruleParts as $rulePart) {
                $this->applyRule($field, $rulePart, $value, $data);
            }
        }
        return ['errors' => $this->errors, 'data' => $data];
    }

    private function applyRule($field, $rulePart, $value, $data): void
    {
        $param = null;
        if (str_contains($rulePart, ':'))
            [$rulePart, $param] = explode(':', $rulePart);

        if ($rulePart !== 'required' && $value === '')
            return;

        $value = is_string($value) ? trim($value) : $value;
        switch ($rulePart) {
            case 'required':
                if (empty($value)) {
                    $this->addError($field, ($field) . ' Lütfen bu alanı doldurun');
                }
                break;
            case 'numeric':
                if (!is_numeric($value)) {
                    $this->addError($field, ($field) . ' Lütfen sadece sayı giriniz');
                }
                break;
            case 'float':
                if (!preg_match('/^[+-]?\d+(\.\d+)?$/', $value)) {
                    $this->addError($field, ($field) . ' Lütfen ondalıklı (10.99,10) bir sayı giriniz.');
                }
                break;
            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, ($field) . ' Lütfen geçerli Bir mail giriniz');
                }
                break;
            case 'max':
                if (strlen($value) > $param) {
                    $this->addError($field, ($field) . ' ' . str_replace('{max}', $param, 'Karakter uzunluğu Çok fazla en fazla {max} karakter olmalıdır'));
                }
                break;
            case 'min':
                if (strlen($value) < $param) {
                    $this->addError($field, ($field) . ' ' . str_replace('{min}', $param, 'Karakter uzunluğu çok kısa en az {min} karakter olmalıdır'));
                }
                break;
            case 'alpha':
                if (!preg_match('/^\p{L}+(?: \p{L}+)*$/u', $value)) {
                    $this->addError($field, ($field) . ' Sadece Harf ve Boşluk Girebilirsiniz');
                }
                break;
            case 'alpha_num':
                if (!preg_match('/^[\p{L}\p{N}]+$/u', $value)) {
                    $this->addError($field, ($field) . ' Sadece Harf ve Sayı Girebilirsiniz');
                }
                break;
            case 'date':
                if (!strtotime($value)) {
                    $this->addError($field, ($field) . ' Lütfen geçerli bir tarih giriniz');
                }
                break;
            case 'in':
                $options = explode(',', $param);
                if (!in_array($value, $options)) {
                    $this->addError($field, ($field) . " Lütfen yalnızca [$param] içeren değerlerden birini giriniz");
                }
                break;
            case 'confirm':
                $confirmationField = $field . '_confirm';
                if (strcasecmp($value, $data[$confirmationField]) !== 0) {
                    $this->addError($field, ($field) . ' Lütfen doğruılama alanı ile aynı değeri giriniz');
                }
                break;
            case 'phone':
                if (!preg_match('/^\+?[\d\s\-\(\)]+$/', $value)) {
                    $this->addError($field, ($field) . ' Lütfen Geçerli Bir Telefon Giriniz');
                } elseif (strlen(preg_replace('/[^\d]/', '', $value)) < 10 || strlen(preg_replace('/[^\d]/', '', $value)) > 15) {
                    $this->addError($field, ($field) . ' Telefon Numarası 10 ila 15 Karakter Olmalıdır');
                }
                break;
            case 'file':
                if (!isset($_FILES[$field]) || $_FILES[$field]['error'] != UPLOAD_ERR_OK) {
                    $this->addError($field, ($field) . ' Lütfen geçerli bir dosya yükleyin');
                    return;
                }

                $file = $_FILES[$field];
                if ($param) {
                    if ($rulePart === 'maxsize') {
                        $maxSize = $param * 1024 * 1024; // Megabaytı byte'a çevir
                        if ($file['size'] > $maxSize) {
                            $this->addError($field, ($field) . " " . str_replace('{max}', $param, 'Maksimum dosya boyutu {max}MB olmalıdır'));
                        }
                    }

                    if ($rulePart === 'mimes') {
                        $allowedMimes = explode(',', $param);
                        $finfo = new \finfo(FILEINFO_MIME_TYPE);
                        $fileMime = $finfo->file($file['tmp_name']);
                        if (!in_array($fileMime, $allowedMimes)) {
                            $allowedMimes = explode(',', str_replace(['application/', 'image/'], '', $param));
                            $this->addError($field, ($field) . ' ' . str_replace('{mimes}', implode(',', $allowedMimes), 'İzin verilen dosya türleri: {mimes}'));
                        }
                    }
                }
                break;
            case 'mimes':
                $allowedMimes = explode(',', $param);
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $tmp = $value['tmp_name'];

                if ($tmp == '' || !in_array($finfo->file($value['tmp_name']), $allowedMimes)) {
                    $this->addError($field, ($field) . ' ' . str_replace('{mimes}', implode(',', $allowedMimes), 'İzin verilen dosya türleri: {mimes}'));
                }

                break;
            case 'maxsize':
                $file = $_FILES[$field];
                if ($file['name'] != '') {
                    $maxSize = $param * 1024 * 1024;
                    if ($file['size'] > $maxSize) {
                        $this->addError($field, ($field) . " " . str_replace('{max}', $param, 'Maksimum dosya boyutu {max}MB olmalıdır'));
                    }
                }
                break;
            case 'accepted':
                if (!is_string($value) || !in_array(strtolower($value), ['on', 'yes', '1', 'true'])) {
                    $this->addError($field, ($field) . ' Lütfen Bu Alanı İşaretleyin');
                }
                break;
            case 'identity':
                if (!IFunction::identityValidate($value))
                    $this->addError($field, ($field) . ' Lütfen Geçerli Bir Kimlik Numarası Giriniz');
                break;
            case 'strip_tags':
                $data[$field] = IFunction::security($value);
                break;
            case 'csrf':
                if (!IFunction::csrfControl($value)) {
                    $this->addError($field, ($field) . ' Geçerli Bir Token Giriniz');
                }
                break;
            case 'same':
                [$compareField, $compareValue] = explode('-', $param);
                if ($value !== ($data[$compareField] ?? null)) {
                    $this->addError($field, ($field) . ' ' . str_replace('{field}', $compareValue, 'Bu alan {field} ile aynı olmalıdır'));
                }
                break;
        }
    }

    private function addError($field, $message): void
    {
        $this->errors[$field] = $message;
    }
}
