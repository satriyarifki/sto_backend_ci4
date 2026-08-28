<?php

namespace App\Models;

use CodeIgniter\Model;
use Config\Database;

class M_manifest extends Model
{
    protected $table      = 'emanifest_order';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'manifest_number', 'order_number', 'part_number', 'part_name', 'dock_code', 'kanban_no', 'supplier_name',
        'order_quantity', 'item_number', 'qtyPerContainer', 'orderPlanQtyLot', 'orderMode', 'order_date', 'shipping_date', 'arrival_date', 'plane_code', 'plane_sequence', 'plane_date',
        'supplier_code', 'supplier_plant_code', 'rcv_plant_code', 'fetch_date', 'order_type', 'receiving_plant', 'shipping_dock', 'company_code', 'carFamilyCd', 'reExportCd', 'kanbanPrintAddress',
        'orderReleaseDt', 'buildOutFlag', 'buildOutQty', 'aicoCeptFlag', 'subRouteCd', 'subRouteSeq', 'subRouteDt', 'fstXDocCd', 'fstXPlantCd', 'fstXShippingDockCd', 'fstXDockArrivalDt', 'fstXDockDepartDt',
        'fstXDockRouteGrpCd', 'fstXDockSeqNo', 'fstXRouteDt', 'sndXDocCd', 'sndXPlantCd', 'sndXShippingDockCd', 'sndXDockArrivalDt', 'sndXDockDepartDt', 'sndXDockSeqNo', 'trdXDocCd', 'trdXPlantCd', 'trdXShippingDockCd',
        'trdXDockArrivalDt', 'trdXDockDepartDt', 'trdXDockRouteGrpCd', 'trdXDockSeqNo', 'trdXRouteDt', 'manifestPrintPointCd', 'manifestPrintDatePlant', 'manifestPrintDateLocal', 'kanbanPrintPointCd', 'kanbanPrintDatePlant',
        'kanbanPrintDateLocal', 'conveyanceRoute', 'ferryPortArrivalDt', 'ferryPortDepartDt', 'printFlag', 'printDate', 'receiveFlag', 'receiveDt', 'usageFlag', 'usageDt', 'deletionFlag', 'deletionDt', 'dropStatusFlag', 'dropStatusDt',
        'sapFlag', 'sapDate', 'totalPage', 'ekbsFlag', 'ekbsDt', 'patDpFlag', 'patPlFlag', 'receivePlantName', 'packageType', 'supplierInfo1', 'supplierInfo2', 'supplierInfo3', 'printCd', 'importirInfo', 'importirInfo2', 'importirInfo3',
        'importirInfo4', 'partBarcode', 'modDstCd', 'caseNo', 'plnPckDt', 'boxNo', 'contSno', 'processType', 'lotModNo', 'cfCd', 'receivedQty', 'dockType'
    ];

    protected $dbGroup = 'dbManifest'; 

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::connect('dbManifest'); 
        $this->table = 'emanifest_order';
    }

    public function getInternalManifest($date = null, $orderType = null, $receivingPlant = null)
    {
        // Ambil data parent + total_kanban dan total_scanned_kanban
        $builder = $this->db->table('emanifest_order eo');
        $builder->select('
            eo.manifest_number,
            eo.order_number,
            eo.dock_code,
            eo.supplier_name,
            eo.shipping_date,
            eo.arrival_date,
            eo.supplier_code,
            eo.subRouteCd,
            eo.subRouteSeq,
            COUNT(DISTINCT kl.id) AS total_kanban,
            COUNT(DISTINCT CASE WHEN kl.is_scan = "Y" THEN kl.id END) AS total_scanned_kanban
        ');

        $builder->join('kanban_list kl', 'kl.manifest_number = eo.manifest_number', 'left');

        if (!empty($date)) {
            $builder->where('eo.fetch_date', $date);
        }

        if (!empty($orderType)) {
            $builder->where('eo.order_type', $orderType);
        }

        if (!empty($receivingPlant)) {
            $builder->where('eo.rcv_plant_code', $receivingPlant);
        }

        $builder->groupBy(['eo.manifest_number', 'eo.order_number', 'eo.dock_code']);
        $parentResults = $builder->get()->getResult();

        // Loop data parent
        foreach ($parentResults as &$parent) {
            $items = $this->db->table('emanifest_order')
                ->select('part_number, kanban_no, part_name, order_quantity, item_number')
                ->where('manifest_number', $parent->manifest_number)
                ->where('order_number', $parent->order_number)
                ->get()
                ->getResult();

            $kanbanData = [];

            foreach ($items as $item) {
                // Join ke tabel kanban_list
                $kanbanList = $this->db->table('kanban_list')
                    ->select('kanban_code, kanban_id, seq_number')
                    ->where('manifest_number', $parent->manifest_number)
                    ->where('item_number', $item->item_number)
                    ->orderBy('seq_number', 'asc')
                    ->get()
                    ->getResult();

                if (!empty($kanbanList)) {
                    $item->kanban_list = $kanbanList;
                    $kanbanData[] = $item;
                }
            }

            $parent->kanban_data = $kanbanData;
        }

        return $parentResults;
    }

}
