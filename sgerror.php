<?php
include "globals.inc";
include "config.inc";
$page_info = <<<EOD
# ----------------------------------------------------------------------------------------------------------------------
# SquidGuard error page generator
# (C)2006-2007 Serg Dvoriancev
# ----------------------------------------------------------------------------------------------------------------------
# This programm processed redirection to specified URL or generated error page for standart HTTP error code.
# Redirection supported http and https protocols.
# ----------------------------------------------------------------------------------------------------------------------
# Format:
#        sgerror.php?url=[http://myurl]or[https://myurl]or[error_code[space_code]output-message][incoming SquidGuard variables]
# Incoming SquidGuard variables:
#        a=client_address
#        n=client_name
#        i=client_user
#        s=client_group
#        t=target_group
#        u=client_url
# Example:
#        sgerror.php?url=http://myurl.com&a=..&n=..&i=..&s=..&t=..&u=..
#        sgerror.php?url=https://myurl.com&a=..&n=..&i=..&s=..&t=..&u=..
#        sgerror.php?url=404%20output-message&a=..&n=..&i=..&s=..&t=..&u=..
# ----------------------------------------------------------------------------------------------------------------------
# Tags:
#        myurl and output messages can include Tags
#                [a] - client address
#                [n] - client name
#                [i] - client user
#                [s] - client group
#                [t] - target group
#                [u] - client url
# Example:
#         sgerror.php?url=401 Unauthorized access to URL [u] for client [n]
#      sgerror.php?url=http://my_error_page.php?cladr=%5Ba%5D&clname=%5Bn%5D // %5b=[ %d=]
# ----------------------------------------------------------------------------------------------------------------------
# Special Tags:
#      blank     - get blank page
#        blank_img - get one-pixel transparent image (for replace banners and etc.)
# Example:
#        sgerror.php?url=blank
#        sgerror.php?url=blank_img
# ----------------------------------------------------------------------------------------------------------------------
EOD;
 
define('ACTION_URL', 'url');
define('ACTION_RES', 'res');
define('ACTION_MSG', 'msg');
 
define('TAG_BLANK',     'blank');
define('TAG_BLANK_IMG', 'blank_img');
 
# ----------------------------------------------------------------------------------------------------------------------
# ?url=EMPTY_IMG
#      Use this options for replace baners/ads to transparent picture. Thisbetter for viewing.
# ----------------------------------------------------------------------------------------------------------------------
# NULL GIF file
# HEX: 47 49 46 38 39 61 - - -
# SYM: G  I  F  8  9  a  01 00 | 01 00 80 00 00 FF FF FF | 00 00 00 2C 00 00 00 00 | 01 00 01 00 00 02 02 44 | 01 00 3B
# ----------------------------------------------------------------------------------------------------------------------
define(GIF_BODY, "GIF89a\x01\x00\x01\x00\x80\x00\x00\xFF\xFF\xFF\x00\x00\x00\x2C\x00\x00\x00\x00\x01\x00\x01\x00\x00\x02\x02\x44\x01\x00\x3B");
 
$url  = '';
$msg  = '';
$cl   = Array(); // squidGuard variables: %a %n %i %s %t %u
$err_code = array();
 
$err_code[301] = "301 Moved Permanently";
$err_code[302] = "302 Found";
$err_code[303] = "303 See Other";
$err_code[305] = "305 Use Proxy";
 
$err_code[400] = "400 Bad Request";
$err_code[401] = "401 Unauthorized";
$err_code[402] = "402 Payment Required";
$err_code[403] = "Acesso Indevido";
$err_code[404] = "404 Not Found";
$err_code[405] = "405 Method Not Allowed";
$err_code[406] = "406 Not Acceptable";
$err_code[407] = "407 Proxy Authentication Required";
$err_code[408] = "408 Request Time-out";
$err_code[409] = "409 Conflict";
$err_code[410] = "410 Gone";
$err_code[411] = "411 Length Required";
$err_code[412] = "412 Precondition Failed";
$err_code[413] = "413 Request Entity Too Large";
$err_code[414] = "414 Request-URI Too Large";
$err_code[415] = "415 Unsupported Media Type";
$err_code[416] = "416 Requested range not satisfiable";
$err_code[417] = "417 Expectation Failed";
 
