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
echo "OK: reconciliation across 9 dimensions, month/year comparison, filters, cost coverage, measured km, route facts, losses and empty periods.\n";
