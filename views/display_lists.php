<?php
/**
 * MailChimp "Display Lists" user view
 *
 * @author CreativeMinds (http://www.cminds.com)
 * @version 1.0
 * @copyright Copyright (c) 2012, CreativeMinds
 * @package MultiMailChimp/Views
 */
?>
<ul class="mmc_list">
    <?php
    foreach ($subscriptionList as $row):
        ?>
        <li class="mmc_list_row" data-id="<?php echo $row['id']; ?>">
            <a class="mmc_button mmc_<?php echo ($row['isSubscribed']) ? 'unfollow' : 'follow'; ?>"></a>
            <div class="mmc_list_label">
                <span class="mmc_list_name"><?php echo $row['name']; ?></span>
                <span class="mmc_list_description"><?php echo $row['description']; ?></span>
            </div>
        </li>
        <?php
    endforeach;
    ?>
</ul>
