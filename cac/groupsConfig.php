<?php

/**
 * Groups configuration for default Minify implementation
 * @package Minify
 */

/** 
 * You may wish to use the Minify URI Builder app to suggest
 * changes. http://yourdomain/min/builder/
 **/

return array(
    'js'=> array('//ui/jquery.js', '//ui/form.js', '//ui/main.js', '//ui/selectbox.js', '//ui/swfobject.js'),
	'css'=> array('//ui/reset.css', '//ui/main.css', '//ui/form.css', '//ui/selectbox.css'),
	'adminjs' => array('//ui/jquery.js', '//ui/form.js', '//ui/main.js', '//ui/selectbox.js', '//sys/ui/nav.js', '//sys/ui/jquery.tablednd.0.7.min.js', '//sys/ui/jquery.tablesorter.js'/*, '//ui/cal/WdatePicker.js' '//ui/swfupload/js/swfupload.js', '//ui/swfupload/js/handlers.js', '//ui/swfupload/js/fileprogress.js'*/ /*, '//sys/ui/artDialog.js'*/ /*, '//ui/jquery.lazyload.js'*/ ),//不需要lazyload，页面图片不多不大，用了这个反而慢；用不了artDialog，合并后会出错，出错在+ + new Date()处
	'admincss'=> array('//ui/reset.css', '//ui/main.css', '//ui/form.css', '//ui/selectbox.css', '//sys/ui/stylesheet.css', '//sys/ui/nav.css'/*, '//sys/ui/artDialog.css'*/),
	
    // custom source example
    /*'js2' => array(
        dirname(__FILE__) . '/../min_unit_tests/_test_files/js/before.js',
        // do NOT process this file
        new Minify_Source(array(
            'filepath' => dirname(__FILE__) . '/../min_unit_tests/_test_files/js/before.js',
            'minifier' => create_function('$a', 'return $a;')
        ))
    ),//*/

    /*'js3' => array(
        dirname(__FILE__) . '/../min_unit_tests/_test_files/js/before.js',
        // do NOT process this file
        new Minify_Source(array(
            'filepath' => dirname(__FILE__) . '/../min_unit_tests/_test_files/js/before.js',
            'minifier' => array('Minify_Packer', 'minify')
        ))
    ),//*/
);

?>