$err_code[500] = "500 Internal Server Error";
$err_code[501] = "501 Not Implemented";
$err_code[502] = "502 Bad Gateway";
$err_code[503] = "503 Service Unavailable";
$err_code[504] = "504 Gateway Time-out";
$err_code[505] = "505 HTTP Version not supported";
 
# ----------------------------------------------------------------------------------------------------------------------
# check arg's
# ----------------------------------------------------------------------------------------------------------------------
 
if (count($_POST)) {
    $url  = trim($_POST['url']);
    $msg  = $_POST['msg'];
    $cl['a'] = $_POST['a'];
    $cl['n'] = $_POST['n'];
    $cl['i'] = $_POST['i'];
    $cl['s'] = $_POST['s'];
    $cl['t'] = $_POST['t'];
    $cl['u'] = $_POST['u'];
}
elseif (count($_GET)) {
    $url  = trim($_GET['url']);
    $msg  = $_GET['msg'];
    $cl['a'] = $_GET['a'];
    $cl['n'] = $_GET['n'];
    $cl['i'] = $_GET['i'];
    $cl['s'] = $_GET['s'];
    $cl['t'] = $_GET['t'];
    $cl['u'] = $_GET['u'];
}
else {
       # Show 'About page'
        echo get_page(get_about());
        exit();
}
 
# ----------------------------------------------------------------------------------------------------------------------
# url's
# ----------------------------------------------------------------------------------------------------------------------
if ($url) {
    $err_id = 0;
 
    // check error code
    foreach ($err_code as $key => $val) {
            if (strpos(strtolower($url), strval($key)) === 0) {
               $err_id = $key;
               break;
            }
    }
 
    # blank page
    if ($url === TAG_BLANK) {
            echo get_page('');
    }
    # blank image
    elseif ($url === TAG_BLANK_IMG) {
           $msg = trim($msg);
           if(strpos($msg, "maxlen_") !== false) {
              $maxlen = intval(trim(str_replace("maxlen_", "", $url)));
              filter_by_image_size($cl['u'], $maxlen);
              exit();
           }
           else {
              # --------------------------------------------------------------
              # return blank image
              # --------------------------------------------------------------
              header("Content-Type: image/gif;"); //  charset=windows-1251");
              echo GIF_BODY;
           }
    }
    # error code
    elseif ($err_id !== 0) {
            $er_msg = strstr($_GET['url'], ' ');
            echo get_error_page($err_id, $er_msg);
    }
    # redirect url
    elseif ((strpos(strtolower($url), "http://") === 0) or (strpos(strtolower($url), "https://") === 0)) {
            # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
            # redirect to specified url
            # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
            header("HTTP/1.0");
            header("Location: $url", '', 302);
    }
    // error arguments
    else {
        echo get_page("sgerror: error arguments $url");
    }
}
else {
        echo get_page($_SERVER['QUERY_STRING']); //$url . implode(" ", $_GET));
#        echo get_error_page(500);
}
 
# ~~~~~~~~~~
# Exit
# ~~~~~~~~~~
exit();
 
# ----------------------------------------------------------------------------------------------------------------------
# functions
# ----------------------------------------------------------------------------------------------------------------------
function get_page($body) {
        $str = Array();
        $str[] = '<html>';
        $str[] = "<body>\n$body\n</body>";
        $str[] = '</html>';
        return implode("\n", $str);
}
 
