const management = document.querySelector('#management');
const { singular, columns, fields, filter } = window.adminTable;
const { endpoint, resource } = management.dataset;
const search = management.querySelector('[data-search]');
const filterInput = management.querySelector('[data-filter]');
const status = management.querySelector('[data-status]');
const rows = management.querySelector('tbody');
const pager = management.querySelector('.management-pagination');
const previous = pager.querySelector('[data-prev]');
const next = pager.querySelector('[data-next]');
const dialog = management.querySelector('dialog');
let currentPage = 1;
let listRequest;
let searchTimer;
let optionsRequest;

async function request(url, method = 'GET', data, signal) {
    const response = await fetch(url, {
        method,
        signal,
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': management.dataset.csrf,
        },
        body: data === undefined ? undefined : JSON.stringify(data),
    });

    if (response.status === 204) return null;
    const result = await response.json();
    if (!response.ok) {
        const messages = result.errors && Object.values(result.errors).flat();
        throw new Error(messages?.join('\n') || result.message || 'The request failed.');
    }
    return result;
}

function getOptions() {
    optionsRequest ??= Promise.all([
        resource === 'products' ? request(management.dataset.categories) : { data: [] },
        resource === 'orders' || resource === 'reviews' ? request(management.dataset.lookups) : {},
    ]).then(([categories, lookups]) => ({ categories: categories.data, ...lookups })).catch(error => {
        optionsRequest = undefined;
        throw error;
    });
    return optionsRequest;
}

function fillSelect(select, choices, prompt) {
    select.replaceChildren(new Option(prompt, ''));
    for (const choice of choices) {
        const [value, text] = Array.isArray(choice) ? choice : typeof choice === 'object'
            ? [choice.id, choice.name]
            : [choice, choice.charAt(0).toUpperCase() + choice.slice(1)];
        select.add(new Option(text, value));
    }
}

function rowButton(label, icon, action) {
    const button = document.createElement('button');
    button.type = 'button';
    button.title = label;
    button.setAttribute('aria-label', label);
    const symbol = document.createElement('i');
    symbol.className = 'bi bi-' + icon;
    symbol.setAttribute('aria-hidden', 'true');
    button.append(symbol);
    button.addEventListener('click', action);
    return button;
}

function renderRow(record) {
    const row = document.createElement('tr');
    for (const [key] of columns) {
        const cell = row.insertCell();
        const value = key.split('.').reduce((item, part) => item?.[part], record);
        cell.textContent = ['is_active', 'is_visible'].includes(key)
            ? (Number(value) ? 'Yes' : 'No')
            : value ?? '—';
    }
    if (fields.length) {
        const actions = document.createElement('div');
        actions.className = 'management-row-actions';
        actions.append(
            rowButton('Edit ' + singular, 'pencil-square', () => openEditor(record.id)),
            rowButton('Delete ' + singular, 'trash', () => deleteRecord(record.id)),
        );
        row.insertCell().append(actions);
    }
    rows.append(row);
}

async function load(page = 1, message = '') {
    listRequest?.abort();
    const controller = new AbortController();
    listRequest = controller;
    status.textContent = 'Loading…';
    try {
        const url = new URL(endpoint);
        url.searchParams.set('page', page);
        if (search.value.trim()) url.searchParams.set('search', search.value.trim());
        if (filterInput?.value) url.searchParams.set(filter[0], filterInput.value);
        const result = await request(url, 'GET', undefined, controller.signal);
        if (controller.signal.aborted) return;
        if (!result.data.length && page > 1) return load(page - 1, message);
        currentPage = result.current_page ?? 1;
        const data = resource === 'categories'
            ? result.data.filter(item => [item.name, item.description].some(value =>
                String(value ?? '').toLowerCase().includes(search.value.trim().toLowerCase())))
            : result.data;
        rows.replaceChildren();
        for (const record of data) renderRow(record);
        status.textContent = message || (data.length ? '' : 'No ' + resource + ' found.');
        pager.hidden = !(result.last_page > 1);
        previous.disabled = currentPage <= 1;
        next.disabled = currentPage >= result.last_page;
        pager.querySelector('[data-page]').textContent = 'Page ' + currentPage + ' of ' + (result.last_page ?? 1);
    } catch (error) {
        if (!controller.signal.aborted) status.textContent = error.message;
    }
}

function formField(field, value, options, editing) {
    const [name, title, type = 'text'] = field;
    const label = document.createElement('label');
    label.append(title);
    const choices = Array.isArray(type) ? type : options[type];
    let input;
    if (choices || type === 'boolean') {
        input = document.createElement('select');
        if (type === 'boolean') {
            input.add(new Option('Yes', '1'));
            input.add(new Option('No', '0'));
            value = value == null ? '1' : String(Number(value));
        } else {
            fillSelect(input, choices, 'Choose ' + title.toLowerCase());
        }
    } else if (type.endsWith('textarea')) {
        input = document.createElement('textarea');
    } else {
        input = document.createElement('input');
        input.type = type.endsWith('money') || type === 'number' ? 'number' : ['email', 'password'].includes(type) ? type : 'text';
        if (input.type === 'number') {
            input.min = name === 'rating' ? '1' : '0';
            if (type.endsWith('money')) input.step = '0.01';
            if (name === 'rating') input.max = '5';
        }
        if (type === 'password') input.autocomplete = 'new-password';
    }
    input.name = name;
    input.required = !(String(type).startsWith('optional') || (type === 'password' && editing));
    input.value = value ?? (name === 'shipping_fee' || name === 'stock' ? '0' : (name === 'payment_method' ? 'cod' : ''));
    if (type === 'password' && editing) input.placeholder = 'Leave blank to keep the current password';
    label.append(input);
    return label;
}

