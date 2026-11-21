<?php

declare(strict_types=1);

/** Escape HTML text and quoted attributes; not JavaScript, CSS, or URL values. */
function escapeHtml(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
