async function api(url, opts={}){
  const res = await fetch(url, opts);
  if(!res.ok) throw new Error('Request failed');
  const ct = res.headers.get('content-type') || '';
  if (ct.includes('application/json')) return res.json();
  return res.text();
}

function peso(n){return `₱${Number(n||0).toFixed(2)}`}

async function refreshSummary(){
  try{const s = await api('/api/summary.php');
    const bd = (s.breakdown||[]).map(b=>`<div>${b.method}: ${peso(b.total)}</div>`).join('');
    document.getElementById('summary').innerHTML = `<div><strong>Total Collected Today</strong><div style="font-size:22px">${peso(s.total_collected_today)}</div></div><div>Transactions: ${s.transactions}</div>${bd}`;
  }catch(e){console.error(e)}
}

async function doSearch(){
  const q = document.getElementById('q').value.trim();
  if(!q) return;
  const {items} = await api(`/api/search.php?q=${encodeURIComponent(q)}`);
  if (!items.length){document.getElementById('consumer').innerHTML = 'No results';return;}
  const c = items[0];
  document.getElementById('consumer').innerHTML = `
    <div><strong>${c.name}</strong> <span class="muted">(${c.account_no})</span></div>
    <div class="muted">${c.sitio}</div>
    <div>Billing Period: ${c.period_start || '-'} - ${c.period_end || '-'}</div>
    <div>Current Charges: ${peso(c.current_charges||0)} | Unpaid: ${peso(c.unpaid_balance||0)} | Penalties: ${peso(c.penalties||0)}</div>
    <div><strong>Total Due: ${peso(c.total_due||0)}</strong></div>`;
  document.getElementById('consumer_id').value = c.id;
  document.getElementById('bill_id').value = c.bill_id || 0;
  document.getElementById('amount_due').value = peso(c.total_due || 0);
  document.getElementById('amount_received').value = (c.total_due||0).toFixed(2);
  updateChange();
  renderReceiptPreview(null);
}

function parsePeso(str){return parseFloat(String(str).replace(/[^0-9.]/g,''))||0}
function updateChange(){
  const due = parsePeso(document.getElementById('amount_due').value);
  const rec = parsePeso(document.getElementById('amount_received').value);
  const change = Math.max(rec - due, 0);
  document.getElementById('change').value = peso(change);
}

async function processPayment(ev){
  ev.preventDefault();
  const consumer_id = Number(document.getElementById('consumer_id').value||0);
  const bill_id = Number(document.getElementById('bill_id').value||0);
  const amount = parsePeso(document.getElementById('amount_received').value);
  const method = document.getElementById('method').value;
  const notes = document.getElementById('notes').value;
  if(!consumer_id || !bill_id){alert('Select a consumer with an unpaid bill.');return;}
  const res = await api('/api/pay.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({consumer_id,bill_id,amount,method,notes})});
  renderReceiptPreview(res);
  await refreshSummary();
  await refreshRecent();
}

function renderReceiptPreview(data){
  const el = document.getElementById('receipt');
  if(!data){el.innerHTML = '<em>Receipt will appear here after payment.</em>'; return;}
  el.innerHTML = `
  SAN ISIDRO WATER ASSOCIATION\nCatanauan, Quezon\nOfficial Receipt\n\nReceipt: ${data.receipt_no}\nDate: ${data.created_at}\nAccount: ${data.consumer.account_no}\nConsumer: ${data.consumer.name}\nAmount Paid: ${peso(data.amount_paid)}\nMethod: ${data.method}\nCashier: ${data.cashier}`.replace(/\n/g,'<br>');
  el.dataset.receipt = data.receipt_no;
}

async function refreshRecent(){
  const r = await api('/api/recent.php');
  const rows = r.items.map(x=>`<tr><td>${x.receipt_no}</td><td>${x.time}</td><td>${x.consumer}</td><td>${x.account_no}</td><td style="text-align:right">${peso(x.amount_paid)}</td><td>${x.method}</td><td><a href="/api/receipt.php?no=${encodeURIComponent(x.receipt_no)}" target="_blank">🔍</a></td></tr>`).join('');
  document.getElementById('recent').innerHTML = `<table class="table"><thead><tr><th>Receipt No</th><th>Time</th><th>Consumer</th><th>Account No</th><th>Amount</th><th>Method</th><th></th></tr></thead><tbody>${rows}</tbody></table>`;
}

async function onPrint(){
  const el = document.getElementById('receipt');
  const no = el.dataset.receipt;
  if(!no){alert('No receipt to print.');return;}
  window.open(`/api/receipt.php?no=${encodeURIComponent(no)}`,'_blank');
}

async function onExport(){ window.location.href = '/api/export_csv.php'; }

document.addEventListener('DOMContentLoaded',()=>{
  const q = document.getElementById('q');
  document.getElementById('btnSearch').addEventListener('click', doSearch);
  q.addEventListener('keydown', e=>{ if(e.key==='Enter'){ e.preventDefault(); doSearch(); }});
  document.getElementById('amount_received').addEventListener('input', updateChange);
  document.getElementById('payForm').addEventListener('submit', processPayment);
  document.getElementById('btnPrint').addEventListener('click', onPrint);
  document.getElementById('btnRefreshRecent').addEventListener('click', refreshRecent);
  document.getElementById('btnExport').addEventListener('click', onExport);
  refreshSummary();
  refreshRecent();
});
