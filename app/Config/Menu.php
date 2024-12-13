<?php

namespace Config;

class Menu
{
    public $side_menubar = [
        'dashboard' => [
            'header_label'  => 'Dashboard',
            'icon'          => 'fas fa-home',
            'admin' => [
                'label'      => 'Admin',
                'url'        => 'dashboard/admin',
                'permission' => ['Module.View.DashboarAdmin', 'child' => ['Create', 'Update', 'Delete']],
            ],
            
            'vendor' => [
                'label'      => 'Vendor',
                'url'        => 'dashboard/vendor',
                'permission' => ['Module.View.DashboarVendor', 'child' => ['Create', 'Update', 'Delete']],
            ],
            
            'maj' => [
                'label'      => 'MAJ',
                'url'        => 'dashboard/maj',
                'permission' => ['Module.View.DashboarMaj', 'child' => ['Create', 'Update', 'Delete']],
            ],
        ],

        'user_management' => [
            'header_label'  => 'Authorization',
            'icon'          => 'fa fa-users',
            'manage_user' => [
                'label'      => 'Hak Akses',
                'url'        => 'auth/set-permission',
                'permission' => ['Module.View.ManageUser', 'child' => ['Create', 'Update', 'Delete']],
            ],
        ],
        'transaction' => [
            'header_label'  => 'Transaction',
            'icon'          => 'fa fa-tasks',
            // 'goods_receipt_vendor' => [
            //     'label'      => 'Goods Receipt (Vendor)',
            //     'url'        => 'invoicing/gr',
            //     'permission' => ['Module.View.GoodsReceipt', 'child' => ['Create', 'Update', 'Delete']],
            // ],

            // 'data_track' => [
            //     'label'      => 'Data Process (Vendor)',
            //     'url'        => 'invoicing/datatrack',
            //     'permission' => ['Module.View.TrackData', 'child' => ['Create', 'Update', 'Delete']],
            // ],

            // 'dropbox_track' => [
            //     'label'      => 'Dropbox Process (Vendor)',
            //     'url'        => 'invoicing/dropbox-track-vendor',
            //     'permission' => ['Module.View.TrackDropbox', 'child' => ['Create', 'Update', 'Delete']],
            // ],

            'scan_pud' => [
                'label'      => 'Scan PUD',
                'url'        => 'transaction/scan-pud',
                'permission' => ['Module.View.ScanPud', 'child' => ['Create', 'Update', 'Delete']],
            ],

            'scan_finance' => [
                'label'      => 'Scan Finance',
                'url'        => 'transaction/scan-finance',
                'permission' => ['Module.View.ScanFinance', 'child' => ['Create', 'Update', 'Delete']],
            ],

            'approve_pending' => [
                'label'      => 'Pending Dropbox',
                'url'        => 'transaction/pending-approve',
                'permission' => ['Module.View.PendingDropbox', 'child' => ['Create', 'Update', 'Delete']],
            ],

            'waiting_pending' => [
                'label'      => 'Waiting Dropbox',
                'url'        => 'transaction/waiting-approve',
                'permission' => ['Module.View.WaitingDropbox', 'child' => ['Create', 'Update', 'Delete']],
            ],

            'cancel_invoice' => [
                'label'      => 'Cancel Invoice',
                'url'        => 'transaction/cancel-invoice',
                'permission' => ['Module.View.CancelInvoice', 'child' => ['Create', 'Update', 'Delete']],
            ],

            'create_invoicing' => [
                'label'      => 'Create Invoice',
                'url'        => 'invoicing/vendor-verify',
                'permission' => ['Module.View.Invoice', 'child' => ['Create', 'Update', 'Delete']],
            ], 

            'invoicing_non_pkp' => [
                'label'      => 'Invoice Non PKP',
                'url'        => 'invoicingnpkp/invoice-non-pkp',
                'permission' => ['Module.View.InvoiceNonPkp', 'child' => ['Create', 'Update', 'Delete']],
            ],

            'invoicing_mgl' => [
                'label'      => 'Transfer Inventory',
                'url'        => 'invoicingmgl/invoice-mgl',
                'permission' => ['Module.View.InvoiceMgl', 'child' => ['Create', 'Update', 'Delete']],
            ],

            'registration_dropbox' => [
                'label'      => 'Registration Dropbox',
                'url'        => 'dropbox/regris',
                'permission' => ['Module.View.RegistrationDropbox', 'child' => ['Create', 'Update', 'Delete']],
            ],

            'registration_dropbox_npkp' => [
                'label'      => 'Registration Dropbox NPKP',
                'url'        => 'dropbox/npkp-regris',
                'permission' => ['Module.View.RegistrationDropboxNpkp', 'child' => ['Create', 'Update', 'Delete']],
            ],

            'registration_dropbox_mgl' => [
                'label'      => 'Registration Dropbox MGL',
                'url'        => 'dropbox/mgl-regris',
                'permission' => ['Module.View.RegistrationDropboxMgl', 'child' => ['Create', 'Update', 'Delete']],
            ],

            'receivable' => [
                'label'      => 'Receivable',
                'url'        => 'invoicing/receiving',
                'permission' => ['Module.View.Receivable', 'child' => ['Create', 'Update', 'Delete']],
            ],

            'dokument_ok' => [
                'label'      => 'Document OK',
                'url'        => 'invoicing/dokumen-ok',
                'permission' => ['Module.View.DocumentOK', 'child' => ['Create', 'Update', 'Delete']],
            ],

            'on_hold' => [
                'label'      => 'On Hold',
                'url'        => 'invoicing/on-hold',
                'permission' => ['Module.View.Onhold', 'child' => ['Create', 'Update', 'Delete']],
            ],

            'print_invoice' => [
                'label'      => 'Print Invoice Verifikasi',
                'url'        => 'invoicing/InvoiceAfterDokOk',
                'permission' => ['Module.View.PrintInvoice', 'child' => ['Create', 'Update', 'Delete']],
            ],

            'register_in' => [
                'label'      => 'Register IN',
                'url'        => 'invoicing/register-in',
                'permission' => ['Module.View.RegisterIn', 'child' => ['Create', 'Update', 'Delete']],
            ],

            'miro' => [
                'label'      => 'Miro',
                'url'        => 'invoicing/miro',
                'permission' => ['Module.View.Miro', 'child' => ['Create', 'Update', 'Delete']],
            ],
            
            'paid' => [
                'label'      => 'Paid',
                'url'        => 'invoicing/paid',
                'permission' => ['Module.View.Paid', 'child' => ['Create', 'Update', 'Delete']],
            ],
        ],
        'reports' => [
            'header_label'  => 'Report',
            'icon'          => 'fa fa-file',
            'report_data' => [
                'label'      => 'Report Data',
                'url'        => 'report/final-report',
                'permission' => ['Module.View.ReportData', 'child' => ['Create', 'Update', 'Delete']],
            ],

            'goods_receipt_maj' => [
                'label'      => 'Goods Receipt',
                'url'        => 'invoicing/majgr',
                'permission' => ['Module.View.GoodsReceiptMaj', 'child' => ['Create', 'Update', 'Delete']],
            ],

            'data_track_maj' => [
                'label'      => 'Data Process',
                'url'        => 'invoicing/majdatatrack',
                'permission' => ['Module.View.MajTrackData', 'child' => ['Create', 'Update', 'Delete']],
            ],
            
            'dropbox_track_maj' => [
                'label'      => 'Dropbox Process',
                'url'        => 'invoicing/dropbox-track-maj',
                'permission' => ['Module.View.MajTrackDropbox', 'child' => ['Create', 'Update', 'Delete']],
            ],

            'arsip_pdf' => [
                'label'      => 'Arsip PDF',
                'url'        => 'report/arsip-pdf',
                'permission' => ['Module.View.ArsipPdf', 'child' => ['Create', 'Update', 'Delete']],
            ],
        ],
        'print_dokumen' => [
            'header_label'  => 'Print Dokumen',
            'icon'          => 'fa fa-print',
            'print_dropbox' => [
                'label'      => 'Print Dropbox',
                'url'        => 'dropbox/reprint',
                'permission' => ['Module.View.PrintDropbox', 'child' => ['Create', 'Update', 'Delete']],
            ],
            'print_dropbox_npkp' => [
                'label'      => 'Print Dropbox NPKP',
                'url'        => 'dropbox/npkp-reprint',
                'permission' => ['Module.View.PrintDropboxNpkp', 'child' => ['Create', 'Update', 'Delete']],
            ],
            'print_dropbox_mgl' => [
                'label'      => 'Print Dropbox MGL',
                'url'        => 'dropbox/mgl-reprint',
                'permission' => ['Module.View.PrintDropboxMgl', 'child' => ['Create', 'Update', 'Delete']],
            ],
        ],
    ];
}
