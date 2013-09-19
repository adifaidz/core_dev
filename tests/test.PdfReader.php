<?php

namespace cd;

set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__).'/../core/');

require_once('PdfReader.php');
require_once('core.php');

$s = '<</Filter/DCTDecode/Type/XObject/Length 17619/BitsPerComponent 8/Height 181/ColorSpace/DeviceRGB/Subtype/Image/Width 420>>';  ///PARSES OK
//$s = '<</ID [<dec3a307af8cac62a65c1f0d0b600228><dec3a307af8cac62a65c1f0d0b600228>]/Root 9 0 R/Size 11/Info 10 0 R>>';  ///PARSES OK


//XXX WONT PARSE BECAUSE ITS MULTIDIMENSIONAL:
//$s = '<</Type/Page/Contents 6 0 R/Parent 7 0 R/Resources<</XObject<</img0 1 0 R>>/ProcSet [/PDF /Text /ImageB /ImageC /ImageI]/Font<</F1 2 0 R/F3 4 0 R/F2 3 0 R/F4 5 0 R>>>>/MediaBox[0 0 595 842]>>';

$dict = pdf_parse_dict($s);
d($dict);

?>