function orderItems(container, options, record) {
    const group = document.createElement('fieldset');
    group.className = 'management-items';
    const legend = document.createElement('legend');
    legend.textContent = 'Order items';
    group.append(legend);

    if (record) {
        for (const item of record.items) {
            const line = document.createElement('span');
            line.textContent = item.product_name + ' × ' + item.quantity + ' · ' + item.unit_price + ' JOD each';
            group.append(line);
        }
        container.append(group);
        return;
    }

    const add = document.createElement('button');
    add.type = 'button';
    add.textContent = 'Add item';
    const addLine = () => {
        const line = document.createElement('div');
        line.className = 'management-line';
        const productField = formField(['line_product', 'Product', 'products'], '', options, false);
        const quantityField = formField(['line_quantity', 'Quantity', 'number'], '1', options, false);
        quantityField.querySelector('input').min = '1';
        line.append(productField, quantityField, rowButton('Remove item', 'x-lg', () => {
            if (group.querySelectorAll('.management-line').length > 1) line.remove();
        }));
        group.insertBefore(line, add);
    };
    group.append(add);
    add.addEventListener('click', addLine);
    addLine();
    container.append(group);
}

let editingId;
let opening = false;
async function openEditor(id = null) {
    if (opening || dialog.open) return;
    opening = true;
    try {
        const [options, record] = await Promise.all([
            getOptions(),
            id === null ? null : request(endpoint + '/' + id).then(result => result.data),
        ]);
        editingId = id;
        const fieldset = dialog.querySelector('[data-fields]');
        fieldset.replaceChildren();
        for (const field of fields) {
            const mode = field[3];
            if ((mode === 'edit' && !record) || (mode === 'create' && record)) continue;
            fieldset.append(formField(field, record?.[field[0]], options, Boolean(record)));
        }
        if (resource === 'orders') orderItems(fieldset, options, record);
        dialog.querySelector('h2').textContent = (record ? 'Edit ' : 'Add ') + singular;
        dialog.querySelector('[data-error]').hidden = true;
        dialog.showModal();
    } catch (error) {
        status.textContent = error.message;
    } finally {
        opening = false;
    }
}

async function deleteRecord(id) {
    if (!window.confirm('Delete this ' + singular + '?')) return;
    try {
        await request(endpoint + '/' + id, 'DELETE');
        await load(currentPage, 'Record deleted.');
    } catch (error) {
        status.textContent = error.message;
    }
}

if (dialog) {
    dialog.setAttribute('aria-labelledby', 'management-dialog-title');
    const form = dialog.querySelector('form');
    const save = form.querySelector('[data-save]');
    function closeEditor() {
        if (!save.disabled) dialog.close();
    }
    dialog.querySelector('[data-close]').addEventListener('click', closeEditor);
    dialog.querySelector('[data-cancel]').addEventListener('click', closeEditor);
    dialog.addEventListener('cancel', event => {
        if (save.disabled) event.preventDefault();
    });
    management.querySelector('[data-add]').addEventListener('click', () => openEditor());
    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (save.disabled) return;
        const data = Object.fromEntries(new FormData(form));
        if (resource === 'orders' && editingId === null) {
            data.items = Array.from(form.querySelectorAll('.management-line'), line => ({
                product_id: line.querySelector('select').value,
                quantity: line.querySelector('input').value,
            }));
            delete data.line_product;
            delete data.line_quantity;
        }
        const errors = form.querySelector('[data-error]');
        errors.hidden = true;
        save.disabled = true;
        try {
            const editing = editingId !== null;
            await request(editing ? endpoint + '/' + editingId : endpoint, editing ? 'PATCH' : 'POST', data);
            dialog.close();
            await load(editing ? currentPage : 1, 'Record saved.');
        } catch (error) {
            errors.textContent = error.message;
            errors.hidden = false;
        } finally {
            save.disabled = false;
        }
    });
}

search.addEventListener('input', () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => load(), 200);
});
previous.addEventListener('click', () => load(currentPage - 1));
next.addEventListener('click', () => load(currentPage + 1));
if (filterInput) {
    filterInput.addEventListener('change', () => load());
    if (Array.isArray(filter[2])) {
        fillSelect(filterInput, filter[2], filter[1]);
    } else {
        getOptions()
            .then(options => fillSelect(filterInput, options[filter[2]], filter[1]))
            .catch(error => { status.textContent = error.message; });
    }
}
load();
