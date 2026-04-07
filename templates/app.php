<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Wasa — Tabi PHP SDK Template</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    [x-cloak] { display: none !important; }
  </style>
</head>
<body class="h-full bg-slate-50 text-slate-900 antialiased">
  <div id="app" class="flex h-full">
    <!-- Sidebar -->
    <aside class="flex w-56 shrink-0 flex-col border-r border-slate-200 bg-white">
      <div class="border-b border-slate-200 px-5 py-4">
        <h1 class="text-lg font-bold tracking-tight text-indigo-600">Wasa</h1>
        <p class="text-[11px] text-slate-400">tabi/sdk PHP template</p>
      </div>
      <nav id="nav" class="flex-1 space-y-0.5 overflow-y-auto p-3"></nav>
      <div class="border-t border-slate-200 p-3">
        <label class="block">
          <span class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Channel ID</span>
          <input id="channelId" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                 value="<?= htmlspecialchars(\Wasa\Env::get('TABI_CHANNEL_ID')) ?>" />
        </label>
      </div>
    </aside>

    <!-- Main -->
    <main class="flex flex-1 flex-col overflow-hidden">
      <header class="border-b border-slate-200 bg-white px-6 py-4">
        <h2 id="pageTitle" class="text-lg font-semibold">Send Text</h2>
      </header>
      <div class="flex flex-1 gap-6 overflow-y-auto p-6">
        <div id="formPanel" class="w-full max-w-md space-y-4"></div>
        <div class="flex-1">
          <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Response</p>
          <pre id="output" class="min-h-[200px] overflow-auto rounded-xl bg-slate-900 p-4 text-xs leading-relaxed text-emerald-300">No response yet.</pre>
        </div>
      </div>
    </main>
  </div>

<script>
const TABS = [
  { id: 'send',      label: 'Send Text' },
  { id: 'media',     label: 'Send Media' },
  { id: 'poll',      label: 'Send Poll' },
  { id: 'location',  label: 'Send Location' },
  { id: 'contact',   label: 'Send Contact' },
  { id: 'contacts',  label: 'Contacts' },
  { id: 'conversations', label: 'Conversations' },
  { id: 'channels',  label: 'Channels' },
];

const inp = 'w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500';
const btnCls = 'rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 transition-colors disabled:opacity-50';
const btnSec = 'rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50';

let currentTab = 'send';
let loading = false;

function field(label, html) {
  return `<label class="block"><span class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">${label}</span>${html}</label>`;
}

function renderNav() {
  document.getElementById('nav').innerHTML = TABS.map(t => {
    const active = t.id === currentTab;
    return `<button onclick="switchTab('${t.id}')" class="w-full rounded-lg px-3 py-2 text-left text-sm font-medium transition-colors ${active ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-100'}">${t.label}</button>`;
  }).join('');
}

function switchTab(id) {
  currentTab = id;
  document.getElementById('output').textContent = 'No response yet.';
  document.getElementById('pageTitle').textContent = TABS.find(t => t.id === id).label;
  renderNav();
  renderForm();
}

async function api(body) {
  loading = true;
  document.getElementById('output').textContent = 'Sending...';
  body.channelId = document.getElementById('channelId').value;
  try {
    const res = await fetch('/api', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(body) });
    const data = await res.json();
    document.getElementById('output').textContent = JSON.stringify(data, null, 2);
  } catch (e) {
    document.getElementById('output').textContent = 'Error: ' + e.message;
  }
  loading = false;
}

