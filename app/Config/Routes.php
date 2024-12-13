<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Auth::login');

$routes->group('users', ['namespace' => 'App\Controllers'], function ($routes) {
	$routes->get('profile', 'Users::index');
	$routes->get('update', 'Users::update');
	$routes->add('create', 'Users::create');
	$routes->add('stored/(:num)', 'Users::stored/$1');
	$routes->get('upload-user', 'Users::upload_user');
	$routes->add('stored-user', 'Users::stored_user');
	$routes->get('getpart', 'Users::getpart');
	$routes->get('template', 'Users::template');
});

$routes->group('invoicing', ['namespace' => 'App\Controllers'], function ($routes) {
	$routes->get('gr', 'Invoicing::gr_index');
	$routes->get('majgr', 'Invoicing::gr_maj');
	$routes->get('datatrack', 'Invoicing::datatrack');
	$routes->get('majdatatrack', 'Invoicing::majdatatrack');
	$routes->add('track_real', 'Invoicing::track_real');
	$routes->add('track_maj_real', 'Invoicing::track_maj_real');
	$routes->get('dropbox-track-vendor', 'Invoicing::dropboxtrackvendor');
	$routes->add('dropbox-json-vendor', 'Invoicing::dropboxtrackvendor_json');
	$routes->get('dropbox-track-maj', 'Invoicing::dropboxtrackmaj');
	$routes->add('dropbox-json-maj', 'Invoicing::dropboxtrackmaj_json');
	$routes->add('real', 'Invoicing::gr_real');
	$routes->add('realmaj', 'Invoicing::gr_real_maj');
	$routes->get('registration-dropbox', 'Invoicing::reistration_dropbox');
	$routes->get('status/(:any)', 'Invoicing::status_update/$1');
	$routes->get('approve/(:any)', 'Invoicing::gr_approve/$1');
	$routes->get('printgr/(:any)', 'Invoicing::print_gr/$1');
	$routes->get('table', 'Invoicing::table_res');
	$routes->get('verify-transaction', 'Invoicing::verify_transaction');
	$routes->add('verify', 'Invoicing::jsonverify');
	$routes->get('process(:any)', 'Invoicing::process/$1');
	$routes->get('generate-invoice', 'Invoicing::generate_invoice');
	$routes->get('vendor-verify', 'Invoicing::vendor_verify');
	$routes->add('vendor-json-verify', 'Invoicing::vendor_verify_json');
	$routes->get('generate-invoice-json', 'Invoicing::generate_invoice_json');
	$routes->get('invoice', 'Invoicing::invoice');
	$routes->add('invoice-json', 'Invoicing::invoice_json');
	$routes->add('pdf', 'Invoicing::pdf');
	$routes->add('unity', 'Invoicing::unity');
	$routes->add('unity-mgl', 'Invoicing::unity_mgl');
	$routes->add('makeinvoice', 'Invoicing::makeinvoice');
	$routes->add('getreceive', 'Invoicing::getreceive');
	$routes->add('status-dropbox', 'Invoicing::statusDropbox');

	$routes->get('receiving', 'Invoicing::receivingindex');
	$routes->add('approvereceiving', 'Invoicing::approvereceiving');
	$routes->add('received-json', 'Invoicing::receiving_json');
	
	$routes->get('dokumen_ok', 'Invoicing::dokumen_okindex');
	$routes->get('cancel_approve_dok_ok/(:any)', 'Invoicing::cancel_approve_dok_ok/$1');
	$routes->get('approvedok_ok/(:any)', 'Invoicing::approvedok_ok/$1');
	//Dokumen OK Revisi//
	$routes->get('dokumen-ok', 'Invoicing::dokumenok_index');
	$routes->get('dokumen-ok-json', 'Invoicing::queryspesific');
	$routes->add('update-dokument-ok', 'Invoicing::updateDokOk');

	$routes->get('on-hold', 'Invoicing::on_hold');
	$routes->add('on-hold-email', 'Invoicing::onhold_email');
	$routes->add('on-hold-email-v2', 'Invoicing::onhold_email_v2');
	$routes->add('cancel-process', 'Invoicing::cancel_process');
	$routes->get('on-hold-json', 'Invoicing::onhold_json');
	$routes->add('restore-onhold', 'Invoicing::restore_onhold');
	$routes->add('onhold-delete', 'Invoicing::del_onhold');
	$routes->add('cancel-process-from-pud', 'Invoicing::cancel_process_from_pud');

	$routes->get('register-in', 'Invoicing::register_in');
	$routes->add('registerin-json', 'Invoicing::registerin_json');
	$routes->add('registerin-approve', 'Invoicing::registerin_approve');
	$routes->get('miro', 'Invoicing::miroindex');
	$routes->add('miro-json', 'Invoicing::miro_json');
	$routes->add('miro-approve', 'Invoicing::miro_approve');
	$routes->get('cancel_approve_miro/(:any)', 'Invoicing::cancel_approve_miro/$1');
	$routes->get('approvemiro/(:any)', 'Invoicing::approvemiro/$1');
	$routes->get('paid', 'Invoicing::paid');
	$routes->add('paid-json', 'Invoicing::paid_json');
	$routes->add('paid-approve', 'Invoicing::paid_approve');
	$routes->add('printdok_ok', 'Invoicing::printdok_ok');
	$routes->get('printdok_ok_count', 'Invoicing::printdok_ok_count');

	$routes->get('InvoiceAfterDokOk', 'Invoicing::InvoiceAfterDokOkindex');
	$routes->add('delete-pud', 'Invoicing::delete_pud');
	$routes->add('out-pud', 'Invoicing::out_pud');
	$routes->add('document-invoice', 'Invoicing::invoiceAfterDokOk_json');
});

