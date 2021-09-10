<?php
/**
 * User: sbraun
 * Date: 05.11.18
 * Time: 11:57
 */
?>
<!-- ============================================================== -->
<!-- User profile and search -->
<!-- ============================================================== -->
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark pro-pic" href="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <img src="<?= $base ?>/assets/images/users/1.jpg" alt="user" class="rounded-circle" width="31">
    </a>
    <div class="dropdown-menu dropdown-menu-right user-dd animated">
        <? # ci()->dump($_SESSION) ?>
        <?php if (ci()->config->item('use_ldap') && is_object(ci()->acl_lib)) { ?>
            <a class="dropdown-item" href="<?= site_url('User/show_profile/' . ci()->acl_lib->get_user_id()) ?>"><i class="ti-user m-r-5 m-l-5"></i>
                My Profile</a>
            <!--        <a class="dropdown-item" href="javascript:void(0)"><i class="ti-wallet m-r-5 m-l-5"></i> My Balance</a>-->
            <!--        <a class="dropdown-item" href="javascript:void(0)"><i class="ti-email m-r-5 m-l-5"></i> Inbox</a>-->
            <div class="dropdown-divider"></div>
            <!--        <a class="dropdown-item" href="javascript:void(0)"><i class="ti-settings m-r-5 m-l-5"></i> Account Settings</a>-->
            <a class="dropdown-item" href="<?= site_url('User/index') ?>"><i class="ti-settings m-r-5 m-l-5"></i> User
                Management </a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="<?= site_url('Server/index') ?>"><i class="ti-settings m-r-5 m-l-5"></i>
                Server </a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="javascript:location = '<?= site_url('login/logout') ?>';"><i class="fa fa-power-off m-r-5 m-l-5"></i>
                Logout</a>
        <?php } else { ?>
            <a class="dropdown-item" href="javascript:void(0)"><i class="fa fa-power-off m-r-5 m-l-5"></i>
                LDAP is off</a>
        <?php } ?>
        <!--        <div class="dropdown-divider"></div>-->
        <!--        <div class="p-l-30 p-10"><a href="javascript:void(0)" class="btn btn-sm btn-success btn-rounded">View Profile</a></div>-->
    </div>
</li>
<!-- ============================================================== -->
<!-- User profile and search -->
<!-- ============================================================== -->