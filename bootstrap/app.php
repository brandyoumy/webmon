<?php

// Shims for systems missing the 'intl' extension (like macOS default PHP)
namespace Illuminate\Support {
    if (!function_exists('Illuminate\Support\extension_loaded')) {
        function extension_loaded($name) {
            if ($name === 'intl') {
                return true;
            }
            return \extension_loaded($name);
        }
    }
}

namespace {
    if (!class_exists('NumberFormatter')) {
        class NumberFormatter {
            const DECIMAL = 1;
            const PERCENT = 2;
            const CURRENCY = 3;
            const SPELLOUT = 4;
            const ORDINAL = 5;
            const DURATION = 6;
            const SCIENTIFIC = 7;
            const DEFAULT_STYLE = 8;
            
            const TYPE_DEFAULT = 1;
            const TYPE_INT32 = 2;
            const TYPE_INT64 = 3;
            const TYPE_DOUBLE = 4;
            
            const FRACTION_DIGITS = 8;
            const MAX_FRACTION_DIGITS = 9;
            const DEFAULT_RULESET = 10;

            private $locale;
            private $style;
            private $attributes = [];
            private $textAttributes = [];

            public function __construct($locale, $style, $pattern = null) {
                $this->locale = $locale;
                $this->style = $style;
            }

            public function setAttribute($attr, $val) {
                $this->attributes[$attr] = $val;
                return true;
            }

            public function getAttribute($attr) {
                return $this->attributes[$attr] ?? null;
            }

            public function setTextAttribute($attr, $val) {
                $this->textAttributes[$attr] = $val;
                return true;
            }

            public function format($value, $type = null) {
                if ($this->style === self::PERCENT) {
                    $precision = $this->attributes[self::FRACTION_DIGITS] ?? 0;
                    return number_format($value * 100, $precision) . '%';
                }
                if ($this->style === self::CURRENCY) {
                    $precision = $this->attributes[self::FRACTION_DIGITS] ?? 2;
                    return '$' . number_format($value, $precision);
                }
                
                $precision = $this->attributes[self::FRACTION_DIGITS] ?? null;
                if ($precision === null) {
                    return number_format($value);
                }
                return number_format($value, $precision);
            }

            public function formatCurrency($value, $currency) {
                $precision = $this->attributes[self::FRACTION_DIGITS] ?? 2;
                return $currency . ' ' . number_format($value, $precision);
            }

            public function parse($text, $type = null, &$position = null) {
                return is_numeric($text) ? (float)$text : false;
            }
        }
    }
}

namespace {
    use Illuminate\Foundation\Application;
    use Illuminate\Foundation\Configuration\Exceptions;
    use Illuminate\Foundation\Configuration\Middleware;

    return Application::configure(basePath: dirname(__DIR__))
        ->withRouting(
            web: __DIR__.'/../routes/web.php',
            commands: __DIR__.'/../routes/console.php',
            health: '/up',
        )
        ->withMiddleware(function (Middleware $middleware): void {
            $middleware->trustProxies(at: '*');
        })
        ->withExceptions(function (Exceptions $exceptions): void {
            //
        })->create();
}
