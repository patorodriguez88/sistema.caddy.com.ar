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
    trend.series=[{name:'Venta sin IVA',type:'column',data:r.trend.map(m=>m.sale)},{name:'Costo de flete registrado',type:'column',data:r.trend.map(m=>m.cost)},{name:'Contribución',type:'line',data:r.trend.map(m=>m.contribution)}];
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
    const daily=baseChart('area');daily.series=[{name:'Venta sin IVA',data:r.daily.map(d=>d.sale)},{name:'Costo de flete registrado',data:r.daily.map(d=>d.cost)}];daily.xaxis={categories:r.daily.map(d=>d.date.slice(8)),tickAmount:10};daily.fill={type:'gradient',gradient:{opacityFrom:.24,opacityTo:.03}};
    await chart('chart-daily',daily);
    const productive=r.driver_performance.rows.filter(d=>d.key!=='unknown'&&d.days>0).sort((a,b)=>b.delivered-a.delivered).slice(0,10);
    const drivers=baseChart('bar',Math.max(310,productive.length*35+65));
    drivers.series=[{name:'Entregas por día con actividad',data:productive.map(d=>({x:d.name,y:Number(d.deliveries_day.toFixed(2))}))}];
    drivers.colors=['#39afd1'];drivers.plotOptions={bar:{horizontal:true,borderRadius:3,barHeight:'55%'}};
    drivers.xaxis={labels:{formatter:v=>decimal.format(v)}};drivers.yaxis={labels:{maxWidth:220}};
    drivers.tooltip={y:{formatter:v=>decimal.format(v)+' entregas / día'}};
    await chart('chart-drivers',drivers);
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
    const performance=loss.length?`${num(loss.length)} clientes tienen contribución negativa por ${money(loss.reduce((a,b)=>a+b.contribution,0))}. Revisá tarifa, recorridos y costo de flete de esos servicios.`:t.sale>0?`El costo de flete registrado representa ${pct(t.incidence)} de la venta. Quedan ${money(t.contribution)} antes de gastos generales y costos faltantes.`:'No hay venta registrada: no se calcula incidencia ni margen porcentual.';
    $('executive-insights').innerHTML=insight('Cobertura antes que margen',coverageText)+insight('Concentración de clientes',concentration)+insight('Presión del costo logístico',performance);
    $('incidence-analysis').textContent=t.incidence==null?'No se calcula sin una venta positiva.':`Por cada $100 vendidos, ${money(t.incidence)} corresponden a costo de flete registrado. ${t.incidence>100?'El costo supera la venta; el arco llega al 100%, el valor muestra la incidencia real.':`Cobertura de costos: ${pct(t.coverage)}.`}`;
  }
  function driverRows() {
    if(!report)return [];
    const search=$('driver-search').value.trim().toLocaleLowerCase('es'),key=$('driver-sort').value;
    return report.driver_performance.rows.filter(d=>(d.name+' '+d.fleet).toLocaleLowerCase('es').includes(search)).sort((a,b)=>{
      if(key==='name')return a.name.localeCompare(b.name,'es',{numeric:true});
      if(a[key]==null)return b[key]==null?0:1;if(b[key]==null)return -1;
      return b[key]-a[key] || a.name.localeCompare(b.name,'es');
    });
  }
  function renderDriverTable() {
    const rows=driverRows();
    $('driver-rows').innerHTML=rows.map(d=>{
      const costs=[];
      if(d.imputed_cost!==0)costs.push('De orden / km: '+money(d.imputed_cost));
      if(d.shared_services)costs.push(num(d.shared_services)+' servicios compartidos');
      const days=d.days?num(d.days):'—',productivity=d.deliveries_day==null?'—':decimal.format(d.deliveries_day);
      return `<tr><td title="${esc(d.name)}"><strong>${esc(d.name)}</strong><small>${esc(d.fleet)}</small><small class="lg-driver-context">${num(d.delivered)} entregas · ${days} días con actividad</small></td><td>${num(d.delivered)}</td><td>${num(d.attempts)}<small>${num(d.managed)} códigos gestionados</small></td><td>${days}</td><td>${productivity}</td><td>${pct(d.effectiveness)}</td><td>${money(d.sale)}</td><td>${money(d.cost)}<small>${costs.map(esc).join('<br>')}</small>${d.settlement_applicable?`<small class="lg-driver-settlement">Liquidado: ${money(d.settled_cost)}<br>Pendiente: ${money(d.pending_cost)}</small>`:''}</td><td>${d.settlement_applicable?money(d.settled_cost):'—'}</td><td>${d.settlement_applicable?money(d.pending_cost):'—'}</td><td class="${d.contribution<0?'lg-negative':'lg-positive'}">${money(d.contribution)}<small>${d.coverage==null||d.coverage<95?'Costo incompleto / sin base':'Antes de gastos generales'}</small><small class="lg-driver-coverage">Cobertura de costos: ${pct(d.coverage)}</small></td><td>${pct(d.margin)}</td><td><span class="lg-cover ${d.coverage>=95?'good':''}">${pct(d.coverage)}</span></td></tr>`;
    }).join('') || '<tr><td colspan="13" class="text-center text-muted py-4">No hay repartidores para esta selección.</td></tr>';
    $('driver-count').textContent=`${num(rows.length)} de ${num(report.driver_performance.rows.length)} repartidores o identidades · Los días y entregas corresponden a los servicios filtrados, con gestiones dentro del mes.`;
  }
  function renderDriverPerformance(r) {
    const perf=r.driver_performance,s=perf.summary;
    const tile=(label,value,note)=>`<article class="lg-driver-kpi"><span>${esc(label)}</span><strong>${esc(value)}</strong><small>${esc(note)}</small></article>`;
    $('driver-kpis').innerHTML=tile('Entregas comprobadas',num(s.delivered),`${num(s.unassigned_delivered)} sin repartidor identificable`)+
      tile('Repartidores con actividad',num(s.active_drivers),`${num(s.person_days)} jornadas registradas (días-persona)`)+
      tile('Entregas por jornada',s.deliveries_day==null?'—':decimal.format(s.deliveries_day),'Entregas atribuidas / jornadas con actividad')+
      tile('Flete externo liquidado',money(s.settled_cost),'Rendiciones Rendido=1 · no confirma transferencia');
    const active=perf.rows.filter(d=>d.key!=='unknown'&&d.days>0),volume=[...active].sort((a,b)=>b.delivered-a.delivered)[0];
    const regular=active.filter(d=>d.days>=3),rhythm=[...regular].sort((a,b)=>b.deliveries_day-a.deliveries_day)[0];
    const financial=perf.rows.filter(d=>d.key!=='unknown'&&d.sale>0&&d.coverage>=95).sort((a,b)=>b.contribution-a.contribution)[0];
    $('driver-insights').innerHTML=insight('Volumen y ritmo de reparto',volume?`${volume.name}: ${num(volume.delivered)} entregas en ${num(volume.days)} días con actividad (${decimal.format(volume.deliveries_day)} por día). ${rhythm?`Entre quienes registran al menos 3 días, ${rhythm.name} tiene el mayor ritmo: ${decimal.format(rhythm.deliveries_day)} entregas por día.`:'Todavía no hay una base de 3 días por persona para comparar ritmos sostenidos.'}`:'Sin gestiones de entrega verificables para los servicios seleccionados dentro del mes.')+
      insight('Contribución con costos cubiertos',financial?`${financial.name} aporta ${money(financial.contribution)}, con ${pct(financial.coverage)} de cobertura de costos. La venta atribuida es ${money(financial.sale)} y el costo registrado ${money(financial.cost)}.`:'No hay repartidores con venta positiva y al menos 95% de cobertura de costos. Completá costos antes de comparar rentabilidad.')+
      insight('Cómo comparar',`Compará rutas y tipos de servicio similares: volumen, distancia y densidad influyen en las entregas por día. ${s.shared_services?`${num(s.shared_services)} servicios tienen varios repartidores: la venta se reparte según el costo registrado de cada uno.`:'La contribución no incluye gastos generales ni salarios sin imputación.'}`);
    $('driver-quality').textContent='Días con actividad no equivale a asistencia laboral. Se usan gestiones de Seguimiento, nunca la fecha de pago. La venta y el costo conservan el mes del servicio; las entregas requieren evidencia dentro de ese mes. Liquidado y pendiente corresponden solo a rendiciones externas. La contribución es provisional cuando faltan costos.';
    renderDriverTable();
  }
  function renderExpenses(r) {
    const g=r.global;
    $('expense-summary').innerHTML=[['Ingresos generados sin IVA',money(g.sale)],['Costos y gastos registrados',money(g.expenses)],['Resultado operativo provisional',money(g.home_result)]].map(([label,value])=>`<div><span>${esc(label)}</span><strong>${value}</strong></div>`).join('');
    $('expense-rows').innerHTML=r.expenses.map(e=>`<tr><td>${esc(e.name)} <small class="text-muted">${esc(e.account)}</small></td><td class="text-end">${money(e.amount)}</td><td class="text-end">${g.expenses?pct(Number(e.amount)/g.expenses*100):'—'}</td></tr>`).join('') || '<tr><td colspan="3" class="text-muted">Sin gastos registrados en el mes seleccionado.</td></tr>';
  }
  async function render(r) {
    report=r; const t=r.total,p=r.previous;
    $('dashboard-content').hidden=false;
    const now=new Date(),current=new Intl.DateTimeFormat('sv-SE',{timeZone:'America/Argentina/Cordoba',year:'numeric',month:'2-digit'}).format(now).slice(0,7);
    $('period-label').textContent=monthName(r.month);
    $('period-note').textContent=`${r.month===current?'· Mes en curso, acumulado parcial':'· Mes calendario completo'}${r.filtered?' · Vista filtrada':''}`;
    $('empty-report').classList.toggle('d-none',t.services>0||t.sale!==0||t.cost!==0||t.km>0);
    $('financial-kpis').innerHTML=kpi('Venta de servicios',money(t.sale),'funds-line',colors[0],'Sin IVA · facturado o pendiente de facturar',delta(t.sale,p.sale))+
      kpi('Costo de flete registrado',money(t.cost),'truck-line',colors[1],`${pct(t.incidence)} de la venta · importes guardados`,delta(t.cost,p.cost))+
      kpi('Contribución operativa',money(t.contribution),'line-chart-line',t.contribution<0?colors[1]:colors[2],`${pct(t.margin)} de margen · antes de gastos generales`,delta(t.contribution,p.contribution))+
      kpi('Cobertura de costos',pct(t.coverage),'shield-check-line',colors[3],`${num(t.covered)} de ${num(t.services)} servicios con costo positivo`,p.coverage==null?'Sin base comparable':`${decimal.format((t.coverage??0)-p.coverage)} puntos vs. mes anterior`);
    $('operation-kpis').innerHTML=operation('Servicios',num(t.services),'Códigos únicos · incluye colectas')+operation('Entregados',num(t.delivered),`${t.services?pct(t.delivered/t.services*100):'—'} de los servicios`)+operation('Devueltos',num(t.returned),`${t.services?pct(t.returned/t.services*100):'—'} de los servicios`)+operation('Salidas vinculadas',num(t.trips),`${num(t.clients)} grupos de clientes`)+operation('Kilómetros reales',num(t.km),`${pct(t.services?t.km_services/t.services*100:null)} servicios con km`)+operation('Venta por servicio',money(t.ticket),`Flete por servicio: ${money(t.cost_service)}`)+operation('Venta por km cubierto',money(t.sale_km),'Solo servicios con km registrados')+operation('Flete por km cubierto',money(t.cost_km),'Misma base de km que la venta')+operation('Servicios por salida',t.trips?decimal.format(t.linked_services/t.trips):'—','Solo servicios con orden vinculada')+operation('Peso registrado',num(t.weight)+' kg','Sin estimar pesos faltantes')+operation('Visitas registradas',num(t.visits),'Suma del campo Visitas')+operation('Servicios facturados',num(t.invoiced),`${t.services?pct(t.invoiced/t.services*100):'—'} del período`);
    renderInsights(r);renderExpenses(r);renderDriverPerformance(r);
    $('fleet-analysis').innerHTML=r.groups.fleet.map(f=>`<div class="lg-fleet-row"><div class="lg-fleet-head"><strong>${esc(f.name)}</strong><strong class="${f.contribution<0?'lg-negative':'lg-positive'}">${money(f.contribution)}</strong></div><p>${num(f.services)} servicios · Flete/venta ${pct(f.incidence)} · Con costo ${pct(f.coverage)}</p><div class="lg-track"><span style="width:${Math.min(100,Math.max(0,f.coverage??0))}%"></span></div></div>`).join('') || '<p class="text-muted">Sin operación en el período.</p>';
    const warnings=[...r.warnings];
    if(t.services>t.covered) warnings.unshift(`${num(t.services-t.covered)} servicios sin costo positivo. Su contribución incluye únicamente costos disponibles.`);
    if(t.services>t.km_services) warnings.push(`${num(t.services-t.km_services)} servicios sin kilómetros reales vinculados: quedan fuera de las razones por kilómetro.`);
    if(r.filtered)warnings.push('Los filtros afectan rentabilidad, tendencia y productividad. La sección de gastos conserva el total de la empresa.');
    warnings.push('Base de ingresos compartida con Resultados del negocio: envíos por fecha de servicio y recorridos por fecha de salida. Los filtros modifican el alcance del análisis. Los gastos conservan su fecha e importe registrados; el resultado de empresa es provisional hasta conciliarlos con los costos de flete.');
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
    const rows=[['Dashboard logística',report.month],['Fecha','Fecha de servicio'],['Moneda','ARS'],['Venta','Transporte sin IVA; otros ingresos operativos por su neto registrado'],['Contribución','Provisional; no descuenta gastos generales ni costos faltantes'],['Dimensión',$('group-dimension').selectedOptions[0].text]];
    dimensions.forEach(d=>{const value=$('filter-'+d).value;if(value)rows.push(['Filtro '+d,value]);});
    rows.push([],['Grupo','Servicios','Venta sin IVA','Costo de flete registrado','Contribución','Margen %','Incidencia %','Cobertura %','Km reales','Venta/km cubierto','Flete/km cubierto']);
    filteredGroups().forEach(r=>rows.push([r.name,r.services,r.sale,r.cost,r.contribution,r.margin,r.incidence,r.coverage,r.km,r.sale_km,r.cost_km]));
    rows.push([],['Gastos de empresa (sin filtros)','Importe registrado']);report.expenses.forEach(e=>rows.push([e.name,Number(e.amount)]));
    downloadCsv(rows,`logistica-${report.month}-${$('group-dimension').value}.csv`);
  }
  function exportDrivers() {
    if(!report)return;
    const rows=[['Desempeño por repartidor',report.month],['Base','Servicios del mes; gestiones comprobadas dentro del mismo mes'],['Días','Fechas distintas con gestión de entrega; no asistencia laboral'],['Liquidado','Rendido=1; no confirma transferencia'],['Venta compartida','Se atribuye según costo positivo por repartidor; sin costos, partes iguales']];
    dimensions.forEach(d=>{const v=$('filter-'+d).value;if(v)rows.push(['Filtro '+d,v]);});
    rows.push([],['Repartidor','Flota','Entregas comprobadas','Gestiones','Códigos gestionados','Días con actividad','Entregas/día','Efectividad %','Venta atribuida sin IVA','Costo de flete registrado','Flete externo liquidado','Pendiente de liquidar externo','Costo de orden/km','Contribución provisional','Margen %','Cobertura %','Servicios compartidos']);
    driverRows().forEach(d=>rows.push([d.name,d.fleet,d.delivered,d.attempts,d.managed,d.days,d.deliveries_day,d.effectiveness,d.sale,d.cost,d.settlement_applicable?d.settled_cost:null,d.settlement_applicable?d.pending_cost:null,d.imputed_cost,d.contribution,d.margin,d.coverage,d.shared_services]));
    downloadCsv(rows,`repartidores-${report.month}.csv`);
  }
  function downloadCsv(rows,filename) {
    // Prevent spreadsheet formula injection in labels from user-maintained master data.
    const cell=v=>{if(v==null)return '""';let s=typeof v==='number'?String(v).replace('.',','):String(v);if(typeof v!=='number'&&/^[=+@\-\t\r\n]/.test(s))s="'"+s;return '"'+s.replace(/"/g,'""')+'"';};
    const blob=new Blob(['\ufeff'+rows.map(row=>row.map(cell).join(';')).join('\r\n')],{type:'text/csv;charset=utf-8'});
    const url=URL.createObjectURL(blob),a=document.createElement('a');a.href=url;a.download=filename;a.click();URL.revokeObjectURL(url);
  }
  $('dashboard-filters').addEventListener('submit',e=>{e.preventDefault();load();});
  $('clear-filters').addEventListener('click',()=>{dimensions.forEach(d=>$('filter-'+d).value='');load();});
  $('group-dimension').addEventListener('change',()=>{page=0;renderTable();});
  $('group-search').addEventListener('input',()=>{page=0;renderTable();});
  $('table-prev').addEventListener('click',()=>{page--;renderTable();});$('table-next').addEventListener('click',()=>{page++;renderTable();});
  document.querySelectorAll('[data-sort]').forEach(button=>button.addEventListener('click',()=>{sort={key:button.dataset.sort,desc:sort.key===button.dataset.sort?!sort.desc:true};page=0;renderTable();}));
  $('export-report').addEventListener('click',exportReport);
  $('export-drivers').addEventListener('click',exportDrivers);
  $('driver-search').addEventListener('input',renderDriverTable);
  $('driver-sort').addEventListener('change',renderDriverTable);
  document.querySelectorAll('[data-driver-view]').forEach(button=>button.addEventListener('click',()=>{
    $('driver-table').dataset.view=button.dataset.driverView;
    document.querySelectorAll('[data-driver-view]').forEach(b=>{const selected=b===button;b.classList.toggle('active',selected);b.setAttribute('aria-pressed',String(selected));});
  }));
  load();
})();
