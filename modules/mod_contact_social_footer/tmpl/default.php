<?php
$sufixo = '';
if ($params->get('moduleclass_sfx')) {
    $sufixo = '-' . $params->get('moduleclass_sfx');
}
?>
<div id="contact-social-footer">

    <div class="phone">
        <?php
        //Telefone
        if (!empty($phone)) : ?>
            <a href="tel:+<?php echo preg_replace("/[^0-9]/", "", $phone); ?>" target="_blank" class="phone">
                <?= $phone ?>
            </a>
        <?php endif; ?>
    </div>

    <div class="email">
        <?php
        //E-mail
        if (!empty($email)) : ?>
            <a href="mailto:<?= $email; ?>" target="_blank" class="email">
                <?= $email; ?>
            </a>
        <?php endif; ?>
    </div>

    <ul class="contact-social-footer footer<?= $params->get('moduleclass_sfx') ?>">

        <?php
        //WhatsApp
        if (!empty($whatsapp)) :
            //Identifica a hora para aparecer o WhatsApp
            date_default_timezone_set('America/Sao_Paulo');
        ?>
            <li>
                <a href="https://api.whatsapp.com/send?phone=+55<?php echo $whatsappNumber; ?><?php if (!empty($whatsapp_msg)) echo '&text=' . $whatsapp_msg; ?>" target="_blank" class="whatsapp-footer">
                    <i class="fab fa-whatsapp-square"></i>
                </a>
            </li>
        <?php endif; ?>

        <?php
        //Twitter
        if (!empty($twitter)) : ?>
            <li>
                <a href="<?php echo $twitter; ?>" target="_blank" class="twitter-footer">
                    <i class="fab fa-twitter-square"></i>
                </a>
            </li>
        <?php endif; ?>

        <?php
        //Facebook
        if (!empty($facebook)) : ?>
            <li>
                <a href="<?php echo $facebook; ?>" target="_blank" class="facebook-footer">
                    <i class="fab fa-facebook-square"></i>
                </a>
            </li>
        <?php endif; ?>

        <?php
        //Youtube
        if (!empty($youtube)) : ?>
            <li>
                <a href="<?php echo $youtube; ?>" target="_blank" class="youtube-footer">
                    <i class="fab fa-youtube-square"></i>
                </a>
            </li>
        <?php endif; ?>

        <?php
        //Instagram
        if (!empty($instagram)) : ?>
            <li>
                <a href="<?php echo $instagram; ?>" target="_blank" class="instagram">
                    <span>
                        <i class="fab fa-instagram-square"></i>
                    </span>
                </a>
            </li>
        <?php endif; ?>

        <?php
        //Linkedin
        if (!empty($linkedin)) : ?>
            <li>
                <a href="<?php echo $linkedin; ?>" target="_blank" class="linkedin">
                    <i class="fab fa-linkedin"></i>
                </a>
            </li>
        <?php endif; ?>

    </ul>
</div>