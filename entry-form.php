<?php

/**
 * Shared create/edit form. Values are escaped at their HTML output locations.
 *
 * @var array{action: string, title: string, submitLabel: string, note: string,
 *     csrfToken: string, id: int|null, values: array{nama: string, email: string, komen: string}} $entryForm
 */
if (!isset($entryForm) || !is_array($entryForm)) {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/html.php';
require_once __DIR__ . '/validation.php';
?>
<div class="entry-card">
    <div class="form-heading">
        <span class="form-heading-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                <path d="m15 5 4 4M4 20l4.5-1 12-12a2.8 2.8 0 0 0-4-4l-12 12L4 20Z"/>
            </svg>
        </span>
        <div>
            <h2 id="form-title"><?= escapeHtml($entryForm['title']) ?></h2>
            <p id="form-instructions">Semua medan perlu diisi.</p>
        </div>
    </div>

    <form id="entry-form" class="entry-form" method="post" action="<?= escapeHtml($entryForm['action']) ?>" aria-labelledby="form-title" aria-describedby="form-instructions" accept-charset="UTF-8">
        <input type="hidden" name="csrf_token" value="<?= escapeHtml($entryForm['csrfToken']) ?>">
        <?php if ($entryForm['id'] !== null): ?>
            <input type="hidden" name="id" value="<?= escapeHtml((string) $entryForm['id']) ?>">
        <?php endif; ?>

        <div class="form-field">
            <label for="nama">Nama</label>
            <input class="field-control" type="text" id="nama" name="nama" placeholder="Nama yang ingin dikenali" autocomplete="name" required maxlength="<?= BOOK_NAME_MAX_LENGTH ?>" aria-describedby="nama-hint nama-error" value="<?= escapeHtml($entryForm['values']['nama']) ?>">
            <p class="field-hint" id="nama-hint">Maksimum <?= BOOK_NAME_MAX_LENGTH ?> aksara.</p>
            <p class="field-error" id="nama-error" hidden></p>
        </div>

        <div class="form-field">
            <label for="email">Email</label>
            <input class="field-control" type="email" id="email" name="email" placeholder="nama@contoh.com" autocomplete="email" inputmode="email" autocapitalize="none" spellcheck="false" required maxlength="<?= BOOK_EMAIL_MAX_LENGTH ?>" aria-describedby="email-hint email-error" value="<?= escapeHtml($entryForm['values']['email']) ?>">
            <p class="field-hint" id="email-hint">Email ini turut dipaparkan bersama catatan. Maksimum <?= BOOK_EMAIL_MAX_LENGTH ?> aksara.</p>
            <p class="field-error" id="email-error" hidden></p>
        </div>

        <div class="form-field">
            <label for="komen">Catatan</label>
            <textarea class="field-control" id="komen" name="komen" rows="6" placeholder="Apa yang ingin anda kongsikan hari ini?" required data-max-bytes="<?= BOOK_COMMENT_MAX_BYTES ?>" aria-describedby="komen-hint komen-error"><?= escapeHtml($entryForm['values']['komen']) ?></textarea>
            <div class="field-meta">
                <p class="field-hint" id="komen-hint">Ucapan, pesanan atau sekadar kata hai.</p>
                <span class="character-count" id="comment-count" hidden>0 aksara</span>
            </div>
            <p class="field-error" id="komen-error" hidden></p>
        </div>

        <div class="form-actions">
            <div class="form-action-buttons">
                <button class="button button-primary submit-entry" type="submit" id="submit-entry">
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M7 3h10l4 4v14H3V3h4Zm0 0v6h10V3M7 21v-7h10v7"/>
                    </svg>
                    <span id="submit-label"><?= escapeHtml($entryForm['submitLabel']) ?></span>
                </button>
                <button class="button button-secondary reset-entry" type="reset" id="reset-entry">Semula</button>
            </div>
            <p class="publish-note"><?= escapeHtml($entryForm['note']) ?></p>
        </div>
    </form>
</div>