$routes->group('invoicingnpkp', ['namespace' => 'App\Controllers'], function ($routes) {
	$routes->get('invoice-non-pkp', 'InvoicingNonPkp::invoice_non_pkp');
	$routes->get('generate-npkp-qr', 'InvoicingNonPkp::generate_npkp_qr');
	$routes->add('makeinvoice', 'InvoicingNonPkp::makeinvoice');
	$routes->add('makeinvoicemgl', 'InvoicingNonPkp::makeinvoicemgl');
	$routes->get('invoice-mgl', 'InvoicingNonPkp::invoice_mgl');
	$routes->add('vendor-json-verify', 'InvoicingNonPkp::vendor_verify_json');
});

$routes->group('invoicingmgl', ['namespace' => 'App\Controllers'], function ($routes) {
	$routes->get('invoice-mgl', 'InvoicingMgl::invoice_mgl');
	$routes->get('generate-mgl-qr', 'InvoicingMgl::generate_mgl_qr');
	$routes->add('makeinvoice', 'InvoicingMgl::makeinvoice');
	$routes->add('vendor-json-verify', 'InvoicingMgl::vendor_verify_json');
});

$routes->group('rundown', ['namespace' => 'App\Controllers'], function ($routes) {
	$routes->get('/', 'andon\Rundown::index');
	$routes->get('getinvoice', 'andon\Rundown::getInvoices');
});

$routes->group('rundownMiro', ['namespace' => 'App\Controllers'], function ($routes) {
	$routes->get('/', 'andon\Rundown::indexMiro');
	$routes->get('getMiro', 'andon\Rundown::getDataMiro');
});

$routes->group('dashboard', ['namespace' => 'App\Controllers'], function ($routes) {
	$routes->get('vendor', 'Dashboard::sup_index');
	$routes->get('maj', 'Dashboard::cli_index');
	$routes->get('admin', 'Dashboard::admin_index');
	$routes->add('real', 'Dashboard::dash_real');
	$routes->add('dash-admin', 'Dashboard::dash_admin');
	$routes->add('value', 'Dashboard::card_value');
	$routes->add('value-maj', 'Dashboard::card_value_maj');
	$routes->get('vendor-id', 'Dashboard::vendor_id');
	$routes->get('news', 'Dashboard::announcement');
	$routes->get('news/detail/(:num)', 'Dashboard::announcement_detail/$1');
	$routes->get('upload-news', 'Dashboard::upload_announcement');
	$routes->add('uploads-data', 'Dashboard::upload_data');
});

