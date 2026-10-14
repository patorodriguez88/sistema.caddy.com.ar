<?php
/** Read-only logistics profitability model. No payment dates are used to date services. */
final class DashboardLogistica
{
    private mysqli $db;
    public array $warnings = [];
    public function __construct(mysqli $db) { $this->db = $db; }
    private function rows(string $sql): array {
        $result = $this->db->query($sql);
        if (!$result) throw new RuntimeException('No se pudo consultar el dashboard');
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    public function load(string $month): array {
        $end = (new DateTimeImmutable($month.'-01'))->modify('+1 month')->format('Y-m-d');
        $start = (new DateTimeImmutable($month.'-01'))->modify('-11 months')->format('Y-m-d');
        // month is strictly validated by the endpoint. Escaping also protects direct model callers.
        $start = $this->db->real_escape_string($start); $end = $this->db->real_escape_string($end);
        $columns = array_column($this->rows('SHOW COLUMNS FROM TransClientes'), 'Field');
        $hasOrigin = in_array('Origen', $columns, true);
        if (!$hasOrigin) $this->warnings[] = 'Esta base no tiene TransClientes.Origen: Tienda Nube y otras integraciones no pueden identificarse con certeza.';
        $select = 'id,Fecha,CodigoSeguimiento,Debe,Haber,FormaDePago,idClienteOrigen,idClienteDestino,RazonSocial,ClienteDestino,Recorrido,NumerodeOrden,Flex,shipments_id,CodigoProveedor,LocalidadDestino,ProvinciaDestino,Entregado,Devuelto,Cantidad,Peso,Visitas,Facturado';
        if ($hasOrigin) $select .= ',Origen';
        $valid = "Eliminado=0 AND TRIM(IFNULL(CodigoSeguimiento,''))<>'' AND IFNULL(Haber,0)=0";
        $raw = $this->rows("SELECT $select FROM TransClientes WHERE $valid AND Fecha>='$start' AND Fecha<'$end'");
        $orders = $this->rows("SELECT l.*,v.Aliados,r.Nombre AS RutaNombre,r.Zona AS RutaZona,r.Cliente AS RutaCliente
            FROM Logistica l
            LEFT JOIN (SELECT Dominio,MAX(Aliados) Aliados FROM Vehiculos GROUP BY Dominio) v ON v.Dominio=l.Patente
            LEFT JOIN (SELECT Numero,MAX(Nombre) Nombre,MAX(Zona) Zona,MAX(Cliente) Cliente FROM Recorridos GROUP BY Numero) r ON r.Numero=l.Recorrido
            WHERE l.Eliminado=0 AND ((l.Fecha>='$start' AND l.Fecha<'$end') OR EXISTS
            (SELECT 1 FROM TransClientes t WHERE t.NumerodeOrden=l.NumerodeOrden AND t.Eliminado=0 AND t.Fecha>='$start' AND t.Fecha<'$end')) ORDER BY l.id");
        $byOrder = [];
        foreach ($orders as $o) {
            $key = (int)$o['NumerodeOrden']>0 ? (string)$o['NumerodeOrden'] : 'orphan-'.$o['id'];
            if (isset($byOrder[$key])) $this->warnings['duplicate_orders'] = 'Hay números de orden duplicados: se usa la orden activa con mayor ID para evitar multiplicar importes.';
            $byOrder[$key] = $o;
        }
        $orderIds = array_filter(array_map('intval', array_keys($byOrder)));
        // Include out-of-period shipments in allocation denominators: filtering a month must never
        // multiply a whole order price/cost by just the shipments visible in that month.
        foreach (array_chunk($orderIds, 500) as $chunk) {
            $raw = array_merge($raw, $this->rows("SELECT $select FROM TransClientes WHERE $valid AND NumerodeOrden IN (".implode(',', $chunk).")"));
        }
        $unique = []; foreach ($raw as $r) $unique[$r['id']] = $r;
        $services = [];
        foreach ($unique as $r) {
            $code = trim($r['CodigoSeguimiento']);
            if (!isset($services[$code])) { $services[$code] = $r; $services[$code]['Debe'] = 0; }
            $sale = (float)$services[$code]['Debe'] + max(0, (float)$r['Debe']);
            if ((int)$r['id'] > (int)$services[$code]['id']) $services[$code] = $r;
            $services[$code]['Debe'] = $sale;
        }
        $codes = array_keys($services); $external = []; $cod = [];
        foreach (array_chunk($codes, 700) as $chunk) {
            $in = implode(',', array_map(fn($s) => "'".$this->db->real_escape_string((string)$s)."'", $chunk));
            foreach ($this->rows("SELECT e.CodigoSeguimiento,SUM(IFNULL(e.PrecioPagado,0)+IFNULL(e.CobranzaIntegrada,0)) cost,
                COUNT(*) records,GROUP_CONCAT(DISTINCT COALESCE(u.Usuario,CONCAT('Repartidor ',e.IdEmpleado)) ORDER BY e.IdEmpleado SEPARATOR ' / ') driver
                FROM Externos_rendicion e LEFT JOIN usuarios u ON u.id=e.IdEmpleado WHERE e.CodigoSeguimiento IN ($in) GROUP BY e.CodigoSeguimiento") as $r) $external[$r['CodigoSeguimiento']] = $r;
            foreach ($this->rows("SELECT NumPedido,SUM(Total) sale FROM Ventas WHERE Eliminado=0 AND not_invoice=1 AND surrender_number<>0 AND NumPedido IN ($in) GROUP BY NumPedido") as $r) $cod[$r['NumPedido']] = (float)$r['sale'];
        }
        $charges = [];
        foreach (array_chunk(array_map(fn($o)=>(int)$o['id'], array_values($byOrder)), 500) as $chunk) {
            foreach ($this->rows('SELECT idLogistica,SUM(Debe) sale FROM Ctasctes WHERE Eliminado=0 AND FacturacionxRecorrido=1 AND idLogistica IN ('.implode(',', $chunk).') GROUP BY idLogistica') as $r) $charges[$r['idLogistica']] = (float)$r['sale'];
        }
        $clients = []; $clientNames = [];
        foreach ($this->rows('SELECT id,nombrecliente FROM Clientes') as $c) {
            $clients[$c['id']] = $c['nombrecliente'];
            $nameKey = self::nameKey((string)$c['nombrecliente']);
            if ($nameKey !== '') $clientNames[$nameKey][] = $c['id'];
        }
        $pools = [];
        foreach ($services as $code=>$s) {
            $key = (string)$s['NumerodeOrden'];
            $pools[$key]['codes'][] = $code;
            if ((float)$s['Debe'] == 0) $pools[$key]['unpriced'][] = $code;
            $pools[$key]['direct'] = ($pools[$key]['direct'] ?? 0) + (float)($external[$code]['cost'] ?? 0);
            if (!isset($external[$code])) $pools[$key]['uncovered'][] = $code;
        }
        $facts = []; $invalidDates=0; $overlap=0;
        foreach ($services as $code=>$s) {
            if (empty($s['Fecha']) || $s['Fecha']==='0000-00-00') $invalidDates++;
            if ((int)$s['Entregado']===1 && (int)$s['Devuelto']===1) $overlap++;
            $key = (string)$s['NumerodeOrden']; $o = $byOrder[$key] ?? null; $pool = $pools[$key] ?? [];
            $clientId = $s['FormaDePago']==='Origen' ? $s['idClienteOrigen'] : $s['idClienteDestino'];
            $clientName = $s['FormaDePago']==='Origen' ? $s['RazonSocial'] : $s['ClienteDestino'];
            $clientLabel = self::clientLabel($clientId, (string)$clientName, $clients, $clientNames);
            $routeSale = $o ? (($charges[$o['id']] ?? 0)>0 ? $charges[$o['id']] : (float)$o['PrecioRecorrido']) : 0;
            $routeShare = (float)$s['Debe']==0 && count($pool['unpriced'] ?? []) ? $routeSale / count($pool['unpriced']) : 0;
            $isExternal = isset($external[$code]);
            $own = $o && $o['Aliados'] !== null && (int)$o['Aliados'] === 0;
            $orderCost = $o ? ($own ? (float)$o['CostoKmTotalImputado'] : (float)$o['Costo_rendicion']) : 0;
            $residual = max(0, $orderCost - ($pool['direct'] ?? 0));
            $costShare = !$isExternal && count($pool['uncovered'] ?? []) ? $residual / count($pool['uncovered']) : 0;
            $cost = $isExternal ? (float)$external[$code]['cost'] : $costShare;
            // Zero-valued records do not establish economic cost coverage.
            $covered = $cost > 0;
            $meli = preg_match('/^4[0-9]{10}$/', (string)$s['shipments_id']) || preg_match('/^4[0-9]{10}$/', (string)$s['CodigoProveedor']);
            $origin = $s['Origen'] ?? '';
            $integration = $origin==='API_TIENDANUBE' ? 'Tienda Nube' : ($meli ? 'Mercado Libre' : ($origin ? $origin : 'Sin identificar'));
            $km = $o && (float)$o['KilometrosRecorridos']>0 ? (float)$o['KilometrosRecorridos']/count($pool['codes']) : 0;
            $facts[] = [
                'date'=>(string)$s['Fecha'], 'code'=>(string)$code, 'client'=>$clientLabel,
                'driver'=>$external[$code]['driver'] ?? (($o['NombreChofer'] ?? '') ?: 'Sin repartidor'),
                'fleet'=>$isExternal ? 'Externo' : ($o && $o['Aliados']!==null ? ($own ? 'Propio' : 'Externo') : 'Sin identificar'),
                'service'=>$routeShare>0 ? 'Recorrido contratado' : ((int)$s['Flex']===1 ? 'Flex' : 'Simple'),
                'route'=>$o ? ($o['RutaNombre'] ?: 'Recorrido '.$o['Recorrido']) : 'Sin ruta vinculada',
                'trip'=>$o ? 'Orden '.$o['NumerodeOrden'] : 'Sin orden vinculada',
                'zone'=>trim($s['LocalidadDestino'] ?: 'Sin localidad'), 'province'=>trim($s['ProvinciaDestino'] ?: 'Sin provincia'),
                'integration'=>$integration, 'sale'=>((float)$s['Debe']+$routeShare)/1.21+($cod[$code]??0),
                'gross'=>(float)$s['Debe']+$routeShare, 'cost'=>$cost,'covered'=>$covered?1:0,'services'=>1,
                'delivered'=>(int)$s['Entregado']===1?1:0,'returned'=>(int)$s['Devuelto']===1?1:0,
                'km'=>$km,'km_services'=>$km>0?1:0,'weight'=>max(0,(float)$s['Peso']),
                'visits'=>max(0,(int)$s['Visitas']),'invoiced'=>(int)$s['Facturado']===1?1:0,
                'allocation'=>$routeShare>0 || $costShare>0, 'cost_source'=>$isExternal?'Rendición externa':($costShare>0?($own?'Costo/km imputado':'Rendición de orden prorrateada'):'Sin costo registrado')
            ];
        }
        foreach ($byOrder as $key=>$o) {
            $pool = $pools[$key] ?? [];
            // Orders with no unpriced shipments retain their sale as a separate fact. No sale disappears.
            $sale = (($charges[$o['id']]??0)>0 ? $charges[$o['id']] : (float)$o['PrecioRecorrido']);
            $remainingSale = empty($pool['unpriced']) ? $sale : 0;
            $own = $o['Aliados']!==null && (int)$o['Aliados']===0;
            $remainingCost = empty($pool['uncovered']) ? max(0,(float)($own ? $o['CostoKmTotalImputado'] : $o['Costo_rendicion']) - ($pool['direct'] ?? 0)) : 0;
            $remainingKm = empty($pool['codes']) ? max(0,(float)$o['KilometrosRecorridos']) : 0;
            if ($remainingSale==0 && $remainingCost==0 && $remainingKm==0) continue;
            $clientId = $o['Cliente'] ?: $o['RutaCliente'];
            $orderClientName = !is_numeric($clientId) ? (string)$clientId : '';
            if ($orderClientName === 'Seleccionar un Cliente') { $clientId=$o['RutaCliente']; $orderClientName=''; }
            $facts[] = ['date'=>(string)$o['Fecha'],'code'=>'','client'=>self::clientLabel($clientId, $orderClientName, $clients, $clientNames),
                'driver'=>$o['NombreChofer'] ?: 'Sin repartidor','fleet'=>$o['Aliados']===null?'Sin identificar':($own?'Propio':'Externo'),
                'service'=>'Recorrido contratado','route'=>$o['RutaNombre'] ?: 'Recorrido '.$o['Recorrido'],'trip'=>'Orden '.$o['NumerodeOrden'],
                'zone'=>$o['RutaZona'] ?: 'Sin zona','province'=>'Sin provincia','integration'=>'No aplica (orden)',
                'sale'=>$remainingSale/1.21,'gross'=>$remainingSale,'cost'=>$remainingCost,'covered'=>0,'services'=>0,'delivered'=>0,'returned'=>0,
                'km'=>$remainingKm,'km_services'=>0,'weight'=>0,'visits'=>0,'invoiced'=>0,'allocation'=>false,
                'cost_source'=>$remainingCost>0?'Costo residual de orden':'Sin costo registrado'];
        }
        if ($invalidDates) $this->warnings[] = $invalidDates.' servicios vinculados no tienen fecha válida: participan del prorrateo de su orden pero no se asignan a un mes.';
        if ($overlap) $this->warnings[] = $overlap.' servicios del conjunto consultado tienen Entregado y Devuelto activos a la vez: esos indicadores pueden superponerse.';
        $expenses = $this->rows("SELECT DATE_FORMAT(t.Fecha,'%Y-%m') month,t.Cuenta account,MAX(t.NombreCuenta) name,SUM(t.Debe) amount
            FROM Tesoreria t JOIN (SELECT DISTINCT Cuenta FROM PlanDeCuentas WHERE MuestraGastos=1) p ON p.Cuenta=t.Cuenta
            WHERE t.Eliminado=0 AND t.NoOperativo=0 AND t.Fecha>='$start' AND t.Fecha<'$end' GROUP BY month,t.Cuenta");
        $extraExists = $this->rows("SHOW TABLES LIKE 'GastosExtras'");
        if ($extraExists) {
            $extra = $this->rows("SELECT DATE_FORMAT(Fecha,'%Y-%m') month,'EXTRAS' account,'Gastos extras' name,SUM(Importe) amount FROM GastosExtras WHERE Eliminado=0 AND Fecha>='$start' AND Fecha<'$end' GROUP BY month");
            $expenses = array_merge($expenses,$extra);
        } else $this->warnings[] = 'GastosExtras no está disponible en esta base; gastos incluye únicamente Tesorería.';
        return ['facts'=>$facts,'expenses'=>$expenses,'warnings'=>array_values($this->warnings),'start'=>$start,'end'=>$end];
    }
    private static function nameKey(string $name): string {
        return mb_strtolower(trim(preg_replace('/\s+/u',' ',$name)), 'UTF-8');
    }
    public static function clientLabel($id, string $name, array $clients, array $nameIds): string {
        if (is_numeric($id) && (int)$id>0 && isset($clients[$id])) return $clients[$id].' · '.$id;
        $matches=$nameIds[self::nameKey($name)] ?? [];
        // Legacy shipments often have client ID 0. An exact, unambiguous master-data name
        // may identify them; ambiguous/unknown names remain explicitly without an ID.
        if (count($matches)===1) { $matched=$matches[0]; return $clients[$matched].' · '.$matched; }
        return (trim($name) ?: 'Sin cliente').' · Sin ID';
    }
    public static function metric(array $facts): array {
        $m = array_fill_keys(['sale','gross','cost','services','covered','delivered','returned','km','km_services','weight','visits','invoiced','covered_sale','covered_cost','km_sale','km_cost','linked_services'],0);
        $trips=[]; $clients=[];
        foreach ($facts as $f) {
            foreach (['sale','gross','cost','services','covered','delivered','returned','km','km_services','weight','visits','invoiced'] as $k) $m[$k]+=$f[$k];
            if ($f['covered']) { $m['covered_sale']+=$f['sale']; $m['covered_cost']+=$f['cost']; }
            if ($f['km']>0) { $m['km_sale']+=$f['sale']; $m['km_cost']+=$f['cost']; }
            if ($f['trip']!=='Sin orden vinculada') $m['linked_services']+=$f['services'];
            if ($f['trip']!=='Sin orden vinculada') $trips[$f['trip']]=true;
            $clients[$f['client']]=true;
        }
        $m['contribution']=$m['sale']-$m['cost'];
        $m['covered_margin']=$m['covered_sale']>0?100*($m['covered_sale']-$m['covered_cost'])/$m['covered_sale']:null;
        $m['margin']=$m['sale']>0?100*$m['contribution']/$m['sale']:null;
        $m['incidence']=$m['sale']>0?100*$m['cost']/$m['sale']:null;
        $m['coverage']=$m['services']>0?100*$m['covered']/$m['services']:null;
        $m['ticket']=$m['services']>0?$m['sale']/$m['services']:null;
        $m['cost_service']=$m['services']>0?$m['cost']/$m['services']:null;
        $m['sale_km']=$m['km']>0?$m['km_sale']/$m['km']:null;
        $m['cost_km']=$m['km']>0?$m['km_cost']/$m['km']:null;
        $m['trips']=count($trips); $m['clients']=count($clients);
        return $m;
    }
    public static function report(array $data,string $month,array $filters=[]): array {
        $dimensions=['client','driver','fleet','service','route','trip','zone','province','integration'];
        $options=[]; foreach ($dimensions as $d) $options[$d]=[];
        $selected=[]; $filtered=[];
        foreach ($data['facts'] as $f) {
            if (substr($f['date'],0,7)===$month) foreach ($dimensions as $d) $options[$d][$f[$d]]=true;
            $pass=true; foreach ($dimensions as $d) if (($filters[$d]??'')!=='' && $filters[$d]!==$f[$d]) {$pass=false;break;}
            if (!$pass) continue;
            $filtered[]=$f;
            if (substr($f['date'],0,7)===$month) $selected[]=$f;
        }
        foreach ($dimensions as $d) {$options[$d]=array_keys($options[$d]);sort($options[$d],SORT_NATURAL|SORT_FLAG_CASE);}
        $months=[]; $cursor=new DateTimeImmutable($month.'-01');
        for($i=11;$i>=0;$i--) {
            $key=$cursor->modify("-$i months")->format('Y-m');
            $months[$key]=['month'=>$key]+self::metric(array_values(array_filter($filtered,fn($f)=>substr($f['date'],0,7)===$key)));
        }
        $groups=[];
        foreach ($dimensions as $d) {
            $buckets=[];foreach($selected as $f) $buckets[$f[$d]][]=$f;
            $groups[$d]=[];foreach($buckets as $name=>$rows) $groups[$d][]=['name'=>$name]+self::metric($rows);
            usort($groups[$d],fn($a,$b)=>$b['contribution']<=>$a['contribution']);
        }
        $daily=[]; foreach($selected as $f) $daily[$f['date']][]=$f;
        $days=[]; $n=(int)$cursor->format('t');for($i=1;$i<=$n;$i++) {$date=$month.'-'.str_pad((string)$i,2,'0',STR_PAD_LEFT);$days[]=['date'=>$date]+self::metric($daily[$date]??[]);}
        $expenseRows=array_values(array_filter($data['expenses'],fn($e)=>$e['month']===$month));
        usort($expenseRows,fn($a,$b)=>(float)$b['amount']<=>(float)$a['amount']);
        $total=self::metric($selected);
        $previous=$months[$cursor->modify('-1 month')->format('Y-m')];
        $global=self::metric(array_values(array_filter($data['facts'],fn($f)=>substr($f['date'],0,7)===$month)));
        $global['expenses']=array_sum(array_column($expenseRows,'amount'));
        // Home uses gross service sales. This is a separate reconciliation, not an extra deduction
        // from contribution: Tesoreria may already contain the freight payments.
        $global['home_result']=$global['gross']-$global['expenses'];
        return ['ok'=>true,'month'=>$month,'total'=>$total,'previous'=>$previous,'trend'=>array_values($months),'daily'=>$days,
            'groups'=>$groups,'options'=>$options,'expenses'=>$expenseRows,'global'=>$global,'warnings'=>$data['warnings'],
            'filtered'=>(bool)array_filter($filters,fn($v)=>$v!==''),'generated_at'=>date(DATE_ATOM)];
    }
}
