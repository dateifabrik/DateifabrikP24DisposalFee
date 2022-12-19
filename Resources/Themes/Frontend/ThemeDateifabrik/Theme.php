<?php

namespace Shopware\Themes\ThemeDateifabrik;

class Theme extends \Shopware\Components\Theme
{
    protected $extend = 'Responsive';

    protected $name = 'ThemeDateifabrik';

    protected $description = 'Theme für DateifabrikP24DisposalFee';

    protected $author = 'Dateifabrik';

    protected $license = 'Dateifabrik';


  /** @var array Defines the files which should be compiled by the javascript compressor */
    protected $javascript = array(
      // eigene javascript-Dateien
      // diese werden abgelegt unter _public/ und so eingefügt:
      'src/js/jquery.test.js',
    );

}
