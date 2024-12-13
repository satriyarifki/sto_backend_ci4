<?= $this->extend('template/default') ?>

<?= $this->section('content') ?>
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Manage Users</h3>
            </div>
            <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="confirmationModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="confirmationModalLabel">Set User Permission</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body" id="permissionContent"></div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-ban" aria-hidden="true"></i> Cancel</button>
                            <button type="button" class="btn btn-primary" id="btnContinue" data-id=""><i class="fa fa-check-circle" aria-hidden="true"></i> Save</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body" id="userCardBody">
                <div class="col-auto d-flex justify-content-end">
                    <button id="addUser" class="btn btn-info">Add User</button>
                </div>
                <div id="errorContainer"></div>
                    <table id="example" class="display nowrap" style="width:100%">
                        <thead>
                            <tr>
                                <th>Email</th>
                                <th>Contact Person Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-body" id="setPermissionCard" style="display:none;">
                <div id="permissionContent"></div>
            </div>
        </div>
</section>

<style>
    div.dt-processing>div:last-child {
        display: none;
    }

    .card-body {
        padding: 1.25rem;
        background-color: #fff;
        border: 1px solid #ddd;
        border-radius: 0.25rem;
    }
</style>

<script>
$(document).ready(function(){
    $('#successModal').modal('show');
    var menu = <?= json_encode($side_menubar); ?>;
    var table = $('#example').DataTable({
        'processing': true,
        language: {
            'loadingRecords': '&nbsp;',
            lengthMenu: '_MENU_ &nbsp Show',
            search: '<i class="fa fa-search" aria-hidden="true"></i>',
            emptyTable: '<div style="height: 120px" class="d-flex justify-content-center align-items-center"><div class="text-center"><i style="font-size:24px" class="far">&#xf07c;</i><p>No Data</p></div></div>',
            processing: '<div class="overlay"><div class="spinner-border text-primary" role="status"></div></div>',
            paginate: {
                first: "<i style='font-size:18px' class='fas'>&#xf100;</i>",
                last: "<i style='font-size:18px' class='fas'>&#xf101;</i>",
                next: "<i style='font-size:18px' class='fas'>&#xf105;</i>",
                previous: "<i style='font-size:18px' class='fas'>&#xf104;</i>",
            },
        },
        layout: {
            topStart: false,
            topEnd: false,
            bottomStart: 'pageLength',
            bottomEnd: 'search',
            bottom2Start: 'info',
            bottom2End: 'paging',
        },
        "scrollY": "650px",
        "sScrollX": "100%",
        "scrollCollapse": true,
    });

    updateTableContent();

    function updateTableContent() {
        $('#example').DataTable().processing(true);

        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'user-query', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var data = JSON.parse(xhr.responseText);
                if (data.hasOwnProperty('get_user')) {
                    updateTable(data.get_user);
                    $('#example').DataTable().processing(false);
                }
            } else {
                var errorData = JSON.parse(xhr.responseText);
                if (errorData.hasOwnProperty('error')) {
                    displayError(errorData.error);
                    $('#example').DataTable().processing(false);
                }
            }
        };
        var data = JSON.stringify();
        xhr.send(data);
    }
    
    function updateTable(data) {
        var table = $('#example').DataTable();
        var errorContainer = document.getElementById('errorContainer');
        errorContainer.innerHTML = '';
        table.clear().draw();
        data.forEach(function(value) {
            table.row.add([
                value.email,
                value.cp_name,
                '<button class="btn btn-sm btn-primary edit-user" data-id="' + value.id + '">Edit</button>' +
                ' <button class="btn btn-sm btn-warning set-permission" data-id="' + value.id + '" data-cp_name="' + value.cp_name + '">Set Permission</button>'
            ]).draw();
        });
    }

    $('#example').on('click', '.set-permission', function() {
        var userId = $(this).data('id');
        var cpName = $(this).data('cp_name');
        $.ajax({
            url: 'get-permission/' + userId,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    var permissions = response.data.permission;
                    // console.log(menu);
                    var tableHtml = `
                        <h4>Set User Permission (${cpName})</h4>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Menu</th>
                                    <th>View</th>
                                    <th>Create</th>
                                    <th>Update</th>
                                    <th>Delete</th>
                                </tr>
                            </thead>
                            <tbody>
                    `;

                    $.each(menu, function(category, items) {
                        $.each(items, function(key, item) {
                            if (typeof item === 'object' && item.label) {
                                if (item.permission.child && item.permission.child.length > 0) {
                                    //console.log(item.permission.child.indexOf('Create') ? 'Gk ada Create' : 'Create');
                                    var viewPermission = item.permission[0];
                                    var moduleName = viewPermission.split('.')[2];
                                    var createPermission = item.permission.child.includes('Create') ? `Module.Create.${moduleName}` : '';
                                    var updatePermission = item.permission.child.includes('Update') ? `Module.Update.${moduleName}` : '';
                                    var deletePermission = item.permission.child.includes('Delete') ? `Module.Delete.${moduleName}` : '';

                                    tableHtml += `
                                        <tr>
                                            <td>${item.label}</td>
                                            <td><input type="checkbox" name="permissions[]" value="${viewPermission}" ${permissions.includes(viewPermission) ? 'checked' : ''}></td>
                                            <td><input type="checkbox" name="permissions[]" value="${createPermission}" ${permissions.includes(createPermission) ? 'checked' : ''}></td>
                                            <td><input type="checkbox" name="permissions[]" value="${updatePermission}" ${permissions.includes(updatePermission) ? 'checked' : ''}></td>
                                            <td><input type="checkbox" name="permissions[]" value="${deletePermission}" ${permissions.includes(deletePermission) ? 'checked' : ''}></td>
                                        </tr>
                                    `;
                                }
                            }
                        });
                    });
                    tableHtml += `
                            </tbody>
                        </table>
                    `;
                    $('#permissionContent').html(tableHtml);
                    $('#btnContinue').data('id', userId); 
                    $('#confirmationModal').modal('show');
                } else {
                    console.error('Failed to get permissions:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching permissions:', error);
            }
        });
    });

    document.getElementById('btnContinue').addEventListener('click', function() {
        var userId = $(this).data('id');
        var checkedPermissions = [];
        var checkboxes = document.querySelectorAll('input[name="permissions[]"]:checked');
        var Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });

        checkboxes.forEach(function(checkbox) {
            checkedPermissions.push(checkbox.value);
        });

        $.ajax({
            url: '<?= base_url()?>auth/set-right',
            method: 'POST',
            data: { 
                permissions: checkedPermissions,
                userId: userId 
            },
            success: function(response) {
                Toast.fire({
                    icon: 'success',
                    title: response.message,
                })
            },
            error: function(error) {
                console.error(error);
            }
        });
        $('#confirmationModal').modal('hide');
    });

    function displayError(error) {
        var table = $('#example').DataTable();
        var errorContainer = document.getElementById('errorContainer');
        errorContainer.innerHTML = '';
        table.clear().draw();
        if (Array.isArray(error)) {
            error.forEach(function(message) {
                errorContainer.innerHTML += '<div class="alert alert-danger">' + message + '</div>';
            });
        } else {
            errorContainer.innerHTML = '<div class="alert alert-danger">' + error + '</div>';
        }
    }
});

$(function () {
    let table = new DataTable('#example');
});
</script>

<?= $this->endSection() ?>