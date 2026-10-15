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
    public function load(string $month, bool $includeActivity = true): array {
        $end = (new DateTimeImmutable($month.'-01'))->modify('+1 month')->format('Y-m-d');
        $start = (new DateTimeImmutable($month.'-01'))->modify('-11 months')->format('Y-m-d');
        // month is strictly validated by the endpoint. Escaping also protects direct model callers.
        $start = $this->db->real_escape_string($start); $end = $this->db->real_escape_string($end);
        $columns = array_column($this->rows('SHOW COLUMNS FROM TransClientes'), 'Field');
        $hasOrigin = in_array('Origen', $columns, true);
        if (!$hasOrigin) $this->warnings[] = 'Faltan datos de origen para identificar con certeza todas las integraciones en esta base.';
        $select = 'id,Fecha,CodigoSeguimiento,Debe,Haber,FormaDePago,idClienteOrigen,idClienteDestino,RazonSocial,ClienteDestino,Recorrido,NumerodeOrden,Flex,shipments_id,CodigoProveedor,LocalidadDestino,ProvinciaDestino,Entregado,Devuelto,Cantidad,Peso,Visitas,Facturado';
        if ($hasOrigin) $select .= ',Origen';
        $valid = "Eliminado=0 AND (IFNULL(Debe,0)<>0 OR (IFNULL(Haber,0)=0 AND TRIM(IFNULL(CodigoSeguimiento,''))<>''))";
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
            $code = trim((string)$r['CodigoSeguimiento']);
            if ($code==='') $code='movement:'.$r['id'];
            if (!isset($services[$code])) { $services[$code] = $r; $services[$code]['Debe'] = 0; }
            $sale = (float)$services[$code]['Debe'] + (float)$r['Debe'];
            if ((int)$r['id'] > (int)$services[$code]['id']) $services[$code] = $r;
            $services[$code]['Debe'] = $sale;
        }
        // Identity is always a user ID when available; equal display names must not merge people.
        $driverUsers = []; $driverNames = [];
        foreach ($this->rows("SELECT u.id,u.Usuario,MAX(e.NombreCompleto) full_name,
            MAX(CASE WHEN u.NIVEL=3 OR e.Puesto='Transportista' THEN 1 ELSE 0 END) is_driver
            FROM usuarios u LEFT JOIN Empleados e ON e.Usuario=u.id GROUP BY u.id,u.Usuario") as $u) {
            $driverUsers[$u['id']] = ['key'=>'user:'.$u['id'], 'name'=>trim((string)($u['full_name'] ?: ($u['Usuario'] ?: 'Repartidor'))).' · '.$u['id'], 'is_driver'=>(bool)$u['is_driver']];
            foreach (array_unique([(string)$u['Usuario'], (string)$u['full_name']]) as $name) {
                if (trim($name)!=='') $driverNames[self::nameKey($name)][$u['id']] = $u['id'];
            }
        }
        foreach ($byOrder as $o) {
            if (isset($driverUsers[$o['idUsuarioChofer']])) $driverUsers[$o['idUsuarioChofer']]['is_driver']=true;
        }
        $codes = array_values(array_filter(array_keys($services),fn($code)=>substr((string)$code,0,9)!=='movement:')); $external = []; $cod = [];
        foreach (array_chunk($codes, 700) as $chunk) {
            $in = implode(',', array_map(fn($s) => "'".$this->db->real_escape_string((string)$s)."'", $chunk));
            foreach ($this->rows("SELECT e.CodigoSeguimiento,e.IdEmpleado,
                SUM(IFNULL(e.PrecioPagado,0)+IFNULL(e.CobranzaIntegrada,0)) cost,
                SUM(CASE WHEN e.Rendido=1 THEN IFNULL(e.PrecioPagado,0)+IFNULL(e.CobranzaIntegrada,0) ELSE 0 END) settled_cost,
                COUNT(*) records,MAX(u.Usuario) username
                FROM Externos_rendicion e LEFT JOIN usuarios u ON u.id=e.IdEmpleado
                WHERE e.CodigoSeguimiento IN ($in) GROUP BY e.CodigoSeguimiento,e.IdEmpleado") as $r) {
                $code=$r['CodigoSeguimiento'];
                if (!isset($external[$code])) $external[$code]=['cost'=>0,'people'=>[],'names'=>[]];
                $identity=self::driverIdentity($r['IdEmpleado'], $r['username'] ?? '', $driverUsers, $driverNames);
                if (isset($driverUsers[$r['IdEmpleado']])) $driverUsers[$r['IdEmpleado']]['is_driver']=true;
                $external[$code]['cost']+=(float)$r['cost'];
                $external[$code]['names'][]=$r['username'] ?: 'Repartidor '.$r['IdEmpleado'];
                $external[$code]['people'][]=$identity+['cost'=>(float)$r['cost'],'settled_cost'=>(float)$r['settled_cost'],
                    'pending_cost'=>(float)$r['cost']-(float)$r['settled_cost'],'imputed_cost'=>0,'settlement_applicable'=>true,'fleet'=>'Externo'];
            }
            foreach ($this->rows("SELECT NumPedido,SUM(COALESCE(NULLIF(ImporteNeto,0),IFNULL(Total,0)-IFNULL(Iva1,0)-IFNULL(Iva2,0)-IFNULL(Iva3,0))) sale FROM Ventas WHERE Eliminado=0 AND not_invoice=1 AND NumPedido IN ($in) GROUP BY NumPedido") as $r) $cod[$r['NumPedido']] = (float)$r['sale'];
        }
        foreach ($external as &$e) {sort($e['names'],SORT_NATURAL|SORT_FLAG_CASE);$e['driver']=implode(' / ',array_unique($e['names']));} unset($e);
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
            if (trim((string)$s['CodigoSeguimiento'])==='') continue;
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
            $isShipment=trim((string)$s['CodigoSeguimiento'])!=='';
            $key = (string)$s['NumerodeOrden']; $o = $isShipment ? ($byOrder[$key] ?? null) : null; $pool = $pools[$key] ?? [];
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
            $collection=$cod[$code]??0;
            $sale=(float)$s['Debe']/1.21+$collection;
            $identity=self::driverIdentity($o['idUsuarioChofer'] ?? 0, $o['NombreChofer'] ?? '', $driverUsers, $driverNames);
            $people=$isExternal ? $external[$code]['people'] : [$identity+['cost'=>$cost,'settled_cost'=>0,'pending_cost'=>0,'imputed_cost'=>$cost,'fleet'=>$o && $o['Aliados']!==null ? ($own?'Propio':'Externo'):'Sin identificar']];
            $facts[] = [
                'date'=>(string)$s['Fecha'], 'code'=>$isShipment?(string)$code:'', 'client'=>$clientLabel,
                'driver'=>$external[$code]['driver'] ?? (($o['NombreChofer'] ?? '') ?: 'Sin repartidor'),
                'fleet'=>$isExternal ? 'Externo' : ($o && $o['Aliados']!==null ? ($own ? 'Propio' : 'Externo') : 'Sin identificar'),
                'service'=>$routeShare>0 ? 'Recorrido contratado' : ((int)$s['Flex']===1 ? 'Flex' : 'Simple'),
                'route'=>$o ? ($o['RutaNombre'] ?: 'Recorrido '.$o['Recorrido']) : 'Sin ruta vinculada',
                'trip'=>$o ? 'Orden '.$o['NumerodeOrden'] : 'Sin orden vinculada',
                'zone'=>trim($s['LocalidadDestino'] ?: 'Sin localidad'), 'province'=>trim($s['ProvinciaDestino'] ?: 'Sin provincia'),
                'integration'=>$integration, 'sale'=>$sale,
                'driver_allocations'=>self::allocateDrivers($people,$sale), 'delivery_eligible'=>(int)$s['idClienteDestino']!==18587,
                'gross'=>(float)$s['Debe'], 'cost'=>$cost,'covered'=>$covered&&$isShipment?1:0,'services'=>$isShipment?1:0,
                'sale_simple'=>(int)$s['Flex']!==1?(float)$s['Debe']/1.21:0,'sale_flex'=>(int)$s['Flex']===1?(float)$s['Debe']/1.21:0,'sale_route'=>0,'sale_collection'=>$collection,
                'delivered'=>$isShipment&&(int)$s['Entregado']===1?1:0,'returned'=>$isShipment&&(int)$s['Devuelto']===1?1:0,
                'km'=>$km,'km_services'=>$km>0?1:0,'weight'=>max(0,(float)$s['Peso']),
                'visits'=>max(0,(int)$s['Visitas']),'invoiced'=>$isShipment&&(int)$s['Facturado']===1?1:0,
                'allocation'=>$routeShare>0 || $costShare>0, 'cost_source'=>$isExternal?'Rendición externa':($costShare>0?($own?'Costo/km imputado':'Rendición de orden prorrateada'):'Sin costo registrado')
            ];
            if ($routeShare!=0) {
                $routeFact=$facts[count($facts)-1];
                $routeFact['date']=(string)$o['Fecha'];$routeFact['code']='';$routeFact['service']='Recorrido contratado';
                foreach (['cost','covered','services','delivered','returned','km','km_services','weight','visits','invoiced','sale_simple','sale_flex','sale_collection'] as $k) $routeFact[$k]=0;
                $routeFact['revenue_covered']=$covered;
                $routeFact['km_basis']=$km>0 && substr((string)$o['Fecha'],0,7)===substr((string)$s['Fecha'],0,7);
                $routeFact['sale']=$routeShare/1.21;$routeFact['gross']=$routeShare;$routeFact['sale_route']=$routeShare/1.21;
                $routeFact['driver_allocations']=self::revenueOnlyAllocations(self::allocateDrivers($people,$routeShare/1.21));
                $routeFact['delivery_eligible']=false;$facts[]=$routeFact;
            }
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
            $identity=self::driverIdentity($o['idUsuarioChofer'] ?? 0, $o['NombreChofer'] ?? '', $driverUsers, $driverNames);
            $facts[] = ['driver_allocations'=>self::allocateDrivers([$identity+['cost'=>$remainingCost,'settled_cost'=>0,'pending_cost'=>0,'imputed_cost'=>$remainingCost,'fleet'=>$o['Aliados']===null?'Sin identificar':($own?'Propio':'Externo')]],$remainingSale/1.21),
                'delivery_eligible'=>false,'date'=>(string)$o['Fecha'],'code'=>'','client'=>self::clientLabel($clientId, $orderClientName, $clients, $clientNames),
                'driver'=>$o['NombreChofer'] ?: 'Sin repartidor','fleet'=>$o['Aliados']===null?'Sin identificar':($own?'Propio':'Externo'),
                'service'=>'Recorrido contratado','route'=>$o['RutaNombre'] ?: 'Recorrido '.$o['Recorrido'],'trip'=>'Orden '.$o['NumerodeOrden'],
                'zone'=>$o['RutaZona'] ?: 'Sin zona','province'=>'Sin provincia','integration'=>'No aplica (orden)',
                'sale'=>$remainingSale/1.21,'sale_simple'=>0,'sale_flex'=>0,'sale_route'=>$remainingSale/1.21,'sale_collection'=>0,'gross'=>$remainingSale,'cost'=>$remainingCost,'covered'=>0,'services'=>0,'delivered'=>0,'returned'=>0,
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
            $extra = $this->rows("SELECT DATE_FORMAT(Fecha,'%Y-%m') month,CONCAT('EXTRA-',Categoria) account,CONCAT(Categoria,' (extra)') name,Categoria category,SUM(Importe) amount FROM GastosExtras WHERE Eliminado=0 AND Fecha>='$start' AND Fecha<'$end' GROUP BY month,Categoria");
            $expenses = array_merge($expenses,$extra);
        } else $this->warnings[] = 'El registro de gastos adicionales no está disponible en esta base; se incluye únicamente Tesorería.';
        $activityStart=$this->db->real_escape_string($month.'-01');
        // Timestamp/FechaRendido is deliberately not used as a proxy for a worked day.
        $activity=$includeActivity?$this->rows("SELECT id,CodigoSeguimiento,Fecha,Hora,Usuario,NumerodeOrden,Estado,Entregado,Visitas
            FROM Seguimiento WHERE (Eliminado=0 OR Eliminado IS NULL) AND Fecha>='$activityStart' AND Fecha<'$end'
            AND Estado NOT IN ('Retirado del Cliente','No se pudo Retirar','No se Pudo Retirar')
            AND (Visitas>0 OR Estado IN ('Entregado al Cliente','No se pudo entregar','Rechazado'))
            ORDER BY Fecha,Hora,id"):[];
        foreach ($activity as &$event) {
            $who=self::driverIdentity(0,(string)$event['Usuario'],$driverUsers,$driverNames);
            // An administrator closing a status is not evidence that the administrator delivered it.
            $userId=substr($who['key'],0,5)==='user:' ? substr($who['key'],5) : '';
            if (!$userId || empty($driverUsers[$userId]['is_driver'])) $who=['key'=>'unknown','name'=>'Sin repartidor identificado'];
            $event['driver_identity']=$who;
        } unset($event);
        return ['activity'=>$activity,'facts'=>$facts,'expenses'=>$expenses,'warnings'=>array_values($this->warnings),'start'=>$start,'end'=>$end];
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
    private static function driverIdentity($id, string $name, array $users, array $names): array {
        if ((int)$id>0 && isset($users[$id])) return ['key'=>$users[$id]['key'],'name'=>$users[$id]['name']];
        $matches=array_values($names[self::nameKey($name)] ?? []);
        if (count($matches)===1 && isset($users[$matches[0]])) return ['key'=>$users[$matches[0]]['key'],'name'=>$users[$matches[0]]['name']];
        if ((int)$id>0) return ['key'=>'user:'.$id,'name'=>'Repartidor · '.$id];
        if (trim($name)!=='') return ['key'=>'name:'.self::nameKey($name),'name'=>trim($name).' · Sin ID'];
        return ['key'=>'unknown','name'=>'Sin repartidor identificado'];
    }
    public static function allocateDrivers(array $people, float $sale): array {
        $positiveCost=array_sum(array_map(fn($p)=>max(0,$p['cost']),$people));
        $result=[];$allocated=0;$last=count($people)-1;
        foreach ($people as $i=>$p) {
            $weight=$positiveCost>0?max(0,$p['cost'])/$positiveCost:1/count($people);
            $share=$i===$last?$sale-$allocated:$sale*$weight;
            $result[]=$p+['sale'=>$share,'weight'=>$weight];$allocated+=$share;
        }
        return $result;
    }
    public static function driverPerformance(array $facts, array $activity, string $month): array {
        $rows=[];$selectedCodes=[];$eligible=[];$shared=[];
        $create=function(array $p) use (&$rows): void {
            if (!isset($rows[$p['key']])) $rows[$p['key']]=['key'=>$p['key'],'name'=>$p['name'],'sale'=>0,'cost'=>0,'settled_cost'=>0,'pending_cost'=>0,'imputed_cost'=>0,
                'service_weight'=>0,'covered_weight'=>0,'days_set'=>[],'codes_set'=>[],'delivered_set'=>[],'fleets_set'=>[],'attempts'=>0,'shared_services'=>0,'settlement_applicable'=>false];
        };
        foreach ($facts as $f) {
            if ($f['code']!=='') {$selectedCodes[$f['code']]=true;$eligible[$f['code']]=$f['delivery_eligible'] ?? true;}
            $people=$f['driver_allocations'] ?? self::allocateDrivers([['key'=>'name:'.$f['driver'],'name'=>$f['driver'],'fleet'=>$f['fleet'],'cost'=>$f['cost'],'settled_cost'=>0,'pending_cost'=>0,'imputed_cost'=>$f['cost']]],$f['sale']);
            if (count($people)>1 && $f['services']) $shared[$f['code']]=true;
            foreach ($people as $p) {
                $create($p);$row=&$rows[$p['key']];
                foreach (['sale','cost','settled_cost','pending_cost','imputed_cost'] as $key) $row[$key]+=$p[$key];
                $row['service_weight']+=$f['services']*$p['weight'];
                if ($p['cost']>0) $row['covered_weight']+=$f['services']*$p['weight'];
                $row['fleets_set'][$p['fleet']]=true;
                $row['settlement_applicable']=$row['settlement_applicable'] || !empty($p['settlement_applicable']);
                if (count($people)>1 && $f['services']) $row['shared_services']++;
                unset($row);
            }
        }
        // One management per person/code/day/trip, not one per status log row.
        $visits=[];
        foreach ($activity as $event) {
            $code=(string)$event['CodigoSeguimiento'];
            if (!isset($selectedCodes[$code]) || empty($eligible[$code]) || substr((string)$event['Fecha'],0,7)!==$month || in_array(mb_strtolower((string)$event['Estado'],'UTF-8'),['retirado del cliente','no se pudo retirar'],true)) continue;
            $who=$event['driver_identity'] ?? ['key'=>'unknown','name'=>'Sin repartidor identificado'];
            $key=json_encode([$who['key'],$code,$event['Fecha'],(string)$event['NumerodeOrden']]);
            $event['_who']=$who;
            $stamp=$event['Fecha'].' '.($event['Hora'] ?? '').' '.str_pad((string)$event['id'],20,'0',STR_PAD_LEFT);
            $event['_stamp']=$stamp;
            if (!isset($visits[$key]) || $stamp>$visits[$key]['_stamp']) $visits[$key]=$event;
        }
        $deliveries=[];
        foreach ($visits as $event) {
            $who=$event['_who'];$create($who);$row=&$rows[$who['key']];
            $row['attempts']++;$row['days_set'][$event['Fecha']]=true;$row['codes_set'][$event['CodigoSeguimiento']]=true;
            if ($event['Estado']==='Entregado al Cliente' && (!isset($deliveries[$event['CodigoSeguimiento']]) || $event['_stamp']>$deliveries[$event['CodigoSeguimiento']]['_stamp'])) $deliveries[$event['CodigoSeguimiento']]=$event;
            unset($row);
        }
        foreach ($deliveries as $code=>$event) $rows[$event['_who']['key']]['delivered_set'][$code]=true;
        $result=[];$active=0;$personDays=0;$unassigned=0;
        foreach ($rows as $row) {
            $row['days']=count($row['days_set']);$row['delivered']=count($row['delivered_set']);$row['managed']=count($row['codes_set']);
            $row['fleet']=implode(' / ',array_keys($row['fleets_set'])) ?: 'Sin costo vinculado';
            $row['deliveries_day']=$row['days']>0?$row['delivered']/$row['days']:null;
            $row['effectiveness']=$row['attempts']>0?100*$row['delivered']/$row['attempts']:null;
            $row['contribution']=$row['sale']-$row['cost'];
            $row['margin']=$row['sale']>0?100*$row['contribution']/$row['sale']:null;
            $row['coverage']=$row['service_weight']>0?100*$row['covered_weight']/$row['service_weight']:null;
            $row['cost_delivery']=$row['delivered']>0?$row['cost']/$row['delivered']:null;
            if ($row['key']==='unknown') $unassigned=$row['delivered'];
            else if ($row['days']>0) {$active++;$personDays+=$row['days'];}
            unset($row['days_set'],$row['codes_set'],$row['delivered_set'],$row['fleets_set']);
            $result[]=$row;
        }
        usort($result,fn($a,$b)=>($b['delivered']<=>$a['delivered']) ?: ($b['contribution']<=>$a['contribution']));
        $assigned=count($deliveries)-$unassigned;
        return ['rows'=>$result,'summary'=>['active_drivers'=>$active,'delivered'=>count($deliveries),'assigned_delivered'=>$assigned,'unassigned_delivered'=>$unassigned,
            'person_days'=>$personDays,'deliveries_day'=>$personDays>0?$assigned/$personDays:null,'settled_cost'=>array_sum(array_column($result,'settled_cost')),
            'shared_services'=>count($shared),'sale'=>array_sum(array_column($result,'sale')),'cost'=>array_sum(array_column($result,'cost'))]];
    }
    private static function revenueOnlyAllocations(array $people): array {
        foreach ($people as &$p) {foreach (['cost','settled_cost','pending_cost','imputed_cost'] as $k) $p[$k]=0;} unset($p);
        return $people;
    }
    /** Shared monthly revenue snapshot for the company result and the operational breakdown. */
    public static function businessSummary(array $data,string $month,array $receipts=[]): array {
        $out=['ok'=>true,'months'=>[],'datos'=>[],'warnings'=>$data['warnings'],'gastos'=>[],'cobrado'=>$receipts,
            'ventas_simples'=>[],'ventas_flex'=>[],'ventas_recorridos'=>[],'ventas_cobranza'=>[],'ventas_total'=>[],
            'basis'=>'servicio-v2','generated_at'=>date(DATE_ATOM)];
        $expenseAccounts=[];$cursor=new DateTimeImmutable($month.'-01');
        $labels=['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
        for($i=11;$i>=0;$i--) {
            $date=$cursor->modify("-$i months");$key=$date->format('Y-m');
            $m=self::metric(array_values(array_filter($data['facts'],fn($f)=>substr($f['date'],0,7)===$key)));
            $m['month']=$key;$m['expenses']=0;$m['provisional']=true;
            foreach($data['expenses'] as $e) {
                if($e['month']!==$key)continue;
                $m['expenses']+=(float)$e['amount'];$account=$e['account'];
                if(!isset($expenseAccounts[$account]))$expenseAccounts[$account]=['cuenta'=>$account,'nombre'=>$e['name'],'grupo'=>$e['category']??''];
                $label=$labels[(int)$date->format('n')-1].'-'.$date->format('y');
                $expenseAccounts[$account][$label]=($expenseAccounts[$account][$label]??0)+(float)$e['amount'];
            }
            $m['result']=$m['sale']-$m['expenses'];$out['months'][]=$m;
            foreach(['simples'=>'sale_simple','flex'=>'sale_flex','recorridos'=>'sale_route','cobranza'=>'sale_collection'] as $name=>$field)$out['ventas_'.$name][$key]=$m[$field];
            $out['ventas_total'][$key]=$m['sale'];$out['gastos'][$key]=$m['expenses'];
        }
        $out['datos']=array_values($expenseAccounts);
        return $out;
    }
    public static function metric(array $facts): array {
        $m = array_fill_keys(['sale','gross','cost','services','covered','delivered','returned','km','km_services','weight','visits','invoiced','sale_simple','sale_flex','sale_route','sale_collection','covered_sale','covered_cost','km_sale','km_cost','linked_services'],0);
        $trips=[]; $clients=[];
        foreach ($facts as $f) {
            foreach (['sale','gross','cost','services','covered','delivered','returned','km','km_services','weight','visits','invoiced'] as $k) $m[$k]+=$f[$k];
            foreach (['sale_simple','sale_flex','sale_route','sale_collection'] as $k) $m[$k]+=$f[$k]??0;
            if ($f['covered'] || !empty($f['revenue_covered'])) { $m['covered_sale']+=$f['sale']; $m['covered_cost']+=$f['cost']; }
            if ($f['km']>0 || !empty($f['km_basis'])) { $m['km_sale']+=$f['sale']; $m['km_cost']+=$f['cost']; }
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
        $global['home_result']=$global['sale']-$global['expenses'];
        return ['ok'=>true,'month'=>$month,'total'=>$total,'previous'=>$previous,'trend'=>array_values($months),'daily'=>$days,
            'driver_performance'=>self::driverPerformance($selected,$data['activity']??[],$month),'groups'=>$groups,'options'=>$options,'expenses'=>$expenseRows,'global'=>$global,'warnings'=>$data['warnings'],
            'filtered'=>(bool)array_filter($filters,fn($v)=>$v!==''),'generated_at'=>date(DATE_ATOM)];
    }
}
