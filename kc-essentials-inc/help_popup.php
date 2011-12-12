<?php

/**
 * @package KC_Essentials
 * @version 0.1
 */


function kcEssentials_help_popup(){
	wp_enqueue_script( 'kc-help-popup', kcEssentials::$data['paths']['scripts'].'/help-popup.js', array('jquery-lightbox_me', 'jquery-hotkeys'), kcEssentials::$data['version'], true );?>
<style type="text/css">#kc-help-popup{ max-width:90%;max-height:90%;background:#000;opacity:.85;color:#fff;font-size:1.2em;line-height:1.5;padding:3em .5em 3em 3em;overflow:hidden;-webkit-border-radius: 15px;-khtml-border-radius: 15px;-moz-border-radius: 15px;border-radius: 15px;position:absolute;} #kc-help-popup ._wrap{width:100%;height:100%;overflow:auto} #kc-help-popup ._inside{padding-right:3em} #kc-help-popup hr{color:#dfdfdf;width:25%;margin:2em 0 3em} #kc-help-popup p, #kc-help-popup ul, #kc-help-popup ol{margin-bottom:1em} #kc-help-popup ol, #kc-help-popup ul{padding-left:2em} #kc-help-popup ol{list-style:decimal} #kc-help-popup ul{list-style:square} #kc-help-popup .title{color:#dddd00;margin-top:2em;font-size:1.4em} #kc-help-popup .title:first-child{margin-top:0}</style>
<?php }
add_action( 'admin_head', 'kcEssentials_help_popup' );

?>