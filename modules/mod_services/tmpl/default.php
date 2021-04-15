<?php
$items = $params->get('items');
if (!empty($items)) :
?>
    <div id="modservices">
        <div id="services">

            <?php
            $count = 1;
            foreach ($items as $item) :
                $link = JRoute::_('index.php?Itemid=' . $item->menu);
                $line = ($count%4)?'line-separator': '';
            ?>
                <div class="service <?= $line ?>">
                    <div class="item-service">
                        <a href="<?= $link; ?>" class="img-service">
                            <img src="<?= $item->icon ?>" alt="<?= $item->title; ?>">
                        </a>
                        <h1>
                            <a href="<?= $link; ?>"><?= $item->title; ?></a>
                        </h1>
                    </div>
                </div>
            <?php
                $count++;
            endforeach;
            ?>

        </div>
    </div>
<?php endif; ?>