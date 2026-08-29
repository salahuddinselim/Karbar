<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

$currentUser = require_permission_for_page('transactions');

$products = db()->query('SELECT id, name, unit, sell_price, cost_price FROM products ORDER BY name')->fetchAll();
$customers = db()->query("SELECT id, name FROM parties WHERE type = 'CUSTOMER' ORDER BY name")->fetchAll();
$suppliers = db()->query("SELECT id, name FROM parties WHERE type = 'SUPPLIER' ORDER BY name")->fetchAll();

$flash = flash_get();

$pageTitle = 'New transaction';
require __DIR__ . '/includes/layout_header.php';
$currencyPrefix = $storeSettings['currency'] === 'BDT' ? 'Tk.' : $storeSettings['currency'];
?>

<a href="/transactions.php" class="mb-4 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-300">&larr; Back to transactions</a>

<div class="rounded-2xl border border-line bg-panel overflow-hidden">
  <div class="flex items-center justify-between border-b border-line px-6 py-4">
    <h2 class="text-lg font-semibold text-white">Create Transaction</h2>
  </div>

  <form method="POST" action="/actions/transactions_create.php" enctype="multipart/form-data" class="p-6 space-y-5" id="txForm">
    <?php csrf_field(); ?>
    <input type="hidden" name="items" id="itemsField" value="[]">

    <div class="flex gap-2" id="typeTabs">
      <label class="tx-type-label cursor-pointer rounded-lg border px-4 py-2 text-sm font-medium border-brand-500 bg-brand-500 text-ink has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-brand-400 has-[:focus-visible]:ring-offset-2 has-[:focus-visible]:ring-offset-panel" data-type="SALE">
        <input type="radio" name="type" value="SALE" checked class="sr-only">Sale
      </label>
      <label class="tx-type-label cursor-pointer rounded-lg border px-4 py-2 text-sm font-medium border-line text-gray-300 has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-brand-400 has-[:focus-visible]:ring-offset-2 has-[:focus-visible]:ring-offset-panel" data-type="PURCHASE">
        <input type="radio" name="type" value="PURCHASE" class="sr-only">Purchase
      </label>
      <label class="tx-type-label cursor-pointer rounded-lg border px-4 py-2 text-sm font-medium border-line text-gray-300 has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-brand-400 has-[:focus-visible]:ring-offset-2 has-[:focus-visible]:ring-offset-panel" data-type="EXPENSE">
        <input type="radio" name="type" value="EXPENSE" class="sr-only">Expense
      </label>
    </div>

    <div class="grid grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-300 mb-1">Date</label>
        <input name="date" type="date" value="<?= e(date('Y-m-d')) ?>"
               class="w-full rounded-lg border border-line bg-panel-2 px-3 py-2 text-gray-100 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
      </div>
      <div id="partyField">
        <label class="block text-sm font-medium text-gray-300 mb-1" id="partyLabel">Customer (optional)</label>
        <select name="partyId" id="partySelect" class="w-full rounded-lg border border-line bg-panel-2 px-3 py-2 text-gray-100 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
          <option value="">Walk-in / none</option>
        </select>
      </div>
    </div>

    <div id="expenseFields" class="grid grid-cols-2 gap-4 hidden">
      <div>
        <label class="block text-sm font-medium text-gray-300 mb-1">Description</label>
        <input name="description" placeholder="e.g. Shop rent"
               class="w-full rounded-lg border border-line bg-panel-2 px-3 py-2 text-gray-100 placeholder-gray-600 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-300 mb-1">Amount</label>
        <input name="expenseAmount" type="number" step="any" id="expenseAmount"
               class="w-full rounded-lg border border-line bg-panel-2 px-3 py-2 text-gray-100 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
      </div>
    </div>

    <div id="itemsSection">
      <div class="overflow-hidden rounded-lg border border-line">
        <table class="w-full text-left text-sm">
          <thead class="bg-panel-2 text-xs uppercase tracking-wide text-gray-500">
            <tr>
              <th class="px-3 py-2 w-10">S.N</th>
              <th class="px-3 py-2">Name</th>
              <th class="px-3 py-2 text-right w-24">Qty</th>
              <th class="px-3 py-2 text-right w-28">Rate</th>
              <th class="px-3 py-2 text-right w-28">Amount</th>
              <th class="px-3 py-2 w-8"></th>
            </tr>
          </thead>
          <tbody id="linesContainer"></tbody>
        </table>
      </div>
      <button type="button" id="addLineBtn" class="mt-2 text-sm font-semibold text-brand-400 hover:underline">+ Add Billing Item</button>
      <p id="noLinesMsg" class="mt-2 rounded-lg border border-dashed border-line p-4 text-center text-sm text-gray-500">No items yet. Add one above.</p>
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-300 mb-1">Bill / receipt photo (optional)</label>
      <input name="receipt" type="file" accept="image/jpeg,image/png,image/webp,image/heic"
             class="w-full rounded-lg border border-line bg-panel-2 px-3 py-2 text-sm text-gray-300 file:mr-3 file:rounded-lg file:border-0 file:bg-panel file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-gray-300">
    </div>

    <div class="grid grid-cols-2 gap-4 rounded-lg bg-panel-2 border border-line p-4">
      <div>
        <label class="block text-sm font-medium text-gray-300 mb-1">Payment method</label>
        <select name="paymentMethod" class="w-full rounded-lg border border-line bg-panel px-3 py-2 text-gray-100 focus:border-brand-500 focus:outline-none">
          <option value="cash">Cash</option>
          <option value="bkash">bKash</option>
          <option value="bank">Bank</option>
          <option value="card">Card</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-300 mb-1">Paid now</label>
        <input name="paidAmount" type="number" step="any" id="paidAmount" placeholder="0.00"
               class="w-full rounded-lg border border-line bg-panel px-3 py-2 text-gray-100 focus:border-brand-500 focus:outline-none">
      </div>
      <div class="col-span-2 flex justify-between border-t border-line pt-3 text-sm">
        <span class="font-medium text-gray-300">Total</span>
        <span class="font-bold text-white" id="totalDisplay"><?= e($currencyPrefix) ?> 0.00</span>
      </div>
      <div class="col-span-2 flex justify-between text-sm">
        <span class="font-medium text-gray-300">Due</span>
        <span class="font-bold text-red-400" id="dueDisplay"><?= e($currencyPrefix) ?> 0.00</span>
      </div>
    </div>

    <div class="flex justify-end gap-3 pt-2 border-t border-line">
      <a href="/transactions.php" class="rounded-lg border border-line px-4 py-2 text-sm font-medium text-gray-300 hover:bg-panel-2">Cancel</a>
      <button type="submit" class="rounded-lg bg-brand-500 hover:bg-brand-400 px-5 py-2.5 text-sm font-semibold text-ink">Save transaction</button>
    </div>
  </form>