// $routes->group('auth', ['namespace' => 'IonAuth\Controllers'], function ($routes) {
$routes->group('auth', ['namespace' => 'App\Controllers'], function ($routes) {
	$routes->add('login', 'Auth::login');
	$routes->get('logout', 'Auth::logout');
	$routes->add('forgot_password', 'Auth::forgot_password');
	$routes->get('/', 'Auth::index');
	$routes->add('create_user', 'Auth::create_user');
	$routes->add('edit_user/(:num)', 'Auth::edit_user/$1');
	$routes->add('create_group', 'Auth::create_group');
	$routes->get('activate/(:num)', 'Auth::activate/$1');
	$routes->get('activate/(:num)/(:hash)', 'Auth::activate/$1/$2');
	$routes->add('deactivate/(:num)', 'Auth::deactivate/$1');
	$routes->get('reset_password/(:hash)', 'Auth::reset_password/$1');
	$routes->post('reset_password/(:hash)', 'Auth::reset_password/$1');
	$routes->get('data-user', 'Auth::data_user');
	$routes->add('data-user-json', 'Auth::data_user_json');
	$routes->get('set-permission', 'Auth::rightaccess');
	$routes->add('user-query', 'Auth::rightaccess_json');
	$routes->get('get-permission/(:any)', 'Auth::getPermission/$1');
	$routes->add('set-right', 'Auth::setPermission');
	$routes->add('change-password', 'Auth::change_password');
});

$routes->group('transaction', ['namespace' => 'App\Controllers'], function ($routes) {
	$routes->get('scan-pud', 'Transaction::scan_pud');
	$routes->add('process-scanned-pud', 'Transaction::process_scan_pud');
	$routes->get('cancel-invoice', 'Transaction::cancel_verification');
	$routes->get('scan-finance', 'Transaction::scan_finance');
	$routes->add('process-scanned-finance', 'Transaction::process_scan_finance');
	$routes->add('pending-approve', 'Transaction::pending_dropbox_index');
	$routes->add('waiting-approve', 'Transaction::waiting_dropbox_index');
	$routes->add('get-unregister', 'Transaction::get_invoice_unreg');
	$routes->add('cancel-verify-json', 'Transaction::cancel_verify_json');
});

$routes->group('dropbox', ['namespace' => 'App\Controllers'], function ($routes) {
	$routes->get('mgl-regris', 'Dropbox::regris_mgl');
	$routes->add('mgl-regris-json', 'Dropbox::regis_mgl_json');
	$routes->get('npkp-regris', 'Dropbox::regris_npkp');
	$routes->add('npkp-regris-json', 'Dropbox::regis_npkp_json');
	$routes->get('regris', 'Dropbox::regris');
	$routes->add('regis-json', 'Dropbox::regis_json');
	$routes->add('regris/temp', 'Dropbox::temp');
	$routes->get('regris/test', 'Dropbox::test');
	$routes->get('reprint', 'Dropbox::reprint');
	$routes->get('npkp-reprint', 'Dropbox::reprint_npkp');
	$routes->get('mgl-reprint', 'Dropbox::reprint_mgl');
	$routes->add('reprint-json', 'Dropbox::reprint_json');
	$routes->add('npkp-reprint-json', 'Dropbox::reprint_json_npkp');
	$routes->add('mgl-reprint-json', 'Dropbox::reprint_json_mgl');
	$routes->get('dropbox-detail', 'Dropbox::dropbox_detail');
	$routes->get('npkp-dropbox-detail', 'Dropbox::dropbox_detail_npkp');
	$routes->get('mgl-dropbox-detail', 'Dropbox::dropbox_detail_mgl');
	$routes->get('pdf', 'Dropbox::pdf');
	$routes->get('pdfnpkp', 'Dropbox::pdfnpkp');
	$routes->get('pdfmgl', 'Dropbox::pdfmgl');
	$routes->add('datapushdropbox', 'Dropbox::datapushdropbox');
	$routes->add('datapushdropboxnpkp', 'Dropbox::datapushdropboxnpkp');
	$routes->add('datapushdropboxmgl', 'Dropbox::datapushdropboxmgl');
	$routes->add('regris/update', 'Dropbox::updateStatusGenerate');
	$routes->add('reprint/invoice', 'Dropbox::getInvoice');
});

