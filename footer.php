<!--::footer_part start::-->
   <style>
      .linkfooter {
         color: <?php echo  $backgroundColorp; ?>;
         cursor: pointer;
      }
   </style>

   <br><br><br>
   <footer style="background-color: #ecf0f1;width: 100%; bottom: 0; left: 0; background: linear-gradient(180deg, rgba(67,67,67,1) -4%, rgba(255,255,255,1) 3%);">
      <div class="container">
         <div class="row">
            <div class="col-sm-6 col-lg-3">
               <div class="single_footer_part">
                  <div style="width: 100% ;">
                     <a href="" class="footer_logo">
                        <img style="width: 100% ; padding-top: 30px;" src="imagenes/<?php echo $Sistema_logo_principal; ?>" alt="Sin imagen">
                     </a>
                  </div>
               </div>
            </div>
            <div class="col-sm-6 col-lg-3" style="padding-top: 30px;">
               <div class="single_footer_part" >
                  <h4><b>Información de contacto</b></h4>
                  <p >Dirección :  <b><?php echo $Sistema_direccion; ?></b></p>
                  <p >Teléfonos :  <b><?php echo $Sistema_telefonos; ?></b>
                  <a class="linkfooter" href="<?php echo $Sistema_whatsapp_liga;?>" target="_blank" ><p>WhatsApp:  <b><?php echo $Sistema_whatsapp_numero; ?></b></p></a>
                  <p >Horario de atención:<b> <?php echo $Sistema_horario_de_atencion; ?></b></p>
               </div>
            </div>
            <div class="col-sm-6 col-lg-3" style="padding-top: 30px;">
               <div class="single_footer_part">
                  <h4 ><b>Links de interés</b></h4>
                  <ul class="list-unstyled">
                     <li><a class="linkfooter" href="#"  target="_blank"><b>Link 1</b></a></li>
                     <li><a class="linkfooter" href="#"  target="_blank"><b>Link 2</b></a></li> 
                  </ul>
               </div>
            </div>
            <div class="col-sm-6 col-lg-3" style="padding-top: 30px;">
               <form action="" method="post">
               <div class="single_footer_part" >
                  <h4><b>Mantente informado</b></h4>
                  
                  </p>
                  <div class="mail_part">
                     <script type="text/javascript" src="https://app.getresponse.com/view_webform_v2.js?u=SEMDh&webforms_id=B7A0m"></script>
                  </div>
               </div>
               </form>
               Si tiene alguna pregunta o inquietud, por favor contáctenos a través
               del correo electrónico <b>contacto@hielolanacional.com</b>
            </div>
         </div>
         <hr>
         <div class="row">
            <div class="col-sm-6 col-lg-6">
               <div class="copyright_text">
                  <P style="text-align: center;"><a class="linkfooter" href="<?php echo $Sistema_liga_sitio_oficial; ?>" target="_blank"><b><?php echo $Sistema_nombre_proyecto_mayusculas; ?></b></a></p>
               </div>
            </div>
            <div class="col-sm-6 col-lg-6" style="text-align: center;">
            <div class="footer_icon">
                  <ul class="list-unstyled">
                  <li class="list-inline-item">
                     <a class="linkfooter" href="<?php echo $SGIligaFacebook; ?>" target="_blank">
                        <i class="fab fa-facebook-f" style="color: #046ee5;"></i>
                        <b>Facebook</b>
                     </a>
                  </li>
                  <li class="list-inline-item">
                     <a class="linkfooter" href="<?php echo $SGIligaLinkedin; ?>" target="_blank">
                        <i class="fab fa-linkedin" style="color: #000;"></i>
                        <b>Linkedin</b>
                     </a>
                  </li>
                  <li class="list-inline-item">
                     <a class="linkfooter" href="<?php echo $SGIligaTwiter; ?>" target="_blank">
                        <i class="fab fa-twitter" style="color: #1d9bf0;"></i>
                        <b>Twitter</b>
                     </a>
                  </li>
                  <li class="list-inline-item">
                     <a class="linkfooter" href="<?php echo $SGIligaYoutube; ?>" target="_blank">
                        <i class="fab fa-youtube" style="color: #ca2929"></i>
                        <b>Youtube</b>
                     </a>
                  </li>
                  <li class="list-inline-item">
                     <a class="linkfooter" href="<?php echo $SGIligaInstagram; ?>" target="_blank">
                        <i class="fab fa-instagram" style="color: #e1306c;"></i>
                        <b>Instagram</b>
                     </a>
                  </li>
                  <li class="list-inline-item">
                     <a class="linkfooter" href="<?php echo $SGIligaTikTok; ?>" target="_blank">
                        <img src="imagenes/tiktok.png" alt="TikTok" style="width: 20px; height: 20px;">
                        <b>TikTok</b>
                     </a>
                  </li>
                  </ul>
               </div>
            </div>
         </div>
      </div>
      <div class="col-12" style="padding-top: 30px; background: linear-gradient(0deg,rgba(67,67,67,1) -30%, rgba(255,255,255,1) 40%);">
      </div>
   </footer>

</body>
</html>
