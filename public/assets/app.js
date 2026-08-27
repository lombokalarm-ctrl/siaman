(() => {
  const root = document.querySelector('[data-shell="root"]');
  const sidebar = document.querySelector('[data-shell="sidebar"]');

  const openSidebar = () => {
    if (!sidebar) return;
    sidebar.classList.add('is-open');
  };

  const closeSidebar = () => {
    if (!sidebar) return;
    sidebar.classList.remove('is-open');
  };

  root?.addEventListener('click', (e) => {
    const el = e.target instanceof Element ? e.target.closest('[data-action]') : null;
    const action = el?.getAttribute('data-action');
    if (action === 'sidebar-open') openSidebar();
    if (action === 'sidebar-close') closeSidebar();

    if (action === 'invoice-item-add') {
      const body = document.querySelector('[data-invoice-items="body"]');
      const row = document.querySelector('[data-invoice-item="row"]');
      if (!body || !row) return;
      const clone = row.cloneNode(true);
      if (!(clone instanceof HTMLElement)) return;
      clone.querySelectorAll('input').forEach((i) => {
        if (!(i instanceof HTMLInputElement)) return;
        if (i.name === 'item_qty[]') i.value = '1';
        else if (!i.hasAttribute('readonly')) i.value = '';
        else i.value = '0';
      });
      body.appendChild(clone);
      calcInvoicePreview();
    }

    if (action === 'invoice-item-remove') {
      const row = el?.closest('[data-invoice-item="row"]');
      const body = document.querySelector('[data-invoice-items="body"]');
      if (!row || !body) return;
      const rows = body.querySelectorAll('[data-invoice-item="row"]');
      if (rows.length <= 1) {
        row.querySelectorAll('input').forEach((i) => {
          if (!(i instanceof HTMLInputElement)) return;
          if (i.name === 'item_qty[]') i.value = '1';
          else if (!i.hasAttribute('readonly')) i.value = '';
          else i.value = '0';
        });
        calcInvoicePreview();
        return;
      }
      row.remove();
      calcInvoicePreview();
    }
  });

  const num = (v) => {
    const n = Number(String(v ?? '').replace(/,/g, '').trim());
    return Number.isFinite(n) ? n : 0;
  };

  const calcInvoicePreview = () => {
    const tableBody = document.querySelector('[data-invoice-items="body"]');
    if (!tableBody) return;

    let subtotal = 0;
    tableBody.querySelectorAll('[data-invoice-item="row"]').forEach((row) => {
      if (!(row instanceof HTMLElement)) return;
      const qtyEl = row.querySelector('[data-invoice-item="qty"]');
      const priceEl = row.querySelector('[data-invoice-item="price"]');
      const totalEl = row.querySelector('[data-invoice-item="total"]');
      if (!(qtyEl instanceof HTMLInputElement) || !(priceEl instanceof HTMLInputElement) || !(totalEl instanceof HTMLInputElement)) return;
      const qty = num(qtyEl.value);
      const price = num(priceEl.value);
      const total = qty * price;
      totalEl.value = String(Math.round(total * 100) / 100);
      subtotal += total;
    });

    const diskonEl = document.querySelector('[data-invoice-summary="diskon"]');
    const pajakEl = document.querySelector('[data-invoice-summary="pajak"]');
    const grandEl = document.querySelector('[data-invoice-summary="grand_total"]');
    const diskon = diskonEl instanceof HTMLInputElement ? num(diskonEl.value) : 0;
    const pajak = pajakEl instanceof HTMLInputElement ? num(pajakEl.value) : 0;
    const grand = Math.max(0, subtotal - diskon + pajak);
    if (grandEl instanceof HTMLInputElement) {
      grandEl.value = String(Math.round(grand * 100) / 100);
    }
  };

  document.addEventListener('input', (e) => {
    const t = e.target;
    if (!(t instanceof HTMLInputElement) && !(t instanceof HTMLTextAreaElement)) return;
    if (
      t.matches('[data-invoice-item="qty"]') ||
      t.matches('[data-invoice-item="price"]') ||
      t.matches('[data-invoice-summary="diskon"]') ||
      t.matches('[data-invoice-summary="pajak"]')
    ) {
      calcInvoicePreview();
    }
  });

  const paketSelect = document.querySelector('[data-invoice-paket="select"]');
  paketSelect?.addEventListener('change', () => {
    if (!(paketSelect instanceof HTMLSelectElement)) return;
    const opt = paketSelect.selectedOptions?.[0];
    if (!opt) return;
    const nama = opt.getAttribute('data-paket-nama') ?? '';
    const harga = opt.getAttribute('data-paket-harga') ?? '';

    const firstRow = document.querySelector('[data-invoice-items="body"] [data-invoice-item="row"]');
    if (!(firstRow instanceof HTMLElement)) return;
    const labelInput = firstRow.querySelector('input[name="item_label[]"]');
    const qtyInput = firstRow.querySelector('input[name="item_qty[]"]');
    const priceInput = firstRow.querySelector('input[name="item_price[]"]');
    if (!(labelInput instanceof HTMLInputElement) || !(qtyInput instanceof HTMLInputElement) || !(priceInput instanceof HTMLInputElement)) return;

    if (opt.value) {
      if (labelInput.value.trim() === '') labelInput.value = nama;
      if (qtyInput.value.trim() === '') qtyInput.value = '1';
      if (priceInput.value.trim() === '') priceInput.value = harga;
    }
    calcInvoicePreview();
  });

  const lowercaseInputs = document.querySelectorAll('[data-lowercase="true"]');
  lowercaseInputs.forEach((input) => {
    input.addEventListener('input', () => {
      if (!(input instanceof HTMLInputElement) && !(input instanceof HTMLTextAreaElement)) return;
      const start = input.selectionStart;
      const end = input.selectionEnd;
      input.value = input.value.toLowerCase();
      if (typeof start === 'number' && typeof end === 'number') {
        input.setSelectionRange(start, end);
      }
    });
  });

  calcInvoicePreview();
})();
