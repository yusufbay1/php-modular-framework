<?php

namespace Core\Http;

class Response
{
    public const int HTTP_CONTINUE = 100;
    public const int HTTP_SWITCHING_PROTOCOLS = 101;
    public const int HTTP_PROCESSING = 102;
    public const int HTTP_EARLY_HINTS = 103;
    public const int HTTP_OK = 200;
    public const int HTTP_CREATED = 201;
    public const int HTTP_ACCEPTED = 202;
    public const int HTTP_NON_AUTHORITATIVE_INFORMATION = 203;
    public const int HTTP_NO_CONTENT = 204;
    public const int HTTP_RESET_CONTENT = 205;
    public const int HTTP_PARTIAL_CONTENT = 206;
    public const int HTTP_MULTI_STATUS = 207;
    public const int HTTP_ALREADY_REPORTED = 208;
    public const int HTTP_IM_USED = 226;
    public const int HTTP_MULTIPLE_CHOICES = 300;
    public const int HTTP_MOVED_PERMANENTLY = 301;
    public const int HTTP_FOUND = 302;
    public const int HTTP_SEE_OTHER = 303;
    public const int HTTP_NOT_MODIFIED = 304;
    public const int HTTP_USE_PROXY = 305;
    public const int HTTP_RESERVED = 306;
    public const int HTTP_TEMPORARY_REDIRECT = 307;
    public const int HTTP_PERMANENTLY_REDIRECT = 308;
    public const int HTTP_BAD_REQUEST = 400;
    public const int HTTP_UNAUTHORIZED = 401;
    public const int HTTP_PAYMENT_REQUIRED = 402;
    public const int HTTP_FORBIDDEN = 403;
    public const int HTTP_NOT_FOUND = 404;
    public const int HTTP_METHOD_NOT_ALLOWED = 405;
    public const int HTTP_NOT_ACCEPTABLE = 406;
    public const int HTTP_PROXY_AUTHENTICATION_REQUIRED = 407;
    public const int HTTP_REQUEST_TIMEOUT = 408;
    public const int HTTP_CONFLICT = 409;
    public const int HTTP_GONE = 410;
    public const int HTTP_LENGTH_REQUIRED = 411;
    public const int HTTP_PRECONDITION_FAILED = 412;
    public const int HTTP_REQUEST_ENTITY_TOO_LARGE = 413;
    public const int HTTP_REQUEST_URI_TOO_LONG = 414;
    public const int HTTP_UNSUPPORTED_MEDIA_TYPE = 415;
    public const int HTTP_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    public const int HTTP_EXPECTATION_FAILED = 417;
    public const int HTTP_I_AM_A_TEAPOT = 418;
    public const int HTTP_MISDIRECTED_REQUEST = 421;
    public const int HTTP_UNPROCESSABLE_ENTITY = 422;
    public const int HTTP_LOCKED = 423;
    public const int HTTP_FAILED_DEPENDENCY = 424;
    public const int HTTP_TOO_EARLY = 425;
    public const int HTTP_UPGRADE_REQUIRED = 426;
    public const int HTTP_PRECONDITION_REQUIRED = 428;
    public const int HTTP_TOO_MANY_REQUESTS = 429;
    public const int HTTP_REQUEST_HEADER_FIELDS_TOO_LARGE = 431;
    public const int HTTP_UNAVAILABLE_FOR_LEGAL_REASONS = 451;
    public const int HTTP_INTERNAL_SERVER_ERROR = 500;
    public const int HTTP_NOT_IMPLEMENTED = 501;
    public const int HTTP_BAD_GATEWAY = 502;
    public const int HTTP_SERVICE_UNAVAILABLE = 503;
    public const int HTTP_GATEWAY_TIMEOUT = 504;
    public const int HTTP_VERSION_NOT_SUPPORTED = 505;
    public const int HTTP_VARIANT_ALSO_NEGOTIATES_EXPERIMENTAL = 506;
    public const int HTTP_INSUFFICIENT_STORAGE = 507;
    public const int HTTP_LOOP_DETECTED = 508;
    public const int HTTP_NOT_EXTENDED = 510;
    public const int HTTP_NETWORK_AUTHENTICATION_REQUIRED = 511;

    public static array $statusTexts = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        103 => 'Early Hints',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Content Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        421 => 'Misdirected Request',
        422 => 'Unprocessable Content',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Too Early',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
    ];

    public static function notfound(string $msg = ''): false|string
    {
        return self::httpCode(self::HTTP_NOT_FOUND, $msg != '' ? $msg : self::$statusTexts[self::HTTP_NOT_FOUND]);
    }

    public static function forbidden(string $msg = ''): false|string
    {
        return self::httpCode(self::HTTP_FORBIDDEN, $msg != '' ? $msg : self::$statusTexts[self::HTTP_FORBIDDEN]);
    }

    public static function tooManyRequests(string $msg = ''): false|string
    {
        return self::httpCode(self::HTTP_TOO_MANY_REQUESTS, $msg != '' ? $msg : self::$statusTexts[self::HTTP_TOO_MANY_REQUESTS]);
    }

    public static function created(string $msg = ''): false|string
    {
        return self::httpCode(self::HTTP_CREATED, $msg != '' ? $msg : self::$statusTexts[self::HTTP_CREATED]);
    }

    public static function error(string $msg = 'Bir Hata Oluştu!'): false|string
    {
        return self::httpCode(self::HTTP_INTERNAL_SERVER_ERROR, $msg != '' ? $msg : self::$statusTexts[self::HTTP_INTERNAL_SERVER_ERROR]);
    }

    public static function ok(string $msg = ''): false|string
    {
        return self::httpCode(self::HTTP_OK, $msg != '' ? $msg : self::$statusTexts[self::HTTP_OK]);
    }

    public static function badRequest(string $msg = ''): false|string
    {
        return self::httpCode(self::HTTP_BAD_REQUEST, $msg != '' ? $msg : 'Invalid fields in request data');
    }

    public static function conflict(string $msg = ''): false|string
    {
        return self::httpCode(self::HTTP_CONFLICT, $msg != '' ? $msg : self::$statusTexts[self::HTTP_CONFLICT]);
    }

    public static function unauthorized(string $msg = ''): false|string
    {
        return self::httpCode(self::HTTP_UNAUTHORIZED, $msg != '' ? $msg : self::$statusTexts[self::HTTP_UNAUTHORIZED]);
    }

    public static function paymentRequired(string $msg = ''): false|string
    {
        return self::httpCode(self::HTTP_PAYMENT_REQUIRED, $msg != '' ? $msg : self::$statusTexts[self::HTTP_PAYMENT_REQUIRED]);
    }

    public static function errorsValidate($errors): false|string
    {
        header('Content-Type: application/json');
        http_response_code(400);
        return json_encode($errors);
    }

    public static function httpCode(int $status, string $message): false|string
    {
        header('Content-Type: application/json');
        http_response_code($status);
        return json_encode(['code' => $status, 'message' => $message]);
    }
}