</div>

<script>
const PRODUCTS = <?= json_encode($products, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
const CUSTOMERS = <?= json_encode($customers, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
const SUPPLIERS = <?= json_encode($suppliers, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
const CUR = <?= json_encode($currencyPrefix) ?>;

let currentType = 'SALE';
let lines = [];

const linesContainer = document.getElementById('linesContainer');
const noLinesMsg = document.getElementById('noLinesMsg');
const itemsSection = document.getElementById('itemsSection');
const expenseFields = document.getElementById('expenseFields');
const partyField = document.getElementById('partyField');
const partySelect = document.getElementById('partySelect');
const partyLabel = document.getElementById('partyLabel');

function escapeHtml(s) {
  return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

function renderPartyOptions() {
  const options = currentType === 'SALE' ? CUSTOMERS : currentType === 'PURCHASE' ? SUPPLIERS : [];
  partyLabel.textContent = (currentType === 'SALE' ? 'Customer' : 'Supplier') + ' (optional)';
  partySelect.innerHTML = '<option value="">Walk-in / none</option>' +
    options.map(p => `<option value="${p.id}">${escapeHtml(p.name)}</option>`).join('');
}

function setType(type) {
  currentType = type;
  lines = [];
  document.querySelectorAll('.tx-type-label').forEach(el => {
    const active = el.dataset.type === type;
    el.classList.toggle('border-brand-500', active);
    el.classList.toggle('bg-brand-500', active);
    el.classList.toggle('text-ink', active);
    el.classList.toggle('border-line', !active);
    el.classList.toggle('text-gray-300', !active);
    el.querySelector('input').checked = active;
  });
  expenseFields.classList.toggle('hidden', type !== 'EXPENSE');
  itemsSection.classList.toggle('hidden', type === 'EXPENSE');
  partyField.classList.toggle('hidden', type === 'EXPENSE');
  renderPartyOptions();
  renderLines();
  recalcTotal();
}

document.querySelectorAll('.tx-type-label').forEach(el => {
  // Listen on the radio's own change event (not the label's click) so keyboard
  // users switching options with arrow keys — which changes the radio without
  // a click on the label — also update the form correctly.
  el.querySelector('input[type=radio]').addEventListener('change', () => setType(el.dataset.type));
});

function addLine() {
  lines.push({ productId: '', name: '', qty: 1, unitPrice: 0 });
  renderLines();
}

function removeLine(idx) {
  lines.splice(idx, 1);
  renderLines();
}

function pickProduct(idx, productId) {
  const p = PRODUCTS.find(x => x.id === productId);
  if (!p) {
    lines[idx].productId = '';
  } else {
    lines[idx].productId = productId;
    lines[idx].name = p.name;
    lines[idx].unitPrice = currentType === 'SALE' ? Number(p.sell_price) : Number(p.cost_price);
  }
  renderLines();
}

function renderLines() {
  noLinesMsg.classList.toggle('hidden', lines.length > 0);
  linesContainer.innerHTML = lines.map((line, idx) => `
    <tr class="border-t border-line">
      <td class="px-3 py-2 text-gray-500">${idx + 1}</td>
      <td class="px-3 py-2">
        <select data-idx="${idx}" class="product-select w-full rounded-lg border border-line bg-panel-2 px-2 py-1.5 text-sm text-gray-100 mb-1">
          <option value="">Custom item</option>
          ${PRODUCTS.map(p => `<option value="${p.id}" ${p.id === line.productId ? 'selected' : ''}>${escapeHtml(p.name)}</option>`).join('')}
        </select>
        <input data-idx="${idx}" data-field="name" placeholder="Item name" value="${escapeHtml(line.name)}" class="line-input w-full rounded-lg border border-line bg-panel-2 px-2 py-1.5 text-sm text-gray-100">
      </td>
      <td class="px-3 py-2 text-right"><input data-idx="${idx}" data-field="qty" type="number" step="any" value="${line.qty}" class="line-input w-20 rounded-lg border border-line bg-panel-2 px-2 py-1.5 text-sm text-gray-100 text-right"></td>
      <td class="px-3 py-2 text-right"><input data-idx="${idx}" data-field="unitPrice" type="number" step="any" value="${line.unitPrice}" class="line-input w-24 rounded-lg border border-line bg-panel-2 px-2 py-1.5 text-sm text-gray-100 text-right"></td>
      <td class="px-3 py-2 text-right text-gray-300 whitespace-nowrap">${(line.qty * line.unitPrice).toFixed(2)}</td>
      <td class="px-3 py-2 text-center"><button type="button" data-idx="${idx}" class="remove-line-btn text-red-400 text-lg leading-none">&times;</button></td>
    </tr>
  `).join('');

  linesContainer.querySelectorAll('.product-select').forEach(el => {
    el.addEventListener('change', () => pickProduct(Number(el.dataset.idx), el.value));
  });
  linesContainer.querySelectorAll('.line-input').forEach(el => {
    el.addEventListener('input', () => {
      const idx = Number(el.dataset.idx);
      const field = el.dataset.field;
      lines[idx][field] = field === 'name' ? el.value : Number(el.value);
      recalcTotal();
      const row = el.closest('tr');
      if (field !== 'name') row.querySelector('td:nth-child(5)').textContent = (lines[idx].qty * lines[idx].unitPrice).toFixed(2);
    });
  });
  linesContainer.querySelectorAll('.remove-line-btn').forEach(el => {
    el.addEventListener('click', () => removeLine(Number(el.dataset.idx)));
  });

  document.getElementById('itemsField').value = JSON.stringify(lines);
  recalcTotal();
}

document.getElementById('addLineBtn').addEventListener('click', addLine);

function recalcTotal() {
  let total = currentType === 'EXPENSE'
    ? (Number(document.getElementById('expenseAmount').value) || 0)
    : lines.reduce((sum, l) => sum + (l.qty * l.unitPrice), 0);
  document.getElementById('totalDisplay').textContent = CUR + ' ' + total.toFixed(2);
  const paidRaw = document.getElementById('paidAmount').value;
  const paid = paidRaw === '' ? total : (Number(paidRaw) || 0);
  document.getElementById('dueDisplay').textContent = CUR + ' ' + Math.max(0, total - paid).toFixed(2);
  document.getElementById('itemsField').value = JSON.stringify(lines);
}

document.getElementById('expenseAmount').addEventListener('input', recalcTotal);
document.getElementById('paidAmount').addEventListener('input', recalcTotal);

document.getElementById('txForm').addEventListener('submit', () => {
  document.getElementById('itemsField').value = JSON.stringify(lines);
});

setType('SALE');
</script>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
