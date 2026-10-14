/* global ApexCharts */
(() => {
  'use strict';
  const $ = id => document.getElementById(id);
  const esc = value => String(value ?? '').replace(/[&<>"']/g, ch => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[ch]));
  const number = new Intl.NumberFormat('es-AR', {maximumFractionDigits: 0});
  const decimal = new Intl.NumberFormat('es-AR', {maximumFractionDigits: 1});
  const money = value => value == null ? '—' : '$ ' + number.format(value);
  const pct = value => value == null ? '—' : decimal.format(value) + '%';
  const num = value => value == null ? '—' : number.format(value);
  const compact = value => new Intl.NumberFormat('es-AR', {notation:'compact',maximumFractionDigits:1}).format(value);
  const monthName = month => new Intl.DateTimeFormat('es-AR', {month:'long',year:'numeric'}).format(new Date(month+'-01T12:00:00'));
  const dimensions = ['client','driver','fleet','service','route','trip','zone','province','integration'];
  const defaults = {client:'Todos los clientes',fleet:'Todas las flotas',service:'Todos los servicios'};
  const colors = ['#727cf5','#fa5c7c','#0acf97','#39afd1','#ffbc00'];
  let report = null, charts = [], controller = null, page = 0, sort = {key:'contribution',desc:true};
  const pageSize = 20;
  function delta(current, previous) {
    if (previous === 0) return current === 0 ? 'Sin variación frente al mes anterior' : 'Sin base comparable el mes anterior';
    const change = (current-previous)/Math.abs(previous)*100;
    return `${change>0?'+':''}${pct(change)} frente al mes anterior completo`;
  }
  function kpi(label,value,icon,color,note,comparison) {
    return `<article class="cf-kpi" style="--cf-accent:${color}"><div class="cf-kpi-label"><i class="ri-${icon}"></i>${esc(label)}</div><div class="lg-kpi-number">${value}</div><div class="lg-kpi-meta">${esc(note)}</div><div class="lg-kpi-meta">${esc(comparison)}</div></article>`;
  }
  function operation(label,value,note) {return `<div class="lg-operation-item"><span>${esc(label)}</span><strong>${esc(value)}</strong><small>${esc(note)}</small></div>`;}
  function insight(title, text) {return `<article><h3>${esc(title)}</h3><p>${esc(text)}</p></article>`;}
  function baseChart(type, height=310) {
    const dark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    return {chart:{type,height,fontFamily:'inherit',foreColor:dark?'#aab8c5':'#8391a2',toolbar:{show:false},animations:{enabled:!matchMedia('(prefers-reduced-motion: reduce)').matches}},
      theme:{mode:dark?'dark':'light'},colors, dataLabels:{enabled:false},grid:{borderColor:dark?'#37404a':'#edf0f5',strokeDashArray:4},
      stroke:{width:2.5,curve:'smooth'},legend:{position:'top',horizontalAlign:'left'},
      tooltip:{y:{formatter:money}},noData:{text:'Sin datos para este período'},yaxis:{labels:{formatter:value=>'$ '+compact(value)}}};
  }
  async function chart(id, options) { const instance = new ApexCharts($(id),options);charts.push(instance);await instance.render(); }
  async function renderCharts(r) {
    charts.forEach(c=>c.destroy()); charts=[];
    const trend = baseChart('line');
    trend.series=[{name:'Venta sin IVA',type:'column',data:r.trend.map(m=>m.sale)},{name:'Flete registrado',type:'column',data:r.trend.map(m=>m.cost)},{name:'Contribución',type:'line',data:r.trend.map(m=>m.contribution)}];
    trend.xaxis={categories:r.trend.map(m=>m.month),labels:{formatter:value=>{const [y,m]=String(value).split('-');return m+'/'+y.slice(2);}}};
    trend.plotOptions={bar:{columnWidth:'55%',borderRadius:3}};trend.stroke={width:[0,0,3],curve:'smooth'};
    await chart('chart-trend',trend);
    const incidence=baseChart('radialBar',270);
    incidence.series=r.total.incidence==null?[]:[Math.min(100,Math.max(0,r.total.incidence))];
    incidence.colors=[r.total.incidence>100?'#fa5c7c':'#727cf5'];
    incidence.plotOptions={radialBar:{startAngle:-120,endAngle:120,hollow:{size:'65%'},track:{background:'#e9edf5'},dataLabels:{name:{fontSize:'12px',offsetY:70},value:{fontSize:'34px',fontWeight:700,offsetY:-5,formatter:()=>pct(r.total.incidence)}}}};
    incidence.labels=['Flete sobre venta']; await chart('chart-incidence',incidence);
    const top=[...r.groups.client].sort((a,b)=>b.sale-a.sale).slice(0,10);
    const clients=baseChart('bar',Math.max(310,top.length*33+65));
    clients.series=[{name:'Contribución',data:top.map(x=>({x:x.name,y:x.contribution,fillColor:x.contribution<0?'#fa5c7c':'#727cf5'}))}];
    clients.plotOptions={bar:{horizontal:true,borderRadius:3,barHeight:'55%'}};clients.xaxis={labels:{formatter:v=>'$ '+compact(v)}};clients.yaxis={labels:{maxWidth:210}};
    await chart('chart-clients',clients);
    const service=r.groups.service.filter(x=>x.sale>0);
    const mix=baseChart('donut');mix.series=service.map(x=>x.sale);mix.labels=service.map(x=>x.name);mix.legend={position:'bottom'};mix.stroke={width:3};mix.plotOptions={pie:{donut:{size:'70%',labels:{show:true,value:{formatter:value=>money(Number(value))},total:{show:true,label:'Venta sin IVA',formatter:()=>money(r.total.sale)}}}}};
    await chart('chart-services',mix);
    const daily=baseChart('area');daily.series=[{name:'Venta sin IVA',data:r.daily.map(d=>d.sale)},{name:'Flete registrado',data:r.daily.map(d=>d.cost)}];daily.xaxis={categories:r.daily.map(d=>d.date.slice(8)),tickAmount:10};daily.fill={type:'gradient',gradient:{opacityFrom:.24,opacityTo:.03}};
    await chart('chart-daily',daily);
  }
  function filteredGroups() {
    if (!report) return [];
    const search=$('group-search').value.trim().toLocaleLowerCase('es');
    return report.groups[$('group-dimension').value].filter(row=>row.name.toLocaleLowerCase('es').includes(search)).sort((a,b)=>{
      const av=a[sort.key],bv=b[sort.key];let c;
      if (av==null) return bv==null?0:1;if(bv==null)return -1;
      c=sort.key==='name'?String(av).localeCompare(String(bv),'es',{numeric:true}):av-bv;
      return sort.desc?-c:c;
    });
  }
  function cells(row,label) {
    return `<td title="${esc(label)}">${esc(label)}</td><td>${num(row.services)}</td><td>${money(row.sale)}</td><td>${money(row.cost)}</td><td class="${row.contribution<0?'lg-negative':'lg-positive'}">${money(row.contribution)}</td><td>${pct(row.margin)}</td><td>${pct(row.incidence)}</td><td><span class="lg-cover ${row.coverage>=95?'good':''}">${pct(row.coverage)}</span></td><td>${num(row.km)}</td><td>${money(row.sale_km)}</td><td>${money(row.cost_km)}</td>`;
  }
  function renderTable() {
    const rows=filteredGroups(),pages=Math.max(1,Math.ceil(rows.length/pageSize));page=Math.min(page,pages-1);
    $('dimension-rows').innerHTML=rows.slice(page*pageSize,(page+1)*pageSize).map(row=>`<tr>${cells(row,row.name)}</tr>`).join('') || '<tr><td colspan="11" class="text-center text-muted py-4">No hay resultados para esta búsqueda.</td></tr>';
    $('dimension-total').innerHTML=`<tr>${cells(report.total,'Total del período filtrado')}</tr>`;
    $('table-count').textContent=`${num(rows.length)} grupos · orden ${sort.desc?'descendente':'ascendente'}`;
    $('table-page').textContent=`${page+1} / ${pages}`;$('table-prev').disabled=page===0;$('table-next').disabled=page>=pages-1;
  }
  function renderInsights(r) {
    const t=r.total,top=[...r.groups.client].sort((a,b)=>b.sale-a.sale)[0];
    const loss=r.groups.client.filter(x=>x.contribution<0);
    const coverageText=t.services?`${num(t.services-t.covered)} de ${num(t.services)} servicios no tienen costo positivo registrado. Margen sobre servicios con costo: ${pct(t.covered_margin)}. ${t.coverage<95?'El margen puede estar sobreestimado; completá las rendiciones antes de decidir tarifas.':'La cobertura permite una comparación más consistente.'}`:'No hay servicios de envío para evaluar cobertura; revisá las órdenes del período.';
    const concentration=top&&t.sale>0?`${top.name} concentra ${pct(top.sale/t.sale*100)} de la venta. ${top.sale/t.sale>.3?'La dependencia comercial es elevada: conviene revisar diversificación y condiciones.':'Compará su volumen y margen antes de negociar precios.'}`:'El período no tiene ventas para analizar concentración.';
    const performance=loss.length?`${num(loss.length)} clientes tienen contribución negativa por ${money(loss.reduce((a,b)=>a+b.contribution,0))}. Revisá tarifa, recorridos y costo de flete de esos servicios.`:t.sale>0?`El flete registrado representa ${pct(t.incidence)} de la venta. Quedan ${money(t.contribution)} antes de gastos generales y costos faltantes.`:'No hay venta registrada: no se calcula incidencia ni margen porcentual.';
    $('executive-insights').innerHTML=insight('Cobertura antes que margen',coverageText)+insight('Concentración de clientes',concentration)+insight('Presión del costo logístico',performance);
    $('incidence-analysis').textContent=t.incidence==null?'No se calcula sin una venta positiva.':`Por cada $100 vendidos, ${money(t.incidence)} corresponden a flete registrado. ${t.incidence>100?'El costo supera la venta; el arco llega al 100%, el valor muestra la incidencia real.':`Cobertura de costos: ${pct(t.coverage)}.`}`;
  }
  function renderExpenses(r) {
    const g=r.global;
    $('expense-summary').innerHTML=[['Ventas brutas de servicios',money(g.gross)],['Gastos registrados de la empresa',money(g.expenses)],['Referencia: venta bruta − gastos',money(g.home_result)]].map(([label,value])=>`<div><span>${esc(label)}</span><strong>${value}</strong></div>`).join('');
    $('expense-rows').innerHTML=r.expenses.map(e=>`<tr><td>${esc(e.name)} <small class="text-muted">${esc(e.account)}</small></td><td class="text-end">${money(e.amount)}</td><td class="text-end">${g.expenses?pct(Number(e.amount)/g.expenses*100):'—'}</td></tr>`).join('') || '<tr><td colspan="3" class="text-muted">Sin gastos registrados en el mes seleccionado.</td></tr>';
  }
  async function render(r) {
    report=r; const t=r.total,p=r.previous;
    $('dashboard-content').hidden=false;
    const now=new Date(),current=new Intl.DateTimeFormat('sv-SE',{timeZone:'America/Argentina/Cordoba',year:'numeric',month:'2-digit'}).format(now).slice(0,7);
    $('period-label').textContent=monthName(r.month);
    $('period-note').textContent=`${r.month===current?'· Mes en curso, acumulado parcial':'· Mes calendario completo'}${r.filtered?' · Vista filtrada':''}`;
    $('empty-report').classList.toggle('d-none',t.services>0||t.sale!==0||t.cost!==0||t.km>0);
    $('financial-kpis').innerHTML=kpi('Venta de servicios',money(t.sale),'funds-line',colors[0],'Sin IVA · incluye ingreso operativo rendido',delta(t.sale,p.sale))+
      kpi('Flete registrado',money(t.cost),'truck-line',colors[1],`${pct(t.incidence)} de la venta · importes guardados`,delta(t.cost,p.cost))+
      kpi('Contribución provisional',money(t.contribution),'line-chart-line',t.contribution<0?colors[1]:colors[2],`${pct(t.margin)} de margen · antes de gastos generales`,delta(t.contribution,p.contribution))+
      kpi('Cobertura de costos',pct(t.coverage),'shield-check-line',colors[3],`${num(t.covered)} de ${num(t.services)} servicios con costo positivo`,p.coverage==null?'Sin base comparable':`${decimal.format((t.coverage??0)-p.coverage)} puntos vs. mes anterior`);
    $('operation-kpis').innerHTML=operation('Servicios',num(t.services),'Códigos únicos · incluye colectas')+operation('Entregados',num(t.delivered),`${t.services?pct(t.delivered/t.services*100):'—'} de los servicios`)+operation('Devueltos',num(t.returned),`${t.services?pct(t.returned/t.services*100):'—'} de los servicios`)+operation('Salidas vinculadas',num(t.trips),`${num(t.clients)} grupos de clientes`)+operation('Kilómetros reales',num(t.km),`${pct(t.services?t.km_services/t.services*100:null)} servicios con km`)+operation('Venta por servicio',money(t.ticket),`Flete por servicio: ${money(t.cost_service)}`)+operation('Venta por km cubierto',money(t.sale_km),'Solo servicios con km registrados')+operation('Flete por km cubierto',money(t.cost_km),'Misma base de km que la venta')+operation('Servicios por salida',t.trips?decimal.format(t.linked_services/t.trips):'—','Solo servicios con orden vinculada')+operation('Peso registrado',num(t.weight)+' kg','Sin estimar pesos faltantes')+operation('Visitas registradas',num(t.visits),'Suma del campo Visitas')+operation('Servicios facturados',num(t.invoiced),`${t.services?pct(t.invoiced/t.services*100):'—'} del período`);
    renderInsights(r);renderExpenses(r);
    $('fleet-analysis').innerHTML=r.groups.fleet.map(f=>`<div class="lg-fleet-row"><div class="lg-fleet-head"><strong>${esc(f.name)}</strong><strong class="${f.contribution<0?'lg-negative':'lg-positive'}">${money(f.contribution)}</strong></div><p>${num(f.services)} servicios · Flete/venta ${pct(f.incidence)} · Con costo ${pct(f.coverage)}</p><div class="lg-track"><span style="width:${Math.min(100,Math.max(0,f.coverage??0))}%"></span></div></div>`).join('') || '<p class="text-muted">Sin operación en el período.</p>';
    const warnings=[...r.warnings];
    if(t.services>t.covered) warnings.unshift(`${num(t.services-t.covered)} servicios sin costo positivo. Su contribución incluye únicamente costos disponibles.`);
    if(t.services>t.km_services) warnings.push(`${num(t.services-t.km_services)} servicios sin kilómetros reales vinculados: quedan fuera de las razones por kilómetro.`);
    if(r.filtered)warnings.push('Los filtros afectan rentabilidad, tendencia y productividad. La sección de gastos conserva el total de la empresa.');
    warnings.push('Las ventas por recorrido distribuidas entre servicios pueden cambiar de mes respecto de Home (que usa la fecha de la orden). La venta del dashboard se expresa sin IVA; la referencia de gastos muestra importes registrados.');
    $('quality-alerts').innerHTML=warnings.map(w=>`<div class="lg-quality">${esc(w)}</div>`).join('');
    dimensions.forEach(d=>{
      const el=$('filter-'+d),selected=el.value;
      el.replaceChildren(new Option(defaults[d]||'Todos',''));
      const options=[...r.options[d]];if(selected&&!options.includes(selected))options.push(selected);
      options.forEach(v=>el.add(new Option(v,v)));el.value=selected;
    });
    page=0;renderTable();
    await renderCharts(r);
  }
  async function load() {
    if(document.querySelector('.lg-page').getAttribute('aria-busy')==='true')return;
    if(controller)controller.abort();controller=new AbortController();const active=controller;
    const params=new URLSearchParams(new FormData($('dashboard-filters')));
    document.querySelector('.lg-page').setAttribute('aria-busy','true');
    $('dashboard-error').classList.add('d-none');$('dashboard-status').textContent='Calculando ventas, fletes y productividad…';$('export-report').disabled=true;
    try {
      const response=await fetch('php/dashboard_logistica.php?'+params,{signal:active.signal,headers:{'X-Requested-With':'XMLHttpRequest'}});
      if(response.status===401)throw new Error('La sesión venció. Volvé a iniciar sesión para consultar el panel.');
      if(response.redirected)throw new Error('Iniciá sesión para consultar el dashboard.');
      const data=await response.json();if(!response.ok||!data.ok)throw new Error(data.error||'No se pudo consultar el dashboard.');
      if(active!==controller)return;
      await render(data);
      $('dashboard-status').textContent='Actualizado '+new Intl.DateTimeFormat('es-AR',{hour:'2-digit',minute:'2-digit',timeZone:'America/Argentina/Cordoba'}).format(new Date(data.generated_at))+' · Comparaciones contra el mes anterior completo.';
      $('export-report').disabled=false;
    } catch(error) {
      if(error.name==='AbortError')return;
      $('dashboard-error').textContent=error.message;$('dashboard-error').classList.remove('d-none');
      $('dashboard-status').textContent='No se pudo actualizar el período. Los resultados anteriores se ocultaron.';
      $('dashboard-content').hidden=true;report=null;
    } finally {if(active===controller)document.querySelector('.lg-page').setAttribute('aria-busy','false');}
  }
  function exportReport() {
    if(!report)return;
    const rows=[['Dashboard logística',report.month],['Fecha','Fecha de servicio'],['Moneda','ARS'],['Venta','Sin IVA (21%); ingreso not_invoice según importe registrado'],['Contribución','Provisional; no descuenta gastos generales ni costos faltantes'],['Dimensión',$('group-dimension').selectedOptions[0].text]];
    dimensions.forEach(d=>{const value=$('filter-'+d).value;if(value)rows.push(['Filtro '+d,value]);});
    rows.push([],['Grupo','Servicios','Venta sin IVA','Flete registrado','Contribución','Margen %','Incidencia %','Cobertura %','Km reales','Venta/km cubierto','Flete/km cubierto']);
    filteredGroups().forEach(r=>rows.push([r.name,r.services,r.sale,r.cost,r.contribution,r.margin,r.incidence,r.coverage,r.km,r.sale_km,r.cost_km]));
    rows.push([],['Gastos de empresa (sin filtros)','Importe registrado']);report.expenses.forEach(e=>rows.push([e.name,Number(e.amount)]));
    // Prevent spreadsheet formula injection in labels from user-maintained master data.
    const cell=v=>{if(v==null)return '""';let s=typeof v==='number'?String(v).replace('.',','):String(v);if(typeof v!=='number'&&/^[=+@\-\t\r\n]/.test(s))s="'"+s;return '"'+s.replace(/"/g,'""')+'"';};
    const blob=new Blob(['\ufeff'+rows.map(row=>row.map(cell).join(';')).join('\r\n')],{type:'text/csv;charset=utf-8'});
    const url=URL.createObjectURL(blob),a=document.createElement('a');a.href=url;a.download=`logistica-${report.month}-${$('group-dimension').value}.csv`;a.click();URL.revokeObjectURL(url);
  }
  $('dashboard-filters').addEventListener('submit',e=>{e.preventDefault();load();});
  $('clear-filters').addEventListener('click',()=>{dimensions.forEach(d=>$('filter-'+d).value='');load();});
  $('group-dimension').addEventListener('change',()=>{page=0;renderTable();});
  $('group-search').addEventListener('input',()=>{page=0;renderTable();});
  $('table-prev').addEventListener('click',()=>{page--;renderTable();});$('table-next').addEventListener('click',()=>{page++;renderTable();});
  document.querySelectorAll('[data-sort]').forEach(button=>button.addEventListener('click',()=>{sort={key:button.dataset.sort,desc:sort.key===button.dataset.sort?!sort.desc:true};page=0;renderTable();}));
  $('export-report').addEventListener('click',exportReport);
  load();
})();
