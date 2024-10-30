<h3>Mobile App Converter</h3>

<?php if(self::isLocalHost()){ ?>

<p>Sorry <strong>Mobile App Converter</strong> doesn't support localhost sites.</p>  

<?php } else { ?>

<p>Click "Go To Mobile" to continue to your Mobile Converter panel.</p>
<a href="<?php echo "http://external.tidiogoapp.com/access?privateKey=".self::getPrivateKey(); ?>" class="button button-primary" target="_blank">Go To Mobile</a>

<?php } ?>