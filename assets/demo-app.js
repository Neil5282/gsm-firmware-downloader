
const API='https://roms.danielspringer.at/api/ota.php';
const DEMO_KEY='gsm_demo_logs';
let catalog=[], current=null;

function fmtSize(bytes){if(!bytes)return 'Unknown size';let n=Number(bytes),u=['B','KB','MB','GB','TB'],i=0;while(n>=1024&&i<u.length-1){n/=1024;i++}return n.toFixed(i?1:0)+' '+u[i]}
function setOptions(sel, values, first){sel.innerHTML=''; let o=document.createElement('option');o.textContent=first;o.value='';sel.appendChild(o);[...values].sort().forEach(v=>{let x=document.createElement('option');x.value=v;x.textContent=v;sel.appendChild(x)})}
async function loadCatalog(){
  const box=document.getElementById('device');
  try{
    const r=await fetch(API+'?latest=1'); catalog=await r.json();
    const devices=[...new Set(catalog.map(x=>x.device).filter(Boolean))];
    setOptions(box,devices,'Choose a device…');
    document.getElementById('region').innerHTML='<option value="">All regions</option>';
    box.onchange=updateRegions;
    document.getElementById('region').onchange=updateVersions;
    document.getElementById('version').onchange=updateVersions;
    document.getElementById('results').innerHTML='<div class="muted">Live catalog loaded. Choose a device to continue.</div>';
  }catch(e){box.innerHTML='<option>Catalog unavailable</option>';document.getElementById('results').innerHTML='<div class="alert">Could not load the live OTA catalog.</div>'}
}
function updateRegions(){let d=document.getElementById('device').value;let a=catalog.filter(x=>!d||x.device===d);setOptions(document.getElementById('region'),[...new Set(a.map(x=>x.region).filter(Boolean))],'All regions');updateVersions()}
function updateVersions(){let d=document.getElementById('device').value,r=document.getElementById('region').value;let a=catalog.filter(x=>(!d||x.device===d)&&(!r||x.region===r));setOptions(document.getElementById('version'),a.map(x=>x.version).filter(Boolean),'Latest');}
function resolveFirmware(){
  let d=document.getElementById('device').value,r=document.getElementById('region').value,v=document.getElementById('version').value;
  let a=catalog.filter(x=>(!d||x.device===d)&&(!r||x.region===r)&&(!v||x.version===v));
  current=a[0]; const el=document.getElementById('results');
  if(!current){el.innerHTML='<div class="muted">Select a device first.</div>';return}
  el.innerHTML=`<div class="result-card"><div><h3>${esc(current.device||'Firmware')}</h3><div class="meta"><span class="pill">${esc(current.region||'')}</span><span class="pill">${esc(current.version||'')}</span><span class="pill">${fmtSize(current.size)}</span>${current.model?`<span class="pill">${esc(current.model)}</span>`:''}</div><p class="muted">Security patch: ${esc(current.security_patch||'Not provided')} · Release ID: ${esc(current.id||'')}</p></div><button class="primary" onclick="startDownload()">Start Download</button></div>`;
}
async function startDownload(){
  if(!current)return;
  const record={...current,started_at:new Date().toISOString(),status:'started'};
  const logs=JSON.parse(localStorage.getItem(DEMO_KEY)||'[]');logs.unshift(record);localStorage.setItem(DEMO_KEY,JSON.stringify(logs));
  if(current.source_url) window.open(current.source_url,'_blank','noopener');
  alert('Download session recorded. In the full PHP/MySQL version this action is inserted into download_logs before redirecting to the source.');
}
function resetFilters(){location.reload()}
function esc(s){return String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]))}
loadCatalog();

const params=new URLSearchParams(location.search);document.getElementById('role').textContent=(params.get('role')||'user').toUpperCase();if(params.get('role')==='admin')document.getElementById('adminLink').style.display='inline-block';
