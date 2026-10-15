<?php
// Run: php SistemaTriangular/Inicio/tests/dashboard_logistica_test.php
if (PHP_SAPI !== 'cli') {http_response_code(404);exit;}
require_once __DIR__.'/../php/dashboard_logistica_model.php';
function check($condition,string $message): void {if(!$condition)throw new RuntimeException($message);}
function closeTo($actual,$expected,string $message): void {check(abs($actual-$expected)<0.00001,$message);}
function fact(array $changes=[]): array {
    return array_replace(['date'=>'2026-01-12','code'=>'A','client'=>'Cliente A','driver'=>'Repartidor A','fleet'=>'Externo','service'=>'Flex','route'=>'Ruta A','trip'=>'Orden 1','zone'=>'Centro','province'=>'Córdoba','integration'=>'Mercado Libre','sale'=>100,'gross'=>121,'cost'=>60,'covered'=>1,'services'=>1,'delivered'=>1,'returned'=>0,'km'=>10,'km_services'=>1,'weight'=>2,'visits'=>1,'invoiced'=>1,'allocation'=>false,'cost_source'=>'Rendición externa'],$changes);
}
$fixture=['facts'=>[
    fact(),
    fact(['code'=>'B','client'=>'Cliente B','sale'=>200,'gross'=>242,'cost'=>0,'covered'=>0,'km'=>0,'km_services'=>0,'trip'=>'Sin orden vinculada']),
    fact(['code'=>'C','client'=>'Cliente A','date'=>'2025-12-20','sale'=>50,'cost'=>75]),
    // A route order with no shipments contributes sale/cost/km without inventing a service.
    fact(['code'=>'','client'=>'Cliente A','sale'=>80,'gross'=>96.8,'cost'=>20,'services'=>0,'covered'=>0,'delivered'=>0,'km'=>20,'km_services'=>0,'trip'=>'Orden 2']),
], 'expenses'=>[['month'=>'2026-01','account'=>'G1','name'=>'Gasto','amount'=>90]],'warnings'=>[]];
$r=DashboardLogistica::report($fixture,'2026-01');
closeTo($r['total']['sale'],380,'Current month sale');
closeTo($r['total']['cost'],80,'Freight sum');
closeTo($r['total']['contribution'],300,'Contribution');
closeTo($r['total']['coverage'],50,'Missing cost remains in service denominator');
closeTo($r['total']['sale_km'],6,'Km revenue excludes services without km');
closeTo($r['total']['cost_km'],80/30,'Km cost shares the same measured population');
check($r['total']['services']===2,'A route fact must not invent a shipment');
check($r['total']['trips']===2,'Distinct trips');
check($r['total']['linked_services']===1,'Only linked services in trip productivity');
closeTo($r['previous']['sale'],50,'Previous month crosses year boundary');
check(count($r['trend'])===12 && $r['trend'][0]['month']==='2025-02','Rolling 12 months');
check(count($r['daily'])===31,'Month day count');
foreach($r['groups'] as $dimension=>$rows) foreach(['sale','cost','contribution','services','km'] as $metric) closeTo(array_sum(array_column($rows,$metric)),$r['total'][$metric],"$dimension reconciles $metric");
$f=DashboardLogistica::report($fixture,'2026-01',['client'=>'Cliente A']);
closeTo($f['total']['sale'],180,'Client filter');
closeTo($f['previous']['sale'],50,'Same filter on previous month');
closeTo($f['global']['expenses'],90,'Expenses remain company-wide under filters');
closeTo($f['global']['sale'],380,'Company reconciliation remains unfiltered');
check(count($f['groups']['client'])===1,'No client leakage');
$empty=DashboardLogistica::report($fixture,'2026-02');
check($empty['total']['margin']===null && $empty['total']['coverage']===null && $empty['total']['sale_km']===null,'No invented ratios in empty month');
check(count($empty['daily'])===28,'February');
$loss=DashboardLogistica::metric([fact(['sale'=>100,'cost'=>140])]);
closeTo($loss['margin'],-40,'Negative margin');closeTo($loss['incidence'],140,'Incidence is not capped at 100');
check(DashboardLogistica::clientLabel(0,'Cliente A',[5=>'Cliente A'],['cliente a'=>[5]])==='Cliente A · 5','Resolve missing client ID by unique exact name');
check(DashboardLogistica::clientLabel(0,'Cliente A',[5=>'Cliente A',6=>'Cliente A'],['cliente a'=>[5,6]])==='Cliente A · Sin ID','Never merge ambiguous names');
// Courier performance must reconcile economics, while activity uses real dates and identities.
$alice=['key'=>'user:1','name'=>'Mismo Nombre · 1','fleet'=>'Externo','cost'=>60,'settled_cost'=>40,'pending_cost'=>20,'imputed_cost'=>0,'settlement_applicable'=>true];
$bob=['key'=>'user:2','name'=>'Mismo Nombre · 2','fleet'=>'Externo','cost'=>40,'settled_cost'=>40,'pending_cost'=>0,'imputed_cost'=>0,'settlement_applicable'=>true];
$split=DashboardLogistica::allocateDrivers([$alice,$bob],300);
closeTo($split[0]['sale'],180,'Shared revenue follows individual positive costs');
closeTo($split[1]['sale'],120,'No duplicated revenue for shared courier service');
$zeroSplit=DashboardLogistica::allocateDrivers([array_replace($alice,['cost'=>0]),array_replace($bob,['cost'=>0])],100);
closeTo($zeroSplit[0]['sale'],50,'Equal attribution without positive costs');
$performanceFacts=[
    fact(['code'=>'A','sale'=>300,'cost'=>100,'driver_allocations'=>$split,'delivery_eligible'=>true]),
    fact(['code'=>'B','sale'=>100,'cost'=>60,'driver_allocations'=>DashboardLogistica::allocateDrivers([$alice],100),'delivery_eligible'=>true]),
    fact(['code'=>'COLECTA','sale'=>0,'cost'=>0,'driver_allocations'=>DashboardLogistica::allocateDrivers([array_replace($bob,['cost'=>0,'settled_cost'=>0,'pending_cost'=>0])],0),'delivery_eligible'=>false]),
    fact(['code'=>'UNKNOWN','sale'=>0,'cost'=>0,'driver_allocations'=>DashboardLogistica::allocateDrivers([['key'=>'unknown','name'=>'Sin repartidor identificado','fleet'=>'Sin identificar','cost'=>0,'settled_cost'=>0,'pending_cost'=>0,'imputed_cost'=>0]],0),'delivery_eligible'=>true]),
];
function eventRow(int $id,string $code,string $date,string $state,array $identity,string $trip='1'): array {
    return ['id'=>$id,'CodigoSeguimiento'=>$code,'Fecha'=>$date,'Hora'=>'12:00:00','Estado'=>$state,'NumerodeOrden'=>$trip,'driver_identity'=>$identity];
}
$events=[
    eventRow(1,'A','2026-01-12','No se pudo entregar',$alice),
    eventRow(2,'A','2026-01-12','No se pudo entregar',$alice), // duplicate status, one management
    eventRow(3,'A','2026-01-13','Entregado al Cliente',$bob),
    eventRow(4,'A','2026-01-13','Entregado al Cliente',$bob), // duplicate delivery, one delivery
    eventRow(5,'B','2026-01-13','Entregado al Cliente',$alice),
    eventRow(6,'B','2026-01-14','Entregado al Cliente',$alice), // later success: credit the code once
    eventRow(7,'COLECTA','2026-01-12','Entregado al Cliente',$bob),
    eventRow(8,'A','2026-02-01','Entregado al Cliente',$alice), // later month not brought forward
    eventRow(9,'NOT-SELECTED','2026-01-12','Entregado al Cliente',$alice),
    eventRow(10,'UNKNOWN','2026-01-12','Entregado al Cliente',['key'=>'unknown','name'=>'Sin repartidor identificado']),
    eventRow(11,'A','2026-01-15','Retirado del Cliente',$alice),
    eventRow(12,'A','2026-01-16','No se pudo Retirar',$alice),
];
$perf=DashboardLogistica::driverPerformance($performanceFacts,$events,'2026-01');
$byId=array_column($perf['rows'],null,'key');
check(count($perf['rows'])===3,'Equal names never merge distinct IDs; unknown stays separate');
check($byId['user:1']['days']===3,'Days are distinct real activity dates, not financial timestamps');
check($byId['user:1']['attempts']===3,'Repeated statuses count as one management');
check($byId['user:1']['delivered']===1 && $byId['user:2']['delivered']===1,'One delivery per code credited to actual final courier');
check($perf['summary']['delivered']===3 && $perf['summary']['unassigned_delivered']===1,'Unknown deliveries visible without inventing courier attribution');
check($perf['summary']['person_days']===4,'Unknown identity excluded from actual courier person-days');
closeTo($perf['summary']['deliveries_day'],0.5,'Daily productivity uses assigned deliveries only');
closeTo($perf['summary']['sale'],400,'Courier revenue reconciles with service cohort');
closeTo($perf['summary']['cost'],160,'Courier cost reconciles with freight');
closeTo($perf['summary']['settled_cost'],120,'Liquidated freight is separate from total cost');
closeTo($byId['user:1']['pending_cost'],40,'Pending external liquidation is retained');
closeTo($byId['user:1']['contribution'],160,'Individual contribution');
$noActivity=DashboardLogistica::driverPerformance($performanceFacts,[],'2026-01');
check($noActivity['summary']['person_days']===0 && $noActivity['summary']['deliveries_day']===null,'No inferred workdays when activity evidence is missing');
$selectedPerf=DashboardLogistica::driverPerformance([$performanceFacts[1]],$events,'2026-01');
check($selectedPerf['summary']['delivered']===1 && count($selectedPerf['rows'])===1,'Operational activity respects selected service filters');
// Both screens read the same monthly income basis, including signed adjustments and
// orders whose service date differs from shipment date. Expense detail is the same snapshot.
$common=['facts'=>[
    fact(['date'=>'2026-01-31','sale'=>100,'gross'=>121,'sale_simple'=>100,'sale_flex'=>0,'sale_route'=>0,'sale_collection'=>0]),
    fact(['code'=>'B','date'=>'2026-01-31','sale'=>20,'gross'=>0,'cost'=>0,'sale_simple'=>0,'sale_flex'=>0,'sale_route'=>0,'sale_collection'=>20]),
    fact(['code'=>'','date'=>'2026-02-01','services'=>0,'covered'=>0,'sale'=>200,'gross'=>242,'cost'=>0,'sale_simple'=>0,'sale_flex'=>0,'sale_route'=>200,'sale_collection'=>0]),
    fact(['code'=>'','date'=>'2026-01-20','services'=>0,'covered'=>0,'sale'=>-10,'gross'=>-12.1,'cost'=>0,'sale_simple'=>-10,'sale_flex'=>0,'sale_route'=>0,'sale_collection'=>0]),
], 'expenses'=>[
    ['month'=>'2026-01','account'=>'000421600','name'=>'Fletes','amount'=>60],
    ['month'=>'2026-01','account'=>'EXTRA-Generales','name'=>'Generales (extra)','category'=>'Generales','amount'=>15],
], 'warnings'=>[]];
$business=DashboardLogistica::businessSummary($common,'2026-02',['2026-01'=>500]);
foreach($business['months'] as $m){
    $operational=DashboardLogistica::report($common,$m['month']);
    closeTo($m['sale'],$operational['total']['sale'],'Business/operational revenue reconciles every month');
    closeTo($m['sale_simple']+$m['sale_flex']+$m['sale_route']+$m['sale_collection'],$m['sale'],'Revenue components reconcile without theoretical 5%');
    closeTo($m['result'],$m['sale']-$m['expenses'],'Expenses deducted once; direct freight reference not deducted again');
    check($m['provisional']===true,'Calendar close does not certify complete accrued expenses');
}
closeTo($business['ventas_total']['2026-01'],110,'Net signed service revenue and non-invoiced fee');
closeTo($business['ventas_recorridos']['2026-01'],0,'Order revenue does not follow shipment month');
closeTo($business['ventas_recorridos']['2026-02'],200,'Order revenue keeps actual departure month');
closeTo($business['cobrado']['2026-01'],500,'Receipts remain informational');
closeTo($business['gastos']['2026-01'],75,'Expense category detail matches total');
closeTo(array_sum(array_map(fn($r)=>$r['ene-26']??0,$business['datos'])),75,'Same snapshot account detail reconciles');
echo "OK: courier performance, shared monthly income, net components, signed adjustments, order dates, receipts and single expense deduction.\n";