function renderForm() {
  const fp = document.getElementById('formPanel');
  let h = '';
  switch (currentTab) {
    case 'send':
      h = `<form onsubmit="event.preventDefault(); api({action:'messages.send', to:this.to.value, content:this.content.value})" class="space-y-4">
        ${field('Recipient (with country code)', `<input name="to" class="${inp}" placeholder="2348012345678" required />`)}
        ${field('Message', `<textarea name="content" class="${inp} min-h-[100px]" placeholder="Hello from Wasa!" required></textarea>`)}
        <button type="submit" class="${btnCls}">Send Text Message</button>
      </form>`;
      break;

    case 'media':
      h = `<form id="mediaForm" onsubmit="event.preventDefault(); handleMediaSubmit(this)" class="space-y-4">
        ${field('Recipient', `<input name="to" class="${inp}" placeholder="2348012345678" required />`)}
        ${field('Media type', `<select name="messageType" class="${inp}"><option value="image">Image</option><option value="video">Video</option><option value="audio">Audio</option><option value="document">Document</option></select>`)}
        <div class="flex gap-2">
          <button type="button" onclick="setMediaMode(true)" id="btnUpload" class="rounded-md px-3 py-1.5 text-xs font-medium transition-colors bg-indigo-100 text-indigo-700">Upload file</button>
          <button type="button" onclick="setMediaMode(false)" id="btnUrl" class="rounded-md px-3 py-1.5 text-xs font-medium transition-colors bg-slate-100 text-slate-500 hover:bg-slate-200">Paste URL</button>
        </div>
        <div id="mediaFileField">${field('Choose file (max 16 MB)', `<input name="file" type="file" accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.xls,.xlsx" class="${inp}" required /><p class="mt-1 text-xs text-slate-400">Images up to 5 MB, videos up to 16 MB, documents up to 16 MB</p>`)}</div>
        <div id="mediaUrlField" style="display:none">${field('Media URL (must be publicly accessible)', `<input name="mediaUrl" class="${inp}" placeholder="https://example.com/photo.jpg" />`)}</div>
        ${field('Caption / text', `<input name="content" class="${inp}" placeholder="Check this out!" />`)}
        <button type="submit" class="${btnCls}">Send Media</button>
      </form>`;
      break;

    case 'poll':
      h = `<form onsubmit="event.preventDefault(); const opts=this.options.value.split('\\n').map(o=>o.trim()).filter(Boolean); api({action:'messages.sendPoll', to:this.to.value, question:this.question.value, options:opts, maxAnswer:+this.maxAns.value})" class="space-y-4">
        ${field('Recipient', `<input name="to" class="${inp}" placeholder="2348012345678" required />`)}
        ${field('Poll question', `<input name="question" class="${inp}" placeholder="What is your favourite?" required />`)}
        ${field('Options (one per line)', `<textarea name="options" class="${inp} min-h-[100px]" required>Option 1\nOption 2\nOption 3</textarea>`)}
        ${field('Max selectable answers', `<input name="maxAns" type="number" min="1" value="1" class="${inp}" />`)}
        <button type="submit" class="${btnCls}">Send Poll</button>
      </form>`;
      break;

    case 'location':
      h = `<form onsubmit="event.preventDefault(); api({action:'messages.sendLocation', to:this.to.value, latitude:+this.lat.value, longitude:+this.lng.value, name:this.locName.value})" class="space-y-4">
        ${field('Recipient', `<input name="to" class="${inp}" placeholder="2348012345678" required />`)}
        ${field('Latitude', `<input name="lat" type="number" step="any" class="${inp}" placeholder="6.5244" required />`)}
        ${field('Longitude', `<input name="lng" type="number" step="any" class="${inp}" placeholder="3.3792" required />`)}
        ${field('Place name', `<input name="locName" class="${inp}" placeholder="Lagos Office" />`)}
        <button type="submit" class="${btnCls}">Send Location</button>
      </form>`;
      break;

    case 'contact':
      h = `<form onsubmit="event.preventDefault(); api({action:'messages.sendContact', to:this.to.value, contactName:this.fn.value, contactPhone:this.cp.value})" class="space-y-4">
        ${field('Recipient', `<input name="to" class="${inp}" placeholder="2348012345678" required />`)}
        ${field('Contact name', `<input name="fn" class="${inp}" placeholder="Jane Doe" required />`)}
        ${field('Contact phone', `<input name="cp" class="${inp}" placeholder="2348099999999" required />`)}
        <button type="submit" class="${btnCls}">Send Contact Card</button>
      </form>`;
      break;

    case 'contacts':
      h = `<div class="space-y-4">
        <button onclick="api({action:'contacts.list'})" class="${btnCls}">List Contacts</button>
        <p class="text-xs text-slate-400">Or create one:</p>
        <form onsubmit="event.preventDefault(); api({action:'contacts.create', data:{phone:this.phone.value, firstName:this.fn.value}})" class="space-y-3 rounded-xl border border-slate-200 bg-white p-4">
          ${field('Phone', `<input name="phone" class="${inp}" placeholder="2348012345678" required />`)}
          ${field('First name', `<input name="fn" class="${inp}" placeholder="John" required />`)}
          <button type="submit" class="${btnSec}">Create Contact</button>
        </form>
      </div>`;
      break;

    case 'conversations':
      h = `<button onclick="api({action:'conversations.list'})" class="${btnCls}">List Conversations</button>`;
      break;

    case 'channels':
      h = `<div class="space-y-3">
        <button onclick="api({action:'channels.list'})" class="${btnCls}">List Channels</button>
        <button onclick="api({action:'channels.status'})" class="${btnSec}">Channel Status</button>
      </div>`;
      break;
  }
  fp.innerHTML = h;
}

let useFileUpload = true;

function setMediaMode(isFile) {
  useFileUpload = isFile;
  const fileField = document.getElementById('mediaFileField');
  const urlField = document.getElementById('mediaUrlField');
  const btnUp = document.getElementById('btnUpload');
  const btnUr = document.getElementById('btnUrl');
  if (fileField && urlField) {
    fileField.style.display = isFile ? '' : 'none';
    urlField.style.display = isFile ? 'none' : '';
    if (isFile) { fileField.querySelector('input').required = true; urlField.querySelector('input').required = false; }
    else { fileField.querySelector('input').required = false; urlField.querySelector('input').required = true; }
  }
  if (btnUp) btnUp.className = `rounded-md px-3 py-1.5 text-xs font-medium transition-colors ${isFile ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-500 hover:bg-slate-200'}`;
  if (btnUr) btnUr.className = `rounded-md px-3 py-1.5 text-xs font-medium transition-colors ${!isFile ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-500 hover:bg-slate-200'}`;
}

async function handleMediaSubmit(form) {
  const out = document.getElementById('output');
  const channelId = document.getElementById('channelId').value;

  if (useFileUpload) {
    const file = form.file.files[0];
    if (!file) { out.textContent = 'Please select a file.'; return; }
    out.textContent = 'Uploading & sending...';
    const fd = new FormData();
    fd.append('file', file);
    fd.append('to', form.to.value);
    fd.append('messageType', form.messageType.value);
    fd.append('content', form.content.value || 'Sent via Wasa (' + form.messageType.value + ')');
    fd.append('channelId', channelId);
    try {
      const res = await fetch('/upload', { method: 'POST', body: fd });
      const data = await res.json();
      out.textContent = JSON.stringify(data, null, 2);
    } catch (e) {
      out.textContent = 'Error: ' + e.message;
    }
  } else {
    api({ action: 'messages.send', to: form.to.value, content: form.content.value || 'Sent via Wasa', messageType: form.messageType.value, mediaUrl: form.mediaUrl.value });
  }
}

renderNav();
renderForm();
</script>
</body>
</html>
