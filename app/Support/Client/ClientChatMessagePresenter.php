<?php

namespace App\Support\Client;

class ClientChatMessagePresenter
{
    /**
     * Split a reply message into quoted context and the new reply body.
     *
     * @return array{quoted: ?string, quoted_author: ?string, body: string}
     */
    public static function splitQuote(string $text): array
    {
        $text = trim($text);

        if ($text === '' || ! str_contains($text, '>')) {
            return ['quoted' => null, 'quoted_author' => null, 'body' => $text];
        }

        $lines = preg_split("/\r\n|\n/", $text) ?: [];
        $quoteLines = [];
        $bodyLines = [];
        $phase = 'quote';

        foreach ($lines as $line) {
            if ($phase === 'quote' && self::isQuoteLine($line)) {
                $quoteLines[] = self::stripQuotePrefix($line);

                continue;
            }

            if ($phase === 'quote' && $quoteLines !== [] && trim($line) === '') {
                $phase = 'body';

                continue;
            }

            if ($phase === 'quote' && $quoteLines !== []) {
                $phase = 'body';
                $bodyLines[] = $line;

                continue;
            }

            if ($phase === 'body') {
                $bodyLines[] = $line;

                continue;
            }

            return ['quoted' => null, 'quoted_author' => null, 'body' => $text];
        }

        if ($quoteLines === []) {
            return ['quoted' => null, 'quoted_author' => null, 'body' => $text];
        }

        $quotedAuthor = self::extractQuotedAuthor($quoteLines);

        $body = trim(implode("\n", $bodyLines));

        if ($body === '' && $phase === 'quote') {
            return ['quoted' => null, 'quoted_author' => null, 'body' => $text];
        }

        $quoted = trim(implode("\n", $quoteLines));

        return [
            'quoted' => $quoted !== '' ? $quoted : null,
            'quoted_author' => $quotedAuthor,
            'body' => $body,
        ];
    }

    protected static function isQuoteLine(string $line): bool
    {
        return str_starts_with($line, '> ') || $line === '>';
    }

    protected static function stripQuotePrefix(string $line): string
    {
        if (str_starts_with($line, '> ')) {
            return substr($line, 2);
        }

        return ltrim(substr($line, 1));
    }

    /**
     * @param  list<string>  $quoteLines
     */
    protected static function extractQuotedAuthor(array &$quoteLines): ?string
    {
        if ($quoteLines === []) {
            return null;
        }

        $first = trim($quoteLines[0]);

        if (preg_match('/^\[(.+)\]$/', $first, $matches) !== 1) {
            return null;
        }

        array_shift($quoteLines);

        return trim($matches[1]) !== '' ? trim($matches[1]) : null;
    }
}
