'use strict';
const test=require('node:test');
const assert=require('node:assert/strict');
const fs=require('node:fs');
const vm=require('node:vm');
const source=fs.readFileSync(__dirname+'/../casepilot-demo.html','utf8');
const script=source.match(/<script>\s*([\s\S]+?)<\/script>/)[1];
const context={};vm.createContext(context);vm.runInContext(script,context);
const {createStore,toCSV,validData,KEY}=context.CasePilotDemo;
const NOW=Date.parse('2026-10-09T05:00:00Z');
const memory=()=>{const data=new Map();return {getItem:key=>data.has(key)?data.get(key):null,setItem:(key,value)=>data.set(key,value),get:key=>data.get(key)};};
const fields={title:'Revisar enlace de muestra',requester:'Ana Demo',description:'El enlace de prueba lleva a una página incorrecta.',category:'Software',priority:'high'};
function parseCSV(input){
  const rows=[];let row=[],field='',quoted=false;
  for(let i=0;i<input.length;i++){
    const c=input[i];if(i===0&&c==='\uFEFF')continue;
    if(c==='"'){if(quoted&&input[i+1]==='"'){field+='"';i++;}else quoted=!quoted;}
    else if(!quoted&&c===','){row.push(field);field='';}
    else if(!quoted&&c==='\r'&&input[i+1]==='\n'){row.push(field);rows.push(row);row=[];field='';i++;}
    else field+=c;
  }
  if(quoted)throw new Error('Unclosed CSV quote');
  assert.equal(field,'');assert.equal(row.length,0);return rows;
}
test('creates, enforces the workflow, persists, reopens and resets only its own key',()=>{
  const storage=memory();storage.setItem('other-app','keep');const store=createStore(storage,()=>NOW);
  assert.equal(store.tickets.length,9);
  const created=store.create(fields);assert.equal(created.code,'CP-0010');assert.equal(created.status,'open');
  assert.equal(Date.parse(created.due_at)-NOW,48*3600000);
  assert.throws(()=>store.update(created.id,{status:'resolved',priority:'high',assignee:''}),/flujo/);
  store.update(created.id,{status:'in_progress',priority:'high',assignee:'Alex Demo'});
  store.update(created.id,{status:'resolved',priority:'critical',assignee:'Alex Demo'});
  let restored=createStore(storage,()=>NOW);let ticket=restored.tickets.find(t=>t.id===created.id);
  assert.equal(ticket.status,'resolved');assert.equal(ticket.due_at,created.due_at);
  restored.update(created.id,{status:'open',priority:'critical',assignee:'Alex Demo'});
  assert.equal(restored.tickets.find(t=>t.id===created.id).status,'open');
  restored.reset();assert.equal(restored.tickets.length,9);assert.equal(restored.events.length,1);assert.equal(storage.get('other-app'),'keep');
  assert.equal(createStore(storage,()=>NOW).tickets.length,9);
});
test('simulated agent cannot assign but can progress the ticket',()=>{
  const store=createStore(memory(),()=>NOW),created=store.create(fields);store.setRole('agent');
  assert.throws(()=>store.update(created.id,{status:'in_progress',priority:'high',assignee:'Alex Demo'}),/administración/);
  assert.equal(store.tickets.find(t=>t.id===created.id).status,'open');
  assert.equal(store.update(created.id,{status:'in_progress',priority:'low',assignee:''}),true);
});
test('combined search, status and priority select the export rows',()=>{
  const store=createStore(memory(),()=>NOW),created=store.create(fields);
  assert.equal(store.filtered({query:'  ANA DEMO ',status:'open',priority:'high'}).length,1);
  assert.equal(store.filtered({query:'cp-0010'})[0].id,created.id);
  assert.equal(store.filtered({query:'Ana Demo',status:'resolved'}).length,0);
  const rows=parseCSV(toCSV(store.filtered({query:'Ana Demo',status:'open',priority:'high'})));
  assert.equal(rows.length,2);assert.equal(rows[1][0],created.code);assert.equal(rows[1][1],fields.title);
});
test('CSV round-trip preserves quotes, commas, line breaks and protects spreadsheet formulas',()=>{
  const store=createStore(memory(),()=>NOW);
  store.create({...fields,title:'=HYPERLINK("https://example.test","Demo")',requester:'@SUM(1,2)'});
  const raw=store.filtered({query:'hyperlink'})[0];raw.title+='\r\nSegunda línea';
  const result=toCSV([raw]),rows=parseCSV(result);
  assert.ok(result.startsWith('\uFEFF'));assert.equal(rows[1][1],"'"+raw.title);assert.equal(rows[1][5],"'@SUM(1,2)");
  for(const value of ['=1+1',' +SUM(1,2)','-2+3','@cmd','\t=1','\r=1','\n=1']){const ticket={...raw,title:value};assert.equal(parseCSV(toCSV([ticket]))[1][1],"'"+value);}
});
test('user strings remain literal and the UI renders through text nodes',()=>{
  const title='<img src=x onerror=alert(1)>',store=createStore(memory(),()=>NOW);
  const ticket=store.create({...fields,title,description:'<script>alert("demo")</script>'});
  assert.equal(ticket.title,title);assert.equal(createStore({getItem:()=>JSON.stringify({version:1,tickets:[ticket],events:[]}),setItem(){}},()=>NOW).tickets[0].description,'<script>alert("demo")</script>');
  assert.equal(/\.innerHTML\s*=|insertAdjacentHTML|\beval\(/.test(script),false);
  assert.match(script,/element\.textContent=String\(value\)/);
});
test('invalid saved structures recover with a visible reason, unavailable storage keeps edits in memory',()=>{
  const corrupt=memory();corrupt.setItem(KEY,'{"version":1,"tickets":[{}],"events":[]}');
  const store=createStore(corrupt,()=>NOW);assert.equal(store.restoreReason,'invalid');assert.equal(store.tickets.length,9);
  assert.equal(validData(JSON.parse(corrupt.get(KEY))),true);
  const unavailable={getItem(){throw new Error('blocked');},setItem(){throw new Error('full');}};
  const temporary=createStore(unavailable,()=>NOW);assert.equal(temporary.persistent,false);assert.equal(temporary.create(fields).code,'CP-0010');assert.equal(temporary.tickets.length,10);
});
test('rejects invalid inputs and enforces a bounded queue without partial writes',()=>{
  const store=createStore(memory(),()=>NOW);assert.throws(()=>store.create({...fields,title:'  a  '}),/Título/);assert.equal(store.tickets.length,9);
  assert.throws(()=>store.create({...fields,priority:'__proto__'}),/prioridad/);
  for(let i=9;i<100;i++)store.create({...fields,title:'Solicitud sintética número '+i});
  assert.equal(store.tickets.length,100);assert.throws(()=>store.create(fields),/100 solicitudes/);assert.equal(store.tickets.length,100);
});
test('validates IDs, dates, enums and duplicates in saved data',()=>{
  const storage=memory();createStore(storage,()=>NOW);const good=JSON.parse(storage.get(KEY));assert.equal(validData(good),true);
  for(const [key,value] of [['id',1.5],['code','wrong'],['status','__proto__'],['due_at','not a date'],['assignee','Someone real']]){
    const candidate=JSON.parse(JSON.stringify(good));candidate.tickets[0][key]=value;assert.equal(validData(candidate),false,key);
  }
  good.tickets.push(good.tickets[0]);assert.equal(validData(good),false);
});
