
<?php include 'header.php';?>
                
        <section class="section">
            <div class="container">
                <span class="is-size-4">Provision a security toolkit quickly on the cloud:</span>
                <hr>
        <div class="columns">
            <div class="column is-one-third">
                    <div class="box">
                            <article class="media">
                              <div class="media-left">
                                <figure class="image is-64x64">
                                  <img src="https://www.digitalocean.com/assets/media/logos-badges/png/DO_Logo_Vertical_Blue-6321464d.png" alt="Image">
                                </figure>
                              </div>
                              <div class="media-content">
                                <div class="content">
                                  <p>
                                    <strong>DigitalOcean</strong>
                                    
                                  </p>
                                  <a class="button is-info is-outlined modal-button"id="do-btn" disabled>Coming Soon</a>
                                </div>
                              </div>
                            </article>
                          </div>
            </div>

            <div class="column is-one-third">
                    <div class="box">
                            <article class="media">
                              <div class="media-left">
                                <figure class="image is-64x64">
                                  <img src="img/aws.png" alt="Image">
                                </figure>
                              </div>
                              <div class="media-content">
                                <div class="content">
                                  <p>
                                    <strong>AWS</strong>
                                    
                                  </p>
                                  <a class="button is-info is-outlined modal-button"id="aws-btn">Go!</a>
                                </div>
                              </div>
                            </article>
                          </div>
            </div>

            <div class="column is-one-third">
                    <div class="box">
                            <article class="media">
                              <div class="media-left">
                                <figure class="image is-64x64">
                                  <img src="img/google.png" alt="Image">
                                </figure>
                              </div>
                              <div class="media-content">
                                <div class="content">
                                  <p>
                                    <strong>Google Cloud</strong>
                                    
                                  </p>
                                  <a class="button is-info is-outlined modal-button"id="goog-btn" disabled>Coming Soon</a>
                                </div>
                              </div>
                            </article>
                          </div>
            </div>


        </div>
        
        <div class="modal" id="mdl">
            <div class="modal-background"></div>
            <div class="modal-card">
                <header class="modal-card-head">
                  <p class="modal-card-title">Amazon API Keys</p>
                  <button id="del" class="delete" aria-label="close"></button>
                </header>
                <form action="create.php" method="post">
                <section class="modal-card-body">
                    <div class="field">
                        <label class="label">Access Key ID</label>
                        <div class="control">
                          <input class="input" id="keyID" name="keyID" type="text" placeholder="ABCXYZ...">
                        </div>
                      </div>
                      <div class="field">
                        <label class="label">Secret Access Key</label>
                        <div class="control">
                          <input class="input" id="keySecret" name="keySecret" type="text" placeholder="YuXz456...">
                        </div>
                      </div>
                </section>
                <footer class="modal-card-foot">
                  <button type="submit" class="button is-success">Go</button>
                  </form>
                </footer>
              </div>
            <!--<button id="mdl-close" class="modal-close is-large" aria-label="close"></button>-->
          </div>

        </section>

        <script type="text/javascript">
          var modalElement = document.getElementById('mdl');
          //var modalClose = document.getElementById('mdl-close');
          var ltlEx = document.getElementById('del');
          document.getElementById('aws-btn').addEventListener('click', function() {
            modalElement.classList.add("is-active");
          });
          
          /*modalClose.addEventListener('click', function() {
            modalElement.classList.remove("is-active");
          });*/
          ltlEx.addEventListener('click', function() {
            modalElement.classList.remove("is-active");
          });
        </script>
    </body>
</html>