#########################################################
#                                                       #
#  Página de Erro do SquidGuard customizada   - Inicio  #
#                                                       #
#########################################################
 
     
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
# IE displayed self-page, if them size > 1024
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 
function get_error_page($er_code_id, $err_msg='') {
        global $err_code;
        global $cl;
        global $g;
        global $config;
        $str = Array();
 
 
             
    $str[] = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html> <head> 
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/js/bootstrap.min.js" integrity="sha384-a5N7Y/aK3qNeh15eJKGWxsqtnX/wWdSZSKp+81YjTmS15nvnvxKHuzaWwXHDli+4" crossorigin="anonymous"></script>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous">
        </head>
             <body> 
             <title> GSF - Grupo Sopro Firewall </title>
  <div class="jumbotron" align="center">
        <div class="container" align ="center">
<img align="top" src="http://suporte.gruposopro.com.br/scp/logo.php?1505139886"</img>
</div>
      <h1 style="color:red;">Acesso negado!</h1>
        <p style="color:red;" > Você tentou acessar : '.$cl['u'].'</p>
          <p> Motivo do Bloqueio : '.$cl['t'].'</p>
               <p> O seu ip é: '.$cl['a'].'</p> 
    <a class="btn btn-success" href="mailto:suporte@gruposopro.com.br?subject=Liberar%20Acesso&body=OlÃ¡, %0D%0AMeu dispositivo Ã©:%20%0A%20'.$cl['n'].'%20%0D%0A%20 %20%0D%0A%20 %0D%0A%20Quero liberar o seguinte site: '.$cl['u'].' ">Requisitar acesso</a>    
             </button>

<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal" data-whatever="@mdo">Enviar solicitação</button>


<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Solicitar acesso</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="gform" method="POST" class="pure-form pure-form-stacked" data-email="example@email.net"
  action="https://script.google.com/macros/s/AKfycbxvbpWFIMJZ4kpXRH5sS4LfY9SW1_sIFdpT9XInB9MwNjxKHdQ/exec">
    <!-- change the form action to your script url -->

          
<div class="form-group">
            <label for="name" class="form-control-label">Nome:</label>
            <input type="text" id="name" name="name" class="form-control" >
          </div>

          <div class="form-group">
            <label for="message" class="form-control-label">Message:</label>
            <textarea class="form-control" id="message" name="message""></textarea> 
          </div>

    <div class="class="form-control-label">
      <label for="email">Email:</label>
      <input type="text" id="email" name="email" type="email" class="form-control" value=""
      required placeholder=""/>
      <span id="email-invalid" style="display:none">
         Digite um endereço válido!</span>
    </div>

    <fieldset disabled>
    <label for="color" class="form-control-label" >Site: </label>
            <input type="text" id="color" name="color" value="'.$cl['u'].'" class="form-control" >
</fieldset>

   
           
  <!-- Submit the Form to Google Using "AJAX" -->
  <script data-cfasync="false" type="text/javascript"
  src="https://cdn.rawgit.com/dwyl/html-form-send-email-via-google-script-without-server/master/form-submission-handler.js"></script>

      </div>
      <div class="modal-footer">

     
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
         <button class="btn btn-success">
      <i class="fa fa-paper-plane"></i>Enviar
      </div>
    </div>

  </form>

        </form>
  </div>
</div>



   

 

  </form>

             
</div>  

    </body>
        </html> 
              ';
 
        return implode("\n", $str);
}
 
 
 
#########################################################
#                                                       #
#  Página de Erro do SquidGuard customizada   - Fim     #
#                                                       #
#########################################################
 
function filter_by_image_size($url, $val_size) {
 
          # load url header
          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $url);
          curl_setopt($ch, CURLOPT_HEADER, 1);
          curl_setopt($ch, CURLOPT_NOBODY, 1);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
          $hd = curl_exec($ch);
          curl_close($ch);
 
         $size = 0;
         $SKEY = "content-length:";
         $s_tmp = strtolower($hd);
         $s_tmp = str_replace("\n", " ", $s_tmp); # replace all "\n"
         if (strpos($s_tmp, $SKEY) !== false) {
             $s_tmp = trim(substr($s_tmp, strpos($s_tmp, $SKEY) + strlen($SKEY)));
             $s_tmp = trim(substr($s_tmp, 0, strpos($s_tmp, " ")));
             if (is_numeric($s_tmp))
                  $size = intval($s_tmp);
             else $size = 0;
         }
 
         # === check url type and content size ===
         # redirect to specified url
         if (($size !== 0) && ($size < $val_size)) {
              header("HTTP/1.0");
              header("Location: $url", '', 302);
         }
         # return blank image
         else {
              header("Content-Type: image/gif;");
              echo GIF_BODY;
         }
}
?>?>