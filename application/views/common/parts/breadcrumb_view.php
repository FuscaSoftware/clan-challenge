<?php
/**
 * User: sbraun
 * Date: 04.07.18
 * Time: 10:24
 */
$view_data['page_title'] = (@$view_data['page_title']) ?: (@$page_title) ?: "Dashboard";
$view_data['breadcrumb'] = $view_data['breadcrumb'] ?? [];
?>
<div class="page-breadcrumb">
    <div class="row">
        <div class="col-12 d-flex no-block align-items-center">
            <h4 class="page-title"><?= $view_data['page_title'] ?></h4>
            <?php /*
			<div class="ml-auto text-right">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <?php foreach ($view_data['breadcrumb'] as $bc): ?>
                            <li class="breadcrumb-item"><a href="<?= $bc['href'] ?>"><?= $bc['label'] ?></a></li>
                        <?php endforeach; ?>
                        <li class="breadcrumb-item active" aria-current="page">Library</li>
                    </ol>
                </nav>
            </div>
 			*/ ?>
        </div>
    </div>
</div>
