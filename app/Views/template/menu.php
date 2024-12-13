<div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="<?= base_url()?>dist/img/default-png.png" class="img-circle" alt="User Image">
        </div>
        <div class="info">
          <a class="d-block">Welcome, <?= $current_user->cp_name?></a>
        </div>
      </div>

      <!-- SidebarSearch Form -->
      <div class="form-inline">
        <div class="input-group" data-widget="sidebar-search">
          <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
          <div class="input-group-append">
            <button class="btn btn-sidebar">
              <i class="fas fa-search fa-fw"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Sidebar Menu -->
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            <?php 
                use Config\Menu;
                $menu = new Menu();
                $side_menubar = $menu->side_menubar;
                $user_permissions = unserialize($permissions);
                $is_admin = $ionAuth->isAdmin();

                function has_permission($user_permissions, $required_permissions, $is_admin, $key = null) {
                    if ($is_admin && $key !== 'dashboard') {
                        return true;
                    }
                    foreach ($required_permissions as $permission) {
                        if (in_array($permission, $user_permissions)) {
                            return true;
                        }
                    }
                    return false;
                }

                foreach ($side_menubar as $key => $menu_item): 
                    $show_menu_item = false;
                    if (isset($menu_item['permission'])) {
                        $show_menu_item = has_permission($user_permissions, $menu_item['permission'], $is_admin, $key);
                    }

                    if (isset($menu_item['header_label'])): 
                        foreach ($menu_item as $submenu_key => $submenu) {
                            if ($submenu_key !== 'header_label' && $submenu_key !== 'icon' && isset($submenu['permission'])) {
                                if (has_permission($user_permissions, $submenu['permission'], $is_admin, $key)) {
                                    $show_menu_item = true;
                                    break;
                                }
                            }
                        }
                    endif;

                    if ($show_menu_item): ?>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon <?= $menu_item['icon'] ?>"></i>
                                <p>
                                    <?= $menu_item['header_label'] ?? '' ?>
                                    <i class="fas fa-angle-left right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                            <?php foreach ($menu_item as $submenu_key => $submenu): ?>
                                <?php if ($submenu_key !== 'header_label' && $submenu_key !== 'icon'): ?>
                                    <?php if (isset($submenu['label']) && isset($submenu['permission']) && has_permission($user_permissions, $submenu['permission'], $is_admin, $key)): ?>
                                        <li class="nav-item">
                                            <a href="<?= base_url() . $submenu['url'] ?>" class="nav-link">
                                                <p><?= $submenu['label'] ?></p>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    <?php if (isset($submenu['header_label'])): ?>
                                        <li class="nav-item">
                                            <a href="#" class="nav-link">
                                                <p><?= $submenu['header_label'] ?><i class="fas fa-angle-left right"></i></p>
                                            </a>
                                            <ul class="nav nav-treeview">
                                                <?php foreach ($submenu as $subsubmenu_key => $subsubmenu): ?>
                                                    <?php if ($subsubmenu_key !== 'header_label' && isset($subsubmenu['label']) && isset($subsubmenu['permission']) && has_permission($user_permissions, $subsubmenu['permission'], $is_admin, $key)): ?>
                                                        <li class="nav-item">
                                                            <a href="<?= base_url() . $subsubmenu['url'] ?>" class="nav-link">
                                                                <p><?= $subsubmenu['label'] ?></p>
                                                            </a>
                                                        </li>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </ul>
                                        </li>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            </ul>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
        </ul>
    </nav>

</div>

<script>
  $(document).ready(function() {
    var currentUrl = window.location.href;

    // Mengaktifkan nav-treeview items
    $('.nav-treeview a').each(function() {
        var linkUrl = $(this).attr('href');
        if (currentUrl.indexOf(linkUrl) !== -1) {
            $(this).addClass('active');
            $(this).closest('.nav-item').addClass('menu-open');
            $(this).closest('.nav-treeview').css('display', 'block');

            // Membuka semua parent nav-items
            $(this).parents('.nav-item').children('.nav-link').addClass('active');
            $(this).parents('.nav-treeview').css('display', 'block');
            $(this).parents('.nav-item').addClass('menu-open');
        }
    });

    // Mengaktifkan top-level nav items
    $('.nav-link').each(function() {
        var linkUrl = $(this).attr('href');
        if (linkUrl && currentUrl.indexOf(linkUrl) !== -1) {
            $(this).addClass('active');
        }
    });
  });
</script>

<style>
  .nav-link.active {
    color: #fff;
    background-color: #007bff;
  }
</style>