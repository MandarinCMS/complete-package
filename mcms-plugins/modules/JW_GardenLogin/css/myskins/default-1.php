<?php

function first_presets() {
  ob_start();
  ?>
  <style media="screen">
    /*************************************************************
     * Module:      GardenLogin - Customizing the MandarinCMS Login.*
     * Description: GardenLogin is the best Login Page Customizer *
     *              in MandarinCMS which allows you to completely  *
     *              change the layout of login, register and     *
     *              forgot password forms.                       *
     * Author:      JIIWorks                                    *
     * visit:       https://mandarincms.org/modules/gardenlogin/    *
     *************************************************************/
    body.login {
      background-image: url(<?php echo modules_url( 'img/bg.jpg', LOGINPRESS_PLUGIN_BASENAME )  ?>);
      /*background-color: #ddd5c3;*/
      background-repeat: no-repeat;
      background-position: center;
      background-size: cover;
      background-attachment: fixed;
    	display: table;
    	min-height: 100vh;
    	width: 100%;
    	padding: 0;
    }
    .header-wrapper{
    	display: table-row;
    }
    #login{
    	margin-top: 2%;
    	background: #fff;
    	position: relative;
    	-webkit-border-radius: 5px;
    	-moz-border-radius: 5px;
    	-ms-border-radius: 5px;
    	-o-border-radius: 5px;
    	border-radius: 5px;
    	-webkit-box-shadow: 0 0 15px rgba(0,0,0,.8);
    	box-shadow: 0 0 15px rgba(0,0,0,.8);
      max-width: 350px;
    	padding: 26px 26px 6px 26px;
    	width: 100%;
    }
    .login form {
    	margin-top: 20px;
    	margin-left: 0;
    	padding: 0 24px 12px;
    	background: #fff;
    	-webkit-box-shadow: none;
    	box-shadow: none;
    }
    .login form .input, .login input[type="text"]{
    	height: 48px;
    	background: #fff;
    	-webkit-border-radius: 5px;
    	-moz-border-radius: 5px;
    	-ms-border-radius: 5px;
    	border-radius: 5px;
    	margin-bottom: 18px;
    	font: normal 15px "Roboto", sans-serif;
    	color: #7f7f7f;
    	border-color: #bdbdbd;
    	padding-left: 27px;
    	font-weight: normal;
    }
    .login input[type="submit"] {
    	background: #fff;
    	color: #fff;
    	margin: 7px 0 7px;
    	height: 46px;
    	-webkit-border-radius: 5px;
    	-moz-border-radius: 5px;
    	-ms-border-radius: 5px;
    	border-radius: 5px;
    	font: normal 15px "Roboto", sans-serif;
    	color: #fff;
    	width: 100%;
    }
    .mcms-core-ui .button-group.button-large .button, .mcms-core-ui .button.button-large{
    	height: 46px;
    	line-height: 0;
    	background: #008ec2;
    }
    .login #nav {
    	text-align: center;
    }
    #login_error { display:block; }
    /*.message { display:none; }*/
    /*.custom-message {
    	-moz-border-radius:3px 3px 3px 3px;
    	border-style:solid;
    	border-width:1px;
    	margin:0 0 16px 8px;
    	padding:12px;
    }*/

    #backtoblog{
    	text-align: center;
    }
    .header-cell{
    	display: table-cell;
    	height: 100px;
    }
    .loginHeaderMenu{
    	text-align: center;
    	position: relative;
    	z-index: 10;
    	list-style: none;
    	background: #333;

    }
    .loginHeaderMenu>ul>li{
    	display: inline-block;
    	vertical-align: top;
    	position: relative;
    	list-style: none;
    }
    .loginHeaderMenu>ul>li>a{
    	color: #fff;
    	text-transform: uppercase;
    	text-decoration: none;
    	font-size: 16px;
    	padding: 17px 20px;
    	display: inline-block;
    }
    .loginHeaderMenu>ul>li:hover>a{
    	background: #4CAF50;
    	color: #fff;
    }
    .loginHeaderMenu>ul>li>ul{
    	position: absolute;
    	width: 200px;
    	padding: 0;
    	top: 100%;
    	left: 0;
    	background: #fff;
    	list-style: none;
    	text-align: left;
    	border-radius: 0 0 5px 5px;
    	-webkit-box-shadow: 0px 5px 10px -1px rgba(0,0,0,0.31);
    	-moz-box-shadow: 0px 5px 10px -1px rgba(0,0,0,0.31);
    	box-shadow: 0px 5px 10px -1px rgba(0,0,0,0.31);
    	overflow: hidden;
    	opacity: 0;
    	visibility: hidden;
    }
    .loginHeaderMenu>ul>li:hover>ul{
    	opacity: 1;
    	visibility: visible;
    }
    .loginHeaderMenu>ul>li>ul>li{
    	font-size: 15px;
    	color: #333;
    }
    .loginHeaderMenu>ul>li>ul>li>a{
    	color: #333;
    	padding: 10px;
    	display: block;
    	text-decoration: none;
    }
    .loginHeaderMenu>ul>li>ul>li>a:hover {
    	background: rgba(51, 51, 51, 0.35);
    	color: #fff;
    }
    .login-wrapper{
    	display: table-row;
    }
    .login-cell{
    	display: table-cell;
    	vertical-align: middle;
    }
    .clear{
    	display: none;
    }
    .footer-wrapper{
    	display: table-footer-group;
    }
    .footer-cont{

    	right: 0;
    	bottom: 0;
    	left: 0;
    	text-align: center;
    	display: table-cell;
    	vertical-align: bottom;
    	height: 100px;
    }
    .loginFooterMenu{
    	text-align: center;
    	background-color: rgba(0,0,0,.7);
    }
    .loginFooterMenu>ul{
    	display: inline-flex;
    }

    .loginFooterMenu>ul>li{
    	display: inline-block;
    	padding: 18px;
    }
    .loginFooterMenu>ul>li:focus{
    	outline: none;
    	border: 0;
    }
    .loginFooterMenu>ul>li>a:focus{
    	outline: none;
    	border: 0;
    }
    .loginFooterMenu>ul>li>a{
    	color: #fff;
    	text-transform: uppercase;
    	text-decoration: none;
    	font-size: 14px;
    }
    .loginHead {
    	width:100%;
    	text-align: center;
    	margin-top: 0;
    	margin-bottom: 20px;
    	padding: 13px 0 0;
    	position: relative;
    	margin: 0;
    }
    .loginHead p a {
  	  text-decoration: none;
    }
    .loginFooter {
    	display: table-footer-group;
    }
    .loginFooter p a {
    	display: inline-block;
    	margin: 0 5px;
    }
    .loginInner {
    	margin: 0 auto;
    	text-align: center;
    	margin-top: 20px;
    	margin-bottom: 0;
    	padding: 13px 0;
    	position: relative;
    }
    .copyRight{
    	text-align: center;
    	padding: 12px;
    	background-color: #efefef;
    }
    .gardenlogin-show-love{
      color: #222;
    }
    .gardenlogin-show-love a{
      color: #263466;
    }
    .gardenlogin-show-love a:hover{
      color: #4d5d95;
    }
    @media screen and (max-width: 767px) {
        #login{
            width: 300px;
        }
        .login form{
            padding-right: 0;
            padding-left: 0;
        }
        .login .gardenlogin-show-love{
            position: static;
            text-align: center;
            float: none;
            background: rgba(255,255,255, .5);
            margin-top: 11px;
            padding-bottom: 0;
            padding: 3px;
        }
    }
      
    </style>

  <?php
  $content = ob_get_clean();
  return $content;
}