$routes->group('report', ['namespace' => 'App\Controllers'], function ($routes) {
	$routes->get('final-report', 'Report::index');
	$routes->add('report-json', 'Report::report_json');
	$routes->get('arsip-pdf', 'Report::arsip_pdf');
	$routes->add('arsip-pdf-json', 'Report::arsip_pdf_json');
	$routes->get('view-pdf', 'Report::viewPDF');
	$routes->get('view-pdf-invoice', 'Report::viewPDFInvoice');
	$routes->get('view-pdf-invoice-npkp', 'Report::viewPDFInvoiceNpkp');
	$routes->get('view-pdf-invoice-mgl', 'Report::viewPDFInvoiceMgl');
	$routes->get('view-pdf-faktur-pengganti', 'Report::viewPDFakturpengganti');
	$routes->get('export-process', 'Report::export_inprocess');
	$routes->get('export-infinance', 'Report::export_infinance');
});

$routes->group('notification', ['namespace' => 'App\Controllers'], function ($routes) {
	$routes->get('/', 'Notification::getNotifications');
	$routes->add('markAsRead', 'Notification::markAsRead');
});

$routes->group('exception', ['namespace' => 'App\Controllers'], function ($routes) {
	$routes->get('vendor', 'Exception::excep_index');
	$routes->add('makexception', 'Exception::makexception');
	$routes->add('makeinvoicenpkp', 'Exception::makeinvoicenpkp');
	$routes->add('makeinvoicemgl', 'Exception::makeinvoicemgl');
	$routes->get('pdf', 'Exception::pdf');
	$routes->get('get-exception-document', 'Exception::get_exception_document');
	$routes->add('update-exception-dropbox', 'Exception::update_exception_dropbox');
	$routes->add('get-exception-document-vendor', 'Exception::get_exception_document_vendor');
});

$routes->group('indexedstatus', ['namespace' => 'App\Controllers'], function ($routes) {
	$routes->get('receiving-status', 'IndexedStatus::receiving');
	$routes->get('document-status', 'IndexedStatus::document_ok');
});

$routes->group('term-police', ['namespace' => 'App\Controllers'], function ($routes) {
	$routes->get('/', 'PrivacyPolice::index');
	$routes->get('privacy-police', 'PrivacyPolice::justRead');
	$routes->add('accept', 'PrivacyPolice::accept');
});

$routes->get('temp', 'Temp::temp');
$routes->add('temp/upload', 'Temp::uploadPDF');
$routes->get('temp/tampilan', 'Temp::tampilan');
$routes->get('temp/getpart', 'Temp::getPart');
$routes->get('temp/uploadsap', 'Temp::postDataToSap');
$routes->add('users/inspect/(:num)', 'Users::temp/$1');
$routes->get('qrcode', 'ExportQR::generateQRCode');
$routes->get('pdf', 'Pdf::index');
$routes->get('readpng', 'Temp::readpng');
$routes->get('pdftopng', 'Temp::pdftopng');
$routes->get('info', 'Temp::info');
$routes->get('add', 'Temp::addtemp');
$routes->get('notif', 'Temp::notif');
$routes->get('jumlah', 'Temp::testindex');
$routes->get('inner', 'Temp::getreceive');
$routes->get('fixheader', 'Temp::fixheader');
$routes->get('getData', 'Temp::queryspesific');
$routes->add('update-dokument-ok', 'Temp::updateDokOk');
$routes->get('invoice-id', 'Temp::idtest_invoice');
$routes->get('dropbox-id', 'Temp::idtest_dropbox');
$routes->get('serbaguna', 'Temp::serbaguna');

$routes->get('run-python', 'Qrpython::runPythonScript');

$routes->set404Override(function() {
	return view('template/error_404');
});