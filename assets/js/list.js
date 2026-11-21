(() => {
    'use strict';

    const deleteForms = Array.from(document.querySelectorAll('.delete-entry'));
    let pendingDeletes = new WeakSet();

    deleteForms.forEach(form => {
        const button = form.querySelector('button[type="submit"]');

        form.addEventListener('submit', event => {
            if (pendingDeletes.has(form) || !window.confirm('Padam catatan ini? Tindakan ini tidak boleh dibatalkan.')) {
                event.preventDefault();
                return;
            }

            pendingDeletes.add(form);
            form.setAttribute('aria-busy', 'true');
            if (button) {
                button.disabled = true;
            }
        });
    });

    window.addEventListener('pageshow', () => {
        pendingDeletes = new WeakSet();
        deleteForms.forEach(form => {
            form.removeAttribute('aria-busy');
            const button = form.querySelector('button[type="submit"]');
            if (button) {
                button.disabled = false;
            }
        });
    });

    const list = document.getElementById('entries-list');
    const controls = document.getElementById('list-controls');
    const search = document.getElementById('entry-search');
    const sort = document.getElementById('entry-sort');
    const summary = document.getElementById('list-summary');
    const noResults = document.getElementById('no-results');

    if (!list || !controls || !search || !sort || !summary || !noResults) {
        return;
    }

    const normalize = value => value.normalize('NFD').replace(/\p{M}/gu, '').toLocaleLowerCase('ms-MY');
    const records = Array.from(list.querySelectorAll('.record-card'), card => ({
        element: card,
        id: Number(card.dataset.entryId),
        text: normalize(['.record-name', '.record-email', '.record-message']
            .map(selector => card.querySelector(selector)?.textContent ?? '')
            .join(' ')),
    }));

    const filterRecords = () => {
        const query = normalize(search.value.trim());
        let visibleCount = 0;

        records.forEach(record => {
            const matches = record.text.includes(query);
            record.element.hidden = !matches;
            if (matches) {
                visibleCount++;
            }
        });

        summary.textContent = query
            ? `${visibleCount} daripada ${records.length} catatan`
            : `${records.length} catatan`;
        noResults.hidden = visibleCount !== 0;
        list.hidden = visibleCount === 0;
    };

    const sortRecords = () => {
        const direction = sort.value === 'oldest' ? 1 : -1;
        const fragment = document.createDocumentFragment();
        [...records].sort((first, second) => direction * (first.id - second.id))
            .forEach(record => fragment.append(record.element));
        list.append(fragment);
    };

    search.addEventListener('input', filterRecords);
    sort.addEventListener('change', sortRecords);
    window.addEventListener('pageshow', () => {
        sortRecords();
        filterRecords();
    });

    sortRecords();
    filterRecords();
    controls.hidden = false;
})();
