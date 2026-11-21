<?php

declare(strict_types=1);

require_once __DIR__ . '/../html.php';

function expectSame(string $expected, string $actual, string $description): void
{
    if ($actual !== $expected) {
        throw new RuntimeException('FAIL: ' . $description);
    }
}

expectSame(
    '&lt;script&gt;alert(1)&lt;/script&gt;',
    escapeHtml('<script>alert(1)</script>'),
    'Script tags must display as text.'
);

expectSame(
    '&quot; autofocus onfocus=&quot;alert(1)',
    escapeHtml('" autofocus onfocus="alert(1)'),
    'Quotes must not break out of an HTML attribute.'
);

expectSame(
    '&lt;/textarea&gt;&lt;script&gt;alert(1)&lt;/script&gt;',
    escapeHtml('</textarea><script>alert(1)</script>'),
    'Input must not close the textarea element.'
);

expectSame('O&#039;Connor &amp; Ali', escapeHtml("O'Connor & Ali"), 'Ordinary punctuation must be encoded.');
expectSame('你好 👋', escapeHtml('你好 👋'), 'Valid Unicode must be preserved.');
expectSame("bad\u{FFFD}", escapeHtml("bad\xFF"), 'Invalid UTF-8 must use a replacement character.');
expectSame('&amp;lt;script&amp;gt;', escapeHtml('&lt;script&gt;'), 'Literal entities must retain their text meaning.');
expectSame(
    "Baris 1<br />\n&lt;script&gt;",
    nl2br(escapeHtml("Baris 1\n<script>")),
    'Line breaks must be added only after escaping user input.'
);

echo "PASS: 8 HTML escaping regression checks.\n";
